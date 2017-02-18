<?php 
header("Content-type:text/html; Charset=utf-8");


$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 50000);
}

// 7  希尔排序
/**
 * 交换两个数的位置
 * @param $a
 * @param $b
 */
function swap(&$a,&$b){
    $temp = $b;
    $b = $a;
    $a = $temp;
}
/**
 * 希尔排序
 * @param $arr
 */
function Shellsort($arr)
{
    $n=count($arr); //数组长度

    for($gap=floor($n/2);$gap>0;$gap=floor($gap/=2)) //
    {
        for($i=$gap;$i<$n;++$i) //根据增量循环
        {
            //以增量为步幅进行查看
            for( $j=$i-$gap; $j>=0 && $arr[$j+$gap] < $arr[$j]; $j -= $gap)
            {
                swap($arr[$j],$arr[$j+$gap]);
            }
        }
    }

    return $arr;
}

$shellsort_start_time = microtime(true);

$shellsort_sort = Shellsort($arr);

$shellsort_end_time = microtime(true);

$shellsort_need_time = $shellsort_end_time - $shellsort_start_time;

print_r("希尔排序耗时:" . $shellsort_need_time . "<br />");
