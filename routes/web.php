<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/favourites', [FavouriteController::class, 'store'])->name('favourites.store');
    Route::delete('/favourites/{favourite}', [FavouriteController::class, 'destroy'])->name('favourites.destroy');
});

Route::redirect('/', '/weather');

Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');

Route::post('/weather/search', [WeatherController::class, 'search'])->name('weather.search');

Route::get('/weather/{city}', [WeatherController::class, 'show'])->name('weather.show');
