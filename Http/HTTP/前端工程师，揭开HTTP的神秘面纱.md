## 前端工程师，揭开HTTP的神秘面纱

来源：[http://finget.github.io/2018/07/03/http/](http://finget.github.io/2018/07/03/http/)

时间 2018-07-03 17:25:39

![][0]
 
### 五层协议 
 
五层协议只是OSI和TCP/IP的综合，实际应用还是TCP/IP的四层结构。
 
![][1]
 
### TCP/IP 协议 
 
TCP(Transmission Control Protocol)传输控制协议
 
TCP/IP协议将应用层、表示层、会话层合并为应用层，物理层和数据链路层合并为网络接口层
 
![][2]
 
### 三种模型结构 
 
![][3]
 
![][4]
 
### 各层的作用 
 
![][5]

* 物理层：
主要定义物理设备标准，如网线的接口类型、光纤的接口类型、各种传输介质的传输速率等。它的主要作用是传输比特流（就是由1、0转化为电流强弱来进行传输,到达目的地后在转化为1、0，也就是我们常说的数模转换与模数转换）。这一层的数据叫做比特。

* 数据链路层：
定义了如何让格式化数据以进行传输，以及如何让控制对物理介质的访问。这一层通常还提供错误检测和纠正，以确保数据的可靠传输。

* 网络层：
在位于不同地理位置的网络中的两个主机系统之间提供连接和路径选择。Internet的发展使得从世界各站点访问信息的用户数大大增加，而网络层正是管理这种连接的层。

* 传输层：
定义了一些传输数据的协议和端口号（WWW端口80等），如：
TCP（transmission control protocol –传输控制协议，传输效率低，可靠性强，用于传输可靠性要求高，数据量大的数据）
UDP（user datagram protocol–用户数据报协议，与TCP特性恰恰相反，用于传输可靠性要求不高，数据量小的数据，如QQ聊天数据就是通过这种方式传输的）。 主要是将从下层接收的数据进行分段和传输，到达目的地址后再进行重组。常常把这一层数据叫做段。

* 会话层：
通过运输层（端口号：传输端口与接收端口）建立数据传输的通路。主要在你的系统之间发起会话或者接受会话请求（设备之间需要互相认识可以是IP也可以是MAC或者是主机名）

* 表示层：
可确保一个系统的应用层所发送的信息可以被另一个系统的应用层读取。例如，PC程序与另一台计算机进行通信，其中一台计算机使用扩展二一十进制交换码（EBCDIC），而另一台则使用美国信息交换标准码（ASCII）来表示相同的字符。如有必要，表示层会通过使用一种通格式来实现多种数据格式之间的转换。

* 应用层：
是最靠近用户的OSI层。这一层为用户的应用程序（例如电子邮件、文件传输和终端仿真）提供网络服务。

## HTTP 发展历史 
 
### HTTP/0.9 

* 只有一个命令GET  
* 响应类型: 仅 超文本  
* 没有header等描述数据的信息  
* 服务器发送完毕，就关闭TCP连接  

### HTTP/1.0 

* 增加了很多命令（post HESD ）  
* 增加status code 和 header  
* 多字符集支持、多部分发送、权限、缓存等  
* 响应：不再只限于超文本 (Content-Type 头部提供了传输 HTML 之外文件的能力 — 如脚本、样式或媒体文件)  

### HTTP/1.1 

* 持久连接。TCP三次握手会在任何连接被建立之前发生一次。最终，当发送了所有数据之后，服务器发送一个消息，表示不会再有更多数据向客户端发送了；则客户端才会关闭连接（断开 TCP）  
* 支持的方法:`GET`,`HEAD`,`POST`,`PUT`,`DELETE`,`TRACE`,`OPTIONS` 
* 进行了重大的性能优化和特性增强，分块传输、压缩/解压、内容缓存磋商、虚拟主机（有单个IP地址的主机具有多个域名）、更快的响应，以及通过增加缓存节省了更多的带宽  

### HTTP2 

* 所有数据以二进制传输。HTTP1.x是基于文本的，无法保证健壮性，HTTP2.0绝对使用新的二进制格式，方便且健壮  
* 同一个连接里面发送多个请求不再需要按照顺序来  
* 头信息压缩以及 **`推送`**  等提高效率的功能  

## 三次握手 
 
客服端和服务端在进行http请求和返回的工程中，需要创建一个`TCP connection`（由客户端发起）,http不存在连接这个概念，它只有请求和响应。请求和响应都是数据包，它们之间的传输通道就是`TCP connection`。
 
![][6]
 
位码即tcp标志位，有6种标示：SYN(synchronous建立联机) ACK(acknowledgement 确认) PSH(push传送) FIN(finish结束) RST(reset重置) URG(urgent紧急)Sequence number(顺序号码) Acknowledge number(确认号码)
 
第一次握手：主机A发送位码为syn＝1，随机产生seq number=1234567的数据包到服务器，主机B由SYN=1知道，A要求建立联机；
 
第二次握手：主机B收到请求后要确认联机信息，向A发送ack number=(主机A的seq+1)，syn=1，ack=1，随机产生seq=7654321的包；
 
第三次握手：主机A收到后检查ack number是否正确，即第一次发送的seq number+1，以及位码ack是否为1，若正确，主机A会再发送ack number=(主机B的seq+1)，ack=1，主机B收到后确认seq值与ack=1则连接建立成功
 
## URI、URL、URN 
 
URI: Uniform Resource Identifier/统一资源标识符
 
URL: Uniform Resource Locator/统一资源定位器
 
URN: Uniform Resource Name/永久统一资源定位符
 
web上的各种资源（html、图片、视频、音频等）都由一个URI标识定位。URI相当于它们的详细“家庭住址”。
 
URI包含了URL和URN。
 
![][7]
 
URL是URI的一种，不仅标识了Web 资源，还指定了操作或者获取方式，同时指出了主要访问机制和网络位置。
 
URN是URI的一种，用特定命名空间的名字标识资源。使用URN可以在不知道其网络位置及访问方式的情况下讨论资源。
 
网上的一个例子：

```
// 这是一个URI
http://bitpoetry.io/posts/hello.html#intro

// 资源访问方式
http://

// 资源存储位置
bitpoetry.io/posts/hello.html

#intro // 资源

// URL
http://bitpoetry.io/posts/hello.html

// URN
bitpoetry.io/posts/hello.html#intro
```

## HTTP报文 

请求报文：

![][8]

响应报文：

![][9]

## HTTP 各种特性 
 
### curl 
 
curl命令是一个利用URL规则在命令行下工作的文件传输工具。它支持文件的上传和下载，所以是综合传输工具，但按传统，习惯称curl为下载工具。作为一款强力工具，curl支持包括HTTP、HTTPS、ftp等众多协议，还支持POST、cookies、认证、从指定偏移处下载部分文件、用户代理字符串、限速、文件大小、进度条等特征。做网页处理流程和数据检索自动化，curl可以祝一臂之力。
 
[更详细的CURL，点这里。][23]

curl 访问`baidu.com`:

![][10]

返回的内容中，html部分只有一个meta标签，`<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">`，这是因为我们访问的是`baidu.com`，在浏览器中，浏览器会自动解析这个meta标签并重定向到`http://www.baidu.com/`，然而命令行中并没有解析的功能。

curl 访问`www.baidu.com`:

![][11]

#### curl常用命令 
 `-v`显示详细的请求信息
 
![][12]
 `-X`指定请求方式

```
curl -X GET www.xxxx.com/xx/xx?xx=123

curl -X POST www.xxxx.com/xx/xx?xx=123
```

 `-o / -O`保存下载的文件

```
// 将文件下载到本地并命名为mygettext.html
curl -o mygettext.html http://www.gnu.org/software/gettext/manual/gettext.html

// 将文件保存到本地并命名为gettext.html
curl -O http://www.gnu.org/software/gettext/manual/gettext.html
```

### CORS跨域请求的限制与解决 

```js
// server1.js
const http = require('http')
const fs = require('fs')

http.createServer(function (request, response) {
  console.log('request come', request.url)

  const html = fs.readFileSync('test.html', 'utf8')
  response.writeHead(200, {
    'Content-Type': 'text/html'
  })
  response.end(html)
}).listen(8888)

console.log('server listening on 8888')
```

```js
// server2.js
const http = require('http')

http.createServer(function (request, response) {
  console.log('request come', request.url)

  response.end('123')
}).listen(8887)

console.log('server listening on 8887')
```

```html
// test.html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>

</body>
<script>
  fetch('http://127.0.0.1:8887');
</script>
</html>
```

![][13]
 
处理方法：
 
1.服务器端处理

```js
// server2.js 服务器端设置允许跨域
response.writeHead(200, {
  'Access-Control-Allow-Origin': '*' // * 表示任何域名下都可以访问这个服务,也可以指定域名
})
```

2.jsonp

```js
// test.html
<script src="http://127.0.0.1:8887"></script>
```

就算存在跨域，请求还是会发送，响应也会返回，只是浏览器端发现了存在跨域问题就将返回内容屏蔽了，并报错提示。
 
### CORS 预请求 

```html
// test.html
<script>
  fetch('http://127.0.0.1:8887',{
    method: 'post',
    headers: {
      'X-Test-Cors': '123'
    }
  });
</script>
```

![][14]
 
我们设置的请求头中`X-Test-Cors`在跨域请求的时候，不被允许。

虽然不允许跨域，但是请求仍然会发送，并返回成功。

![][15]

默认允许的请求方法：

* GET 
* HEAD 
* POST 

其他的方法(PUT、DELETE)都需要预请求验证的。
 
默认允许的`Content-Type`:

* text/plain 
* multipart/form-data 
* application/x-www-form-urlencoded 

怎样设置允许我们设置的请求头：

```js
// server2.js 
response.writeHead(200, {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'X-Test-Cors' // 加上这个设置
})
```

![][16]
 
首先发送一个预请求，预请求就是告诉浏览器接下来要发送的post请求是被允许的。
 
设置允许的请求方法：

```js
// server2.js 
response.writeHead(200, {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'X-Test-Cors',
  'Access-Control-Allow-Methods': 'POST, PUT, DELETE'
})
```

设置一个安全时间：

```js
// server2.js 
response.writeHead(200, {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'X-Test-Cors',
  'Access-Control-Allow-Methods': 'POST, PUT, DELETE',
  'Access-Control-Max-Age': '1000'
})
```

 `Access-Control-Max-Age`的单位是秒，意思就是在多少秒以内，我们设置的这些允许的请求头，请求方法，是不需要发送预请求验证的，直接就可以通过，并发送。
 
### 缓存Cache-Control 

常用值：

![][17]

```js
// server.js
const http = require('http')
const fs = require('fs')

http.createServer(function (request, response) {
  console.log('request come', request.url)

  if (request.url === '/') {
    const html = fs.readFileSync('test.html', 'utf8')
    response.writeHead(200, {
      'Content-Type': 'text/html'
    })
    response.end(html)
  }

  if (request.url === '/script.js') {
    response.writeHead(200, {
      'Content-Type': 'text/javascript',
      'Cache-Control': 'max-age=20,public' // 缓存20s 多个值用逗号分开
    })
    response.end('console.log("script loaded")')
  }
}).listen(8888)

console.log('server listening on 8888')
```

```html
// test.html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>

</body>
<script src="/script.js"></script>
</html>
```

![][18]

刷新会发现`script.js`是从缓存中获取的，请求时间也是0。

![][19]

我们希望浏览器缓存我们的图片，文件、js代码，但是服务器端代码更新了，浏览器端还是在缓存中获取的旧的文件。这就诞生了，webpack打包中出现的文件名后加上hash值，当文件改变时hash值也改变，这样浏览器就会发送新的请求到服务器端。
 
#### 缓存验证 
 
![][20]
 
验证头：

* Last-Modified 

上次修改时间
 
配合If-Modified-Since或者If-Unmodified-Since使用
 
对比上次修改时间以验证资源是否需要更新

* Etag 

数据签名(内容修改，签名就会改变)
 
配合If-Match或者If-Non-Match使用
 
对比资源的签名判断是否使用缓存
 
### Redirect 

```js
const http = require('http')

http.createServer(function (request, response) {
  console.log('request come', request.url)

  if (request.url === '/') {
    response.writeHead(302, {  // or 301
      'Location': '/new'
    })
    response.end()
  }
  if (request.url === '/new') {
    response.writeHead(200, {
      'Content-Type': 'text/html',
    })
    response.end('this is content
')
  }
}).listen(8888)

console.log('server listening on 8888')
```

![][21]
 
302临时跳转，301永久跳转，301从缓存种获取跳转，使用301之后，主动权就掌握在用户手里，如果用户不清理缓存，那就算服务器端改变了也没用。
 
![][22]

[23]: http://man.linuxde.net/curl
[0]: ./img/nyIFn2f.gif 
[1]: ./img/ANvay2f.png 
[2]: ./img/Jv6Z7zu.png 
[3]: ./img/muMfQrb.png 
[4]: ./img/EJNbYfR.png 
[5]: ./img/q2IVveF.png 
[6]: ./img/Nn2iuij.png 
[7]: ./img/b63eYjF.jpg 
[8]: ./img/2u6RviE.jpg 
[9]: ./img/z2euA3v.jpg 
[10]: ./img/eaaQZne.png 
[11]: ./img/nMraQ3f.png 
[12]: ./img/bau2aym.png 
[13]: ./img/UjYN7r2.png 
[14]: ./img/UZvuqeq.png 
[15]: ./img/ma2q2uB.png 
[16]: ./img/fy6Zfuj.png 
[17]: ./img/6z6Rva7.png 
[18]: ./img/32aQreA.png 
[19]: ./img/baqiArV.png 
[20]: ./img/6jEZve2.png 
[21]: ./img/RVRfyqz.png 
[22]: ./img/aYV77zb.png 