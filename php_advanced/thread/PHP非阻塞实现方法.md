# PHP非阻塞实现方法

 时间 2017-11-30 16:04:45  陈鹏个人博客

原文[http://blog.chenpeng.info/html/3869][1]



为让 PHP 在后端处理长时间任务时不阻塞，快速响应页面请求，可以有如下措施：

#### 1 使用 fastcgi_finish_request()

如果 PHP 与 Web 服务器使用了 [PHP-FPM][3] （FastCGI 进程管理器），那通过 [fastcgi_finish_request()][4] 函数能马上结束会话，而 PHP 线程可以继续在后台运行。 
```php
    echo "program start...";
    
    file_put_contents('log.txt','start-time:'.date('Y-m-d H:i:s'), FILE_APPEND);
    fastcgi_finish_request();
    
    sleep(1);
    echo 'debug...';
    file_put_contents('log.txt', 'start-proceed:'.date('Y-m-d H:i:s'), FILE_APPEND);
    
    sleep(10);
    file_put_contents('log.txt', 'end-time:'.date('Y-m-d H:i:s'), FILE_APPEND);
```
从输出结果可看到，页面打印完 program start... ，输出第一行到 log.txt 后会话就返回了，所以后面的 debug... 不会在浏览器上显示，而 log.txt 文件能完整地接收到三个完成时间。 

#### 2 使用 fsockopen()

使用 [fsockopen()][5] 打开一个网络连接或者一个Unix套接字连接，再用 [stream_set_blocking()][6] 非阻塞模式请求： 
```php
    $fp = fsockopen("www.example.com", 80, $errno, $errstr, 30);
    
    if (!$fp) {
        die('error fsockopen');
    }
    
    // 转换到非阻塞模式
    stream_set_blocking($fp, 0);
    
    $http = "GET /save.php  / HTTP/1.1\r\n";
    $http .= "Host: www.example.com\r\n";
    $http .= "Connection: Close\r\n\r\n";
    
    fwrite($fp, $http);
    fclose($fp);
```
#### 3 使用 cURL

利用cURL中的 [curl_multi_*][7] 函数发送异步请求 
```php
    $cmh = curl_multi_init();
    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_URL, "http://localhost/");
    curl_multi_add_handle($cmh, $ch1);
    curl_multi_exec($cmh, $active);
    echo "End\n";
```
#### 4 使用 Gearman/Swoole 扩展

Gearman 是一个具有 php 扩展的分布式异步处理框架，能处理大批量异步任务。

Swoole 最近很火，有很多异步方法，使用简单。

#### 5 使用缓存和队列

使用redis等缓存、队列，将数据写入缓存，使用后台计划任务实现数据异步处理。

这个方法在常见的大流量架构中应该很常见吧

#### 6 调用系统命令

极端的情况下，可以调用系统命令，可以将数据传给后台任务执行，个人感觉不是很高效。
```
    $cmd = 'nohup php ./processd.php $someVar >/dev/null  &';
    `$cmd`
```
#### 7 使用 pcntl_fork()

安装 pcntl 扩展，使用 [pcntl_fork()][8] 生成子进程异步执行任务，个人觉得是最方便的，但也容易出现僵尸进程。 
```php
    $pid = pcntl_fork()
    if ($pid == 0) {
         child_func();    //子进程函数，主进程运行
    } else {
         father_func();   //主进程函数
    }
    
    echo "Process " . getmypid() . " get to the end.\n";   
    function father_func() {
         echo "Father pid is " . getmypid() . "\n";
    }
    
    function child_func() {
        sleep(6);
        echo "Child process exit pid is " . getmypid() . "\n";     
        exit(0);
    }
```
#### 8 PHP 原生支持

外国佬的大招，没看懂

[http://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html][9]

来源： [https://www.awaimai.com/660.html][10]

[1]: http://blog.chenpeng.info/html/3869
[3]: http://php.net/manual/zh/book.fpm.php
[4]: http://php.net/manual/zh/function.fastcgi-finish-request.php
[5]: http://php.net/manual/zh/function.fsockopen.php
[6]: http://php.net/manual/zh/function.stream-set-blocking.php
[7]: http://php.net/manual/en/ref.curl.php
[8]: http://php.net/manual/en/function.pcntl-fork.php
[9]: http://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html
[10]: https://www.awaimai.com/660.html