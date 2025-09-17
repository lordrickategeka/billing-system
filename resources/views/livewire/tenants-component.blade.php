<div>
    <div class="p-6">
        <h2 class="text-xl font-bold mb-4">Tenants</h2>

        @if (session()->has('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="{{ $isEdit ? 'update' : 'save' }}" class="space-y-4 mb-6">
            <div>
                <label class="block font-medium">Tenant Name</label>
                <input type="text" wire:model="name" class="w-full border rounded p-2">
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ $isEdit ? 'Update' : 'Save' }}
            </button>

            @if ($isEdit)
                <button type="button" wire:click="resetForm"
                    class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
            @endif
        </form>

        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">Name</th>
                    <th class="border px-4 py-2">Customers</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tenants as $tenant)
                    <tr>
                        <td class="border px-4 py-2">{{ $tenant->name }}</td>
                        <td class="border px-4 py-2">{{ $tenant->customers_count }}</td>
                        <td class="border px-4 py-2">
                            <button wire:click="edit({{ $tenant->id }})"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</button>
                            <button wire:click="delete({{ $tenant->id }})"
                                class="bg-red-600 text-white px-2 py-1 rounded">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
