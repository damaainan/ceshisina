<?php

// 汉诺塔 
// $topN 移动的盘子数 $from 起始塔座 $inter 中间塔座 $to 目标塔座
function move($topN, $from, $inter, $to) {
    
    if ($topN == 1) {
        echo "盘子1， 从" . $from . "塔座到" . $to . "塔座" . "<br />";
    } else {
        move($topN - 1, $from, $to, $inter);
        echo "盘子" . $topN . "， 从" . $from . "塔座到" . $to . "塔座" . "<br />";
        move($topN - 1, $inter, $from, $to);
    }
    
}


echo move(3, 'A', 'B', 'C');
