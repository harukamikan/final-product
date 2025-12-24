@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <h2 class="text-3xl font-bold mb-6 text-amber-100">
            🎁 報酬決定
        </h2>

        <form method="POST" action="{{ route('admin.rewards.toggle') }}" class="mb-6">
            @csrf
            <button
                class="px-4 py-2 rounded-lg text-sm font-semibold
            {{ auth()->user()->company->reward_survey_active
                ? 'bg-red-600 text-white hover:bg-red-700'
                : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                {{ auth()->user()->company->reward_survey_active
            ? 'アンケートを停止する'
            : 'アンケートを開始する' }}
            </button>
        </form>

        @if($company)
        <form method="POST" action="{{ route('admin.rewards.deadline') }}" class="mb-6">
            @csrf

            <div class="flex items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        回答期限
                    </label>
                    <input
                        type="date"
                        name="reward_survey_deadline"
                        value="{{ $company->reward_survey_deadline }}"
                        class="mt-1 rounded-md border-gray-300"
                        required>
                </div>

                <button
                    class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900">
                    期限を設定
                </button>
            </div>
        </form>
        @endif


        <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
            <div class="p-6">

                <h3 class="font-semibold text-lg text-gray-800 mb-4">
                    📋 報酬アンケート結果
                </h3>

                <div class="flex gap-6 mb-6 text-sm">
                    <div class="px-4 py-2 rounded-lg bg-green-50 text-green-700 font-semibold">
                        回答済み：{{ $answeredCount }} 人
                    </div>

                    <div class="px-4 py-2 rounded-lg bg-red-50 text-red-700 font-semibold">
                        未回答：{{ $unansweredCount }} 人
                    </div>
                </div>


                @if($rewardSurveys->isEmpty())
                <p class="text-sm text-gray-500">
                    現在、アンケートの回答はありません。
                </p>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    社員
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    第1希望
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    第2希望
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    第3希望
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($rewardSurveys as $survey)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $survey->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ $survey->first_choice }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $survey->second_choice }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $survey->third_choice ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>

<style>
    .card-shadow {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12) !important;
    }
</style>
@endsection