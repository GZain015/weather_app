<?php

use App\Models\Favourite;
use App\Models\User;

it('does not let a user remove someone else\'s favourite', function () {
    $favourite = Favourite::factory()->create();

    $response = $this->actingAs(User::factory()->create())
        ->delete(route('favourites.destroy', $favourite));

    $response->assertForbidden();
    $this->assertModelExists($favourite);
});