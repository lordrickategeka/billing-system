<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tenant;
use App\Models\Service;
use App\Models\Voucher;
use App\Models\Subscription;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardComponent extends Component
{
   public $selectedTenant;
    public $stats = [];
    public $currentTenant;
    public $showProfileIncompleteAlert = false;

    public function mount()
    {
        $this->selectedTenant = Tenant::first()?->id;
        $this->loadStats();
        $this->checkCurrentTenantProfile();
    }

    public function updatedSelectedTenant()
    {
        $this->loadStats();
    }

    public function checkCurrentTenantProfile()
    {
        $user = Auth::user();
        if ($user && $user->tenant) {
            $this->currentTenant = $user->tenant;
            // Always show alert if profile is not complete and setup was skipped
            $this->showProfileIncompleteAlert = !$this->currentTenant->isProfileComplete() 
                && $this->currentTenant->wasSetupSkipped();
        }
    }

    public function completeProfile()
    {
        return redirect()->route('profile.setup');
    }

    public function dismissAlert()
    {
        // Only hide the alert for this session, it will reappear on page reload
        $this->showProfileIncompleteAlert = false;
    }

    public function loadStats()
    {
        if (!$this->selectedTenant) return;

        $this->stats = [
            'total_services' => Service::where('tenant_id', $this->selectedTenant)->count(),
            'active_services' => Service::where('tenant_id', $this->selectedTenant)->where('status', 'active')->count(),
            'hotspot_services' => Service::where('tenant_id', $this->selectedTenant)->where('service_type', 'hotspot')->count(),
            'broadband_services' => Service::where('tenant_id', $this->selectedTenant)->where('service_type', 'broadband')->count(),
            'total_vouchers' => Voucher::where('tenant_id', $this->selectedTenant)->count(),
            'unused_vouchers' => Voucher::where('tenant_id', $this->selectedTenant)->where('state', 'unused')->count(),
            'active_subscriptions' => Subscription::where('tenant_id', $this->selectedTenant)->where('status', 'active')->count(),
            'monthly_revenue' => Subscription::where('subscriptions.tenant_id', $this->selectedTenant)
                ->where('subscriptions.status', 'active')
                ->whereMonth('subscriptions.created_at', now()->month)
                ->join('products', 'subscriptions.product_id', '=', 'products.id')
                ->sum('products.price')
        ];
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.dashboard-component', [
            'tenants' => Tenant::all()]);
    }
}
