<?php
// 初始化一个 number变量 数值为1
$number = 1;
$pid    = pcntl_fork();
if ($pid > 0) {
    $number += 1;
    echo "我是父亲，number+1 : { $number }" . PHP_EOL;
} else if (0 == $pid) {
    $number += 2;
    echo "我是父亲，number+2 : { $number }" . PHP_EOL;
} else {
    echo "fork失败" . PHP_EOL;
}
