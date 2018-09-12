<?php
$host = '0.0.0.0';
$port = 9999;
// 创建一个tcp socket
$listen_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
// 将socket bind到IP：port上
socket_bind($listen_socket, $host, $port);
// 开始监听socket
socket_listen($listen_socket);
// 给主进程换个名字
cli_set_process_title('phpserver master process');
// 按照数量fork出固定个数子进程
for ($i = 1; $i <= 10; $i++) {
    $pid = pcntl_fork();
    if (0 == $pid) {
        cli_set_process_title('phpserver worker process');
        while (true) {
            $conn_socket = socket_accept($listen_socket);
            $msg         = "helloworld\r\n";
            socket_write($conn_socket, $msg, strlen($msg));
            socket_close($conn_socket);
        }
    }
}
// 主进程不可以退出，代码演示比较粗暴，为了不保证退出直接走while循环，休眠一秒钟
// 实际上，主进程真正该做的应该是收集子进程pid，监控各个子进程的状态等等
while (true) {
    sleep(1);
}
socket_close($connection_socket);
