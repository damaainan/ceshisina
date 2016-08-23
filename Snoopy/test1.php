<?php
include("Snoopy.class.php");
$url ="http://www.u148.net/article/139317.html";
$snoopy =new Snoopy();
//获取网页所有内容
// $snoopy->fetch($url);
//获取网页纯文本内容
// $snoopy->fetchtext($url);
//获取网页所有链接
$snoopy->fetchlinks ($url);
//获取网页表单
// $snoopy->fetchform($url);

//打印查看
var_dump($snoopy->results);
