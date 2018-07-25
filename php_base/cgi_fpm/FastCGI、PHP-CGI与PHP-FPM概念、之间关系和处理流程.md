## Web Service(Nginx、Apache)、FastCGI、PHP-CGI与PHP-FPM概念、之间关系和处理流程

来源：[https://segmentfault.com/a/1190000008627499](https://segmentfault.com/a/1190000008627499)


## 一、名词解释与说明
### 1.1 Web Service
 **`Web Server（Apache/Nginx/IIS）只是内容的分发者`** 。比如，如果请求`/index.html`，那么`web server`会去文件系统中找到这个文件，发送给浏览器，这里分发的是静态数据。好了，如果现在请求的是`/index.php`，根据配置文件，`Nginx`知道这个不是静态文件，需要去找PHP解析器来处理，那么他会把这个请求简单处理后交给PHP解析器。`Nginx`会传哪些数据给 **``PHP解析器``** 呢？`url`要有吧，查询字符串也得有吧，`POST`数据也要有，`HTTP header`等信息。
### 1.2 CGI

 **`CGI`就是规定要传哪些数据、以什么样的格式传递给后方处理这个请求的`协议`。** 
当`webserver`收到`/index.php`这个请求后，会启动对应的`CGI`程序，这里就是 **`PHP的解析器`** 。接下来`PHP`解析器会解析`php.ini`文件，初始化执行环境，然后处理请求，再以规定`CGI`规定的格式返回处理后的结果，退出进程。`webserver`再把结果返回给浏览器。

* **`CGI执行流程`** 

![][0]

好了，`CGI`是个协议，跟进程什么的没关系。
### 1.3 FastCGI

那`FastCGI`又是什么呢？ **`FastCGI`是用来提高`CGI`程序性能的。** 
那么`CGI`程序的性能问题在哪呢？

PHP解析器会解析php.ini文件，初始化执行环境
就是这里了。 **`标准的`CGI`对`每个请求都会执行这些步骤`** （不闲累啊！启动进程很累的说！），所以处理每个时间的时间会比较长。这明显不合理嘛！

那么`FastCGI`是怎么做的呢？首先，`FastCGI`会先启一个`master`，解析配置文件，初始化执行环境，然后再启动多个`worker`。当请求过来时，`master`会传递给一个`worker`，然后立即可以接受下一个请求。这样就避免了重复的劳动，效率自然是高。而且当`worker`不够用时，`master`可以根据配置预先启动几个`worker`等着；当然空闲`worker`太多时，也会停掉一些，这样就提高了性能，也节约了资源。这就是`FastCGI`的对进程的管理。
好了，`FastCGI`是`CGI`的升级版，一种语言无关的协议，用来沟通程序(如`PHP, Python, Java`)和`Web`服务器(`Apache`,`Nginx`,`IIS`), 理论上任何语言编写的程序都可以通过`FastCGI`来提供`Web`服务。`FastCGI`的特点是会在一个进程中依次完成多个请求，以达到提高效率的目的，大多数`FastCGI`实现都会维护一个进程池。
### 1.4 PHP-FPM

那`PHP-FPM`又是什么呢？是一个实现了 **`FastCGI`** 的程序，被`PHP`官方收了。
大家都知道， **`PHP的解释器是php-cgi。php-cgi只是个CGI程序，他自己本身只能解析请求，返回结果，不会进程管理`** （皇上，臣妾真的做不到啊！）所以就出现了一些能够 **`调度php-cgi进程的程序`** ，比如说由`lighthttpd`分离出来的`spawn-fcgi`。好了`PHP-FPM`也是这么个东东，在长时间的发展后，逐渐得到了大家的认可（要知道，前几年大家可是抱怨`PHP-FPM`稳定性太差的），也越来越流行。

好了，`PHP-FPM`就是针对于`PHP`的，`FastCGI`的一种实现，他负责管理一个进程池，来处理来自`Web`服务器的请求。目前，`PHP-fpm`是内置于`PHP`的。
但是`PHP-fpm`仅仅是个“`PHP Fastcgi`进程管理器”, 它仍会调用`PHP`解释器本身来处理请求，`PHP`解释器(在`Windows`下)就是`php-cgi.exe`.

* **`FastCGI执行流程`** 

![][1]
### 1.5 Q&A

网上有的说，`fastcgi`是一个协议，`php-fpm`实现了这个协议

对。 
有的说，`php-fpm`是`fastcgi`进程的管理器，用来管理`fastcgi`进程的对。  
php-fpm的管理对象是php-cgi。但不能说php-fpm是fastcgi进程的管理器， **`因为前面说了fastcgi是个协议`** ，似乎没有这么个进程存在，就算存在php-fpm也管理不了他（至少目前是）。


有的说，`php-fpm`是`php`内核的一个补丁

以前是对的。因为最开始的时候`php-fpm`没有包含在`PHP`内核里面，要使用这个功能，需要找到与源码版本相同的`php-fpm`对内核打补丁，然后再编译。后来`PHP`内核集成了`PHP-FPM`之后就方便多了，使用`--enalbe-fpm`这个编译参数即可。
有的说，修改了`php.ini`配置文件后，没办法平滑重启，所以就诞生了`php-fpm`是的，修改`php.ini`之后，`php-cgi`进程的确是没办法平滑重启的。`php-fpm`对此的处理机制是新的`worker`用新的配置，已经存在的`worker`处理完手上的活就可以歇着了，通过这种机制来平滑过度。
还有的说`PHP-CGI`是`PHP`自带的`FastCGI`管理器，那这样的话干吗又弄个`php-fpm`不对。`php-cgi`只是解释`PHP`脚本的程序而已。
## 二、白话解释

       你(`PHP`)去和爱斯基摩人(`web`服务器，如`Apache`、`Nginx`)谈生意你说中文(`PHP`代码)，他说爱斯基摩语(`C`代码)，互相听不懂，怎么办？那就都把各自说的话转换成英语(`FastCGI`协议)吧。
       怎么转换呢？你就要使用一个翻译机(`PHP-FPM`) (当然对方也有一个翻译机，那个是他自带的)。 **`PHP的解释器是php-cgi。php-cgi只是个CGI程序，他自己本身只能解析请求，返回结果，不会进程管理`** （皇上，臣妾真的做不到啊！）所以就出现了一些能够 **`调度php-cgi进程的程序`** ，比如说由`lighthttpd`分离出来的`spawn-fcgi`。好了`PHP-FPM`也是这么个东东

我们这个翻译机是最新型的，老式的那个（`PHP-CGI`）被淘汰了。不过它(`PHP-FPM`)只有年轻人（`Linux`系统）会用，老头子们（`Windows`系统）不会摆弄它，只好继续用老式的那个。
## 三、总结
### 3.1 CGI与FastCGI关系


* CGI与FastCGI都是一种 **`通讯协议`** ，是 **`WebSever`** （Apache/Nginx/IIS）与 **`其它程序`** （此程序通常叫做CGI程序，如PHP脚本解析器） **`之间通讯的桥梁`** 。
* `FastCGI`是`CGI`的改良进化版，`FastCGI`相比`CGI`更安全、性能更好,所以现在都是使用`FastCGI`协议进行通讯。
* FastCGI兼容CGI。


### 3.2 PHP-CGI与PHP-FPM


* PHP-CGI其实就是PHP脚本解析器，他是CGI协议的实现
* PHP-FPM就是FastCGI协议的实现
* `PHP-CGI`和`PHP-FPM` **`都是程序`** ，是具体实现WEB容器和PHP语言之间解析请求，返回结果的程序。


[推荐文档：Nginx、CGI、FastCGI、PHP-CGI、PHP-FPM处理流程][2]  
[推荐文档2：cgi php-cgi,PHP底层原理][3]  
[参考文档：CGI、FastCGI、PHP-CGI、PHP-FPM个人理解][4]  
[参考文档2：搞不清FastCgi与PHP-fpm之间是个什么样的关系][5]  

[2]: https://segmentfault.com/a/1190000004638171
[3]: http://blog.csdn.net/zhuanshenweiliu/article/details/46413241
[4]: https://segmentfault.com/a/1190000008237887
[5]: https://segmentfault.com/q/1010000000256516
[0]: ../img/bVTRC0.png 
[1]: ../img/bVTRDb.png 