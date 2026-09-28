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
            <nav class="ml-auto flex items-center gap-4 text-sm">
                @auth
                    <span class="text-slate-600"> {{ auth()->user()->name }} </span>   

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="font-medium text-sky-700 hover:underline">Log out</button>
                    </form>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="font-medium text-sky-700 hover:underline">Log in</a>   
                    <a href="{{ route('register') }}" class="rounded-lg bg-sky-600 px-3 py-1.5 font-medium text-white hover:bg-sky-700">Register</a>   
                @endguest
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-xl px-4 py-10">
        @yield('content')
    </main>
</body>
</html>