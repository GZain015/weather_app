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

@endsection