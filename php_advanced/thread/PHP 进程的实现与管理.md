## PHP 进程的实现与管理

来源：[https://segmentfault.com/a/1190000014735390](https://segmentfault.com/a/1190000014735390)


## 应用场景

一些耗时任务：


* 大数据表分表后的统计信息功能
* 分批发送短信或邮件功能
* 其他可分目标的任务功能（很多种）


所以我们就需要一个常驻内存的任务管理工具，为了保证实时性，一方面我们让它一直执行任务(适当的睡眠，保证cpu不被100%占用)，另一方面我们实现多进程保证并发的执行任务，当然除此之外也可按情况使用线程、协程实现。
## 运行模式

实现PHP进程前，需了解常见的php的运行模式：


* CGI通用网关接口模式
* FAST-CGI模式
* CLI命令行模式 （php xxx.php）
* 模块模式（作为服务器模块）


而php进程则是使用CLI命令行模式运行的
## 基本实现

PHP中提供了一个扩展pcntl，可以利用操作系统的fork调用来实现多进程。fork调用后执行的代码将是并行的，且只能在linux下运行。

```php
$ppid = posix_getpid();// 获取当前进程PID
$pid  = pcntl_fork(); //创建进程

switch ($pid){
        // 创建进程错误
    case -1:
        throw new Exception('fork子进程失败!');
        break;

        // 子进程worker
    case 0:
        $cpid = posix_getpid();
        cli_set_process_title("我是{$ppid}的子进程,我的进程id是{$cpid}.");
        sleep(30);
        exit; // 这里exit掉，避免worker继续执行下面的代码而造成一些问题
        break;

        // 主进程master
    default:
        cli_set_process_title("我是父进程,我的进程id是{$ppid}.");
        pcntl_wait($status); // 挂起父进程，等待并返回子进程状态，防止子进程成为僵尸进程
        break;
}
```

在命令行php xxx.php运行后，使用ps aux | grep 进程可以看到：

![][0]

如果没看到，可能是中文乱码了，使用ps aux，查看

![][1]

或者使用ps –ajft查看层次显示

![][2]
## 进程管理-防止进程成为僵尸进程

创建好了进程，那么怎么对子进程进行管理呢？使用信号，对子进程的管理，一般有两种情况：
posix_kill()：此函数并不能顾名思义，它通过向子进程发送一个信号来操作子进程，在需要要时可以选择给子进程发送进程终止信号来终止子进程；
pcntl_waitpid()：等待或返回fork的子进程状态，如果指定的子进程在此函数调用时已经退出（俗称僵尸进程），此函数将立刻返回，并释放子进程的所有系统资源，此进程可以避免子进程变成僵尸进程，造成系统资源浪费；

孤儿进程：父进程挂了，子进程被pid=1的init进程接管(wait/waitpid)，直到子进程自身生命周期结束被系统回收资源和父进程 采取相关的回收操作
僵尸进程：子进程exit退出,父进程没有通过wait/waitpid获取子进程状态，子进程占用的进程号等描述资源符还存在，产生危害：例如进程号是有限的，无法释放进程号导致未来可能无进程号可用

**父进程中使用：pcntl_wait或者pcntl_waitpid的目的就是防止worker成为僵尸进程
作用：使用pcntl_wait()后，在子进程死掉后，父进程也会被停止**

最后我们通过下图(1-1)来简单的总结和描述这个多进程实现的过程：

![][3]
## 进程管理-进程间通信

队列：如Redis，推荐
socket：推荐
管道：实现复杂，且管道(pipe)，使用文件形式存在，存在硬盘IO性能瓶颈
信号：承载信息量少，不好管理
## 进程管理-切换为守护进程

使用&实现
php deadloop.php &
## 实际多进程的使用

一个耗时10S的任务，执行2次，总耗时20S，而开2个进程，只需10S，如下：

job.php：
![][4]

index.php(进程开启脚本)：
```php
echo '开始时间：'.date('H:i:s', time())."\n";

$cmds = [
    ['./job.php', 0, 50000],//执行脚本，并传参
    ['./job.php', 50000, 100000]
];
for ($i = 0; $i < 2; $i++){
    $ppid = posix_getpid();// 获取当前进程PID
    $pid  = pcntl_fork(); //创建进程
    switch ($pid){
        // 创建进程错误
        case -1:
            throw new Exception('fork子进程失败!');
            break;

        // 子进程worker
        case 0:
            $cpid = posix_getpid();
            cli_set_process_title("我是{$ppid}的子进程,我的进程id是{$cpid}.");

            // 执行业务脚本
            pcntl_exec('/usr/local/php/bin/php', $cmds[$i]);

            exit; // 这里exit掉，避免worker继续执行下面的代码而造成一些问题
            break;
    }
}

// 等待子进程结束
while (pcntl_waitpid(0, $status) != -1) {
    $status = pcntl_wexitstatus($status);
    echo '子进程结束时间：'.date('H:i:s', time())."\n";
}

```

运行php index.php后：

![][5]

实例达到理想效果。

[0]: ../img/bV9Zsz.png
[1]: ../img/bV9ZsC.png
[2]: ../img/bV9ZsF.png
[3]: ../img/bV9Zs3.png
[4]: ../img/bV9ZZB.png
[5]: ../img/bV9ZZI.png