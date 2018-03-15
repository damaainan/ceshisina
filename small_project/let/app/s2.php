<?php
header("Content-type:text/html; Charset=utf-8");
// set_time_limit(0);
require "../vendor/autoload.php";

use Tools\DB\LetDetail;
use Tools\DB\LetLink;
use Tools\GetContent;

$allTurns = LetLink::allTurns(['id', 'turn', 'link'], ['status' => 0]);
// var_dump($allTurns);

foreach ($allTurns as $val) {
    $url = $val['link'];
    $ret = GetContent::dealUrl($url);
    // var_dump($ret);
    $ret = formatDetail($ret);
    // print_r($ret);
    $rr = LetDetail::addDetails($ret);
    if (!$rr) {
        echo $val['id'];
        continue;
    }
    $re = LetLink::alterLink($val['id'], ['status' => 1]);
    if ($re === 1) {
        echo ".";
    } else {
        echo $val['id'];
    }
}

function formatDetail($info) {
    $temp['turn'] = $info['turn'];
    $temp['pdate'] = $info['date'];
    $temp['c_total'] = $info['ctotal'];
    $temp['total'] = $info['total'];
    $temp['ballturn'] = $info['ballturn'];
    // $temp[''] = $info[''];
    list($temp['code_1'], $temp['code_2'], $temp['code_3'], $temp['code_4'], $temp['code_5'], $temp['code_6'], $temp['code_7']) = $info['codes'];
    $temp = array_merge($info['plus'], $temp);
    $temp = array_merge($info['pride'], $temp);
    return $temp;
}

/*
array(8) {
'turn' =>
string(5) "18022"
'date' =>
string(10) "2018-02-26"
'codes' =>
array(7) {
[0] =>
string(2) "02"
[1] =>
string(2) "10"
[2] =>
string(2) "19"
[3] =>
string(2) "33"
[4] =>
string(2) "35"
[5] =>
string(2) "05"
[6] =>
string(2) "06"
}
'ctotal' =>
string(11) "202,288,216"
'total' =>
string(13) "4,988,506,161"
'pride' =>
array(12) {
'one' =>
string(1) "0"
'onep' =>
string(1) "0"
'two' =>
string(2) "43"
'twop' =>
string(7) "223,044"
'three' =>
string(3) "503"
'threep' =>
string(5) "7,433"
'four' =>
string(6) "25,204"
'fourp' =>
string(3) "200"
'five' =>
string(7) "494,801"
'fivep' =>
string(2) "10"
'six' =>
string(9) "5,045,083"
'sixp' =>
string(1) "5"
}
'plus' =>
array(6) {
'one' =>
string(1) "0"
'onep' =>
string(1) "0"
'two' =>
string(2) "12"
'twop' =>
string(7) "133,826"
'three' =>
string(3) "138"
'threep' =>
string(5) "4,459"
}
'ballturn' =>
string(22) "33 19 10 35 02 + 05 06"
}
 */