# Memcache和Redis的选型分析

 时间 2017-08-15 14:03:54  刘召考的博客

原文[http://www.liuzk.com/337.html][1]


Memcache和Redis都能很好的满足解决数据库表数据量极大（千万条），要求让服务器更加快速地响应用户的需求的问题，它们性能都很高，总的来说，可以把Redis理解为是对Memcache的拓展，是更加重量级的实现，提供了更多更强大的功能。具体来说：

## 1.性能上：

性能上都很出色，具体到细节，由于`Redis只使用单核`，而`Memcache可以使用多核`，所以平均每一个核上Redis在存储小数据时比，Memcache性能更高。

而在**100k以上的数据中，Memcache性能要高于Redis**，虽然redis最近也在存储大数据的性能上进行优化，但是比起 Memcache，还是稍有逊色。

## 2.内存空间和数据量大小：

MemCache可以修改最大内存，采用**LRU算法**。

Redis增加了VM的特性，突破了物理内存的限制。

## 3.操作便利上：

**MemCache数据结构单一**，仅用来缓存数据。

而Redis支持更加丰富的数据类型，也可以在服务器端直接对数据进行丰富的操作，这样可以减少网络IO次数和数据体积。

## 4.可靠性上：

**MemCache不支持数据持久化**，断电或重启后数据消失，但其稳定性是有保证的。

**Redis支持数据持久化和数据恢复**，允许单点故障，但是同时也会付出性能的代价。

## 5.应用场景：

Memcache：动态系统中减轻数据库负载，提升性能；做缓存，适合多读少写，大数据量的情况（如人人网大量查询用户信息、好友信息、文章信息等）。

Redis：适用于对读写效率要求都很高，数据处理业务复杂和对安全性要求较高的系统（如新浪微博的计数和微博发布部分系统，对数据安全性、读写要求都很高）。

其它：

1、Memcache单个key-value大小有限，一个value最大只支持1MB，而**Redis最大支持512MB**。

2、Memcache只是个**内存缓存**，对可靠性无要求；而`Redis更倾向于内存数据库`，因此对对可靠性方面要求比较高

3、从本质上讲，Memcache只是一个单一key-value内存Cache；而Redis则是一个数据结构内存数据库，支持五种数据类型，因此Redis除单纯缓存作用外，还可以处理一些简单的逻辑运算，Redis不仅可以缓存，而且还可以作为数据库用

4、新版本（3.0）的Redis是指集群分布式，也就是说集群本身均衡客户端请求，各个节点可以交流，可拓展行、可维护性更强大。

其他参考材料：摘自 [http://www.cnblogs.com/EE-NovRain/p/3268476.html][4]

在stackoverflow网站上，有一段来自Redis作者的回答：

You should not care too much about performances. Redis is faster per core with small values, but memcached is able to use multiple cores with a single executable and TCP port without help from the client. Also memcached is faster with big values in the order of 100k. Redis recently improved a lot about big values (unstable branch) but still memcached is faster in this use case. The point here is: nor one or the other will likely going to be your bottleneck for the query-per-second they can deliver.

You should care about memory usage. For simple key-value pairs memcached is more memory efficient. If you use Redis hashes, Redis is more memory efficient. Depends on the use case.

You should care about persistence and replication, two features only available in Redis. Even if your goal is to build a cache it helps that after an upgrade or a reboot your data are still there.

You should care about the kind of operations you need. In Redis there are a lot of complex operations, even just considering the caching use case, you often can do a lot more in a single operation, without requiring data to be processed client side (a lot of I/O is sometimes needed). This operations are often as fast as plain GET and SET. So if you don’t need just GEt/SET but more complex things Redis can help a lot (think at timeline caching).

翻译如下[1]：

没有必要过多的关注性能。由于Redis只使用单核，而Memcached可以使用多核，所以在比较上，平均每一个核上Redis在存储小数据时比Memcached性能更高。而在100k以上的数据中，Memcached性能要高于Redis，虽然Redis最近也在存储大数据的性能上进行优化，但是比起Memcached，还是稍有逊色。说了这么多，结论是，**无论你使用哪一个，每秒处理请求的次数都不会成为瓶颈**。

你需要关注内存使用率。对于key-value这样简单的数据储存，**memcache的内存使用率更高**。**如果采用`hash结构`，redis的内存使用率会更高**。当然，这些都依赖于具体的应用场景。

你需要关注关注**`数据持久化`**和**`主从复制`**时，只有redis拥有这两个特性。如果你的目标是构建一个缓存在升级或者重启后之前的数据不会丢失的话，那也只能选择redis。

你应该关心你需要的操作。redis支持很多复杂的操作，甚至只考虑内存的使用情况，在一个单一操作里你常常可以做很多，而不需要将数据读取到客户端中（这样会需要很多的IO操作）。这些复杂的操作基本上和纯GET和POST操作一样快，所以你不只是需要GET/SET而是更多的操作时，redis会起很大的作用。

 对于两者的选择还是要看具体的应用场景，如果需要缓存的数据只是key-value这样简单的结构时，我在项目里还是采用memcache，它也足够的稳定可靠。如果涉及到存储，排序等一系列复杂的操作时，毫无疑问选择redis。


[1]: http://www.liuzk.com/337.html

[4]: http://www.cnblogs.com/EE-NovRain/p/3268476.html