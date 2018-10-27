<?php

/*
// 注册自加载
spl_autoload_register('autoload');

function autoload($class)
{
  require dirname($_SERVER['SCRIPT_FILENAME']) . '//..//' . str_replace('\\', '/', $class) . '.php';
}
*/
// 将原作者的 spl 注册函数改成 composer 自动加载  
require "../../vendor/autoload.php";

/************************************* test *************************************/

use proxy\ext\RealSubject;
use proxy\ext\Proxy;

try {
    echo "未加代理之前：\n";
    $subject = new RealSubject();
    $subject->doSomething();

    echo "\n--------------------\n";

    echo "加代理：\n";
    $proxy = new Proxy($subject);
    $proxy->doSomething();
} catch (\Exception $e) {
    echo $e->getMessage();
}
