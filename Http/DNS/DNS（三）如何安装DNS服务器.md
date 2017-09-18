# DNS（三）如何安装DNS服务器

2017.07.08 06:10  字数 1069  

前两篇文章是从 DNS 使用的角度去理解 DNS 的，但是假如想进一步明白 DNS 内部运作原理，就有必要理解如何安装 DNS 服务器。DNS 服务器有多种类型，理解类型是安装 DNS 服务器的前提。同时从使用的角度看，理解 DNS 服务器类型，也能够更加灵活的使用和理解 DNS 系统。

#### DNS 服务器类型

（1）权威 DNS 服务器，对于具备一定规模的企业来说，为了方便控制自己域名的解析，会自己搭建权威服务器。通过权威服务器能够查询特定 zone 的信息（先不用管什么是 zone）。这种 DNS 服务器没有递归和迭代功能，只负责响应特定域名的解析。权威 DNS 服务器返回的结果永远是最新的。

（2）Caching DNS Server 

在自己电脑配置（比如 /etc/resolv.conf）的本地 DNS 服务器叫做 Caching DNS Server。客户端（实际上是 system resolver）会向 Caching DNS 服务器发送递归域名解析请求，而 Caching DNS 服务器会迭代查询域名解析结果。 对于上网的用户来说，可以使用 ISP 提供的 Caching DNS 服务器，也可以使用类似 8.8.8.8 这样的公共 DNS 服务器。

这种类型的 DNS 服务器一般企业内部会搭建，一方面是为了安全，所有的 DNS 查询经过内部的机器进行查询。另外一方面就是加速查询结果。

（3）Forwarding DNS Server 

对于客户端来说，Forwarding DNS 服务器和 Caching DNS 服务器功能上没有差别，但是在机制上两者是不一样的。它不会进行迭代查询，而是将所有的请求转发给其他的 DNS 服务器，然后缓存获取到的结果。

#### 安装 DNS 服务器

这篇博文使用 bind 这个软件来提供 DNS 服务器，安装的环境是 Ubuntu 14.04.4 LTS。

安装启动或者调试 bind ：

    apt-get install bind9 bind9utils bind9-doc
    named-checkconf #检查配置文件有没有错误
    service bind9 restart #重新启动 bind，修改配置文件后务必运行
    tail -f  /var/log/syslog  #查看系统文件

相关的配置文件：

    /etc/bind/named.conf #主文件，下面包含三个子文件
    
    include "/etc/bind/named.conf.options";
    include "/etc/bind/named.conf.local";
    include "/etc/bind/named.conf.default-zones";

#### 安装 Caching DNS Server

配置比较简单，只要修改 /etc/bind/named.conf.options 文件即可。

    options {
        directory "/var/cache/bind";
        recursion yes;
        allow-query { any; };
        dnssec-validation auto;
        auth-nxdomain no;  
        listen-on-v6 { any; };
    };

重要的参数就是 recursion，开启就行，表示允许客户端进行递归查询。一般非公共的 DNS 只允许内部的客户端进行 DNS 查询，可以给 allow-query 配置一个 acl ，假如允许所有客户端查询，配置 为 any 即可。

#### 安装 Forwarding DNS Server

配置比较简单，只要修改 /etc/bind/named.conf.options 文件即可。

    options {
        directory "/var/cache/bind";
    
        recursion yes;
        allow-query { any; };
    
        forwarders {
                8.8.8.8;
        };
        forward only;
        dnssec-validation auto;
        auth-nxdomain no;    
        listen-on-v6 { any; };
    };

重要的参数就两个，forwarders 配置需要转发查询的 DNS 列表，forward 表示转发请求（即便 recursion 参数配置为 yes）。

#### 安装 Authoritative-Only DNS Server

相对与前两种类型的 DNS 服务器来说，这种类型的服务器配置相对复杂，先做以下说明：

* 不演示 IP 反解的过程
* 不做 master/slave 配置
* NS 服务器名称是 ns.example.com.，对应的 IP 地址是 139.129.23.162

（1）在服务器上修改 /etc/hosts，配置 

    192.0.2.1 ns.example.com 
    #必须是外网地址，因为外部的客户端会查询，不能给一个内部地址。

（2）修改 /etc/bind/named.conf.options ，配置 

    options {
        directory "/var/cache/bind";
        recursion no;
        allow-transfer { none; };
    
        dnssec-validation auto;
        auth-nxdomain no;  
        listen-on-v6 { any; };
    };

recursion 配置为 no，因为权威 DNS 服务器只负责它自己的 zone 解析，不负责递归查询。假如不是主辅的 DNS 服务器，allow-transfer 配置为 none；

（3）配置 /etc/bind/named.conf.local 

    zone "example.com" {
        type master;
        file "/etc/bind/zones/db.example.com";
    };

配置的 zone 表示那些域名是这台 dns 服务器控制的，具体的信息来自于 /etc/bind/zones/db.example.com 文件。假如 named.conf.local 文件被破坏了，可以从 named.conf.default-zones 拷贝过来。

（4）配置 /etc/bind/zones/db.example.com 文件

这文件先初始化，可以从 /etc/bind/db.local 文件进行拷贝。

    $TTL    604800
    
    @       IN      SOA     ns.example.com. admin.example.com. (
                                  5         ; Serial
                             604800         ; Refresh
                              86400         ; Retry
                            2419200         ; Expire
                             604800 )       ; Negative Cache TTL
    ;
    
    ; Name servers
    example.com.    IN      NS      ns.example.com.
    
    ; A records for name servers
    ns             IN      A        139.129.23.162
    
    ; Other A records
    @               IN      A       139.129.23.162
    www             IN      A       139.129.23.162

（5）测试 zone 文件是否正确 

named-checkzone example.com /etc/bind/zones/db.example.com ，假如返回以下内容说明正确。

    zone example.com/IN: loaded serial 5
    OK

（7）使用该 DNS 

dig -t a @139.129.23.162 www.example.com 

也可以在你的域名解析商管理后台将 NS 地址修改为 ns.example.com（139.129.23.162 ）。

