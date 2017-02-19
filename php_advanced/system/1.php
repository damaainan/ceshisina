<?php 

//查找到php安装位置
$phpcmd = exec("which php");
print_r($phpcmd);

$arr = array();
$ret = exec("ls -l", $arr); 
print_r($ret);
print_r($arr);