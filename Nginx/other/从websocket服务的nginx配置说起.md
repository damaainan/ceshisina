## 从websocket服务的nginx配置说起

来源：[http://echizen.github.io/tech/2018/10-21-nginx-websocket](http://echizen.github.io/tech/2018/10-21-nginx-websocket)

时间 2018-10-21 16:08:52

 
之所以去了解了一波nginx，最直接的导火索是我的服务上线后，里面的websocket请求400了，降级成了轮询。网上一查就有解决方案，but 这些配置都是啥意思呢？怎么验证可用性？我该怎么去给我们的运维同事提工单配置？
 
现象：
 
![][0]
 
亲测解决方案，配置nginx：

```
proxy_http_version 1.1;
proxy_set_header Upgrade $http_upgrade;
proxy_set_header Connection "upgrade";
```
 
再来挖挖背后的故事
 
## websocket的连接有怎样的要求？
 
websocket需要请求头和响应头都设置`Upgrade: WebSocket`和`Connection: Upgrade`。
 
### Upgrade_header 和 Connection: Upgrade
 
来自wiki的解释：[https://en.wikipedia.org/wiki/HTTP/1.1_Upgrade_header][4]

```
The Upgrade header field is an HTTP header field introduced in HTTP/1.1. In the exchange, the client begins by making a cleartext request, which is later upgraded to a newer HTTP protocol version or switched to a different protocol.


```
 
WebSocket also uses this mechanism to set up a connection with a HTTP server in a compatible way. The WebSocket Protocol has two parts: a handshake to establish the upgraded connection, then the actual data transfer. First, a client requests a WebSocket connection by using the`Upgrade: WebSocket`and`Connection: Upgrade`headers, along with a few protocol-specific headers to establish the version being used and set up a handshake. The server, if it supports the protocol, replies with the same`Upgrade: WebSocket`and`Connection: Upgrade`headers and completes the handshake. Once the handshake is completed successfully, data transfer begins.
 
也就是说从`HTTP/1.1`开始在头信息里允许定义一个`Upgrade`字段，用来告诉服务端后续要使用新的http协议版本或者换一个协议，websocket就是从http协议发起要更换到ws协议的。所以需要添加这个字段。`Connection`表示后续连接状态，譬如我们熟悉的`keep-alive`，而`Connection: Upgrade`是告诉服务器后续要进行`Upgrade`，在websoket这里就是要切换协议。
 
#### HTTP Upgrade 机制
 
HTTP/1.1 引入了 Upgrade 机制，它使得客户端和服务端之间可以借助已有的 HTTP 语法升级到其它协议。详情见[RFC7230 6.7. Upgrade][5]
 
WebSocket 连接的建立是典型的 HTTP Upgrade 机制。以HTTP/1.1建立初次握手，在这之后，客户端和服务端之间就可以使用 WebSocket 协议进行双向数据通讯。
 
HTTP Upgrade 响应的状态码是 101。
 
## 为啥要在nginx里配置这2项
 
可能我们面临的情况是，本地用的好好的，发服务器上就不行了。那是因为出现这个400是走了代理的原因。`Connection`和`Upgrade`都是`hop-by-hop headers`,`hop-by-hop headers`只传输一层，默认不会被代理和缓存。

```
Connection: standard hop-by-hop headers (Keep-Alive, Transfer-Encoding, TE, Connection, Trailer, Upgrade, Proxy-Authorization and Proxy-Authenticate), any hop-by-hop headers used by the message must be listed in the Connection header, so that the first proxy knows it has to consume them and not forward them further. Standard hop-by-hop headers can be listed too


```
 
from:[https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Connection][6]

```
Hop-by-hop headers: These headers are meaningful only for a single transport-level connection and must not be retransmitted by proxies or cached. Such headers are: Connection, Keep-Alive, Proxy-Authenticate, Proxy-Authorization, TE, Trailer, Transfer-Encoding and Upgrade. Note that only hop-by-hop headers may be set using the Connection general header.


```
 
from:[https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers][7]
 
这2个头不会被带到代理服务器上，那么代理服务器就不知道这个升级连接为ws的信息，也就不能成功建立ws的连接了。所以nginx为了解决这个问题，为我们提供了`proxy_set_header`的配置，让我们把`Upgrade`和`Connection`的信息带到代理服务器上。

```
Since version 1.3.13, nginx implements special mode of operation that allows setting up a tunnel between a client and proxied server if the proxied server returned a response with the code 101 (Switching Protocols), and the client asked for a protocol switch via the “Upgrade” header in a request. As noted above, hop-by-hop headers including “Upgrade” and “Connection” are not passed from a client to proxied server, therefore in order for the proxied server to know about the client’s intention to switch a protocol to WebSocket, these headers have to be passed explicitly:


```
 
from :[http://nginx.org/en/docs/http/websocket.html][8]
 
## 验证
 
本地nginx配置一下代理先重现下400场景：

```nginx
server {
    listen 80;
    server_name local.skynet.meili-inc.com;
    index static/index.html;

    location / {
        proxy_pass http://127.0.0.1:8050;
        root /Users/echizen/repo/skynet;
    }
}
```
 
此时访问http://local.skynet.meili-inc.com下一个有ws请求的页面，我们看到请求头已经有`Upgrade: WebSocket`和`Connection: Upgrade`了。
 
![][1]
 
但是因为代理服务器没收到这个头信息，没有成功建立websocket连接，返回的`Connection: keep-alive`。
 
![][2]
 
现在我们配置上：

```nginx
location / {
    proxy_pass http://127.0.0.1:8050;
    root /Users/echizen/repo/skynet;

    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
}
```
 `nginx -s reload`重启后再访问，可以看到相应头已含有`Upgrade: WebSocket`和`Connection: Upgrade`，并且状态码也变成了`101 Switching Protocols`：
 
![][3]
 
多说一点关于101的：
 
101 Switching Protocols: 服务器已经理解了客户端的请求，并将通过Upgrade消息头通知客户端采用不同的协议来完成这个请求。在发送完这个响应最后的空行后，服务器将会切换到在Upgrade消息头中定义的那些协议。
 
## 总结
 
写的比较啰嗦，总结下。
 
解决方案：

```nginx
proxy_http_version 1.1;
proxy_set_header Upgrade $http_upgrade;
proxy_set_header Connection "upgrade";
```
 
原因： websocket需要设置头`Upgrade: WebSocket`和`Connection: Upgrade`。而这2个头信息都是hop-by-hop的头信息，不会被带到代理服务器上。需要在nginx层转发时再次设置 使用客户端http request中的upgrade字段来设置给被代理服务器的请求头中的upgrade字段。


[4]: https://en.wikipedia.org/wiki/HTTP/1.1_Upgrade_header
[5]: http://httpwg.org/specs/rfc7230.html#header.upgrade
[6]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Connection
[7]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
[8]: http://nginx.org/en/docs/http/websocket.html
[0]: https://img2.tuicool.com/3EvyQfn.png
[1]: https://img2.tuicool.com/RRJjeiq.png
[2]: https://img1.tuicool.com/rQR36ba.png
[3]: https://img2.tuicool.com/imAnmen.png