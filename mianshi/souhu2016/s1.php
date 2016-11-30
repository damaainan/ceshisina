<?php
/**
 *马戏团
搜狐员工小王最近利用假期在外地旅游，在某个小镇碰到一个马戏团表演，精彩的表演结束后发现团长正和大伙在帐篷前激烈讨论，小王打听了下了解到， 马戏团正打算出一个新节目“最高罗汉塔”，即马戏团员叠罗汉表演。考虑到安全因素，要求叠罗汉过程中，站在某个人肩上的人应该既比自己矮又比自己瘦，或相等。 团长想要本次节目中的罗汉塔叠的最高，由于人数众多，正在头疼如何安排人员的问题。小王觉得这个问题很简单，于是统计了参与最高罗汉塔表演的所有团员的身高体重，并且很快找到叠最高罗汉塔的人员序列。 现在你手上也拿到了这样一份身高体重表，请找出可以叠出的最高罗汉塔的高度，这份表中马戏团员依次编号为1到N。

输入描述:
首先一个正整数N，表示人员个数。
之后N行，每行三个数，分别对应马戏团员编号，体重和身高。


输出描述:
正整数m，表示罗汉塔的高度。

输入例子:
6
1 65 100
2 75 80
3 80 100
4 60 95
5 82 101
6 81 70

输出例子:
4
 */
/**
 * 分析过程   即有几个人可以使用
 * 满足 身高和体重的排序 的 所有组合  的高度和最高的
 *
 * 二维数组的排序   转化数组结构
 */

function deal($arr){
	$narr=[];
	foreach ($arr as $k => $v) {
		foreach ($v as $ke => $va) {
			$narr[$ke][$k]=$va;
		}
	}
	$arr1=$narr;
	$arr2=$narr;
	array_multisort($arr1[1],SORT_NUMERIC,SORT_ASC);
	array_multisort($arr2[2],SORT_NUMERIC,SORT_ASC);
	$sarr1=[];
	$sarr2=[];
	foreach ($arr1[1] as $k => $v) {
		$ks=array_keys($narr[1],$v);
		$sarr1=array_merge($sarr1,$ks);
	}
	foreach ($arr2[2] as $k => $v) {
		$ks=array_keys($narr[2],$v);
		$sarr2=array_merge($sarr2,$ks);
	}
	$sarr1=array_unique($sarr1);
	$sarr2=array_unique($sarr2);
	$sarr1=array_reverse($sarr1);//为了消除不连续的索引
	$sarr2=array_reverse($sarr2);
	
	$resu=check($sarr1,$sarr2);
	// var_dump($resu);
	$na=count(array_keys($resu,-1));
	$ze=count(array_keys($resu,0));
	$po=count(array_keys($resu,1));
	if($na>$po){
		$sum=$na+$ze;
		echo "高度：",$sum;
	}else{
		$sum=$po+$ze;
		echo "高度：",$sum;
	}
}
//问题转变成取两个数组中 顺序相同的子数组的最大长度
//第二个数组相应元素索引前移 标记 为 -1 后移 1 未变 0
function check($arr1,$arr2){
	$l=count($arr1);
	$resu=[];
	foreach ($arr1 as $ke => $va) {
		$k=array_search($va,$arr2);
		if($k<$ke)
			$resu[]=-1;
		elseif($k===$ke)
			$resu[]=0;
		else
			$resu[]=1;
	}
	return $resu;
}


$arr=[
	[1,65,100],
	[2,75,80],
	[3,80,100],
	[4,60,95],
	[5,82,101],
	[6,81,70]
];
deal($arr);