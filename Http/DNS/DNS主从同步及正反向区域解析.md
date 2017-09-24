# DNS主从同步及正反向区域解析

 时间 2017-09-24 15:22:37  

原文[http://zhimajihua.cn/post/dnsSyncPlus.html][1]


环境说明：

* 主DNS：Centos 6.9, IP：192.168.1.19
* 从DNS：Centos 7.3, IP：192.168.1.20

开始前的准备工作

1. 关闭防火墙和SELinxu 

```
    [root@Centos6 ~]# chkconfig iptables off  #对于Centos 6
    [root@centos7 ~]# systemctl disable firewalld  #对于Centos 7
    [root@Centos6 ~]# sed -i.bak 's@SELINUX=enforcing@SELINUX=disabled@' /etc/selinux/config  #通用

    [root@Centos6 ~]# setenforce 0  #通用
```
1. 安装bind软件 

```

    [root@Centos6 ~]# yum -y install bind  #必须保持主从版本一致或主低从高
```
1. 设置named服务开机启动，并启动服务 

```
    [root@Centos6 ~]# chkconfig named on
    [root@Centos6 ~]# service named start
    [root@centos7 ~]# systemctl enable named
    [root@centos7 ~]# systemctl start named
```

配置缓存服务器

分别在Centos 6和Centos 7上执行下面的操作

    [root@Centos6 ~]# vim /etc/named.conf
    options {
            listen-on port 53 { localhost; };  #监听本机所有端口
            //listen-on-v6 port 53 { ::1; };
            directory       "/var/named";
            dump-file       "/var/named/data/cache_dump.db";
            statistics-file "/var/named/data/named_stats.txt";
            memstatistics-file "/var/named/data/named_mem_stats.txt";
            allow-query     { localhost; 192.168.1.0/24; };  #允许本机和192.168.1.0/24网段查询
            recursion yes;  #允许递归
    
            dnssec-enable no;  #安全检查项，建议7.3以下系统关闭此项
            dnssec-validation no;  #安全检查项，建议7.3以下系统关闭此项
    
            /* Path to ISC DLV key */
            bindkeys-file "/etc/named.iscdlv.key";
    
            managed-keys-directory "/var/named/dynamic";
    };

配置主服务器


* 编辑主配置文件 named.conf ,在 options 配置块添加以下控制语句

```
    allow-transfer { 192.168.1.20; };  #仅允许同特定IP主机进行区域传送
```

* 编辑辅助区域文件 named.rfc1912.zones ，添加域

```
    [root@Centos6 ~]# vim /etc/named.rfc1912.zones
    #添加下述区域配置块
    zone "zhimajihua.cn" IN {  #正向区域
            type master;
            file "zhimajihua.cn.zone";
            allow-update { none; };
    };
    
    zone "1.168.192.in-addr.arpa" IN {  #反向区域
            type master;
            file "1.168.192.in-addr.arpa.zone";
            allow-update { none; };
    };
```

* 进行配置文件的语法检查

```
    [root@Centos6 ~]# named-checkconf
```

* 创建区域解析库文件

```
    [root@Centos6 ~]# cd /var/named
    [root@Centos6 named]# ll
    total 28
    drwxrwx--- 2 named named 4096 Sep 23 20:55 data
    drwxrwx--- 2 named named 4096 Sep 23 20:55 dynamic
    -rw-r----- 1 root  named 3171 Jan 11  2016 named.ca
    -rw-r----- 1 root  named  152 Dec 15  2009 named.empty
    -rw-r----- 1 root  named  152 Jun 21  2007 named.localhost
    -rw-r----- 1 root  named  168 Dec 15  2009 named.loopback
    drwxrwx--- 2 named named 4096 Mar 22  2017 slaves
    [root@Centos6 named]# cp -p named.localhost zhimajihua.cn.zone
    [root@Centos6 named]# vim zhimajihua.cn.zone  #正向区域
    $TTL 1D
    @       IN SOA ns1 mu.zhimajihua.cn. (
                                            2017092401      ; serial
                                            1D              ; refresh
                                            1H              ; retry
                                            1W              ; expire
                                            3H )            ; minimum
            NS      ns1
            NS      ns2
            MX   5  mx1
    ns1     A       192.168.1.19
    ns2     A       192.168.1.20
    mx1     A       192.168.1.30
    web     A       192.168.1.40
    image   A       192.168.1.50
    www     CNAME   web
    
    [root@Centos6 named]# vim 1.168.192.in-addr.arpa.zone  #反向区域
    $TTL 1D
    @       IN SOA  ns1 mu.zhimajihua.cn. (
                                            2017092401
                                            1D
                                            1H
                                            1W
                                            3H )
            NS      ns1.zhimajihua.cn.
            NS      ns2.zhimajihua.cn.
    19      PTR     ns1.zhimajihua.cn.
    20      PTR     ns2.zhimajihua.cn.
    30      PTR     mx1.zhimajihua.cn.
    40      PTR     www.zhimajihua.cn.
    50      PTR     image.zhimajihua.cn.
```

* 进行区域解析的语法检查

```
    [root@Centos6 named]# named-checkzone 'zhimajihua.cn.zone' zhimajihua.cn.zon
    e
    zone zhimajihua.cn.zone/IN: loaded serial 20170924
    OK
    [root@Centos6 named]# named-checkzone '1.168.192.in-addr.arpa.zone' 1.168.19
    2.in-addr.arpa.zone
    zone 1.168.192.in-addr.arpa.zone/IN: loaded serial 20170924
    OK
    [root@Centos6 named]# service named restart

配置从服务器
```

* 编辑主配置文件 named.conf ,在 options 配置块添加以下控制语句

```
    allow-transfer { none; };
```

* 编辑辅助区域文件 named.rfc1912.zones ，添加域

```
    [root@centos7 ~]# vim /etc/named.rfc1912.zones
    #添加下述区域配置块
    zone "zhimajihua.cn" IN {  #正向区域
            type slave;
            masters { 192.168.1.19; };
            file "slaves/zhimajihua.cn.zone";
    };
    
    zone "1.168.192.in-addr.arpa" IN {    #反向区域
            type slave;
            masters { 192.168.1.19; };
            file "slaves/1.168.192.in-addr.arpa.zone";
    };
    
    [root@centos7 ~]# named-checkconf
```

* 区域传送

```
    [root@centos7 ~]# ll /var/named/slaves/  #当前从服务器的区域数据库目录无任何区域文件
    total 0
    [root@centos7 ~]# systemctl restart named  #重启服务使其生效并开始区域传送
    [root@centos7 ~]# ll /var/named/slaves/  #
    total 8
    -rw-r--r-- 1 named named 564 Sep 23 22:04 1.168.192.in-addr.arpa.zone
    -rw-r--r-- 1 named named 540 Sep 23 22:04 zhimajihua.cn.zone
    [root@client ~]# dig www.zhimajihua.cn @192.168.1.20  #对从服务器进行正向解析测试
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> www.zhimajihua.cn @192.168.1.20
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 6093
    ;; flags: qr aa rd ra; QUERY: 1, ANSWER: 2, AUTHORITY: 2, ADDITIONAL: 3
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;www.zhimajihua.cn.             IN      A
    
    ;; ANSWER SECTION:
    www.zhimajihua.cn.      86400   IN      CNAME   web.zhimajihua.cn.
    web.zhimajihua.cn.      86400   IN      A       192.168.1.40
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86400   IN      NS      ns1.zhimajihua.cn.
    zhimajihua.cn.          86400   IN      NS      ns2.zhimajihua.cn.
    
    ;; ADDITIONAL SECTION:
    ns1.zhimajihua.cn.      86400   IN      A       192.168.1.19
    ns2.zhimajihua.cn.      86400   IN      A       192.168.1.20
    
    ;; Query time: 0 msec
    ;; SERVER: 192.168.1.20#53(192.168.1.20)
    ;; WHEN: Sat Sep 23 22:07:51 GMT 2017
    ;; MSG SIZE  rcvd: 148
    
    [root@client ~]# dig -x 192.168.1.50 @192.168.1.20  #对从服务器进行反向解析测试
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> -x 192.168.1.50 @192.168.1.20
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 1837
    ;; flags: qr aa rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 2, ADDITIONAL: 3
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;50.1.168.192.in-addr.arpa.     IN      PTR
    
    ;; ANSWER SECTION:
    50.1.168.192.in-addr.arpa. 86400 IN     PTR     image.zhimajihua.cn.
    
    ;; AUTHORITY SECTION:
    1.168.192.in-addr.arpa. 86400   IN      NS      ns1.zhimajihua.cn.
    1.168.192.in-addr.arpa. 86400   IN      NS      ns2.zhimajihua.cn.
    
    ;; ADDITIONAL SECTION:
    ns1.zhimajihua.cn.      86400   IN      A       192.168.1.19
    ns2.zhimajihua.cn.      86400   IN      A       192.168.1.20
    
    ;; Query time: 0 msec
    ;; SERVER: 192.168.1.20#53(192.168.1.20)
    ;; WHEN: Sat Sep 23 22:08:24 GMT 2017
    ;; MSG SIZE  rcvd: 155
```

检查主从DNS组合能否适应容灾工作和自动同步

* 检测自动同步

```
    [root@Centos6 named]# vim zhimajihua.cn.zone
    xiaomu  A       192.168.1.60  #在正向区域库文件添加一条A记录, 并将序列号增至2017092402
    [root@Centos6 named]# vim 1.168.192.in-addr.arpa.zone
    60      PTR     xiaomu.zhimajihua.cn.  #在反向区域库添加PTR记录, 并将序列号增至2017092402
    [root@Centos6 named]# named-checkzone 'zhimajihua.cn.zone' zhimajihua.cn.zon
    e
    zone zhimajihua.cn.zone/IN: loaded serial 2017092402
    OK
    [root@Centos6 named]# named-checkzone '1.168.192.in-addr.arpa.zone' 1.168.19
    2.in-addr.arpa.zone
    zone 1.168.192.in-addr.arpa.zone/IN: loaded serial 2017092402
    OK
    [root@Centos6 named]# service named restart
```

查看从服务器是否已更新区域库文件

    [root@centos7 ~]# ll /var/named/slaves/  #注意，时间已经变了，说明同步成功
    total 8
    -rw-r--r-- 1 named named 635 Sep 23 22:12 1.168.192.in-addr.arpa.zone
    -rw-r--r-- 1 named named 588 Sep 23 22:12 zhimajihua.cn.zone

通过 dig 命令进一步测试同步后是否能够正确解析 

    [root@client ~]# dig xiaomu.zhimajihua.cn @192.168.1.20  #正解测试
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-51.el7 <<>> xiaomu.zhimajihua.cn @192.168.1.20
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 5344
    ;; flags: qr aa rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 2, ADDITIONAL: 3
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;xiaomu.zhimajihua.cn.          IN      A
    
    ;; ANSWER SECTION:
    xiaomu.zhimajihua.cn.   86400   IN      A       192.168.1.60
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86400   IN      NS      ns1.zhimajihua.cn.
    zhimajihua.cn.          86400   IN      NS      ns2.zhimajihua.cn.
    
    ;; ADDITIONAL SECTION:
    ns1.zhimajihua.cn.      86400   IN      A       192.168.1.19
    ns2.zhimajihua.cn.      86400   IN      A       192.168.1.20
    
    ;; Query time: 0 msec
    ;; SERVER: 192.168.1.20#53(192.168.1.20)
    ;; WHEN: Sat Sep 23 22:25:24 CST 2017
    ;; MSG SIZE  rcvd: 133
    
    [root@client ~]# dig -x 192.168.1.60 @192.168.1.20  #反解测试
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-51.el7 <<>> -x 192.168.1.60 @192.168.1.20
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 63035
    ;; flags: qr aa rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 2, ADDITIONAL: 3
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;60.1.168.192.in-addr.arpa.     IN      PTR
    
    ;; ANSWER SECTION:
    60.1.168.192.in-addr.arpa. 86400 IN     PTR     xiaomu.zhimajihua.cn.
    
    ;; AUTHORITY SECTION:
    1.168.192.in-addr.arpa. 86400   IN      NS      ns2.zhimajihua.cn.
    1.168.192.in-addr.arpa. 86400   IN      NS      ns1.zhimajihua.cn.
    
    ;; ADDITIONAL SECTION:
    ns1.zhimajihua.cn.      86400   IN      A       192.168.1.19
    ns2.zhimajihua.cn.      86400   IN      A       192.168.1.20
    
    ;; Query time: 0 msec
    ;; SERVER: 192.168.1.20#53(192.168.1.20)
    ;; WHEN: Sat Sep 23 22:25:57 CST 2017
    ;; MSG SIZE  rcvd: 156

* 设置主从服务器的地址为client的默认DNS1及DNS2

```
    [root@client ~]# vim /etc/resolv.conf
    [root@client ~]# cat /etc/resolv.conf
    # Generated by NetworkManager
    search zhimajihua.cn
    nameserver 192.168.1.19
    nameserver 192.168.1.20
```
* 将主DNS的网卡down掉，看从服务器是否能够暂时接替工作

```
    [root@Centos6 named]# ifconfig eth0 down
    [root@client ~]# ping www.zhimajihua.cn  #会感觉稍有一会才解析过去的原因是因为会先去找DNS1
    PING web.zhimajihua.cn (192.168.1.40) 56(84) bytes of data.
    64 bytes from www.zhimajihua.cn (192.168.1.40): icmp_seq=1 ttl=64 time=0.594
     ms
    64 bytes from www.zhimajihua.cn (192.168.1.40): icmp_seq=2 ttl=64 time=0.620
     ms
    64 bytes from www.zhimajihua.cn (192.168.1.40): icmp_seq=3 ttl=64 time=0.493
     ms

区域传送安全检查
```
* 从服务器向主服务器及本机发起区域传送请求

```
    [root@centos7 ~]# dig -t axfr zhimajihua.cn @192.168.1.19
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> -t axfr zhimajihua.cn @192.168.1.1
    9
    ;; global options: +cmd
    zhimajihua.cn.          86400   IN      SOA     ns1.zhimajihua.cn. mu.zhimaj
    ihua.cn. 2017092402 86400 3600 604800 10800
    zhimajihua.cn.          86400   IN      NS      ns1.zhimajihua.cn.
    zhimajihua.cn.          86400   IN      NS      ns2.zhimajihua.cn.
    zhimajihua.cn.          86400   IN      MX      5 mx1.zhimajihua.cn.
    image.zhimajihua.cn.    86400   IN      A       192.168.1.50
    mx1.zhimajihua.cn.      86400   IN      A       192.168.1.30
    ns1.zhimajihua.cn.      86400   IN      A       192.168.1.19
    ns2.zhimajihua.cn.      86400   IN      A       192.168.1.20
    web.zhimajihua.cn.      86400   IN      A       192.168.1.40
    www.zhimajihua.cn.      86400   IN      CNAME   web.zhimajihua.cn.
    xiaomu.zhimajihua.cn.   86400   IN      A       192.168.1.60
    zhimajihua.cn.          86400   IN      SOA     ns1.zhimajihua.cn. mu.zhimaj
    ihua.cn. 2017092402 86400 3600 604800 10800
    ;; Query time: 0 msec
    ;; SERVER: 192.168.1.19#53(192.168.1.19)
    ;; WHEN: Sat Sep 23 22:51:21 GMT 2017
    ;; XFR size: 12 records (messages 1, bytes 293)
    
    [root@centos7 ~]# dig -t axfr zhimajihua.cn @192.168.1.20  #从服务器向自己请求
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> -t axfr zhimajihua.cn @192.168.1.2
    0
    ;; global options: +cmd
    ; Transfer failed.
```
* client端向主, 从服务器发起区域传送请求

```
    [root@client ~]# dig -t axfr zhimajihua.cn @192.168.1.19
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-51.el7 <<>> -t axfr zhimajihua.cn @192.168.1.1
    9
    ;; global options: +cmd
    ; Transfer failed.
    [root@client ~]# dig -t axfr zhimajihua.cn @192.168.1.20
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-51.el7 <<>> -t axfr zhimajihua.cn @192.168.1.2
    0
    ;; global options: +cmd
    ; Transfer failed.
    [root@client ~]#
```

[1]: http://zhimajihua.cn/post/dnsSyncPlus.html
