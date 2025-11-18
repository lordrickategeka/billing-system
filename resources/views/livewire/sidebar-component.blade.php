<nav class="space-y-2">
    @foreach ($menu as $item)
        @if ($item['type'] === 'link')
            <a href="{{ route($item['route']) }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg {{ $item['active'] ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50 hover:text-orange-600' }} font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @elseif ($item['type'] === 'dropdown')
            <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }">
                <button @click="open = !open" class="flex items-center justify-between w-full px-3 py-2 rounded-lg {{ $item['active'] ? 'text-orange-600 bg-orange-50' : 'text-gray-700 hover:bg-gray-50' }} font-medium group">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <div x-show="open" x-transition class="ml-8 mt-2 space-y-1">
                    @foreach ($item['items'] as $sub)
                        <a href="{{ route($sub['route']) }}" class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm {{ $sub['active'] ? 'text-orange-600 bg-orange-50' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-600' }}">
                            <span>{{ $sub['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</nav>
