<?php 
function yuese(array $arr, int $n): int{
	$m = 0;
	while(count($arr) > 1){
		$m++;
		$survice = array_shift($arr);
		if($m % $n !== 0){
			array_push($arr, $survice);
		}
	}
	return $arr[0];
}
$arr = array(1 , 2 , 3 , 4 , 5 , 6 , 7, 8 , 9 , 10);
echo yuese($arr, 3);