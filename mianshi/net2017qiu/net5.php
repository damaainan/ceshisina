<?php
/**
 * 数字翻转
对于一个整数X，定义操作rev(X)为将X按数位翻转过来，并且去除掉前导0。例如:
如果 X = 123，则rev(X) = 321;
如果 X = 100，则rev(X) = 1.
现在给出整数x和y,要求rev(rev(x) + rev(y))为多少？
输入描述:
输入为一行，x、y(1 ≤ x、y ≤ 1000)，以空格隔开。


输出描述:
输出rev(rev(x) + rev(y))的值

输入例子:
123 100

输出例子:
223
 */
function deal($n, $m) {
    $n = rev($n);
    $m = rev($m);
    return rev($n + $m);
}
function rev($n) {
    $n = dealZero($n);
    $str = "" . $n;
    $str = strrev($str);
    return 0 + $str;
}
function dealZero($n) {
    $k = $n % 10;
    if ($k == 0) {
        $j = $n / 10;
        $m = dealZero($j);
        return $m;
    } else {
        return $n;
    }
}

$j = deal(123, 100);
echo 'j==', $j;