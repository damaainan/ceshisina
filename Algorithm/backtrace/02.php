<?php

/**
 *PHP实现的回溯算法
问题：
 一头大牛驼2袋大米，一头中牛驼一袋大米，两头小牛驼一袋大米，请问100袋大米需要多少头大牛，多少头中牛，多少头小牛？
 */

// 没有关于牛的限制条件 无法计算最优解 

/*
 * k = 2x + y + 1/2z
取值范围
 * 0 <= x <= 1/2k
 * 0 <= y <= k
 * 0 <= z < = 2k
 * x,y,z最大值 2k
 */


$daMi   = 100;
$result = array();
function isOk($t, $daMi, $result)
{
/*{{{*/
    $total   = 0;
    $hash    = array();
    $hash[1] = 2;
    $hash[2] = 1;
    $hash[3] = 0.5;
    for ($i = 1; $i <= $t; $i++) {
        $total += $result[$i] * $hash[$i];
    }
    if ($total <= $daMi) {
        return true;
    }
    return false;
} /*}}}*/
function backtrack($t, $daMi, $result)
{
/*{{{*/
    //递归出口
    if ($t > 3) {
        //输出最优解
        if ($daMi == (2 * $result[1] + $result[2] + 0.5 * $result[3])) {
            echo "最优解，大米:${daMi},大牛：$result[1],中牛： $result[2]，小牛：$result[3]\r\n";
        }
        return;
    }
    for ($i = 0; $i <= 2 * $daMi; $i++) {
        $result[$t] = $i;
        //剪枝
        if (isOk($t, $daMi, $result)) {
            backtrack($t + 1, $daMi, $result);
        }
        $result[$t] = 0;
    }
} /*}}}*/
backtrack(1, $daMi, $result);
