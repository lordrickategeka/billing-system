<div class="p-6 bg-base-200 min-h-screen">
    <!-- Create Voucher Modal -->

    <input type="checkbox" id="create-voucher-modal" class="modal-toggle" @if($showGenerateModal) checked @endif />
    <div class="modal" @if($showGenerateModal) style="display:flex;" @endif>
        <div class="modal-box w-full max-w-lg bg-base-100 shadow-lg rounded-xl border border-base-300">
            <h3 class="font-bold text-lg mb-4 text-primary">Create New Voucher</h3>
            <form wire:submit.prevent="generateVouchers" class="space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Product</span>
                    </label>
                    <select wire:model="product_id" class="select select-primary w-full">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Number of Vouchers</span>
                    </label>
                    <input type="number" wire:model="quantity" min="1" class="input input-primary w-full" />
                    @error('quantity') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Value</span>
                    </label>
                    <input type="text" wire:model="value" class="input input-primary w-full" />
                    @error('value') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Max Uses</span>
                    </label>
                    <input type="number" wire:model="max_uses" min="1" class="input input-primary w-full" />
                    @error('max_uses') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Expires At</span>
                    </label>
                    <input type="date" wire:model="expires_at" class="input input-primary w-full" />
                    @error('expires_at') <span class="text-error text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="modal-action flex gap-2 mt-4">
                    <label for="create-voucher-modal" class="btn btn-outline btn-error" wire:click="$set('showGenerateModal', false)">Cancel</label>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
        <label class="modal-backdrop" for="create-voucher-modal" wire:click="$set('showGenerateModal', false)"></label>
    </div>

    <!-- Header and Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="avatar placeholder">
                <div class="bg-primary text-primary-content rounded-full w-10 shadow">
                    <span><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></span>
                </div>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-primary">Voucher List</h2>
                <div class="text-xs text-base-content/60">Auto-updates in 2 min</div>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button wire:click="$set('showGenerateModal', true)" class="btn btn-accent shadow">
                <span class="font-bold">+ Add New Voucher</span>
            </button>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div class="flex gap-2 flex-wrap">
            <select class="select select-secondary select-sm">
                <option>All Products</option>
                <!-- Add dynamic options here -->
            </select>
            <select class="select select-secondary select-sm">
                <option>Status</option>
                <option>Unused</option>
                <option>Used</option>
                <option>Expired</option>
            </select>
            <select class="select select-secondary select-sm">
                <option>Monthly</option>
                <option>Weekly</option>
                <option>Yearly</option>
            </select>
        </div>
        <div class="flex gap-2 flex-wrap items-center">
            <input type="text" class="input input-accent input-sm" placeholder="Search..." />
            <button class="btn btn-outline btn-info btn-sm">Export PDF</button>
            <button class="btn btn-outline btn-success btn-sm">Export Excel</button>
        </div>
    </div>

    <!-- Table Card -->

    <div class="bg-base-100 rounded-xl shadow-lg p-6 border border-base-300 mt-6">
        <div class="overflow-x-auto rounded-t-xl">
            <table class="table table-zebra w-full">
                <thead class="bg-base-200 text-base-content">
                    <tr>
                        <th><input type="checkbox" class="checkbox checkbox-primary checkbox-sm" /></th>
                        <th class="font-semibold">Code</th>
                        <th class="font-semibold">Product</th>
                        <th class="font-semibold">Batch</th>
                        <th class="font-semibold">Value</th>
                        <th class="font-semibold">Max Uses</th>
                        <th class="font-semibold">State</th>
                        <th class="font-semibold">Expires At</th>
                        <th class="font-semibold">Created</th>
                        <th class="text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-accent/10 transition">
                            <td><input type="checkbox" class="checkbox checkbox-primary checkbox-sm" /></td>
                            <td class="whitespace-nowrap">{{ $voucher->code }}</td>
                            <td class="whitespace-nowrap">{{ $voucher->product->name ?? '-' }}</td>
                            <td class="whitespace-nowrap">{{ $voucher->batch_id }}</td>
                            <td class="whitespace-nowrap">{{ $voucher->value }}</td>
                            <td class="whitespace-nowrap">{{ $voucher->max_uses }}</td>
                            <td>
                                <span class="badge px-3 py-1 text-xs border-0 {{
                                    $voucher->state === 'unused' ? 'badge-success' :
                                    ($voucher->state === 'used' ? 'badge-warning' : 'badge-error')
                                }} capitalize shadow-sm">
                                    {{ $voucher->state }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap">{{ $voucher->expires_at ? $voucher->expires_at->format('Y-m-d') : '-' }}</td>
                            <td class="whitespace-nowrap">{{ $voucher->created_at->format('Y-m-d H:i') }}</td>
                            <td class="flex gap-2 justify-center">
                                <button class="btn btn-outline btn-xs btn-info" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6 3 3-6 6H9v-3z" />
                                    </svg>
                                </button>
                                <button class="btn btn-outline btn-xs btn-error" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-base-content/50 py-8">No vouchers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex justify-between items-center">
            <div class="text-xs text-base-content/60">
                Showing {{ $vouchers->firstItem() ?? 0 }} - {{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() ?? 0 }} entries
            </div>
            <div class="join">
                {{ $vouchers->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
