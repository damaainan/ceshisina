<?php
header("Content-type:text/html; Charset=utf-8");
function bubble($arr){
	$isSort=true;
	for($i=0,$leni=count($arr);$i<$leni;$i++){
		for($j=0,$lenj=$leni-1;$j<$lenj;$j++){
			if($arr[$j]>$arr[$j+1]){
				$isSort=false;
				$arr[$j]=[$arr[$j+1],$arr[$j+1]=$arr[$j]][0];
			}
		}
		if($isSort) break;
	}
	return $arr;
}

$arr=[1,4,3,7,5,2];
$arr=bubble($arr);
print_r($arr);