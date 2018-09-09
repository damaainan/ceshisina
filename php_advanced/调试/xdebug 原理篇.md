# 成为高级 PHP 程序员的第一步——调试（xdebug 原理篇） 

5个月前 ⋅ 2433 ⋅ 36 ⋅ 6 

[文章参考：Xdebug工作原理][0]  
xdebug 对于许多 phper 应该一点也不陌生。说来惭愧，对于常年使用 `var_dump`，`echo`，现在又多了一个`dd` ，来调试程序的猿来说，这种方式实在有点过时。今天花了一些时间好好了解了一下，现在就整理一下这款，能提升你调试效率以及逼格满满的调试利器———— xdebug

### 先讲一下 xdebug 工作原理，总结为下面几个步骤：[#][1]

1. IDE（比如 PhpStorm ，下文所述的客户端）中已经集成了一个遵循 BGDp 的 Xdebug 插件。当要 debug 的时候，点击一些 IDE 的某个按钮，启动这个插件。该插件会启动一个 9000 的端口监听远程服务器发过来的 debug 信息。  
   
    **phpstorm 中，开启 / 关闭的位置为：`工具栏 > Run > Start / Stop Listening for PHP Xdebug Connetions`**

1. 浏览器向 Httpd 服务器发送一个带有 `XDEBUG_SESSION_START` 参数的请求，服务器收到这个请求之后交给后端的PHP（已开启 xdebug 模块）进行处理。
1. Php 看到这个请求是带了 `XDEBUG_SESSION_START` 参数，就告诉 Xdebug，“嘿，我要debug喔，你准备一下”。这时，Xdebug 会向来源 ip 客户端的9000端口（默认是 9000 端口）发送一个debug请求，然后客户端的 9000 端口响应这个请求，那么 debug 就开始了。

    **这里通知客户端其实有两种方式，根据 xdebug 的配置 `xdebug.remote_connect_back = 0 | 1` 使用不同的通知方式，下文会详细介绍**

1. Php 知道 Xdebug 已经准备好了，那么就开始开始一行一行的执行代码，但是每执行一行都会让 Xdebug 过滤一下，Xdebug 在过滤每一行代码的时候，都会暂停代码的执行，然后向客户端的 9000 端口发送该行代码的执行情况，等待客户端的决策（是一句代码还是下一个断点待）。。
1. 相应，客户端（IDE）收到 Xdebug 发送过来的执行情况，就可以把这些信息展示给开发者看了，包括一些变量的值等。同时向 Xdebug 发送下一步应该什么。

### 以上就是整个工作流程，下面介绍一下两种通知客户端的方式：[#][2]

Xdebug 的官方文档给了两张很清楚的交互图

#### 第一种，静态绑定客户端 host[#][3]

`xdebug.remote_connect_back = 0` ，也是 xdebug 的默认方式，这种情况下，xdebug 在收到调试通知时会读取配置 `xdebug.remote_host` 和 `xdebug.remote_port` ，默认是 localhost:9000，然后向这个端口发送通知

![file][4]

> 可以看到，`remote_host` 的 IP 是固定的，这种方式只适合单一客户端开发调试

#### 第二种，不绑定 IP，根据请求来源通知[#][5]

`xdebug.remote_connect_back = 1`，这种方式和上面基本相同，唯一不同的是，php 在 接受 http 请求后，xdebug 会将请求来源的 IP 绑定，并通知

![file][6]

> 以上就是所有 xdebug 工作原理相关的介绍，下一篇讲在 `homestead + phpstorm` 作为开发环境如何具体配置并使用 xdebug

[0]: https://my.oschina.net/atanl/blog/371424
[1]: #先讲一下-xdebug-工作原理总结为下面几个步骤
[2]: #以上就是整个工作流程下面介绍一下两种通知客户端的方式
[3]: #第一种静态绑定客户端-host
[4]: ./img/WZ8PZTpI79.gif
[5]: #第二种不绑定-IP根据请求来源通知
[6]: ./img/9AXPuV1r97.gif