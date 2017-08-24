<?php

//kmp算法是一种改进的字符串匹配算法，由D.E.Knuth与V.R.Pratt和J.H.Morris同时发现，因此人们称它为克努特——莫里斯——普拉特操作（简称KMP算法）。KMP算法的关键是根据给定的模式串W1,m,定义一个next函数。next函数包含了模式串本身局部匹配的信息


/*

字符串匹配KMP算法的PHP语言实现

*/

function KMP($str) {
    $K = array(0);
    $M = 0;
    $strLen = strlen($str);
    for($i=1; $i<$strLen; $i++) {
        if ($str[$i] == $str[$M]) {
            $K[$i] = $K[$i-1] + 1;
            $M ++;
        } else {
            $M = 0;
            $K[$i] = $K[$M];
        }
    }
    return $K;
}
 
// KMP查找

function KMPMatch($src, $par) {
    $K = KMP($par);
 
    $srcLen = strlen($src);
    $parLen = strlen($par);
 
    for($i=0,$j=0; $i<$srcLen; ) {
 
        
//返回完全匹配的位置

        if ($j == $parLen) return $i-$j;
 
        
//打印匹配过程

        echo $i."  ".$j. " {$src[$i]}-{$par[$j]} \r\n";
 
        if ($par[$j] === $src[$i]) {
            
//记录匹配个数

            $j++;
            $i++;
        } else {
            if ($j === 0) {
                $i++;
            }
            $j = $K[$j-1 >= 0 ? $j -1 : 0];
        }
    }
    return false;
}
 
// 测试下是否可用

$src = 'BBC ABCDAB ABCDABCDABDE';
$par = 'ABCDABD';
 
// 匹配值

echo "部分匹配值:", implode(" ", KMP($par)), "\r\n";
// 在给定的字符串中查找特定字符（串）

echo  KMPMatch($src, $par), "\r\n";