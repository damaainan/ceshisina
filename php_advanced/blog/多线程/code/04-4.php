<?php
$pid = pcntl_fork();
if ($pid > 0) {
    // 下面这个函数可以更改php进程的名称
    cli_set_process_title('php father process');

    // 返回值保存在$wait_result中
    // $pid参数表示 子进程的进程ID
    // 子进程状态则保存在了参数$status中
    // 将第三个option参数设置为常量WNOHANG，则可以避免主进程阻塞挂起，此处父进程将立即返回继续往下执行剩下的代码
    $wait_result = pcntl_waitpid($pid, $status);
    var_dump($wait_result);
    var_dump($status);

    // 让主进程休息60秒钟
    sleep(60);

} else if (0 == $pid) {
    cli_set_process_title('php child process');
    // 让子进程休息10秒钟，但是进程结束后，父进程不对子进程做任何处理工作，这样这个子进程就会变成僵尸进程
    sleep(10);
} else {
    exit('fork error.' . PHP_EOL);
}
