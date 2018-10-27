<?php
/**
 * 行为型模式
 *
 * php访问者模式
 *
 * 说说我对的策略模式和访问者模式的区分：
 * 乍一看，其实两者都挺像的，都是实体类依赖了外部实体的算法，但是：
 * 对于策略模式：首先你是有一堆算法，然后在不同的逻辑中去使用；
 * 对于访问者模式：实体的【结构是稳定的】，但是结构中元素的算法却是多变的，比如就像人吃饭这个动作
 * 是稳定不变的，但是具体吃的行为却又是多变的；
 *
 * @author  TIGERB <https://github.com/TIGERB>
 * @example 运行 php test.php
 */


/*
function autoload($class)
{
    require dirname($_SERVER['SCRIPT_FILENAME']) . '//..//' . str_replace('\\', '/', $class) . '.php';
}
// 注册自加载
spl_autoload_register('autoload');
*/

// 将原作者的 spl 注册函数改成 composer 自动加载  
// 避免 psr-4 格式的以下警告
/*
 A file should declare new symbols (classes, functions, constants, etc.) and cause no other side effects, or 
 it should execute logic with side effects, but should not do both. The first symbol is defined on line 3 
 and the first side effect is on line 9.
 */

require "../vendor/autoload.php";

/************************************* test *************************************/

use visitor\Person;
use visitor\VisitorAsia;
use visitor\VisitorAmerica;

// 生产一个人的实例
$person = new Person();

// 来到了亚洲
$person->eat(new VisitorAsia());

// 来到了美洲
$person->eat(new VisitorAmerica());
