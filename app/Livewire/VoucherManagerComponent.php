<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Voucher;
use App\Models\Tenant;

use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Str;

class VoucherManagerComponent extends Component
{
 use WithPagination;

    public $selectedTenant;
    public $showGenerateModal = false;
    public $showBatchModal = false;
    public $selectedBatch;

    // Generate form
    public $product_id;
    public $quantity = 10;
    public $prefix = 'HS';
    public $max_uses = 1;
    public $custom_value;
    public $expires_at;

    // Filters
    public $filterState = '';
    public $filterBatch = '';
    public $filterProduct = '';

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1|max:10000',
        'prefix' => 'nullable|string|max:5',
        'max_uses' => 'required|integer|min:1|max:100',
        'custom_value' => 'nullable|numeric|min:0',
        'expires_at' => 'nullable|date|after:now'
    ];

    public function mount()
    {
        $this->selectedTenant = Tenant::first()?->id;
    }

    public function generateVouchers()
    {
        $this->validate();

        $product = Product::findOrFail($this->product_id);

        if ($product->service_type !== 'hotspot') {
            session()->flash('error', 'Selected product is not for hotspot service.');
            return;
        }

        $batchId = 'BATCH_' . strtoupper(Str::random(8));
        $vouchers = [];
        $generatedCodes = [];

        for ($i = 0; $i < $this->quantity; $i++) {
            do {
                $code = ($this->prefix ?: 'HS') . strtoupper(Str::random(8));
            } while (in_array($code, $generatedCodes) || Voucher::where('code', $code)->exists());

            $generatedCodes[] = $code;
            $vouchers[] = [
                'tenant_id' => $this->selectedTenant,
                'product_id' => $this->product_id,
                'code' => $code,
                'batch_id' => $batchId,
                'value' => $this->custom_value ?: $product->price,
                'max_uses' => $this->max_uses,
                'expires_at' => $this->expires_at,
                'state' => 'unused',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        Voucher::insert($vouchers);

        session()->flash('message', "Generated {$this->quantity} vouchers successfully! Batch ID: {$batchId}");
        $this->showGenerateModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function viewBatch($batchId)
    {
        $this->selectedBatch = $batchId;
        $this->showBatchModal = true;
    }

    public function exportBatch($batchId)
    {
        return redirect()->route('vouchers.export', $batchId);
    }

    public function resetForm()
    {
        $this->reset(['product_id', 'quantity', 'prefix', 'max_uses', 'custom_value', 'expires_at']);
        $this->quantity = 10;
        $this->prefix = 'HS';
        $this->max_uses = 1;
    }

    public function render()
    {
        $query = Voucher::with(['product', 'tenant'])
            ->where('tenant_id', $this->selectedTenant);

        if ($this->filterState) {
            $query->where('state', $this->filterState);
        }

        if ($this->filterBatch) {
            $query->where('batch_id', 'like', '%' . $this->filterBatch . '%');
        }

        if ($this->filterProduct) {
            $query->where('product_id', $this->filterProduct);
        }

        $vouchers = $query->orderBy('created_at', 'desc')->paginate(20);

        $batchVouchers = $this->selectedBatch ?
            Voucher::where('batch_id', $this->selectedBatch)->get() : collect();

        return view('livewire.voucher-manager', [
            'vouchers' => $vouchers,
            'products' => Product::where('tenant_id', $this->selectedTenant)
                ->where('service_type', 'hotspot')
                ->where('is_active', true)
                ->get(),
            'batchVouchers' => $batchVouchers
        ]);
        return view('livewire.voucher-manager-component');
    }
}
