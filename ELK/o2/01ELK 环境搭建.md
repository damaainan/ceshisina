## 【通天塔之日志分析平台】壹 ELK 环境搭建 

发表于 2016-11-19  |    更新于 2017-08-03    

前一讲我们对 ElasticStack 进行了简要介绍并完成了基本的系统环境配置，这一次我们要把 Elasticsearch/Logstash/Kibana 安装配置好，并把 Linux 的系统日志导入进来。

- - -

更新历史

* 2016.11.21: 完成初稿

#### 系列文章

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

#### 任务目标

1. 掌握并完成 Elasticsearch, Logstash 和 Kibana 的安装配置
1. 了解 Linux 系统日志的内容及保存位置，并利用 Logstash 导入到 Elasticsearch 中，最终由 Kibana 展示
1. 掌握 Linux 的进程控制机制，学会如何启动和关闭前台/后台应用

#### 安装与启动

无论是 Elasticsearch, Logstash 还是 Kibana，我们都推荐手动安装的方式，毕竟不涉及太多配置操作，用 `apt-get` 反而有些用牛刀杀鸡了。这里我们直接上命令

    

### 进入用户文件夹
```
cd ~
# 下载 Elasticsearch
wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-5.0.1.tar.gz
# 下载 Logstash
wget https://artifacts.elastic.co/downloads/logstash/logstash-5.0.1.tar.gz
# 下载 Kibana
wget https://artifacts.elastic.co/downloads/kibana/kibana-5.0.1-linux-x86_64.tar.gz
# 解压 
tar -xvf elasticsearch-5.0.1.tar.gz
tar -xvf logstash-5.0.1.tar.gz
tar -xvf kibana-5.0.1-linux-x86_64.tar.gz
# 把安装包保存到固定文件夹中，这里叫 software
mv elasticsearch-5.0.1.tar.gz software/
    mv kibana-5.0.1-linux-x86_64.tar.gz software/
    mv logstash-5.0.1.tar.gz software/
```
解压完成之后，ElasticStack 运行前的准备就基本完成了。Logstash 可以在需要时再启用，这里我们先把 Elasticsearch 和 Kibana 给启动起来（这里我们继续用 tmux，关于 tmux 的使用可以参考我写的[tmux 指南][11]）

    
```
# 新建 tmux session

tmux

# 启动 Elasticsearch

# 这里注意，最好虚拟机有 4G 内存，不然很容易卡死

cd elasticsearch-5.0.1/bin; ./elasticsearch

# 启动 Kibana

cd kibana-5.0.1-linux-x86_64/; ./bin/kibana
```
打开浏览器，访问 `localhost:5601`，如果看到如下所示的页面，Elasticsearch 和 Kibana 基本就没问题了。

![][12]

然后我们体验一下 Logstash，输入下列命令：

    
```
# 进入文件夹

cd logstash-5.0.1/

# 启动 logstash，输入和输出均为命令行

bin/logstash -e 'input { stdin { } } output { stdout {} }'
```
然后我们随意输入一些内容，显示为：

    
```
parallels@ubuntu:~/logstash-5.0.1$ bin/logstash -e 'input { stdin { } } output { stdout {} }'
    wdxtub.com updated
    Sending Logstash's logs to /home/parallels/logstash-5.0.1/logs which is now configured via log4j2.properties
    The stdin plugin is now waiting for input:[2016-11-20T22:54:20,014][INFO ][logstash.pipeline        ] Starting pipeline {"id"=>"main", "pipeline.workers"=>2, "pipeline.batch.size"=>125, "pipeline.batch.delay"=>5, "pipeline.max_inflight"=>250}
    
    [2016-11-20T22:54:20,038][INFO ][logstash.pipeline        ] Pipeline main started
    [2016-11-20T22:54:20,088][INFO ][logstash.agent           ] Successfully started Logstash API endpoint {:port=>9600}
    2016-11-21T06:54:20.036Z ubuntu wdxtub.com updated
    wdxtub.com is a personal blog
    2016-11-21T06:54:41.187Z ubuntu wdxtub.com is a personal blog
    wdxtub.com is created in 2015
    2016-11-21T06:54:55.190Z ubuntu wdxtub.com is created in 2015
```
至此，ElasticStack 三大组件都已经运行了一次，我们可以用一个实际的任务来上手了。不过开始之前，我们来简单了解一下 ElasticStack 的发展历程。

#### ElasticStack 5.0 的变化

ElasticStack 之所以版本一开始就是 5.0，主要原因是把各个产品进行版本统一。5.0 之前，Elasticsearch 的版本是 2.4，Logstash 的版本也是 2.4，但是 Kibana 是 4.5。这样一来开发者其实很难把各个组件对应起来，于是 Elastic 公司干脆直接统一到 5.0，皆大欢喜。

