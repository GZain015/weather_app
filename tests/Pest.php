<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->use(LazilyRefreshDatabase:: class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function fakeOpenMeteo(): void
{
    Http::preventStrayRequests();

    Http::fake([
        'geocoding-api.open-meteo.com/v1/search*' => Http::response([
            'results' => [
                ['name' => 'Lahore', 'country' => 'Pakistan', 'latitude' => 31.55, 'longitude' => 74.34],
            ],
        ]),
        'api.open-meteo.com/v1/forecast*' => Http::response([
            'current' => ['temperature_2m' => 31.4, 'weather_code' => 0, 'wind_speed_10m' => 9.2],
        ]),
    ]);
}
