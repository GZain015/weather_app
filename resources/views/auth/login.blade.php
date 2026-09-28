@extends('layouts.app')

@section('title', 'Login to your account')


@section('content')
    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Login to your account</h1>
    {{-- <p class="mt-2 text-slate-600">Save your favourite cities.</p> --}}

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <x-form-field name="email" label="Email" type="email" autocomplete="email" autofocus/>
        <x-form-field name="password" label="Password" type="password" autocomplete="current-password" />
        
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="remember" class="rounded border-slate-300">
            Remember me
        </label>

        <button type="submit" class="w-full rounded-xl bg-sky-600 px-5 py-3 font-semibold text-white shadow-sm outline-none hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
            Log IN
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-slate-600">
        No account? <a href="{{ route('register') }}" class="font-medium text-sky-700 hover:underline">Register</a>
    </p>
@endsection