@extends('layouts.app')

@section('title', 'Weather in ' . $city)

@section('content')
    <h1>Weather in {{ $city }}</h1>

    <p>Temperature: {{ $temperature }}&deg;C</p>
    <p>Condition: {{ $condition }}</p>

    <p><a href="{{ route('weather.index') }}">&larr; Back</a></p>
@endsection