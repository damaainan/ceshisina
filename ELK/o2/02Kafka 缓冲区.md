## 【通天塔之日志分析平台】贰 Kafka 缓冲区 

发表于 2016-11-19  |    更新于 2017-08-03    |    分类于  [Technique][0]    |     |   1726     5,825  |    22

前一讲我们已经搭建好了 ElasticStack 的核心组件，但是在日常使用中，一般会在 Logstash 和 Elasticsearch 之间加一层 Kafka 用来缓存和控制，这次我们就来看看如何实现这样的功能。

- - -

更新历史

* 2016.11.21: 完成初稿

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

1. 安装并配置好 Kafka
1. 理解 Kafka 的工作机制
1. 掌握 Kafka 的基本操作，学会基本的错误处理
1. 完成 Logstash-Kafka-Elasticsearch 链路的构建，理解这种架构的优劣
1. 通过实际操作，增加对 ElasticStack 的理解

## Kafka 简介

作为云计算大数据的套件，Kafka 是一个分布式的、可分区的、可复制的消息系统。该有的功能基本都有，而且有自己的特色：

* 以 topic 为单位进行消息归纳
* 向 topic 发布消息的是 producer
* 从 topic 获取消息的是 consumer
* 集群方式运行，每个服务叫 broker
* 客户端和服务器通过 TCP 进行通信

在Kafka集群中，没有“中心主节点”的概念，集群中所有的服务器都是对等的，因此，可以在不做任何配置的更改的情况下实现服务器的的添加与删除，同样的消息的生产者和消费者也能够做到随意重启和机器的上下线。

对每个 topic 来说，Kafka 会对其进行分区，每个分区都由一系列有序的、不可变的消息组成，这些消息被连续的追加到分区中。分区中的每个消息都有一个连续的序列号叫做 offset,用来在分区中唯一的标识这个消息。

发布消息通常有两种模式：队列模式(queuing)和发布-订阅模式(publish-subscribe)。队列模式中，consumers 可以同时从服务端读取消息，每个消息只被其中一个 consumer 读到；发布-订阅模式中消息被广播到所有的 consumer 中。更常见的是，每个 topic 都有若干数量的 consumer 组，每个组都是一个逻辑上的『订阅者』，为了容错和更好的稳定性，每个组由若干 consumer 组成。这其实就是一个发布-订阅模式，只不过订阅者是个组而不是单个 consumer。

通过分区的概念，Kafka 可以在多个 consumer 组并发的情况下提供较好的有序性和负载均衡。将每个分区分只分发给一个 consumer 组，这样一个分区就只被这个组的一个 consumer 消费，就可以顺序的消费这个分区的消息。因为有多个分区，依然可以在多个 consumer 组之间进行负载均衡。注意 consumer 组的数量不能多于分区的数量，也就是有多少分区就允许多少并发消费。

Kafka 只能保证一个分区之内消息的有序性，在不同的分区之间是不可以的，这已经可以满足大部分应用的需求。如果需要 topic 中所有消息的有序性，那就只能让这个 topic 只有一个分区，当然也就只有一个 consumer 组消费它。

## 环境配置

我们可以根据自己的需求来进行简单的配置，具体如下：

### (1) 下载 Kafka

    
```
# 美国主机
wget http://www-us.apache.org/dist/kafka/0.10.1.0/kafka_2.11-0.10.1.0.tgz
# 解压
tar -xzf kafka_2.11-0.10.1.0.tgz
# 进入文件夹
cd kafka_2.11-0.10.1.0
```
### (2) 配置 Zookeeper 及 Kafka

Zookeeper 的配置在 config/zookeeper.properties 文件中，Kafka 的配置在 config/server.properties 文件中。