考虑到不是所有的朋友都有接触过 2.4 及之前版本的 ElasticStack，所以这部分会简明扼要介绍一下 5.0 版本的重大改变：

* Elasticsearch 的底层引擎是 Lucene，5.0 版本中集成了 Lucene6， 新增的多维浮点字段特性极大提高了对 date, numeric, ip 等类型字段的操作的性能。更直观一点说：磁盘空间少一半；索引时间少一半；查询性能提升25%（底层采用 k-d 树编码，更多信息可以在 [Lucene 官网][13]中查阅）
* Instant Aggregations 特性在 Shard 层级提供了聚合结果的缓存，如果数据没有变化，Elasticsearch 可以直接返回上次的结果
* Scliced Scroll 操作允许并发进行数据遍历，大大提升索引重建和遍历的速度
* Profile API 可以帮助进行查询的优化，通过确定每个组件的性能消耗来进行优化（设置 profile:true 即可）
* Shrink API 可以对分片(Shard)数量进行收缩（从前是不能更改的），利用这个特性，我们可以在写入压力非常大的收集阶段，设置足够多的索引，充分利用shard的并行写能力，索引写完之后收缩成更少的shard，提高查询性能（利用系统的 Hardlink 来进行链接，速度很快）
* Rollover API 可以帮助我们按日切割日志，只需要简单的配置即可更加方便灵活分割日志，不用原来 [YYYY-MM-DD] 这样的模板了
* Wait for Refresh 提供了文档级别的刷新
* Ingest Node 可以直接在建立索引的时候对数据进行加工，这个功能还是很强大的
* Task Manager 任务调度管理，来做离线任务

总而言之，5.0 是目前最好的一个 ElasticStack 版本，增加了很多新功能，非常值得试一试，更加详细的变动可以查看如下链接，这里不再赘述

* [Elasticsearch 5.0 Breaking Changes][14]
* [Kibana 5.0 Breaking Changes][15]
* [Logstash 5.0 Breaking Changes][16]

接下来我们先简单了解一下 Elasticsearch 的基本概念，然后就可以上手来完成一个小小的实例了。

#### Elasticsearch 快速入门

##### 基本概念

和 Mongodb 的思路类似，Elasticsearch 中保存的是整个文档(document)，并且还会根据文档的内容进行索引，于是我们得以进行搜索、排序和过滤等操作。在 Elasticsearch 中，利用 JSON 来表示文档。举个例子，下面的 JSON 文档就表示一个用户对象：

    
```
{
    "email": "dawang@wdxtub.com",
    "name": "Da Wang",
    "info": {
        "bio": "Sharp Blade, Shape Mind",
        "age": "25",
        "interests": ["games", "music"]
    },
    "birthday": "1990/09/11"
}
```
在 Elasticsearch 中存储数据的行为就叫做索引(indexing)，而前面提到的文档，属于一种类型(type)，这里类型会存在索引(index)中，如果列一个表来和传统数据库比较，大概是这样的：

关系型数据 Elasticsearch Databases Indices Tables Types Rows Documents Columns Fields 

一个 Elasticsearch 集群可以包含多个索引(indices，对应于『数据库』)，每个索引可以包含多个类型(types，对应于『表』)，每个类型可以包含多个文档(document，对应于『行』)，每个文档可以包含多个字段(fields，对应于『列』)

