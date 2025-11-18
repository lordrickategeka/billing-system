<?php

namespace App\Livewire\ISP;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\RadiusIdentity;
use App\Models\Tenant;
use App\Services\RadiusSyncService;
use Illuminate\Support\Str;

class ServiceManager extends Component
{
    use WithPagination;

    public $selectedTenant;
    public $showCreateModal = false;
    public $showDetailsModal = false;
    public $selectedService = null;

    // Service form fields
    public $customer_id;
    public $product_id;
    public $service_type = 'pppoe';
    public $installation_address = [];
    public $circuit_id;
    public $ont_serial;
    public $static_ip;
    public $installation_date;
    public $notes;

    // Filters
    public $filterStatus = '';
    public $filterServiceType = '';
    public $search = '';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'product_id' => 'required|exists:products,id',
        'service_type' => 'required|in:pppoe,ipoe',
        'installation_address.street' => 'required|string|max:255',
        'installation_address.city' => 'required|string|max:100',
        'installation_address.coordinates.lat' => 'nullable|numeric',
        'installation_address.coordinates.lng' => 'nullable|numeric',
        'circuit_id' => 'nullable|string|max:100',
        'ont_serial' => 'nullable|string|max:100',
        'static_ip' => 'nullable|ip',
        'installation_date' => 'required|date',
        'notes' => 'nullable|string|max:1000'
    ];

    public function mount()
    {
        $this->selectedTenant = Tenant::first()?->id;
        $this->initializeInstallationAddress();
        $this->installation_date = now()->addDays(7)->format('Y-m-d');
    }

    public function initializeInstallationAddress()
    {
        $this->installation_address = [
            'street' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'coordinates' => ['lat' => null, 'lng' => null],
            'installation_notes' => ''
        ];
    }

    public function createService()
    {
        $this->validate();

        // Validate product is for broadband
        $product = Product::findOrFail($this->product_id);
        if ($product->service_type !== 'broadband') {
            session()->flash('error', 'Selected product is not for broadband service.');
            return;
        }

        \DB::transaction(function () use ($product) {
            // Create service
            $service = Service::create([
                'tenant_id' => $this->selectedTenant,
                'customer_id' => $this->customer_id,
                'service_number' => 'SV-' . strtoupper(Str::random(10)),
                'service_type' => 'broadband',
                'installation_address' => $this->installation_address,
                'circuit_id' => $this->circuit_id,
                'ont_serial' => $this->ont_serial,
                'static_ip' => $this->static_ip,
                'status' => 'pending'
            ]);

            // Create subscription
            $subscription = Subscription::create([
                'tenant_id' => $this->selectedTenant,
                'service_id' => $service->id,
                'product_id' => $this->product_id,
                'start_at' => $this->installation_date,
                'end_at' => now()->parse($this->installation_date)->addMonth(),
                'auto_renew' => true,
                'status' => 'pending'
            ]);

            // Generate credentials
            $username = $this->generateUsername($service);
            $password = Str::random(12);

            // Create RADIUS identity
            RadiusIdentity::create([
                'tenant_id' => $this->selectedTenant,
                'service_id' => $service->id,
                'subscription_id' => $subscription->id,
                'username' => $username,
                'password' => $password,
                'circuit_id' => $this->circuit_id,
                'ont_serial' => $this->ont_serial,
                'static_ip' => $this->static_ip,
                'auth_type' => $this->service_type,
                'status' => 'active'
            ]);
        });

        session()->flash('message', 'Broadband service created successfully!');
        $this->resetForm();
        $this->showCreateModal = false;
        $this->resetPage();
    }

    public function activateService($serviceId)
    {
        \DB::transaction(function () use ($serviceId) {
            $service = Service::with(['subscriptions', 'radiusIdentities'])->findOrFail($serviceId);

            $service->update([
                'status' => 'active',
                'activated_at' => now()
            ]);

            // Activate subscription
            $subscription = $service->subscriptions()->where('status', 'pending')->first();
            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'activated_at' => now()
                ]);
            }

            // Sync to RADIUS
            foreach ($service->radiusIdentities as $identity) {
                app(RadiusSyncService::class)->syncIdentity($identity);
            }
        });

        session()->flash('message', 'Service activated successfully!');
    }

    public function suspendService($serviceId)
    {
        $service = Service::with('radiusIdentities')->findOrFail($serviceId);

        $service->update([
            'status' => 'suspended',
            'suspended_at' => now()
        ]);

        // Update RADIUS identities
        foreach ($service->radiusIdentities as $identity) {
            $identity->update(['status' => 'suspended']);
            app(RadiusSyncService::class)->syncIdentity($identity);
        }

        session()->flash('message', 'Service suspended successfully!');
    }

    public function viewService($serviceId)
    {
        $this->selectedService = Service::with([
            'customer',
            'subscriptions.product',
            'radiusIdentities'
        ])->findOrFail($serviceId);

        $this->showDetailsModal = true;
    }

    private function generateUsername(Service $service): string
    {
        $prefix = $this->service_type === 'pppoe' ? 'pppoe' : 'ipoe';
        return $prefix . '_' . strtolower($service->service_number);
    }

    public function resetForm()
    {
        $this->reset([
            'customer_id', 'product_id', 'service_type', 'circuit_id',
            'ont_serial', 'static_ip', 'installation_date', 'notes'
        ]);
        $this->initializeInstallationAddress();
        $this->installation_date = now()->addDays(7)->format('Y-m-d');
    }

    public function render()
    {
        $query = Service::with(['customer', 'activeSubscription.product'])
            ->where('tenant_id', $this->selectedTenant)
            ->where('service_type', 'broadband');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('service_number', 'like', '%' . $this->search . '%')
                  ->orWhere('circuit_id', 'like', '%' . $this->search . '%')
                  ->orWhere('ont_serial', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($customerQuery) {
                      $customerQuery->where('first_name', 'like', '%' . $this->search . '%')
                                   ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterServiceType) {
            $query->whereHas('radiusIdentities', function($q) {
                $q->where('auth_type', $this->filterServiceType);
            });
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.isp.service-manager', [
            'services' => $services,
            'customers' => Customer::where('tenant_id', $this->selectedTenant)->get(),
            'products' => Product::where('tenant_id', $this->selectedTenant)
                                ->where('service_type', 'broadband')
                                ->get(),
            'tenants' => Tenant::all()
        ])->layout('layouts.app');
    }

}
