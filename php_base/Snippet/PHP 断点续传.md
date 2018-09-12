# PHP 断点续传

 时间 2017-11-10 13:44:49  怼码人生

原文[https://blog.duicode.com/2529.html][1]


在做一个案例，要给客户端做断点续传的服务，

断点续传主要是HTTP协议中的**`Content-Range`**报头。其理解如下：

Content-Range：响应资源的范围。可以在多次请求中标记请求的资源范围，在连接断开重新连接时，客户端只请求该资源未被下载的部分，而不是重新请求整个资源，实现了断点续传。迅雷就是基于这个原理，使用多线程分段读取网络上的资源，最后合并。关于PHP使用多线程实现断点续传稍后讨论。本文只实现简单的断点续传。

```php
$file = $_GET['video'];
$size = filesize($file);
$size2 = $size-1;
$range = 0;
if(isset($_SERVER['HTTP_RANGE'])) {   //http_range表示请求一个实体/文件的一个部分,用这个实现多线程下载和断点续传！
    header('HTTP /1.1 206 Partial Content');
    $range = str_replace('=','-',$_SERVER['HTTP_RANGE']);
    $range = explode('-',$range);
    $range = trim($range[1]);
    header('Content-Length:'.$size);
    header('Content-Range: bytes '.$range.'-'.$size2.'/'.$size);
} else {
    header('Content-Length:'.$size);
    header('Content-Range: bytes 0-'.$size2.'/'.$size);
}
header("Content-type: video/mp4");
header('Accenpt-Ranges: bytes');
header('application/octet-stream');
header("Cache-control: public");
header("Pragma: public");
// 解决在IE中下载时中文乱码问题
$ua = $_SERVER['HTTP_USER_AGENT'];
if(preg_match('/MSIE/',$ua)) {    //表示正在使用 Internet Explorer。
    $ie_filename = str_replace('+','%20',urlencode($file));
    header('Content-Dispositon:attachment; filename='.$ie_filename);
} else {
    header('Content-Dispositon:attachment; filename='.$file);
}
$fp = fopen($file,'rb+');
fseek($fp,$range);                //fseek:在打开的文件中定位,该函数把文件指针从当前位置向前或向后移动到新的位置，新位置从文件头开始以字节数度量。成功则返回 0；否则返回 -1。注意，移动到 EOF 之后的位置不会产生错误。
while(!feof($fp)) {               //feof:检测是否已到达文件末尾 (eof)
    set_time_limit(0);              //控制运行时间
    print(fread($fp,1024));         //读取文件（可安全用于二进制文件,第二个参数:规定要读取的最大字节数）
    ob_flush();                     //刷新PHP自身的缓冲区
    flush();                        //刷新缓冲区的内容(严格来讲, 这个只有在PHP做为apache的Module(handler或者filter)安装的时候, 才有实际作用. 它是刷新WebServer(可以认为特指apache)的缓冲区.)
}
fclose($fp);
```

php中`set_time_limit()`函数运用

当你的页面有大量数据时，建议使用`set_time_limit()`来控制运行时间，默认是30s，所以需要你将执行时间加长点。

如 set_time_limit(800) ,其中将秒数设为0 ，表示持续运行到程序结束。如果要停止运行只能重启php-fpm（文章后面附有重启命令）

如：set_time_limit(0)表示持续运行到程序结束，但这个函数有些在window环境下有些人设置不成功，Linux下也可能会出现问题的，做好在逻辑代码加上try catch避免异常。

注意：这个函数的运行需要你关闭安全模式，在php.ini中将safe_mode = Off 安全模式设置为Off，否则将会出现下面错误：

```
    Warning: set_time_limit() [function.set-time-limit]: Cannot set time limit in safe mode in 
```

ps：在php.ini可以通过定义max_execution_time来设置PHP页面的最大执行时间。

在phpinfo()输出内容可以看到php相关配置。

```
    Loaded Configuration File /etc/php.ini
    set_time_limit(800);
```

这个函数指定了当前所在php脚本的最大执行时间为800秒，实际上

最大执行时间＝php.ini里的max_execution_time数值 － 当前脚本已经执行的时间 + 设定值

假如php.ini里的max_execution_time＝30，当前脚本已经执行5秒，则：

最大执行时间＝30-5+800＝825秒。

查看php运行目录命令：

```
    which php
    /usr/bin/php
```

查看php-fpm进程数：

```
    ps aux | grep -c php-fpm
```

查看运行内存

```
    /usr/bin/php  -i|grep mem
```

重启php-fpm

```
    /etc/init.d/php-fpm restart
```

[1]: https://blog.duicode.com/2529.html
