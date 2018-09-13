<?php
require './bean/beans.php';

$instance->getName(); // 执行Person 里的方法getName
// 或者：
$method = $class->getmethod('getName'); // 获取Person 类中的getName方法
$method->invoke($instance);    // 执行getName 方法
// 或者：
$method = $class->getmethod('setName'); // 获取Person 类中的setName方法
$method->invokeArgs($instance, array('snsgou.com'));