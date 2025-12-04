@extends('layouts.app') {{-- レイアウト使ってなければ外してOK --}}

@section('content')
<div class="container">
    <h1 class="mb-4">目標一覧</h1>

    {{-- フラッシュメッセージなどあればここに --}}

    @if ($goals->isEmpty())
        <p>登録されている目標はありません。</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>タイトル</th>
                    <th>状態</th>
                    <th>期限</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($goals as $goal)
                    <tr>
                        <td>{{ $goal->id }}</td>
                        <td>{{ $goal->title }}</td>
                        <td>{{ $goal->status }}</td>
                        <td>
                            {{-- カラム型に合わせてフォーマット調整 --}}
                            {{ \Carbon\Carbon::parse($goal->due_date)->format('Y-m-d') }}
                        </td>
                        <td>{{ $goal->created_at?->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
