## PHP协程：并发 shell_exec

来源：[https://segmentfault.com/a/1190000017196514](https://segmentfault.com/a/1190000017196514)

在PHP程序中经常需要用`shell_exec`执行一些命令，而普通的`shell_exec`是阻塞的，如果命令执行时间过长，那可能会导致进程完全卡住。
在`Swoole4`协程环境下可以用`Co::exec`并发地执行很多命令。

本文基于`Swoole-4.2.9`和`PHP-7.2.9`版本## 协程示例

```php
<?php
$c = 10;
while($c--) {
    go(function () {
        //这里使用 sleep 5 来模拟一个很长的命令
        co::exec("sleep 5");
    });
}
```
## 返回值
`Co::exec`执行完成后会恢复挂起的协程，并返回命令的输出和退出的状态码。

```php
var_dump(co::exec("sleep 5"));
```
## 协程结果

```
htf@htf-ThinkPad-T470p:~/workspace/debug$ time php t.php

real    0m5.089s
user    0m0.067s
sys    0m0.038s
htf@htf-ThinkPad-T470p:~/workspace/debug$
```

只用了`5秒`，程序就跑完了。

下面换成 PHP 的 shell_exec 来试试。
## 阻塞代码

```php
<?php
$c = 10;
while($c--) {
    //这里使用 sleep 5 来模拟一个很长的命令
    shell_exec("sleep 5");
}
```

使用`nohup`或`&`转为后台执行，无法得到命令执行的结果和输出，本文不对此进行深度探讨## 阻塞结果

```
htf@htf-ThinkPad-T470p:~/workspace/debug$ time php s.php 

real    0m50.119s
user    0m0.066s
sys    0m0.058s
htf@htf-ThinkPad-T470p:~/workspace/debug$ 
```

可以看到阻塞版本花费了`50秒`才完成。`Swoole4`提供的协程，是并发编程的利器。在工作中很多地方都可以使用协程，实现并发程序，大大提升程序性能。
