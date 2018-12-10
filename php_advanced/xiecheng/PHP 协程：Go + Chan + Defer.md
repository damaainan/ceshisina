## PHP 协程：Go + Chan + Defer

来源：[https://segmentfault.com/a/1190000017243966](https://segmentfault.com/a/1190000017243966)

`Swoole4`为`PHP`语言提供了强大的`CSP`协程编程模式。底层提供了`3`个关键词，可以方便地实现各类功能。


* `Swoole4`提供的`PHP协程`语法借鉴自`Golang`，在此向`GO`开发组致敬
* `PHP+Swoole`协程可以与`Golang`很好地互补。`Golang`：静态语言，严谨强大性能好，`PHP+Swoole`：动态语言，灵活简单易用


本文基于`Swoole-4.2.9`和`PHP-7.2.9`版本## 关键词


* `go`：创建一个协程
* `chan`：创建一个通道
* `defer`：延迟任务，在协程退出时执行，先进后出


这`3`个功能底层实现全部为 **`内存操作`** ，没有任何`IO`资源消耗。就像`PHP`的`Array`一样是非常廉价的。如果有需要就可以直接使用。这与`socket`和`file`操作不同，后者需要向操作系统申请端口和文件描述符，读写可能会产生阻塞的`IO`等待。
## 协程并发

使用`go`函数可以让一个函数并发地去执行。在编程过程中，如果某一段逻辑可以并发执行，就可以将它放置到`go`协程中执行。
## 顺序执行

```php
function test1() 
{
    sleep(1);
    echo "b";
}
    
function test2() 
{
    sleep(2);
    echo "c";
}

test1();
test2();
```
#### 执行结果：

```
htf@LAPTOP-0K15EFQI:~$ time php b1.php
bc
real    0m3.080s
user    0m0.016s
sys     0m0.063s
htf@LAPTOP-0K15EFQI:~$
```

上述代码中，`test1`和`test2`会顺序执行，需要`3`秒才能执行完成。
## 并发执行

使用`go`创建协程，可以让`test1`和`test2`两个函数变成并发执行。

```php
Swoole\Runtime::enableCoroutine();

go(function () 
{
    sleep(1);
    echo "b";
});
    
go(function () 
{
    sleep(2);
    echo "c";
});
```

`Swoole\Runtime::enableCoroutine()`作用是将`PHP`提供的`stream`、`sleep`、`pdo`、`mysqli`、`redis`等功能从同步阻塞切换为协程的异步`IO`#### 执行结果：

```
bchtf@LAPTOP-0K15EFQI:~$ time php co.php
bc
real    0m2.076s
user    0m0.000s
sys     0m0.078s
htf@LAPTOP-0K15EFQI:~$
```

可以看到这里只用了`2`秒就执行完成了。


* 顺序执行耗时等于所有任务执行耗时的总和 ：`t1+t2+t3...`
* 并发执行耗时等于所有任务执行耗时的最大值 ：`max(t1, t2, t3, ...)`


## 协程通信

有了`go`关键词之后，并发编程就简单多了。与此同时又带来了新问题，如果有`2`个协程并发执行，另外一个协程，需要依赖这两个协程的执行结果，如果解决此问题呢？

答案就是使用通道（`Channel`），在`Swoole4`协程中使用`new chan`就可以创建一个通道。通道可以理解为自带协程调度的队列。它有两个接口`push`和`pop`：


* `push`：向通道中写入内容，如果已满，它会进入等待状态，有空间时自动恢复
* `pop`：从通道中读取内容，如果为空，它会进入等待状态，有数据时自动恢复


使用通道可以很方便地实现 **`并发管理`** 。

```php
$chan = new chan(2);

# 协程1
go (function () use ($chan) {
    $result = [];
    for ($i = 0; $i < 2; $i++)
    {
        $result += $chan->pop();
    }
    var_dump($result);
});

# 协程2
go(function () use ($chan) {
   $cli = new Swoole\Coroutine\Http\Client('www.qq.com', 80);
       $cli->set(['timeout' => 10]);
       $cli->setHeaders([
       'Host' => "www.qq.com",
       "User-Agent" => 'Chrome/49.0.2587.3',
       'Accept' => 'text/html,application/xhtml+xml,application/xml',
       'Accept-Encoding' => 'gzip',
   ]);
   $ret = $cli->get('/');
   // $cli->body 响应内容过大，这里用 Http 状态码作为测试
   $chan->push(['www.qq.com' => $cli->statusCode]);
});

# 协程3
go(function () use ($chan) {
   $cli = new Swoole\Coroutine\Http\Client('www.163.com', 80);
   $cli->set(['timeout' => 10]);
   $cli->setHeaders([
       'Host' => "www.163.com",
       "User-Agent" => 'Chrome/49.0.2587.3',
       'Accept' => 'text/html,application/xhtml+xml,application/xml',
       'Accept-Encoding' => 'gzip',
   ]);
   $ret = $cli->get('/');
   // $cli->body 响应内容过大，这里用 Http 状态码作为测试
   $chan->push(['www.163.com' => $cli->statusCode]);
});
```
#### 执行结果：

```
htf@LAPTOP-0K15EFQI:~/swoole-src/examples/5.0$ time php co2.php
array(2) {
  ["www.qq.com"]=>
  int(302)
  ["www.163.com"]=>
  int(200)
}

real    0m0.268s
user    0m0.016s
sys     0m0.109s
htf@LAPTOP-0K15EFQI:~/swoole-src/examples/5.0$
```

这里使用`go`创建了`3`个协程，协程`2`和协程`3`分别请求`qq.com`和`163.com`主页。协程`1`需要拿到`Http`请求的结果。这里使用了`chan`来实现并发管理。


* 协程`1`循环两次对通道进行`pop`，因为队列为空，它会进入等待状态
* 协程`2`和协程`3`执行完成后，会`push`数据，协程`1`拿到了结果，继续向下执行


## 延迟任务

在协程编程中，可能需要在协程退出时自动实行一些任务，做清理工作。类似于`PHP`的`register_shutdown_function`，在`Swoole4`中可以使用`defer`实现。

```php
Swoole\Runtime::enableCoroutine();

go(function () {
    echo "a";
    defer(function () {
        echo "~a";
    });
    echo "b";
    defer(function () {
        echo "~b";
    });
    sleep(1);
    echo "c";
});
```
#### 执行结果：

```
htf@LAPTOP-0K15EFQI:~/swoole-src/examples/5.0$ time php defer.php
abc~b~a
real    0m1.068s
user    0m0.016s
sys     0m0.047s
htf@LAPTOP-0K15EFQI:~/swoole-src/examples/5.0$
```
## 结语
`Swoole4`提供的`Go + Chan + Defer`为`PHP`带来了一种全新的`CSP`并发编程模式。灵活使用`Swoole4`提供的各项特性，可以解决工作中各类复杂功能的设计和开发。
