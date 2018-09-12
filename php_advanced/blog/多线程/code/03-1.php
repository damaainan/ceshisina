<?php
$pid = pcntl_fork();
if ($pid > 0) {
    echo "我是父亲" . PHP_EOL;
} else if (0 == $pid) {
    echo "我是儿子" . PHP_EOL;
} else {
    echo "fork失败" . PHP_EOL;
}
