<?php
/**
 *寻找Coder
请设计一个高效算法，再给定的字符串数组中，找到包含"Coder"的字符串(不区分大小写)，并将其作为一个新的数组返回。结果字符串的顺序按照"Coder"出现的次数递减排列，若两个串中"Coder"出现的次数相同，则保持他们在原数组中的位置关系。
给定一个字符串数组A和它的大小n，请返回结果数组。保证原数组大小小于等于300,其中每个串的长度小于等于200。同时保证一定存在包含coder的字符串。
测试样例：
["i am a coder","Coder Coder","Code","Coder Coder",],3
返回：["Coder Coder","i am a coder"]
 */
/**
 * 用子元素 包含 coder 的个数进行排序  
 * 保持他们在原数组中的位置关系  原来的位置？？？
 * 构造二维数组
 */

function deal($arr){
	$l=count($arr);
	$narr=[];
	for($i=0;$i<$l;$i++){
		$s=preg_match_all('/coder/i', $arr[$i], $matches);//匹配次数
		if($s==0)
			continue;
		$narr[$i]=$s;//索引为原索引的值
	}
	arsort($narr);//保持索引关系 逆序
	$narr=rightTurn($narr);
	$resu=[];
	foreach ($narr as $k=>$va) {
		$resu[]=$arr[$k];
	}
	var_dump($resu);//结果
	
}
function rightTurn($arr){//传入的数组索引不连续
	//找出相同的值 重新索引
	//一段一段的解决
	$karr=array_keys($arr);
	$varr=array_values($arr);
	$flag=1;
	$temp=$varr[0];
	$s=0;
	for($i=1,$l=count($varr);$i<$l;$i++){
		if($varr[$i]==$temp){//最后一个元素的比较
			$flag++;
			if($i==$l-1){
				$nkarr=array_slice($karr,$s,$flag);
				sort($nkarr);//只排序索引即可
				array_splice($karr,$s,$flag,$nkarr);
			}
		}else{
			if($flag>1){
				// $narr=array_slice($varr,$s,$flag);
				$nkarr=array_slice($karr,$s,$flag);
				// sort($narr);//排序的问题
				sort($nkarr);//只排序索引即可
				// array_splice($varr,$s,$flag,$narr);
				array_splice($karr,$s,$flag,$nkarr);
			}
			$flag=1;
			$temp=$varr[$i];
			$s=$i;
		}
	}
	$t=[];
	foreach ($karr as $ke=>$va) {//找回原来的索引关系
		$t[$va]=$arr[$va];
	}
	return $t;
}
deal(["i am a coder","Coder Coder","Code","Coder12 Coder",'coder']);