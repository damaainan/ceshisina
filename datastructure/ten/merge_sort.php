<?php 
header("Content-type:text/html; Charset=utf-8");
$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}


//4 并归排序

//merge函数将指定的两个有序数组(arr1arr2,)合并并且排序
//我们可以找到第三个数组,然后依次从两个数组的开始取数据哪个数据小就先取哪个的,然后删除掉刚刚取过///的数据
function al_merge($arrA, $arrB)
{
    $arrC = array();
    while (count($arrA) && count($arrB)) {
        //这里不断的判断哪个值小,就将小的值给到arrC,但是到最后肯定要剩下几个值,
        //不是剩下arrA里面的就是剩下arrB里面的而且这几个有序的值,肯定比arrC里面所有的值都大所以使用
        $arrC[] = $arrA['0'] < $arrB['0'] ? array_shift($arrA) : array_shift($arrB);
    }
    return array_merge($arrC, $arrA, $arrB);
}

//归并排序主程序
function al_merge_sort($arr)
{
    $len = count($arr);
    if ($len <= 1)
        return $arr;//递归结束条件,到达这步的时候,数组就只剩下一个元素了,也就是分离了数组
    $mid = intval($len / 2);//取数组中间
    $left_arr = array_slice($arr, 0, $mid);//拆分数组0-mid这部分给左边left_arr
    $right_arr = array_slice($arr, $mid);//拆分数组mid-末尾这部分给右边right_arr
    $left_arr = al_merge_sort($left_arr);//左边拆分完后开始递归合并往上走
    $right_arr = al_merge_sort($right_arr);//右边拆分完毕开始递归往上走
    $arr = al_merge($left_arr, $right_arr);//合并两个数组,继续递归
    return $arr;
}

$merge_start_time = microtime(true);

$merge_sort = al_merge_sort($arr);

$merge_end_time = microtime(true);

$merge_need_time = $merge_end_time - $merge_start_time;

print_r("并归排序耗时:" . $merge_need_time . "<br />");