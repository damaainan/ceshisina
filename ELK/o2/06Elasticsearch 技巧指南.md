## 【通天塔之日志分析平台】陆 Elasticsearch 技巧指南 

  发表于 2016-11-19  |    更新于 2017-08-03    |    分类于  [Technique][0]    |     |   951     3,508  |    13

前面我们已经把系统搭建完成，但是具体的应用都比较简单。这一次我们来详细了解一下 Elasticsearch，尤其是如何利用其内置的各种强大功能来完成我们的需求。

- - -

更新历史

* 2016.11.24: 完成初稿（部分内容后续会陆续完善）

## 系列文章

* [『通天塔』技术作品合集介绍][1]
* [零 系列简介与环境配置][2]
* [壹 ELK 环境搭建][3]
* [贰 Kafka 缓冲区][4]
* [叁 监控、安全、报警与通知][5]
* [肆 从单机到集群][6]
* [伍 Logstash 技巧指南][7]
* [陆 Elasticsearch 技巧指南][8]
* [柒 Kibana 技巧指南][9]
* [捌 实例：接入外部应用日志][10]

## 任务目标

1. 了解 Elasticsearch 的底层存储
1. 熟悉 HTTP 接口和 RESTful API
1. 了解 Elasticsearch 的调优与运维

## 基本原理

这部分内容虽然不一定对工程有立竿见影的帮助，但是知其然还知其所以然，才是高手的做事风格。那么问题来了

> 写入的数据是如何变成 Elasticsearch 里可以被检索和聚合的索引内容的？

关键在于『倒排索引』，新收到的数据会被写入到内存的 buffer 中，然后在一定的时间间隔后刷到磁盘中，成为一个新的 segment，然后另外使用一个 commit 文件来记录所有的 segment，数据只有在成为 segment 之后才能被检索。默认的从 buffer 到 segment 的时间间隔是 1 秒，基本已经是『实时』了，如果需要更改，也可以调用 /_refresh 接口。不过很多时候我们不需要这么『实时』，所以可以加大这个时间间隔，以获得更快的写入性能。导入历史数据时甚至可以关闭，导入完成再重新开启。

为了保证数据从 buffer 到 segment 的一致性，Elasticsearch 还会有一个名为 Translog 的记录，至于 Translog 的一致性则是通过定期保存到磁盘中来实现的

前面说过 Lucene 会不断开新文件，这样磁盘上就会有一堆小文件，所以 ES 会在后台把这些零散的 segment 做数据归并，归并完成后就可以把小的 segment 删掉，也就减少了 segment 的数量了。为了不影响 IO 和 CPU，会对归并线程做一定的限制，我们可以根据硬件的不同来调整 indices.store.throttle.max_bytes_per_sec 来提高性能。与此同时，我们也有不同的归并策略，不过总体来说就是让我们加大 flush 的间隔，尽量让每次新生成的 segment 本身就比较大。

ES 的分布式处理主要是通过 sharding 机制，也会保留副本进行冗余备份，具体采用的是 gossip 协议，配置也不算复杂，这里就不赘述，如果有机会专门写一篇实例教程。

## 操作管理

配置文件在 /etc/elasticsearch/elasticsearch.yml，重启命令 sudo service elasticsearch restart## 增删改查

ES 虽然不是数据库，不过其特性决定了，这就是一个很好的 NoSQL 数据库嘛，因为 ELK stack 的缘故，写入由 Logstash 负责，查询由 Kibana 负责，不过修改和删除就有些无能为力了（毕竟为什么要简单修改和删除日志？），可是修改和删除是数据库必须的功能，好在 ES 提供了 RESTful 接口来处理 JSON 请求，最简单的用 curl 就可以完成各类操作。这里推荐一个 Chrome 的插件 Postman，可以很方便进行各类测试。具体如何发送请求请参考文档，这里不赘述了。

## 搜索

前面的增删改查针对的是单条记录，ES 中更重要的是搜索。这里回顾一下：刚写入的数据，可以通过 translog 立刻获取；但是直到其成为一个 segment 之后，才能被搜索到

可以利用 /_search?q= 这种 querystring 的简单语法，或者发送完整的 json 来进行查询。具体可以依据版本查阅文档，这里不赘述。

另外，聚合、管道聚合

## 其他功能

Elasticsearch 目前已经可以和 Hadoop, HDFS, Spark Streaming 等大数据工具连接使用。如果需要配置权限，可以使用 Elastic 官方的 Shield，如果想用开源的话，可以使用 [search-guard][11]，这样不同的用户可以访问不同的索引，达到权限控制。

监控集群健康状态也可以通过接口访问，比如 curl -XGET 127.0.0.1:9200/_cluster/health?pretty，更多监控信息请参阅文档，这里不赘述。

需要提的一点就是 GC 是非常影响性能的，所以我们来简单介绍一下 JVM 的机制。启动 JVM 虚拟机的时候，会分配固定大小的内存块，也就是堆 heap。堆又分成两组，Young 组是为新实例化的对象所分配的空间，比较小，一般来说几百 MB，Young 组内又分为两个 survivor 空间。Young 空间满了后，就垃圾回收一次，还存活的对象放到幸存空间中，失效的就被移除。Old 组就是保存那些重启存活且一段时间不会变化的内容，对于 ES 来说可能有 30 GB 内存是 Old 组，同样，满了之后就垃圾回收。

