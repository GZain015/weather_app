<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Weather App')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-linear-to-b from-sky-100 to-white text-slate-800 antialiased">
    <header class="border-b border-sky-200 bg-white/70 backdrop-blur">
        <div class="mx-auto flex max-w-xl items-center px-4 py-4">
            <a href="{{ route('weather.index') }}" class="flex items-center gap-2 text-lg font-semibold text-sky-700">
                <x-icon name="cloud-sun" class="size-6" />
                Weather App
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-xl px-4 py-10">
        @yield('content')
    </main>
</body>
</html>