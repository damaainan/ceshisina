<?php
/**
回文序列
如果一个数字序列逆置之后跟原序列是一样的就称这样的数字序列为回文序列。例如：
{1, 2, 1}, {15, 78, 78, 15} , {112} 是回文序列,
{1, 2, 2}, {15, 78, 87, 51} ,{112, 2, 11} 不是回文序列。
现在给出一个数字序列，允许使用一种转换操作：
选择任意两个相邻的数，然后从序列移除这两个数，并用这两个数字的和插入到这两个数之前的位置(只插入一个和)。
现在对于所给序列要求出最少需要多少次操作可以将其变成回文序列。

输入描述:
输入为两行，第一行为序列长度n ( 1 ≤ n ≤ 50)
第二行为序列中的n个整数item[i]  (1 ≤ iteam[i] ≤ 1000)，以空格分隔。


输出描述:
输出一个数，表示最少需要的转换次数

输入例子:
4
1 1 1 3

输出例子:
2
 */
function dealHui($arr) {
	$n = count($arr);
	$narr = deal($n, $arr);
	echo "newarr";
	$flag = check($narr);
	if ($flag == 1) {
		var_dump($narr);
		echo "newarr";
		$k = $n - count($narr);
		return $k;
	} else {
		return -1;
	}
	var_dump($narr);
	echo "newarr";
	$k = $n - count($narr);
	return $k;
}
function deal($n, $arr) {
	if ($n == 1) {
		return $arr;
	}
	$flag = check($arr);
	if ($flag == 1) {
		return $arr;
	}
	if ($flag == 0 && count($arr) == 2) {
		return $arr;
	}
	// $arr = dealOdd($arr);
	$k = count($arr);
	echo "<br/>", "k====", $k, "<br/>";
	if ($k % 2 == 0) {
		$arr = dealOdd($arr);
		$narr = deal(count($arr), $arr);
		return $narr;
	} else {
		$arr = dealEven($arr);
		$narr = deal(count($arr), $arr);
		return $narr;
	}

}
function check($arr) {
	$n = count($arr);
	$flag = 1;
	for ($i = 0; $i < $n; $i++) {
		if ($arr[$i] != $arr[$n - 1 - $i]) {
			$flag = 0;
			break;
		}
	}
	return $flag;
}

function dealOdd($arr) {
	$n = count($arr);
	for ($i = 0; $i < ($n - 1) / 2; $i++) {
		if ($arr[$i] != $arr[$n - $i - 1]) {
			// if($i+1<($n-1)/2){
			if (($arr[$i] + $arr[$i + 1]) < ($arr[$n - $i - 1] + $arr[$n - $i - 2])) {
				//前加
				echo $i;
				$newArr = array_splice($arr, $i, 2, $arr[$i] + $arr[$i + 1]);
				break;
			} else {
				//后加
				echo $i;
				$newArr = array_splice($arr, $n - $i - 1, 2, $arr[$n - $i - 1] + $arr[$n - $i - 2]);
				break;
			}
		}
	}
	var_dump($arr);
	return $arr;
}
function dealEven($arr) {
	$n = count($arr);
	var_dump($arr);
	for ($i = 0; $i < $n / 2 - 1; $i++) {
		if ($arr[$i] != $arr[$n - $i - 1]) {
			// if($i+1<($n-1)/2){
			if (($arr[$i] + $arr[$i + 1]) < ($arr[$n - $i - 1] + $arr[$n - $i - 2])) {
				//前加
				echo $i;
				$newArr = array_splice($arr, $i, 2, $arr[$i] + $arr[$i + 1]);
				break;
			} else {
				//后加
				echo $i;
				$newArr = array_splice($arr, $n - $i - 1, 2, $arr[$n - $i - 1] + $arr[$n - $i - 2]);
				break;
			}
		}
	}
	return $arr;
}

$arr = [1, 2, 4, 3, 6, 5, 1, 1, 2];
$as = dealHui($arr);
var_dump($as);