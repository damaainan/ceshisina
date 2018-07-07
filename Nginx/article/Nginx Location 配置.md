## Nginx Location 配置

来源：[https://segmentfault.com/a/1190000014866603](https://segmentfault.com/a/1190000014866603)

原文来自：[https://www.codecasts.com/blo...][0]

今天有一位同学问到 Nginx 的站点多路径匹配的问题？

1.`www.domain.com/a`需要返回`/var/www/domain.com/a/index.html`
2.`www.domain.com/b`需要返回`/var/www/domain.com/b/index.html`
如何配置 Nginx 使之生效？
解决这个问题，第一的反映是直接使用 Nginx 的 location 指令来解决，不过在给出答案之前，我们先来了解一下 Nginx location 指令的基础。
## Nginx 区块配置概念

在 Nginx 的配置文件中，通常会用两个常用的区块(Block)来进行设置：

1.Server 区块
2.Localtion 区块

这里的区块是指 Block，你甚至可以理解为后面的那一对`{}`之间的配置内容。
Sever 区块主要是真的主机的配置，比如配置主机的域名，IP，端口等内容。当然，在一个 Nginx 的配置文件里面，我们是可以指定多个 Sever 区块的配置的。

而 Location 区块则是在 Sever 区块里面，细分到针对不同的路径和请求而进行的配置。因为一个站点中的 URI 通常会非常多，所以在 Location 区块设置这部分，你也是可以写多个 Location 的配置的。

下面来看看 Location 配置的基本语法先：

```nginx
location optional_modifier location_match {
 # 这个 {} 里面的配置内容就是一个区块 Block
}
```

上面的`optional_modifier`配置项是可以使用正则表达式的。常用的几种如下：


* `留空`。对，留空也是一种设置方式。在留空的情况下，配置表示请求路径由`location_match`开始。
* `=`，等于号还是非常容易理解的：就是请求路径正好等于后面的`location_match`的值；跟第一项留空还是有区别的。
* `~`，飘号（注意是英文输入的飘号）表示大小写敏感的正则匹配。
* `~*`表示大小写不敏感的正则匹配。
* `^~`表示这里不希望有正则匹配发生。


## Nginx 处理 Location 区块的顺序

上面了解了 location 指令基本的概念和常用配置。我们再来看看 Location 生效的顺序！这个也很重要：

每一个请求进来 Nginx 之后，Nginx 就会选择一个 Location 的最佳匹配项进行响应，处理的具体流程是逐一跟 location 的配置进行比对，这个步骤可以分为以下几步：


* 先进行`前缀式`的匹配（也就是 location 的 optional_modifier 为空的配置）。
* Nginx 其次会根据 URI 寻找完全匹配的 location 配置（也就是 location 的 optional_modifier 为`=`的配置）.
* 如果还是没有匹配到，那就先匹配`^~`配置，如果找到一个配置的话，则会停止寻找过程，直接返回响应内容。
* 如果还是没有找到匹配项的话，则会先进行大小写敏感的正则匹配，然后再是大小不写敏感的正则匹配。


Nginx Location 配置的一些例子：

多说无益，看了那么多理论，没有具体的例子支撑也是白搭，所以我们来看一下具体的配置例子：

```nginx
location  = / {
  # = 等号配置符，只匹配 / 这个路由
}
```

```nginx
location /data {
   # 留空配置，会匹配有 /data 开始的路由，后续有匹配会往下匹配。
}
```

```nginx
location ^~ /img/ {
  # 注意 ^~ 配置，这里匹配到 /img/ 开始的话，直接就返回了。
}
```

```nginx
location ~* .(png|gif|ico|jpg|jpeg)$ {
  # 匹配以 png, gif, ico, jpg or jpeg 结尾的请求；这个通常用来设置图片的请求响应。 
}
```
 **`非常实用的两个例子：`** 

1.简单的图片防盗链

```nginx
location ~ .(png|gif|jpe?g)$ {
  valid_referers none blocked yourwebsite.com *.yourwebsite.com;
  # 注意上面写上你的域名就好  
  if ($invalid_referer) {
      return   403;
  }
}
```

2.针对一些可写入的路径，禁止`php`或者`js`的脚步执行

```nginx
location ~* /(media|images|cache|tmp|logs)/.*.(php|jsp|pl|py|asp|cgi|sh)$ {
  return 403;
}
```
## 问题的答案

最后，我们再看问题的答案，可以是类似这个样子的：

```nginx
location /a {
   root /var/www/domain.com/a;
}

location /b {
   root /var/www/domain.com/b;
}
```

[0]: https://www.codecasts.com/blog/post/nginx-location-configuration