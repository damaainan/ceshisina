<?php  
  //调用方式：
// 引入DBUtils.php文件，然后通过 DBUtils::方法名 这种形式，即可操作SQLite数据库了。
class SQLiteDB extends SQLite3 {  
    function __construct(){  
        try {  
            $this->open(dirname(__FILE__).'/../data/sqlite_ecloud.db');  
        }catch (Exception $e){  
            die($e->getMessage());  
        }  
    }  
}  
  
class DBUtils {  
      
    private static $db;  
      
    private static function instance(){  
        if (!self::$db) {  
            self::$db = new SQLiteDB();  
        }         
    }  
  
    /** 
     * 创建表 
     * @param string $sql 
     */  
    public static function create($sql){  
        self::instance();  
        $result = @self::$db->query($sql);  
        if ($result) {  
            return true;  
        }  
        return false;  
    }  
  
    /** 
     * 执行增删改操作 
     * @param string $sql 
     */  
    public static function execute($sql){  
        self::instance();  
        $result = @self::$db->exec($sql);  
        if ($result) {  
            return true;  
        }  
        return false;  
    }  
  
    /** 
     * 获取记录条数 
     * @param string $sql 
     * @return int 
     */  
    public static function count($sql){  
        self::instance();  
        $result = @self::$db->querySingle($sql);  
        return $result ? $result : 0;  
    }  
  
    /** 
     * 查询单个字段 
     * @param string $sql 
     * @return void|string 
     */  
    public static function querySingle($sql){  
        self::instance();  
        $result = @self::$db->querySingle($sql);  
        return $result ? $result : '';  
    }  
  
    /** 
     * 查询单条记录 
     * @param string $sql 
     * @return array 
     */  
    public static function queryRow($sql){  
        self::instance();  
        $result = @self::$db->querySingle($sql,true);  
        return $result;  
    }  
  
    /** 
     * 查询多条记录 
     * @param string $sql 
     * @return array 
     */  
    public static function queryList($sql){  
        self::instance();  
        $result = array();  
        $ret = @self::$db->query($sql);  
        if (!$ret) {  
            return $result;  
        }  
        while($row = $ret->fetchArray(SQLITE3_ASSOC) ){  
            array_push($result, $row);  
        }  
        return $result;       
    }  
}  
  
?>  