<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

Route::get('/', function () {
    return view('index');
});

Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

Route::post('/weather/search', [WeatherController::class, 'search'])->name('weather.search');

Route::get('/weather/{city}', [WeatherController::class, 'show'])->name('weather.show');