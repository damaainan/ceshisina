<?php
namespace MultiProcesses;
/**
 * Class MultiProcesses
 */
class MultiProcesses {
    /**
     * @var int 子进程数量
     */
    private $workerNum = 1;
    /**
     * @var bool 子进程挂掉后，自动重启
     */
    private $reActive = false;
    /**
     * @var int 主进程ID
     */
    private $masterPid = 0;
    /**
     * @var int 当前进程编号
     */
    private $currentWorkerId = 0;
    /**
     * @var int 当前进程ID
     */
    private $currentPid = 0;
    /**
     * @var array 子进程号集合
     */
    private $workers = [];
    /**
     * @var string 执行进程用户
     */
    private $user = '';
    /**
     * @var string 进程名称
     */
    private $title = __CLASS__;
    /**
     * @var array 回调函数
     */
    public $function = [
        'childProcessStart' => null, //子进程启动
        'childProcessStop'  => null, //子进程停止
    ];
    /**
     * MultiProcesses constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = []) {
        $this->workerNum = isset($config["workerNum"]) ? intval($config["workerNum"]) : $this->workerNum;
        $this->reActive  = isset($config["reActive"]) ? $config["reActive"] : $this->reActive;
        $this->user      = isset($config["user"]) ? $config["user"] : posix_getlogin();
        $this->title     = isset($config["title"]) ? $config["title"] : $this->title;
        set_time_limit(0);
        ob_implicit_flush();
    }
    /**
     * 开始进程
     */
    public function start() {
        for ($i = 0; $i < $this->workerNum; $i++) {
            $this->forkWorker($i);
        }
        $this->monitorWorker();
    }
    /**
     * 启动进程
     * @param $workerId
     */
    private function forkWorker($workerId) {
        $pid = pcntl_fork();
        $this->setProcessTitle($this->title);
        $this->setProcessUser($this->user);
        $this->workers[$pid]   = ['pid' => $pid, 'workerId' => $workerId];
        $this->currentWorkerId = $workerId;
        //父进程执行过程中，得到的fork返回值为子进程号，而子进程得到的是0。
        if ($pid > 0) {
            $this->currentPid = $this->masterPid = posix_getpid();
        } elseif (0 === $pid) {
            $this->currentPid = posix_getpid();
            if ($this->function['childProcessStart']) {
                call_user_func_array($this->function['childProcessStart'], [$this->currentWorkerId, $this->currentPid]);
            }
            exit(0);
        } else {
            exit("fork one worker fail");
        }
    }
    /**
     * 监控进程
     */
    private function monitorWorker() {
        while (true) {
            $status = 0;
            $pid    = pcntl_wait($status, WUNTRACED);
            echo "进程ID:{$pid}, 发来信号,状态:{$status}." . PHP_EOL;
            // 子进程退出信号
            if ($pid > 0) {
                $pid      = $this->workers[$pid]['pid'];
                $workerId = $this->workers[$pid]['workerId'];
                //从进程集合中删除相关信息
                unset($this->workers[$pid]);
                //子进程退出回调
                if ($this->function['childProcessStop']) {
                    call_user_func_array($this->function['childProcessStop'], [$workerId, $pid]);
                }
                // 进程被意外kill
                if ($status !== 0) {
                    echo "进程被意外杀死 \n";
                    if ($this->reActive) {
                        echo "正在重新启动新的子进程 \n";
                        $this->forkWorker($this->$workerId);
                        echo "子进程启动成功!!! \n";
                    }
                }
                if (!$this->workers) {
                    exit("主进程退出\n");
                }
            } else {
                exit("主进程异常退出\n");
            }
        }
    }
    /**
     * 设置进程的用户
     * @param $username
     * @return bool
     */
    private function setProcessUser($username) {
        // 用户名为空或者当前用户不是root用户
        if (empty($username) || posix_getuid() !== 0) {
            return false;
        }
        $userInfo = posix_getpwnam($username);
        if ($userInfo['uid'] != posix_getuid() || $userInfo['gid'] != posix_getgid()) {
            if (!posix_setgid($userInfo['gid']) || !posix_setuid($userInfo['uid'])) {
                return false;
            } else {
                return true;
            }
        } else {
            //切换的用户与当前用户相同
            return true;
        }
    }
    /**
     * 设置进程的名字
     * @param $title
     * @return bool
     */
    private function setProcessTitle($title) {
        if (!empty($title)) {
            if (extension_loaded('proctitle') && function_exists('setproctitle')) {
                return setproctitle($title);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}