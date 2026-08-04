<?php

use App\Models\City;

it('uses a city image when one is configured', function () {
    City::create([
        'name' => 'Indore',
        'slug' => 'indore',
        'state' => 'Madhya Pradesh',
        'is_active' => true,
        'image_url' => 'https://images.example.com/indore-hero.jpg',
    ]);

    expect(City::resolveHeroImage('Indore'))->toBe('https://images.example.com/indore-hero.jpg');
});

it('falls back to the default hero image when no city image is configured', function () {
    City::create([
        'name' => 'Bhopal',
        'slug' => 'bhopal',
        'state' => 'Madhya Pradesh',
        'is_active' => true,
    ]);

    expect(City::resolveHeroImage('Bhopal'))->toBe(asset('assets/images/indore-hero-v2.png'));
});