这里有一点需要强调一下，前面出现了两种『索引』，第一种，索引(indexing，动词，对应于关系型数据库的插入 insert)指的是把一个文档存储到索引(index，名词) 中；第二种的索引(index，名词）对应于关系型数据库的数据库，这里一定要根据上下文来进行理解。一般来说，我们会对数据库增加索引（这里是第三种意思，就是传统的索引的定义）来提高检索效率，Elasticsearch 和 Lucene 使用『倒排索引』的数据结构来完成这个工作（传统数据库一般用红黑树或者 B 树来完成）。默认情况下，文档中的每个字段都会拥有其对应的倒排索引，Elasticsearch 也是通过这个来进行检索的。

##### Hello World

我们用一个简单的例子来感受一下 Elasticsearch 的威力吧。设定一个场景，有一天我开了一家名为 “ohmywdx” 的公司，我需要为每个公司里的员工创建记录，我需要做的是：

* 为每个员工的文档(document)建立索引，每个文档包含一个员工的各类信息，类型为 wdxtuber
* wdxtuber 类型属于索引 ohmywdx（这里的索引对应于数据库）
* ohmywdx 索引存储在 Elasticsearch 集群中

我们先来插入几条员工记录

    
```
PUT /ohmywdx/wdxtuber/1
{
    "name": "Da Wang",
    "age": 25,
    "about": "First one who is stupid enough to join this company",
    "interests": ["game", "music"]
}
PUT /ohmywdx/wdxtuber/2
{
    "name": "Tracy Bryant",
    "age": 20,
    "about": "First basketball robot for our company",
    "interests": ["guard", "forward"]
}
PUT /ohmywdx/wdxtuber/3
{
    "name": "Shadow Mouse",
    "age": 50,
    "about": "Secret agent for our company",
    "interests": ["guitar", "sugar"]
}
```
具体怎么插入呢，我们可以使用 Kibana 5.0 自带的 Dev Tools 来折腾，把上面的命令粘贴到左边的输入框，然后点击绿色的运行按钮，就可以在右边看到结果了：

![][17]

按照这个套路，继续把其他两个人的资料插入 Elasticsearch。

有了数据之后，我们来看看如何搜索，简单来说，按照存储的方式来检索即可，不过这里我们使用 GET 方法，如下图所示：

![][18]

我们可以看到，原始文档内容包含在 _source 字段中。如果说这个搜索太明确了，啥都指定了没意思，我们可以来试试看下面几条搜索

* GET /ohmywdx/wdxtuber/_search
* GET /ohmywdx/wdxtuber/_search?q=name:Da

这里我们来看看第二个搜索的结果

    
```
{
      "took": 11,
      "timed_out": false,
      "_shards": {
        "total": 5,
        "successful": 5,
        "failed": 0
      },
      "hits": {
        "total": 1,
        "max_score": 0.25811607,
        "hits": [
          {
            "_index": "ohmywdx",
            "_type": "wdxtuber",
            "_id": "1",
            "_score": 0.25811607,
            "_source": {
              "name": "Da Wang",
              "age": 25,
              "about": "First one who is stupid enough to join this company",
              "interests": [
                "game",
                "music"
              ]
            }
          }
        ]
      }
    }
```
除了 _source 的信息之外，我们可以看到有一个 _score，敏感的同学大概会意识到，Elasticsearch 是根据相关性来对结果进行排序的，这个得分就是相关性分数。

除了前面说明的搜索，我们还可以使用 DSL 语句来组合更加复杂的搜索，什么过滤、组合条件、全文、短语搜索，都不在话下，我们甚至可以高亮搜索结果。另外我们还可以利用『聚合』来实现关系型数据库中『Group By』类似的操作。其他诸如推荐、定位、渗透、模糊及部分匹配同样也支持。不过这一篇仅仅是一个简要介绍，就不继续深入了。

#### 实例：收集 Linux 系统日志

万事俱备，只欠东风，我们现在就把 ElasticStack 用起来！第一个任务很简单，就是把本机的日志给监控起来，这样我们在查询系统发生的事件时，就不用再去 /var/log/ 文件夹里『翻箱倒柜』了。

##### 系统日志介绍

这里简要介绍一下比较通用的系统日志及对应的内容，之后导入日志的时候我会挑选：

* /var/log/apport.log 应用程序崩溃记录
* /var/log/apt/ 用 apt-get 安装卸载软件的信息
* /var/log/auth.log 登录认证的日志
* /var/log/boot.log 系统启动时的日志。
* /var/log/dmesg 包含内核缓冲信息(kernel ringbuffer)。在系统启动时，显示屏幕上的与硬件有关的信息
* /var/log/faillog 包含用户登录失败信息。此外，错误登录命令也会记录在本文件中
* /var/log/fsck 文件系统日志
* /var/log/kern.log 包含内核产生的日志，有助于在定制内核时解决问题
* /var/log/wtmp 包含登录信息。使用 wtmp 可以找出谁正在登陆进入系统，谁使用命令显示这个文件或信息等

##### 启动 Logstash

这一步的任务是利用 Logstash 把系统日志给导入 Elasticsearch 中。暂时没有使用最新的 Beats 组件，而是继续使用传统的 Logstash 来进行操作（比较重型），用法和之前有些不同，我们会把配置写在一个文件中，而不是直接在命令中输入。

Logstash 使用一个名叫 FileWatch 的 Ruby Gem 库来监听文件变化。这个库支持 glob 展开文件路径，而且会记录一个叫 .sincedb 的数据库文件来跟踪被监听的日志文件的当前读取位置。通过记录下来的 inode, major number, minor number 和 pos 就可以保证不漏过每一条日志。

具体配置如下：

    
```
### 我的习惯是把配置文件统一放到名为 confs 的文件夹中
### 本配置文件名为 syslog.conf
input {
  file {
    # 确定需要检测的文件
    path => [ "/var/log/*.log", "/var/log/messages", "/var/log/syslog", "/var/log/apt", "/var/log/fsck", "/var/log/faillog"]
    # 日志类型
    type => "syslog"
  }
}
output {
  # 输出到命令行，一般用于调试
  stdout { 
    codec => rubydebug 
  }
  # 输出到 elasticsearch，这里指定索引名为 system-log
  elasticsearch { 
    hosts => "localhost:9200"
    index => "system-log" 
  }
}
```
这里说一下 File rotation 的情况，为了处理被 rotate 的情况，最好把 rotate 之后的文件名也加到 path 中（如上面所示），这里注意，如果 start_position 被设为 beginning，被 rotate 的文件因为会被认为是新文件，而重新导入。如果用默认值 end，那么在最后一次读之后到被 rotate 结束前生成的日志不会被采集。

有了配置文件，我们就可以把日志导入到 Elasticsearch 了，命令如下：

    

### -f 表示从文件中读取配置

bin/logstash -f confs/syslog.conf

大概的输出是如下：

![][19]

如果到这里一切正常，我们就可以去 Kibana 中查看导入的日志了。

##### 使用 Kibana

Kibana 相当于是 Elasticsearch 的一个可视化插件，所以我们需要在 Management 页面中告诉 Kibana 我们刚才创建的 system-log 索引，完成之后可以看到具体的条目及对应的信息（包括是否可被检索，是否能聚合，是否被分词等）

![][20]

创建完成后我们就可以在 Discover 面板里查看数据了。

![][21]

分别介绍下每个面板的作用（更加详细的介绍参见 [柒 Kibana 技巧指南][9]）

* Discover: 探索数据
* Visualize: 可视化统计
* Dashboard: 仪表盘
* Timelion: 时序，这里我们暂时不用
* Management: 设置
* Dev Tools: 开发工具，可以方便的测试内置接口

这里我简单给出两个实例，介绍一下 Visualize 和 Dashboard 的基本用法

如下图配置，我们可以轻松查看日志都是从哪些文件导入的，除了饼图外，柱状图折线图之类的都是支持的，大家可以自行尝试一下

![][22]

每个 Visualization 都可以保存，保存之后可以在 Dashboard 面板里集中显示，这样我们只需要看一眼，就对机器运行的状况有一个清晰的了解。

![][23]

至此，我们就完成了收集系统日志并展示的任务，本章内容到此基本结束。

#### 试一试

1. 搭建完成之后，看看自己的系统中最多出现的 log 是什么？
1. 在 Kibana 中 Dev Tools 的使用 Elasticsearch 的 HTTP 接口来进行简单的查询
1. 尝试 logstash 自己感兴趣的插件，看看能不能为系统日志添加更多字段
1. 创建几个不同的 Visualization 并添加到 Dashboard 中，让内容更丰富一些

#### 总结

本节中我们完成了 ElasticStack 核心的 ELK 安装，并用一个简单的实例熟悉了相关操作。刚开始接触，一定是会有很多陌生的概念，建议大家去浏览一下官方的快速入门文档，写得还是比较清晰的。下一讲我们会介绍整个日志处理流程中很重要的『缓冲区』- Kafka，有了它，我们的日志分析平台就有了基本的雏形了。

#### 参考链接

* [Elastic Stack and Product Documentation][24]
* [大数据杂谈微课堂|Elasticsearch 5.0新版本的特性与改进][25]
* [开源搜索引擎Elasticsearch 5.0版本正式发布][26]


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
[11]: http://wdxtub.com/2016/03/30/tmux-guide/
[12]: ../img/14797108735042.jpg
[13]: http://lucene.apache.org/
[14]: https://www.elastic.co/guide/en/elasticsearch/reference/current/breaking-changes.html
[15]: https://www.elastic.co/guide/en/kibana/current/breaking-changes.html
[16]: https://www.elastic.co/guide/en/logstash/current/breaking-changes.html
[17]: ../img/14797196056865.jpg
[18]: ../img/14797197975730.jpg
[19]: ../img/14797212868418.jpg
[20]: ../img/14797126705730.jpg
[21]: ../img/14797126971993.jpg
[22]: ../img/14797163664177.jpg
[23]: ../img/14797164257675.jpg
[24]: https://www.elastic.co/guide/index.html
[25]: http://www.infoq.com/cn/news/2016/08/Elasticsearch-5-0-Elastic
[26]: http://www.infoq.com/cn/news/2016/11/Elasticsearch-5-0-publish