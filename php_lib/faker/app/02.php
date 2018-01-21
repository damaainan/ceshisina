<?php 
require "../vendor/autoload.php";

header("Content-type:text/html; Charset=utf-8");

function fff(){
    $faker = Faker\Factory::create('zh_CN');
    return [
        'name' => $faker->name(),
        'sex' => $faker->numberBetween($min = 0, $max = 1),
        'age' => $faker->numberBetween($min = 30, $max = 90),
        'minzu_id' => $faker->numberBetween($min = 1, $max = 8),
        'location_id' => $faker->numberBetween($min = 5, $max = 9),
        'marriage' => 1,
        'education' => 1,
        'job' => 1,
        'user_id' => 1,
        'orgnization' => 'xx大学',
        'contactor' => $faker->name(),
        'contactormobile' => $faker->phoneNumber(),
        'idnumber' => $faker->numerify('640############'),
        'address' => $faker->address(),
        'hukou' => $faker->address(),
        'mobile' => $faker->phoneNumber(),
        'telephone' => $faker->phoneNumber(),
        'created_at' => $faker->date($format = 'Y-m-d', $max = 'now'),//'2016-09-01',
        'updated_at' => $faker->date($format = 'Y-m-d', $max = 'now')//'2016-09-01'
    ];
}

$ret = fff();
var_dump($ret);

