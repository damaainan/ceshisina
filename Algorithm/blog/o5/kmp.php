<?php 

/*

kmp算法有两个关键点：1.计算模式字符串的部分匹配表（这时候，自己跟自己比较）2.匹配主串时候，主串字符只遍历一遍，匹配时候，根据模式串的部分匹配表计算模式串应该移动的位置。kmp算法时间复杂度为O(m+n);
 */

function kmp_next($string){
    $length = strlen($string);//获取字符串长度
    $next[0] =0;
    $j =0;
    $i = 1;
    while($i<$length){
        if($string{$j} == $string{$i}){
            $j++;
            $next[$i]= $j;
            $i++;
        }else if($j == 0){
            $next[$i] =$j;
            $i++;
        }else{
            $j=$next[$j];
        }
    }
    return $next;
}

function kmp($text,$mode){
    $t_length = strlen($text);
    $m_length = strlen($mode);
    if($t_length < $m_length){
        return -1;
    }
    $arr = kmp_next($mode);
    $j=0;$i=0;
    while($i<$t_length){
        if($text{$i}==$mode{$j}){
            if($j < $m_length-1){
                $j++;$i++;
            }else{
                return $i-$m_length+1;
            }
        }else if($j==0){
            $i++;
        }else{
            $j=$arr[$j-1];
        }
    }
    return -1;
}


$string = 'BBC ABCDAB ABCDABCDABDE';
$mode = 'ABCDABD';

$key = kmp($string,$mode);
var_dump(kmp_next($mode));
var_dump($key);
var_dump(substr($string,$key,strlen($mode)));    