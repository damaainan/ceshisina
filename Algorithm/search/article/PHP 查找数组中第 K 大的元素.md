## PHP 查找数组中第 K 大的元素

来源：[https://blog.tanteng.me/2018/05/php-find-the-k-largest/](https://blog.tanteng.me/2018/05/php-find-the-k-largest/)

时间 2018-05-25 16:36:17

 
比如一个数组 [33, 5, 1, 90, 99, 3, 45, 13]，要找出第 K 大的元素，首先可以使用快速排序算法对数组进行排序，然后从排序后的数组中就很容易得到这个元素。
 
代码如下：
 
```php
<?php
 
$arr = [33, 5, 1, 90, 99, 3, 45, 13];
 
// 找到数组中第 K 大的元素
function findK($arr, $k){
	$sortedArr = quickSort($arr);
	echo sprintf("排序结果：%s\n", implode(",", $sortedArr));
	return $sortedArr[count($sortedArr) - $k];
}
 
// 快速排序
function quickSort($arr){
	if(!isset($arr[0])){
		return $arr;
	}
 
	if(count($arr) <= 1){
		return $arr;
	}
 
	$first = $arr[0];
	$left = $right = [];
	foreach ($arr as $item) {
		if($item < $first){
			$left[] = $item;
		}
 
		if($item > $first){
			$right[] = $item;
		}
	}
 
	$left = quickSort($left);
	$right = quickSort($right);
 
	return array_merge($left, array($first), $right);
}
 
$k = findK($arr, 3);
echo $k . PHP_EOL;


```
 
虽然可以实现，但是快速排序的时间复杂度最坏是 O(n²)，最好是 O(nlogn)，有没有办法可以优化呢？
 
![][0]
 
快速排序算法把元素分成两边分别递归再合并，如图，第一趟会把元素分成左中右三个部分，以第一个元素 33 作为基准，左边是较小的，右边是较大的，右、中、左元素个数是 3，1，4.
 
那么第 4 大的元素就是中间的 33 了，第 1-3 大的就在右边查找，第 5-8 大的就在左边查找，这样根据 k 可以判断查找的范围，不需要两边都递归排序。
 
```php
<?php
 
$arr = [33, 5, 1, 90, 99, 3, 45, 13];
 
// 找到数组中第 K 大的元素
function findK($arr, $k){
	if(!isset($arr[0])){
		return $arr;
	}
 
	if(count($arr) <= 1){
		return $arr;
	}
 
	$first = $arr[0];
	$left = $right = [];
	foreach ($arr as $item) {
		if($item < $first){
			$left[] = $item;
		}
 
		if($item > $first){
			$right[] = $item;
		}
	}
 
	$leftnum = count($left);
	$rightnum = count($right);
 
	if($k == $rightnum + 1){
		return $first;
	}
 
	if($k < $rightnum + 1){
		$sorted = quickSort($right);
		return $sorted[ $rightnum - $k];
	}
 
	if($k > $rightnum + 1){
		$sorted = quickSort($left);
		return $sorted[ $k - $leftnum ];
	}
}
 
//快速排序
function quickSort($arr){
	if(!isset($arr[0])){
		return $arr;
	}
 
	if(count($arr) <= 1){
		return $arr;
	}
 
	$first = $arr[0];
	$left = $right = [];
	foreach ($arr as $item) {
		if($item < $first){
			$left[] = $item;
		}
 
		if($item > $first){
			$right[] = $item;
		}
	}
 
	$left = quickSort($left);
	$right = quickSort($right);
 
	return array_merge($left, array($first), $right);
}
 
$k = findK($arr, 4);
echo $k . PHP_EOL;


```
 
根据优化思路整理后的算法如上，当然还可以进一步抽象精简，但是这样更保留“思路”，以供参考。
 


[0]: ../img/URVzyya.png 