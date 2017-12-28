<?php

// 多线程只有在处理费时较长的过程时才有优势 例如 sleep

// 空循环以及像 echo 这样的过程 是劣势，因为 新建多个进程更费时

//这里用一个函数，表示操作日志，每操作一次花1秒的时间
function doThings($i) {
    //  Write log file
    // echo $i;
    // sleep(1);
}

$s = microtime(true);
for ($i = 1; $i <= 10; $i++) {
    doThings($i);
}
$e = microtime(true);
echo "For循环：" . ($e - $s) . "\n";

#############################################
class MyThread extends Thread {
    private $i = null;

    public function __construct($i) {
        $this->i = $i;
    }

    public function run() {
        doThings($this->i);
    }
}

$s = microtime(true);
$work = array();
for ($i = 1; $i <= 10; $i++) {
    $work[$i] = new MyThread($i);
    $work[$i]->start();
}
$e = microtime(true);
echo "多线程：" . ($e - $s) . "\n";