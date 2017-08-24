<?php 
header("Content-type:text/html; Charset=utf-8");
function bin_search($arr,$low,$high,$k){
     if($low <= $high){
         $mid = intval(($low + $high)/2);
         if($arr[$mid] == $k){
             return $mid;
         }else if($k < $arr[$mid]){
             return bin_search($arr,$low,$mid-1,$k);
         }else{
             return bin_search($arr,$mid+1,$high,$k);
         }
     }
     return -1;
 }

$arr = array(1,2,3,4,5,6,7,8,9,10);
print(bin_search($arr,0,9,3));