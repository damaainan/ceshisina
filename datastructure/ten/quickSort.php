<?php 
header("Content-type:text/html; Charset=utf-8");
$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}


//5 快速排序

function quickSort(&$arr){
    if(count($arr)>1){
        $k=$arr[0];
        $x=array();
        $y=array();
        $_size=count($arr);
        for($i=1;$i<$_size;$i++){
            if($arr[$i]<=$k){
                $x[]=$arr[$i];
            }elseif($arr[$i]>$k){
                $y[]=$arr[$i];
            }
        }
        $x=quickSort($x);
        $y=quickSort($y);
        return array_merge($x,array($k),$y);
    }else{
        return$arr;
    }
}

$quick_start_time = microtime(true);

$quick_sort = quickSort($arr);

$quick_end_time = microtime(true);

$quick_need_time = $quick_end_time - $quick_start_time;

print_r("快速排序耗时:" . $quick_need_time . "<br />");