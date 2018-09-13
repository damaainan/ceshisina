<?php

require './bean/beans.php';

// Person 在beans.php文件中声明
$protype = new ReflectionClass("Person");
// 可以添加一个参数，来进行过滤操作。如只获取public类型的属性
$properties = $protype->getProperties();

// 反射获取到类的属性信息
foreach ($properties as $property) {
    echo $property."\r\n";
}