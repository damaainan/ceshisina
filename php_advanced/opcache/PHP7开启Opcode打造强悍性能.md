## PHP7开启Opcode打造强悍性能

来源：[http://www.cnblogs.com/lamp01/p/8985068.html](http://www.cnblogs.com/lamp01/p/8985068.html)

2018-05-04 15:12

鸟哥在博客中说，提高PHP 7性能的几个tips，第一条就是开启opcache：


记得启用Zend Opcache, 因为PHP7即使不启用Opcache速度也比PHP-5.6启用了Opcache快,  
所以之前测试时期就发生了有人一直没有启用Opcache的事情




那么什么是Opcache呢？

Opcache 的前生是 `Optimizer+` ，它是PHP的官方公司 Zend 开发的一款闭源但可以免费使用的 PHP 优化加速组件。 Optimizer+ 将PHP代码预编译生成的脚本文件 `Opcode` 缓存在共享内存中供以后反复使用，从而避免了从磁盘读取代码再次编译的时间消耗。同时，它还应用了一些代码优化模式，使得代码执行更快。从而加速PHP的执行。

 PHP的正常执行流程如下

![][0]

 

request请求（nginx,apache,cli等）-->Zend引擎读取.php文件-->扫描其词典和表达式 -->解析文件-->创建要执行的计算机代码(称为Opcode)-->最后执行Opcode--> response 返回

每一次请求PHP脚本都会执行一遍以上步骤，如果PHP源代码没有变化，那么Opcode也不会变化，显然没有必要每次都重新生成Opcode，结合在Web中无所不在的缓存机制，我们可以把Opcode缓存下来，以后直接访问缓存的Opcode岂不是更快，启用Opcode缓存之后的流程图如下所示：

 
![][1]

 Opcode cache 的目地是避免重复编译，减少 CPU 和内存开销。

下面介绍Opcache的安装

安装：


``` 
1、找到opcache的扩展，我的是php7.1
yum list php71*
2、安装扩展
yum install php71w-opcache.x86_64
```



配置：


```ini
zend_extension=opcache.so
[opcache]
;开启opcache
opcache.enable=1  

;CLI环境下，PHP启用OPcache
opcache.enable_cli=1

;OPcache共享内存存储大小,单位MB
opcache.memory_consumption=128  

;PHP使用了一种叫做字符串驻留（string interning）的技术来改善性能。例如，如果你在代码中使用了1000次字符串“foobar”，在PHP内部只会在第一使用这个字符串的时候分配一个不可变的内存区域来存储这个字符串，其他的999次使用都会直接指向这个内存区域。这个选项则会把这个特性提升一个层次——默认情况下这个不可变的内存区域只会存在于单个php-fpm的进程中，如果设置了这个选项，那么它将会在所有的php-fpm进程中共享。在比较大的应用中，这可以非常有效地节约内存，提高应用的性能。
这个选项的值是以兆字节（megabytes）作为单位，如果把它设置为16，则表示16MB，默认是4MB
opcache.interned_strings_buffer=8

;这个选项用于控制内存中最多可以缓存多少个PHP文件。这个选项必须得设置得足够大，大于你的项目中的所有PHP文件的总和。
设置值取值范围最小值是 200，最大值在 PHP 5.5.6 之前是 100000，PHP 5.5.6 及之后是 1000000。也就是说在200到1000000之间。
opcache.max_accelerated_files=4000

;设置缓存的过期时间（单位是秒）,为0的话每次都要检查
opcache.revalidate_freq=60

;从字面上理解就是“允许更快速关闭”。它的作用是在单个请求结束时提供一种更快速的机制来调用代码中的析构器，从而加快PHP的响应速度和PHP进程资源的回收速度，这样应用程序可以更快速地响应下一个请求。把它设置为1就可以使用这个机制了。
opcache.fast_shutdown=1

;如果启用（设置为1），OPcache会在opcache.revalidate_freq设置的秒数去检测文件的时间戳（timestamp）检查脚本是否更新。
如果这个选项被禁用（设置为0），opcache.revalidate_freq会被忽略，PHP文件永远不会被检查。这意味着如果你修改了你的代码，然后你把它更新到服务器上，再在浏览器上请求更新的代码对应的功能，你会看不到更新的效果
强烈建议你在生产环境中设置为0，更新代码后，再平滑重启PHP和web服务器。
opcache.validate_timestamps=0 

;开启Opcache File Cache(实验性), 通过开启这个, 我们可以让Opcache把opcode缓存缓存到外部文件中, 对于一些脚本, 会有很明显的性能提升.
这样PHP就会在/tmp目录下Cache一些Opcode的二进制导出文件, 可以跨PHP生命周期存在.
opcache.file_cache=/tmp
```



查看phpinfo：

![][2]

测试结果：

![][3]

同样的接口从以前的几百毫秒提升到现在的50ms左右

![][4]

 

参考资料：[Opcode是啥以及如何使用好Opcache][100]

[让PHP7达到最高性能的几个Tips ][101]

[0]: ../img/1128628-20180504142714761-711951956.png 
[1]: ../img/1128628-20180504142702126-1584014725.png 
[2]: ../img/1128628-20180504150553607-1732347193.png 
[3]: ../img/1128628-20180504150651899-685437096.png 
[4]: ../img/1128628-20180504150720857-181358113.png 
[100]: https://www.zybuluo.com/phper/note/1016714
[101]: http://www.laruence.com/2015/12/04/3086.html