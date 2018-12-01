## 用php入门网络编程

来源：[http://TIGERB.cn/2018/11/24/php-network-programming/](http://TIGERB.cn/2018/11/24/php-network-programming/)

时间 2018-11-24 21:54:11

 
随着工作年限的变长，干这行的紧迫感仍然和刚参加工作一样，毫无疑问作为一名服务端开发人员 **`网络编程`**  是我下一步需要攻破的地方之一。
 
## 学习思路 
 
以下是我对学习网络编程的一个简单的学习思路，之后我将会按照这个计划去逐步学习网络编程相关的知识。

```
step 1. 原生php实现TCP Server -> 原生php实现http协议 -> 掌握tcpdump的使用 -> 深刻理解tcp连接过程
step 2. 原生php实现多进程webserver
    2.1 引入I/O多路复用
    2.2 引入php协程(yield)
    2.3 对比 I/O多路复用版本 和 协程版本的性能差异

step 3. 实现简单的go web框架

step 4. php c扩展实现简单的webserver
```

 
为什么我会选择用php去学习网络编程？因为对于我来说，php算是最熟悉的，其次php相对来说简单些，同时php自身也有相应的函数支持。
 
我们今天先开始第一部分的学习。
 
step 1. 原生php实现TCP Server -> 原生php实现http协议 -> 掌握tcpdump的使用 -> 深刻理解tcp连接过程
 
## 正文 
 
我们先简单回顾下php作为后端语言的常见的交互方式过程：

```
client –(protocol:http)–> nginx –(protocol:fastcgi)–> php-fpm –(interface:sapi)–> php
```
 
在这里nginx充当的web server和反向代理server的角色，把http协议转换成了fastcgi协议。看到这里有些小伙伴可能会说了：“如果php自己直接处理http请求，不就可以不用nginx&php-fpm了么？”  遗憾的是原生php木有实现http协议(是吧，欢迎纠错)。
 
然后可能又有小伙伴说：“原生php不是支持tcp协议么？nginx把http请求代理成tcp协议不就可以不用php-fpm了吗。”  ，嗯，是的，没错。这位小伙伴的描述的交互过程如下：

```
client –(protocol:http)–> nginx –(protocol:tcp)–> php
```
 
这样看起来是没啥问题，很不错的想法，但是理论来说还是没有实现http协议，接收到的内容应该还是一坨字符串。我们马上来试一下：
 
#### step 1: 起一个nginx服务 
 
#### step 2: php简单实现一个TCP server，简单的代码如下 

```php
<?php

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($server, '127.0.0.1', '8889');
socket_listen($server);

while (true) {
    $client = socket_accept($server);
    if (! $client) {
        continue;
    }
    $request = socket_read($client, 1024);
    // 查看接收到的内容
    var_dump($request);
    socket_close($client);
}
```

 
#### step 3: nginx 反向代理http请求到 上面的tcp server, 配置如下 

```nginx
upstream tcp_server {
    ip_hash;
    server 127.0.0.1:8889 max_fails=3 fail_timeout=5;
}

server {
    listen       80;
    server_name  test.local;

    access_log  /tmp/logs/nginx/test.access.log  main;

    location / {
        proxy_set_header X-Forwarded-For $remote_addr;
        proxy_set_header Host            $http_host;
        proxy_pass http://tcp_server;
    }

}
```

 
最后我们访问下[http://test.local/?aaa=1/][2] 看下打印的结果和之前的推测一致:

```
string(127) "GET /?aaa=1 HTTP/1.0
X-Forwarded-For: 127.0.0.1
Host: test.local
Connection: close
User-Agent: curl/7.54.0
Accept: */*

"
```

 
所以我们就需要实现http协议，既然都实现了http协议，那就可以直接使用http作为web server了。

```
  client –(protocol:http)–> php
```
 
是吧！之后nginx的角色就是负载均衡，其实过分点你自己也可以用php做负载均衡。
 
### 原生php实现TCP Server 
 
接着我们看看如何用php创建一个简单的TCP Server过程如下：
 
![][0]
 
主要涉及的PHP函数如下：

```
socket_create

socket_listen

socket_accept

socket_recv || socket_read

socket_write

socket_close
```

 
代码：

```php
<?php

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($socket, '127.0.0.1', '8889');

socket_listen($socket);

while (true) {
    // accept
    $client = socket_accept($server);
    if (! $client) {
        continue;
    }
    $request = socket_read($client, 1024);
    socket_close($client);
    echo socket_strerror(socket_last_error($server)) . "\n";
}
```

 
命令行运行上述代码，然后用nc命令测试小tcp连接是否成功：

```
(tigerb) ➜  demo git:(master) ✗ nc -z -v 127.0.0.1 8889
found 0 associations
found 1 connections:
     1: flags=82<CONNECTED,PREFERRED>
        outif lo0
        src 127.0.0.1 port 60668
        dst 127.0.0.1 port 8889
        rank info not available
        TCP aux info available

Connection to 127.0.0.1 port 8889 [tcp/ddi-tcp-2] succeeded!
```

 
没毛病，TCP Server起来了。
 
### 原生php实现HTTP协议 
 
上面简单的TCP Server基本出来了，我们需要让php直接成为一个Web Server，想一想Web Server是基于HTTP协议的，HTTP协议又是基于TCP协议实现的。也就是说我们在上面的TCP Server基础上实现下HTTP协议即可。我们改进下流程图加入HTTP部分（橙黄色），如下
 
![][1]
 
实现HTTP协议的过程其实就是：

 
* 能读懂发来请求的信息 
* 能返回给浏览器等客户端它们能懂的信息 
 
 
协议无非就是双方协定好的规范，一样在HTTP/1.1中 请求&响应的格式基本如下
 
请求：

```
<HTTP Method> <url> <HTTP Version>
<KEY>:<VALUE>\r\n
...
\r\n
```

 
响应：

```
<HTTP Version> <HTTP Status> <HTTP Status Description>
<KEY>:<VALUE>\r\n
...
\r\n
```

 
所以简单来说，我们的php代码只要按照上面的规范 **`解析`**  和 **`返回`**  出对应的内容即可，简单的代码例子如下：

```php
/**
 * php实现简单的http协议
 */
class HttpProtocol
{
    /**
     * 原始请求字符串
     *
     * @var string
     */
    public  $originRequestContentString = '';

    /**
     * 原始请求字符串拆得的列表
     *
     * @var array
     */
    private $originRequestContentList = [];

    /**
     * 原始请求字符串拆得的键值对
     *
     * @var array
     */
    private $originRequestContentMap = [];

    /**
     * 定义响应头信息
     *
     * @var array
     */
    private $responseHead = [
        'http'         => 'HTTP/1.1 200 OK',
        'content-type' => 'Content-Type: text/html',
        'server'       => 'Server: php/0.0.1',
    ];

    /**
     * 定义响应体信息
     *
     * @var string
     */
    private $responseBody = '';

    /**
     * 响应内容
     *
     * @var string
     */
    public  $responseData = '';

    /**
     * 解析请求信息
     *
     * @param string $content
     * @return void
     */
    public function request($content = '')
    {
        if (empty($content)) {
            // exception
        
        }
        $this->originRequestContentList = explode("\r\n", $this->originRequestContentString);
        if (empty($this->originRequestContentList)) {
            // exception

        }
        foreach ($this->originRequestContentList as $k => $v) {
            if ($v === '') {
                // 过滤空
                continue;
            }
            if ($k === 0) {
                // 解析http method/request_uri/version
                list($http_method, $http_request_uri, $http_version) = explode(' ', $v);
                $this->originRequestContentMap['Method'] = $http_method;
                $this->originRequestContentMap['Request-Uri'] = $http_request_uri;
                $this->originRequestContentMap['Version'] = $http_version;
                continue;
            }
            list($key, $val) = explode(': ', $v);
            $this->originRequestContentMap[$key] = $val;
        }
    }
    
    /**
     * 组装响应内容
     *
     * @param [type] $responseBody
     * @return void
     */
    public function response($responseBody)
    {
        $count = count($this->responseHead);
        $finalHead = '';
        foreach ($this->responseHead as $v) {
            $finalHead .= $v . "\r\n";
        }
        $this->responseData = $finalHead . "\r\n" . $responseBody;
    }
}
```

 
我们在socket_read后面插入代码即可

```php
while (true) {
    // accept
    $client = socket_accept($server);
    if (! $client) {
        continue;
    }
    $request = socket_read($client, 1024);

    /**
     * HTTP 
     */
    $http = new HttpProtocol;
    $http->originRequestContentString = $request;
    $http->request($request);
    $http->response("Hello World");
    socket_write($client, $http->responseData);
    
    socket_close($client);
    echo socket_strerror(socket_last_error($server)) . "\n";
}
```

 
最后访问[http://127.0.0.1:8889/][3] 结果如下，或者浏览器打开页面即输出“Hello World”

```
(tigerb) ➜  demo git:(master) ✗ curl "http://127.0.0.1:8889/" -vv
*   Trying 127.0.0.1...
* TCP_NODELAY set
* Connected to 127.0.0.1 (127.0.0.1) port 8889 (#0)
> GET / HTTP/1.1
> Host: 127.0.0.1:8889
> User-Agent: curl/7.54.0
> Accept: */*
>
< HTTP/1.1 200 OK
< Content-Type: text/html
< Server: php/0.0.1
* no chunk, no close, no size. Assume close to signal end
<
* Closing connection 0
Hello World%
```


[2]: http://test.local/?aaa=1/
[3]: http://127.0.0.1:8889/
[0]: ./img/vQbAzeV.png
[1]: ./img/byu6Fjn.png