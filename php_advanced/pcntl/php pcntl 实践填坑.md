## php pcntl 实践填坑

来源：[https://segmentfault.com/a/1190000003503671](https://segmentfault.com/a/1190000003503671)

PHP 可以通过`pcntl`扩展实现多进程编程， 而网上关于如何通过`pcntl`创建多进程的在这里就不表了， 我主要说说关于`pcntl_fork`的一个坑和相关的比较生僻的几个函数的使用方式， 这也是通过挖坑和填坑得出的结论。
闲言碎语不要讲， 直接开始
## pcntl_fork

在实践中， 我在使用php进行多进程实践的模型大概如下， 期待的是每个子进程都能创建一个与之对应文件， 最后父进程创建一个属于父进程的文件，代码如下(`有坑`)：

```php
$pid_dir = __dir__."/pid_files";
for($i=0; $i<3; $i++){
    $pid = pcntl_fork();
    if($pid == -1){
        var_dump("fork failed");
    }
    if(!$pid){
        //子进程代码
        $pid = posix_getpid();
        $ppid = posix_getppid();
        $r = rand(0,100);  //随机数
        touch("$pid_dir/fork_child_process_{$i}_{$ppid}_{$pid}_{$r}");
    }
} 
$pid = posix_getpid();
$ppid = posix_getppid();
$r = rand(0,100); //随机数
touch("$pid_dir/fork_process_pid_{$ppid}_{$pid}_$r");

```

上面的代码我通过循环创建3个子进程， 每个进程创建一个文件，完成后到最后， 父进程创建一个属于他自己的文件，所以， 最后应该会创建出4个文件， 但事实并非如此：

```
fork_child_process_0_62656_62658_39
fork_child_process_1_62656_62659_51
fork_child_process_1_62658_62660_22
fork_child_process_2_62656_62661_91
fork_child_process_2_62658_62662_22
fork_child_process_2_62659_62663_82
fork_child_process_2_62660_62664_59
fork_process_pid_62225_62656_48
fork_process_pid_62656_62658_22
fork_process_pid_62656_62659_82
fork_process_pid_62656_62661_65
fork_process_pid_62658_62660_59
fork_process_pid_62658_62662_59
fork_process_pid_62659_62663_61
fork_process_pid_62660_62664_10

```

为何会出现上面的结果，  这是因为在fork之后， 原有的进程会分裂为两个进程， 一个主进程， 一个子进程， fork后面所有的代码都是共享的， 虽然通过fork的返回值可以判断是主进程还是子进程来执行相应的子进程或主进程逻辑，但之后子进程自己又走到了for循环的部分， 子进程自己有创建了子进程， 所以上面看到了多个child_process 文件， 至于为什么是7个，
来分析一下。

=====================华丽的分割线=============================

循环变量`$i`, 当$i为0时， 会产生一个主进程`a`(不变)和一个子进程`aa`,这个子进程创建了一个子进程文件，即`fork_child_process_0_62656_62658_39`, 主进程`a`继续循环， 即$i=1, 又创建了一个子进程`ab`, 他创建了`fork_child_process_1_62656_62659_51`， 主进程`a`继续循环$i=2, 又创建了一个子进程`ac`, 他创建了`fork_child_process_2_62656_62661_91`这里可以看到`62656`就是主进程a的pid.

至此， 主进程a的循环完毕， 在看看`a`创建的第一个子进程`aa`,`aa`在创建之后， 创建好了上面的子进程文件之后并不会什么也不做， 他也会继续走`for`的循环， 而且继承了主进程a的循环变量， 也就是$i的值为`0`，所以`aa`进程下一次的循环的$i就是1, 然后`aa`继续创建了子进程`aaa`，从而创建文件`fork_child_process_1_62658_62660_22`，`aa`继续，$i=2， 又创建了一个子进程`aab`, 这个子进程创建了文件`fork_child_process_2_62658_62662_22`， 这里可以看到`aaa`和`aab`的ppid就是`aa`的pid`62658`,
 同理`aaa`,`aab`也继承了`aa`的$i值，这时$i的值为1， 当继续循环时， $i 就变成了2, 也就只能循环一次了，相应`aaa`，`aab`创建了子进程文件`fork_child_process_2_62659_62663_82`(aaaa),`fork_child_process_2_62660_62664_59`(aaba),而他们相应的父进程就是`aaa`(62659)和`aab`(62660).

至此， for循环中的多进程逻辑完成了， 也就是为何产生了第一部分的7个文件

=====================华丽的分割线=============================

而至于为何第二部分是8个文件， 各位可以自己思考一下， 注意， 无论主进程还是子进程， 在for循环完毕之后会继续往下走， 知道这一点就好理解了。

在实际的代码中， 我就犯了这种错误。
那如何解决上面的问题呢， 只要在子进程执行的最后`exit`就好啦，

```
fork_child_process_0_63219_63221_66
fork_child_process_1_63219_63222_88
fork_child_process_2_63219_63223_22
fork_process_pid_62225_63219_77

```

继续，那么在网上看到很多多进程编程中使用`pcntl_waitpid`， 并不了解他是做什么的，且相应的例子很少， 我暂且来说说我的理解
## pcntl_waitpid

等待或返回fork的子进程状态。
 多进程的主进程创建了子进程，那主进程如何确认子进程的状态呢。 假如主进程需要根据子进程的状态做不同的处理呢， 这里的状态包括子进程被kill掉，或变成僵尸进程等。 pcntl_waitpid就可以获取子进程的状态码， 通过这个状态码， 就可知道子进程处于什么状态
他的用法：

```
int pcntl_waitpid ( int $pid , int &$status [, int $options = 0 ] )

```

返回的值可以是-1，0或者 >0的值， 如果是-1, 表示子进程出错， 如果>0表示子进程已经退出且值是退出的子进程pid，至于如何退出， 可以通过$status状态码反应。 那什么时候返回0呢， 只有在`option`参数为`WNOHANG`且子进程正在运行时0, 也就是说当设置了`options=WNOHANG`时， 如果子进程还没有退出， 此时`pcntl_waitpid`就会返回0
另外， 如果不设置这个参数为`WNOHANG`，`pcntl_waitpid`就会`阻塞运行`， 直到子进程退出， 至于`option`的另外一个值`WUNTRACED`， 暂未理解， 不表
 **`那么如何根据$status(状态码)判断进程是如何退出呢， 如下(参数都是$status)`** 
## pcntl_wifexited

这个函数可以根据$status 判断进程是否正常退出， 何为正常退出， 比如exit
## pcntl_wexitstatus

这个函数仅在`pcntl_wifexited`返回True(即正常退出)时有效， 且返回子进程退出的返回状态码， 这个返回状态码可以通过exit($s)的参数($s必须为整数时)定义
## pcntl_wifsignaled

检查子进程状态码是否代表由于某个信号而中断， 比如是不是我们给他发送了term, int 等信号了
## pcntl_wexitstatus

假如是发送信号而导致子进程中断， 那么这个信号是什么信号呢， 这个函数就是获取这个信号的
## pcntl_wifstopped

仅当option选项为WUNTRACED时有效， 未理解， 不表
## pcntl_wtermsig

同上

综合实例代码：

```php
$res = pcntl_waitpid($pid, $status, WNOHANG);
//FileLog::log("pid is $pid; wait result is $res");
if($res == -1 || $res > 0){
    if(!pcntl_wifexited($status)){
        //进程非正常退出
        FileLog::log("service stop unusally; pid is $pid");
    }else{
        //获取进程终端的退出状态码;
        $code = pcntl_wexitstatus($status);
        FileLog::log("service stop code: $code;pid is $pid ");
    }

    if(pcntl_wifsignaled($status)){
        //不是通过接受信号中断
        FileLog::log("service stop not by signal;pid is $pid ");
    }else{
        $signal = pcntl_wtermsig($status);
        FileLog::log("service stop by signal $signal;pid is $pid");
    }
}

```

上面的这个代码就通过根据`pcntl_waitpid`的返回结果和状态码对子进程因为不同原因中断做了不同的处理
