<?php

//php多维数组的去重（针对任意的键值进行去重）--二维数组的唯一--时间复杂度~O(n)
//
//以二维数组为例，来说明针对任意键值的去重，时间复杂度为~O(n)，只用一个foreach循环：

$arr = array(
    '0'    => array(
        'name' => 'james',
        'age'  => 30,
    ),
    '1'    => array(
        'name' => 'susu',
        'age'  => 26,
    ),
    '2'    => array(
        'name' => 'james',
        'age'  => 30,
    ),
    'new'  => array(
        'name' => 'kube',
        'age'  => 37,
    ),
    'list' => array(
        'name' => 'kube',
        'age'  => 27,
    ),
);
/*针对任意键值来进行去重*/
function getArrayUniqueByKeys($arr)
{
    $arr_out = array();
    foreach ($arr as $k => $v) {
        $key_out = $v['name'] . "-" . $v['age']; //提取内部一维数组的key(name age)作为外部数组的键

        if (array_key_exists($key_out, $arr_out)) {
            continue;
        } else {
            $arr_out[$key_out] = $arr[$k]; //以key_out作为外部数组的键
            $arr_wish[$k]      = $arr[$k]; //实现二维数组唯一性
        }
    }
    return $arr_wish;
}
$arr_wish = getArrayUniqueByKeys($arr);
printf("As for the arbitrarily key:<br>");
print_r($arr_wish);
echo "<br/>";

/**
 * 输出结果：

As for the arbitrarily key:
Array ( [0] => Array ( [name] => james [age] => 30 ) [1] => Array ( [name] => susu [age] => 26 ) [new] => Array ( [name] => kube [age] => 37 ) [list] => Array ( [name] => kube [age] => 27 ) )
 */
