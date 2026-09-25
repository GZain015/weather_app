@extends('layouts.app')

@section('title', 'Create an account')


@section('content')
    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Create an account</h1>
    <p class="mt-2 text-slate-600">Save your favourite cities.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <x-form-field name="name" label="Name" autocomplete="name" autofocus />
        <x-form-field name="email" label="Email" type="email" autocomplete="email" />
        <x-form-field name="password" label="Password" type="password" autocomplete="new-password" />
        <x-form-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" />

        <button type="submit" class="w-full rounded-xl bg-sky-600 px-5 py-3 font-semibold text-white shadow-sm outline-none hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
            Create account
        </button>
    </form>
@endsection