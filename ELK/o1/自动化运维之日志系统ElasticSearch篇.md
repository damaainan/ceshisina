# [自动化运维之日志系统ElasticSearch篇(一)][0]


## 1.没有日志分析系统

### 1.1运维痛点

1.运维要不停的查看各种日志。  
2.故障已经发生了才看日志(时间问题。)  
3.节点多，日志分散，收集日志成了问题。  
4.运行日志，错误等日志等，没有规范目录，收集困难。

### 1.2环境痛点

1.开发人员不能登陆线上服务器查看详细日志。  
2.各个系统都有日志，日志数据分散难以查找。  
3.日志数据量大，查询速度慢，数据不够实时。

### 1.3解决痛点

1.收集（Logstash）  
2.存储（Elasticsearch、Redis、Kafka）  
3.搜索+统计+展示（Kibana)  
4.报警，数据分析（Zabbix）

## 2.ElkStack介绍

对于日志来说，最常见的需求就是收集、存储、查询、展示，开源社区正好有相对应的开源项目：logstash（收集）、elasticsearch（存储+搜索）、kibana（展示），我们将这三个组合起来的技术称之为ELKStack，所以说ELKStack指的是Elasticsearch、Logstash、Kibana技术栈的结合，一个通用的架构如下图所示：

![elk-01][3]

ELK架构图

## 3.ElkStack环境

1.node1和node2为elasticsearch集群(不部署Logstash)  
2.node3收集对象,Nginx、java、tcp、syslog等日志  
3.node4将logstash日志写入Redis,减少程序对elasticsearch依赖性，同时实现程序解耦以及架构扩展。  
4.被收集主机需要部署Logstash。

主机名 | IP | JVM | 内存 | 服务 
-|-|-|-
node1.com | 192.168.90.201 | 32G | 64G | Elasticsearch、Kibana 
node2.com | 192.168.90.202 | 32G | 64G | Elasticsearch、Kibana 
node3.com | 192.168.90.203 | 32G | 64G | Logstash、服务及程序日志 
node4.com | 192.168.90.204 | 32G | 64G | Logstash、Redis(消息队列) 

## 4.ElkStack部署

Elasticsearch、需要Java环境，所以直接使用yum安装。

1.安装java

    [root@linux-node1 ~]# yum install java
    [root@linux-node1 ~]# java -version
    openjdk version “1.8.0_101”
    OpenJDK Runtime Environment (build 1.8.0_101-b13)
    OpenJDK 64-Bit Server VM (build 25.101-b13, mixed mode)

2.下载并安装GPG key

    [root@linux-node1 ~]# rpm –import https://packages.elastic.co/GPG-KEY-elasticsearch

3.添加elasticsearch、logstash、kibana的yum仓库

    # 添加elasticsearch的yum仓库
    [root@linux-node1 ~]# cat /etc/yum.repos.d/elasticsearch.repo
    [elasticsearch-2.x]
    name=Elasticsearch repository for 2.x packages
    baseurl=http://packages.elastic.co/elasticsearch/2.x/centos
    gpgcheck=1
    gpgkey=http://packages.elastic.co/GPG-KEY-elasticsearch
    enabled=1

4.安装ElasticSearch

    [root@linux-node1 ~]# yum install -y elasticsearch
    [root@linux-node1 ~]# yum install -y logstash
    [root@linux-node1 ~]# yum install -y kibana

5.yum安装需要配置limits

    [root@linux-node1 ~]# vim /etc/security/limits.conf
    elasticsearch soft memlock unlimited
    elasticsearch hard memlock unlimited

### 4.1配置Elasticsearch

    [root@linux-node1 ~]# mkdir -p /data/es-data #创建es数据目录
    [root@linux-node1 ~]# chown -R elasticsearch.elasticsearch /data/es-data/ #授权
    [root@linux-node1 /]# grep ‘^[a-z]’ /etc/elasticsearch/elasticsearch.yml
    cluster.name: elk-cluter #集群名称
    node.name: linux-node1 #节点的名称
    path.data: /data/es-data #数据存放路径
    path.logs: /var/log/elasticsearch/ #日志存放日志
    bootstrap.mlockall: true #不使用swap分区,锁住内存
    network.host: 192.168.90.201 #允许访问的IP
    http.port: 9200 #elasticsearch访问端口

### 4.2运行Elasticsearch

1.启动elasticsearch

    [root@linux-node1 ~]# systemctl start elasticsearch

2.访问:elasticsearch_url: “[http://es-mon-1:9200][4]“

    {
    “name” : “linux-node1”,
    “cluster_name” : “elk-cluter”,
    “version” : {
    “number” : “2.3.5”,
    “build_hash” : “90f439ff60a3c0f497f91663701e64ccd01edbb4”,
    “build_timestamp” : “2016-07-27T10:36:52Z”,
    “build_snapshot” : false,
    “lucene_version” : “5.5.0”
    },
    “tagline” : “You Know, for Search”
    }

### 4.3Elasticsearch插件

1.安装Elasticsearch集群管理插件

    [root@linux-node1 ~]# /usr/share/elasticsearch/bin/plugin install mobz/elasticsearch-head

访问head集群插件:http://ES_IP:9200/_plugin/head/

![es-02][5]

es_head插件

![es06][6]

![es07][7]

2.安装Elasticsearch监控插件

    [root@linux-node3 plugins]# /usr/share/elasticsearch/bin/plugin install lmenezes/elasticsearch-kopf

访问kopf监控插件:[http://ES_IP:9200/_plugin/kopf][8]

![es-03][9]

kopf监控插件

### 4.4elasticsearch集群

1.linux-node2配置一个相同的节点，通过组播进行通信，会通过cluster进行查找，如果无法通过组播查询，修改成单播即可

    [root@linux-node2 ~]# grep “^[a-Z]” /etc/elasticsearch/elasticsearch.yml
    cluster.name: elk-cluter
    node.name: linux-node2
    path.data: /data/es-data
    path.logs: /var/log/elasticsearch/
    bootstrap.mlockall: true
    network.host: 0.0.0.0
    http.port: 9200
    discovery.zen.ping.unicast.hosts: [“192.168.90.201″,”192.168.90.202”] #单播（配置一台即可，生产可以使用组播方式）

[0]: http://www.cloudstack.top/archives/107.html
[3]: ../img/elk-01.png
[4]: http://es-mon-1:9200/
[5]: ../img/es-02.jpg
[6]: ../img/es06.png
[7]: ../img/es07.png
[8]: http://es_ip:9200/_plugin/kopf
[9]: ../img/es-03.jpg