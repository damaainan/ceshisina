<?php 
class vote extends Thread {
    public $res    = '';
    public $url    = array();
    public $name   = '';
    public $runing = false;
    public $lc     = false;

    public function __construct($name) {
        $this->res    = '暂无,第一次运行.';
        $this->param    = 0;
        $this->lurl   = 0;
        $this->name   = $name;
        $this->runing = true;
        $this->lc     = false;
    }

    public function run() {
        while ($this->runing) {
            if ($this->param != 0) {
                $nt          = rand(1, 10);
                echo "线程[{$this->name}]收到任务参数::{$this->param},需要{$nt}秒处理数据.\n";
                $this->res   = rand(100, 999);
                sleep($nt);
                $this->lurl = $this->param;
                $this->param   = '';
            } else {
                echo "线程[{$this->name}]等待任务..\n";
            }
            sleep(1);
        }
    }
}

//这里创建线程池.
$pool[] = new vote('a');
$pool[] = new vote('b');
$pool[] = new vote('c');

//启动所有线程,使其处于工作状态
foreach ($pool as $w) {
    $w->start();
}

//派发任务给线程
for ($i = 1; $i < 10; $i++) {
    $worker_content = rand(10, 99);
    while (true) {
        foreach ($pool as $worker) {
            //参数为空则说明线程空闲
            if ($worker->param=='') {
                $worker->param = $worker_content;
                echo "[{$worker->name}]线程空闲,放入参数{$worker_content},上次参数[{$worker->lurl}]结果[{$worker->res}].\n";
                break 2;
            }
        }
        sleep(1);
    }
}
echo "所有线程派发完毕,等待执行完成.\n";

//等待所有线程运行结束
while (count($pool)) {
    //遍历检查线程组运行结束
    foreach ($pool as $key => $threads) {
        if ($worker->param=='') {
            echo "[{$threads->name}]线程空闲,上次参数[{$threads->lurl}]结果[{$threads->res}].\n";
            echo "[{$threads->name}]线程运行完成,退出.\n";
            //设置结束标志
            $threads->runing = false;
            unset($pool[$key]);
        }
    }
    echo "等待中...\n";
    sleep(1);
}
echo "所有线程执行完毕.\n";