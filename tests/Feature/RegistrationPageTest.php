<?php

test('the registration page is public', function () {
    $response = $this->get(route('registrations.create'));

    $response->assertOk();
});
