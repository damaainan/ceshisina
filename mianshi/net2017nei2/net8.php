<?php 
/**
 * 小易喜欢的单词
小易喜欢的单词具有以下特性：
1.单词每个字母都是大写字母
2.单词没有连续相等的字母
3.单词没有形如“xyxy”(这里的x，y指的都是字母，并且可以相同)这样的子序列，子序列可能不连续。
例如：
小易不喜欢"ABBA"，因为这里有两个连续的'B'
小易不喜欢"THETXH"，因为这里包含子序列"THTH"
小易不喜欢"ABACADA"，因为这里包含子序列"AAAA"
小易喜欢"A","ABA"和"ABCBA"这些单词
给你一个单词，你要回答小易是否会喜欢这个单词。 
输入描述:
输入为一个字符串，都由大写字母组成，长度小于100


输出描述:
如果小易喜欢输出"Likes",不喜欢输出"Dislikes"

输入例子:
AAA

输出例子:
Dislikes
 */
function deal($str){

}

function checkOrd($str){
	//65-90
	$len=strlen($str);
	for($i=0;$i<$len;$i++){
		if(ord($str[$i])>90 || ord($str[$i])<65){
			return 0;
			break;
		}
	}
}
function checkCon($str){
	//检查连续
	$len=strlen($str);
	for($i=0;$i<$len-1;$i++){
		if($str[$i]==$str[$i+1]){
			return 0;
			break;
		}
	}
}
function checkSub($str){
	$len=strlen($str);
	for($k=1;$k<ceil($len/2);$k++){//子串数目减一
		for($i=0;$i<$len-$k;$i++){//子串开头字符

		}
	}
}