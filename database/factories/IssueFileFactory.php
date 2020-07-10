<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Issue;
use App\IssueFile;
use App\User;
use Faker\Generator as Faker;

$factory->define(IssueFile::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'path' => $faker->slug,
        'description' => $faker->text,
        'ext_id' => $faker->unique()->randomNumber,
        'issue_id' => function () {
            return factory(Issue::class)->create()->id;
        },
        'author_id' => function () {
            return factory(User::class)->create()->id;
        }
    ];
});
