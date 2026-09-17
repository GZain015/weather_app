@extends('layouts.app')

@section('title', 'Weather in ' . $city)

@section('content')
    <h1>{{ $city }} , {{ $country }}</h1>

    <p>Temperature: {{ $temperature }}&deg;C</p>
    <p>Condition: {{ $condition }}</p>
    <p>Wind: {{ $windSpeed }} km/h</p>

    <p><a href="{{ route('weather.index') }}">&larr; Search another city</a></p>
@endsection