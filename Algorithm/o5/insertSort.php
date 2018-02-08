<?php 

//简单插入排序(O(n2))
function insertSort($arr){//升序排列
    $length = count($arr);
    if($length <= 1){//输入数组至少有两个值
        return $arr;
    }

    $sort_arr = array($arr[0],$arr[1]);
    if($arr[0]>$arr[1]){
        list($sort_arr[0],$sort_arr[1]) = array($sort_arr[1],$sort_arr[0]);
    }
    for($i=2;$i<$length;$i++){
        $sort_length = count($sort_arr);
        $sort_arr[$sort_length]= null;
        for($j=$sort_length-1;$j >= 0;$j--){
            if($sort_arr[$j] > $arr[$i]){
                $sort_arr[$j+1] = $sort_arr[$j];
            }else{
                $sort_arr[$j+1] = $arr[$i];
                break;
            }
        }
        if($j==-1){
            $sort_arr[0] = $arr[$i];
        }
    }
    return $sort_arr;
}

$item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
var_dump(implode(',',$item));
var_dump(implode(',',insertSort($item)));    