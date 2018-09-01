<?php 
function interpolationSearch($arr, $target)
{
    $low = 0;
    $high = count($arr)-1;
    while($low <= $high){
        $gap = $arr[$high] - $arr[$low];
        if($gap){
            $mid = intval(($high - $low)*($target - $arr[$low]) / $gap) + $low;
        }else{
            $mid = $low;
        }
        if( $mid < $low || $mid > $high)
            break;
        if($target < $arr[$mid]){
            $high = $mid - 1;
        }else if($target > $arr[$mid]){
            $low = $mid + 1;
        }else{
            return $mid;
        }
    }
    return -1;
}

$numbers = [1, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 4, 4, 5, 5];
$number  = 2;
$pos     = interpolationSearch($numbers, $number);

echo $pos;