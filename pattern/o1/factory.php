<?php
 
//设定数据库接口
 
interface Db{
    public function realwork();
}
 
//设定工厂接口
 
interface Factory{
    public function facWorking();
}
 
//MySQL实际功能类
 
class Mysql implements Db{
    public function realWork(){
        return '开始使用mysql';
    }
}
 
//Oracle
 
class Oracle implements db{
    public function realWork(){
        return '开始使用oracle';
    }
}
 
//NoSQL实际功能类
 
class Nosql implements db{
    public function realWork(){
        return '开始使用nosql';
    }
}
 
//对外展示的MySQL工厂类
 
class FacMysql implements factory{
 
    protected static $database;
    public function facWorking(){
        self::$database = new Mysql();
        return self::$database->realWork();
    }
 
}
 
//对外展示的Oracle工厂类
 
class FacOracle implements factory{
 
    protected static $database;
 
    public function facWorking(){
        self::$database = new Oracle();
        return self::$database->realWork();
 
    }
 
}
 
//对外展示的NoSQL工厂类
 
class FacNosql implements factory{
 
    protected static $database;
 
    public function facWorking(){
        self::$database = new Nosql();
        return self::$database->realWork();
 
    }
 
}
 
//现在我想实现MySQL数据库的功能，但是我只需要调用MySQL的工厂类即可，我无法并且也没必要知道，实际类和方法的名字
 
$db = new facMysql();
print_r($db->facWorking()); //output：开始使用MySQL