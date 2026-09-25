<?php

use App\Models\Search;

// test('example', function () {
//     $response = $this->get('/');

//     $response->assertStatus(200);
// });


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
