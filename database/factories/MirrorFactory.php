<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Credential;
use App\Mirror;
use App\Project;
use App\User;
use Faker\Generator as Faker;

$factory->define(Mirror::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'owner_id' => function () {
            return factory(User::class)->create()->id;
        },
        'left_type' => Project::class,
        'right_type' => Project::class,
        'left_id' => function () {
            return factory(Project::class)->create()->id;
        },
        'right_id' => function () {
            return factory(Project::class)->create()->id;
        },
        'config' => 'ltr'
    ];
});

$factory->afterMaking(Mirror::class, function ($mirror) {
    $mirror->owner->credentials()->save(factory(Credential::class)->make([
        'user_id' => $mirror->owner_id,
        'server_id' => $mirror->left->server_id
    ]));
    $mirror->owner->credentials()->save(factory(Credential::class)->make([
        'user_id' => $mirror->owner_id,
        'server_id' => $mirror->right->server_id
    ]));
});
