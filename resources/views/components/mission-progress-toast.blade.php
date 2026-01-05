{{--
    ミッション進捗トースト
    
    使用方法:
    @include('components.mission-progress-toast', [
        'progressData' => [
            'mission_title' => 'ミッション名',
            'current' => 1,
            'required' => 3,
        ]
    ])
--}}

@if(isset($progressData))
<div 
    x-data="{ 
        show: true,
        init() {
            setTimeout(() => { this.show = false; }, 3000);
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-x-full"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-full"
    class="fixed top-24 right-6 z-50 max-w-sm w-full"
    style="display: none;"
>
    <div class="bg-white/95 backdrop-blur-sm shadow-lg rounded-2xl p-4 border-l-4 border-indigo-600">
        <div class="flex items-start gap-3">
            <div class="text-2xl flex-shrink-0">📊</div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 text-sm mb-1 truncate">
                    {{ $progressData['mission_title'] ?? 'ミッション' }}
                </p>
                <p class="text-sm text-gray-600">
                    <span class="font-bold text-indigo-600">{{ $progressData['current'] ?? 0 }} / {{ $progressData['required'] ?? 1 }}</span> 達成！
                    @if(($progressData['required'] ?? 1) - ($progressData['current'] ?? 0) > 0)
                        あと<span class="font-bold">{{ ($progressData['required'] ?? 1) - ($progressData['current'] ?? 0) }}</span>回
                    @endif
                </p>
            </div>
            <button 
                @click="show = false"
                class="text-gray-400 hover:text-gray-600 transition flex-shrink-0"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
@endif
