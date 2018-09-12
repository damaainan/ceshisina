<?php
$pid = pcntl_fork();
if (0 > $pid) {
    exit('fork error.' . PHP_EOL);
} else if (0 < $pid) {
    // 在父进程中
    // 给父进程安装一个SIGCHLD信号处理器
    pcntl_signal(SIGCHLD, function () use ($pid) {
        echo "收到子进程退出" . PHP_EOL;
        pcntl_waitpid($pid, $status, WNOHANG);
    });
    cli_set_process_title('php father process');
    // 父进程不断while循环，去反复执行pcntl_waitpid()，从而试图解决已经退出的子进程
    while (true) {
        sleep(1);
        // 注释掉原来老掉牙的代码，转而使用pcntl_signal_dispatch()
        //pcntl_waitpid( $pid, &$status, WNOHANG );
        pcntl_signal_dispatch();
    }
} else if (0 == $pid) {
    // 在子进程中
    // 子进程休眠3秒钟后直接退出
    cli_set_process_title('php child process');
    sleep(20);
    exit;
}
