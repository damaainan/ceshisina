## LNMP的运维追踪技巧总结

来源：[https://segmentfault.com/a/1190000015616555](https://segmentfault.com/a/1190000015616555)


## LNMP的运维追踪技巧总结

曾几何时我开始运维公司的LNMP网站，经过一段时间的摸爬滚打，也算是总结了不少在LNMP服务器下调试追踪各种网站错误的方法。好记性不如烂笔头，还是总结一下吧！

在开始我会梳理一下我所理解的一个web请求从发起到响应的各个阶段服务器和浏览器分别做了什么。所以的用户响应异常都是发生在这个流程中的，知道每个流程的细节可以通过不同的方法分别定位异常发生在哪个阶段，从而更准确快速的定位错误。后面就是持续更新的我在被这个网站折磨中经历的各种错误，给自己做一个记录，当然如果能帮到其他人，我也很荣幸。
## 一个Web请求过程中到底发生了什么？

![][0]

上图是一个简单的web请求全过程，嗯，画的确实有点过于简单，上图中我隐藏了很多细节，下面一一说明，可能有没涉及到的地方欢迎补充：
#### 第一步

用户输入url如[http:www.baidu.com到浏览器][1]，浏览器如chrom需要将其解析为ip地址才知道需要到哪里去访问哪个服务器。浏览器解析DNS步骤如下：


* 搜索浏览器自身的dns缓存，这个缓存缓存时间短，缓存数目有限。
* 搜索操作系统的dns缓存
* 读取host文件的dns映射(一般做本地开发映射都是修改这个文件来达到拦截浏览器请求到本地服务器的目的，从而使本地可以成功映射服务器地址)
* 先本地网卡配置里的dns服务器发起域名解析请求，这里好像还有一套运营商的处理流程就不在展开了。
* 下面好像还有一些流程，由于基本不会执行到这一步，一般所以dns运营商的dns服务器都会搞定的。
* 解析失败，以上任何一步成功都会返回一个成功的ip地址


#### 第二步

浏览器以一个随机的端口享这个ip地址的特定端口(默认80)发起著名的TCP3次握手。关于一个http请求是如何到达nginx服务的流程大致如下：

```
st=>start: TCP请求
en=>end: 异常
op=>operation: Nginx模块
cond1=>condition: 进入网卡？
cond2=>condition: 内核的TCP/IP协议栈？
cond3=>condition: 防火墙?

st->cond1
cond1(yes)->cond2
cond1(no)->en
cond2(yes)->cond3
cond2(no)->en
cond3(no)->en
cond3(yes)->op
```
#### 第三步

握手完成后的浏览器和服务器就可以愉快地发送http请求了，具体在nginx上流程如下：

```
st=>start: http请求
en=>end: response响应 
op1=>operation: 第二步流程 
op2=>operation: nginx进程 
op3=>operation: 获取http的头部信息 
op4=>operation: 匹配server_name，定位到站点的root 
op5=>operation: 进入代码框架的路由 
op6=>operation: 框架的路由解析器解析出php文件 
op7=>operation: php进入fastcgi进程 
op8=>operation: fastcgi进程将php填充成html文件 
op9=>operation: html文件递交给nginx并设置响应信息 

st->op1->op2->op3->op4->op5->op6->op7->op8->op9->en
```
#### 第四步

浏览器根据服务器resopnse的响应头和响应体渲染出可视化页面

| 响应码 | 说明 |
|-|-|
| 1xx | 信息性状态说明 |
| 2xx | 成功状态码 |
| 3xx | 重定向状态码 |
| 301 | 永久重定向, Location响应首部的值仍为当前URL，因此为隐藏重定向 |
| 302 | 临时重定向，显式重定向, Location响应首部的值为新的URL |
| 304 | Not Modified  未修改，比如本地缓存的资源文件和服务器上比较时，发现并没有修改，服务器返回一个304状态码,告诉浏览器，你不用请求该资源，直接使用本地的资源即可 |
| 4xx | 客户端错误 |
| 404 | Not Found  请求的URL资源并不存在 |
| 5xx | 服务器错误 |
| 500 | Internal Server Error  服务器内部错误 |
| 502 | Bad Gateway  前面代理服务器联系不到后端的服务器时出现 |
| 504 | Gateway Timeout  这个是代理能联系到后端的服务器，但是后端的服务器在规定的时间内没有给代理服务器响应 |



-----

未完分割线，后面我会总结一些各个阶段可能发生的错误，这些错误在客户端的表现，如何定位，以及如何解决。
[CSDN传送门][2]

[1]: http:www.baidu.com%E5%88%B0%E6%B5%8F%E8%A7%88%E5%99%A8
[2]: https://blog.csdn.net/csdnhyp/article/details/81017946
[0]: ../img/1460000015616558.png