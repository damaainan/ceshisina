<?php
$arr = [1, 2, 3];
var_dump($arr);
unset($arr[0]);
var_dump($arr);
list($a, $b) = $arr;
echo $a; // undefined
echo $b;

