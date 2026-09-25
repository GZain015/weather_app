@extends('layouts.app')

@section('title', 'Weather in ' . $city)

@section('content')
    <a href="{{ route('weather.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-sky-700 hover:text-sky-900">
        <x-icon name="arrow-left" class="size-4" />
        Search another city
    </a>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 p-6">
            <div class="min-w-0">
                <h1 class="flex items-center gap-1 truncate text-2xl font-bold text-slate-900">
                    <x-icon name="map-pin" class="size-5 shrink-0 text-sky-600"/>
                    {{ $city }}
                </h1>
                <p class="text-slate-500">{{ $country}}</p>
                <p class="mt-4 text-6xl font-bold tracking-tight text-slate-900">
                    {{ round($temperature) }}&deg;<span class="text-3xl text-slate-400">C</span>
                </p>
                <p class="mt-1 font-medium text-slate-600">{{ $condition }}</p>
            </div>

            <x-weather-icon :condition="$condition" class="size-24 shrink-0"/>
        </div>

        <dl class="grid grid-cols-2 divide-x divide-slate-100 border-t border-slate-100 bg-slate-50">
            <div class="flex items-center gap-3 p-4">
                    <x-icon name="thermometer" class="size-5 text-slate-400" />
                    <div>
                        <dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Exact</dt>
                        <dd class="font-semibold tabular-nums">{{ $temperature }}&deg;C</dd>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4">
                    <x-icon name="wind" class="size-5 text-slate-400" />
                    <div>
                        <dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Wind</dt>
                        <dd class="font-semibold tabular-nums">{{ $windSpeed }} km/h</dd>
                    </div>
                </div>
        </dl>
    </div>

@endsection