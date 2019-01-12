## php-fpm进程数优化方法

来源：[http://www.jianshu.com/p/1ca8d0fb4894](http://www.jianshu.com/p/1ca8d0fb4894)

时间 2019-01-06 21:48:24


  
#### php-fpm优化参数介绍

他们分别是：pm、pm.max_children、pm.start_servers、pm.min_spare_servers、pm.max_spare_servers。


pm：表示使用那种方式，有两个值可以选择，就是static（静态）或者dynamic（动态）。

在更老一些的版本中，dynamic被称作apache-like。这个要注意看配置文件的说明。

下面4个参数的意思分别为：


pm.max_children：静态方式下开启的php-fpm进程数量

pm.start_servers：动态方式下的起始php-fpm进程数量

pm.min_spare_servers：动态方式下的最小php-fpm进程数

pm.max_spare_servers：动态方式下的最大php-fpm进程数量

区别：


如果dm设置为 static，那么其实只有pm.max_children这个参数生效。系统会开启设置数量的php-fpm进程。

如果dm设置为 dynamic，那么pm.max_children参数失效，后面3个参数生效。

系统会在php-fpm运行开始 的时候启动pm.start_servers个php-fpm进程，

然后根据系统的需求动态在pm.min_spare_servers和pm.max_spare_servers之间调整php-fpm进程数

      
#### 服务器具体配置


对于我们的服务器，选择哪种执行方式比较好呢？事实上，跟Apache一样，运行的PHP程序在执行完成后，或多或少会有内存泄露的问题。

这也是为什么开始的时候一个php-fpm进程只占用3M左右内存，运行一段时间后就会上升到20-30M的原因了。

    
对于内存大的服务器（比如8G以上）来说，指定静态的max_children实际上更为妥当，因为这样不需要进行额外的进程数目控制，会提高效率。

因为频繁开关php-fpm进程也会有时滞，所以内存够大的情况下开静态效果会更好。数量也可以根据 内存/30M 得到，比如8GB内存可以设置为100，

那么php-fpm耗费的内存就能控制在 2G-3G的样子。如果内存稍微小点，比如1G，那么指定静态的进程数量更加有利于服务器的稳定。

这样可以保证php-fpm只获取够用的内存，将不多的内存分配给其他应用去使用，会使系统的运行更加畅通。

    
对于小内存的服务器来说，比如256M内存的VPS，即使按照一个20M的内存量来算，10个php-cgi进程就将耗掉200M内存，那系统的崩溃就应该很正常了。

因此应该尽量地控制php-fpm进程的数量，大体明确其他应用占用的内存后，给它指定一个静态的小数量，会让系统更加平稳一些。或者使用动态方式，

因为动态方式会结束掉多余的进程，可以回收释放一些内存，所以推荐在内存较少的服务器或VPS上使用。具体最大数量根据 内存/20M 得到。

比如说512M的VPS，建议pm.max_spare_servers设置为20。至于pm.min_spare_servers，则建议根据服务器的负载情况来设置，比如服务器上只是部署php环境的话，比较合适的值在5~10之间。

