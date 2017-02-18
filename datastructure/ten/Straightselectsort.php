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
// 8  直接选择排序

/**
 * 直接选择排序
 * @param $arr
 * @return mixed
 */
function  Straightselectsort($arr){

    $n = count($arr);

    for($i = 0 ; $i < $n - 1;$i++){
        $m = $i;
        for($j = $i+1 ; $j < $n; $j++){
            if($arr[$j] < $arr[$m] ){
                $m = $j;
            }

            if($m != $j){
                //进行交换
                swap($arr[$m],$arr[$j]);
            }
        }
    }
    return $arr;
}

$straightselectsort_start_time = microtime(true);

$straightselectsort_sort = Straightselectsort($arr);

$straightselectsort_end_time = microtime(true);

$straightselectsort_need_time = $straightselectsort_end_time - $straightselectsort_start_time;

print_r("直接选择排序耗时:" . $straightselectsort_need_time . "<br />");