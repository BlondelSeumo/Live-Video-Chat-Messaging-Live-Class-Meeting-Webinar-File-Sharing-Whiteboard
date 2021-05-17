<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Helpers\ArrHelper;
use App\Helpers\CalHelper;
use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {

    $genders = ArrHelper::getList('genders');

    $gender = $faker->randomElement($genders);
    $name = $faker->name($gender);
    $username = str_replace(" ", ".", strtolower($name));
    $created_at = CalHelper::randomDate(
        Carbon::now()->subYear(1)->toDateTimeString(),
        Carbon::now()->startOfYear()->toDateTimeString(),
    );

    return [
        'uuid' => $faker->uuid,
        'name' => $name,
        'gender' => $gender,
        'birth_date' => $faker->dateTimeBetween($startDate = '-50 years', $endDate = '-18 years', $timezone = null),
        'username' => $username,
        'email' => $username.'@example.com',
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'status' => 'activated',
        'remember_token' => Str::random(10),
        'created_at' => $created_at
    ];
});
