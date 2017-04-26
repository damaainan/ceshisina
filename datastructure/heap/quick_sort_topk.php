<?php 

//利用快速排序算法来实现 TopN  

//为了测试运行内存调大一点
    ini_set('memory_limit', '2024M');
    
    //实现一个快速排序函数
    function quick_sort(array $array){
        $length = count($array);
        $left_array = array();
        $right_array = array();
        if($length <= 1){
            return $array;
        }
        $key = $array[0];
        for($i=1;$i<$length;$i++){
            if($array[$i] > $key){
                $right_array[] = $array[$i];
            }else{
                $left_array[] = $array[$i];
            }
        }
        $left_array = quick_sort($left_array);
        $right_array = quick_sort($right_array);
        return array_merge($right_array,array($key),$left_array);    
    }
    
    //构造500w不重复数
    for($i=0;$i<5000000;$i++){
        $numArr[] = $i;    
    }
    //打乱它们
    shuffle($numArr);
    
    //现在我们从里面找到top10最大的数
    var_dump(time());
    print_r(array_slice(quick_sort($all),0,10));
    var_dump(time());