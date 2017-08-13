<?php 
header("Content-type:text/html; Charset=utf-8");


$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 50000);
}

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

// 6 鸡尾酒排序法

/**
 * 鸡尾酒排序
 * @param $arr
 * @return mixed
 */
function Cocktailsort($arr) {
    $arr_len  =count($arr);

    for($i = 0 ; $i < ($arr_len/2) ; $i ++){
        //将最小值排到队尾
        for( $j = $i ; $j < ( $arr_len - $i - 1 ) ; $j ++ ){
            if($arr[$j] < $arr[$j + 1] ){
                swap($arr[$j],$arr[$j + 1]);
            }
        }
        //将最大值排到队头
        for($j = $arr_len - 1 - ($i + 1); $j > $i ; $j --){
            if($arr[$j] > $arr[$j - 1]){
                swap($arr[$j],$arr[$j - 1]);
            }
        }
    }
    return $arr;
}

$cocktailsort_start_time = microtime(true);

$cocktailsort_sort = Cocktailsort($arr);

$cocktailsortt_end_time = microtime(true);

$cocktailsort_need_time = $cocktailsortt_end_time - $cocktailsort_start_time;

print_r("鸡尾酒排序耗时:" . $cocktailsort_need_time . "<br />");