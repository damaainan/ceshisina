<?php
$pid = pcntl_fork();
if ($pid > 0) {
    // 显示父进程的进程ID，这个函数可以是getmypid()，也可以用posix_getpid()
    echo "Father PID:" . getmypid() . PHP_EOL;
    // 让父进程停止两秒钟，在这两秒内，子进程的父进程ID还是这个父进程
    sleep(2);
} else if (0 == $pid) {
    // 让子进程循环10次，每次睡眠1s，然后每秒钟获取一次子进程的父进程进程ID
    for ($i = 1; $i <= 10; $i++) {
        sleep(1);
        // posix_getppid()函数的作用就是获取当前进程的父进程进程ID
        echo posix_getppid() . PHP_EOL;
    }
} else {
    echo "fork error." . PHP_EOL;
}
