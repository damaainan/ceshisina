# Nginx静动分离

 时间 2017-09-10 10:23:17  

原文[http://www.jialeens.com/archives/312][1]
<font face=微软雅黑>

静动分离是根据资源的类型，将资源分为两类：静态资源和动态资源，并将静态资源进行缓存，以达到加快后端应用速度的目的。

为什么要做静动分离分离？

web应用中，JSP、ASP、PHP等请求是需要后端应用处理的，还有一部分信息PNG，JPG，GIF，CSS，JS等，是不需要后端应用处理的，如果所有的信息都经过后端应用处理，后端应用的请求数就会很多，因此，我们将静态资源放到Nginx端，由Nginx来处理静态资源，后端应用只处理请求，已达到提高效率的结果。

因此，动态资源转发到tomcat服务器我们就使用到了前面讲到的反向代理了。

```nginx
    //动态资源
      location ~ \.(jsp|jspx|do|action)(\/.*)?$ { //动态请求转发到tomcat服务器，匹配方式可自定义
                #设置真实ip
                proxy_set_header real_ip $remote_addr;  
                proxy_pass http://127.0.0.1:8080;
            }
     //静态资源    
     location ~ .*\.(js|css|htm|html|gif|jpg|jpeg|png|bmp|swf|ioc|rar|zip|txt|flv|mid|doc|ppt|pdf|xls|mp3|wma)$ { 
                root static;
            }
    
```

从上面可以看到，JSP等请求会直接反向代理到`http://127.0.0.1:8080`上，而js等静态资源，Nginx直接会去`static`目录下去寻找。

咱们在`static`目录下放一张图片，可以直接访问到，同时访问Jsp等文件，后端应用也会获取到请求，并进行处理。

可以发现，这部分内容和Nginx缓存很相似，区别在于，Nginx缓存时，先去后端应用拉取静态资源，存到缓存目录，在缓存时间内的请求会从缓存里获取，而静动分离则不受缓存时间的影响。 

同时Nginx缓存相对简单一些，在开发时，不需要关注静态资源的处理。而对于静动分离，则需要在开发时就需要考虑。

有兴趣的朋友可以试试啦。
</font>

[1]: http://www.jialeens.com/archives/312
