<?php 

class Database {         
    //数据库连接资源         
    protected static $_db;         
    //单例标识符         
    protected static $_instance;         
    //设可见性设置成private，防止外部进行 实例化操作         
    private function __construct(){         
    }         
    //外部调用的是 getInstance         
    public static function getInstance(){         
        if (self :: $_instance === null) {         
            self::$_instance = new self();         
            self::$_db = mysql_connect('localhost','root','root');         
            echo '只有一次实例化';         
        }         
        return self::$_instance;         
    }         
    public function select_db($db){         
        return mysql_select_db($db,self::$_db);         
    }         
    //设可见性设置成private，防止外部进行 clone操作         
    private function __clone(){         
    }
 
}
 
$db = Database::getInstance();       
print_r($db->select_db('test'));     
print_r($db->select_db('test'));       
print_r($db->select_db('test'));    
//output:只有一次实例化111