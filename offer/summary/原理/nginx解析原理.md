<font face=微软雅黑>


[深入理解PHP之：Nginx 与 FPM 的工作机制](../../../php_base/cgi_fpm/深入理解PHP之：Nginx 与 FPM 的工作机制.md)


为了能够使 Nginx 理解 `fastcgi` 协议，Nginx 提供了 **`fastcgi` 模块**来将 http 请求映射为对应的 `fastcgi` 请求。

Nginx 的 `fastcgi` 模块提供了 **`fastcgi_param`** 指令来主要处理这些映射关系，下面 Ubuntu 下 Nginx 的一个配置文件，其主要完成的工作是将 Nginx 中的变量翻译成 PHP 中能够理解的变量。

除此之外，非常重要的就是 `fastcgi_pass` 指令了，这个指令用于指定 **`fpm` 进程**监听的地址，Nginx 会把所有的 php 请求翻译成 `fastcgi` 请求之后再发送到这个地址。下面一个简单的可以工作的 Nginx 配置文件：




</font>