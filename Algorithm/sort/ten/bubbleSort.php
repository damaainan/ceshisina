<?php 
header("Content-type:text/html; Charset=utf-8");
$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}

//2 冒泡排序

function bubbleSort($arr){
    $len=count($arr);
    for ($i = 0; $i < $len; $i++) {
        for ($j = 0; $j < $i - 1; $j++) {
            if ($arr[$j +1 ] < $arr[$j]) {
                $temp = $arr[$j + 1];
                $arr[$j + 1] = $arr[$j];
                $arr[$j] = $temp;

            }
        }
    }
    return $arr;
}

function bubble($arr){
    $isSort=true;
    for($i=0,$leni=count($arr);$i<$leni;$i++){
        for($j=0,$lenj=$leni-1;$j<$lenj;$j++){
            if($arr[$j]>$arr[$j+1]){
                $isSort=false;
                $arr[$j]=[$arr[$j+1],$arr[$j+1]=$arr[$j]][0];
            }
        }
        if($isSort) break;
    }
    return $arr;
}


$bubble_start_time = microtime(true);

$bubble_sort = bubble($arr);

$bubble_end_time = microtime(true);

$bubble_need_time = $bubble_end_time - $bubble_start_time;

print_r("冒泡排序耗时:" . $bubble_need_time . "<br />");