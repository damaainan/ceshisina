## PHP的pcntl进程控制教程一(pcntl_fork)

来源：[https://segmentfault.com/a/1190000015249428](https://segmentfault.com/a/1190000015249428)


## pcntl 简介

PHP的进程控制支持实现了Unix方式的进程创建, 程序执行, 信号处理以及进程的中断。 进程控制不能被应用在Web服务器环境，当其被用于Web服务环境时可能会带来意外的结果。

这份文档用于阐述每个进程控制函数的通常用法。关于Unix进程控制的更多信息建议您查阅 系统文档中关于fork（2）,waitpid（2），signal（2）等的部分或更全面的参考资料比如 《Unix环境高级编程》（作者：W. Richard Stevens，Addison-Wesley出版）。

PCNTL现在使用了ticks作为信号处理的回调机制，ticks在速度上远远超过了之前的处理机制。 这个变化与“用户ticks”遵循了相同的语义。您可以使用declare() 语句在程序中指定允许发生回调的位置。这使得我们对异步事件处理的开销最小化。在编译PHP时 启用pcntl将始终承担这种开销，不论您的脚本中是否真正使用了pcntl。

有一个调整是PHP 4.3.0之前的所有pcntl脚本要使其工作，要么在期望允许回调的（代码）部分使用 declare() ，要么使用declare()新的全局语法 使其在整个脚本范围有效。

Note: 此扩展在 Windows 平台上不可用。
### 官方文档

[pcntl官方文档][0]
## pcntl_fork

```
# 来源官方

PHP 4 >= 4.1.0, PHP 5, PHP 7)

pcntl_fork — 在当前进程当前位置产生分支（子进程）。译注：fork是创建了一个子进程，父进程和子进程 都从fork的位置开始向下继续执行，不同的是父进程执行过程中，得到的fork返回值为子进程 号，而子进程得到的是0。

说明
int pcntl_fork ( void )
pcntl_fork()函数创建一个子进程，这个子进程仅PID（进程号） 和PPID（父进程号）与其父进程不同。fork怎样在您的系统工作的详细信息请查阅您的系统 的fork（2）手册。

返回值
成功时，在父进程执行线程内返回产生的子进程的PID，在子进程执行线程内返回0。失败时，在 父进程上下文返回-1，不会创建子进程，并且会引发一个PHP错误。
```
### 代码

```php
<?php
/**
 * Created by PhpStorm.
 * User: Object
 * Date: 2018/6/11
 * Time: 10:12
 */

const NEWLINE = "\n\n";

if (strtolower(php_sapi_name()) != 'cli') {
    die("请在cli模式下运行");
}

echo "当前进程：" . getmypid() . NEWLINE;

$pid = pcntl_fork(); //fork出子进程

//fork后父进程会走自己的逻辑，子进程从处开始走自己的逻辑，堆栈信息会完全复制给子进程内存空间，父子进程相互独立

if ($pid == -1) { // 创建错误，返回-1

    die('进程fork失败');

} else if ($pid) { // $pid > 0, 如果fork成功，返回子进程id

    // 父进程逻辑
    $time = microtime(true);
    echo "我是父进程:{$time}".NEWLINE;

} else { // $pid = 0

    // 子进程逻辑
    $time = microtime(true);
    echo "我是子进程:{$time}".NEWLINE;
}
```
### 执行结果

```
当前进程：17472

我是父进程:1528697500.2961

我是子进程:1528697500.2961
```
## fork后会子进程先执行还是父进程先执行逻辑呢？
### 测试代码

此处我们调换上面代码的父子进程的if顺序
```php
if ($pid == -1) { // 创建错误，返回-1

    die('进程fork失败');

} else if (!$pid) { // $pid = 0

    // 子进程逻辑
    $time = microtime(true);
    echo "我是子进程:{$time}".NEWLINE;
} else if ($pid) { // $pid > 0, 如果fork成功，返回子进程id

    // 父进程逻辑
    $time = microtime(true);
    echo "我是父进程:{$time}".NEWLINE;

}
```
### 执行结果

```
当前进程：17472

我是父进程:1528697500.2961

我是子进程:1528697500.2961
```
### 测试总结

fork首先会执行父进程逻辑再执行子进程逻辑[0]: http://php.net/manual/zh/book.pcntl.php