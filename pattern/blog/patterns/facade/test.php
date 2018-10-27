<?php
/**
 * 结构型模式
 *
 * php外观模式
 * 把系统中类的调用委托给一个单独的类，对外隐藏了内部的复杂性，很有依赖注入容器的感觉哦
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

use facade\AnimalMaker;

// 初始化外观类
$animalMaker = new AnimalMaker();

// 生产一只猪
$animalMaker->producePig();

// 生产一只鸡
$animalMaker->produceChicken();
