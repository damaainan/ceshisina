<?php
header("Content-type:text/html; Charset=utf-8");

// 分组命名相关 

$str ="email:ywdblog@gmail.com;";
preg_match("/email:(?<email>[\w@\.]*)/is", $str, $matches);
var_dump($matches);
// echo  $matches["email"] . "_" .  $matches['no'];
