@extends('layouts.app')

@section('title', 'Weather App')

@section('content')
    <h1>Weather App</h1>

    <form method="POST" action="{{ route('weather.search') }}">
        @csrf

        <label for="city">City</label>
        <input type="text" id="city" name="city" value="{{ old('city') }}" placeholder="e.g. Lahore">

        @error('city')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <button type="submit">Get weather</button>
    </form>

    @if ($recentSearches->isNotEmpty())
        <h2> Recent Searches</h2>

        <ul>
            @foreach ($recentSearches as $search)
                <li>
                    <a href="{{ route('weather.show', ['city' => $search->city]) }}">
                        {{ $search->city}}, {{ $search->country}}
                    </a>
                    - {{ $search->temperature }}&deg;C, {{ $search->condition }}
                    <small>({{ $search->updated_at->diffForHumans() }})</small>
                </li>
            @endforeach
        </ul>

    @endif

@endsection