<?php 
header("Content-type:text/html; Charset=utf-8");
function quick($arr){
	if(count($arr)<=1) return $arr;
	$in=$arr[0];//标尺选择很重要
	$left=[];
	$right=[];
	for($i=1,$len=count($arr);$i<$len;$i++){
		if($arr[$i]<$in) array_push($left,$arr[$i]);
		else array_push($right,$arr[$i]);
	}
	return array_merge(quick($left),[$in],quick($right));
}

$arr=[1,3,2,6,6,6,6,6,9,6,6,6,7];
$arr=quick($arr);
print_r($arr);