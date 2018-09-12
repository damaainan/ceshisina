<?php
// 管道文件绝对路径
$pipe_file = __DIR__ . DIRECTORY_SEPARATOR . 'test.pipe';
// 如果这个文件存在，那么使用posix_mkfifo()的时候是返回false，否则，成功返回true
if (!file_exists($pipe_file)) {
    if (!posix_mkfifo($pipe_file, 0666)) {
        exit('create pipe error.' . PHP_EOL);
    }
}
// fork出一个子进程
$pid = pcntl_fork();
if ($pid < 0) {
    exit('fork error' . PHP_EOL);
} else if (0 == $pid) {
    // 在子进程中
    // 打开命名管道，并写入一段文本
    $file = fopen($pipe_file, "w");
    fwrite($file, "helo world.");
    exit;
} else if ($pid > 0) {
    // 在父进程中
    // 打开命名管道，然后读取文本
    $file = fopen($pipe_file, "r");
    // 注意此处fread会被阻塞
    $content = fread($file, 1024);
    echo $content . PHP_EOL;
    // 注意此处再次阻塞，等待回收子进程，避免僵尸进程
    pcntl_wait($status);
}
