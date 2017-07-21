# 反向代理负载均衡系列之Apache

 时间 2017-01-01 11:22:29  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/772.html][1]


## 1.反向代理概述

反向代理（Reverse Proxy）方式是指以代理服务器来接受internet上的连接请求，然后将请求转发给内部网络上的服务器，并将从服务器上得到的结果返回给internet上请求连接的客户端，此时代理服务器对外就表现为一个反向代理服务器。

环境准备:

主机名 | IP地址 | 角色 | 系统 
-|-|-|-
web-node1.com | eth0:192.168.90.201 | web-node1节点 | CentOS7.2 
web-node2.com | eth0:192.168.90.202 | web-node2节点 | CentOS7.2 
lb-node1.com  | eth0:192.168.90.203 | Apache反向代理 | CentOS7.2 

## 2.Node节点部署

在两台web-node节点中均使用Yum安装一个Apache用于做真实机，监听8080端口

web-node1.com部署

    [root@web-node1~]#rpm-ivh \
    http://mirrors.aliyun.com/epel/epel-release-latest-7.noarch.rpm
    [root@web-node1~]#yum install-y gcc glibc gcc-c++make screen tree lrzsz
    
    ##部署web-node1 httpd服务
    [root@web-node1~]#yum install-y httpd
    [root@web-node1~]#sed-i's/Listen 80/Listen 8080/g' /etc/httpd/conf/httpd.conf
    [root@web-node1~]#systemctl start httpd
    [root@web-node1~]#echo"web-node1.com" > /var/www/html/index.html
    [root@web-node1~]#curl http://192.168.90.201:8080/
    web-node1.com
    

web-node2.com部署

    [root@web-node1~]#rpm-ivh \
    http://mirrors.aliyun.com/epel/epel-release-latest-7.noarch.rpm
    [root@web-node1~]#yum install-y gcc glibc gcc-c++make screen tree lrzsz
    
    ##部署web-node2 httpd服务
    [root@web-node2~]#yum install-y httpd
    [root@web-node2~]#sed-i's/Listen 80/Listen 8080/g' /etc/httpd/conf/httpd.conf
    [root@web-node2~]#systemctl start httpd
    [root@web-node2~]#echo"web-node2.com" > /var/www/html/index.html
    [root@web-node2~]#curl http://192.168.90.202:8080/
    web-node2.com
    

## 3.反向代理部署

Apache 源码编译安装，并监听80端口

    [root@lb-node1~]#yum install-y apr-devel apr-util-devel pcre-devel openssl-devel
    [root@lb-node1~]#cd/usr/local/src
    [root@lb-node1 src]#wget http://www-eu.apache.org/dist/httpd/httpd-2.4.23.tar.gz
    [root@lb-node1 src]#tar xf httpd-2.4.23.tar.gz
    [root@lb-node1 src]#cd httpd-2.4.23
    [root@lb-node1 httpd-2.4.23]# ./configure--prefix=/usr/local/httpd-2.4.23 --enable-so--enable-modules="all"
    [root@lb-node1 httpd-2.4.23]#make&&make install
    [root@lb-node1 httpd-2.4.23]#ln-s/usr/local/httpd-2.4.23/ /usr/local/httpd
    
    ## 测试配置并启动Apache
    [root@lb-node1~]#sed-i's@#ServerName www.example.com:80@ServerName 192.168.90.203:80@g' /usr/local/httpd/conf/httpd.conf
    [root@lb-node1~]# /usr/local/httpd/bin/apachectl-t
    SyntaxOK
    [root@lb-node1~]# /usr/local/httpd/bin/apachectl-k start
    

### 3.1Apache配置反向代理

1.在 /usr/local/httpd/conf/httpd.conf 配置引用proxy配置文件 

    Include conf/extra/httpd-proxy.conf
    

2.配置proxy反向代理

    [root@linux-node1~]#cat/usr/local/httpd/conf/extra/httpd-proxy.conf
    LoadModuleproxy_module modules/mod_proxy.so
    LoadModuleproxy_connect_module modules/mod_proxy_connect.so
    LoadModuleproxy_http_module modules/mod_proxy_http.so
    LoadModuleproxy_balancer_module modules/mod_proxy_balancer.so
    LoadModulelbmethod_byrequests_module modules/mod_lbmethod_byrequests.so
    LoadModulelbmethod_bytraffic_module modules/mod_lbmethod_bytraffic.so
    LoadModulelbmethod_bybusyness_module modules/mod_lbmethod_bybusyness.so
    LoadModuleslotmem_shm_module modules/mod_slotmem_shm.so
    
    ProxyRequests Off
    <Proxybalancer://web-cluster>
    BalancerMemberhttp://192.168.90.201:8080 loadfactor=1
    BalancerMemberhttp://192.168.90.202:8080 loadfactor=2
    </Proxy>
    ProxyPass /biaoganxu balancer://web-cluster
    ProxyPassReverse /biaoganxu balancer://web-cluster
    
    <Location /manager>
     SetHandlerbalancer-manager
     Order Deny,Allow
     Allow fromall
    </Location>
    

3.重载Apache服务

    [root@lb-node1~]# /usr/local/httpd/bin/apachectl-k graceful
    

4.测试反向代理

    [root@lb-node1~]#curl http://192.168.90.203/biaogan/
    web-node1.com
    [root@lb-node1~]#curl http://192.168.90.203/biaogan/
    web-node2.com
    [root@lb-node1~]#curl http://192.168.90.203/biaogan/
    web-node2.com
    [root@lb-node1~]#curl http://192.168.90.203/biaogan/
    web-node1.com
    

5.使用HTTP访问Apache管理页面

访问 http://192.168.90.203/manager Apache管理页面 

### 3.2APache配置文件详解

proxy代理配置文件注释

    #proxy模块
    LoadModuleproxy_module modules/mod_proxy.so
    #链接模块
    LoadModuleproxy_connect_module modules/mod_proxy_connect.so
    #http代理模块
    LoadModuleproxy_http_module modules/mod_proxy_http.so
    #负载均衡模块
    LoadModuleproxy_balancer_module modules/mod_proxy_balancer.so
    
    
    #算法默认是byrequest,可以是bytraffic或者bybusyness
    
    #算法模块，根据server的请求量
    LoadModulelbmethod_byrequests_module modules/mod_lbmethod_byrequests.so
    #算法模块，根据server流量
    LoadModulelbmethod_bytraffic_module modules/mod_lbmethod_bytraffic.so
    #算法模块，根据server繁忙
    LoadModulelbmethod_bybusyness_module modules/mod_lbmethod_bybusyness.so
    
    
    LoadModuleslotmem_shm_module modules/mod_slotmem_shm.so
    ProxyRequests Off
    
    #LB集群组名称
    <Proxybalancer://web-cluster>
    #node节点并设置权重(可很多)
    BalancerMemberhttp://192.168.90.201:8080 loadfactor=1
    BalancerMemberhttp://192.168.90.202:8080 loadfactor=2
    </Proxy>
    
    #跳转至LB集群组名称,交由后端WEB节点处理
    ProxyPass /biaogan balancer://web-cluster
    ProxyPassReverse /biaogan balancer://web-cluster
    
    # Apache管理页面
    <Location /manager>
     SetHandlerbalancer-manager
     Order Deny,Allow
     Allow fromall
    </Location>


[1]: http://www.xuliangwei.com/xubusi/772.html