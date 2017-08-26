<?php

/**
回溯法解决0-1背包问题的方法
 */

$v_arr = array(11, 21, 31, 33, 43, 53, 55, 65);
$w_arr = array(1, 11, 21, 23, 33, 43, 45, 55);
$n     = count($w_arr);
//测试输出
var_dump(bknap1(110));
//var_dump(bound(139,89,7,110));
function bound($v, $w, $k, $W_total)
{
    global $v_arr, $w_arr, $n;
    $b = $v;
    $c = $w;
    //var_dump($W_total);var_dump($n);var_dump($k);var_dump($v);var_dump($w);
    //die;
    for ($i = $k + 1; $i < $n; $i++) {
        $c = $c + $w_arr[$i];
        //var_dump($W_total);var_dump($c);
        if ($c < $W_total) {
            $b += $v_arr[$i];
        } else {
            //var_dump((1-($c-$W_total)/$w_arr[$i])*$v_arr[$i]);
            $b = $b + (1 - ($c - $W_total) / $w_arr[$i]) * $v_arr[$i];
            return $b;
        }
    }
    /*var_dump('------bound head');
    var_dump($k);
    var_dump($b);
    var_dump('------bound end');*/
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
            //var_dump($cw);var_dump($cp);var_dump($Y_arr);var_dump($k);var_dump($n);
        if ($k == $n) {
            $fp    = $cp;
            $fw    = $cw;
            $k     = $n - 1;
            $X_arr = $Y_arr;
            //bound($cp,$cw,$k,$W_total);
            //var_dump(bound($cp,$cw,$k,$W_total),$fp,$k);die;
            //var_dump($fp);var_dump($fw);var_dump($Y_arr);var_dump($k);var_dump($n);
        } else {
            $Y_arr[$k] = 0;
        }
        //var_dump($Y_arr);var_dump($k);var_dump($n);//die;
        //var_dump(bound($cp,$cw,$k,$W_total),$fp);die;
        while (bound($cp, $cw, $k, $W_total) <= $fp) {
            while ($k >= 0 && $Y_arr[$k] != 1) {
                $k -= 1;
            }
            if ($k < 0) {
                return $X_arr;
            }
            // var_dump($k);
            $Y_arr[$k] = 0;
            $cw -= $w_arr[$k];
            $cp -= $v_arr[$k];
        }
        $k += 1;
    }
}
