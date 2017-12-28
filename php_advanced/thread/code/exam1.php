<?php 
class vote extends \Thread {
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

/*
线程[a]等待任务..
线程[b]等待任务..
[a]线程空闲,放入参数83,上次参数[0]结果[暂无,第一次运行.].
[b]线程空闲,放入参数12,上次参数[0]结果[暂无,第一次运行.].
[c]线程空闲,放入参数39,上次参数[0]结果[暂无,第一次运行.].
线程[c]收到任务参数::39,需要10秒处理数据.
线程[a]收到任务参数::83,需要6秒处理数据.
线程[b]收到任务参数::12,需要6秒处理数据.
[a]线程空闲,放入参数13,上次参数[83]结果[636].
[b]线程空闲,放入参数43,上次参数[12]结果[497].
线程[a]收到任务参数::13,需要9秒处理数据.
线程[b]收到任务参数::43,需要4秒处理数据.
[c]线程空闲,放入参数86,上次参数[39]结果[764].
线程[c]收到任务参数::86,需要9秒处理数据.
[b]线程空闲,放入参数59,上次参数[43]结果[256].
线程[b]收到任务参数::59,需要1秒处理数据.
[b]线程空闲,放入参数15,上次参数[59]结果[699].
线程[b]收到任务参数::15,需要7秒处理数据.
[a]线程空闲,放入参数22,上次参数[13]结果[623].
所有线程派发完毕,等待执行完成.
等待中...
线程[a]收到任务参数::22,需要5秒处理数据.
等待中...
等待中...
等待中...
线程[c]等待任务..
等待中...
线程[c]等待任务..
等待中...
线程[b]等待任务..
线程[c]等待任务..
[a]线程空闲,上次参数[22]结果[803].
[a]线程运行完成,退出.
[b]线程空闲,上次参数[15]结果[668].
[b]线程运行完成,退出.
[c]线程空闲,上次参数[86]结果[111].
[c]线程运行完成,退出.
等待中...
所有线程执行完毕.
 */