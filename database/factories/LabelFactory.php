<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Label;
use App\Server;
use Faker\Generator as Faker;

$factory->define(Label::class, function (Faker $faker) {
    return [
        'server_id' => function () {
            return factory(Server::class)->create()->id;
        },
        'ext_id' => $faker->unique()->randomNumber,
        'type' => $faker->randomElement(['status', 'priority', 'tracker']),
        'name' => $faker->word
    ];
});
