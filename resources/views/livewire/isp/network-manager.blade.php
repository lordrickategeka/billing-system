<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Network Devices</h1>
                    <p class="text-gray-600">Manage NAS devices and RADIUS connectivity</p>
                </div>
                <button wire:click="$set('showCreateModal', true)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                    Add Device
                </button>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input wire:model.live="search" type="text" placeholder="Search name, NAS IP, or site..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <select wire:model.live="filterType"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Types</option>
                        @foreach ($types as $deviceType)
                            <option value="{{ $deviceType }}">{{ ucfirst(str_replace('_', ' ', $deviceType)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterVendor"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Vendors</option>
                        @foreach ($vendors as $deviceVendor)
                            <option value="{{ $deviceVendor }}">{{ ucfirst($deviceVendor) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="selectedTenant"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NAS IP
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type / Vendor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($devices as $device)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $device->name }}</div>
                                <div class="text-xs text-gray-500">Last seen:
                                    {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $device->nas_ip_address }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $device->type)) }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst($device->vendor) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $device->site_name }}</div>
                                <div class="text-xs text-gray-500">{{ data_get($device->location, 'address', 'N/A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($device->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="editDevice({{ $device->id }})"
                                        class="text-blue-600 hover:text-blue-900">Edit</button>
                                    <button wire:click="testDevice({{ $device->id }})"
                                        class="text-indigo-600 hover:text-indigo-900">Test</button>
                                    <button wire:click="toggleDevice({{ $device->id }})"
                                        class="{{ $device->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}">
                                        {{ $device->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                No network devices found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-6 py-3 border-t border-gray-200">
                {{ $devices->links() }}
            </div>
        </div>

        @if ($showCreateModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Network Device</h3>

                        <form wire:submit.prevent="createDevice" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input wire:model="name" type="text" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NAS IP Address</label>
                                    <input wire:model="nas_ip_address" type="text" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('nas_ip_address')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select wire:model="type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach ($types as $deviceType)
                                            <option value="{{ $deviceType }}">{{ ucfirst(str_replace('_', ' ', $deviceType)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vendor</label>
                                    <select wire:model="vendor" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach ($vendors as $deviceVendor)
                                            <option value="{{ $deviceVendor }}">{{ ucfirst($deviceVendor) }}</option>
                                        @endforeach
                                    </select>
                                    @error('vendor')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Site Name</label>
                                    <input wire:model="site_name" type="text" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('site_name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shared Secret</label>
                                <input wire:model="secret" type="password" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('secret')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-md font-medium text-gray-900">Location</h4>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input wire:model="location.address" type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('location.address')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Latitude</label>
                                        <input wire:model="location.lat" type="number" step="any"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('location.lat')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Longitude</label>
                                        <input wire:model="location.lng" type="number" step="any"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('location.lng')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-md font-medium text-gray-900">Management</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SSH Host</label>
                                        <input wire:model="management.ssh_host" type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('management.ssh_host')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SSH Port</label>
                                        <input wire:model="management.ssh_port" type="number" min="1" max="65535"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('management.ssh_port')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SSH Username</label>
                                        <input wire:model="management.ssh_username" type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SNMP Community</label>
                                        <input wire:model="management.snmp_community" type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('management.snmp_community')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">API Endpoint</label>
                                    <input wire:model="management.api_endpoint" type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('management.api_endpoint')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-md font-medium text-gray-900">Capabilities</h4>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.coa"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>CoA</span></label>
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.dm"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>DM</span></label>
                                    <label class="inline-flex items-center space-x-2"><input
                                            wire:model="capabilities.accounting" type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>Accounting</span></label>
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.ipoe"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>IPoE</span></label>
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.pppoe"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>PPPoE</span></label>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" wire:click="$set('showCreateModal', false)"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Create Device
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if ($showEditModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Network Device</h3>

                        <form wire:submit.prevent="updateDevice" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input wire:model="name" type="text" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NAS IP Address</label>
                                    <input wire:model="nas_ip_address" type="text" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('nas_ip_address')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select wire:model="type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach ($types as $deviceType)
                                            <option value="{{ $deviceType }}">{{ ucfirst(str_replace('_', ' ', $deviceType)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vendor</label>
                                    <select wire:model="vendor" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach ($vendors as $deviceVendor)
                                            <option value="{{ $deviceVendor }}">{{ ucfirst($deviceVendor) }}</option>
                                        @endforeach
                                    </select>
                                    @error('vendor')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Site Name</label>
                                    <input wire:model="site_name" type="text" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('site_name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shared Secret</label>
                                <input wire:model="secret" type="password" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('secret')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-md font-medium text-gray-900">Location</h4>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input wire:model="location.address" type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('location.address')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Latitude</label>
                                        <input wire:model="location.lat" type="number" step="any"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('location.lat')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Longitude</label>
                                        <input wire:model="location.lng" type="number" step="any"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('location.lng')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-md font-medium text-gray-900">Management</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SSH Host</label>
                                        <input wire:model="management.ssh_host" type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('management.ssh_host')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SSH Port</label>
                                        <input wire:model="management.ssh_port" type="number" min="1" max="65535"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('management.ssh_port')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SSH Username</label>
                                        <input wire:model="management.ssh_username" type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">SNMP Community</label>
                                        <input wire:model="management.snmp_community" type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('management.snmp_community')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">API Endpoint</label>
                                    <input wire:model="management.api_endpoint" type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('management.api_endpoint')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-md font-medium text-gray-900">Capabilities</h4>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.coa"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>CoA</span></label>
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.dm"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>DM</span></label>
                                    <label class="inline-flex items-center space-x-2"><input
                                            wire:model="capabilities.accounting" type="checkbox"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>Accounting</span></label>
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.ipoe"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>IPoE</span></label>
                                    <label class="inline-flex items-center space-x-2"><input wire:model="capabilities.pppoe"
                                            type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"><span>PPPoE</span></label>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" wire:click="$set('showEditModal', false)"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Update Device
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('message'))
            <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>
