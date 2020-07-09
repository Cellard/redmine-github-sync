<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Project;
use App\Server;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'server_id' => function () {
            return factory(Server::class)->create()->id;
        },
        'ext_id' => $faker->unique()->randomNumber,
        'slug' => $faker->unique()->slug,
        'name' => $faker->unique()->name,
        'description' => $faker->text
    ];
});
