@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <h2 class="text-3xl font-bold mb-6 text-amber-100">
            🎁 報酬決定
        </h2>

        <div class="bg-white overflow-hidden shadow-2xl card-shadow sm:rounded-lg">
            <div class="p-6">

                <h3 class="font-semibold text-lg text-gray-800 mb-4">
                    📋 報酬アンケート結果
                </h3>

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
