<?php

/**
 * @author jiangkejun
 * @name Bm (Boyer-Moore算法) for PHP5
 * @version 1.1.11 beta
 * @since 2013.5.9
 * @link http://developer.51cto.com/art/201305/392552.htm
 * @uses 文本文档里的Ctrl+F
 * @see 没有进行数组多项匹配，所以写的beta，学习用
 *
 * @param string $haystack
 * @param mixed  $needle
 */
function boyerMoore($haystack, $needle)
{
    $len = strlen($needle);
    // 起始句柄
    $i = strlen($needle) - 1;
    // 坏字符点
    $bad = 0;
    $tmp = '';
    // 好后缀点
    $good = 0;

    while ($i < strlen($haystack)) {
        // 最后字优先匹配原则
        if ($haystack{$i} == $needle{$len - 1}) {
            $tmp = $haystack{$i} . $tmp;
            // 好后缀计算 (后移位数 = 好后缀的位置 - 搜索词中的上一次出现位置)
            for ($v = 1; $v < $len; $v++) {
                if ($needle{$len - 1 - $v} == $haystack{$i - $v}) {
                    $tmp = $haystack{$i - $v} . $tmp;
                } else {
                    $tlen = strlen($tmp);
                    if ($tlen == 1) {
                        $good = $bad = $len;
                    } else {
                        if ($index2 = strpos(substr($tmp, 0, $tlen - 1), $haystack{$i})) {
                            $good = ($len - 1) - $index2;
                            $bad  = ($len - 1 - $tlen) - $index2;
                        } else {
                            $good = ($len - 1) - (-1);
                            $bad  = ($len - 1 - $tlen) - (-1);
                        }
                    }
                    $tmp = '';
                }
            }
            /* result is here */
            if ($tmp) {
                echo "Found between " . ($i - $len + 1) . " to $i ";
                break;}
            // 这样展现思路会更清晰点
            $i += max($good, $bad);
        } else {
            // 坏字符计算  (后移位数 = 坏字符的位置 - 搜索词中的上一次出现位置)
            // 结尾不匹配但在字符串内
            if ($index = strpos($needle, $haystack{$i})) {
                $bad = ($len - 1) - ($index);
            }
            // 反之
            else {
                $bad = ($len - 1) - (-1);
            }
            $i += $bad;
        }
    }
}

$a = "HERE IS A SIMPLE EXAMPLE";
$b = "EXAMPLE";
boyerMoore($a, $b);
