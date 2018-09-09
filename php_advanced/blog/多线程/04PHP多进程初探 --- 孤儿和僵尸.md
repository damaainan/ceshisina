## PHP多进程初探 --- 孤儿和僵尸

来源：[https://segmentfault.com/a/1190000016187050](https://segmentfault.com/a/1190000016187050)

[原文地址：[https://blog.ti-node.com/blog...][7]]

实际上，你们一定要记住：PHP的多进程是非常值得应用于生产环境具备高价值的生产力工具。

但我认为在正式开始吹牛之前还是要说两个基本概念：孤儿进程、僵尸进程。

上篇我整篇尬聊的都是pcntl_fork()，只管fork生产，不管产后护理，实际上这样并不符合主流价值观，而且，操作系统本身资源有限，这样无限生产不顾护理，操作系统也会吃不消的。

孤儿进程是指父进程在fork出子进程后，自己先完了。这个问题很尴尬，因为子进程从此变得无依无靠、无家可归，变成了孤儿。用术语来表达就是，父进程在子进程结束之前提前退出，这些子进程将由init（进程ID为1）进程收养并完成对其各种数据状态的收集。init进程是Linux系统下的奇怪进程，这个进程是以普通用户权限运行但却具备超级权限的进程，简单地说，这个进程在Linux系统启动的时候做初始化工作，比如运行getty、比如会根据/etc/inittab中设置的运行等级初始化系统等等，当然了，还有一个作用就是如上所说的：收养孤儿进程。

僵尸进程是指父进程在fork出子进程，而后子进程在结束后，父进程并没有调用wait或者waitpid等完成对其清理善后工作，导致改子进程进程ID、文件描述符等依然保留在系统中，极大浪费了系统资源。所以，僵尸进程是对系统有危害的，而孤儿进程则相对来说没那么严重。在Linux系统中，我们可以通过ps -aux来查看进程，如果有[Z+]标记就是僵尸进程。

在PHP中，父进程对子进程的状态收集等是通过pcntl_wait()和pcntl_waitpid()等完成的。依然还是要通过代码还演示说明：

演示并说明孤儿进程的出现，并演示孤儿进程被init进程收养：

```php
<?php
        $pid = pcntl_fork();
        if( $pid > 0 ){
            // 显示父进程的进程ID，这个函数可以是getmypid()，也可以用posix_getpid()
            echo "Father PID:".getmypid().PHP_EOL;
            // 让父进程停止两秒钟，在这两秒内，子进程的父进程ID还是这个父进程
            sleep( 2 );
        } else if( 0 == $pid ) {
            // 让子进程循环10次，每次睡眠1s，然后每秒钟获取一次子进程的父进程进程ID
            for( $i = 1; $i <= 10; $i++ ){
                sleep( 1 );
                // posix_getppid()函数的作用就是获取当前进程的父进程进程ID
                echo posix_getppid().PHP_EOL;
            }
        } else {
            echo "fork error.".PHP_EOL;
        }
```

运行结果如下图：

![][0]

可以看到，前两秒内，子进程的父进程进程ID为4129，但是从第三秒开始，由于父进程已经提前退出了，子进程变成孤儿进程，所以init进程收养了子进程，所以子进程的父进程进程ID变成了1。

演示并说明僵尸进程的出现，并演示僵尸进程的危害：

```php
<?php
        $pid = pcntl_fork();
        if( $pid > 0 ){
            // 下面这个函数可以更改php进程的名称
            cli_set_process_title('php father process');
            // 让主进程休息60秒钟
            sleep(60);
        } else if( 0 == $pid ) {
            cli_set_process_title('php child process');
            // 让子进程休息10秒钟，但是进程结束后，父进程不对子进程做任何处理工作，这样这个子进程就会变成僵尸进程
            sleep(10);
        } else {
            exit('fork error.'.PHP_EOL);
        }
```

运行结果如下图：

![][1]

通过执行ps -aux命令可以看到，当程序在前十秒内运行的时候，php child process的状态列为[S+]，然而在十秒钟过后，这个状态变成了[Z+]，也就是变成了危害系统的僵尸进程。

那么，问题来了？如何避免僵尸进程呢？PHP通过pcntl_wait()和pcntl_waitpid()两个函数来帮我们解决这个问题。了解Linux系统编程的应该知道，看名字就知道这其实就是PHP把C语言中的wait()和waitpid()包装了一下。

通过代码演示pcntl_wait()来避免僵尸进程，在开始之前先简单普及一下pcntl_wait()的相关内容：这个函数的作用就是 “ 等待或者返回子进程的状态 ”，当父进程执行了该函数后，就会阻塞挂起等待子进程的状态一直等到子进程已经由于某种原因退出或者终止。换句话说就是如果子进程还没结束，那么父进程就会一直等等等，如果子进程已经结束，那么父进程就会立刻得到子进程状态。这个函数返回退出的子进程的进程ID或者失败返回-1。
##### 我们将第二个案例中代码修改一下：

```php
<?php
        $pid = pcntl_fork();
        if( $pid > 0 ){
            // 下面这个函数可以更改php进程的名称
            cli_set_process_title('php father process');
            
            // 返回$wait_result，就是子进程的进程号，如果子进程已经是僵尸进程则为0
            // 子进程状态则保存在了$status参数中，可以通过pcntl_wexitstatus()等一系列函数来查看$status的状态信息是什么
            $wait_result = pcntl_wait( $status );
            print_r( $wait_result );
            print_r( $status );
            
            // 让主进程休息60秒钟
            sleep(60);
        } else if( 0 == $pid ) {
            cli_set_process_title('php child process');
            // 让子进程休息10秒钟，但是进程结束后，父进程不对子进程做任何处理工作，这样这个子进程就会变成僵尸进程
            sleep(10);
        } else {
            exit('fork error.'.PHP_EOL);
        }
```

将文件保存为wait.php，然后php wait.php，在另外一个终端中通过ps -aux查看，可以看到在前十秒内，php child process是[S+]状态，然后十秒钟过后进程消失了，也就是被父进程回收了，没有变成僵尸进程。

![][2]

但是，pcntl_wait()有个很大的问题，就是阻塞。父进程只能挂起等待子进程结束或终止，在此期间父进程什么都不能做，这并不符合多快好省原则，所以pcntl_waitpid()闪亮登场。pcntl_waitpid( $pid, &$status, $option = 0 )的第三个参数如果设置为WNOHANG，那么父进程不会阻塞一直等待到有子进程退出或终止，否则将会和pcntl_wait()的表现类似。

修改第三个案例的代码，但是，我们并不添加WNOHANG，演示说明pcntl_waitpid()功能：

```php
<?php
        $pid = pcntl_fork();
        if( $pid > 0 ){
            // 下面这个函数可以更改php进程的名称
            cli_set_process_title('php father process');
            
            // 返回值保存在$wait_result中
            // $pid参数表示 子进程的进程ID
            // 子进程状态则保存在了参数$status中
            // 将第三个option参数设置为常量WNOHANG，则可以避免主进程阻塞挂起，此处父进程将立即返回继续往下执行剩下的代码
            $wait_result = pcntl_waitpid( $pid, $status );
            var_dump( $wait_result );
            var_dump( $status );
            
            // 让主进程休息60秒钟
            sleep(60);
            
        } else if( 0 == $pid ) {
            cli_set_process_title('php child process');
            // 让子进程休息10秒钟，但是进程结束后，父进程不对子进程做任何处理工作，这样这个子进程就会变成僵尸进程
            sleep(10);
        } else {
            exit('fork error.'.PHP_EOL);
        }
```

下面是运行结果，一个执行php程序的终端窗口，另一个是ps -aux终端窗口。实际上可以看到主进程是被阻塞的，一直到第十秒子进程退出了，父进程不再阻塞：

![][3] 

![][4]

那么我们修改第四段代码，添加第三个参数WNOHANG，代码如下：

```php
<?php
        $pid = pcntl_fork();
        if( $pid > 0 ){
            // 下面这个函数可以更改php进程的名称
            cli_set_process_title('php father process');
            
            // 返回值保存在$wait_result中
            // $pid参数表示 子进程的进程ID
            // 子进程状态则保存在了参数$status中
            // 将第三个option参数设置为常量WNOHANG，则可以避免主进程阻塞挂起，此处父进程将立即返回继续往下执行剩下的代码
            $wait_result = pcntl_waitpid( $pid, $status, WNOHANG );
            var_dump( $wait_result );
            var_dump( $status );
            echo "不阻塞，运行到这里".PHP_EOL;
            
            // 让主进程休息60秒钟
            sleep(60);
            
        } else if( 0 == $pid ) {
            cli_set_process_title('php child process');
            // 让子进程休息10秒钟，但是进程结束后，父进程不对子进程做任何处理工作，这样这个子进程就会变成僵尸进程
            sleep(10);
        } else {
            exit('fork error.'.PHP_EOL);
        }
```

面是运行结果，一个执行php程序的终端窗口，另一个是ps -aux终端窗口。实际上可以看到主进程是被阻塞的，一直到第十秒子进程退出了，父进程不再阻塞：

![][5] 

![][6]
## 问题出现了，竟然php child process进程状态竟然变成了[Z+]，这是怎么搞得？回头分析一下代码：

我们看到子进程是睡眠了十秒钟，而父进程在执行pcntl_waitpid()之前没有任何睡眠且本身不再阻塞，所以，主进程自己先执行下去了，而子进程在足足十秒钟后才结束，进程状态自然无法得到回收。如果我们将代码修改一下，就是在主进程的pcntl_waitpid()前睡眠15秒钟，这样就可以回收子进程了。但是即便这样修改，细心想的话还是会有个问题，那就是在子进程结束后，在父进程执行pcntl_waitpid()回收前，有五秒钟的时间差，在这个时间差内，php child process也将会是僵尸进程。那么，pcntl_waitpid()如何正确使用啊？这样用，看起来毕竟不太科学。

那么，是时候引入信号学了！

[原文地址：[https://blog.ti-node.com/blog...][7]]

[7]: https://blog.ti-node.com/blog/6375380006637404161
[8]: https://blog.ti-node.com/blog/6375380006637404161
[0]: ./img/1460000016187053.png
[1]: ./img/1460000016187054.png
[2]: ./img/1460000016187055.png
[3]: ./img/1460000016187056.png
[4]: ./img/1460000016187057.png
[5]: ./img/1460000016187058.png
[6]: ./img/1460000016187059.png