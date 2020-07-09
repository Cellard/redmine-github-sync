<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Credential;
use App\Server;
use App\User;
use Faker\Generator as Faker;

$factory->define(Credential::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'server_id' => function () {
            return factory(Server::class)->create()->id;
        },
        'username' => $faker->word,
        'api_key' => $faker->md5,
        'ext_id' => $faker->unique()->randomNumber,
    ];
});
