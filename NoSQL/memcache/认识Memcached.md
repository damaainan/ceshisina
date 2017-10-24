# 认识 Memcached

 时间 2017-07-24 17:41:25 

原文[https://segmentfault.com/a/1190000010324623][1]



## 简介

`Memcached` 是一个开源、免费、高性能的分布式对象缓存系统，通过减少对数据库的读取以提高 Web 应用的性能； `Memcached` 基于一个存储键/值对的 `hashmap` 。其守护进程（ `daemon` ）是用 C 写的，但是客户端可以用任何语言来编写，并通过 `memcached` 协议与守护进程通信。当某个服务器停止运行或崩溃了，所有存放在该服务器上的键/值对都将丢失。 

`Memcached` 的服务器端没有提供分布式功能，各个 `Memcached` 应用不会互相通信以共享信息。想要实现分布式通过，可以多搭建几个 `Memcached` 应用，通过算法实现此效果； 

`Memcached` 里有两个重要概念： 

* `slab` ：为了防止内存碎片化， `Memcached` 服务器端会预先将数据空间划分为一系列 `slab` ；举个例子，现在有一个100立方米的房间，为了合理规划这个房间放置东西，会在这个房间里放置 30 个 1 立方米的盒子、20 个 1.25 立方米的盒子、15 个 1.5 立方米的盒子...这些盒子就是 `slab` ；
* `LRU` ：**`最近最少使用算法`**；当同一个 `slat` 的格子满了，这时需要新加一个值时，不会考虑将这个新数据放到比当前 `slat` 更大的空闲 slat ，而是使用 `LRU` 移除旧数据，放入这个新数据；

## 部署

`Memcached` 能够在大多数 Linux 和 类 BSD 系统上运行；官方没有给出 Windows 上安装 `Memcached` 的支持； 

对于 Debian / Ubuntu 系统： 

    apt-get install memcached

对于 Redhat / Fedora / CentOs 系统： 

    yum install memcached

通过 memcached -h 查看帮助，同时也算是测试是否安装成功； 

如果遇到错误，可参考官方上的 [FAQ][3] ； 

## 使用

## 服务器端

启动一个 Memcached 应用，常见的启动方式是这样的： 

开启一个 memcached 应用作守护进程， TCP 连接，端口号是 `11211`； `-u` 参数是运行 Memcached 应用的用户（这个参数也只有 root 用户才能使用）； 

    memcached -u root -p 11211 -d -vvv

其他常见的参数也有

1. `-m <num>` ：分配给 Memcached 应用使用的内存大小，默认是 64M；
1. `-l <ip_addr>` ：设置能访问 Memcached 应用的 IP (默认：所有都允许；无论内外网或者本机更换IP，有安全隐患；若设置为 127.0.0.1 就只能本机访问)；
1. `-c <num>` ：设置最大运行的并发连接数，默认是 1024；
1. `-f <factor>` ：设置 slat 大小增长因子；默认是 1.25；比如说 10号 slab 大小是752，那么11号 slab 大小就是 752 * 1.25；

## 客户端

Memcached 客户端与服务器端的通信比较简单，使用的基于文本的协议，而不是二进制协议；因此可以通过 telnet 进行交互； 

    telnet [host] [port]

按下 `Ctrl + ]` ，并回车，即可回显； 

### Storage命令

**`set`**  
存储数据。如果 set 的 key 已经存在，该命令可以更新该key所对应的原来的数据，也就是实现更新的作用。详细命令指南可参考 [菜鸟教程 - Memcached set 命令][4] ； 

**`add`**  
只有在 set 的 key 不存在的情况下，才会存储数据；详细命令指南可参考 [菜鸟教程 - Memcached add 命令][5] ； 

**`replace`**  
只有在 set 的 key 存在的情况下，才会替换数据；详细命令指南可参考 [菜鸟教程 - Memcached replace 命令][6] ； 

**`append`**  
向已存在的元素值后追加数据；详细命令指南可参考 [菜鸟教程 - Memcached append 命令][7] ； 

**`prepend`**  
向已存在的元素值的头部追加数据；详细命令指南可参考 [菜鸟教程 - Memcached prepend 命令][8] ； 

**`cas`**  
命令用于执行一个"检查并设置"的操作。它仅在当前客户端最后一次取值后，该key 对应的值没有被其他客户端修改的情况下，才能够将值写入。检查是通过 cas_token 参数进行的， 这个参数是 Memcach 指定给已经存在的元素的一个唯一的 64 位值。详细命令指南可参考 [菜鸟教程 - Memcached cas 命令][9] ； 

### Retrive命令

**`get`**  
根据元素的键名获取值；详细命令指南可参考 [菜鸟教程 - Memcached get 命令][10] ； 

**`gets`**  
获取带有 CAS 令牌的数据值；详细命令指南可参考 [菜鸟教程 - Memcached gets 命令][11] ； 

**`delete`**  
删除已存在的元素；详细命令指南可参考 [菜鸟教程 - Memcached delete 命令][12] ； 

**`incr/decr`**  
对于已存在的键值进行自增或自减操作；详细命令指南可参考 [菜鸟教程 - Memcached incr/decr 命令][13] ； 

### Statistics命令

**`stats`**  
查看 memcached 所有的统计信息；详细命令指南可参考 [菜鸟教程 - Memcached stats 命令][14] ； 

**`stats items`**  
显示各个 slab 中 item 的数目和存储时长等其它信息；详细命令指南可参考 [菜鸟教程 - Memcached stats items 命令][15] ； 

**`stats slabs`**  
显示各个 slab 的信息，包括 chunk 的大小、数目、使用情况等。详细命令指南可参考 [菜鸟教程 - Memcached stats slabs 命令][16] ； 

**`stats sizes`**  
用于显示所有item的大小和个数。该信息返回两列，第一列是 item 的大小，第二列是 item 的个数。详细命令指南可参考 [菜鸟教程 - Memcached stats sizes 命令][17] ； 

**`flush_all`**  
清除所有缓存数据；详细命令指南可参考 [菜鸟教程 - Memcached flush_all 命令][18] ； 

## 分布式算法

## 取余算法

根据服务器节点数的余数来进行分散，就是通过 hash 函数求得的 Key 的整数哈希值再除以服务器节点数并取余数来选择服务器。这种算法取余计算简单，分散效果好，但是缺点是如果某一台机器宕机，那么应该落在该机器的请求就无法得到正确的处理，这时需要将当掉的服务器从算法从去除，此时候会有 (N-1) / N 的服务器的缓存数据需要重新进行计算；如果新增一台机器，会有N / (N+1)的服务器的缓存数据需要进行重新计算。对于系统而言，这通常是不可接受的颠簸（因为这意味着大量缓存的失效或者数据需要转移）。 

【本段内容摘自 [大脸猫的博客][19] 】 

## 一致性哈希

表现为一个封闭的圆环，圆环上的点分别代表0 ~ 2^32。各个 memcached 节点根据 hash 算法，分别占据圆环上的一个点，当某 key 进行存储操作，会针对 key 进行 hash 操作， hash 后也是圆环上的一个点，那么这个 key 将被存储在顺时针方向的第一个节点上。 

 ![][20]

如上图：分配不均的节点，此时 key 将会被存储到节点C上。 

此时，我们新增节点D，如下图。受影响的部分只有节点A~节点D中间的部分，这边分数据不会再映射到节点B上，而是映射到新增节点D上。减掉一个节点同理，只影响顺时针后面一个节点。

 ![][21]

优点：动态的增删节点，服务器 down 机，影响的只是顺时针的下一个节点 

缺点：当服务器进行hash后值较为接近会导致在圆环上分布不均匀，进而导致 key 的分布、服务器的压力不均匀。若中间某一权重较大的 serverdown 机，命中率下降明显； 

在一致性哈希算法的基础上引入虚拟节点

 ![][22]

引入虚拟节点的思想，解决一致性 hash 算法分布不均导致负载不均的问题。一个真实节点对应若干个虚拟节点，当 key 被映射到虚拟节点上时，则被认为映射到虚拟节点所对应的真实节点上。 

优点：引入虚拟节点的思想，每个物理节点对应圆环上若干个虚拟节点（比如200~300个），当 keyhash 到虚拟节点，就会存储到实际的物理节点上，有效的实现了负载均衡； 

【本段内容摘自 [鱼我所欲也的“memcached学习 - 分布式算法”文章][23] 】 

## 工作中常见的问题

## 缓存雪崩现象

缓存雪崩一般是由某个缓存节点失效，导致其他节点的缓存命中率下降，缓存中缺失的数据去数据库查询，短时间内，造成数据库服务器崩溃；

重启 DB ，短期又被压垮，但缓存数据也多一些； DB 反复多次启动多次，缓存重建完毕， DB 才稳定运行；或者，是由于缓存周期性的失效，比如每 6 小时失效一次，那么每 6 小时，将有一个请求“峰值”，严重者甚至会令DB崩溃； 

## 缓存的无底洞现象（multiget-hole）

该问题由 facebook 的工作人员提出的, facebook 在 2010 年左右， memcached 节点就已经达3000 个.缓存数千 G 内容。 

他们发现了一个问题， memcached 连接频率，效率下降了，于是加 memcached 节点，添加了后，发现因为连接频率导致的问题，仍然存在，并没有好转，称之为“无底洞现象”。 

### 问题分析

以用户为例: user-133-age ， user-133-name ， user-133-height .....N 个 key，当服务器增多，133 号用户的信息，也被散落在更多的节点，所以，同样是访问个人主页，得到相同的个人信息， 节点越多，要连接的节点也越多。 

对于 memcached 的连接数，并没有随着节点的增多，而降低。 于是问题出现。 

### multiget-hole 解决方案

把某一组 key ，按其共同前缀，来分布。比如 user-133-age ， user-133-name ， user-133-height 这 3 个 key，在用分布式算法求其节点时，应该以 ‘ user-133 ’来计算，而不是以 user-133-age/name/height 来计算。 

这样，3 个关于个人信息的 key，都落在同 1 个节点上，访问个人主页时，只需要连接 1 个节点。

## 永久数据被踢现象

网上有人反馈为" memcached 数据丢失"，明明设为永久有效，却莫名其妙的丢失了。 

分析原因：

1. 如果 `slab` 里的很多 `chunk` ，已经过期，但过期后没有被 get 过， 系统不知他们已经过期。
1. 永久数据很久没 get 了， 不活跃， 如果新增 item ，则永久数据被踢了。
1. 当然，如果那些非永久数据被 get ，也会被标识为 `expire` ，从而不会再踢掉永久数据；

解决方案：永久数据和非永久数据分开放；


[1]: https://segmentfault.com/a/1190000010324623

[3]: https://github.com/memcached/memcached/wiki/Install#problems-with-packages
[4]: http://www.runoob.com/memcached/memcached-set-data.html
[5]: http://www.runoob.com/memcached/memcached-add-data.html
[6]: http://www.runoob.com/memcached/memcached-replace-data.html
[7]: http://www.runoob.com/memcached/memcached-append-data.html
[8]: http://www.runoob.com/memcached/memcached-prepend-data.html
[9]: http://www.runoob.com/memcached/memcached-cas.html
[10]: http://www.runoob.com/memcached/memcached-get-data.html
[11]: http://www.runoob.com/memcached/memcached-get-cas-data.html
[12]: http://www.runoob.com/memcached/memcached-delete-key.html
[13]: http://www.runoob.com/memcached/memcached-incr-decr.html
[14]: http://www.runoob.com/memcached/memcached-stats.html
[15]: http://www.runoob.com/memcached/memcached-stats-items.html
[16]: http://www.runoob.com/memcached/memcached-stats-slabs.html
[17]: http://www.runoob.com/memcached/memcached-stats-sizes.html
[18]: http://www.runoob.com/memcached/memcached-clear-data.html
[19]: http://blog.sina.com.cn/s/blog_7141f8900100nia9.html
[20]: ./img/NfuaQjN.png
[21]: ./img/n6JRf2B.png
[22]: ./img/mmEziqE.png
[23]: http://www.cnblogs.com/douJiangYouTiao888/p/6267542.html