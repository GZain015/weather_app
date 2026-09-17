<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
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
        $location = Http::get('https://geocoding-api.open-meteo.com/v1/search', [
            'name' => $city,
            'count' => 1,
        ])->json('results.0');

        if($location == null){
            abort(404, "We couldn't find a city called \"{$city}\".");
        }

        $current = Http::get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'current' => 'temperature_2m,weather_code,wind_speed_10m',
            'timezone' => 'auto',
        ])->json('current');

        return view('weather.show', [
            'city' => $location['name'],
            'country' => $location['country'],
            'temperature' => $current['temperature_2m'],
            'windSpeed' => $current['wind_speed_10m'],
            'condition' => $this->describeWeatherCode($current['weather_code']),
        ]);
    }

    private function describeWeatherCode(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear sky',
            in_array($code, [1, 2, 3]) => 'Partly cloudy',
            in_array($code, [45, 48]) => 'Foggy',
            in_array($code, [51, 53, 55, 56, 57]) => 'Drizzle',
            in_array($code, [61, 63, 65, 66, 67, 80, 81, 82]) => 'Rain',
            in_array($code, [71, 73, 75, 77, 85, 86]) => 'Snow',
            in_array($code, [95, 96, 99]) => 'Thunderstorm',
            default => 'Unknown',
        };
    }

}
