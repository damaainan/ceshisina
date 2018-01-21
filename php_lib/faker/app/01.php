<?php 
require "../vendor/autoload.php";

header("Content-type:text/html; Charset=utf-8");

 //用模型工厂创建
$faker = Faker\Factory::create();

echo $faker->name;
echo $faker->ipv4;
  // 'Lucy Cechtelar';
echo $faker->address;
  // "426 Jordy Lodge
  // Cartwrightshire, SC 88120-6700"
echo $faker->text;
  // Dolores sit sint laboriosam dolorem culpa et autem. Beatae nam sunt fugit
  // et sit et mollitia sed.
  // Fuga deserunt tempora facere magni omnis. Omnis quia temporibus laudantium
  // sit minima sint.