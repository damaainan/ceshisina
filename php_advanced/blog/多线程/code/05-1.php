<?php
$pid = pcntl_fork();
if (0 > $pid) {
    exit('fork error.' . PHP_EOL);
} else if (0 < $pid) {
    // 在父进程中
    cli_set_process_title('php father process');
    // 父进程不断while循环，去反复执行pcntl_waitpid()，从而试图解决已经退出的子进程
    while (true) {
        sleep(1);
        pcntl_waitpid($pid, &$status, WNOHANG);
    }
} else if (0 == $pid) {
    // 在子进程中
    // 子进程休眠3秒钟后直接退出
    cli_set_process_title('php child process');
    sleep(20);
    exit;
}
