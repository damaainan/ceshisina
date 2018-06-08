<?php

/**
回溯法解决0-1背包问题的方法
 */

$v_arr = array(11, 21, 31, 33, 43, 53, 55, 65); // 价值
$w_arr = array(1, 11, 21, 23, 33, 43, 45, 55); // 重量
$n     = count($w_arr);
//测试输出
print_r(bknap1(110)); // 输入总价值

function bound($v, $w, $k, $W_total) // 计算总价值 
{
    global $v_arr, $w_arr, $n;
    $b = $v; // 价值
    $c = $w; // 重量
    for ($i = $k + 1; $i < $n; $i++) {
        $c = $c + $w_arr[$i];
        if ($c < $W_total) {
            $b += $v_arr[$i];
        } else { // 后续总重 大于等于 前面总重时 
            $b = $b + (1 - ($c - $W_total) / $w_arr[$i]) * $v_arr[$i];
            return $b;
        }
    }
    return $b;
}
function bknap1($W_total)
{
    global $v_arr, $w_arr, $n;
    $cw = $cp = 0; // 当前总重 当前总价值
    $k  = 0;
    $fp = -1;
    while (true) {
        while ($k < $n && $cw + $w_arr[$k] <= $W_total) { // 第一个循环 条件1：范围内  条件2：重量符合要求
            $cw += $w_arr[$k]; // 当前总重
            $cp += $v_arr[$k]; // 当前总价值
            $Y_arr[$k] = 1; // 取该值
            $k += 1; // 指针前移
        }
        if ($k == $n) { // 如果是最后一个元素 
            $fp    = $cp; // 当前总价值
            $fw    = $cw;
            $k     = $n - 1; // 指针后退一个
            $X_arr = $Y_arr; // 取该值 更新结果
        } else {
            $Y_arr[$k] = 0; // 放弃该值
        }
        while (bound($cp, $cw, $k, $W_total) <= $fp) { // 验证 只有 加上后面可能的价值 不大于 当前总价值时
            // 当回退足够多时 条件不满足 跳出过滤
            while ($k >= 0 && $Y_arr[$k] != 1) { // 当前元素不符合要求 
                $k -= 1; // 回退 1 
            }
            if ($k < 0) { // 回退出界 返回当前结果
                return $X_arr; // 结果
            }
            $Y_arr[$k] = 0; // 清空前一元素
            $cw -= $w_arr[$k]; // 当前重量减去
            $cp -= $v_arr[$k];
        }
        $k += 1; // 当回退足够多时 条件不满足 跳出过滤 再从下一个开始
    }
}
