{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal App</title>

    {{-- Vite（Tailwind + Alpine + JS） --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body class="bg-gray-100">

    <div class="min-h-screen">
        @yield('content')
    </div>

</body>
</html>