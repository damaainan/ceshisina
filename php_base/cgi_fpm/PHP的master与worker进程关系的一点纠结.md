# PHP的master与worker进程关系的一点纠结

[2017年03月22日][0] 作者 **[夜行人][1]**

### 纠结的起点

同事发了一篇文档，里面提及

1. FPM 的 master 进程接收到请求
1. master 进程根据配置指派特定的 worker 进程进行请求处理，如果没有可用进程，返回错误，这也是我们配合 Nginx 遇到502错误比较多的原因。

全文请参考： [Nginx 与 FPM 的工作机制][2]

我曾经认为Nginx也是由master负责派发请求给worker，但同事那边马上发了篇文档出来打脸，文章提到`master只负责管理worker`，如重启，**重新加载配置文件，`并不会派发请求`**。详见：[nginx平台初探][3]

### 纠结过程

为什么我会纠结呢？

1. 上面提到的Nginx
1. `fpm`一开始其实是一个第三方管理软件，类似`spawn-cgi`，说白了就是负责启动`php-cgi`进程的，那PHP官方把它整合进来作为官方的`php-cgi`管理工具后，会委以「派发请求」这样的重任吗？

早上和同事一起纠结了一下，纠结过程如下：

1. strace对比master和worker的行为，同事把Nginx和fpm都设置成了1个worker进程观察，得出结论是不会经过fpm的master进程
```
    strace -e network -p fpm_master_pid
    strace -e network -p fpm_worker_pid
```
1. 放狗，发现另外一种说法，见：[关于fastcgi和php-fpm的疑惑][4]，引用如下


> master进程并不接收和分发请求,而是`worker进程直接accpet请求后poll处理`.

> master进程不断调用`epoll_wait`和`getsockopt`是用来异步处理信号事件和定时器事件.

> 这里提一下,Nginx也类似,master进程并不处理请求,而是worker进程直接处理, 不过区别在于**`Nginx的worker进程是epoll异步处理请求`**,而**`PHP-FPM仍然是poll`**.

1. 把master干掉，看请求是否可以正常处理，经实际测试，**`master干掉后，worker依然在，请求也可以正常处理`**。
```
    kill -HUP fpm_master_pid
```

### 其他

1. worker进程数量不够的时候，显然是manager启动了更多进程，这个时候是manager怎么知道的

答：[PHP源码分析 – PHP-FPM运行模式详解][5]，看起来就是满足下面的条件就会执行 `fpm_children_make`

    1. idle < pm_min_spare_servers
    2. running_children < pm_max_children
    3. MIN(MIN(idle_spawn_rate, pm_min_spare_servers - idle), pm_max_children - running_children) > 0

1. nginx中配置的fastcgi_pass默认是fastcgi的监听端口，这个配置的意义是什么？

答：[浅谈多进程程序的进程控制和管理方式][6]，主要看「多进程下的套接字」，这段文字解释了为什么多进程不需要派发，因为它socket是多进程共享的

### 纠结论

**`fpm的master并不承担派发请求的角色`**。

特别鸣谢纠结侠：郑导（C好厉害）

# 其他资料

* [NGINX 1.9.1 新特性：套接字端口共享][7]
* [python使用master worker管理模型开发服务端][8]，里面提到各种进程信号


[0]: https://www.187299.com/archives/2238
[1]: https://www.187299.com/archives/author/admin
[2]: https://zhuanlan.zhihu.com/p/20694204
[3]: http://tengine.taobao.org/book/chapter_02.html
[4]: https://segmentfault.com/q/1010000004113822
[5]: http://mojijs.com/2016/11/221271/index.html
[6]: https://taozj.org/201611/about-multi-process-thread-dev-manage.html
[7]: http://io.upyun.com/2015/07/20/nginx-socket-sharding/
[8]: http://xiaorui.cc/2015/07/13/python%E4%BD%BF%E7%94%A8master-worker%E7%AE%A1%E7%90%86%E6%A8%A1%E5%9E%8B%E5%BC%80%E5%8F%91%E6%9C%8D%E5%8A%A1%E7%AB%AF/