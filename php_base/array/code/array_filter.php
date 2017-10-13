<?php

//example1：筛选奇数
function odd($var)
{
    // 判断书否是奇数，和1作位运算
    return ($var & 1);
}
$array1 = array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5);
print_r(array_filter($array1, "odd")); //['a'=>1,'c'=>3,'e'=>5]

//example2：无回调函数，过滤空值
$entry = array(
    0 => 'hello',
    1 => false,
    2 => -1,
    3 => null,
    4 => '',
);
print_r(array_filter($entry)); //[0=>'hello',2=>-1]

//example3：带flag参数
$arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
var_dump(array_filter($arr, function ($k) {
    return $k == 'b';
}, ARRAY_FILTER_USE_KEY)); //['b'=>2]

var_dump(array_filter($arr, function ($v, $k) {
    return $k == 'b' || $v == 4;
}, ARRAY_FILTER_USE_BOTH)); //['b'=>2,'d'=>4]
