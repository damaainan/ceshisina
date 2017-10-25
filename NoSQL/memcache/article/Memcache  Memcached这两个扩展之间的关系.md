# 话说PHP的Memcache  Memcached这两个扩展之间的关系，你都摸清楚了吗？

 时间 2017-07-11 16:46:20  

原文[http://mdsa.51cto.com/art/201707/544569.htm][2]


【51CTO.com原创稿件】Memcached是一个免费开源的、高性能的、分布式内存缓存系统，对于很多WEB程序员来说，对它应该非常熟悉，很多WEB程序员经常用它将数据库里面的数据缓存起来从而提供网站或者应用的性能，而PHP作为网站开发的热门语言，肯定也是支持Memcached的，但是当我们打开PHP的官方手册，发现一个有趣的情况，就是PHP有两个扩展提供了类似的功能，它们就是Memcache和Memcached扩展，这两个扩展的关系是什么？以及两个扩展是不是相同的？带着这些问题，下面我们就来一一进行分析。

#### 一些基本的概念

其实，Memcached就是一个C/S应用，所以有下面两个基本概念： 

* Memcached服务端。就是真正提供数据缓存的应用端，这个端是一个独立的进程，并且开放相应端口供Memcached客户端对数据进行增删改查等等操作。

* Memcached客户端。只要是能够与Memcached服务端进行通讯、并且完成相应的数据操作功能，我们都可以称之为Memcached客户端，比如本文说的PHP两个扩展，都可以称之为客户端。

#### 两个扩展的相关知识和运行原理

在pecl官方扩展库，我们能够看到，Memcache扩展的生日是2004年2月26日，而Memcached扩展的生日是2009年1月29日，哈哈，看着这两个生日，大家是不是想到了什么呢？ 

从上面的生日，我们能够看到，Memcache是先出生的，而Memcached是后出生的，下面我们再来看看二者的定义： 

* Memcache扩展。该扩展是一个提供了面向过程和面向对象两种方式的扩展。
* Memcached。这个扩展使用libmemcached库与Memcached服务程序进行通信。

看了两个扩展的定义，越来越有意思了，我们似乎离真相越来越近了，只不过还差一步，就是这个`libmemcached`到底是什么，我们接着思考这个问题，继续打开Memcached的官方文档，我们不难发现，其实这个`libmemcached`就是Memcached提供的官方客户端，换句话说，php的Memcached扩展其实就是一个二次封装扩展，该扩展站在官方提供的客户端扩展的肩上，所以提供的功能肯定就多，而反观Memcache扩展仅仅是PHP自己实现的一套Memcached扩展库而已，说到这里，想必很多PHP程序员应该明白了，为什么我们不再提倡使用Memcache扩展的原因，下图是两个扩展的工作原理。

![][5]

#### 在PHP里面还有一个特殊的扩展

上面分析了两个扩展，其实这两个扩展的情况在PHP里面并不是唯一的，在PHP里面还有一个知识点和这个情况是一样的，它就是我们接下来要说的Mysqlnd和libmysqlclient。 

通过前面的分析，我们不难想到，libmysqlclient其实就是MySQL数据库官方提供的MySQL编程客户端，而Mysqlnd其实就是PHP自己实现的MySQL编程客户端，它不是站在libmysqlclient的肩上进行二次开发的，它是用C语言编写的。

![][6]

其实，Memcached不仅仅支持PHP语言，还支持C、C++、Java、MySQL、Python、Nodejs等等，通过上面的Memcache扩展，我们知道，只要按照它提供的协议，很容易实现自己领域编程语言的Memcached客户端


[2]: http://mdsa.51cto.com/art/201707/544569.htm

[5]: https://img2.tuicool.com/77jE3qn.png
[6]: https://img2.tuicool.com/aemUJbY.png