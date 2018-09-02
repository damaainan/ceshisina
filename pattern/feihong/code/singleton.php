<?php
class Db
{
    public function query(){
        echo 'query' . PHP_EOL;
    }
    public function exec(){
        echo 'exec' . PHP_EOL;
    }
}
class Factory extends Db
{
    private static $db = null;
    /**
     * 单例工厂模式
     */
    public static function getInstance(){
        if (self::$db === null) {
            self::$db = new Db();
        }
        return self::$db;
    }
    /**私有化构造方法，禁止外部实例化*/
    private function __construct(){}
    /**禁止克隆*/
    private function __clone(){}
}

$db = Factory::getInstance();
$db->query();
var_dump($db);

$db2 = Factory::getInstance();
$db->exec();
var_dump($db2);
