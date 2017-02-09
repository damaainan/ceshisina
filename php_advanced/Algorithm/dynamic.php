<?php 
//动态规划算法 
//可以突破循环层数的限制
function DynamicFib($index){
	if($index<0)
		die("unvalid parameter");

	$fibo_arr=[];
	$fibo_arr[0]=1;
	$fibo_arr[1]=1;
	for($pos=2;$pos<=$index;$pos++){
		$fibo_arr[$pos]=$fibo_arr[$pos-2]+$fibo_arr[$pos-1];
	}
	return $fibo_arr[$index];
}

// $rest=DynamicFib(120);
// var_dump($rest);


function Climb($floor){
	if($floor<1)
		return 1;

	$plans=[];

	$plans[1]=1;
	$plans[2]=2;

	for($cur=3;$cur<=$floor;$cur++){
		$plans[$cur]=$plans[$cur-1]+$plans[$cur-2];
	}

	return $plans;
}

$rest=Climb(12);
var_dump($rest);