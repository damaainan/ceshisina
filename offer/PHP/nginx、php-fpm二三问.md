## nginx、php-fpm二三问

来源：[https://www.cnblogs.com/huanxiyun/p/5417611.html](https://www.cnblogs.com/huanxiyun/p/5417611.html)

2016-04-21 16:46

### **`php-cgi为什么没了`**？ php-fpm子进程是干啥的?  
php-cgi是原来php自带的fastcgi进程管理器，有一些缺点，比如不能平滑重启，进程管理差。  
php-fpm可以看做升级版的php-fpm.  
php-fpm子进程就是工作进程，负责接收和处理请求, 和nginx类似。  

### fastcgi_pass 127.0.0.1:9000是干啥的 这种方式是http协议还是fastcgi协议？   
是php-fpm的监听地址,可以是本机，也可以是其他机器.比如192.168.0.21:9000,php-fpm.conf中也需要为php-fpm进程池配置相同参数.
fastcgi协议。  
这种通过tcp方式发送数据。  
如果nginx和php部署在同台机器,也可用socket形式进行进程间通讯.

### nginx如果和php socket通讯，是不是也要占用端口？
是，也是通过tcp协议，目标地址和端口已知，nginx作为客户端.php-fpm接受请求时需分配端口.如果部署在同台机器，可通过socket文件进行进程间通讯。极大提高性能。  

### fastcgi_pass和proxy_pass区别 
fastcgi_pass是把进程按照fastcgi要求的格式发送到php-fpm监听的地址。  
proxy_pass只是把请求转发到其他web服务器.
fastcgi_pass和proxy_pass都可以交给upstream 模块处理。  

php-fpm master进程并不接收和分发请求,而是worker进程直接accpet请求后poll处理.
我看一篇文章这么说，那几百个worker进程,**怎么知道谁去处理当前这个请求呢,这个逻辑谁做的 ？**
抢占式的吗 ?还是master来做空闲子进程的判断，指定某个进程处理？
**`抢占式，自己抢自己的`**。公园里喂鱼是的。  

### php-fpm和mod apache比较？php-fpm优势，为什么选择php-fpm
性能表现差距不大。  
内存小的时候,php-fpm更快，php-fpm使用更少的内存，apache对php模块管理较差。  
以CGI方式运行时,web server将用户请求以消息的方式转交给PHP独立进程,PHP与web服务之间无从属关系.
纯粹调用--返回结果的形式通讯.而模块方式,则是将PHP做为web-server的子进程控制,两者之间有从属关系.最明显的例子就是在CGI模式下,如果修改了PHP.INI的配置文件,不用重启web服务便可生效,而模块模式下则需要重启web服务.
php-fpm可独立部署,当然解耦的同时也会带来tcp网络通讯的开销.

### php为什么需要nginx,自己接受请求处理不就行了吗？
php当然可以自己接受请求处理，比如用socket可以自己实现一个web服务器，并且本身php5.4也内置一个web服务器。  
使用nginx，个人理解，首先是一个`解耦`的操作，web服务器代码和业务代码解耦。并且**`nginx使用epoll`**，能快速处理请求。  
其次，安全考虑，nginx能过滤很多非法请求，php本身是一门语言，请求处理并不强大，当然go语言等这方面做的不错。  

### `--enable-force-cgi-redirect` 选项作用？
 一般安装php用 `--enable-force-cgi-redirect` 选项,PHP 在此模式下只会解析已经通过了 web 服务器的重定向规则的 URL。  
若使用 CGI VERSION 模式来执行 PHP ，打开本选项会增加安全性。此编译选项可以防止任何人通过如 <http://my.host/cgi-bin/php/secretdir/script.php> 这样的 URL 直接调用 PHP。PHP 在此模式下只会解析已经通过了 web 服务器的重定向规则的 URL。 
个人理解这种适合单一入口的程序。都进入index.php统一处理。如果程序非单一入口，则不可以启用该选项。  

### cgi和http的区别？
CGI脚本简单地讲是个运行在Web服务器上的程序, 有浏览器的输入触发. 这个脚本通常象服务器和系统中其他程序如数据库的桥梁。  
CGI脚本是用下列两种方法使用的: 作为一个表单的ACTION 或 作为一个页中的直接link。现在cgi大部分用来处理表单。  
为了能编写和运行CGI脚本, 你需要一个Web服务器.   
即使你有一个Web服务器, 这个服务器必须特别地为运行CGI脚本配置一下. 那意味着你所有的脚本必须放置在一个叫做cgi-bin的目录下.
cgi比较古老，新的动态网页解析语言有php等。  
http是一种协议，浏览器发送的也是http接口，个人理解浏览器发送过去的数据经过nginx,nginx处理成符合fastcgi协议标准，传递到php-fpm,经php解释器处理后，一部分是填充一些超全局变量，一部分解析为满足http协议的具体数据(比如form表单数据)，然后进行处理。  
总之关系不大，一个是**`处理动态网页程序`**，一个是**`一种程序间通讯协议`**。  

### http和rpc的区别？为什么要使用rpc框架(hessian等)?
http是一种应用层协议,php使用时，需要自己拼装http协议的头部和内容,如果需要响应头，curl设置 `curl_setopt($curl, CURLOPT_HEADER, true);` 自己解析。  
**RPC是一个软件结构概念**，是构建分布式应用的理论基础.也可以基于http实现，或者基于socket等其他方式实现，个人理解，他的好处就是客户端调用像本地调用一样，方便，简洁，当系统之间交互很多，优点就体现出来了.
如果**`rpc基于tcp实现，传输中更是少了http头部传输，理论上一个请求能更快的完成`**。  

### http长连接和短连接的区别？
HTTP1.1规定了默认保持长连接（HTTP persistent connection ，也有翻译为持久连接），数据传输完成了保持TCP连接不断开（不发RST包、不四次握手），等待在同域名下继续用这个通道传输数据；相反的就是短连接。  
Keep-Alive: timeout=20，表示这个TCP通道可以保持20秒。另外还可能有max=XXX，表示这个长连接最多接收XXX次请求就断开。  
用长连接之后，客户端、服务端怎么知道本次传输结束呢？两部分：1是判断传输数据是否达到了Content-Length指示的大小；2动态生成的文件没有Content-Length，它是分块传输（chunked），这时候就要根据chunked编码来判断，chunked编码的数据在最后有一个空chunked块，表明本次传输数据结束。  

