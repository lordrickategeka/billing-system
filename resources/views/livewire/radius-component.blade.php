<div>
    <div class="flex items-center space-x-3">
    <div class="flex items-center space-x-2">
        <div class="w-3 h-3 rounded-full {{ $isConnected ? 'bg-green-500' : 'bg-red-500' }}"></div>
        <span class="text-sm font-medium {{ $isConnected ? 'text-green-700' : 'text-red-700' }}">
            RADIUS {{ $isConnected ? 'Connected' : 'Disconnected' }}
        </span>
    </div>

    @if($isConnected)
    <div class="text-xs text-gray-500">
        {{ $stats['active_sessions'] }} active sessions
    </div>
    @endif

    <button wire:click="checkConnection" class="text-xs text-gray-400 hover:text-gray-600">
        Refresh
    </button>
</div>
</div>
