<?php

/**
回溯法解决0-1背包问题的方法
 */

$v_arr = array(11, 21, 31, 33, 43, 53, 55, 65); // 价值
$w_arr = array(1, 11, 21, 23, 33, 43, 45, 55); // 重量
$n     = count($w_arr);
//测试输出
print_r(bknap1(110)); // 输入总价值

function bound($v, $w, $k, $W_total)
{
    global $v_arr, $w_arr, $n;
    $b = $v;
    $c = $w;
    for ($i = $k + 1; $i < $n; $i++) {
        $c = $c + $w_arr[$i];
        if ($c < $W_total) {
            $b += $v_arr[$i];
        } else {
            $b = $b + (1 - ($c - $W_total) / $w_arr[$i]) * $v_arr[$i];
            return $b;
        }
    }
    return $b;
}
function bknap1($W_total)
{
    global $v_arr, $w_arr, $n;
    $cw = $cp = 0;
    $k  = 0;
    $fp = -1;
    while (true) {
        while ($k < $n && $cw + $w_arr[$k] <= $W_total) {
            $cw += $w_arr[$k];
            $cp += $v_arr[$k];
            $Y_arr[$k] = 1;
            $k += 1;
        }
        if ($k == $n) {
            $fp    = $cp;
            $fw    = $cw;
            $k     = $n - 1;
            $X_arr = $Y_arr;
        } else {
            $Y_arr[$k] = 0;
        }
        while (bound($cp, $cw, $k, $W_total) <= $fp) {
            while ($k >= 0 && $Y_arr[$k] != 1) {
                $k -= 1;
            }
            if ($k < 0) {
                return $X_arr;
            }
            $Y_arr[$k] = 0;
            $cw -= $w_arr[$k];
            $cp -= $v_arr[$k];
        }
        $k += 1;
    }
}
