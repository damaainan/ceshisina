<?php
/**
 * 互斥锁

什么情况下会用到互斥锁？在你需要控制多个线程同一时刻只能有一个线程工作的情况下可以使用。

下面我们举一个例子，一个简单的计数器程序，说明有无互斥锁情况下的不同。

我们使用文件 ./counter.txt保存计数器值，每次打开该文件将数值加一，然后写回文件。当多个线程同时操作一个文件的时候，就会线程运行先后取到的数值不同，写回的数值也不同，最终计数器的数值会混乱。

没有加入锁的结果是计数始终被覆盖，最终结果是2

而加入互斥锁后，只有其中的一个进程完成加一工作并释放锁，其他线程才能得到解锁信号，最终顺利完成计数器累加操作
 */

$counter = 0;
//$handle=fopen("php://memory", "rw");
//$handle=fopen("php://temp", "rw");
$handle = fopen("./counter.txt", "a");
fwrite($handle, $counter);
fclose($handle);

class CounterThread extends Thread {
    public function __construct($mutex = null) {
        $this->mutex = $mutex;
        $this->handle = fopen("/tmp/counter.txt", "w+");
    }
    public function __destruct() {
        fclose($this->handle);
    }
    public function run() {
        if ($this->mutex) {
            $locked = Mutex::lock($this->mutex);
        }

        $counter = intval(fgets($this->handle));
        $counter++;
        rewind($this->handle);
        fputs($this->handle, $counter);
        printf("Thread #%lu says: %s\n", $this->getThreadId(), $counter);

        if ($this->mutex) {
            Mutex::unlock($this->mutex);
        }

    }
}

//没有互斥锁
for ($i = 0; $i < 50; $i++) {
    $threads[$i] = new CounterThread();
    $threads[$i]->start();

}

//加入互斥锁
$mutex = Mutex::create(true);
for ($i = 0; $i < 50; $i++) {
    $threads[$i] = new CounterThread($mutex);
    $threads[$i]->start();

}

Mutex::unlock($mutex);
for ($i = 0; $i < 50; $i++) {
    $threads[$i]->join();
}
Mutex::destroy($mutex);