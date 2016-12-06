<?php 
header('Content-Type:text/html; charset=UTF-8');  
$str="顺序为：出球顺序：双色球 14 04 15 18 17 20 + 15 幸运蓝 1。地址1223333333";
preg_match('/[\d\s]{12,}/', $str,$match);
var_dump($match);
$turn=explode(' ',trim($match[0]));
var_dump($turn);