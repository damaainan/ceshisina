<?php
require './bean/beans.php';

$protype = new ReflectionClass ( "Person" );

// 模拟数据库中获取到的值，以关联数组的形式抛出
$values = array(
    "name"=>"郭璞",
    "age"=> 21,
    "address"=>"辽宁省大连市"
);

// 开始实例化
$instance = $protype->newInstanceArgs($values); 
print_r($instance);
// var_dump($instance);
echo $instance->getName();