Zookeeper 的配置不需要特别更改，注意默认数据存放的位置是 /zookeeper，这里最好放到挂载磁盘上（如果使用云主机，一般来说系统盘比较小，具体可以用 df -h 查看）。Kafka 的默认数据存放位置是 /tmp/kafka-logs，我们把 zookeeper 和 kafka 的数据存放位置一并进行修改

    
```
# 在 zookeeper.properties 中
/data/home/logger/kafka-data/zookeeper
# 在 server.properties 中
log.dirs=/data/home/logger/kafka-data/kafka-logs
```
其他配置这里推荐进行一些修改，具体如下：

    
```
# advertised.listerners 改为对外服务的地址
# 比如对外的 ip 地址是 xx.xx.xx.xx，端口是 8080，那么
advertised.listeners=PLAINTEXT://xx.xx.xx.xx:8080
# 允许删除 topic
delete.topic.enable=true
# 不允许自动创建 topic，方便管理
auto.create.topics.enable=false
# 设定每个 topic 的分区数量，这里设为 100
num.partitions=100
# 设定日志保留的时间，这里改为 72 小时
log.retention.hours=72
```
### (3) 启动 Zookeeper 及 Kafka

    
```
# 可以使用 tmux 或 nohup & 等方式来进行后台运行，这里使用 tmux
# 启动 Zookeeper
bin/zookeeper-server-start.sh config/zookeeper.properties
# 启动 Kafka
bin/kafka-server-start.sh config/server.properties
```
如果没有出现错误，则启动成功，接下来可以做一个简单的测试

### (4) 内部测试 Kafka

先创建一个叫做 wdxtub 的 topic，它只有一个分区和一个副本，命令如下：

    
```
# 创建 topic
bin/kafka-topics.sh --create --zookeeper localhost:2181 --replication-factor 1 --partitions 1 --topic wdxtub
```
然后我们可以使用 bin/kafka-topics.sh --list --zookeeper localhost:2181 命令来查看目前已有的 topic 列表，这时候应该能看到我们刚才创建的名为 wdxtub 的 topic。如果看到程序返回了 wdxtub，那么表示 topic 创建成功。

接下来我们创建一个简单的 producer，用来从标准输入中读取消息并发送给 Kafka，命令为 

    
```
# 创建一个向 topic wdxtub 发送消息的 producer
# 按回车发送，ctrl+c 退出
bin/kafka-console-producer.sh --broker-list localhost:9092 --topic wdxtub
```
另外新建一个窗口，启动一个 consumer，用来读取消息并输出到标准输出，命令为：

    
```
# 创建一个从 topic wdxtub 读取消息的 consumer

bin/kafka-console-consumer.sh --zookeeper localhost:2181 --topic wdxtub --from-beginning
```
启动成功后，我们在 producer 中输入的内容，就可以在 consumer 中看到：

    
```
# producer 窗口内容
$> ~/kafka_2.11-0.10.1.0$ bin/kafka-console-producer.sh --broker-list localhost:9092 --topic wdxtub
abcdefu
dalkdjflka^H^H^H^H^H^H^H
wdxtub.com
wdxtub.com is good
# consumer 窗口内容
$> ~/kafka_2.11-0.10.1.0$ bin/kafka-console-consumer.sh --zookeeper localhost:2181 --topic wdxtub --from-beginning
Using the ConsoleConsumer with old consumer is deprecated and will be removed in a future major release. Consider using the new consumer by passing [bootstrap-server] instead of [zookeeper].
abcdefu
dalkdjflka
wdxtub.com
wdxtub.com is good
```
### (5) Nginx 配置

因为 Kafka 集群的通讯是走内网 ip，而外网访问的端口因为安全考虑只开了少数几个（这里是 8080），所以我们用 Nginx 反向代理来连通内外网

    
```
upstream mq_pool{
server ip1:9092 weight=1 max_fails=3 fail_timeout=30s;
server localhost:9092 weight=1 max_fails=3 fail_timeout=30s;
}
server{
listen 8080;
allow all;
proxy_pass mq_pool;
proxy_connect_timeout 24h;
proxy_timeout 24h;
}
```
这个配置的意思大概是把所有 8080 端口的消息转发到 mq_pool 的两台机器上（负载均衡），其他的就是常规配置。

### (6) 外部测试 Kafka

现在我们的 Kafka 已经在运行了，但是刚才的测试程序是在本机，所以我们无法保证外部应用也能向 Kafka 发送消息（很多时候会用 Nginx 来控制），这里我们就来编写一段简单的 python 脚本来测试能否从其他服务器连接 Kafka。

