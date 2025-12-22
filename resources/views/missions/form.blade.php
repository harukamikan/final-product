@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F6F7FB] py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('missions.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
                <span class="text-lg">←</span>
                ミッション一覧へ戻る
            </a>

            <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">
                {{ $mission->key }}
            </span>
        </div>

        <div class="rounded-2xl bg-gradient-to-r from-[#4F47E6] via-[#6F69EA] to-[#918CF0] p-6 sm:p-8 shadow-md">
            <p class="text-white/90 text-sm font-medium">ミッションフォーム</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-white">
                {{ $mission->title }}
            </h1>
            <p class="mt-2 text-sm text-white/80">
                送信するとミッション達成として記録されます。証跡リンクがあれば一緒に貼ってください。
            </p>
        </div>

        <div class="mt-6 bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
            <div class="p-6 sm:p-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                        <div class="font-semibold text-red-800 mb-2">入力エラーがあります</div>
                        <ul class="list-disc pl-5 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('missions.form.store', $mission) }}" class="space-y-6">
                    @csrf

                    @php
                        $titlePlaceholder = match($mission->key) {
                            'event_speaker' => '例：◯◯勉強会で「◯◯」について登壇',
                            'event_host' => '例：社内LT会（12月）を企画・開催',
                            'certification' => '例：AWS SAA 合格',
                            default => '例：実施した内容のタイトル',
                        };

                        $detailsHint = match($mission->key) {
                            'event_speaker' => '例：イベント名 / 対象者 / 発表内容の概要 / 学び',
                            'event_host' => '例：目的 / 参加人数 / 工夫した点 / 次回の改善',
                            'certification' => '例：スコア・等級 / 学習時間 / 使った教材 / 学び',
                            default => '補足情報があれば書いてください。',
                        };
                    @endphp

                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-800">
                            タイトル <span class="text-red-500">*</span>
                        </label>
                        <input id="title" name="title" type="text" required
                               value="{{ old('title') }}"
                               placeholder="{{ $titlePlaceholder }}"
                               class="mt-2 block w-full rounded-xl border-gray-200 bg-[#F6F7FB]
                                      shadow-sm focus:border-[#4F47E6] focus:ring-[#4F47E6]" />
                    </div>

                    <div>
                        <label for="occurred_on" class="block text-sm font-semibold text-gray-800">
                            実施日 / 取得日 <span class="text-red-500">*</span>
                        </label>
                        <input id="occurred_on" name="occurred_on" type="date" required
                               value="{{ old('occurred_on') ?? now()->toDateString() }}"
                               class="mt-2 block w-full rounded-xl border-gray-200 bg-[#F6F7FB]
                                      shadow-sm focus:border-[#4F47E6] focus:ring-[#4F47E6]" />
                    </div>

                    <div>
                        <label for="details" class="block text-sm font-semibold text-gray-800">
                            詳細（任意）
                        </label>
                        <textarea id="details" name="details" rows="5"
                                  placeholder="{{ $detailsHint }}"
                                  class="mt-2 block w-full rounded-xl border-gray-200 bg-[#F6F7FB]
                                         shadow-sm focus:border-[#4F47E6] focus:ring-[#4F47E6]">{{ old('details') }}</textarea>
                        <p class="mt-2 text-xs text-gray-500">ここに書いた内容は社内の成果・ナレッジとして活用されます。</p>
                    </div>

                    <div>
                        <label for="evidence_url" class="block text-sm font-semibold text-gray-800">
                            URL
                        </label>
                        <input id="evidence_url" name="evidence_url" type="url"
                               value="{{ old('evidence_url') }}"
                               placeholder="例：https://connpass.com/... / https://speakerdeck.com/... / 合格証の共有リンクなど"
                               class="mt-2 block w-full rounded-xl border-gray-200 bg-[#F6F7FB]
                                      shadow-sm focus:border-[#4F47E6] focus:ring-[#4F47E6]" />
                        <p class="mt-2 text-xs text-gray-500">登壇資料、イベントページ、合格証の共有URLなど（あれば）</p>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <a href="{{ route('missions.index') }}"
                           class="inline-flex justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            キャンセル
                        </a>

                        <button type="submit"
                                class="inline-flex justify-center rounded-xl bg-[#4F47E6] px-5 py-2.5 text-sm font-semibold text-white
                                       hover:bg-[#3F38D8] focus:outline-none focus:ring-2 focus:ring-[#4F47E6] focus:ring-offset-2">
                            送信して達成する
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-[#F6F7FB] px-6 sm:px-8 py-4 border-t border-gray-200">
                <p class="text-xs text-gray-500">※ 送信内容は保存され、後から確認できるようになります</p>
            </div>
        </div>

    </div>
</div>
@endsection
