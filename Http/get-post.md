GET与POST的区别   
可以看看这篇文章 [浅谈HTTP中Get与Post的区别][0]。我个人认为主要的一点是：**URL不存在参数上限的问题，HTTP协议规范没有对URL长度进行限制。这个限制是特定的浏览器及服务器对它的限制。**  
关于URL和queryString长度限制的相关链接：

* [What is the maximum length of a URL in different browsers?][1]
* [What is the maximum possible length of a query string?][2]  
因此对于GET请求时，URL超出浏览器或者服务器限制的情况，建议改成POST请求。

301与302区别   
答：301是永久性重定向，搜索引擎在抓取新内容的同时也将旧的网址替换为重定向之后的网址。  
302是临时性重定向，搜索引擎会抓取新的内容而保留旧的网址。因为服务器返回302代码，搜索引擎认为新的网址只是暂时的。

为什么三次握手，二次不可以吗？   
答：不可以，只有完成3次才能进行后续操作，若在握手过程中某个阶段中断，TCP协议会再次以相同的顺序发送相同的数据包。而且，第三次握手是客户端为了让服务器知道它是否接收到响应，确保连接建立成功。

为什么有时候下载高清大图时，图片会一块一块地加载。   
答：这就是因为设置了http请求的长度，这样就可以分块的加载资源文件。  
在请求报文中使用Range属性，在响应报文中使用Content-Type属性都可以指定一定字节范围的http请求。


[0]: http://www.cnblogs.com/hyddd/archive/2009/03/31/1426026.html
[1]: http://stackoverflow.com/questions/417142/what-is-the-maximum-length-of-a-url-in-different-browsers/417184
[2]: http://stackoverflow.com/questions/812925/what-is-the-maximum-possible-length-of-a-query-string