这里我们采用的 python 包名为 [dpkp/kafka-python][11]，如果已经有 pip 工具的话，直接 pip install kafka-python 即可。然后我们可以简单编写一个 producer 来进行测试：

    
```
# 名为 kafka-test.py
from kafka import KafkaProducer
# 设置 Kafka 地址
producer = KafkaProducer(
    bootstrap_servers='your.host.name:8080')
# 设置需要发送的 topic 及内容
producer.send('wdxtub', 'Hello World! This is wdxtub.com.')
执行一下 python kafka-test.py，如果能在第(4)步中打开的 consumer 中看到 Hello World 这行字儿，说明能够正确连接。
```
## Kafka 常用操作

所有的工具都可以在 bin/ 文件夹下查看，如果不带任何参数，就会给出所有命令的列表说明，这里只简要说明一些常用的命令

### 管理 topic

可以手动创建 topic，或在数据进来时自动创建不存在的 topic，如果是自动创建的话，可能需要根据[这里][12]来进行对应调整。

**创建 topic**

    bin/kafka-topics.sh --zookeeper zk_host:port/chroot --create --topic my_topic_name --partitions 20 --replication-factor 3 --config x=y

replication-factor 控制复制的份数，建议 2-3 份来兼顾容错和效率。partitions 控制该 topic 将被分区的数目，partitions 的数目最好不要超过服务器的个数（因为分区的意义是增加并行效率，而服务器数量决定了并行的数量，假设只有 2 台服务器，分 4 个区和 2 个区其实差别不大）。另外，topic 的名称不能超过 249 个字符

**修改 topic**

    bin/kafka-topics.sh --zookeeper zk_host:port/chroot --alter --topic my_topic_name --partitions 40

这里需要注意，即使修改了分区的个数，已有的数据也不会进行变动，Kafka 不会做任何自动重分布

**增加配置**

    bin/kafka-topics.sh --zookeeper zk_host:port/chroot --alter --topic my_topic_name --config x=y

**移除配置**

    bin/kafka-topics.sh --zookeeper zk_host:port/chroot --alter --topic my_topic_name --delete-config x

**删除 topic**

    bin/kafka-topics.sh --zookeeper zk_host:port/chroot --delete --topic my_topic_name

这个需要 delete.topic.enable=true，目前 Kafka 不支持减少 topic 的分区数目

### 优雅关闭

Kafka 会自动检测 broker 的状态并根据机器状态选举出新的 leader。但是如果需要进行配置更改停机的时候，我们就需要使用优雅关闭了，好处在于：

1. 会把所有的日志同步到磁盘上，避免重启之后的日志恢复，减少重启时间
1. 会在关闭前把以这台机为 leader 的分区数据迁移到其他节点，会减少不可用的时间

但是这个需要开启 controlled.shutdown.enable=true。

刚重启之后的节点不是任何分区的 leader，所以这时候需要进行重新分配：

bin/kafka-preferred-replica-election.sh --zookeeper zk_host:port/chroot这里需要开启 auto.leader.rebalance.enable=true然后可以使用脚本 bin/kafka-server-stop.sh注意，如果配置文件中没有 auto.leader.rebalance.enable=true，就还需要重新平衡。

## 深入理解

这里只是一部分摘录，更多内容可查阅参考链接（尤其是美团技术博客的那篇）

### 文件系统

Kafka 大量依赖文件系统去存储和缓存消息。而文件系统最终会放在硬盘上，不过不用担心，很多时候硬盘的快慢完全取决于使用它的方式。设计良好的硬盘架构可以和内存一样快。

所以与传统的将数据缓存在内存中然后刷到硬盘的设计不同，Kafka直接将数据写到了文件系统的日志中，因此也避开了 JVM 的劣势——Java 对象占用空间巨大，数据量增大后垃圾回收有困难。使用文件系统，即使系统重启了，也不需要刷新数据，也简化了维护数据一致性的逻辑。

对于主要用于日志处理的消息系统，数据的持久化可以简单的通过将数据追加到文件中实现，读的时候从文件中读就好了。这样做的好处是读和写都是 O(1) 的，并且读操作不会阻塞写操作和其他操作。这样带来的性能优势是很明显的，因为性能和数据的大小没有关系了。

