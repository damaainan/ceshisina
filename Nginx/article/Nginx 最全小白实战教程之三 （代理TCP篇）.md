## Nginx 最全小白实战教程之三 （代理TCP篇）

来源：[https://segmentfault.com/a/1190000014035942](https://segmentfault.com/a/1190000014035942)

Nginx代理TCP主要是使用stream模块，这个功能是从1.9.0版本开始的。
我用它来代理Mysql。

### 一、配置代码

```nginx
stream {
    upstream mysqls {
        hash $remote_addr consistent;
        server 192.168.58.143:3306 weight=5 max_fails=3 fail_timeout=30s;
        server 192.168.58.142:3306 weight=1 max_fails=3 fail_timeout=30s;
    }
    server {
        listen 9945;
        proxy_connect_timeout 1s;
        proxy_timeout 3s;
        proxy_pass mysqls;
    }
}
```

这个就是一个最基本的配置

有几个注意的地方：


* `stream`的配置必须是和`events`同级的，所以我直接就写在了`nginx.conf`主配置文件中的，这样就确保了和`events`同级。当然也可以单独写出来。
* 这里的`server`里面是不准写`location`的，所以就不能判断`\sss\`这样的路径来做的。我开始的时候想这样的，模仿`spring`实现的`websocket`，后来才意识到，`websocket`可以这样是因为它连接是靠`http`协议的，传输才靠`tcp`的。终于明白了。
* `server`的监听端口不能和`http`的重复。
* 重新加载nginx时候可能会出现错误：`[emerg] 30181#0: bind() to 0.0.0.0:8090 failed (13: Permission denied)`这个错误是由于`SElinux`，关掉这就好了。
* 关闭`SElinux`方法：修改`/etc/selinux/config`文件，将`SELINUX=enforcing`改为`SELINUX=disabled`重启之后就好了。


### 二、用法

```
语法:    listen address:port [ssl] [udp] [proxy_protocol] [backlog=number] [bind] [ipv6only=on|off] [reuseport] [so_keepalive=on|off|[keepidle]:[keepintvl]:[keepcnt]];
默认值: —
上下文: server
```

设置方式可以是下面任意一种：

```nginx
listen 127.0.0.1:12345;
listen *:12345;
listen 12345;     # same as *:12345
listen localhost:12345;
```

IPV6必须加上中括号：

```nginx
listen [::1]:12345;
listen [::]:12345;
```

UNIX-domain sockets要写`unix:`前缀

```nginx
listen unix:/var/run/nginx.sock;
```
* `ssl`
指定连接此端口的连接都是`SSL模式``udp`
用于处理套接字
* `proxy_protocol`
指定此端口上的所有连接都使用 [PROXY protocol][0]协议
* `backlog=number`
限制挂起连接队列的最大长度（1.9.2）。默认情况下，backlog在FreeBSD，DragonFly BSD和Mac OS X上设置为-1，在其他平台上设置为511。
* `bind`
表示对一个指定的`address:port`对进行单独的绑定。实是，如果有几个listen指令具有相同的端口但地址不同，并且其中一个listen指令监听给定端口`（\*：port）`的所有地址，nginx将只绑定`\*：port`。 应该注意，在这种情况下调用getsockname（）以确定接受连接的地址。 如果使用ipv6only或so_keepalive参数，那么对于给定的地址：端口对将始终进行单独的绑定。
* `ipv6only=on|off`
确定侦听通配符地址[::]的IPv6套接字是否只接受IPv6连接，或者是接受IPv6和IPv4连接。 此参数默认处于打开状态。 且它只能在启动时设置一次。
* `so_keepalive=on|off|[keepidle]:[keepintvl]:[keepcnt]`
此参数配置侦听套接字的`“TCP keepalive”`行为。 如果省略此参数，则操作系统的设置将对套接字生效。 如果将其设置为值“on”，则套接字的SO_KEEPALIVE选项将打开。 如果它设置为值“off”，则套接字的SO_KEEPALIVE选项被关闭。 某些操作系统支持使用TCP_KEEPIDLE，TCP_KEEPINTVL和TCP_KEEPCNT套接字选项在每个套接字上设置TCP保持活动参数。 在这些系统（目前，Linux 2.4+，NetBSD 5+和FreeBSD 9.0-STABLE）上，可以使用keepidle，keepintvl和keepcnt参数配置它们。 可以省略一个或两个参数，在这种情况下，相应套接字选项的系统默认设置将有效。 例如，

```ini
so_keepalive=30m::10
```
### 三、相关指令

1.指定[preread][1] buffer的大小

```
Syntax:    preread_buffer_size size;
Default:    
preread_buffer_size 16k;
Context:    stream, server
```

2.指定[preread][1] buffer的超时时间

```
Syntax:    preread_timeout timeout;
Default:    
preread_timeout 30s;
Context:    stream, server
```

3.指定完成读取代理协议头的超时时间，如果超过这个时间，就关闭连接

```
Syntax:    proxy_protocol_timeout timeout;
Default:    
proxy_protocol_timeout 30s;
Context:    stream, server
```

4.配置用于将upstream servers中名称解析到地址的服务器

```
Syntax:    resolver address ... [valid=time] [ipv6=on|off];
Default:    —
Context:    stream, server
This directive appeared in version 1.11.3.
```

例如：

```nginx
resolver 127.0.0.1 [::1]:5353;
resolver 127.0.0.1 [::1]:5353 valid=30s;
```

5.名字解析的超时时间

```
Syntax:    resolver_timeout time;
Default:    
resolver_timeout 30s;
Context:    stream, server
```

6.配置服务器

```
Syntax:    server { ... }
Default:    —
Context:    stream
```

7.配置stream服务器

```
Syntax:    stream { ... }
Default:    —
Context:    main
```

8.配置是否允许`TCP_NODELAY`选项，这个可以用在客户端和代理服务器上

```
Syntax:    tcp_nodelay on | off;
Default:    
tcp_nodelay on;
Context:    stream, server
```

9.设置变量的哈希表容量。

```
Syntax:    variables_hash_bucket_size size;
Default:    
variables_hash_bucket_size 64;
Context:    stream
```

10.设置变量的哈希表最大容量

```
Syntax:    variables_hash_max_size size;
Default:    
variables_hash_max_size 1024;
Context:    stream
This directive appeared in version 1.11.2.
```
### 四、相关变量


* `$binary_remote_addr`：二进制形式的客户端地址，对于IPv4地址，值的长度始终为4字节，对于IPv6地址，值的长度始终为16字节
* `$bytes_received`：从客户端接收到的字节数
* `$bytes_sent`：发送到客户端的字节数
* `$connection`：连接序列号
* `$hostname`：host名称
* `$msec`：当前时间(秒)，以毫秒为单位
* `$nginx_version`：nginx版本
* `$pid`：work process的pid
* `$protocol`：和客户端通信的协议：TCP或者UDP
* `$proxy_protocol_addr`：PROXY协议头中的客户端地址，或者为空字符串。 **`必须先通过在listen指令中设置proxy_protocol参数才能启用PROXY协议。`** 
* `$proxy_protocol_port`：PROXY协议头中的客户端端口，或者为空字符串。 **`必须先通过在listen指令中设置proxy_protocol参数才能启用PROXY协议。`** 
* `$remote_addr`：客户端地址
* `$server_addr`：接收连接的服务器地址。 **`计算此变量的值通常需要一次系统调用。 为了避免系统调用，listen指令必须指定地址并使用bind参数。`** 
* `$server_port`：接收连接的服务器端口
* `$session_time`：会话持续时间（秒），以毫秒为单位
* `$time_iso8601`：ISO8610格式的本地时间
* `$time_local`：通用日志格式的本地时间
* `status`：状态值。200：会话完成；400：客户端数据无法解析，例如PROXY协议头；403：访问受限；500：内部服务器错误；502：网关错误；503：服务不可用。


[0]: http://www.haproxy.org/download/1.5/doc/proxy-protocol.txt
[1]: https://nginx.org/en/docs/stream/stream_processing.html#preread_phase
[2]: https://nginx.org/en/docs/stream/stream_processing.html#preread_phase