<?php
/**
 *扎金花
两个搜狐的程序员加了一个月班，终于放假了，于是他们决定扎金花渡过愉快的假期 。

游戏规则：
共52张普通牌，牌面为2,3,4,5,6,7,8,9,10,J,Q,K,A之一，大小递增，各四张； 每人抓三张牌。两人比较手中三张牌大小，大的人获胜。

对于牌型的规则如下：
1.三张牌一样即为豹子
2.三张牌相连为顺子（A23不算顺子）
3.有且仅有两张牌一样为对子 豹子>顺子>对子>普通牌型 在牌型一样时，比较牌型数值大小（如AAA>KKK,QAK>534，QQ2>10104） 在二人均无特殊牌型时，依次比较三张牌中最大的。大的人获胜，如果最大的牌一样，则比较第二大，以此类推（如37K>89Q） 如二人牌面相同，则为平局。


输入描述:
输入两个字符串代表两个玩家的牌（如"10KQ" "354"），先输入的作为玩家1，后输入的作为玩家2


输出描述:
1 代表 玩家1赢 0 代表 平局 -1 代表 玩家2赢 -2 代表不合法的输入

输入例子:
KQ3 3Q9
10QA 6102
5810 7KK
632 74J
10102 K77
JKJ 926
68K 27A

输出例子:
1
1
-1
-1
1
1
-1
 */
/**
 * 转化成比较数字
 *
 * 怎样减少比较的次数  抽象出一种特征
 * 1. 去重   元素个数相等 不等
 * 2. 不等
 * 3. 相等  豹子 顺子 对子 普通
 */
function init($str1,$str2){
	$arr1=str2array($str1);
	$arr2=str2array($str2);
	// var_dump($arr1);
	// var_dump($arr2);
	deal($arr1,$arr2);
}
function str2array($str){
	$s=['2','3','4','5','6','7','8','9','10','J','Q','K','A'];
	$arr=str_split($str);
	$narr=[];
	for($i=0,$len=count($arr);$i<$len;$i++){
		if(!in_array($arr[$i],$s) ){
			$narr[]=$arr[$i].$arr[$i+1];
			$i++;
		}else{
			$narr[]=$arr[$i];
		}
	}
	return $narr;
}

function deal($arr1,$arr2){
	$arr1=change($arr1);
	$arr2=change($arr2);
	$k1=check($arr1);
	$k2=check($arr2);
	if($k1!=$k2){
		if($k1<$k2)
			echo 1;
		else
			echo -1;
	}else{
		// 分 对子 和其他
		if($k1==2){//比较对子的大小
			$dd1=dealD($arr1);
			$dd2=dealD($arr2);
			if($dd1>$dd2)
				echo 1;
			else
				echo -1;
			
		}else{
			for ($j=count($arr1)-1;$j>0;$j--) {
				if($arr1[$j]>$arr2[$j]){
					echo 1;
					return;
				}
				if($arr1[$j]<$arr2[$j]){
					echo -1;
					return;
				}
			}
			echo 0;
		}
	}


}
function dealD($arr){//将数组的比较转化成 数的大小的比较
	$a=array_unique($arr);
	$sum=0;
	foreach ($a as $key => $value) {
		$k=array_keys($arr,$a[$key]);
		if(count($k)==2)
			$sum+=$a[$key]*100;
		else
			$sum+=$a[$key];
	}
	return $sum;
}
function change($arr){
	$tu=["2"=>2,"3"=>3,"4"=>4,"5"=>5,"6"=>6,"7"=>7,"8"=>8,"9"=>9,"10"=>10,"J"=>11,"Q"=>12,"K"=>13,"A"=>14];
	foreach ($arr as $ke => $va) {
		$narr[]=$tu[$va];
	}
	sort($narr);
	return $narr;
}

function check($arr){
	// var_dump($arr);
	sort($arr);
	$arr=array_unique($arr);
	$len=count($arr);
	if($len==1){
		return 1;
	}elseif($len==2){
		return 2;
	}else{
		if($arr[2]-$arr[1]==1 && $arr[1]-$arr[0]==1){
			return 3;
		}else{
			return 4;
		}
	}
}

$str1="KQ3";
$str2="3Q9";
init($str1,$str2);
echo "<br/>";

$str1="10QA";
$str2="6102";
init($str1,$str2);
echo "<br/>";
$str1="5810";
$str2="7KK";
init($str1,$str2);
echo "<br/>";
$str1="632";
$str2="74J";
init($str1,$str2);
echo "<br/>";
$str1="10102";
$str2="K77";
init($str1,$str2);
echo "<br/>";