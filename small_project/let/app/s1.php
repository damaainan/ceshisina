<?php
header("Content-type:text/html; Charset=utf-8");
// set_time_limit(0);
require "../vendor/autoload.php";

use Tools\GetContent;
use Tools\GetList;
use Tools\DB\LetLink;
use Tools\DB\LetDetail;
use Tools\DB\MedooDB;


$list = GetList::dealUrl('https://www.17500.cn/let/all.php'); // 按期号排序 
// var_dump($list);

arraySortByColumn($list,'turn',SORT_ASC);

// 写入数据库
//     校验数据是否已存入 
    // 获取所有 turn 
$allTurns = LetLink::allTurns('turn');
// var_dump($allTurns);
$temp=[];
foreach ($list as $ke=>$va) {
    if(!in_array($va['turn'],$allTurns)){
        $temp[] = $va;
    }
}
// var_dump($temp);

$s =LetLink::addLinks($temp);
echo $s;
// var_dump($s);

function arraySortByColumn(&$arr, $col, $dir=SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=>$row) {
        $sort_col[$key] = $row[$col];
    }   
    array_multisort($sort_col, $dir, $arr);
}  