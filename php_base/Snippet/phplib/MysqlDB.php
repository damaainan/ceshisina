<?php
/**
 * @author: Rudy
 * @time: 2015/10/27 16:45
 *
 * Mysql数据库操作,需要mysqli扩展库
 *
 */

class MysqlOption{
    private $dbConnection = null;   // 数据库连接
    private $dbName = null;         // 当前默认数据库
    private $connectionName = null; // 数据库连接名

    function __construct($connectionName,$host, $username, $passwd, $dbname, $port, $charset, $socket=null){
        $this->connectionName = $connectionName;
        $this->dbName = $dbname;
        $dbConnection = new mysqli($host, $username, $passwd, $dbname, $port, $socket);
        if ($dbConnection->connect_error){
            throw new Exception("mysql {$connectionName} connection failure:({$dbConnection->connect_errno}){$dbConnection->connect_error}",-1);
        }
        $dbConnection->set_charset($charset);
        $this->dbConnection = $dbConnection;
    }

    function __destruct(){
        if ($this->dbConnection){
            // 操作对象销毁时候，关闭连接
            $this->dbConnection->close();
        }
    }

    /**
     * 获取当前默认的字符集
     * @return string 字符集
     */
    function getCharset(){
        return $this->dbConnection->character_set_name();
    }


    /**
     * 获取select结果集中的所有结果
     * @param $option
     * @return array
     */
    protected function fetch_all($option){
        $result = array();
        if(method_exists($option,'fetch_all')){
            $result =  $option->fetch_all(MYSQLI_ASSOC);
        }else{
            while($row = $option->fetch_assoc()){
                $result[] = $row;
            }
        }

        return $result;
    }



    /**
     * 切换默认数据库
     * @param $dbName String 要切换的数据库名称
     * @return $this 做链式访问
     * @throws Exception
     */
    function switchDb($dbName){
        if (is_string($dbName) && !empty($dbName)){
            if($this->dbConnection->select_db($dbName)){
                return $this;
            };
            throw new Exception("mysql {$this->connectionName} switch db from {$this->dbName} to {$dbName} failure:({$this->dbConnection->errno}){$this->dbConnection->error}",-1);
        }
        throw new Exception("mysql {$this->connectionName} switch db from {$this->dbName} to {$dbName} failure:param error",-1);
    }


    /**
     * 执行数据库操作
     * @param $sql String 要执行的sql语句
     * @param array $params 如果不为空，进行参数预处理
     * @return bool|int|mixed 执行select等返回结果集数组，执行insert返回自增值，执行其他返回受影响的行数。失败返回false
     * @throws Exception 支持出错抛出异常。
     */
    function query($sql,$params=array()){
        $returnValue = true;
        $sql = trim($sql,' ');
        if (empty($params)){
            $result = $this->dbConnection->query($sql);
            if ($result == false){
                if ($this->dbConnection->error){
                    throw new Exception("{$this->connectionName} query error:({$this->dbConnection->errno}){$this->dbConnection->error}");
                }else{
                    return false;
                }
            }
            if ($result === true){
                if (stripos($sql,'insert') === false){
                    return $this->dbConnection->affected_rows;
                }else{
                    return $this->dbConnection->insert_id;
                }
            }
            $returnValue = $this->fetch_all($result);
            if($result->fetch_assoc()){

            }
            $result->close();
        }else{
            $statement = $this->dbConnection->prepare($sql);
            if ($statement == false){
                throw new Exception("{$this->connectionName} prepare error:({$this->dbConnection->errno}){$this->dbConnection->error}");
            }
            $types = '';
            $data = array('');
            foreach($params as $key=>$value){
                $type = gettype($value);
                switch($type){
                    case 'string':{
                        $types .= 's';
                        $data[] = &$params[$key];
                    }break;
                    case 'integer':{
                        $types .= 'i';
                        $data[] = &$params[$key];
                    }break;
                    case 'double':{
                        $types .= 'd';
                        $data[] = &$params[$key];
                    }break;
                    default:
                        $types .= 'b';
                        $data[] = &$params[$key];
                }
            }
            $data[0] = $types;
            $method   = new ReflectionMethod($statement,'bind_param');
            if ($method->invokeArgs($statement,$data) == false){
                throw new Exception("{$this->connectionName} bind_param error:({$this->dbConnection->errno}){$this->dbConnection->error}");
            }
            if ($statement->execute() == false){
                throw new Exception("{$this->connectionName} execute error:({$this->dbConnection->errno}){$this->dbConnection->error}");
            }
            $arr = explode(' ',$sql);
            $type = isset($arr[0])?strtolower($arr[0]):'';
            switch($type){
                case 'explain':
                case 'select':{
                    $rel = $statement->get_result();
                    if ($rel === false){
                        throw new Exception("{$this->connectionName} select error:({$this->dbConnection->errno}){$this->dbConnection->error}");
                    }
                    $returnValue = $this->fetch_all($rel);
                }break;
                case 'insert':{
                    $returnValue = $this->dbConnection->insert_id;
                }break;
                case 'update':
                case 'delete':{
                    $returnValue = $this->dbConnection->affected_rows;
                };
            }
            $statement->close();
        }

        return $returnValue;
    }
}


class MysqlDB{
    private static $dbConnectionPool = array();
    private static $configs = array();

    public static function loadOneConfig($arr){
        if (!extension_loaded('mysqli')){
            throw new Exception('need extension mysqli!', -1);
        }
        if (!is_array($arr) || empty($arr)){
            throw new Exception('config not empty!', -1);
        }
        if (!isset($arr['connectionName']) || empty($arr['connectionName']) || !is_string($arr['connectionName'])){
            throw new Exception('connectionName not exit or not a string!',-1);
        }

        $config = array();
        $config['connectionName'] = $arr['connectionName'];
        $config['host'] = isset($arr['host'])?$arr['host']:'';
        $config['userName'] = isset($arr['userName'])?$arr['userName']:'';
        $config['password'] = isset($arr['password'])?$arr['password']:'';
        $config['dbName'] = isset($arr['dbName'])?$arr['dbName']:'';
        $config['port'] = isset($arr['port'])?$arr['port']:3306;
        $config['charset'] = isset($arr['charset'])?$arr['charset']:'utf8';

        self::$configs[$config['connectionName']] = $config;
        if (isset(self::$dbConnectionPool[$config['connectionName']])){
            unset(self::$dbConnectionPool[$config['connectionName']]);
        }
        return true;
    }

    public static function getConnection($connectionName){
        if (!is_string($connectionName) || !isset(self::$configs[$connectionName])){
            throw new Exception("mysql config : {$connectionName} is't loaded !",-1);
        }
        if (!isset(self::$dbConnectionPool[$connectionName])){
            $config = self::$configs[$connectionName];
            $dbConnection = new MysqlOption($connectionName,$config['host'],$config['userName'],$config['password'],$config['dbName'],$config['port'],$config['charset']);
            self::$dbConnectionPool[$connectionName] = $dbConnection;
        }
        return self::$dbConnectionPool[$connectionName];
    }
}


//测试
if(strtolower(PHP_SAPI) == 'cli' && basename(__FILE__) == basename($argv[0])){
    $config = array(
        'connectionName' => 'dbLol',
        'host'=> '*.*.*',
        'userName' => 'xxxx',
        'password' => '*****',
        'dbName' => 'test',
        'port' => '3306'
    );

    MysqlDB::loadOneConfig($config);
    $lolConnection = MysqlDB::getConnection('dbLol');
    $data = $lolConnection->query('select * from test where id<=?;',array(10));
    print_r($data);
}
