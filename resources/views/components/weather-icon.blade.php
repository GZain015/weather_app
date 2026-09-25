
@props(['condition'])

@php
    [$icon, $color] = match ($condition) {
        'Clear sky' => ['sun', 'text-amber-500'],
        'Partly cloudy' => ['cloud-sun', 'text-amber-400'],
        'Foggy' => ['cloud-fog', 'text-slate-400'],
        'Drizzle' => ['cloud-drizzle', 'text-sky-400'],
        'Rain' => ['cloud-rain', 'text-sky-600'],
        'Snow' => ['cloud-snow', 'text-sky-300'],
        'Thunderstorm' => ['cloud-lightning', 'text-violet-500'],
        default => ['cloud', 'text-slate-400'],
    };
@endphp


<x-icon :name="$icon" {{ $attributes->class($color) }} />