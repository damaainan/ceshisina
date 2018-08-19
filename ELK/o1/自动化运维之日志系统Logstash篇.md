# [自动化运维之日志系统Logstash篇(三)][0]



## 5.Logstash日志收集实践

在学习Logstash之前，我们需要先了解以下几个基本概念:  
logstash收集日志基本流程: input–>codec–>filter–>codec–>output  
1.input:从哪里收集日志。  
2.filter:发出去前进行过滤  
3.output:输出至Elasticsearch或Redis消息队列  
4.codec:输出至前台，方便边实践边测试  
5.数据量不大日志按照月来进行收集

    #通常使用rubydebug方式前台输出展示以及测试
    [root@linux-node3 ～]# /opt/logstash/bin/logstash -e ‘input { stdin {} } output { stdout{codec => rubydebug} }’
    hello #输入
    {
    “message” => “hello”,
    “@version” => “1”,
    “@timestamp” => “2016-09-01T08:16:36.354Z”,
    “host” => “linux-node3.com”
    }

也可结合elasticsearch：

    [root@linux-node2 ~]# /opt/logstash/bin/logstash -e ‘input { stdin{} } output { elasticsearch { hosts => [“10.0.0.7:9200”] } }’
    Settings: Default pipeline workers: 2
    Pipeline main started
    2132
    gfdgd
    hhh


![es08][3]

## 6.Logstach实践案例

以下所有收集的日志写入node4的Redis，最后node4通过logstash写入ES,具体架构图如下:  
如果数据量不大需要直接写入elasticsearch，可将案例Redis改为elasticsearch即可。在后面我也会放出实际的案例。

![es-04][4]

es收集架构### 6Logstash安装

Logstash需要Java环境，所以直接使用yum安装。

1.安装java

    [root@linux-node1 ~]# yum install java
    [root@linux-node1 ~]# java -version
    openjdk version “1.8.0_101”
    OpenJDK Runtime Environment (build 1.8.0_101-b13)
    OpenJDK 64-Bit Server VM (build 25.101-b13, mixed mode)

2.下载并安装GPG key

    [root@linux-node1 ~]# rpm –import https://packages.elastic.co/GPG-KEY-elasticsearch

3.添加logstash的yum仓库

    #添加logstash的yum仓库
    [root@linux-node1 ~]# cat /etc/yum.repos.d/logstash.repo
    [logstash-2.3]
    name=Logstash repository for 2.3.x packages
    baseurl=https://packages.elastic.co/logstash/2.3/centos
    gpgcheck=1
    gpgkey=https://packages.elastic.co/GPG-KEY-elasticsearch
    enabled=1

4.安装Logstash

    [root@linux-node1 ~]# yum install -y logstash

声明:如果需要前台查看测试结果，在output加入如下:

    [root@linux-node2 logstash]# cat conf.d/01-logstash.conf
    input { stdin { } }
    output {
    elasticsearch { hosts => [“localhost:9200”] }
    stdout { codec => rubydebug }
    }
    #执行命令：
    /opt/logstash/bin/logstash -f /etc/logstash/conf.d/01-logstash.conf
    #执行完毕，将文件放置/etc/logstash/conf.d目录,logstash会自动读取相关配置文件
    如果无法读取，可将/etc/init.d/logstash里USER和GROUP修改为root

[0]: http://www.cloudstack.top/archives/125.html
[3]: ./img/es08.png
[4]: ./img/es-04.jpg