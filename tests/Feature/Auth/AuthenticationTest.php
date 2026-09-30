<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a new user and logs them in', function () {
    $response = $this->post(route('register'), [
        'name' => 'Ali',
        'email' => 'ali@example.com',
        'password' => 'secret-pass-123',
        'password_confirmation' => 'secret-pass-123',
    ]);

    $response->assertRedirect(route('weather.index'));
    $this->assertAuthenticated();

    $user = User::where('email', 'ali@example.com')->first();
    expect(Hash::check('secret-pass-123', $user->password))->toBeTrue();
});