既然可以使用几乎没有容量限制（相对于内存来说）的硬盘空间建立消息系统，就可以在没有性能损失的情况下提供一些一般消息系统不具备的特性。比如，一般的消息系统都是在消息被消费后立即删除，Kafka却可以将消息保存一段时间（比如一星期），这给consumer提供了很好的机动性和灵活性。

### 事务定义

数据传输的事务定义通常有以下三种级别：

* 最多一次: 消息不会被重复发送，最多被传输一次，但也有可能一次不传输。
* 最少一次: 消息不会被漏发送，最少被传输一次，但也有可能被重复传输.
* 精确的一次（Exactly once）: 不会漏传输也不会重复传输,每个消息都传输被一次而且仅仅被传输一次，这是大家所期望的。

Kafka 的机制和 git 有点类似，有一个 commit 的概念，一旦提交且 broker 在工作，那么数据就不会丢失。如果 producer 发布消息时发生了网络错误，但又不确定实在提交之前发生的还是提交之后发生的，这种情况虽然不常见，但是必须考虑进去，现在Kafka版本还没有解决这个问题，将来的版本正在努力尝试解决。

并不是所有的情况都需要“精确的一次”这样高的级别，Kafka 允许 producer 灵活的指定级别。比如 producer 可以指定必须等待消息被提交的通知，或者完全的异步发送消息而不等待任何通知，或者仅仅等待 leader 声明它拿到了消息（followers没有必要）。

现在从 consumer 的方面考虑这个问题，所有的副本都有相同的日志文件和相同的offset，consumer 维护自己消费的消息的 offset。如果 consumer 崩溃了，会有另外一个 consumer 接着消费消息，它需要从一个合适的 offset 继续处理。这种情况下可以有以下选择：

* consumer 可以先读取消息，然后将 offset 写入日志文件中，然后再处理消息。这存在一种可能就是在存储 offset 后还没处理消息就 crash 了，新的 consumer 继续从这个 offset 处理，那么就会有些消息永远不会被处理，这就是上面说的『最多一次』
* consumer 可以先读取消息，处理消息，最后记录o ffset，当然如果在记录 offset 之前就 crash 了，新的 consumer 会重复的消费一些消息，这就是上面说的『最少一次』
* 『精确一次』可以通过将提交分为两个阶段来解决：保存了 offset 后提交一次，消息处理成功之后再提交一次。但是还有个更简单的做法：将消息的 offset 和消息被处理后的结果保存在一起。比如用 Hadoop ETL 处理消息时，将处理后的结果和 offset 同时保存在 HDFS 中，这样就能保证消息和 offser 同时被处理了

### 性能优化

Kafka 在提高效率方面做了很大努力。Kafka 的一个主要使用场景是处理网站活动日志，吞吐量是非常大的，每个页面都会产生好多次写操作。读方面，假设每个消息只被消费一次，读的量的也是很大的，Kafka 也尽量使读的操作更轻量化。

线性读写的情况下影响磁盘性能问题大约有两个方面：太多的琐碎的 I/O 操作和太多的字节拷贝。I/O 问题发生在客户端和服务端之间，也发生在服务端内部的持久化的操作中。

**消息集(message set)**

为了避免这些问题，Kafka 建立了**消息集(message set)**的概念，将消息组织到一起，作为处理的单位。以消息集为单位处理消息，比以单个的消息为单位处理，会提升不少性能。Producer 把消息集一块发送给服务端，而不是一条条的发送；服务端把消息集一次性的追加到日志文件中，这样减少了琐碎的 I/O 操作。consumer 也可以一次性的请求一个消息集。

另外一个性能优化是在字节拷贝方面。在低负载的情况下这不是问题，但是在高负载的情况下它的影响还是很大的。为了避免这个问题，Kafka 使用了标准的二进制消息格式，这个格式可以在 producer, broker 和 producer 之间共享而无需做任何改动。

**zero copy**

Broker 维护的消息日志仅仅是一些目录文件，消息集以固定队的格式写入到日志文件中，这个格式 producer 和 consumer 是共享的，这使得 Kafka 可以一个很重要的点进行优化：消息在网络上的传递。现代的 unix 操作系统提供了高性能的将数据从页面缓存发送到 socket 的系统函数，在 linux 中，这个函数是 sendfile为了更好的理解 sendfile 的好处，我们先来看下一般将数据从文件发送到 socket 的数据流向：

