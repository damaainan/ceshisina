<?php 
header("Content-type:text/html; Charset=utf-8");
function shell($arr){
	$len=count($arr);
	$h=1;
	while($h<$len/3)
		$h=3*$h+1;
	while($h>=1){// =  至关重要
		for($i=$h;$i<$len;$i++){
			for($j=$i;$j>=$h && $arr[$j]<$arr[$j-$h];$j-=$h)
				$arr[$j]=[$arr[$j-$h],$arr[$j-$h]=$arr[$j]][0];
		}
		$h=($h-1)/3;
	}
	return $arr;
}

$arr=[1,3,2,16,4,9,36,26,11,7];
$arr=shell($arr);
print_r($arr);