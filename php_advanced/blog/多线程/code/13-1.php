<?php
// 给当前php进程安装一个alarm信号处理器
// 当进程收到alarm时钟信号后会作出动作
pcntl_signal(SIGALRM, function () {
    echo "tick." . PHP_EOL;
});
// 定义一个时钟间隔时间，1秒钟吧
$tick = 1;
while (true) {
    // 当过了tick时间后，向进程发送一个alarm信号
    pcntl_alarm($tick);
    // 分发信号，呼唤起安装好的各种信号处理器
    pcntl_signal_dispatch();
    // 睡个1秒钟，继续
    sleep($tick);
}
