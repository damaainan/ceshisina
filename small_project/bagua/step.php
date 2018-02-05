<?php 
require "yao.class.php";
header("Content-type:text/html; Charset=utf-8");

// 执行本程序即可

$yao =new yao();
$one=$yao->guaxiang(49);
print_r($one);