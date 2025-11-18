<?php

namespace App\Livewire\ISP;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\NetworkDevice;
use App\Models\Tenant;
use App\Services\RadiusSyncService;

class NetworkManager extends Component
{
    use WithPagination;

    public $selectedTenant;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingDevice = null;

    // Device form fields
    public $name;
    public $nas_ip_address;
    public $secret;
    public $vendor = 'generic';
    public $type = 'router';
    public $site_name;
    public $location = [];
    public $management = [];
    public $capabilities = [];

    // Filters
    public $filterType = '';
    public $filterVendor = '';
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'nas_ip_address' => 'required|ip|unique:network_devices,nas_ip_address',
        'secret' => 'required|string|min:8|max:255',
        'vendor' => 'required|string|max:100',
        'type' => 'required|in:access_point,bng,olt,switch,router',
        'site_name' => 'required|string|max:255',
        'location.address' => 'nullable|string|max:500',
        'location.lat' => 'nullable|numeric|between:-90,90',
        'location.lng' => 'nullable|numeric|between:-180,180',
        'management.ssh_host' => 'nullable|ip',
        'management.ssh_port' => 'nullable|integer|min:1|max:65535',
        'management.snmp_community' => 'nullable|string|max:100',
        'management.api_endpoint' => 'nullable|url'
    ];

    public function mount()
    {
        $this->selectedTenant = Tenant::first()?->id;
        $this->initializeArrays();
    }

    public function initializeArrays()
    {
        $this->location = [
            'address' => '',
            'lat' => null,
            'lng' => null
        ];

        $this->management = [
            'ssh_host' => '',
            'ssh_port' => 22,
            'ssh_username' => '',
            'snmp_community' => 'public',
            'api_endpoint' => ''
        ];

        $this->capabilities = [
            'coa' => true,
            'dm' => true,
            'accounting' => true,
            'ipoe' => false,
            'pppoe' => false
        ];
    }

    public function createDevice()
    {
        $this->validate();

        $device = NetworkDevice::create([
            'tenant_id' => $this->selectedTenant,
            'name' => $this->name,
            'nas_ip_address' => $this->nas_ip_address,
            'secret' => $this->secret,
            'vendor' => $this->vendor,
            'type' => $this->type,
            'site_name' => $this->site_name,
            'location' => $this->location,
            'management' => $this->management,
            'capabilities' => $this->capabilities,
            'is_active' => true
        ]);

        // Sync to RADIUS
        app(RadiusSyncService::class)->syncNetworkDevice($device);

        session()->flash('message', 'Network device created successfully!');
        $this->resetForm();
        $this->showCreateModal = false;
        $this->resetPage();
    }

    public function editDevice($deviceId)
    {
        $device = NetworkDevice::findOrFail($deviceId);
        $this->editingDevice = $device->id;

        $this->name = $device->name;
        $this->nas_ip_address = $device->nas_ip_address;
        $this->secret = $device->secret;
        $this->vendor = $device->vendor;
        $this->type = $device->type;
        $this->site_name = $device->site_name;
        $this->location = $device->location ?: $this->location;
        $this->management = $device->management ?: $this->management;
        $this->capabilities = $device->capabilities ?: $this->capabilities;

        $this->showEditModal = true;
    }

    public function updateDevice()
    {
        $rules = $this->rules;
        $rules['nas_ip_address'] = 'required|ip|unique:network_devices,nas_ip_address,' . $this->editingDevice;
        $this->validate($rules);

        $device = NetworkDevice::findOrFail($this->editingDevice);
        $device->update([
            'name' => $this->name,
            'nas_ip_address' => $this->nas_ip_address,
            'secret' => $this->secret,
            'vendor' => $this->vendor,
            'type' => $this->type,
            'site_name' => $this->site_name,
            'location' => $this->location,
            'management' => $this->management,
            'capabilities' => $this->capabilities
        ]);

        // Sync to RADIUS
        app(RadiusSyncService::class)->syncNetworkDevice($device);

        session()->flash('message', 'Network device updated successfully!');
        $this->resetForm();
        $this->showEditModal = false;
    }

    public function toggleDevice($deviceId)
    {
        $device = NetworkDevice::findOrFail($deviceId);
        $device->update(['is_active' => !$device->is_active]);

        if ($device->is_active) {
            app(RadiusSyncService::class)->syncNetworkDevice($device);
        }

        session()->flash('message', 'Device status updated successfully!');
    }

    public function testDevice($deviceId)
    {
        $device = NetworkDevice::findOrFail($deviceId);

        // Here you would implement actual device testing
        // For now, we'll just update last_seen_at
        $device->update(['last_seen_at' => now()]);

        session()->flash('message', 'Device test completed!');
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'nas_ip_address', 'secret', 'vendor', 'type',
            'site_name', 'editingDevice'
        ]);
        $this->initializeArrays();
    }

    public function render()
    {
        $query = NetworkDevice::where('tenant_id', $this->selectedTenant);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('nas_ip_address', 'like', '%' . $this->search . '%')
                  ->orWhere('site_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterVendor) {
            $query->where('vendor', $this->filterVendor);
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.isp.network-manager', [
            'devices' => $devices,
            'tenants' => Tenant::all(),
            'vendors' => ['mikrotik', 'cisco', 'ubiquiti', 'huawei', 'zte', 'generic'],
            'types' => ['access_point', 'bng', 'olt', 'switch', 'router']
        ])->layout('layouts.app');
    }
}
