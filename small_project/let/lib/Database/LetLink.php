<?php
namespace Tools\DB;
use Tools\DB\MedooDB;

class LetLink {
    public static $table = 'lottery_let_link';
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

    public static function addLinks($data) {
        $rest = MedooDB::adds(self::$table, $data);
        return $rest;
    }

    public static function alterLink($id, $data) {
        $rest = MedooDB::alter(self::$table, ['id' => $id], $data);
        return $rest;
    }

}