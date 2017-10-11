# [轻松配置httpd的虚拟主机][0]

httpd使用VirtualHost指令进行虚拟主机的定义。支持三种虚拟主机：基于ip，基于端口和基于名称。其中基于端口的虚拟主机在httpd的术语上(例如官方手册)也属于基于IP的。

**当一个请求到达时，将首先匹配虚拟主机。匹配虚拟主机的规则为最佳匹配法。所谓最佳，是指通配的越少，匹配的优先级越高。例如"192.168.100.14:80"的优先级高于"*:80"。如果基于名称的虚拟主机无法匹配上，则采用虚拟主机列表中的第一个虚拟主机作为响应主机。如果所有虚拟主机都无法匹配上，则采用从主配置段落中的主机，如果主配置段落中注释了DocumentRoot，则返回对应的错误。**

主配置段落的指令基本上都能使用在虚拟主机容器中。至于虚拟主机中必须配有什么指令，这没有规定，因为虚拟主机只是封装一组指令而已，即使其中没有任何指令，它也会从主配置段落中继承。但是，既然要使用且已经使用了虚拟主机，按照常理来说，至少得提供不同的ServerName，DocumentRoot等指令以让它们各自独立。

最后需要说明的是，httpd的"-S"选项在调试虚拟主机配置选项时非常有用。

# 1 基于IP的虚拟主机

基于IP的虚拟主机是在不同的IP+PORT上提供不同的站点服务，最常见的是在不同端口上提供不同站点。

如果仅基于IP，即使用不同IP地址，那么要求操作系统上有两个或更多IP地址，可以提供多个网卡，或者通过网卡别名来实现。

如果基于端口，即使用不同端口，则使用相同IP或不同IP均可，但在httpd术语中，基于单个IP但不同端口的虚拟主机，也是基于IP的虚拟主机。

假设本机为192.168.100.14。

    # 首先设置个虚拟网卡。
    shell> ip a add 192.168.100.144 dev eth0 label eth0:0
    
    # 添加基于IP地址的虚拟主机，DocumentRoot使用的相对路径，基于ServerRoot
    shell> vim /etc/apache/extra/vhosts.conf

```apache
    <VirtualHost 192.168.100.14:80>
        ServerName www.a.com
        DocumentRoot htdocs/a.com
    </VirtaulHost>
    
    <VirtualHost 192.168.100.144:80>
        ServerName www.b.com
        DocumentRoot htdocs/b.com
    </VirtaulHost>
```

在主配置文件中，将该虚拟主机配置文件vhosts.conf包含进去。

```apache
    include /etc/apache/extra/vhosts.conf
```

再提供DocumentRoot和各自的index.html。

```
    mkdir /usr/local/apache/htdocs/{a.com,b.com}
    echo '<h1>a.com<h1>' >/usr/local/apache/htdocs/a.com/index.html
    echo '<h1>b.com<h1>' >/usr/local/apache/htdocs/b.com/index.html
```

使用httpd -S查看配置文件加载过程。

```
    [root@xuexi httpd-2.4.27]# httpd -S -f /etc/apache/httpd.conf 
    VirtualHost configuration:
    192.168.100.14:80      www.a.com (/etc/apache/extra/vhosts.conf:23)
    192.168.100.144:80     www.b.com (/etc/apache/extra/vhosts.conf:28)
    ServerRoot: "/usr/local/apache"
    Main DocumentRoot: "/usr/local/apache/htdocs"
    Main ErrorLog: "/usr/local/apache/logs/error_log"
    Mutex proxy: using_defaults
    Mutex default: dir="/usr/local/apache/logs/" mechanism=default 
    PidFile: "/usr/local/apache/logs/httpd.pid"
    Define: DUMP_VHOSTS
    Define: DUMP_RUN_CFG
    User: name="daemon" id=2
    Group: name="daemon" id=2
```

重启httpd。

```
    service httpd restart
```

测试。

# 2 基于端口的虚拟主机

基于端口的虚拟主机需要监听两个套接字。

首先在配置文件中使用Listen指令修改监听套接字，这里假设只基于端口，所以只需修改端口号即可。

```apache
    listen 80
    listen 8080
```

修改虚拟主机配置文件vhosts.conf文件如下：

    shell> vim /etc/apache/extra/vhosts.conf

```apache
    <VirtualHost 192.168.100.14:80>
        ServerName www.a.com
        DocumentRoot htdocs/a.com
    </VirtaulHost>
    
    <VirtualHost 192.168.100.14:8080>
        ServerName www.b.com
        DocumentRoot htdocs/b.com
    </VirtaulHost>
```

重启httpd。测试www.a.com和www.b.com能否显示。

# 3 基于名称的虚拟主机

请求报文中获取资源时包含了两部分资源定位的格式：TCP/IP协议和HTTP协议，虽然TCP/IP部分相同，但是HTTP协议的请求报文中指定了HOST，这就是基于域名的虚拟主机能实现的原因。也因此，基于名称的虚拟主机必须指定ServerName指令，否则它将会继承操作系统的FQDN。

    shell> vim /etc/apache/extra/vhosts.conf

```apache
    <VirtualHost 192.168.100.14:80>
        ServerName www.a.com
        DocumentRoot htdocs/a.com
    </VirtaulHost>
    
    <VirtualHost 192.168.100.14:80>
        ServerName www.b.com
        DocumentRoot htdocs/b.com
    </VirtaulHost>
```

注意，对于基于名称的虚拟主机，当使用IP地址请求(例如浏览器中输入的是IP地址)，或者无法匹配到任何虚拟主机时，将采用第一个虚拟主机作为默认虚拟主机。

例如，当某个hosts文件中添加了"192.168.100.14 www.c.com"时，即使在配置文件中并没有配置www.c.com的虚拟主机，但访问时仍然会访问虚拟主机列表的第一个。

[0]: http://www.cnblogs.com/f-ck-need-u/p/7632878.html