<?php
/**
 * 创建型模式
 *
 * php单例模式
 *
 * @author  TIGERB <https://github.com/TIGERB>
 * @example 运行 php test.php
 */


/*
// 注册自加载
spl_autoload_register('autoload');

function autoload($class)
{
  require dirname($_SERVER['SCRIPT_FILENAME']) . '//..//' . str_replace('\\', '/', $class) . '.php';
}
*/
// 将原作者的 spl 注册函数改成 composer 自动加载  
require "../vendor/autoload.php";

/************************************* test *************************************/

use singleton\Singleton;

// 获取单例
$instance = Singleton::getInstance();
$instance->test();

// clone对象试试
$instanceClone = clone $instance;
