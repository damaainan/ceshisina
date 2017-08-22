# 理解php-fpm的两种执行方式

 时间 2016-06-13 04:39:19 

原文[http://page.factj.com/blog/p/7][1]

<font face=微软雅黑>

前段时间配置`php-fpm`的时候，无意间发现原来他还有两种执行方式。与Apache一样，他的进程数也是可以根据设置分为动态和静态的。关于Apache的工作方式及对应的设置方法，我已经在《Ubuntu下配置Apache的Worker模式》一文中写出，这里不再多说。 而`php-fpm`也是同样存在两种方式，  
一种是直接开启指定数量的`php-fpm`进程，不再增加或者减少；   
另一种则是开始的时候开启一定数量的`php-fpm`进程，当请求量变大的时候，动态的增加`php-fpm`进程数到上限，当空闲的时候自动释放空闲的进程数到一个下限。    
这两种不同的执行方式，可以根据服务器的实际需求来进行调整。 这里先说一下涉及到这个的几个参数吧，他们分别是 

    pm、pm.max_children、pm.start_servers、pm.min_spare_servers和pm.max_spare_servers。

`pm` 表示使用那种方式，有两个值可以选择，就是static（静态）或者dynamic（动态）。在更老一些的版本中，dynamic被称作apache-like。这个要注意看配置文件给出的说明了。    

下面4个参数的意思分别为：    
`pm.max_children` ：静态方式下开启的`php-fpm`进程数量。    
`pm.start_servers` ：动态方式下的起始`php-fpm`进程数量。    
`pm.min_spare_servers` ：动态方式下的最小`php-fpm`进程数量。    
`pm.max_spare_servers` ：动态方式下的最大`php-fpm`进程数量。   

如果`dm`设置为`static`，那么其实只有`pm.max_children`这个参数生效。系统会开启设置的数量个`php-fpm`进程。   

如果`dm`设置为`dynamic`，那么`pm.max_children`参数失效，后面3个参数生效。系统会在`php-fpm`运行开始的时候启动`pm.start_servers`个`php-fpm`进程，然后根据系统的需求动态在`pm.min_spare_servers`和`pm.max_spare_servers`之间调整`php-fpm`进程数。   
那么，对于我们的服务器，选择哪种执行方式比较好呢？事实上，跟Apache一样，我们运行的PHP程序在执行完成后，或多或少会有内存泄露的问题。这也是为什么开始的时候一个`php-fpm`进程只占用3M左右内存，运行一段时间后就会上升到20-30M的原因了。  

所以，动态方式因为会结束掉多余的进程，可以回收释放一些内存，所以推荐在内存较少的服务器或者VPS上使用。具体最大数量根据 **内存/20M** 得到。比如说512M的VPS，建议`pm.max_spare_servers`设置为`20`。至于`pm.min_spare_servers`，则建议根据服务器的负载情况来设置，比较合适的值在`5~10`之间。   

然后对于比较大内存的服务器来说，设置为静态的话会提高效率。因为频繁开关`php-fpm`进程也会有时滞，所以内存够大的情况下开静态效果会更好。数量也可以根据 **内存/30M** 得到。比如说2GB内存的服务器，可以设置为50；4GB内存可以设置为100等。   

本博客建立在512M的VPS上，因此我设置的参数如下： 

    pm=dynamic
    pm.max_children=20
    pm.start_servers=5
    pm.min_spare_servers=5
    pm.max_spare_servers=20

这样就可以最大的节省内存并提高执行效率。
</font>

[1]: http://page.factj.com/blog/p/7
