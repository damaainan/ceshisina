<?php 
/**
 * 单例模式
------------
现实例子
> 一个国家同一时间只能有一个总统。当使命召唤的时候，这个总统要采取行动。这里的总统就是单例的。

白话
> 确保指定的类只生成一个对象。

*****坏处******
单例模式其实被看作一种反面模式，应该避免过度使用。它不一定不好，而且确有一些有效的用例，但是应该谨慎使用，因为它在你的应用里引入了全局状态，在一个地方改变，会影响其他地方。而且很难 debug 。另一个坏处是它让你的代码紧耦合，而且很难仿制单例。



 */


final class President {//final 关键字
    private static $instance;

    private function __construct() {
        // Hide the constructor
    }
    
    public static function getInstance() : President {
        if (!self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    private function __clone() {//私有化方法 禁止使用
        // echo "禁止克隆";
    }
    
    private function __wakeup() {
        // Disable unserialize
    }
}


$president1 = President::getInstance();
$president2 = President::getInstance();

var_dump($president1 === $president2); // true
// $p=clone $president1;