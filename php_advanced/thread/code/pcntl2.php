<?php
echo '开始时间：'.date('H:i:s', time())."\n";

$cmds = [
    ['./job.php', 0, 50000],//执行脚本，并传参
    ['./job.php', 50000, 100000]
];
for ($i = 0; $i < 2; $i++){
    $ppid = posix_getpid();// 获取当前进程PID
    $pid  = pcntl_fork(); //创建进程
    switch ($pid){
        // 创建进程错误
        case -1:
            throw new Exception('fork子进程失败!');
            break;

        // 子进程worker
        case 0:
            $cpid = posix_getpid();
            cli_set_process_title("我是{$ppid}的子进程,我的进程id是{$cpid}.");

            // 执行业务脚本
            pcntl_exec('/usr/local/php/bin/php', $cmds[$i]);

            exit; // 这里exit掉，避免worker继续执行下面的代码而造成一些问题
            break;
    }
}

// 等待子进程结束
while (pcntl_waitpid(0, $status) != -1) {
    $status = pcntl_wexitstatus($status);
    echo '子进程结束时间：'.date('H:i:s', time())."\n";
}