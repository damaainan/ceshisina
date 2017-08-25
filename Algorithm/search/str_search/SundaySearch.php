<?php
/*
 *@param $pattern 模式串
 *@param $text 待匹配串
 */
function mySunday($pattern = '', $text = '')
{
    if (!$pattern || !$text) {
        return false;
    }

    $pattern_len = mb_strlen($pattern);
    $text_len    = mb_strlen($text);
    if ($pattern_len >= $text_len) {
        return false;
    }

    $i = 0;
    for ($i = 0; $i < $pattern_len; $i++) {
        //组装以pattern中的字符为下标的数组
        $shift[$pattern[$i]] = $pattern_len - $i;
    }
    while ($i <= $text_len - $pattern_len) {
        $nums = 0; //匹配上的字符个数
        while ($pattern[$nums] == $text[$i + $nums]) {
            $nums++;
            if ($nums == $pattern_len) {
                return "The first match index is $i\r\n";
            }
        }
        if ($i + $pattern_len < $text_len && isset($shift[$text[$i + $pattern_len]])) { //判断模式串后一位字符是否在模式串中
            $i += $shift[$text[$i + $pattern_len]]; //对齐该字符
        } else {
            $i += $pattern_len; //直接滑动pattern_len位
        }
    }
}
$text    = "I am testing mySunday on sunday!";
$pattern = "sunday";
echo mySunday($pattern, $text);
