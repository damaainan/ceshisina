# [自动化运维之日志系统ES+Kibana展示(二)][0]


## 8.Kibana实践

Kibana 是为 Elasticsearch 设计的开源分析和可视化平台。你可以使用 Kibana 来搜索，查看存储在 Elasticsearch 索引中的数据并与之交互。你可以很容易实现高级的数据分析和可视化，以图表的形式展现出来。

### 8.1安装Kibana

1.安装java

    [root@linux-node1 ~]# yum install java
    [root@linux-node1 ~]# java -version
    openjdk version “1.8.0_101”
    OpenJDK Runtime Environment (build 1.8.0_101-b13)
    OpenJDK 64-Bit Server VM (build 25.101-b13, mixed mode)

2.下载并安装GPG key

    [root@linux-node1 ~]# rpm –import https://packages.elastic.co/GPG-KEY-elasticsearch

3.添加kibana的yum仓库

    # 添加kibana的yum仓库
    [root@linux-node1 ~]# vim /etc/yum.repos.d/kibana.repo
    [kibana-4.5]
    name=Kibana repository for 4.5.x packages
    baseurl=http://packages.elastic.co/kibana/4.5/centos
    gpgcheck=1
    gpgkey=http://packages.elastic.co/GPG-KEY-elasticsearch
    enabled=1

4.安装Kibana

    [root@linux-node1 ~]# yum install -y kibana

### 8.2配置Kibana

    [root@linux-node1 ～]# grep “^[a-Z]” /opt/kibana/config/kibana.yml
    server.port: 5601 #访问端口
    server.host: “0.0.0.0” #允许访问主机(建议内网)
    elasticsearch.url: “http://192.168.90.201:9200” #es地址
    kibana.index: “.kibana”

### 8.3启动Kibana

    [root@linux-node1 ~]# systemctl start kibana

### 8.4操作Kibana

1.创建Kibana索引

![k1][3]

创建kibana

![k2][4]

查看Kibana  
![k3][5]

使用Kibana

![k4][6]

Kibana图形  
![k5][7]

kibana展示Kibana数据从Elasticsearch存储里面获取,那么就需要Logstash往Elasticsearch写入才可以。  
运维最重要的如何收集日志,那么往下开启Logstash学习之旅

[0]: http://www.cloudstack.top/archives/117.html
[3]: ../img/k1.jpg
[4]: ../img/k2.jpg
[5]: ../img/k3.jpg
[6]: ../img/k4.jpg
[7]: ../img/k5.jpg