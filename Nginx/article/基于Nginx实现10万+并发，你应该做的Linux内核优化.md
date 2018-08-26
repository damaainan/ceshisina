## 基于Nginx实现10万+并发，你应该做的Linux内核优化

阅读 1615，4月18日 发布，来源：[zhuanlan.zhihu.com][0]

由于默认的linux内核参数考虑的是最通用场景，这明显不符合用于支持高并发访问的Web服务器的定义，所以需要修改Linux内核参数，是的Nginx可以拥有更高的性能；  
  
在优化内核时，可以做的事情很多，不过，我们通常会根据业务特点来进行调整，当Nginx作为静态web内容服务器、反向代理或者提供压缩服务器的服务器时，期内核参数的调整都是不同的，这里针对最通用的、使Nginx支持更多并发请求的TCP网络参数做简单的配置；  
  
首先，你需要修改 `/etc/sysctl.conf` 来更改内核参数。  

```cfg
fs.file-max = 999999  
#表示单个进程最大可以打开的句柄数；  
  
net.ipv4.tcp_tw_reuse = 1  
#参数设置为 1 ，表示允许将TIME_WAIT状态的socket重新用于新的TCP链接，这对于服务器来说意义重大，因为总有大量TIME_WAIT状态的链接存在；  
  
ner.ipv4.tcp_keepalive_time = 600  
#当keepalive启动时，TCP发送keepalive消息的频度；默认是2小时，将其设置为10分钟，可以更快的清理无效链接。  
  
net.ipv4.tcp_fin_timeout = 30   
#当服务器主动关闭链接时，socket保持在FIN_WAIT_2状态的最大时间  
  
net.ipv4.tcp_max_tw_buckets = 5000  
#这个参数表示操作系统允许TIME_WAIT套接字数量的最大值，如果超过这个数字，TIME_WAIT套接字将立刻被清除并打印警告信息。  
#该参数默认为180000，过多的TIME_WAIT套接字会使Web服务器变慢。  
  
net.ipv4.ip_local_port_range = 1024 65000   
#定义UDP和TCP链接的本地端口的取值范围。  
  
net.ipv4.tcp_rmem = 10240 87380 12582912   
#定义了TCP接受缓存的最小值、默认值、最大值。  
  
net.ipv4.tcp_wmem = 10240 87380 12582912   
#定义TCP发送缓存的最小值、默认值、最大值。  
  
net.core.netdev_max_backlog = 8096   
#当网卡接收数据包的速度大于内核处理速度时，会有一个列队保存这些数据包。这个参数表示该列队的最大值。  
  
net.core.rmem_default = 6291456   
#表示内核套接字接受缓存区默认大小。  
  
net.core.wmem_default = 6291456   
#表示内核套接字发送缓存区默认大小。  
  
net.core.rmem_max = 12582912   
#表示内核套接字接受缓存区最大大小。  
  
net.core.wmem_max = 12582912   
#表示内核套接字发送缓存区最大大小。  
```

注意：以上的四个参数，需要根据业务逻辑和实际的硬件成本来综合考虑；  

```cfg
net.ipv4.tcp_syncookies = 1  
#与性能无关。用于解决TCP的SYN攻击。  
  
net.ipv4.tcp_max_syn_backlog = 8192  
#这个参数表示TCP三次握手建立阶段接受SYN请求列队的最大长度，默认1024，将其设置的大一些可以使出现Nginx繁忙来不及accept新连接的情况时，Linux不至于丢失客户端发起的链接请求。  
  
net.ipv4.tcp_tw_recycle = 1   
#这个参数用于设置启用timewait快速回收。  
  
net.core.somaxconn=262114   
# 选项默认值是128，这个参数用于调节系统同时发起的TCP连接数，在高并发的请求中，默认的值可能会导致链接超时或者重传，因此需要结合高并发请求数来调节此值。  
  
net.ipv4.tcp_max_orphans=262114   
#选项用于设定系统中最多有多少个TCP套接字不被关联到任何一个用户文件句柄上。如果超过这个数字，孤立链接将立即被复位并输出警告信息。这个限制指示为了防止简单的DOS攻击，不用过分依靠这个限制甚至认为的减小这个值，更多的情况是增加这个值。  
```

为了方便使用，下方不带注释的可以直接复制  

```cfg
fs.file-max = 999999  
net.ipv4.tcp_tw_reuse = 1  
ner.ipv4.tcp_keepalive_time = 600  
net.ipv4.tcp_fin_timeout = 30   
net.ipv4.tcp_max_tw_buckets = 5000  
net.ipv4.ip_local_port_range = 1024 65000   
net.ipv4.tcp_rmem = 10240 87380 12582912   
net.ipv4.tcp_wmem = 10240 87380 12582912   
net.core.netdev_max_backlog = 8096   
net.core.rmem_default = 6291456   
net.core.wmem_default = 6291456   
net.core.rmem_max = 12582912   
net.core.wmem_max = 12582912   
net.ipv4.tcp_syncookies = 1  
net.ipv4.tcp_max_syn_backlog = 8192  
net.ipv4.tcp_tw_recycle = 1   
net.core.somaxconn=262114   
net.ipv4.tcp_max_orphans=262114   
```

修改好配置文件，执行 `sysctl -p` 命令，使配置立即生效

[0]: https://segmentfault.com/r/1250000009100891?shareId=1210000009100892