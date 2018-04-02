## Postman教程——调试和日志

来源：[https://www.jellythink.com/archives/167](https://www.jellythink.com/archives/167)

时间 2018-03-19 00:09:22

 
## 前言
 
对于Postman来说，并不是所有东西都是一眼就能看的到的。有一些内容是在控制台中输出的，这篇文章就对Postman中通过控制台进行调试和打印日志进行总结。
 
关于调试和日志，在Postman中主要涉及到以下两点：
 

* Postman控制台 
* DevTools 
 

现在就分别对上面说的这两点进行总结。
 
## Postman控制台
 
Postman控制台类似于浏览器的开发者控制台，不同之处在于它针对API开发进行了调整。如果API或API测试没有按照我们期望的那样进行，那么Postman控制台将是我们进行调试的得力工具。只要Postman控制台窗口处于打开状态，所有的API活动都将记录在此处，我们可以通过这里查看底层发生了什么。
 
Postman控制台会记录以下信息：
 

* 发送的实际请求，包括所有底层请求头和变量值等； 
* 服务器返回的最原始的响应报文，这里输出的响应报文是没有被Postman处理的响应报文； 
* 用于请求的代理配置和证书； 
* 测试脚本或预先请求脚本的错误日志； 
* 使用`console.log()`输出的内容。  
 

在脚本中使用`console.info()`或`console.warn()`将有助于确认执行的代码行。这个使用方法和JavaScript中的`console.log()`类似。
 
## DevTools
 
通过如下图所示的方式打开DevTools。
 
![][0]
 
打开DevTools后，你会发现这货和Chrome的开发者控制台基本一模一样。接下来的用法和Chrome的开发者控制台也几乎一模一样。各位读者自行挖掘吧。
 
## 总结
 
总结完毕！希望对大家有帮助。
 
果冻想-一个原创技术文章分享网站。
 
2018年2月21日 于包头。
 


[0]: ./img/bUz6Vbz.png 