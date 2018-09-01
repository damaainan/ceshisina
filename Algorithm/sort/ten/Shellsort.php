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


    // 核心部分 三行代码
    // 第一层 取尽 gap
    // 第二层 
    // 第三层 比较根据 gap 分组的每个序列 相同位置元素的大小

    for($gap=floor($n/2);$gap>0;$gap=floor($gap/=2)) // gap 取 数组长度的一般，向下取整，并且大于 0
    {
        for($i=$gap;$i<$n;++$i) //根据增量循环   从 $gap 直到最后
        {
            //以增量为步幅进行查看
            for( $j=$i-$gap; $j>=0 && $arr[$j+$gap] < $arr[$j]; $j -= $gap)   // gap 分组的每个序列 相同位置元素的大小
            {
                swap($arr[$j],$arr[$j+$gap]);  // 替换两个 相隔 $gap 的元素 4.1350209712982
                // $arr[$j]=[ $arr[$j+$gap] , $arr[$j+$gap] = $arr[$j] ][0]; // 0.18511009216309
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

//希尔排序耗时:0.6606240272522