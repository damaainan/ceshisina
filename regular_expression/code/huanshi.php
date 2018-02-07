<?php
header("Content-type:text/html; Charset=utf-8");

// 环视的使用

/**
 * 向前查找 ?=
 * 向后查找 ?<=
 *
    ?<= 表示假如匹配到特定字符，则返回该字符后面的内容。 

    ?= 表示假如匹配到特定字符，则返回该字符前面的内容。 
 */

$str = 'chinadhello';
$preg = '/(?<=a)d(?=h)/';
preg_match($preg, $str, $arr);
print_r($arr);

$str = ("chinaWorldHello");
$preg = "/(?=[A-Z])/";
$arr = preg_split($preg, $str);
print_r($arr);

// ?=:  ?!: 结果一样 why?

// $str = "http://www.google.com";
$str = "https://www.google.com";
$preg = '/[a-z]+(?=:)/'; // : 之前的部分
preg_match($preg,$str,$arr);
print_r($arr);

// $str = "http://www.google.com";
$str = "https://www.google.com";
$preg = '/[a-z]+(?!:)/'; // : 之前的不要 一个字母
preg_match($preg,$str,$arr);
print_r($arr);