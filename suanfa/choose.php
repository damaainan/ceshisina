<?php
header("Content-type:text/html; Charset=utf-8");
function choose($arr){
	for($i=0,$leni=count($arr);$i<$leni;$i++){
		for($j=$i+1,$lenj=$leni;$j<$lenj;$j++){
			if($arr[$j]<$arr[$i]){
				$arr[$i]=[$arr[$j],$arr[$j]=$arr[$i]][0];
			}
		}
	}
	return $arr;
}

$arr=[1,4,3,7,5,2];
$arr=choose($arr);
print_r($arr);