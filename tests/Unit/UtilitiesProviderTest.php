<?php

use AlanTiller\Framework\Providers\UtilitiesProvider;

it('can generate random string', function () {
    $utilities = new UtilitiesProvider();
    $randomString = $utilities->generateRandomString(10);
    expect(strlen($randomString))->toBe(10);
});

it('can hash password', function () {
    $utilities = new UtilitiesProvider();
    $password = 'password123';
    $hash = $utilities->hashPassword($password);
    expect($hash)->not->toBe($password);
    expect(password_verify($password, $hash))->toBeTrue();
});

it('can slugify string', function () {
    $utilities = new UtilitiesProvider();
    $string = 'This is a test string';
    $slug = $utilities->slugify($string);
    expect($slug)->toBe('this-is-a-test-string');
});