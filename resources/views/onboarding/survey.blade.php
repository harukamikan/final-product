@extends('layouts.app')

@section('content')
{{-- 動的グラデーション背景 --}}
<div class="fixed inset-0 -z-10 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 animate-gradient-shift"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(255,255,255,0.1),transparent_50%)]"></div>
</div>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-10" 
     x-data="{
        isSubmitting: false,
        sliderValue: {{ old('current_situation') === 'busy' ? 0 : (old('current_situation') === 'new' ? 2 : 1) }},
        busynessLevel: '{{ old('current_situation', 'normal') }}'
     }" 
     x-init="$watch('sliderValue', value => {
        if (value <= 0.66) {
            busynessLevel = 'busy';
        } else if (value <= 1.33) {
            busynessLevel = 'normal';
        } else {
            busynessLevel = 'new';
        }
     })">
    <div class="w-full max-w-3xl">
        
        {{-- Progress Header --}}
        <div class="text-center mb-8" x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)" 
             x-show="show" x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/20 backdrop-blur-md text-white text-sm font-semibold mb-6 shadow-lg border border-white/30">
                <span class="text-xl animate-bounce">🎯</span>
                <span>オンボーディング</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-3 drop-shadow-lg">
                あなたに合った<br class="md:hidden">ミッションを用意します
            </h1>
            <p class="text-white/90 text-lg drop-shadow">
                簡単な質問に答えてください
            </p>
        </div>

        {{-- Survey Form --}}
        <div class="rounded-3xl bg-white/10 backdrop-blur-xl shadow-2xl p-6 md:p-10 space-y-8 border border-white/20"
             x-data="{ show: false }" x-init="setTimeout(() => show = true, 300)" 
             x-show="show" x-transition:enter="transition ease-out duration-700 delay-200"
             x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            
            @if ($errors->any())
                <div class="rounded-2xl border border-red-300 bg-red-50/90 backdrop-blur-sm px-5 py-4 text-red-800 text-sm space-y-1 shadow-lg">
                    @foreach ($errors->all() as $error)
                        <p>・{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('onboarding.submit') }}" class="space-y-10"
                  @submit="isSubmitting = true">
                @csrf

                {{-- Q1: Engineer Job Type --}}
                <div class="space-y-5" x-data="{ show: false }" x-init="setTimeout(() => show = true, 400)" 
                     x-show="show" x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-sm font-bold shadow-lg">1</span>
                        <label class="text-xl font-bold text-white drop-shadow">
                            普段どのような業務を行っていますか？
                        </label>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="role" value="dev" {{ old('role') === 'dev' ? 'checked' : '' }} 
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">💻</span>
                                <span class="text-white font-semibold drop-shadow">開発系</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="role" value="infra" {{ old('role') === 'infra' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">⚙️</span>
                                <span class="text-white font-semibold drop-shadow">インフラ系</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="role" value="mgmt" {{ old('role') === 'mgmt' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">📊</span>
                                <span class="text-white font-semibold drop-shadow">マネジメント系</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="role" value="all" {{ old('role') === 'all' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">🌟</span>
                                <span class="text-white font-semibold drop-shadow">全て行っている</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                    </div>
                </div>

                {{-- Q2: Preferred Output --}}
                <div class="space-y-5" x-data="{ show: false }" x-init="setTimeout(() => show = true, 500)" 
                     x-show="show" x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 text-white text-sm font-bold shadow-lg">2</span>
                        <label class="text-xl font-bold text-white drop-shadow">
                            直近で一番やりたいアウトプットは？
                        </label>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="preferred_output" value="blog" {{ old('preferred_output') === 'blog' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">📝</span>
                                <span class="text-white font-semibold drop-shadow">技術ブログ（Qiita）</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="preferred_output" value="event" {{ old('preferred_output') === 'event' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">🎪</span>
                                <span class="text-white font-semibold drop-shadow">イベント企画・開催</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="preferred_output" value="speaker" {{ old('preferred_output') === 'speaker' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">🎤</span>
                                <span class="text-white font-semibold drop-shadow">イベント登壇</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="preferred_output" value="cert" {{ old('preferred_output') === 'cert' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="flex items-center gap-2">
                                <span class="text-2xl drop-shadow">🏆</span>
                                <span class="text-white font-semibold drop-shadow">資格取得</span>
                            </span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                    </div>
                </div>

                {{-- Q3: Current Situation (Slider UI) --}}
                <div class="space-y-5" x-data="{ show: false }" x-init="setTimeout(() => show = true, 600)" 
                     x-show="show" x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-rose-600 text-white text-sm font-bold shadow-lg">3</span>
                        <label class="text-xl font-bold text-white drop-shadow">
                            現在の状況は？
                        </label>
                    </div>
                    
                    <div class="p-6 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm space-y-4">
                        {{-- ラベル表示 --}}
                        <div class="flex justify-between items-center text-white font-semibold drop-shadow">
                            <span class="text-sm">忙しい</span>
                            <span class="text-sm">普通</span>
                            <span class="text-sm">暇</span>
                        </div>
                        
                        {{-- スライダー --}}
                        <div class="relative">
                            <input 
                                type="range" 
                                min="0" 
                                max="2" 
                                step="0.01"
                                value="{{ old('current_situation') === 'busy' ? 0 : (old('current_situation') === 'new' ? 2 : 1) }}"
                                x-model.number="sliderValue"
                                class="w-full h-3 bg-white/20 rounded-full appearance-none cursor-pointer slider-gradient"
                            >
                            <input type="hidden" name="current_situation" :value="busynessLevel">
                        </div>
                        
                        {{-- 現在選択中の値を表示 --}}
                        <div class="text-center">
                            <span class="inline-block px-4 py-2 rounded-full bg-white/20 text-white font-semibold text-sm">
                                <span x-show="busynessLevel == 'busy'">😰 忙しい</span>
                                <span x-show="busynessLevel == 'normal'">😊 普通</span>
                                <span x-show="busynessLevel == 'new'">😌 暇</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Q4: Skill Level --}}
                <div class="space-y-5" x-data="{ show: false }" x-init="setTimeout(() => show = true, 700)" 
                     x-show="show" x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-rose-500 to-orange-600 text-white text-sm font-bold shadow-lg">4</span>
                        <label class="text-xl font-bold text-white drop-shadow">
                            あなたのスキルレベルは？
                        </label>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="experience_level" value="junior" {{ old('experience_level') === 'junior' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="text-white font-semibold drop-shadow">🌱 初心者</span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="experience_level" value="mid" {{ old('experience_level') === 'mid' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="text-white font-semibold drop-shadow">🚀 中級者</span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                        
                        <label class="group relative flex items-center gap-3 p-5 rounded-2xl border-2 border-white/30 bg-white/10 backdrop-blur-sm cursor-pointer hover:border-white/60 hover:bg-white/20 transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                            <input type="radio" name="experience_level" value="senior" {{ old('experience_level') === 'senior' ? 'checked' : '' }}
                                   class="w-5 h-5 text-purple-600 focus:ring-purple-500 focus:ring-2">
                            <span class="text-white font-semibold drop-shadow">⭐ 上級者</span>
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/0 to-white/0 group-hover:from-white/5 group-hover:to-white/10 transition-all duration-300"></div>
                        </label>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-6" x-data="{ show: false }" x-init="setTimeout(() => show = true, 800)" 
                     x-show="show" x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="group relative w-full rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white py-5 font-bold text-xl hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 transition-all duration-300 shadow-2xl hover:shadow-pink-500/50 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden"
                    >
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            <span x-show="!isSubmitting">ミッションを受け取る 🚀</span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                処理中...
                            </span>
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>

{{-- カスタムアニメーション --}}
<style>
    @keyframes gradient-shift {
        0%, 100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }
    
    .animate-gradient-shift {
        background-size: 200% 200%;
        animation: gradient-shift 15s ease infinite;
    }
    
    /* チェックされた選択肢のスタイル */
    input[type="radio"]:checked + span {
        font-weight: 700;
    }
    
    input[type="radio"]:checked ~ div {
        background: linear-gradient(to bottom right, rgba(255,255,255,0.15), rgba(255,255,255,0.25)) !important;
    }
    
    label:has(input[type="radio"]:checked) {
        border-color: rgba(255,255,255,0.8) !important;
        background-color: rgba(255,255,255,0.25) !important;
        transform: scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    
    /* スライダーのカスタムスタイル */
    input[type="range"].slider-gradient {
        -webkit-appearance: none;
        appearance: none;
    }
    
    input[type="range"].slider-gradient::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(to bottom right, #ec4899, #f472b6);
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.5);
        transition: all 0.2s ease;
    }
    
    input[type="range"].slider-gradient::-webkit-slider-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 6px 16px rgba(236, 72, 153, 0.7);
    }
    
    input[type="range"].slider-gradient::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(to bottom right, #ec4899, #f472b6);
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.5);
        border: none;
        transition: all 0.2s ease;
    }
    
    input[type="range"].slider-gradient::-moz-range-thumb:hover {
        transform: scale(1.2);
        box-shadow: 0 6px 16px rgba(236, 72, 153, 0.7);
    }
    
    input[type="range"].slider-gradient::-webkit-slider-runnable-track {
        background: linear-gradient(to right, #fbbf24, #a78bfa, #60a5fa);
        height: 12px;
        border-radius: 6px;
    }
    
    input[type="range"].slider-gradient::-moz-range-track {
        background: linear-gradient(to right, #fbbf24, #a78bfa, #60a5fa);
        height: 12px;
        border-radius: 6px;
    }
</style>
@endsection
