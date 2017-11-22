<?php 
/**
 * 两种排序方法
考拉有n个字符串字符串，任意两个字符串长度都是不同的。考拉最近学习到有两种字符串的排序方法： 1.根据字符串的字典序排序。例如：
"car" < "carriage" < "cats" < "doggies < "koala"
2.根据字符串的长度排序。例如：
"car" < "cats" < "koala" < "doggies" < "carriage"
考拉想知道自己的这些字符串排列顺序是否满足这两种排序方法，考拉要忙着吃树叶，所以需要你来帮忙验证。 
输入描述:
输入第一行为字符串个数n(n ≤ 100)
接下来的n行,每行一个字符串,字符串长度均小于100，均由小写字母组成


输出描述:
如果这些字符串是根据字典序排列而不是根据长度排列输出"lexicographically",

如果根据长度排列而不是字典序排列输出"lengths",

如果两种方式都符合输出"both"，否则输出"none"

输入例子:
3
a
aa
bbb

输出例子:
both
 */

function deal($arr){
	$f1=checkDic($arr);
	$f2=checkLen($arr);
	if($f1==1 && $f2==1){
		echo "both";
	}elseif($f1==1 && $f2==0){
		echo 'lexicographically';
	}elseif($f1==0 && $f2==1){
		echo 'lengths';
	}else{
		echo 'none';
	}
}
function checkDic($arr){
	$len=count($arr);
	for($i=0;$i<$len-1;$i++){
		$n1=strlen($arr[$i]);
		$n2=strlen($arr[$i+1]);
		$n=$n1<$n2?$n1:$n2;
		echo 'n==',$n;
		for($j=0;$j<$n;$j++){
			if(ord($arr[$i][$j])<ord($arr[$i+1][$j])){
				break;
			}
			if(ord($arr[$i][$j])==ord($arr[$i+1][$j])){
				continue;
			}
			if(ord($arr[$i][$j])>ord($arr[$i+1][$j])){
				return 0;
			}
		}
	}
	return 1;
}
function checkLen($arr){
	$len=count($arr);
	for($i=0;$i<$len-1;$i++){
		if(strlen($arr[$i])>strlen($arr[$i+1])){
			return 0;
		}
	}
	return 1;
}
$arr=["car" , "carriage" , "cats" , "doggies" , "koala"];
deal($arr);