<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Tenant;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all';
    public $tenantFilter = 'all';
    public $showRolesModal = false;
    public $showDeleteModal = false;
    public $showTenantModal = false;

    public $userId;
    public $selectedRoles = [];
    public $selectedTenant = null;

    public function render()
    {
    $query = User::with(['roles', 'tenant']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter !== 'all') {
            $query->whereHas('roles', function($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        if ($this->tenantFilter !== 'all') {
            $query->where('tenant_id', $this->tenantFilter);
        }

        $users = $query->orderBy('name')->paginate(15);
        $roles = Role::orderBy('name')->get();
        $allRoles = Role::orderBy('name')->get();
        $tenants = Tenant::orderBy('name')->get();

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'roles' => $roles,
            'allRoles' => $allRoles,
            'tenants' => $tenants,
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedTenantFilter()
    {
        $this->resetPage();
    }

    public function openRolesModal($userId)
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->userId = $user->id;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();

        $this->showRolesModal = true;
    }

    public function updateUserRoles()
    {
        $user = User::findOrFail($this->userId);
        $roles = Role::whereIn('id', $this->selectedRoles)->get();

        $user->syncRoles($roles);

        $this->showRolesModal = false;
        $this->resetForm();

        session()->flash('message', 'User roles updated successfully!');
    }

    public function openTenantModal($userId)
    {
        $user = User::with('tenant')->findOrFail($userId);

        $this->userId = $user->id;
        $this->selectedTenant = $user->tenant_id;

        $this->showTenantModal = true;
    }

    public function updateUserTenant()
    {
        $user = User::findOrFail($this->userId);

        $user->update([
            'tenant_id' => $this->selectedTenant
        ]);

        $this->showTenantModal = false;
        $this->resetForm();

        session()->flash('message', 'User tenant updated successfully!');
    }

    public function openDeleteModal($userId)
    {
        $this->userId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        $user = User::findOrFail($this->userId);

        // Check if user has any critical data that should prevent deletion
        // Add any business logic checks here

        $user->delete();

        $this->showDeleteModal = false;
        session()->flash('message', 'User deleted successfully!');
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);

        // Toggle email_verified_at to simulate active/inactive status
        // You might want to add a proper 'active' field to your users table
        if ($user->email_verified_at) {
            $user->update(['email_verified_at' => null]);
            session()->flash('message', 'User deactivated successfully!');
        } else {
            $user->update(['email_verified_at' => now()]);
            session()->flash('message', 'User activated successfully!');
        }
    }

    public function closeModals()
    {
        $this->showRolesModal = false;
        $this->showDeleteModal = false;
    $this->showTenantModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->userId = null;
        $this->selectedRoles = [];
    $this->selectedTenant = null;
        $this->resetErrorBag();
    }
}
