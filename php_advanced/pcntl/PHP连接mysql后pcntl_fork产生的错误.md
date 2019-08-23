## PHP连接mysql后pcntl_fork产生的错误

来源：[https://blog.csdn.net/Magic_YH/article/details/75577999](https://blog.csdn.net/Magic_YH/article/details/75577999)

时间：

最近使用PHP做多进程上传碰到了一个问题，上传完成以后写入数据库时报错：Packets out of order. Expected 1 received 0. Packet size=4131940

也有可能是：MySQL server has gone away

总之可能是这种各样奇怪的错误


思考了一下，考虑到PDO是在主进程中创建的，因此fork后多个子进程使用的是同一个PDO连接，如果有两个子进程同时使用该连接与数据库通信，那么就可能因为通信协议时序的不正确导致mysql出现异常


为了验证该想法，写了一个测试程序抓包看了一下

```php
$dsn = "mysql:host={$host};dbname={$dbName};port={$port};charset=utf8";
$dbh = new PDO($dsn, $user, $pswd);

for ($i=0; $i < 2; $i++) { 
	$pid = pcntl_fork();
    if ($pid == -1) {
        throw new \Exception("Fork process error!", 1);
    }
    if ($pid == 0) {    // child
        $sth = $dbh->query("select * from test_table");
        exit(0);
    }
}
```

![][0]


从抓包结果可以看到两个子进程确实是使用的同一个tcp链接，其中61是客户机，178是服务机


知道了原因，解决起来就好办了，不要使用主进程中创建的mysql连接，进入子进程了以后再重新建立新的mysql连接就不会出现这种问题了


参考链接：https://bugs.php.net/bug.php?id=67061


[0]: ../img/20170720211700993.png