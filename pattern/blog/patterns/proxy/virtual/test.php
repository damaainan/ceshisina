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

use proxy\virtual\Proxy;

try {
    echo "使用虚拟加代理：\n";
    $proxy = new Proxy();
    $proxy->doSomething();
} catch (\Exception $e) {
    echo $e->getMessage();
}
