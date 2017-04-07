<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});
$factory->define(App\Model\Eequipment\Order::class, function ($faker) {
    return [
        'order_id' => str_random(32),
        'order_no' => mt_rand(),
        'state' => 0,
        'create_time' => $faker->dateTime(),
        'order_desc' => $faker->sentence(mt_rand(3, 10)),
        'creator_id' => 'B465B4DDA74F1B91F891AAA9535EC74B',
        'place'=> '中国江苏省南京市玄武区北京东路41号',
        'user_name' => '滑吉行',
        'mobile' => '13585123012',
        'org_name' => '趣客吧-测试学校',
        'repaire_time' => date('Y-m-d H:i:s',time()),
        'order_flag'=>1
    ];
});