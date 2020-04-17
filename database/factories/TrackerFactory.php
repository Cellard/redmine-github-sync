<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Tracker;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Tracker::class, function (Faker $faker) {
    return [
        'tracker' => $faker->randomElement(['helpdesk.101m.ru', 'rm.fc-zenit.ru', 'gogs.101m.ru']),
        'api_key' => Str::random(64)
    ];
});