垃圾回收的时候，JVM 采用的是 STW(Stop The World) 机制，Young 组比较小还好，但是 Old 组可能需要几秒十几秒，那就是服务器无响应啊！所以我们必须非常关注 GC 性能。

如果 ES 集群中经常有很耗时的 GC，说明内存不足，如果影响集群之间 ping 的话，就会退出集群，然后因为分片缘故导致更大的影响。我们可以在节点状态中的 jvm 部分查看对应的数值，最重要是 heap_used_percent，如果大于 75，那么就要垃圾回收了，如果长期在 75 以上，那就是内存不足。

注：节点状态可以通过 curl -XGET http://127.0.0.1:9200/_nodes/stats 查看，下面是一个例子（省略了部分内容）：

    
```
{

  "cluster_name" : "wdxtub",

  "nodes" : {

    "M-OzSwFBTc6uU8ndWU1SFw" : {

      "timestamp" : 1470310258934,

      "name" : "Kleinstocks",

      "transport_address" : "127.0.0.1:9302",

      "host" : "127.0.0.1",

      "ip" : [ "127.0.0.1:9302", "NONE" ],

      "indices" : {

        "docs" : {

          "count" : 7240861,

          "deleted" : 257

        },

        "store" : {

          "size_in_bytes" : 1836976476,

          "throttle_time_in_millis" : 0

        },

        ...

      "fs" : {

        "timestamp" : 1470310262388,

        "total" : {

          "total_in_bytes" : 316934193152,

          "free_in_bytes" : 32878755840,

          "available_in_bytes" : 16755851264

        },

        "data" : [ {

          "path" : "/data2/active2/dji-active/nodes/0",

          "mount" : "/data2 (/dev/xvdf)",

          "type" : "ext4",

          "total_in_bytes" : 316934193152,

          "free_in_bytes" : 32878755840,

          "available_in_bytes" : 16755851264,

          "spins" : "false"

        } ]

      },

      "transport" : {

        "server_open" : 0,

        "rx_count" : 30,

        "rx_size_in_bytes" : 8193,

        "tx_count" : 36,

        "tx_size_in_bytes" : 13202

      }

    }

  }

}
```
状态比较多，这里挑几个说一下，首先是 gc 部分，显示的是 young 和 old gc 的耗时，一般来说 young 会比较大，这是正常的。一次 young gc 大概在 1-2ms，old gc 在 100 ms 左右，如果有量级上的差距，建议打开 slow-gc 日志，具体研究原因。

thread_pool 是线程池信息，我们主要看 rejected 的数据，如果这个数值很大，就说明 ES 忙不过来了。

其他的基本就是系统和文件系统的数据如果 fielddata_breaker.tripped 数值太高，那么就需要优化了。

其他一些监控接口

* hot_threads 状态 curl -XGET 'http://127.0.0.1:9200/_nodes/_local/hot_threads?interval=60s'
* 等待执行的任务列表 curl -XGET http://127.0.0.1:9200/_cluster/pending_tasks{ "tasks": [] }
* 可以用 /_cat 接口，具体参考文档
    * 集群状态 curl -XGET http://127.0.0.1:9200/_cat/health?v
    * 节点状态 curl -XGET http://127.0.0.1:9200/_cat/nodes?v

Elasticsearch 的日志在 $ES_HOME/logs/ 中，或者可以使用 官方自己的监控工具 - marvel。如果在生产环境中，最好使用 nagios, zabbix, ganglia, collectd 这类监控系统。

## 优化

### 合理计划服务器

在 Elasticsearch 的配置文件中，可以根据两个配置(node.master 和 node.data)选项来分配不同节点的角色，以达到提高服务器性能的目的。

* node.master: false; node.data: true - 该节点只作为数据节点，用于存储和查询，资源消耗会较低
* node.master: true; node.data: false - 该节点只作为 master 节点，不存储数据，主要负责协调索引请求和查询请求
* node.master: false; node.data: falst - 该节点不作为 master 节点，也不存储数据，主要用于查询时的负载均衡（做结果汇总等工作）

另外，一台服务器最好只部署一个节点以维持服务器稳定，毕竟资源是有限的，多开也没啥

### 数据节点就是数据节点

如果有配置数据节点，那么可以关闭其 http 功能，让它专注于索引的操作。插件之类的也最好安装到非数据节点服务器上，这样是一个兼顾数据安全和服务器性能的考虑。具体的配置项是 http.enabled: false### 线程池配置

针对 Elasticsearch 的不同操作，可以配置不同大小的线程池，这个需要根据业务需求确定最佳值，场景的操作有：index, search, suggest, get, bulk, percolate, snapshot, snapshot_data, warmer, refresh。

这里以 index(创建/更新/删除索引数据)和 search(搜索操作)为例：

    
```
threadpool:

     index:

         type: fixed

         size: 24（逻辑核心数*3）

         queue_ size: 1000

     search:

         type: fixed

         size: 24（逻辑核心数*3）

         queue_ size: 1000
```
### 分片与副本

