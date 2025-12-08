{{-- resources/views/missions/blog_url.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>技術ブログURLの登録（テスト）</title>
</head>
<body>
    <h1>技術ブログURLの登録（テスト）</h1>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('missions.blog-url.submit') }}">
        @csrf
        <div>
            <label>技術系ブログのURL（Qiita / Zenn など）</label><br>
            <input type="url" name="url" value="{{ old('url') }}" style="width: 400px;">
            @error('url')
                <p style="color:red;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">URLを送信してミッション達成</button>
    </form>
</body>
</html>
