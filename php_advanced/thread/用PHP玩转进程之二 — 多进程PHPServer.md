## 用PHP玩转进程之二 — 多进程PHPServer

来源：[https://www.fanhaobai.com/2018/09/process-php-multiprocess-server.html](https://www.fanhaobai.com/2018/09/process-php-multiprocess-server.html)

时间 2018-09-02 16:10:53

 
经过 [用 PHP 玩转进程之一 — 基础][7] 的回顾复习，我们已经掌握了进程的基础知识，现在可以尝试用 PHP 做一些简单的进程控制和管理，来加深我们对进程的理解。接下来，我将用多进程模型实现一个简单的 PHPServer，基于它你可以做任何事。
 
PHPServer 完整的源代码，可前往 [fan-haobai/php-server][8] 获取。
 
## 总流程 
 
该 PHPServer 的 Master 和 Worker 进程主要控制流程，如下图所示：
 
![][0]
 
其中，主要涉及 **`3 个对象`**  ，分别为入口脚本  、Master 进程  、Worker 进程  。它们扮演的角色如下：

 
* 入口脚本  ：主要实现 PHPServer 的启动、停止、重载功能，即触发 Master 进程`start`、`stop`、`reload`流程；  
* Master 进程  ：负责创建并监控 Worker 进程在启动阶段，会注册信号处理器，然后创建 Worker；在运行阶段，会持续监控 Worker 进程健康状态，并接受来自入口脚本的控制信号并作出响应；在停止阶段，会停止掉所有 Worker；  
* Worker 进程  ：负责执行业务逻辑。在 Master 进程创建后，就处于持续运行阶段，会监听到来自 Master 进程的信号，以实现自我的停止；  
 
 
又主要包括 **`4 个流程`**  ：

 
* 流程 ①  ：以守护态启动 PHPServer 时的主要流程。入口脚本会进行daemonize  ，也就是实现进程的守护态，此时会`fork`出一个 Master 进程；Master 进程先经过保存 PID  、注册信号处理器  操作，然后创建 Worker  会`fork`出多个 Worker 进程；  
* 流程 ②  ：为 Master 进程持续监控的流程，过程中会捕获入口脚本发送来的信号。主要监控 Worker 进程健康状态，当 Worker 进程异常退出时，会尝试创建新的 Worker 进程以维持 Worker 进程数量；  
* 流程 ③  ：为 Worker 进程持续运行的流程，过程中会捕获 Master 进程发送来的信号。流程 ① 中Worker 进程被创建后，就会持续执行业务逻辑，并阻塞于此；  
* 流程 ④  ：停止 PHPServer 的主要流程。入口脚本首先会向 Master 进程发送 SIGINT 信号，Master 进程捕获到该信号后，会向所有的 Worker 进程转发 SIGINT 信号（通知所有的 Worker 进程终止），等待所有 Worker 进程终止退出后；  
 
 
在流程 ② 中，Worker 进程被 Master 进程`fork`出来后，就会持续运行  并阻塞于此，只有 Master 进程才会继续后续的流程。
 
## 代码实现 
 
### 启动 
 
#### 守护进程 
 
首先，在入口脚本中`fork`一个子进程，然后该进程退出，并设置新的子进程为会话组长，此时的这个子进程就会脱离当前终端的控制。如下图所示：
 
![][1]
 
这里使用了 2 次`fork`，所以最后`fork`的一个子进程才是Master 进程，其实一次`fork`也是可以的。代码如下：

```php
protected static function daemonize()
{
    umask(0);
    $pid = pcntl_fork();
    if (-1 === $pid) {
        exit("process fork fail\n");
    } elseif ($pid > 0) {
        exit(0);
    }

    // 将当前进程提升为会话leader
    if (-1 === posix_setsid()) {
        exit("process setsid fail\n");
    }

    // 再次fork以避免SVR4这种系统终端再一次获取到进程控制
    $pid = pcntl_fork();
    if (-1 === $pid) {
        exit("process fork fail\n");
    } elseif (0 !== $pid) {
        exit(0);
    }
}
```

 
通常在启动时增加`-d`参数，表示进程将运行于守护态模式。
 
当顺利成为一个守护进程后，Master 进程已经脱离了终端控制，所以有必要关闭标准输出和标准错误输出。如下：

```php
protected static function resetStdFd()
{
    global $STDERR, $STDOUT;
    //重定向标准输出和错误输出
    @fclose(STDOUT);
    fclose(STDERR);
    $STDOUT = fopen(static::$stdoutFile, 'a');
    $STDERR = fopen(static::$stdoutFile, 'a');
}
```

 
#### 保存PID 
 
为了实现 PHPServer 的重载或停止，我们需要将 Master 进程的 PID 保存于 PID 文件中，如`php-server.pid`文件。代码如下：

```php
protected static function saveMasterPid()
{
    // 保存pid以实现重载和停止
    static::$_masterPid = posix_getpid();
    if (false === file_put_contents(static::$pidFile, static::$_masterPid)) {
        exit("can not save pid to" . static::$pidFile . "\n");
    }

    echo "PHPServer start\t \033[32m [OK] \033[0m\n";
}


```

 
#### 注册信号处理器 
 
因为守护进程一旦脱离了终端控制，就犹如一匹脱缰的野马，任由其奔腾可能会为所欲为，所以我们需要去驯服它。
 
这里使用信号来实现进程间通信并控制进程的行为，注册信号处理器如下：

```php
protected static function installSignal()
{
    pcntl_signal(SIGINT, array('\PHPServer\Worker', 'signalHandler'), false);
    pcntl_signal(SIGTERM, array('\PHPServer\Worker', 'signalHandler'), false);

    pcntl_signal(SIGUSR1, array('\PHPServer\Worker', 'signalHandler'), false);
    pcntl_signal(SIGQUIT, array('\PHPServer\Worker', 'signalHandler'), false);

    // 忽略信号
    pcntl_signal(SIGUSR2, SIG_IGN, false);
    pcntl_signal(SIGHUP, SIG_IGN, false);
}

protected static function signalHandler($signal)
{
    switch($signal) {
        case SIGINT:
        case SIGTERM:
            static::stop();
            break;
        case SIGQUIT:
        case SIGUSR1:
            static::reload();
            break;
        default: break;
    }
}


```

 
其中，SIGINT 和 SIGTERM 信号会触发`stop`操作，即终止所有进程；SIGQUIT 和 SIGUSR1 信号会触发`reload`操作，即重新加载所有 Worker 进程；此处忽略了 SIGUSR2 和 SIGHUP 信号，但是并未忽略 SIGKILL 信号，即所有进程都可以被强制`kill`掉。
 
#### 创建多进程Worker 
 
Master 进程通过`fork`系统调用，就能创建多个 Worker 进程。实现代码，如下：

```php
protected static function forkOneWorker()
{
    $pid = pcntl_fork();

    // 父进程
    if ($pid > 0) {
        static::$_workers[] = $pid;
    } else if ($pid === 0) { // 子进程
        static::setProcessTitle('PHPServer: worker');

        // 子进程会阻塞在这里
        static::run();

        // 子进程退出
        exit(0);
    } else {
        throw new \Exception("fork one worker fail");
    }
}

protected static function forkWorkers()
{
    while(count(static::$_workers) < static::$workerCount) {
        static::forkOneWorker();
    }
}
```

 
### Worker进程的持续运行 
 
Worker 进程的持续运行，见。其内部调度流程，如下图：
 
![][2]
 
对于 Worker 进程，`run()`方法主要执行具体业务逻辑，当然 Worker 进程会被阻塞于此。对于任务 ①  这里简单地使用`while`来模拟调度，实际中应该使用事件（Select 等）驱动。

```php
public static function run()
{
    // 模拟调度,实际用event实现
    while (1) {
        // 捕获信号
        pcntl_signal_dispatch();

        call_user_func(function(){
            // do something
            usleep(200);
        });
    }
}
```

 
其中，`pcntl_signal_dispatch()`会在每次调度过程中，捕获信号并执行注册的信号处理器。
 
### Master进程的持续监控 
 
#### 调度流程 
 
Master 进程的持续监控，见。其内部调度流程，如下图：
 
![][3]
 
对于 Master 进程的调度，这里也使用了`while`，但是引入了`wait`的系统调用，它会挂起当前进程，直到一个子进程退出或接收到一个信号。

```php
protected static function monitor()
{
    while (1) {
        // 这两处捕获触发信号,很重要
        pcntl_signal_dispatch();
        // 挂起当前进程的执行直到一个子进程退出或接收到一个信号
        $status = 0;
        $pid = pcntl_wait($status, WUNTRACED);
        pcntl_signal_dispatch();

        if ($pid >= 0) {
            // worker健康检查
            static::checkWorkerAlive();
        }
        // 其他你想监控的
    }
}
```

 
第两次的`pcntl_signal_dispatch()`捕获信号，是由于`wait`挂起时间可能会很长，而这段时间可能恰恰会有信号，所以需要再次进行捕获。
 
其中，PHPServer 的和操作是由信号触发，在信号处理器中完成具体操作；Worker 进程的健康检查会在每一次的调度过程中触发。
 
#### Worker进程的健康检查 
 
由于 Worker 进程执行繁重的业务逻辑，所以可能会异常崩溃。因此 Master 进程需要监控 Worker 进程健康状态，并尝试维持一定数量的 Worker 进程。健康检查流程，如下图：
 
![][4]
 
代码实现，如下：

```php
protected static function checkWorkerAlive()
{
    $allWorkerPid = static::getAllWorkerPid();
    foreach ($allWorkerPid as $index => $pid) {
        if (!static::isAlive($pid)) {
            unset(static::$_workers[$index]);
        }
    }

    static::forkWorkers();
}
```

 
#### 停止 
 
Master 进程的持续监控，见。其详细流程，如下图：
 
![][5]
 
入口脚本给 Master 进程发送 SIGINT 信号，Master 进程捕获到该信号并执行，调用`stop()`方法。如下：

```php
protected static function stop()
{
    // 主进程给所有子进程发送退出信号
    if (static::$_masterPid === posix_getpid()) {
        static::stopAllWorkers();

        if (is_file(static::$pidFile)) {
            @unlink(static::$pidFile);
        }
        exit(0);
    } else { // 子进程退出

        // 退出前可以做一些事
        exit(0);
    }
}
```

 
若是 Master 进程执行该方法，会先调用`stopAllWorkers()`方法，向所有的 Worker 进程发送 SIGINT 信号并等待所有 Worker 进程终止退出，再清除 PID 文件并退出。有一种特殊情况，Worker 进程退出超时时，Master 进程则会再次发送 SIGKILL 信号强制杀死所有 Worker 进程；
 
由于 Master 进程会发送 SIGINT 信号给 Worker 进程，所以 Worker 进程也会执行该方法，并会直接退出。

```php
protected static function stopAllWorkers()
{
    $allWorkerPid = static::getAllWorkerPid();
    foreach ($allWorkerPid as $workerPid) {
        posix_kill($workerPid, SIGINT);
    }

    // 子进程退出异常,强制kill
    usleep(1000);
    if (static::isAlive($allWorkerPid)) {
        foreach ($allWorkerPid as $workerPid) {
            static::forceKill($workerPid);
        }
    }

    // 清空worker实例
    static::$_workers = array();
}
```

 
#### 重载 
 
代码发布后，往往都需要进行重新加载。其实，重载过程只需要重启所有 Worker 进程即可。流程如下图：
 
![][6]
 
整个过程共有 2 个流程，流程 ① 终止所有的 Worker 进程，流程 ② 为Worker 进程的健康检查。其中流程 ① ，入口脚本给 Master 进程发送 SIGUSR1 信号，Master 进程捕获到该信号，执行信号处理器调用`reload()`方法，`reload()`方法调用`stopAllWorkers()`方法。如下：

```php
protected static function reload()
{
    // 停止所有worker即可,master会自动fork新worker
    static::stopAllWorkers();
}


```

 `reload()`方法只会在 Master 进程中执行，因为 SIGQUIT 和 SIGUSR1 信号不会发送给 Worker 进程。
 
你可能会纳闷，为什么我们需要重启所有的 Worker 进程，而这里只是停止了所有的 Worker 进程？这是因为，在 Worker 进程终止退出后，由于 Master 进程对Worker 进程的健康检查作用，会自动重新创建所有 Worker 进程。
 
## 运行效果 
 
到这里，我们已经完成了一个多进程 PHPServer。我们来体验一下：

```
$ php server.php 
Usage: Commands [mode] 

Commands:
start Start worker.
stop Stop worker.
reload Reload codes.

Options:
-d to start in DAEMON mode.

Use "--help" for more information about a command.
```

 
首先，我们启动它：

```
$ php server.php start -d
PHPServer start [OK]
```

 
其次，查看进程树，如下：

```
init(1)-+-init(3)---bash(4)
 |-php(1286)-+-php(1287)
 `-php(1288)

```

 
最后，我们把它停止：

```
$ php server.php stop
PHPServer stopping ...
PHPServer stop success

```

 
现在，你是不是感觉进程控制其实很简单，并没有我们想象的那么复杂。(￣┰￣*)
 
## 总结 
 
我们已经实现了一个简易的多进程 [PHPServer][9] ，模拟了进程的管理与控制。需要说明的是，Master 进程可能偶尔也会异常地崩溃，为了避免这种情况的发生：
 
首先，我们不应该给 Master 进程分配繁重的任务，它更适合做一些类似于调度和管理性质的工作；
 
其次，可以使用Supervisor 等工具来管理我们的程序，当 Master 进程异常崩溃时，可以再次尝试被拉起，避免 Master 进程异常退出的情况发生。
 
#### 相关文章»  

 
* [用PHP玩转进程之一 — 基础][10]（2018-08-28）   
 


[7]: https://www.fanhaobai.com/2018/08/process-php-basic-knowledge.html
[8]: https://github.com/fan-haobai/php-server
[9]: https://github.com/fan-haobai/php-server
[10]: https://www.fanhaobai.com/2018/08/process-php-basic-knowledge.html
[0]: ../img/euQZ3eR.png
[1]: ../img/FJBfmui.png
[2]: ../img/zEJnM3N.png
[3]: ../img/VJbY7vj.png
[4]: ../img/EzMzmin.png
[5]: ../img/JziEjea.png
[6]: ../img/na67zev.png