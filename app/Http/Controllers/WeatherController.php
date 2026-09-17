<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function index(): string
    {
        return "This is a Weather App!";
    }

    public function show(string $city) : string 
    {
        return "Showing Weather for {$city}"; 
    }
}
