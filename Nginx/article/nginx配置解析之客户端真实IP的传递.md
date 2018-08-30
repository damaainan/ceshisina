## nginx配置解析之客户端真实IP的传递

来源：[http://www.cnblogs.com/heioray/p/9530650.html](http://www.cnblogs.com/heioray/p/9530650.html)

时间 2018-08-24 17:48:00

 
前后端分离之后，采用nginx作为静态服务器，并通过反向代理的方式实现接口跨域的方式，在降低开发成本的同时也带来了诸多问题，例如客户端真实IP的获取。
 
  
在一些特殊场景下，比如风控和支付流程，往往需要获取用户的ip信息，但是nginx反向代理在实现跨域的同时，也彻底地改变了服务端请求来源，隔离了用户和服务端的连接，如下图

![][0]

 
用户访问前端页面' [https://a.test.com/index/html][1] '，调用支付接口的时候，支付接口的地址是' [https://a.test.com/goPay][2] '，然后由nginx反向代理到server端的' [https://b.test.com/goPay][3] '。这个时候对于server端来说，他接到的请求都是来自nginx服务器的，此时server 端默认获取到的ip则是nginx服务器的ip。这并不是我们想要的。这个时候就需要添加如下配置：

```
proxy_set_header X-Real-IP $remote_addr;
proxy_set_header X-Real-Port $remote_port;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
```
 
三个header分别表示：

```
X-Real-IP            客户端或上一级代理ip
X-Real-Port          客户端或上一级端口
X-Forwarded-For      包含了客户端和各级代理ip的完整ip链路
```
 
其中X-Real-IP是必需的，后两项选填。当只存在一级nginx代理的时候X-Real-IP和X-Forwarded-For是一致的，而当存在多级代理的时候，X-Forwarded-For 就变成了如下形式

```
X-Forwarded-For: 客户端ip， 一级代理ip， 二级代理ip...
```
 
在获取客户端ip的过程中虽然X-Forwarded-For是选填的，但是个人建议还是保留这，以便出现安全问题的时候，可以根据日志文件回溯来源。
 
### 有个坑～
 
除了上述配置部分网友还给了一个host的header

```
proxy_set_header Host $host;
```
 
首先这个header并不是必需的，其次这个header host和proxy_pass转发产生的hostheader会出现冲突，导致接口502的情况。但是这个配置更新后，nginx重启包括使用nginx -t进行测试也不会报错，这个值得大家注意一下。


[1]: https://a.test.com/index/html
[2]: https://a.test.com/goPay
[3]: https://b.test.com/goPay
[0]: ../img/bYbuIfE.png