<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Validated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeatherController extends Controller
{
    // public function index(): string
    // {
    //     return "This is a Weather App!";
    // }

    // public function show(string $city) : string 
    // {
    //     return "Showing Weather for {$city}";
    // }

    public function index(): View
    {
        return View('weather.index');
    }

    public function search(Request $request) : RedirectResponse
    {
        $validated = $request->validate([
            "city" => ['required', 'string', 'min:2', 'max:60'],
        ]);

        return redirect()->route('weather.show', ['city' => $validated['city']]);
    }

    public function show(string $city) : View 
    { 
        return view('weather.show', [
            'city' => $city,
            'temperature' => 30,
            'condition' => 'Partialy Clouded',
        ]);
    }

}
