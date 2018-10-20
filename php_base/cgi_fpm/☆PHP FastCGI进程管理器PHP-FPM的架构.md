## PHP FastCGI进程管理器PHP-FPM的架构

来源：[https://www.cnblogs.com/huanxiyun/articles/5413755.html](https://www.cnblogs.com/huanxiyun/articles/5413755.html)

2016-04-20 18:06

![][0]

 **经查看源代码结构，php已经支持`epoll模型`,监听时`epoll`,`accept`后`poll`** 
master进程不断调用`epoll_wait`和`getsockopt`是用来异步处理信号事件和定时器事件. 

这里提一下,Nginx也类似,**`master进程并不处理请求`**,而是worker进程直接处理, 不过区别在于`Nginx的worker进程是epoll异步处理请求`,而`PHP-FPM仍然是poll`. 

如果worker进程不够用,master进程会prefork更多进程, 如果prefork达到了`pm.max_children上限`,worker进程又全都繁忙, 这时master进程会把请求挂起到连接队列backlog里(默认值是511). 

1个PHP-FPM工作进程在同一时刻里只能处理1个请求. **MySQL的最大连接数max_connections默认是151**. 只要PHP-FPM工作进程数不超过151,就不会出现连接不上MySQL的情况. 而且正常情况下,也不需要开启那么多的PHP-FPM工作进程, 比如4个PHP-FPM进程就能跑满4个核心的CPU, 那么你开40个PHP-FPM进程也没有任何意义, 只会占用更多的内存,造成更多的**`CPU上下文切换`**,性能反而更差. 为了减少每个请求都重复建立和释放连接的开销,可以开启持久连接, 一个PHP-FPM进程保持一个到MySQL的长连接,实现透明的"连接池". 

Nginx跟PHP-FPM分开,其实是很好的解耦,PHP-FPM专门负责处理PHP请求,`一个页面对应一个PHP请求`, 页面中 **`所有静态资源的请求都由Nginx来处理`**,这样就实现了动静分离,而 **`Nginx最擅长的就是处理高并发`**. 

PHP-FPM是一个多进程的FastCGI服务,类似**`Apache的prefork的进程模型, 对于只处理PHP请求来说,这种模型是很高效很稳定的`**. 不像Apache(libphp.so),一个页面,要处理多个请求,包括图片,样式表,JS脚本,PHP脚本等. 

php-fpm从5.3开始才进入PHP源代码主干,之前版本没有php-fpm. 那时的spawn-fcgi是一个需要调用php-cgi的FastCGI进程管理器, 另外像Apache的mod_fcgid和IIS的PHP Manager也需要调用php-cgi进程, 但**php-fpm则根本不依赖php-cgi**,`完全独立运行`,**`也不依赖php(cli)命令行解释器`**. 因为php-fpm是一个内置了php解释器的FastCGI服务,启动时能够自行读取php.ini配置和php-fpm.conf配置. 

个人认为,**PHP-FPM工作进程数,设置为2倍CPU核心数就足够了**. 毕竟,Nginx和MySQL以及系统同样要消耗CPU. 根据服务器内存来设置PHP-FPM进程数非常不合理, 把内存分配给MySQL,Memcached,Redis,Linux磁盘缓存(buffers/cache)这些服务显然更合适. 过多的PHP-FPM进程反而会增加CPU上下文切换的开销. 

**`PHP代码中应该尽量避免curl或者file_get_contents这些可能会产生较长网络I/O耗时的代码.`** 

注意设置`CURLOPT_CONNECTTIMEOUT_MS`超时时间,避免进程被长时间阻塞. 如果要异步执行耗时较长的任务,可以 `pclose(popen('/path/to/task.php &', 'r'));` 打开一个进程来处理, 或者借助消息队列,总之就是要尽量避免阻塞到PHP-FPM工作进程. 在`php-fpm.conf`中把`request_slowlog_timeout`设为1秒,在slowlog中查看是否有耗时超过1秒的代码. 优化代码,能够为所有PHP-FPM工作进程减负,这个才是提高性能的根本方法. 

能让CPU满负荷运行的操作可以视为CPU密集型操作. 
`上传和下载则是典型的I/O密集型操作`,因为耗时主要发生在`网络I/O和磁盘I/O`.   
需要PHP认证的下载操作可以委托为Nginx的AIO线程池: 
header("X-Accel-Redirect: $file_path"); 
至于上传操作,比如可以建立一个监听9001端口的名为upload的PHP-FPM进程池(pool), 
专门负责处理上传操作(通过Nginx分发),避免上传操作阻塞到监听9000端口的计算密集的www进程池. 
这时upload进程池多开点进程也无所谓. 

 **`nginx.conf:`**  

```nginx
location = /upload.php { 
    include fastcgi_params; 
    fastcgi_pass 127.0.0.1:9001; 
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; 
} 
```


 **`php-fpm.conf:`**  

```cfg
[www] 
listen = 127.0.0.1:9000 
pm = static 
pm.max_children = 4 
[upload] 
listen = 127.0.0.1:9001 
pm = dynamic 
pm.max_children = 8 
pm.start_servers = 4 
pm.min_spare_servers = 4 
pm.max_spare_servers = 4 

```

　　

其中IO密集这个进程池[io]采用动态的prefork进程,比如这里是繁忙时8个,空闲时4个. 
利用PHP-FPM提供的池的隔离性,分离计算密集和I/O密集操作,可以减少阻塞对整个PHP应用的影响. 

补充: 
info.php 

```php
<?php 
if( isset($_POST['submit']) ) { 
    header('Content-Type: text/plain; charset=utf-8'); 
    //chmod 777 uploads 
    move_uploaded_file($_FILES['upload_file']['tmp_name'], 'uploads/'.$_FILES['upload_file']['name']); 
    print_r($_FILES['upload_file']); 
    exit(); 
} else { 
    header('Content-Type: text/html; charset=utf-8'); 
} 
?> 
<!DOCTYPE HTML> 
<html> 
    <head> 
        <meta charset="utf-8"> 
        <title>PHP文件上传测试</title> 
    </head> 
    <body> 
        <!-- enctype="multipart/form-data" 以二进制格式POST传输数据 --> 
        <form action="<?php echo pathinfo(__FILE__)['basename']; ?>" method="POST" enctype="multipart/form-data"> 
            文件1 <input type="file" name="upload_file" />

            <input type="submit" name="submit" value="提交" />

        </form> 
    </body> 
</html> 

```
　　

Nginx和PHP-FPM的工作进程各自只开1个.   
以2KB每秒上传图片: 

```
time trickle -s -u 2 curl \ 
-F "action=info.php" \ 
-F "upload_file=@linux.jpeg;type=image/jpeg" \ 
-F "submit=提交" \ 
http://www.example.com/app/info.php 
sudo netstat -antp|egrep "curl|nginx|fpm" 

```

　　

发现只有nginx和curl处于`ESTABLISHED`状态,nginx和fpm都没有被阻塞.    
`top -p 4075` 可见Nginx单线程.   
`sudo strace -p 4075` 可见Nginx调用recvfrom接收数据并且pwrite保存数据.   
`sudo strace -p 13751` 可见PHP-FPM是在Nginx接收完成用户上传的数据时才获取数据.   
既然如此,我设想的另开PHP-FPM进程池处理上传操作的用处就不是太大了.   
在文件上传过程中PHP-FPM并不会被阻塞,因为Nginx接收完上传的内容后才一次性交给PHP-FPM.    
附:以2KB每秒下载图片 

```
time trickle -s -u 2 curl \ 
-F "action=info.php" \ 
-F "upload_file=@linux.jpeg;type=image/jpeg" \ 
-F "submit=提交" \ 
http://www.example.com/app/info.php 
sudo netstat -antp|egrep "curl|nginx|fpm" 

```


[0]: http://static.oschina.net/uploads/space/2015/0919/222219_6hEX_561214.jpg