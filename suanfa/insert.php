<?php
header("Content-type:text/html; Charset=utf-8");
function insert($arr){
	for($j=0;$j<count($arr);$j++){
		$key=$arr[$j];
		$i=$j-1;
		while($i>-1 && $arr[$i]>$key){//前 大于 后   3 大于4 
			$arr[$i+1]=$arr[$i];//大者后移  4 等于 3
			$i--;
		}
		$arr[$i+1]=$key;//i已减  当前等于  3 等于4 
	}
	return $arr;
}

$arr=[1,4,3,7,5,2];
$arr=insert($arr);
print_r($arr);
