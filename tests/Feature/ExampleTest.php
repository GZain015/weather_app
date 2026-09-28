<?php

test('the home page redirects to the weather search', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('weather.index'));
});
