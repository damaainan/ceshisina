<?php
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
        sleep(30);
        exit; // 这里exit掉，避免worker继续执行下面的代码而造成一些问题
        break;

        // 主进程master
    default:
        cli_set_process_title("我是父进程,我的进程id是{$ppid}.");
        pcntl_wait($status); // 挂起父进程，等待并返回子进程状态，防止子进程成为僵尸进程
        break;
}

// ps aux | grep 进程 
// ps aux 
// ps –ajft