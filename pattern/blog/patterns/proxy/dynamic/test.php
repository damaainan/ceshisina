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

use proxy\dynamic\RealSubject;
use proxy\dynamic\Proxy;
use proxy\dynamic\SubjectIH;

try {
    echo "动态代理：\n";
    $subject = new RealSubject();
    $handler = new SubjectIH($subject);

    $proxy = Proxy::newProxyInstance($subject, $handler);
    $proxy->doSomething();
    echo "\n--------------------\n";
} catch (\Exception $e) {
    echo $e->getMessage();
}
