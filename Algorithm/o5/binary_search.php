<?php 


//非递归实现折半查找


function binary_search($arr,$target){
        $length = count($arr);
        if($length <=0 || $arr[0] > $target || $arr[$length-1] < $target){
            return -1;
        }
        $low = 0;
        $height = $length-1;
        while($low <= $height){
            $mid = (int)(($low+$height)/2);
            if($arr[$mid] > $target){
                $height = $mid-1;
            }else if($arr[$mid] < $target){
                $low = $mid+1;
            }else{
                return $mid;
            }
        }
        return -1;
    }

    $item = array(50, 30, 20,35,33,40,36, 100, 56, 78);
    var_dump(binary_search($item,'8'));
    var_dump($item[binary_search($item,'8')]);