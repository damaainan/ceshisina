## PHP多进程消费队列

来源：[http://www.cnblogs.com/iforever/p/9039579.html](http://www.cnblogs.com/iforever/p/9039579.html)

时间 2018-05-15 10:36:00

 
## 引言
 
最近开发一个小功能，用到了队列mcq，启动一个进程消费队列数据，后边发现一个进程处理不过来了，又加了一个进程，过了段时间又处理不过来了......
 
这种方式每次都要修改crontab，如果进程挂掉了，不会及时的启动，要等到下次crontab执行的时候才会启动。关闭（重启）进程的时候用的是kill，这可能会丢失正在处理的数据，比如下面这个例子，我们假设sleep过程就是处理逻辑，这里为了明显看出效果，将处理时间放大到10s：
 
```php
<?php
$i = 1;
while (1) {
    echo "开始第[{$i}]次循环\n";
    sleep(10);
    echo "结束第[{$i}]次循环\n";
    $i++;
}
```
 
当我们运行脚本之后，等到循环开始之后，给进程发送`kill {$pid}`，默认发送的是编号为15的`SIGTERM`信号。假设`$i`是从队列拿到的，拿到2的时候，正在处理，我们给程序发送了kill信号，和队列数据丢失一样，问题比较大，因此我要想办法解决这些问题。
 
``` 
开始第[1]次循环
结束第[1]次循环
开始第[2]次循环


[1]    28372 terminated  php t.php
```
 
## nginx进程模型
 
这时候我想到了nginx，nginx作为高性能服务器的中流砥柱，为成千上万的企业和个人服务，他的进程模型比较经典，如下所示：
 
![][0]
 
管理员通过master进程和nginx进行交互，从`/path/to/nginx.pid`读取nginx master进程的pid，发送信号给master进程，master根据不同的信号做出不同的处理，然后反馈信息给管理员。worker是master进程fork出来的，master负责管理worker，不会去处理业务，worker才是具体业务的处理者，master可以控制worker的退出、启动，当worker意外退出，master会收到子进程退出的消息，也会重新启动新的worker进程补充上来，不让业务处理受影响。nginx还可以平滑退出，不丢失任何一个正在处理的数据，更新配置时nginx可以做到不影响线上服务来加载新的配置，这在请求量很大的时候特别有用。
 
## 进程设计
 
  
看了nginx的进模型，我们完全可以开发一个类似的类库来满足处理mcq数据的需求，做到单文件控制所有进程、可以平滑退出、可以查看子进程状态。不需要太复杂，因为我们处理队列数据接收一定的延迟，做到nginx那样不间断服务比较麻烦，费时费力，意义不是很大。设计的进程模型跟nginx类似，更像是nginx的简化版本。
 
  
![][1]
 
 
 
#### 进程信号量设计
 
master进程启动的时候保存pid到文件`/path/to/daeminze.pid`，管理员通过信号和master进程通讯，master进程安装3种信号，碰到不同的信号，做出不同的处理，如下所示：
 
``` 
SIGINT  => 平滑退出，处理完正在处理的数据再退出
SIGTERM => 暴力退出，无论进程是否正在处理数据直接退出
SIGUSR1 => 查看进程状态，查看进程占用内存，运行时间等信息
```
 
master进程通过信号和worker进程通讯，worker进程安装了2个信号，如下所示：
 
``` 
SIGINT  => 平滑退出
SIGUSR1 => 查看worker进程自身状态
```
 
为什么worker进程只安装2个信号呢，少了个`SIGTERM`，因为master进程收到信号`SIGTERM`之后，向worker进程发送`SIGKILL`信号，默认强制关闭进程即可。
 
worker进程是通过master进程fork出来的，这样master进程可以通过`pcntl_wait`来等待子进程退出事件，当有子进程退出的时候返回子进程pid，做处理并启动新的进程补充上来。
 
master进程也通过`pcntl_wait`来等待接收信号，当有信号到达的时候，会返回`-1`，这个地方还有些坑，在下文中会详细讲。
 
PHP中有2种信号触发的方式，第一种方式是`declare(ticks = 1);`，这种效率不高，Zend每执行一次低级语句，都会去检查进程中是否有未处理的信号，现在已经很少使用了，`PHP 5.3.0`及之前的版本可能会用到这个。
 
第二种是通过`pcntl_signal_dispatch`来调用未处理的信号，`PHP 5.4.0`及之后的版本适用，可以巧妙的将该函数放在循环中，性能上基本没什么损失，现在推荐适用。
 
#### PHP安装修信号量
 
PHP通过`pcntl_signal`安装信号，函数声明如下所示：
 
``` 
bool pcntl_signal ( int $signo , [callback $handler [, bool $restart_syscalls = true ] )
```
 
第三个参数`restart_syscalls`不太好理解，找了很多资料，也没太查明白，经过试验发现，这个参数对`pcntl_wait`函数接收信号有影响，当设置为缺省值`true`的时候，发送信号，进程用`pcntl_wait`收不到，必须设置为`false`才可以，看看下面这个例子：
 
```php
<?php
$i = 0;
while ($i<5) {
    $pid = pcntl_fork();
    $random = rand(10, 50);
    if ($pid == 0) {
        sleep($random);
        exit();
    }
    echo "child {$pid} sleep {$random}\n";
    $i++;
}

pcntl_signal(SIGINT,  function($signo) {
     echo "Ctrl + C\n";
});

while (1) {
    $pid = pcntl_wait($status);
    var_dump($pid);
    pcntl_signal_dispatch();
}
```
 
运行之后，我们对父进程发送`kill -SIGINT {$pid}`信号，发现pcntl_wait没有反应，等到有子进程退出的时候，发送过的`SIGINT`会一个个执行，比如下面结果：
 
``` 
child 29643 sleep 48
child 29644 sleep 24
child 29645 sleep 37
child 29646 sleep 20
child 29647 sleep 31
int(29643)
Ctrl + C
Ctrl + C
Ctrl + C
Ctrl + C
int(29646)
```
 
这是运行脚本之后马上给父进程发送了四次`SIGINT`信号，等到一个子进程推出的时候，所有信号都会触发。
 
但当把安装信号的第三个参数设置为`false`：
 
``` 
pcntl_signal(SIGINT,  function($signo) {
     echo "Ctrl + C\n";
}, false);
```
 
这时候给父进程发送`SIGINT`信号，`pcntl_wait`会马上返回`-1`，信号对应的事件也会触发。
 
所以第三个参数大概意思就是，是否重新注册此信号，如果为false只注册一次，触发之后就返回，`pcntl_wait`就能收到消息，如果为true，会重复注册，不会返回，`pcntl_wait`收不到消息。
 
#### 信号量和系统调用
 
信号量会打断系统调用，让系统调用立刻返回，比如`sleep`，当进程正在sleep的时候，收到信号，sleep会马上返回剩余sleep秒数，比如：
 
```php
<?php
pcntl_signal(SIGINT,  function($signo) {
     echo "Ctrl + C\n";
}, false);

while (true) {
    pcntl_signal_dispatch();
    echo "123\n";
    $limit = sleep(2);
    echo "limit sleep [{$limit}] s\n";
}
```
 
运行之后，按`Ctrl + C`，结果如下所示：
 
``` 
123
^Climit sleep [1] s
Ctrl + C
123
limit sleep [0] s
123
^Climit sleep [1] s
Ctrl + C
123
^Climit sleep [2] s
```
 
#### daemon（守护）进程
 
这种进程一般设计为daemon进程，不受终端控制，不与终端交互，长时间运行在后台，而对于一个进程，我们可以通过下面几个步骤把他升级为一个标准的daemon进程：
 
```php
protected function daemonize()
{
    $pid = pcntl_fork();
    if (-1 == $pid) {
        throw new Exception("fork进程失败");
    } elseif ($pid != 0) {
        exit(0);
    }
    if (-1 == posix_setsid()) {
        throw new Exception("新建立session会话失败");
    }

    $pid = pcntl_fork();
    if (-1 == $pid) {
        throw new Exception("fork进程失败");
    } else if($pid != 0) {
        exit(0);
    }

    umask(0);
    chdir("/");
}
```
 
拢共分五步：
 
 
* fork子进程，父进程退出。 
* 设置子进程为会话组长，进程组长。 
* 再次fork，父进程退出，子进程继续运行。 
* 恢复文件掩码为`0`。  
* 切换当前目录到根目录`/`。  
 
 
第2步是为第1步做准备，设置进程为会话组长，必要条件是进程非进程组长，因此做第一次fork，进程组长（父进程）退出，子进程通过`posix_setsid()`设置为会话组长，同时也为进程组长。
 
第3步是为了不让进程重新控制终端，因为一个进程控制一个终端的必要条件是会话组长（pid=sid）。
 
第4步是为了恢复默认的文件掩码，避免之前做的操作对文件掩码做了设置，带来不必要的麻烦。关于文件掩码， linux中，文件掩码在创建文件、文件夹的时候会用到，文件的默认权限为666，文件夹为777，创建文件（夹）的时候会用默认值减去掩码的值作为创建文件（夹）的最终值，比如掩码`022`下创建文件`666 - 222 = 644`，创建文件夹`777 - 022 = 755`：
 
| 掩码 | 新建文件权限 | 新建文件夹权限 | 
|-|-|-|
| umask(0) | 666 (-rw-rw-rw-) | 777 (drwxrwxrwx) | 
| umask(022) | 644 (-rw-r--r--) | 755 (drwxr-xr-x) | 
 
 
第5步是切换了当前目录到根目录`/`，网上说避免起始运行他的目录不能被正确卸载，这个不是太了解。
 
对应5步，每一步的各种id变化信息：
 
| 操作后 | pid | ppid | pgid | sid | 
|-|-|-|-|
| 开始 | 17723 | 31381 | 17723 | 31381 | 
| 第一次fork | 17723 | 1 | 17723 | 31381 | 
| posix_setsid() | 17740 | 1 | 17740 | 17740 | 
| 第二次fork | 17840 | 1 | 17740 | 17740 | 
 
 
  
另外，会话、进程组、进程的关系如下图所示，这张图有助于更好的理解。
 
  
![][2]
 
 
 
至此，你也可以轻松地造出一个daemon进程了。
 
## 命令设计
 
我准备给这个类库设计6个命令，如下所示：
 
 
* start 启动命令 
* restart 强制重启 
* stop 平滑停止 
* reload 平滑重启 
* quit 强制停止 
* status 查看进程状态 
 
 
#### 启动命令
 
启动命令就是默认的流程，按照默认流程走就是启动命令，启动命令会检测pid文件中是否已经有pid，pid对应的进程是否健康，是否需要重新启动。
 
#### 强制停止命令
 
管理员通过入口文件结合pid给master进程发送`SIGTERM`信号，master进程给所有子进程发送`SIGKILL`信号，等待所有worker进程退出后，master进程也退出。
 
#### 强制重启命令
 `强制停止命令`+`启动命令`#### 平滑停止命令
 
平滑停止命令，管理员给master进程发送`SIGINT`信号，master进程给所有子进程发送`SIGINT`，worker进程将自身状态标记为`stoping`，当worker进程下次循环的时候会根据`stoping`决定停止，不在接收新的数据，等所有worker进程退出之后，master进程也退出。
 
#### 平滑重启命令
 `平滑停止命令`+`启动命令`#### 查看进程状态
 
查看进程状态这个借鉴了 [workerman][3] 的思路，管理员给master进程发送`SIGUSR1`信号，告诉主进程，我要看所有进程的信息，master进程，master进程将自身的进程信息写入配置好的文件路径A中，然后发送`SIGUSR1`，告诉worker进程把自己的信息也写入文件A中，由于这个过程是异步的，不知道worker进程啥时候写完，所以master进程在此处等待，等所有worker进程都写入文件之后，格式化所有的信息输出，最后输出的内容如下所示：
 
``` 
➜/dir /usr/local/bin/php DaemonMcn.php status
Daemon [DaemonMcn] 信息:
-------------------------------- master进程状态 --------------------------------
pid       占用内存       处理次数       开始时间                 运行时间
16343     0.75M          --             2018-05-15 09:42:45      0 天 0 时 3 分
12 slaver
-------------------------------- slaver进程状态 --------------------------------
任务task-mcq:
16345     0.75M          236            2018-05-15 09:42:45      0 天 0 时 3 分
16346     0.75M          236            2018-05-15 09:42:45      0 天 0 时 3 分
--------------------------------------------------------------------------------
任务test-mcq:
16348     0.75M          49             2018-05-15 09:42:45      0 天 0 时 3 分
16350     0.75M          49             2018-05-15 09:42:45      0 天 0 时 3 分
16358     0.75M          49             2018-05-15 09:42:45      0 天 0 时 3 分
16449     0.75M          1              2018-05-15 09:46:40      0 天 0 时 0 分
--------------------------------------------------------------------------------
```
 
等待worker进程将进程信息写入文件的时候，这个地方用了个比较trick的方法，每个worker进程输出一行信息，统计文件的行数，达到worker进程的行数之后表示所有worker进程都将信息写入完毕，否则，每个1s检测一次。
 
## 其他设计
 
另外还加了两个比较实用的功能，一个是worker进程运行时间限制，一个是worker进程循环处理次数限制，防止长时间循环进程出现内存溢出等意外情况。时间默认是1小时，运行次数默认是10w次。
 
除此之外，也可以支持多任务，每个任务几个进程独立开，统一由master进程管理。
 
代码已经放到 [github][4] 中，有兴趣的可以试试，不支持windows哦，有什么错误还望指出来。
 


[3]: https://github.com/walkor/Workerman.git
[4]: https://github.com/aizuyan/daemon
[0]: ../img/zmAJfeQ.png 
[1]: ../img/Z7b6Jbe.png 
[2]: ../img/qQFzQzb.png 