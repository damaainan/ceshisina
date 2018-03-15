<?php

namespace Tools\DB;
// require "../../vendor/autoload.php";

use Medoo\Medoo;

class MedooDB {
//取名 model 是为了区别原类
    public static $database;
    public function __construct() {

        self::$database = new medoo([
            'database_type' => 'mysql',
            'database_name' => 'caiji',
            'server' => 'localhost',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ]);

    }

    /**
     * [findOne description]
     * @param  [type] $table    [description]
     * @param  [type] $whereArr ["user_id[>]" => 100]  ['id'=>100]
     * @param  string $select   ['id','name']  or "*"
     * @return
     */
    public static function findAlls($table, $select = "*", $whereArr = []) {
        // $database = self::$database;
        new MedooDB(); // 静态方法调用 不执行 构造方法
        // var_dump(self::$database);
        $rest = self::$database->select($table, $select, $whereArr);
        // var_dump($rest);
        return $rest;
    }

    public static function findOne($table, $select = "*", $whereArr = []) {
        // $database = self::$database;
        new MedooDB(); // 静态方法调用 不执行 构造方法
        // var_dump(self::$database);
        $rest = self::$database->get($table, $select, $whereArr);
        // var_dump($rest);
        return $rest;
    }
    /**
     * [adds description]
     * @param  [type] $table
     * @param  [type] $data  [[],[],[]] 二维数组即可
     * @return [type]        [description]
     */
    public static function adds($table, $data) {
        new MedooDB(); // 静态方法调用 不执行 构造方法
        $rest = self::$database->insert($table, $data);
        $id = self::$database->id();
        return $id;
    }

    public static function addItem($table, $data) {
        new MedooDB(); // 静态方法调用 不执行 构造方法
        $rest = self::$database->insert($table, $data);
        $id = self::$database->id();
        return $id;
    }

    public static function addOne($table, $data) {
        new MedooDB(); // 静态方法调用 不执行 构造方法
        $rest = self::$database->insert($table, [$data]);
        return $rest;
    }

    public static function alter($table, $where, $data) {
        new MedooDB();
        $rest = self::$database->update($table, $data, $where);
        return $rest->rowCount();
    }

}