* 操作系统把数据从文件拷贝内核中的页缓存中
* 应用程序从页缓存从把数据拷贝自己的内存缓存中
* 应用程序将数据写入到内核中 socket 缓存中
* 操作系统把数据从 socket 缓存中拷贝到网卡接口缓存，从这里发送到网络上。

这显然是低效率的，有 4 次拷贝和 2 次系统调用。sendfile 通过直接将数据从页面缓存发送网卡接口缓存，避免了重复拷贝，大大的优化了性能。

在一个多consumers的场景里，数据仅仅被拷贝到页面缓存一次而不是每次消费消息的时候都重复的进行拷贝。这使得消息以近乎网络带宽的速率发送出去。这样在磁盘层面你几乎看不到任何的读操作，因为数据都是从页面缓存中直接发送到网络上去了。

**数据压缩**

很多时候，性能的瓶颈并非CPU或者硬盘而是网络带宽，对于需要在数据中心之间传送大量数据的应用更是如此。当然用户可以在没有 Kafka 支持的情况下各自压缩自己的消息，但是这将导致较低的压缩率，因为相比于将消息单独压缩，将大量文件压缩在一起才能起到最好的压缩效果。

Kafka 采用了端到端的压缩：因为有『消息集』的概念，客户端的消息可以一起被压缩后送到服务端，并以压缩后的格式写入日志文件，以压缩的格式发送到 consumer，消息从 producer 发出到 consumer 拿到都被是压缩的，只有在 consumer 使用的时候才被解压缩，所以叫做『端到端的压缩』。Kafka支持GZIP和Snappy压缩协议。

## 实例：把系统日志通过 Kafka 接入 Elasticsearch

现在我们就把上一讲搭建好的架构中加入 Kakfa 作为缓冲区，具体分两步

### (1) Logstash -> Kafka

让我们回想一下之前的架构，Logstash 会直接把日志发送给 Elasticsearch，再由 Kibana 进行展示。因为 Logstash 是同步把日志发送给 Elasticsearch 的，所以等于这俩耦合在了一起，Elasticsearch 一旦挂掉，可能就会丢失数据。

于是，我们考虑利用 Kafka 作为缓冲区，让 Logstash 不受 Elasticsearch 的影响。所以第一步就是让 Logstash 把日志发送到 Kafka，这里 Logstash 相当于 producer。

不过在开始之前，我们先启动 Kafka

    
```
# 启动 Zookeeper
bin/zookeeper-server-start.sh config/zookeeper.properties
# 启动 Kafka
bin/kafka-server-start.sh config/server.properties
```
我们之前的 Logstash 配置文件是把日志直接发送到 Elasticsearch 的，这里我们需要更新为发送到 Kafka

    
```
# 我的习惯是把配置文件统一放到名为 confs 的文件夹中
# 本配置文件名为 log-to-kafka.conf
input {
  file {
    # 确定需要检测的文件
    path => [ "/var/log/*.log", "/var/log/messages", "/var/log/syslog", "/var/log/apt", "/var/log/fsck", "/var/log/faillog"]
    # 日志类型
    type => "syslog"
    add_field => { "service" => "system-log"}
    # stat_interval => 1800
  }
}
output {
  # 输出到命令行，一般用于调试
  stdout { 
    codec => rubydebug 
  }
  # 输出到 Kafka，topic 名称为 logs，地址为默认的端口号
  kafka {
    topic_id => "logs"
    bootstrap_servers => "localhost:9092"
  }
}
```
file 插件其他一些配置设定原因

* add_field 添加一个 topic 字段，用作之后导入 elasticsearch 的索引标识
* stat_interval 单位是秒，这里 30 分钟进行一次检测，不过测试的时候需要去掉这个配置

kafka 插件其他一些需要注意的配置

