<?php
//最长公共子序列

//有错误
function LCS($str1,$str2){
	$len1=strlen($str1);
	$len2=strlen($str2);

	if($len1==0 || $len2==0)
		return 0;

	$record=[];
	// for($i=0;$i<=$len1;$i++){
	// 	$record[$i][0]=0;
	// }
	// for($j=0;$j<=$len2;$j++){
	// 	$record[0][$j]=0;
	// }

	for($i=1;$i<$len1;$i++){
		for($j=1;$j<$len2;$j++){
			if($str1[$i-1]==$str2[$j-1]){
				$record[$i][$j]=isset($record[$i-1][$j-1])?$record[$i-1][$j-1]+1:1;
			}else{
				$record[$i-1][$j]=isset($record[$i-1][$j])?$record[$i-1][$j]:0;
				$record[$i][$j-1]=isset($record[$i][$j-1])?$record[$i][$j-1]:0;

				$record[$i][$j]=max($record[$i-1][$j],$record[$i][$j-1]);
			}
		}
	}
	print_r($record);
	return $record;
}

$str1="abca";
$str2="bc";
$arr=LCS($str1,$str2);
// var_dump($arr);


// 以动态规划的思想来解这个题，用一个二位数组 $dp[][] 来存储各个字符串对应的状态，具体含义百度一下
// http://blog.csdn.net/qq_17765229/article/category/6154160

function lcs1($str1, $str2){
    // 存储生成的二维矩阵
    $dp = array();
    // 最大子串长度
    $max = 0;

    for ($i = 0; $i < strlen($str1); $i++) { 
        for ($j = 0; $j < strlen($str2); $j++) { 
            if ($str1[$i] == $str2[$j]) {
                $dp[$i][$j] = isset($dp[$i-1][$j-1]) ? $dp[$i-1][$j-1] + 1 : 1;
            } else {
                $dp[$i-1][$j] = isset($dp[$i-1][$j]) ? $dp[$i-1][$j] : 0;
                $dp[$i][$j-1] = isset($dp[$i][$j-1]) ? $dp[$i][$j-1] : 0;

                $dp[$i][$j] = $dp[$i-1][$j] > $dp[$i][$j-1] ? $dp[$i-1][$j] : $dp[$i][$j-1];
            }

            $max = $dp[$i][$j] > $max ? $dp[$i][$j] : $max;
        }
    }

    for ($i = 0; $i < strlen($str1); $i++) { 
        for ($j = 0; $j < strlen($str2); $j++) { 
            echo $dp[$i][$j] . " ";
        }
        echo "\n";
    }

    var_dump($max);
}

lcs1("abcbdab", "bdcaba");