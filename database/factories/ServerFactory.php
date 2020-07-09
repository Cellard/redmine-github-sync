<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Server;
use Faker\Generator as Faker;

$factory->define(Server::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
        'driver' => $faker->randomElement(Server::DRIVERS),
        'base_uri' => 'https://' . $faker->unique()->domainName() . '/'
    ];
});
