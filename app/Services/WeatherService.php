<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException as ClientConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WeatherService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @return array{city: string, country: string, temperature: float, windSpeed: float, condition: string}|null
    */
    public function forCity(string $city) : ?array
    {
        $cacheKey = 'weather:'.Str::slug($city);

        return Cache::remember($cacheKey, now()->addMinutes(15), fn (): ?array => $this->fetch($city));
    }
    
    public function fetch(string $city) : ?array
    {
        $location = $this->findlocation($city);

        if ($location === null) {
            return null;
        }

        $current = $this->currentConditions($location['latitude'], $location['longitude']);

        if ($current === null) {
            return null;
        }

        return [
            'city' => $location['name'],
            'country' => $location['country'],
            'temperature' => $current['temperature_2m'],
            'windSpeed' => $current['wind_speed_10m'],
            'condition' => $this->describeWeatherCode($current['weather_code']),
        ];
    }

    private function findLocation(string $city): ?array
    {
        return $this->request(config('services.open_meteo.geocoding_url').'/search', [
            'name' => $city,
            'count' => 1,
        ])?->json('results.0');
    }

    private function currentConditions(float $latitude, float $longitude): ?array
    {
        return $this->request(config('services.open_meteo.forecast_url').'/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,weather_code,wind_speed_10m',
                'timezone' => 'auto',
            ])?->json('current');
    }

    /**
     *  @ param  array<string, mixed>  $query
    */
    private function request(string $url, array $query): ?Response
    {
        try {
            $response = Http::timeout(config('services.open_meteo.timeout'))->get($url, $query);
        } catch (ClientConnectionException $e){
            Log::warning('Weather API unreachable', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Weather API returned an error', ['url' => $url, 'status' => $response->status()]);

            return null;
        }

        return $response;
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
