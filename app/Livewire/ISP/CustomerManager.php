<?php

namespace App\Livewire\ISP;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerManager extends Component
{

    use WithPagination;

    public $selectedTenant;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingCustomer = null;

    // Customer form fields
    public $customer_number;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $address = [];
    public $kyc_data = [];
    public $status = 'active';

    // Search and filters
    public $search = '';
    public $filterStatus = '';
    public $filterKyc = '';

    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('customers')->ignore($this->editingCustomer)],
            'phone' => 'required|string|max:20',
            'address.street' => 'required|string|max:255',
            'address.city' => 'required|string|max:100',
            'address.state' => 'required|string|max:100',
            'address.postal_code' => 'required|string|max:20',
            'address.country' => 'required|string|max:2',
            'kyc_data.id_type' => 'required|string|in:national_id,passport,drivers_license',
            'kyc_data.id_number' => 'required|string|max:50',
            'kyc_data.id_expiry' => 'nullable|date|after:today',
            'status' => 'required|in:active,suspended,terminated'
        ];
    }

    public function mount()
    {
        $this->selectedTenant = Tenant::first()?->id;
        $this->initializeAddress();
        $this->initializeKyc();
    }

    public function initializeAddress()
    {
        $this->address = [
            'street' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => 'US'
        ];
    }

    public function initializeKyc()
    {
        $this->kyc_data = [
            'id_type' => 'national_id',
            'id_number' => '',
            'id_expiry' => '',
            'verified' => false,
            'verified_at' => null,
            'verified_by' => null
        ];
    }

    public function createCustomer()
    {
        $this->validate();

        // Generate customer number
        $this->customer_number = 'CUST-' . strtoupper(uniqid());

        // Ensure we have a valid tenant_id
        if (!$this->selectedTenant) {
            $this->selectedTenant = Tenant::first()?->id ?? 1;
        }

        Customer::create([
            'tenant_id' => $this->selectedTenant,
            'customer_number' => $this->customer_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'kyc_data' => $this->kyc_data,
            'status' => $this->status
        ]);

        session()->flash('message', 'Customer created successfully!');
        $this->resetForm();
        $this->showCreateModal = false;
        $this->resetPage();
    }

    public function editCustomer($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $this->editingCustomer = $customer->id;

        $this->first_name = $customer->first_name;
        $this->last_name = $customer->last_name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address ?: $this->address;
        $this->kyc_data = $customer->kyc_data ?: $this->kyc_data;
        $this->status = $customer->status;

        $this->showEditModal = true;
    }

    public function updateCustomer()
    {
        $this->validate();

        // Ensure we have a valid tenant_id
        if (!$this->selectedTenant) {
            $this->selectedTenant = Tenant::first()?->id ?? 1;
        }

        $customer = Customer::findOrFail($this->editingCustomer);
        $customer->update([
            'tenant_id' => $this->selectedTenant,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'kyc_data' => $this->kyc_data,
            'status' => $this->status
        ]);

        session()->flash('message', 'Customer updated successfully!');
        $this->resetForm();
        $this->showEditModal = false;
    }

    public function verifyKyc($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $customer->update([
            'kyc_verified_at' => now(),
            'kyc_data' => array_merge($customer->kyc_data, [
                'verified' => true,
                'verified_at' => now()->toISOString(),
                'verified_by' => Auth::id() ?? 'system'
            ])
        ]);

        session()->flash('message', 'KYC verified successfully!');
    }

    public function resetForm()
    {
        $this->reset(['first_name', 'last_name', 'email', 'phone', 'status', 'editingCustomer']);
        $this->initializeAddress();
        $this->initializeKyc();
    }

    public function render()
    {
        $query = Customer::with('services')
            ->where('tenant_id', $this->selectedTenant);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterKyc === 'verified') {
            $query->whereNotNull('kyc_verified_at');
        } elseif ($this->filterKyc === 'unverified') {
            $query->whereNull('kyc_verified_at');
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.isp.customer-manager', [
            'customers' => $customers,
            'tenants' => Tenant::all()
        ]);
    }
}
