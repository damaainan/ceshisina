## Nginx、CGI、FastCGI、PHP-CGI、PHP-FPM处理流程

来源：[https://segmentfault.com/a/1190000004638171](https://segmentfault.com/a/1190000004638171)

对于cgi fastcgi php-fpm php-cgi的解释，网上挺多的，可以百度查看，下面是我自己的理解

名词术语：

``` 
1、web服务器
2、通信协议
3、进程、主进程、子进程
4、php解析器

CGI：Common Gateway Interface 公共网关接口，web服务器和脚本语言通信的一个标准、
接口、协议【协议】

FastCGI：CGI协议的升级版【协议】

PHP-CGI: 实现了CGI接口协议的PHP脚本解析器【程序】

PHP-FPM: 管理和调度php-cgi进程，进而实现了FastCGI接口协议的程序【程序】

```

``` 
webserver只能处理静态文件，对于php这样的动态脚本无能为力，只能交给php自己来处理，
于是有了下面这个流程：
```

![][0]

``` 
但是上面架构有个性能问题，CGI对每个请求会parse一遍对应脚本的配置文件（如php.ini），
加载配置和扩展，初始化执行环境，性能非常差，所有有了下面的流程：
```

![][1]

那么实现Fastcgi协议的程序，如PHP-FPM是怎么做的呢？首先，Fastcgi会先启一个master进程，解析配置文件，初始化执行环境，然后再启动多个worker进程，这个worker就是php-cgi。当请求过来时，master会传递给一个worker，然后立即可以接受下一个请求。这样就避免了重复的劳动，效率自然是高。而且当worker不够用时，master可以根据配置预先启动几个worker等着，比如20worker，当然空闲worker太多时，也会停掉一些，这样就提高了性能，也节约了资源。这就是fastcgi的对进程的管理。

下面是php-fpm配置文件里面的对worker数量的配置项：

``` 
; The maximum number of processes FPM will fork. This has been design to control
; the global number of processes when using dynamic PM within a lot of pools.
; Use it with caution.
; Note: A value of 0 indicates no limit
; Default Value: 0
; process.max = 128
```

那么最大的worker进程数就是128

更多的对FastCGI的解释

``` 
    fastcgi是基于cgi架构的扩展，他的核心思想就是在web server和具体cgi程序之间建立一个智能的可持续的中间层，统管cgi程序的运行，这样web server只需要将请求提交给这个层，这个层再派生出几个可复用的cgi程序实例，然后再把请求分发给这些实例，这些实例是可控的，可持续，可复用的，因此一方面避免了进程反复fork，另一方面又可以通过中间层的控制和探测机制来监视这些实例的运行情况，根据不同的状况fork或者回收实例，达到灵活性和稳定性兼得的目的。
```

参考文档

[https://segmentfault.com/q/1010000000256516][2]

[http://blog.csdn.net/zhuanshenweiliu/article/details/46413241][3]

[2]: https://segmentfault.com/q/1010000000256516
[3]: http://blog.csdn.net/zhuanshenweiliu/article/details/46413241
[0]: ../img/bVtCJ0.png
[1]: ../img/bVtCKS.png