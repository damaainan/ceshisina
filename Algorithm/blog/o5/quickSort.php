<?php 


//快速排序
function quickSort(&$arr,$height,$low=0){
    if($height <= $low){
        return $arr;
    }
    $i=$low+1;
    $j= $height;
    $temp = $arr[$low];
    while($i<$j){
        while($i<$j && $arr[$j] > $temp){$j--;}
        while($i<$j && $arr[$i] <= $temp){$i++;}
        list($arr[$i],$arr[$j]) = array($arr[$j],$arr[$i]);
    }
    if($arr[$i] <= $temp){
        list($arr[$low],$arr[$i])=array($arr[$i],$arr[$low]);
    }

    quickSort($arr,$i-1,$low);
    quickSort($arr,$height,$j+1);
    return $arr;
}
$item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
var_dump(implode(',',$item));
var_dump(implode(',',quickSort($item)));