<?php

//二维数组内部的一维数组因某一个键值不能相同，删除重复项

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
function secondArrayUniqueBykey($arr, $key)
{
    $tmp_arr = array();

    foreach ($arr as $k => $v) {
        if (in_array($v[$key], $tmp_arr)) //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
        {
            unset($arr[$k]); //销毁一个变量  如果$tmp_arr中已存在相同的值就删除该值
        } else {
            $tmp_arr[$k] = $v[$key]; //将不同的值放在该数组中保存
        }
    }
    //ksort($arr); //ksort函数对数组进行排序(保留原键值key)  sort为不保留key值
    return $arr;
}
$key     = 'name';
$arr_key = secondArrayUniqueBykey($arr, $key);
printf("As for the givenkey->%s:<br>", $key);
print_r($arr_key);
echo "<br/>";

/**
 * 输出结果：

As for the given key->name:
Array ( [0] => Array ( [name] => james [age] => 30 ) [1] => Array ([name] => susu [age] => 26 ) [new] => Array ( [name] => kube [age]=> 37 ) )
 */
