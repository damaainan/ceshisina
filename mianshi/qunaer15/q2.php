<?php
/**
 *首个重复字符
对于一个字符串，请设计一个高效算法，找到第一次重复出现的字符。
给定一个字符串(不一定全为字母)A及它的长度n。请返回第一个重复出现的字符。保证字符串中有重复字符，字符串的长度小于等于500。
测试样例：
"qywyer23tdd",11
返回：y
 */

// 分割  去重 顺序索引 值为null
function deal($str) {
    $l = strlen($str);
    $narr = [];
    for ($i = 0; $i < $l; $i++) {
        $s = ord($str[$i]);
        if (($s > 64 && $s < 91) || ($s > 96 && $s < 123)) {
            if (!in_array($s, $narr)) {
                $narr[] = $s;
            } else {
                echo "first==  ", chr($s);
                break;
            }
        }
    }
}

deal("qywyer23tdd");