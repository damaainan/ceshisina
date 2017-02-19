<?php 
header("Content-type:text/html; Charset=utf-8");


$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 50000);
}


// 9  计数排序

/**
 * 计数排序
 * @param $arr
 * @return mixed
 */
/*function Countsort($arr){

    $max = $arr[0];
    $min = $arr[0];

    foreach($arr as $key => $value) {
        if ($value > $max) {
            $max = $value;
        }
        if ($value < $min) {
            $min = $value;
        }
    }
        //这里k的大小是要排序的数组中，元素大小的极值差+1
        $c=[];
        $k = $max - $min + 1;
        for($i = 0; $i < count($arr) ; $i ++){
            $c[$arr[$i] - $min ] +=1;
        }

        for($i=1;$i < count($c); ++$i){
            $c[$i] = $c[$i] + $c[$i - 1];
        }

        for($i = count($arr);$i > 0 ; --$i){
            $b[ -- $c[$arr[$i] - $min] ] = $arr[$i];
        }

    return $b;
}*/

function countingSort($arr) {

        $length = count($arr);
        if($length <= 1) return $arr;

        $size = count($arr);
        $max = $arr[0];

        //找出数组中最大的数
        for($i=1;$i<$size;$i++) {
            if($max < $arr[$i]) $max = $arr[$i];
        }

        //初始化用来计数的数组
        for ($i=0;$i<=$max;$i++) {
            $count_arr[$i] = 0;
        }

        //对计数数组中键值等于$arr[$i]的加1
        for($i=0;$i<$size;$i++) {
            $count_arr[$arr[$i]]++;
        }

        //相邻的两个值相加
        for($i=1;$i<=$max;$i++) {
            $count_arr[$i] = $count_arr[$i-1] + $count_arr[$i];
        }

        //键与值翻转
        for ($i=$size-1;$i>=0;$i--) {
            $over_turn[$count_arr[$arr[$i]]] = $arr[$i];
            $count_arr[$arr[$i]]--; // 前一个数找到位置后，那么和它值相同的数位置往前一步
        }

        //按照顺序排列
        $result = array();
        for ($i=1;$i<=$size;$i++) {
            array_push($result,$over_turn[$i]);
        }

        return $result;
    }



$countsort_start_time = microtime(true);

$countsort_sort = countingSort($arr);

$countsort_end_time = microtime(true);

$countsort_need_time = $countsort_end_time - $countsort_start_time;

print_r("计数排序耗时:" . $countsort_need_time . "<br />");