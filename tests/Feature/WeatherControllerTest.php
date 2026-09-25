<?php

use App\Models\Search;
use Illuminate\Support\Facades\Http;

// test('example', function () {
//     $response = $this->get('/');

//     $response->assertStatus(200);
// });

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


function fakeUnknownCity(): void
{
    Http::preventStrayRequests();

    Http::fake([
        'geocoding-api.open-meteo.com/v1/search*' => Http::response(['generationtime_ms' => 0.4]),
    ]);
}


it('lists the five most recently searched cities, newest first', function () {
    foreach (['Oslo', 'Lima', 'Rome', 'Cairo', 'Paris', 'Tokyo'] as $daysAgo => $city) {
        Search::factory()->create([
            'city' => $city,
            'country' => 'Testland',
            'updated_at' => now()->subDays($daysAgo),
        ]);
    }

    $response = $this->get(route('weather.index'));

    $response->assertSeeInOrder(['Oslo', 'Lima', 'Rome', 'Cairo', 'Paris'])
        ->assertDontSee('Tokyo');
});

it('hides the recent searches section when nothing has been searched', function () {
    $response = $this->get(route('weather.index'));

    $response->assertOk()->assertDontSee('Recent Searches');
});


it('rejects an invalid city', function (string $city, string $message) {
    $response = $this->from(route('weather.index'))
        ->post(route('weather.search'), ['city' => $city]);

    $response->assertRedirect(route('weather.index'))
        ->assertSessionHasErrors(['city' => $message]);

    $this->assertDatabaseCount('searches', 0);
})->with([
    'empty' => ['', 'The city field is required.'],
    'too short' => ['a', 'The city field must be at least 2 characters.'],
    'too long' => [str_repeat('a', 61), 'The city field must not be greater than 60 characters.'],
]);


it('saves a successful search and redirects to the weather page', function () {
    fakeOpenMeteo();

    $response = $this->post(route('weather.search'), ['city' => 'lahore']);

    $response->assertRedirect(route('weather.show', ['city' => 'lahore']));

    $this->assertDatabaseHas('searches', [
        'city' => 'Lahore',
        'country' => 'Pakistan',
        'temperature' => 31.4,
        'condition' => 'Clear sky',
    ]);
});

it('shows the current weather for a city', function () {
    fakeOpenMeteo();

    $response = $this->get(route('weather.show', ['city' => 'Lahore']));

    $response->assertSeeInOrder(['Lahore', 'Pakistan', 'Clear sky', '9.2 km/h']);
});




it('sends the user back with an error when the city cannot be found', function () {
    fakeUnknownCity();

    $response = $this->from(route('weather.index'))
        ->post(route('weather.search'), ['city' => 'Atlantis']);

    $response->assertRedirect(route('weather.index'))
        ->assertSessionHasErrors(['city' => 'We couldn\'t find weather for "Atlantis".'])
        ->assertSessionHasInput('city', 'Atlantis');

    $this->assertDatabaseCount('searches', 0);
});

it('returns 404 for the weather page of a city that cannot be found', function () {
    fakeUnknownCity();

    $response = $this->get(route('weather.show', ['city' => 'Atlantis']));

    $response->assertNotFound();
});