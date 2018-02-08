<?php 

//冒泡排序(O(n2))
function bubbleSort($arr){
    $length = count($arr);
    if($length < 2){
        return $arr;
    }
    for($i=0;$i<$length;$i++){
        $temp = $arr[0];
        for($j=0;$j< $length-$i-1;$j++){
            if($arr[$j] > $arr[$j+1]){
                list($arr[$j],$arr[$j+1])= array($arr[$j+1],$arr[$j]);
            }
        }
    }
    return $arr;
}
$item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
var_dump(implode(',',$item));
var_dump(implode(',',bubbleSort($item)));