<?php
// 就是一个工具类

class Ioc
{
/**
 * @var 注册的依赖数组
 */

    protected static $registry = array();

    /**
     * 添加一个resolve到registry数组中
     * @param string $name 依赖标识
     * @param object $resolve 一个匿名函数用来创建实例
     * @return void
     */
    public static function register($name, Closure $resolve)
    {
        static::$registry[$name] = $resolve;
    }

    /**
     * 返回一个实例
     * @param string $name 依赖的标识
     * @return mixed
     */
    public static function resolve($name)
    {
        if (static::registered($name)) { // static 作用
            $name = static::$registry[$name];
            return $name();
        }
        throw new Exception('Nothing registered with that name, fool.');
    }
    /**
     * 查询某个依赖实例是否存在
     * @param string $name id
     * @return bool
     */
    public static function registered($name)
    {
        return array_key_exists($name, static::$registry);
    }
}

class book
{
    private static $db;
    private static $file;
    public static function setdb($db)
    {
        static::$db = $db;
    }
    public static function setfile($file)
    {
        static::$file = $file;
    }
    public static function setfunc($func)
    {
        $func();
    }
    public static function get()
    {
        echo static::$file,'******',static::$db;
    }
}

class demo
{
    public static function setdb($param)
    {
        echo $param,"***\r\n";
    }
    
    
}

$func = function(){
    echo "exec func";
};
$book = Ioc::register('book', function () use($func) {
    // $book = new Book;
    Book::setdb('db');
    Book::setfile('file');
    Book::setfunc($func);
    // $func = Demo::setDb();
    // Book::setfunc($func);

    Book::get();
    // return $book;
});

//注入依赖
$book = Ioc::resolve('book');
// var_dump($book);
