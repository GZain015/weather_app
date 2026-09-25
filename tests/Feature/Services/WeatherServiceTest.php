<?php

use App\Services\WeatherService;
use Illuminate\Http\Client\Request;

// test('example', function () {
//     $response = $this->get('/');

//     $response->assertStatus(200);
// });



function fakeForecastWithCode(int $weatherCode): void
{
    Http::preventStrayRequests();

    Http::fake([
        'geocoding-api.open-meteo.com/v1/search*' => Http::response([
            'results' => [
                ['name' => 'Lahore', 'country' => 'Pakistan', 'latitude' => 31.55, 'longitude' => 74.34],
            ],
        ]),
        'api.open-meteo.com/v1/forecast*' => Http::response([
            'current' => ['temperature_2m' => 31.4, 'weather_code' => $weatherCode, 'wind_speed_10m' => 9.2],
        ]),
    ]);
}


it('returns the current weather for a city', function () {
    fakeForecastWithCode(0);

    $weather = app(WeatherService::class)->forCity('lahore');

    expect($weather)->toBe([
        'city' => 'Lahore',
        'country' => 'Pakistan',
        'temperature' => 31.4,
        'windSpeed' => 9.2,
        'condition' => 'Clear sky',
    ]);
});

it('looks up the forecast using the coordinates of the found city', function () {
    fakeForecastWithCode(0);

    app(WeatherService::class)->forCity('lahore');

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/forecast')
        && $request['latitude'] == 31.55
        && $request['longitude'] == 74.34);
});

it('describes each weather code in plain words', function (int $weatherCode, string $condition) {
    fakeForecastWithCode($weatherCode);

    $weather = app(WeatherService::class)->forCity('Lahore');

    expect($weather['condition'])->toBe($condition);
})->with([
    [0, 'Clear sky'],
    [1, 'Partly cloudy'],
    [3, 'Partly cloudy'],
    [45, 'Foggy'],
    [55, 'Drizzle'],
    [61, 'Rain'],
    [82, 'Rain'],
    [71, 'Snow'],
    [86, 'Snow'],
    [95, 'Thunderstorm'],
    [99, 'Thunderstorm'],
    [4, 'Unknown'],
]);