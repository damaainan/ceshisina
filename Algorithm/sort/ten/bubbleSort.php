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
        for ($j = 0; $j < $len - 1; $j++) {
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
    for($i=0,$leni=count($arr);$i<$leni;$i++){// 两层循环  第一层 全部循环
        for($j=0,$lenj=$leni-1;$j<$lenj;$j++){ // 第二层 因下边 需要取 $j+1 
            // 把最大的数放在最后
            if($arr[$j]>$arr[$j+1]){ // 前一个数大，则后移
                $isSort=false;
                $arr[$j]=[$arr[$j+1],$arr[$j+1]=$arr[$j]][0]; // 交换  $a=[$b,$b=$a][0] 先发生 $b=$a ， 后 $a=[$b,新$a][0] ,$a=$b
            }
        }
        if($isSort) break; //只有第一遍有效，即如果已排好序，则中断直接返回结果
    }
    return $arr;
}


$bubble_start_time = microtime(true);

$bubble_sort = bubbleSort($arr);
// $bubble_sort = bubble($arr);


$bubble_end_time = microtime(true);

$bubble_need_time = $bubble_end_time - $bubble_start_time;

print_r("冒泡排序耗时:" . $bubble_need_time . "<br />");


// 冒泡排序耗时:25.524669885635 
// 20s左右