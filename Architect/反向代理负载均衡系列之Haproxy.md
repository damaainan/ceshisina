# 反向代理负载均衡系列之Haproxy

 时间 2017-01-01 20:05:15  徐亮偉架构师之路

原文[http://www.xuliangwei.com/xubusi/784.html][1]



## 1.反向代理概述

反向代理（Reverse Proxy）方式是指以代理服务器来接受internet上的连接请求，然后将请求转发给内部网络上的服务器，并将从服务器上得到的结果返回给internet上请求连接的客户端，此时代理服务器对外就表现为一个反向代理服务器。

环境准备:

主机名 | IP地址 | 角色 | 系统 
-|-|-|-
web-node1.com |  eth0:192.168.90.201 | web-node1节点 | CentOS7.2 
web-node2.com |  eth0:192.168.90.202 | web-node2节点 | CentOS7.2 
lb-node1.com  |  eth0:192.168.90.203 | Nginx反向代理 | CentOS7.2 

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

1.Haproxy 源码编译安装，并监听80端口

    [root@lb-node1~]#cd/usr/local/src/
    [root@lb-node1 src]#wget http://www.haproxy.org/download/1.6/src/haproxy-1.6.9.tar.gz
    [root@lb-node1 src]#tar xf haproxy-1.6.9.tar.gz
    [root@lb-node1 src]#cd haproxy-1.6.9
    [root@lb-node1 haproxy-1.6.9]#make TARGET=linux2628 PREFIX=/usr/local/haproxy-1.6.9
    [root@lb-node1 haproxy-1.6.9]#make install
    [root@lb-node1~]#cp/usr/local/sbin/haproxy/usr/sbin/
    [root@lb-node1~]#haproxy-v
    HA-Proxyversion1.6.9 2016/08/30
    Copyright 2000-2016 Willy Tarreau <willy@haproxy.org>
    
    
    ## Haproxy启动脚本
    [root@lb-node1~]#cp/usr/local/src/haproxy-1.6.9/examples/haproxy.init/etc/init.d/haproxy
    [root@lb-node1~]#chmod755 /etc/init.d/haproxy
    
    
    ## Haproxy配置文件
    [root@lb-node1~]#useradd-r haproxy
    [root@lb-node1~]#mkdir/etc/haproxy
    [root@lb-node1~]#mkdir/var/lib/haproxy
    [root@lb-node1~]#mkdir/var/run/haproxy
    

2.编辑Haproxy配置文件，并启动

    [root@lb-node1~]#cat/etc/haproxy/haproxy.cfg
    global
    log127.0.0.1local3 info
    chroot/var/lib/haproxy
     user haproxy
     grouphaproxy
     daemon
    
    defaults
    logglobal
     mode http
     option httplog
     option dontlognull
    timeout connect5000
    timeout client50000
    timeout server50000
    
    frontend ha_xuliangwei_com
     mode http
    bind*:80
    stats uri/haproxy?stats
     default_backend ha_xuliangwei_com_backend
     ##acl配置
    acl proxy_xuliangwei_com hdr_end(host)proxy.xuliangwei.com
    use_backend proxy_xuliangwei_com_backendifproxy_xuliangwei_com
    
    
    backend ha_xuliangwei_com_backend
    #source cookie SERVERID
    option forwardfor header X-REAL-IP
    option httpchk GET/index.html
     balance roundrobin
    server web-node1192.168.90.201:8080check inter2000rise3fall3weight1
    server web-node2192.168.90.202:8080check inter2000rise3fall3weight1
    
    backend proxy_xuliangwei_com_backend
    option forwardfor header X-REAL-IP
    option httpchk GET/index.html
     balance roundrobin
    server web-node2192.168.90.202:8080check inter2000rise3fall3weight1
    
    
    ##配置Haproxy日志
    [root@lb-node1~]#sed-i's@\#\$ModLoad imudp@\$ModLoad imudp@g' /etc/rsyslog.conf
    [root@lb-node1~]#sed-i's@\#\$UDPServerRun 514@\$UDPServerRun 514@g' /etc/rsyslog.conf
    [root@lb-node1~]#echo"local3.* /var/log/haproxy.log" >> /etc/rsyslog.conf
    
    ##启动Haproxy服务
    [root@lb-node1~]# /etc/init.d/haproxy start
    

3.测试Haproxy

    [root@lb-node1~]#curl http://192.168.90.203/
    web-node2.com
    [root@lb-node1~]#curl http://192.168.90.203/
    web-node1.com
    [root@lb-node1~]#curl http://192.168.90.203/
    web-node2.com
    [root@lb-node1~]#curl http://192.168.90.203/
    web-node1.com
    

proxy.xuliangwei.com调度至web-node2(解析hosts) [更多acl配置][4]

    [root@lb-node1~]#curl proxy.xuliangwei.com
    web-node2.com
    [root@lb-node1~]#curl proxy.xuliangwei.com
    web-node2.com
    

4.Haproxy状态管理页面

访问: http://192.168.90.203/haproxy?stats haproxy状态管理页面 

### 3.1Haproxy动态维护

1.在global下添加socket文件

    stats socket/var/lib/haproxy/haproxy.sock mode600level admin
    stats timeout2m
    

2.安装socat

    [root@lb-node1~]#yum install-y socat
    #查看Haproxy的help
    [root@lb-node1~]#echo"help" |socat stdio/var/lib/haproxy/haproxy.sock
    

3.查看info状态信息，可以通过zabbix来监控相关状态值

    [root@lb-node1~]#echo"show info" |socat stdio/var/lib/haproxy/haproxy.sock
    Name: HAProxy
    Version: 1.6.9
    Release_date: 2016/08/30
    Nbproc: 1
    Process_num: 1
    Pid: 6108
    Uptime: 0d 0h01m24s
    Uptime_sec: 84
    Memmax_MB: 0
    Ulimit-n: 4034
    Maxsock: 4034
    Maxconn: 2000
    Hard_maxconn: 2000
    CurrConns: 0
    CumConns: 2
    CumReq: 2
    Maxpipes: 0
    PipesUsed: 0
    PipesFree: 0
    ConnRate: 0
    ConnRateLimit: 0
    MaxConnRate: 0
    SessRate: 0
    SessRateLimit: 0
    MaxSessRate: 0
    CompressBpsIn: 0
    CompressBpsOut: 0
    CompressBpsRateLim: 0
    Tasks: 9
    Run_queue: 1
    Idle_pct: 100
    node:lb-node1.com
    description:
    

4.Haproxy维护模式

关闭proxy_xuliangwei_com下web-node2

    [root@lb-node1~]#echo"disable server proxy_xuliangwei_com_backend/web-node2" |socat stdio/var/lib/haproxy/haproxy.sock
    
    Message fromsyslogd@localhost atOct 19 17:16:56 ...
    haproxy[6180]:backend proxy_xuliangwei_com_backend hasnoserver available!
    

![][5]

Haproxy维护模式 

重启启动web-node2(此操作对现有Server生效,不支持新增加节点)

    [root@lb-node1~]#echo"enable server proxy_xuliangwei_com_backend/web-node2" |socat stdio/var/lib/haproxy/haproxy.sock
    

![][6]

Haproxy维护模式 

### 3.2Haproxy生产使用建议

haproxy的本地端口会出现用尽情况，解决方案如下4条

1.更改local的端口范围,调整内核参数

    [root@lb-node1~]#cat/proc/sys/net/ipv4/ip_local_port_range
    32768 61000
    

2.调整timewait的端口复用，设置为1

    [root@lb-node1~]#cat/proc/sys/net/ipv4/tcp_tw_reuse
    1
    

3.调整tcp_wait的时间，不建议修改

    [root@lb-node1~]#cat/proc/sys/net/ipv4/tcp_fin_timeout
    60
    

4.最佳方案：增加多个ip，端口数量就足够

### 3.3Haproxy与nginx

Nginx 

服务 | 优点 | 缺点 
-|-|-
Nginx | web服务器，应用广泛，安装配置简单 | 健康检查单一 
- | 7层负载均衡，并且支持4层负载均衡 | 负载均衡算法少 
- | 性能强大，网络依赖小 | 不支持动态管理 
- | location灵活匹配 | 没有集群管理状态页面 

Haproxy 

服务 | 优点 | 缺点 
-|-|-
Haproxy | 高性能负载均衡、负载均衡算法比较多 | 1.配置稍有麻烦 
- | 强大7层代理，性能优于Nginx | 2.应用没有nginx广泛 
- | 与socket通信进行动态管理 | - 
- | 丰富的集群管理状态页面 | -


[1]: http://www.xuliangwei.com/xubusi/784.html
[4]: http://cbonte.github.io/haproxy-dconv/1.6/configuration.html
[5]: ./img/RJVnQ3m.png
[6]: ./img/jquuEnI.png