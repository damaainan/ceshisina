<?php
$pid = pcntl_fork();
if ($pid < 0) {
    exit('fork error.');
} else if ($pid > 0) {
    // 主进程退出
    exit();
}
// 子进程继续执行

// 最关键的一步来了，执行setsid函数！
if (!posix_setsid()) {
    exit('setsid error.');
}

// 理论上一次fork就可以了
// 但是，二次fork，这里的历史渊源是这样的：在基于system V的系统中，通过再次fork，父进程退出，子进程继续，保证形成的daemon进程绝对不会成为会话首进程，不会拥有控制终端。

$pid = pcntl_fork();
if ($pid < 0) {
    exit('fork error');
} else if ($pid > 0) {
    // 主进程退出
    exit;
}

// 子进程继续执行

// 啦啦啦，啦啦啦，啦啦啦，已经变成daemon啦，开心
cli_set_process_title('testtesttest');
// 循环1000次，每次睡眠1s，输出一个字符test
for ($i = 1; $i <= 1000; $i++) {
    sleep(1);
    echo "test" . PHP_EOL;
}
