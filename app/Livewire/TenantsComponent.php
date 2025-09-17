<?php

namespace App\Livewire;

use App\Models\Tenant;
use Livewire\Component;

class TenantsComponent extends Component
{
    public $tenants;
    public $name;
    public $tenantId;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->loadTenants();
    }

    public function loadTenants()
    {
        $this->tenants = Tenant::withCount('customers')->get();
    }

    public function save()
    {
        $this->validate();

        Tenant::create(['name' => $this->name]);

        $this->resetForm();
        $this->loadTenants();
        session()->flash('success', 'Tenant created successfully.');
    }

    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);
        $this->tenantId = $tenant->id;
        $this->name = $tenant->name;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();

        $tenant = Tenant::findOrFail($this->tenantId);
        $tenant->update(['name' => $this->name]);

        $this->resetForm();
        $this->loadTenants();
        session()->flash('success', 'Tenant updated successfully.');
    }

    public function delete($id)
    {
        Tenant::destroy($id);
        $this->loadTenants();
        session()->flash('success', 'Tenant deleted successfully.');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->tenantId = null;
        $this->isEdit = false;
    }

    public function render()
    {
        return view('livewire.tenants-component');
    }
}
