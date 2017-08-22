<?php 
header("Content-type:text/html; Charset=utf-8");
$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}


//5 快速排序

function quickSort(&$arr){
    if(count($arr)>1){
        $k=$arr[0]; // 将第一个元素设为基准  这里是可以优化的一个地方
        $x=array(); // 小于等于基准的元素
        $y=array();  // 大于基准的元素
        $_size=count($arr);
        for($i=1;$i<$_size;$i++){
            if($arr[$i]<=$k){
                $x[]=$arr[$i];
            }elseif($arr[$i]>$k){
                $y[]=$arr[$i];
            }
        }
        $x=quickSort($x);  // 对两部分 分别递归 快速排序
        $y=quickSort($y);
        return array_merge($x,array($k),$y);  // 将 左半部  基准（需要数组化）  右半部 合并
    }else{
        return $arr;
    }
}

$quick_start_time = microtime(true);

$quick_sort = quickSort($arr);

$quick_end_time = microtime(true);

$quick_need_time = $quick_end_time - $quick_start_time;

print_r("快速排序耗时:" . $quick_need_time . "<br />");


// 快速排序耗时:0.25127196311951