默认的参数是 5 个分片(shard)和 1 个副本(replica)，碎片数目越多，索引速度越快；副本数目越多，搜索能力及可用性更高。分片的数目是在一开始就设定好的，但是副本的数目是可以后期修改的。

而在恢复数据的时候，可以先减少分片刷新索引的时间间隔，如

    
```
curl -XPUT 'http://10.1.1.0:9200/_settings' -d '{ 

    "index" : { 

        "refresh_interval" : "-1" 

    } 

}'
```
完成插入之后再恢复

    
```
curl -XPUT 'http://10.1.1.0:9200/_settings' -d '{ 

    "index" : { 

        "refresh_interval" : "1s" 

    } 

}'
```
### 查询

查询中最重要的思路就是 routing，尽量减少慢查询的次数。而当索引越来越大的时候，每个分片也会增大，查询速度就会变慢。一个可行的解决思路就是分索引，比方说不同类型的数据利用不同的 routing 进行分离。

还有一个从业务出发的思路，就是不索引不需要的字段，这样就可以减小集群所需资源的量。

### JVM 设置

关于 JVM 的设置我还在摸索中，不过有几个技巧：

* JVM 的堆大小不要超过 32G，来源 [Don’t Cross 32 GB!][12]
* 使用 bootstrap.mlockall: true，启动时就锁定内存
* 用较小的 heapsize 配合 SSD

### Full GC 问题

这里以一个实例来介绍我是如何在生产环境中排查和修复 Elasticsearch 集群忽然响应时间剧增的问题的。

情况是这样的，随着接入 Elasticsearch 的数据量增大，忽然有一个周末出问题了 - ES 集群的查询和插入都变得巨慢无比。监控报警都把邮箱和手机发爆炸了。

那么问题来了，究竟是哪里出了乱子？

因为发送数据的客户端和服务器近期并没有特别大的改动，我检查了 Kafka 队列也一切正常，于是可以锁定问题出在 Elasticsearch 身上。

第一反应就是先去看 Elasticsearch 的日志，发现根据日志显示，一致在不停的垃圾回收。因此对症下药，把 JVM 的堆内存改大。但是在集群重启之后仍然会出现性能急剧下降的状况，于是继续检查日志，发现是因为 JVM 进行 Full GC 的时间过长，导致 ES 集群认为拓扑结构改变，开始迁移数据所导致。而迁移数据本身又会导致 Full GC，让情况更糟的是，在 Full GC 结束之后，集群的拓扑结构又再次改变，于是就陷入了这样的死循环。

破局的方法其实非常简单粗暴，把检测集群拓扑的时间间隔和超时次数加大一点，留足够的时间给 JVM 进行 Full GC 即可。

### 导入数据过慢问题

最近在从 MySQL 数据库中导入大量数据到 Elasticsearch 的时候，出现写入极其缓慢，甚至在使用了 bulk（批量）接口之后也没有改善的问题。奇怪的是，从 MySQL 的表 A 和表 B 中导入甚至会有几十倍的速度差距，这是为什么呢？

经过一步一步排查，基本上 ES 的文档和可以配置的参数都调整过之后并没有改善，于是开始从数据源入手，最后发现表 A 和 表 B 的数据顺序是不太一样的。表 A 中基本是顺序递增的数据，主键（自增长 ID）基本对应于时间顺序；而表 B 中则基本是随机插入的，所以按照数据库中的 ID 进行顺序导出，就会发现相邻记录对应的日期可能相差很大，而正好我们在 ES 中又是根据日期来进行索引的切割的，导致每次都需要在不同的索引中进行切换，速度自然上不去。

所以我们把从 MySQL 数据库中选择数据的语句利用 timestamp 作为 order by 的标准，导入速度就很快了。

这里有一点需要注意每次除了 ID 之外，还需要记录 timestamp 的值，这样才能保证是顺序导入的 where id > xxx and timestamp > xxx，其中 timestamp 每次需要归 0。

## 试一试

1. 通过 JVM 命令查看 Elasticsearch 的运行状况
1. 尝试不同的检索，通过 Profile API 来判断到底是哪一步最耗时
1. 有没有办法快速重启 Elasticsearch 集群，尽量减少分区恢复的时间？

## 总结

Elasticsearch 的调优其实是一个玄学问题，这里的建议是直接把 SSD 和 内存申请够，不然出现各种奇葩问题非常头疼。好消息是，一旦稳定下来，Elasticsearch 还是非常好用的，所以学会这一套，性价比是不低的。

[0]: /categories/Technique/
[1]: http://wdxtub.com/2016/11/19/babel-series-intro/
[2]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-0/
[3]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-1/
[4]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-2/
[5]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-3/
[6]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-4/
[7]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-5/
[8]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-6/
[9]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-7/
[10]: http://wdxtub.com/2016/11/19/babel-log-analysis-platform-8/
[11]: https://github.com/floragunncom/search-guard
[12]: https://www.elastic.co/guide/en/elasticsearch/guide/current/heap-sizing.html#compressed_oops