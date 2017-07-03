<?php 

    //直接选择排序
    function selectSort($arr){
        $length = count($arr);
        if($length < 2){
            return $arr;
        }
        for($i=0;$i<$length-1;$i++){
            $minIndex = $i;
            for($j=$i+1;$j<$length;$j++){
                if($arr[$minIndex]>$arr[$j]){
                    $minIndex = $j;
                }
            }
            if($minIndex != $i){
                list($arr[$minIndex],$arr[$i]) = array($arr[$i],$arr[$minIndex]);
            }
        }
        return $arr;
    }

    $item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
    var_dump(implode(',',$item));
    var_dump(implode(',',selectSort($item)));    