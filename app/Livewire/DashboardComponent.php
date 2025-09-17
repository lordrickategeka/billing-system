<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tenant;
use App\Models\Service;
use App\Models\Voucher;
use App\Models\Subscription;

use Illuminate\Support\Facades\DB;

class DashboardComponent extends Component
{
   public $selectedTenant;
    public $stats = [];

    public function mount()
    {
        $this->selectedTenant = Tenant::first()?->id;
        $this->loadStats();
    }

    public function updatedSelectedTenant()
    {
        $this->loadStats();
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
            'monthly_revenue' => Subscription::where('tenant_id', $this->selectedTenant)
                ->where('status', 'active')
                ->whereMonth('created_at', now()->month)
                ->join('products', 'subscriptions.product_id', '=', 'products.id')
                ->sum('products.price')
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-component', [
            'tenants' => Tenant::all()
        ])->layout('layouts.app');
    }
}
