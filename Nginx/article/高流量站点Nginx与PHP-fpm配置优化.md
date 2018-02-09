## 【学习笔记】高流量站点Nginx与PHP-fpm配置优化

来源：[https://lnmp.ymanz.com/lnmp/289.html](https://lnmp.ymanz.com/lnmp/289.html)

时间 2018-02-03 22:32:03

 
使用Nginx搭配PHP已有7年的这份经历让我们学会如何为高流量站点优化NGINX和PHP-fpm配置。
 

![][0]
 
以下正是这方面的一些提示和建议：
 
### 1. 将TCP切换为UNIX域套接字
 
UNIX域套接字相比TCP套接字在loopback接口上能提供更好的性能（更少的数据拷贝和上下文切换）。
 
但有一点需要牢记：仅运行在同一台服务器上的程序可以访问UNIX域套接字（显然没有网络支持）。

```nginx
upstream backend
{
    # UNIX domain sockets
    server unix:/var/run/fastcgi.sock;

    # TCP sockets
    # server 127.0.0.1:8080;
}
```
 
### 2. 调整工作进程数
 
现代计算机硬件是多处理器的，Nginx可以利用多物理或虚拟处理器。
 
多数情况下，你的Web服务器都不会配置为处理多种任务（比如作为Web服务器提供服务的同时也是一个打印服务器），你可以配置Nginx使用所有可用的处理器，Nginx工作进程并不是多线程的。
 
运行以下命令可以获知你的机器有多少个处理器：
 
#### Linux上

```sh
cat /proc/cpuinfo | grep processor
```
 
#### FreeBSD上

```sh
sysctl dev .cpu | grep location
```
 
将`nginx.conf` 文件中`work_processes` 的值设置为机器的处理器核数。 
 
同时，增大`worker_connections` （每个处理器核心可以处理多少个连接）的值，以及将`multi_accept` 置为ON，如果你使用的是Linux，则也使用`epoll： 

```nginx
# We have 16 cores
worker_processes 16;

# connections per worker
events
{
    worker_connections 4096;
    multi_accept on;
}
```
 
### 3. 设置`upstream` 负载均衡 
 
以我们的经验来看，同一台机器上多个`upstream` 后端相比单个`upstream` 后端能够带来更高的吞吐量。 
 
例如，如果你想支持最大1000个PHP-fpm子进程（children），可以将该数字平均分配到两个upstream后端，各自处理500个PHP-fpm子进程：

```nginx
upstream backend {
    server unix:/var/run/php5-fpm.sock1 weight=100 max_fails=5 fail_timeout=5;
    server unix:/var/run/php5-fpm.sock2 weight=100 max_fails=5 fail_timeout=5;
}
```
 
#### 以下是两个来自php-fpm.conf的进程池：

```
<section name="pool">

    <value name="name">www1</value>
    <value name="listen_address">/var/run/php5-fpm.sock1</value>

    <value name="listen_options">
        <value name="backlog">-1</value>
        <value name="owner"></value>
        <value name="group"></value>
        <value name="mode">0666</value>
    </value>

    <value name="user">www</value>
    <value name="group">www</value>

    <value name="pm">
        <value name="style">static</value>
        <value name="max_children">500</value>
    </value>

    <value name="rlimit_files">50000</value>
    <value name="rlimit_core">0</value>
    <value name="request_slowlog_timeout">20s</value>
    <value name="slowlog">/var/log/php-slow.log</value>
    <value name="chroot"></value>
    <value name="chdir"></value>
    <value name="catch_workers_output">no</value>
    <value name="max_requests">5000</value>
    <value name="allowed_clients">127.0.0.1</value>

    <value name="environment">
        <value name="HOSTNAME">$HOSTNAME</value>
        <value name="PATH">/usr/local/bin:/usr/bin:/bin</value>
        <value name="TMP">/usr/tmp</value>
        <value name="TMPDIR">/usr/tmp</value>
        <value name="TEMP">/usr/tmp</value>
        <value name="OSTYPE">$OSTYPE</value>
        <value name="MACHTYPE">$MACHTYPE</value>
        <value name="MALLOC_CHECK_">2</value>
    </value>

</section>

<section name="pool">

    <value name="name">www2</value>
    <value name="listen_address">/var/run/php5-fpm.sock2</value>

    <value name="listen_options">
        <value name="backlog">-1</value>
        <value name="owner"></value>
        <value name="group"></value>
        <value name="mode">0666</value>
    </value>

    <value name="user">www</value>
    <value name="group">www</value>

    <value name="pm">
        <value name="style">static</value>
        <value name="max_children">500</value>
    </value>

    <value name="rlimit_files">50000</value>
    <value name="rlimit_core">0</value>
    <value name="request_slowlog_timeout">20s</value>
    <value name="slowlog">/var/log/php-slow.log</value>
    <value name="chroot"></value>
    <value name="chdir"></value>
    <value name="catch_workers_output">no</value>
    <value name="max_requests">5000</value>
    <value name="allowed_clients">127.0.0.1</value>

    <value name="environment">
        <value name="HOSTNAME">$HOSTNAME</value>
        <value name="PATH">/usr/local/bin:/usr/bin:/bin</value>
        <value name="TMP">/usr/tmp</value>
        <value name="TMPDIR">/usr/tmp</value>
        <value name="TEMP">/usr/tmp</value>
        <value name="OSTYPE">$OSTYPE</value>
        <value name="MACHTYPE">$MACHTYPE</value>
        <value name="MALLOC_CHECK_">2</value>
    </value>

</section>
```
 
### 4. 禁用访问日志文件
 
这一点影响较大，因为高流量站点上的日志文件涉及大量必须在所有线程之间同步的IO操作。

```nginx
access_log off;
log_not_found off;
error_log /var/log/nginx-error.log warn;
```
 
若你不能关闭访问日志文件，至少应该使用缓冲：

```nginx
access_log /var/log/nginx/access.log main buffer=16k;
```
 
### 5. 启用GZip

```nginx
gzip on;
gzip_disable "msie6";
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_min_length 1100;
gzip_buffers 16 8k;
gzip_http_version 1.1;
gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript;
```
 

![][1]
 
### 6. 缓存被频繁访问的文件相关的信息

```nginx
open_file_cache max=200000 inactive=20s;
open_file_cache_valid 30s;
open_file_cache_min_uses 2;
open_file_cache_errors on;
```
 
### 7. 调整客户端超时时间

```nginx
client_max_body_size 500M;
client_body_buffer_size 1m;
client_body_timeout 15;
client_header_timeout 15;
keepalive_timeout 2 2;
send_timeout 15;
sendfile on;
tcp_nopush on;
tcp_nodelay on;
```
 
### 8. 调整输出缓冲区大小

```nginx
fastcgi_buffers 256 16k;
fastcgi_buffer_size 128k;
fastcgi_connect_timeout 3s;
fastcgi_send_timeout 120s;
fastcgi_read_timeout 120s;
reset_timedout_connection on;
server_names_hash_bucket_size 100;
```
 
### 9. /etc/sysctl.conf调优

```
# Recycle Zombie connections
net.inet.tcp.fast_finwait2_recycle=1
net.inet.tcp.maxtcptw=200000

# Increase number of files
kern.maxfiles=65535
kern.maxfilesperproc=16384

# Increase page share factor per process
vm.pmap.pv_entry_max=54272521
vm.pmap.shpgperproc=20000

# Increase number of connections
vfs.vmiodirenable=1
kern.ipc.somaxconn=3240000
net.inet.tcp.rfc1323=1
net.inet.tcp.delayed_ack=0
net.inet.tcp.restrict_rst=1
kern.ipc.maxsockbuf=2097152
kern.ipc.shmmax=268435456

# Host cache
net.inet.tcp.hostcache.hashsize=4096
net.inet.tcp.hostcache.cachelimit=131072
net.inet.tcp.hostcache.bucketlimit=120

# Increase number of ports
net.inet.ip.portrange.first=2000
net.inet.ip.portrange.last=100000
net.inet.ip.portrange.hifirst=2000
net.inet.ip.portrange.hilast=100000
kern.ipc.semvmx=131068

# Disable Ping-flood attacks
net.inet.tcp.msl=2000
net.inet.icmp.bmcastecho=1
net.inet.icmp.icmplim=1
net.inet.tcp.blackhole=2
net.inet.udp.blackhole=1
```
 
### 10. 监控
 
持续监控打开连接的数目，空闲内存以及等待状态线程的数目。
 
设置警报在超出阈值时通知你。你可以自己构建这些警报，或者使用类似ServerDensity的东西。
 
确认安装了NGINX的stub_status模块。该模块默认并不会编译进NGINX，所以可能你需要重新编译NGINX -

```nginx
./configure --with-http_ssl_module --with-http_stub_status_module --without-mail_pop3_module
--without-mail_imap_module --without-mail_smtp_module
make install BATCH=yes
```
 
原文： [Optimizing NGINX and PHP-fpm for high traffic sites][2] 
 
译者： [youngsterxyf][3] 
 


[2]: http://www.softwareprojects.com/resources/programming/t-optimizing-nginx-and-php-fpm-for-high-traffic-sites-2081.html
[3]: https://github.com/youngsterxyf/
[0]: https://img0.tuicool.com/viI3Mba.png!web
[1]: https://img1.tuicool.com/QZRfumR.jpg!web