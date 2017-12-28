<?php
// 线程同步

// 有些场景我们不希望 thread->start() 就开始运行程序，而是希望线程等待我们的命令。

// $thread->wait();作用是 thread->start()后线程并不会立即运行，只有收到 $thread->notify(); 发出的信号后才运行

$tmp = tempnam(__FILE__, 'PHP');
$key = ftok($tmp, 'a');

$shmid = shm_attach($key);
$counter = 0;
shm_put_var($shmid, 1, $counter);

class CounterThread extends Thread {
    public function __construct($shmid) {
        $this->shmid = $shmid;
    }
    public function run() {

        $this->synchronized(function ($thread) {
            $thread->wait();
        }, $this);

        $counter = shm_get_var($this->shmid, 1);
        $counter++;
        shm_put_var($this->shmid, 1, $counter);

        printf("Thread #%lu says: %s\n", $this->getThreadId(), $counter);
    }
}

for ($i = 0; $i < 100; $i++) {
    $threads[] = new CounterThread($shmid);
}
for ($i = 0; $i < 100; $i++) {
    $threads[$i]->start();

}

for ($i = 0; $i < 100; $i++) {
    $threads[$i]->synchronized(function ($thread) {
        $thread->notify();
    }, $threads[$i]);
}

for ($i = 0; $i < 100; $i++) {
    $threads[$i]->join();
}
shm_remove($shmid);
shm_detach($shmid);