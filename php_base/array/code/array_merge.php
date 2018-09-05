<?php
$a = array('a','b'); 
$b = array('c', 'd'); 
$c = $a + $b; 
var_dump($c);
//输出：
// array (size=2)
//  0 => string 'a' (length=1)
//  1 => string 'b' (length=1) 
var_dump(array_merge($a, $b));
//输出：
//array (size=4)
// 0 => string 'a' (length=1)
// 1 => string 'b' (length=1)
// 2 => string 'c' (length=1)
// 3 => string 'd' (length=1)
$a = array('a' => 'a' ,'b' => 'b');
$b = array('a' => 'A', 'b' => 'B');
$c = $a + $b;
var_dump($c);
//输出：
//array (size=2)
//'a' => string 'a' (length=1)
//'b' => string 'b' (length=1)
var_dump(array_merge($a, $b));
//输出：
//array (size=2)
//'a' => string 'A' (length=1)
//'b' => string 'B' (length=1)