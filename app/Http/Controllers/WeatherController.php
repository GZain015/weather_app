<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class WeatherController extends Controller
{
    public function index(): string
    {
        return "This is a Weather App!";
    }

    // public function show(string $city) : string 
    // {
    //     return "Showing Weather for {$city}";
    // }

    public function show(string $city) : View 
    { 
        return view('weather.show', [
            'City' => $city,
            'Temperature' => 30,
            'Condiditon' => 'Partialy Clouded',
        ]);
    }
}
