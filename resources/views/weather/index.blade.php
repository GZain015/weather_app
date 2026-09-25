@extends('layouts.app')

@section('title', 'Weather App')

@section('content')
    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Check the Weather</h1>
    <p class="mt-2 text-slate-600">Search any city in the world.</p>

    <form method="POST" action="{{ route('weather.search') }}" class="mt-6">
        @csrf

        <label for="city" class="sr-only">City</label>

        <div class="flex gap-2">
            <div class="relative grow">
                <x-icon name='map-pin' class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-slate-400" />
                
                <input 
                    type="text" 
                    id="city" 
                    name="city" 
                    value="{{ old('city') }}" 
                    placeholder="e.g. Lahore"
                    autofocus
                    @class([
                        'w-full rounded-xl border bg-white py-3 pr-4 pl-10 shadow-sm outline-none focus:ring-4',
                        'border-slate-300 focus:border-sky-500 focus:ring-sky-100' => ! $errors->has('city'),
                        'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has('city'),
                    ])
                >
            </div>

            <button type="submit" class="flex items-center gap-2 rounded-xl bg-sky-600 px-5 font-semibold text-white shadow-sm outline-none hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
                <x-icon name="search" class="size-5" />
                Search
            </button>
        </div>
        
        @error('city')
            <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                <x-icon name="alert-circle" class="size-4" />
                {{ $message }}
            </p>
        @enderror

    </form>

    @if ($recentSearches->isNotEmpty())
        <section class="mt-10">
            <h2 class="flex items-center gap-2 text-sm font-semibold tracking-wide text-slate-500 uppercase">
                <x-icon name='history' class="size-4"/>
                Recent Searches
            </h2>

            <ul class="mt-3 divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                @foreach ($recentSearches as $search)
                    <li>
                        <a href="{{ route('weather.show', ['city' => $search->city]) }}" class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-sky-50" >
                            <div class="flex min-w-0 items-center gap-3">
                                <x-weather-icon :condition="$search->condition" class="size-8 shrink-0" />

                                <div class="min-w-0">
                                    <p class="truncate font-medium text-slate-900">{{ $search->city}}, {{ $search->country}}</p>
                                    <p class="text-sm text-slate-500">{{ $search->condition }} · {{ $search->updated_at->diffForHumans() }}</p>
                                </div>
                                </div>
                            <span class="shrink-0 text-lg font-semibold text-sky-700">{{ $search->temperature }}&deg;C</span>
                        </a>
                        
                    </li>
                @endforeach
            </ul>
        </section>

    @endif

@endsection