<?php 

header("Content-type:text/html; Charset=utf-8");

/**
 *
 *
 *
 *
 * 
 */

class Singleton{
    //存放实例
    private static $_instance = null;

    //私有化构造方法、
    private function __construct(){
        echo "单例模式的实例被构造了\n";
    }
    //私有化克隆方法
    private function __clone(){

    }

    //公有化获取实例方法
    public static function getInstance(){
        if (!(self::$_instance instanceof Singleton)){
            self::$_instance = new Singleton();
        }
        return self::$_instance;
    }

    public function select(){
        echo "选择方法";
    }
}

$singleton=Singleton::getInstance();

$singleton->select();