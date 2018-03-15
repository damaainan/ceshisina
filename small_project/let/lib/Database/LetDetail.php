<?php
namespace Tools\DB;
use Tools\DB\MedooDB;

class LetDetail {
    public static $table = 'lottery_let_detail';
    public static function getAll($name = '*') {
        // $medoo = new MedooDB();
        // $database = $medoo::$database;
        // $rest = $database->select(self::$table, ["id"]);

        $rest = MedooDB::findAlls(self::$table);
        // var_dump($rest);
        return $rest;
    }

    /**
     * [allTurns description]
     * @param  [type] $select ['turn'] 会返回 [['turn']=>1,['turn']=>1,['turn']=>1,....]
     *                        'turn'   会返回 [1,2,3,4...]  决定返回 一维数组 还是 二维数组
     * @return [type]         [description]
     */
    public static function allTurns($select, $where = []) {
        $rest = MedooDB::findAlls(self::$table, $select, $where);
        // var_dump($rest);
        return $rest;
    }

    public static function addDetails($data) {
        $rest = MedooDB::adds(self::$table, $data);
        return $rest;
    }
    public static function addDetail($data) {
        $rest = MedooDB::addItem(self::$table, $data);
        return $rest;
    }

    public static function alterLink() {}

}