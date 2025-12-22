@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center px-6">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">

        <h2 class="text-lg font-semibold text-red-600 mb-2">
            アカウント削除の確認
        </h2>

        <p class="text-sm text-gray-600 mb-6">
            アカウントを削除するには、セキュリティのため
            現在のパスワードを入力してください。
            この操作は取り消せません。
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 p-3 text-red-700 text-sm">
                パスワードが正しくありません。
            </div>
        @endif

        <form method="POST" action="{{ route('profile.delete') }}">
            @csrf
            @method('DELETE')

            <div class="mb-4">
                <label class="block text-sm text-gray-600 mb-1">
                    現在のパスワード
                </label>

                <input
                    type="password"
                    name="password"
                    class="w-full border rounded-md px-3 py-2
                        @error('password') border-red-500 @else border-gray-300 @enderror"
                    required
                    autofocus
                >
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('profile.edit') }}"
                   class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                    キャンセル
                </a>

                <button
                    type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    削除する
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
