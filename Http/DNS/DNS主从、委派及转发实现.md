# DNS主从、委派及转发实现

 时间 2017-09-20 16:02:47  

原文[http://zhimajihua.cn/post/dnsSynthesise.html][1]


本实例，我们将通过多种DNS技术的交错，说明如何搭建DNS主从服务器、DNS转发及子域授权的相关知识。

前提条件：

* 时间同步；
* 关闭防火墙；
* 关闭selinux；
* 各个DNS主机将DNS指向自己；
* 假定各个将用作DNS的主机都已安装bind，如果没有安装，你可以运行以下命令 yum -y install bind

主要步骤

主从区域构建 -> 子域授权 -> 转发服务器构建主从服务器构建

主服务器* 编辑主配置文件 

    [root@DataCenter ~]# vim /etc/named.conf
    options {
        listen-on port 53 { localhost; };  #监听本机所有IP地址(等同于删除该行)，也可以填写具体的IP地址
    #   listen-on-v6 port 53 { ::1; };       #监听IPv6地址 如果没有使用可注释或删除
              此处省略若干行
        allow-query     { 192.168.1.0/24; };  #允许指定网络主机查询DNS
* 添加具体的解析域 

```
    [root@DataCenter ~]# vim /etc/named.rfc1912.zones
    25 zone "zhimajihua.cn" IN {  #定义域名
    26     type master;                 #定义域类型(必选)，可选的值为{master|slave|hint|forward}
    27     file "zhimajihua.cn.zone";  #定义解析库文件的位置，该路径是/var/named的相对路径
    28 };
```

* 编辑解析库文件 

```
    [root@DataCenter ~]# cd /var/named/
    [root@DataCenter named]# ll
    total 16
    drwxrwx--- 2 named named    6 Aug 23 21:32 data
    drwxrwx--- 2 named named    6 Aug 23 21:32 dynamic
    -rw-r----- 1 root  named 2281 May 22 17:51 named.ca  #权限至少为640(建议值也是640)，且组所有者为named组
    -rw-r----- 1 root  named  152 Dec 15  2009 named.empty
    -rw-r----- 1 root  named  152 Jun 21  2007 named.localhost
    -rw-r----- 1 root  named  168 Dec 15  2009 named.loopback
    drwxrwx--- 2 named named    6 Aug 23 21:32 slaves
    [root@DataCenter named]# cp -p named.localhost zhimajihua.cn.zone  #为避免权限问题 建议拷贝示例
    [root@DataCenter named]# vim zhimajihua.cn.zone
    [root@DataCenter named]# cat zhimajihua.cn.zone
    $TTL 1D
    @       IN SOA  dns1 rname.invalid. (  #SOA记录 必须为第1条
                                          1       ; serial      #序列号
                                          1D      ; refresh  #刷新时间
                                          1H      ; retry     #重试时间(应小于刷新时间)
                                          1W      ; expire  #过期时间
                                          3H )    ; minimum  #否定答案的TTL值
          NS      dns1                     #dns主机的NS记录
          NS     slave                     #此记录为从DNS主机的NS解析
    dns1    A   192.168.1.100      #对应记录的IP指向
    slave   A   192.168.1.200
    www     A   192.168.1.33
    test    A   192.168.1.66
    ftp     A   192.168.1.77
```

* 语法检查 

```
    [root@DataCenter named]# named-checkconf  #检查相关配置文件是否存在语法错误
    [root@DataCenter named]# named-checkzone 'zhimajihua.cn.zone' zhimajihua.cn.
    zone  #检查区域解析库文件是否存在语法错误
    zone zhimajihua.cn.zone/IN: loaded serial 1
    OK
    [root@DataCenter named]# systemctl restart named  #重启服务
```

* 在客户端测试该DNS服务器

```
    [root@client ~]# dig www.zhimajihua.cn @192.168.1.100 #dig FQDN @IP：指定DNS解析特定域名
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> www.zhimajihua.cn @192.168.1.100
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 31233
    ;; flags: qr aa rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 2, ADDITIONAL: 3
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;www.zhimajihua.cn.             IN      A
    
    ;; ANSWER SECTION:
    www.zhimajihua.cn.      86400   IN      A       192.168.1.33
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86400   IN      NS      slave.zhimajihua.cn.
    zhimajihua.cn.          86400   IN      NS      dns1.zhimajihua.cn.
    
    ;; ADDITIONAL SECTION:
    dns1.zhimajihua.cn.     86400   IN      A       192.168.1.100
    slave.zhimajihua.cn.    86400   IN      A       192.168.1.200
    
    ;; Query time: 2 msec
    ;; SERVER: 192.168.1.100#53(192.168.1.100)
    ;; WHEN: Wed Sep 20 11:02:54 GMT 2017
    ;; MSG SIZE  rcvd: 133
```

OK, 通过上述返回的解析结果可以确定，主DNS服务器构建完成，且可正确解析。

从服务器* 同主DNS服务器类似，修改主配置文件。 

    options {
          listen-on port 53 { localhost; };  
          listen-on-v6 port 53 { ::1; };
          directory       "/var/named";
          dump-file       "/var/named/data/cache_dump.db";
          statistics-file "/var/named/data/named_stats.txt";
          memstatistics-file "/var/named/data/named_mem_stats.txt";
          allow-query     { 192.168.1.0/24; };

    zone "zhimajihua.cn" IN {
          type slave;  #指明本服务器类型为DNS Slave Host
          masters { 192.168.1.100; };  #给出主服务器的IP地址
          file "slaves/zhimajihua.cn.slave.zone";  #解析库文件路径，请注意，从服务器文件在slaves目录下
    };
* 查看当前/var/named/slaves/下，并没有任何区域解析配置文件 

```
    [root@dnsslave ~]# cd /var/named/slaves/
    [root@dnsslave slaves]# ll
    total 0
```

* 重启服务。再次查看，我们发现相应的域解析库文件同步过来了

```
    [root@dnsslave slaves]# systemctl restart named
    [root@dnsslave slaves]# ll
    total 4
    -rw-r--r--. 1 named named 425 Sep 20 11:10 zhimajihua.cn.slave.zone
```

* 同样的，我们通过客户端利用该从DNS服务器解析

```
    [root@client ~]# dig ftp.zhimajihua.cn @192.168.1.200
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> ftp.zhimajihua.cn @192.168.1.200
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 26908
    ;; flags: qr aa rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 2, ADDITIONAL: 3
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;ftp.zhimajihua.cn.             IN      A
    
    ;; ANSWER SECTION:
    ftp.zhimajihua.cn.      86400   IN      A       192.168.1.77
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86400   IN      NS      dns1.zhimajihua.cn.
    zhimajihua.cn.          86400   IN      NS      slave.zhimajihua.cn.
    
    ;; ADDITIONAL SECTION:
    dns1.zhimajihua.cn.     86400   IN      A       192.168.1.100
    slave.zhimajihua.cn.    86400   IN      A       192.168.1.200
    
    ;; Query time: 2 msec
    ;; SERVER: 192.168.1.200#53(192.168.1.200)
    ;; WHEN: Wed Sep 20 11:12:29 GMT 2017
    ;; MSG SIZE  rcvd: 133
```
为了确定是否会自动传送区域文件，现在我们修改主DNS服务器的配置

    [root@DataCenter named]# vim zhimajihua.cn.zone
    [root@DataCenter named]# cat zhimajihua.cn.zone
    $TTL 1D
    @       IN SOA  dns1 rname.invalid. (
                                            2       ; serial  #修改完后 务必记得此处需要递增，一般每次加1
                                            1D      ; refresh
                                            1H      ; retry
                                            1W      ; expire
                                            3H )    ; minimum
            NS      dns1
            NS      slave
    dns1    A   192.168.1.100
    slave   A   192.168.1.200
    www     A   192.168.1.33
    test    A   192.168.1.66
    ftp     A   192.168.1.77
    xiaomu  A   192.168.1.99  #新增一条A记录
    [root@DataCenter named]# systemctl restart named  #重启主DNS服务

查看当前的从DNS服务器。请注意同之前时间的区别

    [root@dnsslave slaves]# ll
    total 4
    -rw-r--r--. 1 named named 473 Sep 20 11:16 zhimajihua.cn.slave.zone

再次通过客户端验证

    [root@client ~]# dig xiaomu.zhimajihua.cn @192.168.1.200
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> xiaomu.zhimajihua.cn @192.168.1.20
    0
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 866
    ;; flags: qr aa rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 2, ADDITIONAL: 3
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;xiaomu.zhimajihua.cn.          IN      A
    
    ;; ANSWER SECTION:
    xiaomu.zhimajihua.cn.   86400   IN      A       192.168.1.99
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86400   IN      NS      dns1.zhimajihua.cn.
    zhimajihua.cn.          86400   IN      NS      slave.zhimajihua.cn.
    
    ;; ADDITIONAL SECTION:
    dns1.zhimajihua.cn.     86400   IN      A       192.168.1.100
    slave.zhimajihua.cn.    86400   IN      A       192.168.1.200
    
    ;; Query time: 0 msec
    ;; SERVER: 192.168.1.200#53(192.168.1.200)
    ;; WHEN: Wed Sep 20 11:17:44 GMT 2017
    ;; MSG SIZE  rcvd: 136

至此，从服务器确认可以正常同主服务器配合工作。

构建cn域并对zhimajihua.cn域进行委派

* 在cn域(CN Domain)DNS主机上编辑主配置文件 

```
    [root@cnDNS ~]# vim /etc/named.conf
    options {
          listen-on port 53 { localhost; };
          listen-on-v6 port 53 { ::1; };
          directory       "/var/named";
          dump-file       "/var/named/data/cache_dump.db";
          statistics-file "/var/named/data/named_stats.txt";
          memstatistics-file "/var/named/data/named_mem_stats.txt";
          allow-query     { any; };  #cn域旗下有千万以上的被委派域 因此使用any 即允许任何主机查询

    [root@cnDNS ~]# vim /etc/named.rfc1912.zones
    zone "cn" IN {  #同上
          type master;
          file "cn.zone";
    };
```

* 编辑区域库文件

```
    [root@cnDNS ~]# cd /var/named/
    [root@cnDNS named]# ll
    total 28
    drwxrwx--- 2 named named 4096 Mar 22 20:26 data
    drwxrwx--- 2 named named 4096 Mar 22 20:26 dynamic
    -rw-r----- 1 root  named 3171 Jan 11  2016 named.ca
    -rw-r----- 1 root  named  152 Dec 15  2009 named.empty
    -rw-r----- 1 root  named  152 Jun 21  2007 named.localhost
    -rw-r----- 1 root  named  168 Dec 15  2009 named.loopback
    drwxrwx--- 2 named named 4096 Mar 22 20:26 slaves
    [root@cnDNS named]# cp -p named.localhost cn.zone
    [root@cnDNS named]# vim cn.zone
    [root@cnDNS named]# cat cn.zone
    $TTL 1D
    @       IN SOA  dns1 rname.invalid. (
                                            0       ; serial
                                            1D      ; refresh
                                            1H      ; retry
                                            1W      ; expire
                                            3H )    ; minimum
                    NS      dns1
    zhimajihua      NS      zmjhdns  #注意，需要添加别委派的域的NS记录及A记录
    dns1            A       192.168.1.20
    zmjhdns         A       192.168.1.100
    
    [root@cnDNS named]# named-checkconf
    [root@cnDNS named]# named-checkzone 'cn.zone' cn.zone
    zone cn.zone/IN: loaded serial 0
    OK
```

重启服务以生效

    [root@cnDNS named]# service named restart

我们再次回到客户机上，现在，我们需要明确一点， _如果将DNS指向cn域的DNS主机能够解析zhimajihua.cn域下的Name才能说明子域授权正确完成，否则失败_

    [root@client ~]# dig ftp.zhimajihua.cn @192.168.1.20
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> ftp.zhimajihua.cn @192.168.1.20
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 11154
    ;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 1, ADDITIONAL: 2
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;ftp.zhimajihua.cn.             IN      A
    
    ;; ANSWER SECTION:
    ftp.zhimajihua.cn.      86396   IN      A       192.168.1.77
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86400   IN      NS      zmjhdns.cn.
    
    ;; ADDITIONAL SECTION:
    zmjhdns.cn.             86400   IN      A       192.168.1.100
    
    ;; Query time: 0 msec
    ;; SERVER: 192.168.1.20#53(192.168.1.20)
    ;; WHEN: Wed Sep 20 11:31:42 GMT 2017
    ;; MSG SIZE  rcvd: 100

OK，通过返回的结果我们可以确定，授权正确完成。

根域的搭建和授权

在根域(Root Domain)主机上编辑配置文件

    [root@rootdns ~]# vim /etc/named.conf
    options {
            listen-on port 53 { localhost; };
            listen-on-v6 port 53 { ::1; };
            directory       "/var/named";
            dump-file       "/var/named/data/cache_dump.db";
            statistics-file "/var/named/data/named_stats.txt";
            memstatistics-file "/var/named/data/named_mem_stats.txt";
            allow-query     { any; };  #同cn域 应该定义为any
    //删除下面4行 因为根域不需要去查找自己在哪里 其知道自己的根域
    zone "." IN {
            type hint;
            file "named.ca";
    };

    zone "." IN {  #重新创建一个根域库文件指向
            type master;
            file "root.zone";
    };

编辑根域的库文件

    [root@rootdns ~]# cd /var/named/
    [root@rootdns named]# cp -p named.localhost root.zone
    [root@rootdns named]# vim root.zone
    [root@rootdns named]# cat root.zone
    $TTL 1D
    @       IN SOA  dns1 rname.invalid. (
                                            0       ; serial
                                            1D      ; refresh
                                            1H      ; retry
                                            1W      ; expire
                                            3H )    ; minimum
            NS      dns1  
    cn      NS      cndns  #记录委派的cn域的NS记录和A记录
    dns1    A       192.168.1.10
    cndns   A       192.168.1.20
    [root@rootdns named]# named-checkconf
    [root@rootdns named]# named-checkzone "." root.zone
    zone ./IN: loaded serial 0
    OK
    [root@rootdns named]# systemctl restart named

同样，我们在客户机上测试

    [root@client ~]# dig www.zhimajihua.cn @192.168.1.10
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> www.zhimajihua.cn @192.168.1.10
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 25751
    ;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 1, ADDITIONAL: 2
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;www.zhimajihua.cn.             IN      A
    
    ;; ANSWER SECTION:
    www.zhimajihua.cn.      86400   IN      A       192.168.1.33
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86389   IN      NS      zmjhdns.cn.
    
    ;; ADDITIONAL SECTION:
    zmjhdns.cn.             86389   IN      A       192.168.1.100
    
    ;; Query time: 3 msec
    ;; SERVER: 192.168.1.10#53(192.168.1.10)
    ;; WHEN: Wed Sep 20 12:30:17 GMT 2017
    ;; MSG SIZE  rcvd: 100

通过结果，我们可以判定根域已经正常工作。

转发服务器的构建

由于是本地模拟包含根域在内的DNS系统，因此我们需要修改主机NetService上的根文件，将其指向我们前面构建的根域192.168.1.10

    [root@NetService ~]# cd /var/named/
    [root@NetService named]# vim named.ca
    ; <<>> DiG 9.9.2-P1-RedHat-9.9.2-6.P1.fc18 <<>> +bufsize=1200 +norec @a.root
    -servers.net
    ; (2 servers found)
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 25828
    ;; flags: qr aa; QUERY: 1, ANSWER: 13, AUTHORITY: 0, ADDITIONAL: 23
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 512
    ;; QUESTION SECTION:
    ;.                              IN      NS
    
    ;; ANSWER SECTION:
    .                       518400  IN      NS      a.root-servers.net.  #该行必须保留 因为这里规定了根域的NS记录
    a.root-servers.net.     3600000 IN      A       198.168.1.10  #根域的A记录 必须保留 但是注意IP地址改为我们自己创建的

    [root@NetService ~]# vim /etc/named.conf
    options {
            listen-on port 53 { localhost; };
            directory       "/var/named";
            dump-file       "/var/named/data/cache_dump.db";
            statistics-file "/var/named/data/named_stats.txt";
            memstatistics-file "/var/named/data/named_mem_stats.txt";
            allow-query     { localhost; 192.168.1.0/24; };  #只开放给机器自身和本网络的其它主机查询
            recursion yes;
            dnssec-enable no;
            dnssec-validation no;
            forward first;                #设置转发模式
            forwarders { 192.168.1.10;};  #目标转发主机

在客户端测试

    [root@client ~]# dig www.zhimajihua.cn @192.168.1.30
    
    ; <<>> DiG 9.9.4-RedHat-9.9.4-37.el7 <<>> www.zhimajihua.cn @192.168.1.30
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 25751
    ;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 1, ADDITIONAL: 2
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;www.zhimajihua.cn.             IN      A
    
    ;; ANSWER SECTION:
    www.zhimajihua.cn.      86400   IN      A       192.168.1.33
    
    ;; AUTHORITY SECTION:
    zhimajihua.cn.          86389   IN      NS      zmjhdns.cn.
    
    ;; ADDITIONAL SECTION:
    zmjhdns.cn.             86389   IN      A       192.168.1.100
    
    ;; Query time: 3 msec
    ;; SERVER: 192.168.1.10#53(192.168.1.30)
    ;; WHEN: Wed Sep 20 12:33:17 GMT 2017
    ;; MSG SIZE  rcvd: 100

通过返回的解析结果可以确定，转发服务器工作正常。

* 至此，实验完成，感谢阅读。


[1]: http://zhimajihua.cn/post/dnsSynthesise.html
