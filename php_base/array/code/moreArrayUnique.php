<?php

//二维数组内部的一维数组中的值不能完全相同，删除其中重复的项：

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
printf("Before tranform the array:<br>"); //输出原来的数组
print_r($arr);
echo "<br/>";
function moreArrayUnique($arr = array())
{
    foreach ($arr[0] as $k => $v) {
        $arr_inner_key[] = $k; //先把二维数组中的内层数组的键值记录在在一维数组中
    }
    foreach ($arr as $k => $v) {
        $v        = join(",", $v); //降维 用implode()也行
        $temp[$k] = $v; //保留原来的键值 $temp[]即为不保留原来键值
    }
    printf("After split the array:<br>");
    print_r($temp); //输出拆分后的数组
    echo "<br/>";
    $temp = array_unique($temp); //去重：去掉重复的字符串
    foreach ($temp as $k => $v) {
        $a             = explode(",", $v); //拆分后的重组 如：Array( [0] => james [1] => 30 )
        $arr_after[$k] = array_combine($arr_inner_key, $a); //将原来的键与值重新合并
    }
    //ksort($arr_after);//排序如需要：ksort对数组进行排序(保留原键值key) ,sort为不保留key值
    return $arr_after;
}
$arr_new = moreArrayUnique($arr); //调用去重函数
printf("Duplicate removal of the array:<br>");
print_r($arr_new);
echo "<br/>";

/**
 * 输出结果：

Before tranform the array:   //原来数组
Array ( [0] => Array ( [name] => james [age] => 30 ) [1] => Array ([name] => susu [age] => 26 ) [2] => Array ( [name] => james [age]=> 30 ) [new] => Array ( [name] => kube [age] => 37 ) [list] =>Array ( [name] => kube [age] => 27 ) )
After split the array:  //拆分后数组
Array ( [0] => james,30 [1] => susu,26 [2] => james,30 [new] =>kube,37 [list] => kube,27 )
Duplicate removal of thearray:  //去重后数组
Array ( [0] => Array ( [name] => james [age] => 30 ) [1] => Array ([name] => susu [age] => 26 ) [new] => Array ( [name] => kube [age]=> 37 ) [list] => Array ( [name] => kube [age] => 27 ) )
 */
