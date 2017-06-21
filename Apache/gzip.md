 gzip是GNUzip的缩写，最早用于UNIX系统的文件压缩。HTTP协议上的gzip编码是一种用来改进web应用程序性能的技术，web服务器和客户端（浏览器）必须共同支持gzip。目前主流的浏览器，Chrome,firefox,IE等都支持该协议。常见的服务器如Apache，Nginx，IIS同样支持gzip。gzip压缩比率在3到10倍左右，可以大大节省服务器的网络带宽。而在实际应用中，并不是对所有文件进行压缩，通常只是压缩静态文件。 那么客户端和服务器之间是如何通信来支持gzip的呢？通过图1我们可以很清晰的了解。

![v2-f0e962977436a2dba8754c406473bb42_b.pn][0]

图1 gzip工作原理图

1)浏览器请求url，并在request header中设置属性accept-encoding:gzip。表明浏览器支持gzip。

2)服务器收到浏览器发送的请求之后，判断浏览器是否支持gzip，如果支持gzip，则向浏览器传送压缩过的内容，不支持则向浏览器发送未经压缩的内容。一般情况下，浏览器和服务器都支持gzip，response headers返回包含content-encoding:gzip。

3)浏览器接收到服务器的响应之后判断内容是否被压缩，如果被压缩则解压缩显示页面内容。

下面以淘宝为例，验证一下开启gzip的效果。客户端（浏览器）请求[http://www.taobao.com/][1]。本次测试使用的浏览器为Chrome,打开控制台查看网络信息可以看到request headers中包含：**accept-encoding:gzip, deflate, sdch**，表明chrome浏览器支持这三种压缩。这里值得一提的是accept-encoding中添加的另外两个压缩方式deflate和sdch。deflate与gzip使用的压缩算法几乎相同，这里不再赘叙。sdch是Shared Dictionary Compression over HTTP的缩写，即通过字典压缩算法对各个页面中相同的内容进行压缩，减少相同的内容的传输。sdch是Google推出的，目前只在Google Chrome, Chromium 和Android中支持。图2为浏览器发送的request header。图3为服务器返回的response header。

![v2-f7eed78ad841f5461cdc5836fd0d2d91_b.pn][2]

图2 淘宝request header

![v2-28d4aa3bae6437f4e3b95db714ab5fde_b.pn][3]

图3 淘宝response header

通过图2以图3很明显可以看出网站支持gzip，那么当支持gzip之后，压缩效率如何体现呢？通常浏览器都有现成的插件检测gzip压缩效率，如firefoxd的YSlow插件，我这里使用了网站[http:// gzip.zzbaike.com/][4]做了检测。检测结果如图4所示：

![v2-21ac5a1d9779d45a50c81bb179f9904b_b.pn][5]

图4 淘宝gzip检测结果

很明显可以看出，通过使用gzip，静态文件被压缩了80.5%，极大的节省了服务器的网络带宽，这对于访问量巨大的淘宝来讲节约的流量非常可观。

在企业级应用中，通常被使用到的服务器有nginx，Apache等。nginx是取代Apache的高性能服务器，本文接下来的内容会介绍一下在Nginx中如何开启gzip。

### NGINX中开启GZIP：

如果服务端接口使用nodejs和express，那么开启nginx非常简单。启用 compress() 中间件即可并在nginx.conf中添加gzip配置项即可，express.compress() gzip压缩中间件，通过filter函数设置需要压缩的文件类型。压缩算法为gzip/deflate。这个中间件应该放置在所有的中间件最前面以保证所有的返回都是被压缩的。如果使用java开发，需要配置filter。

下面详细介绍一下如何在nginx.conf中配置gzip。此次我配置的gzip参数如图5所示：

![v2-b6b639c4fe1550e91368c75e73f8543e_b.pn][6]

图5 gzip参数

添加完参数后，运行 **nginx –t** 检查一下语法，若语法检测通过，则开始访问url检测gzip是否添加成功。以下为我所使用的gzip配置的作用。

1) gzip on：开启gzip。

2) gzip_comp_level：gzip压缩比。

3) gzip_min_length：允许被压缩的页面最小字节数。

4) gzip_types：匹配MIME类型进行压缩，text/html默认被压缩。

[0]: http://www.fimvisual.com/wp-content/uploads/2017/01/v2-f0e962977436a2dba8754c406473bb42_b.png
[1]: https://link.zhihu.com/?target=http%3A//www.taobao.com
[2]: http://www.fimvisual.com/wp-content/uploads/2017/01/v2-f7eed78ad841f5461cdc5836fd0d2d91_b.png
[3]: http://www.fimvisual.com/wp-content/uploads/2017/01/v2-28d4aa3bae6437f4e3b95db714ab5fde_b.png
[4]: https://link.zhihu.com/?target=http%3A//gzip.zzbaike.com/
[5]: http://www.fimvisual.com/wp-content/uploads/2017/01/v2-21ac5a1d9779d45a50c81bb179f9904b_b.png
[6]: http://www.fimvisual.com/wp-content/uploads/2017/01/v2-b6b639c4fe1550e91368c75e73f8543e_b.png