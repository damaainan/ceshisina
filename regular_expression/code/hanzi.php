<?php
header("Content-type:text/html; Charset=utf-8");

// 匹配汉字的练习
$str = "做一只有梦想的青蛙 to travel";
$ret = preg_match_all("/^\b[\x{4e00}-\x{9fa5}]{1,}/u", $str, $match); // 匹配到整句
var_dump($match);

$ret = preg_match_all("/[\x{4e00}-\x{9fa5}]{1,}/u", $str, $match); // 匹配到整句
var_dump($match);

$ret = preg_match_all("/[\x{4e00}-\x{9fa5}]/u", $str, $match);// 单字
var_dump($match);