* acks 可以选的值为 0, 1, all，这里解释一下，0 表示不需要 server 返回就认为请求已完成；1 表示需要 leader 返回才认为请求完成；all 表示需要所有的服务器返回才认为请求完成
* batch_size 单位是字节，如果是发送到同一分区，会攒够这个大小才发送一次请求
* block_on_buffer_full 这个设置在缓冲区慢了之后阻塞还是直接报错
* buffer_memory 发送给服务器之前的缓冲区大小，单位是字节
* client_id 可以在这里设定有意义的名字，就不一定要用 ip 和 端口来区分
* compression_type 压缩方式，默认是 none，其他可选的是 gzip 和 snappy

### (2) Kafka -> Elasticsearch

利用 Logstash 从 Kafka 导出数据到 Elasticsearch。这一步就比较简单了，先从 Kafka 中读取，然后写入到 elasticsearch，这里 Logstash 作为 consumer。唯一需要注意的地方是要保证 topic 名称一致

    
```
# 文件名 kafka-to-es.conf
input {
  kafka {
    bootstrap_servers => "localhost:9092"
    topics => ["logs"]
  }
}
output {
  # for debugging
  stdout {
     codec => rubydebug
  }
  elasticsearch { 
    hosts => "localhost:9200"
    index => "system-log"
  }
}
```
至此，我们完成了从 Logstash 到 Kafka 再到 Elasticsearch 的连接，下一步就可以用 kibana 来展示日志的监控分析结果了。

![][13]

如上图所示，打开 Kibana，即可见到我们使用 Logstash 通过 Kafka 再发送到 Elasticsearch 的日志。至此，我们就成功把 Kafka 加入到日志分析平台的架构中了。

## 试一试

1. 查阅 Logstash 的 Kafka 插件的文档，了解其他的配置选项
1. Logstash 能够处理 json 格式的日志，试着把系统日志转换成 json，并进行处理
1. 更新 Logstash 配置，看看能不能多记录一些系统事件
1. 随着日志的增多，使用 Kibana 多创建一些图表并添加到 Dashboard 中

一个可能的例子如下：

![][14]

## 总结

这一讲我们主要学习了 Kafka 的相关内容，并在了解原理的基础上更新了日志分析平台的架构，这样我们的日志在发送到 Elasticsearch 之前。下一讲我们会在单机的状态下完成监控、安全、报警与通知的功能。

## 参考链接

* [Kafka 快速入门][15]
* [Kafka 2.11-0.10.1.0 下载][16]
* [Kafka学习整理六(server.properties配置实践)][17]
* [Apache Kafka][18]
* [Quick Start][19]
* [Kafka入门经典教程][20]
* [Apache kafka 工作原理介绍][21]
* [事无巨细 Apache Kafka 0.9.0.1 集群环境搭建][22]
* [kafka集群搭建][23]
* [Kafka文件存储机制那些事][24]
* [kafka原理以及设计实现思想][25]
* [kafka设计原理介绍][26]
* [Kafka集群操作指南][27]
* [What is the actual role of ZooKeeper in Kafka?][28]

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
[11]: https://github.com/dpkp/kafka-python
[12]: http://kafka.apache.org/documentation.html#topic-config
[13]: ../img/14797818253620.jpg
[14]: ../img/14797829019725.jpg
[15]: http://kafka.apache.org/quickstart
[16]: https://www.apache.org/dyn/closer.cgi?path=/kafka/0.10.1.0/kafka_2.11-0.10.1.0.tgz
[17]: http://blog.csdn.net/LOUISLIAOXH/article/details/51567515
[18]: http://kafka.apache.org/
[19]: http://kafka.apache.org/documentation.html#quickstart
[20]: http://www.aboutyun.com/thread-12882-1-1.html
[21]: https://www.ibm.com/developerworks/cn/opensource/os-cn-kafka/
[22]: http://www.coderli.com/setup-kafka-cluster-step-by-step/
[23]: http://blog.csdn.net/dhtx_wzgl/article/details/46892231
[24]: http://tech.meituan.com/kafka-fs-design-theory.html
[25]: http://kaimingwan.com/post/kafka/kafkayuan-li-yi-ji-she-ji-shi-xian-si-xiang
[26]: http://www.dexcoder.com/dexcoder/article/2194
[27]: http://blog.jobbole.com/99195/
[28]: https://www.quora.com/What-is-the-actual-role-of-ZooKeeper-in-Kafka