<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\DatabaseNotification;
use Faker\Generator as Faker;

$factory->define(\Illuminate\Notifications\DatabaseNotification::class, function (Faker $faker) {
    return [
        'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'type' => 'App\Notifications\ThreadWasUpdated',
        'notifiable_id' => function () {
            factory('App\User')->create()->id;
        },
        'notifiable_type' => 'App\User',
        'data' => ['foo' => 'bar']
    ];
});

$factory->state(\Illuminate\Notifications\DatabaseNotification::class, 'auth', function (Faker $faker) {
    return [
        'notifiable_id' => auth()->id()
    ];
});
