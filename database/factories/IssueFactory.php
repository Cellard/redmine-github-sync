<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Issue;
use App\IssueComment;
use App\IssueFile;
use App\Project;
use App\User;
use Faker\Generator as Faker;

$factory->define(Issue::class, function (Faker $faker) {
    return [
        'ext_id' => $faker->unique()->randomNumber,
        'project_id' => function () {
            return factory(Project::class)->create()->id;
        },
        'author_id' => function () {
            return factory(User::class)->create()->id;
        },
        'assignee_id' => function () {
            return factory(User::class)->create()->id;
        },
        'subject' => $faker->sentence,
        'description' => $faker->text
    ];
});

$factory->afterCreating(Issue::class, function ($issue) {
    $issue->comments()->save(factory(IssueComment::class)->make([
        'issue_id' => $issue->id
    ]));
    $issue->files()->save(factory(IssueFile::class)->make([
        'issue_id' => $issue->id
    ]));
});

