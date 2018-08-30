## 如何发挥出PHP7的高性能

来源：[https://zhuanlan.zhihu.com/p/42824965](https://zhuanlan.zhihu.com/p/42824965)

时间：编辑于 08:50



![][0]

一点PHP博客分享如何发挥出PHP7版本中的高性能，PHP7发布也有一段时间了，但是现在还有很多小伙伴还在用PHP5.6开发项目，有的小伙伴用了PHP7只是听说是高性能的版本，却不知道如何体现出PHP7的优势，博主看了关于鸟哥(PHP7核心开发人员)对PHP7的一些描述后，决定基于鸟哥的描述总结一篇关于PHP7的文章分享给大家。

在使用PHP7的时候如果要开启它的高性能，需注意以下几点：

##  **1. Opcache** 

一定要启用Zend Opcache，不过就算不去开启这个扩展，它的性能速度也比PHP5.6高很多。启用Opcache方式，在php.ini配置文件中加入:

```cfg

zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=1

```

##  **2. 使用新的编译器** 

使用新一点的编译器，推荐GCC 4.8以上，因为只有GCC 4.8以上PHP才会开启Global Register for opline and execute_data支持，这个会带来5%左右的性能提升(Wordpres的QPS角度衡量)

其实GCC 4.8以前的版本也支持，但是我们发现它支持的有Bug，所以必须是4.8以上的版本才会开启这个特性。

##  **3. HugePage** 

一定要在系统中开启HugePages，开启Opcache的huge_code_pages，可以通过以下代码做到。

以我的CentOS 6.5为例，通过:

```cfg

$sudo sysctl vm.nr_hugepages=512

```

分配512个预留的大页内存:

```

$ cat /proc/meminfo | grep Huge
AnonHugePages: 106496 kB
HugePages_Total: 512
HugePages_Free: 504
HugePages_Rsvd: 27
HugePages_Surp: 0
Hugepagesize: 2048 kB

```

最后在php.ini中加入:

```cfg

opcache.huge_code_pages=1

```

这样一来，PHP会把自身的text段，以及内存分配中的huge都采用大内存页来保存，减少TLB miss，从而提高性能。

##  **4. Opcache file cache** 

开启Opcache File Cache(实验性)，通过开启这个，我们可以让Opcache把opcode缓存缓存到外部文件中，对于一些脚本，会有很明显的性能提升。

在php.ini中加入:

```cfg

opcache.file_cache=/tmp

```

这样PHP就会在/tmp目录下Cache一些Opcode的二进制导出文件，可以跨PHP生命周期存在。

##  **5. PGO** 

如果你的PHP只是用来运行一个独有的项目，比如只是为你的Wordpress，或者drupal，或者其他什么，那么你就可以尝试通过PGO，来提升PHP，专门为你的这个项目提高性能。

具体的，以wordpress 4.1为优化场景。首先在编译PHP的时候首先:

```

$ make prof-gen

```

然后用你的项目训练PHP，比如对于Wordpress:

```

$ sapi/cgi/php-cgi -T 100 /home/huixinchen/local/www/htdocs/wordpress/index.php >/dev/null

```

也就是让php-cgi跑100遍wordpress的首页，从而生成一些在这个过程中的profile信息，从而让PHP记住这些信息。

最后:

```

$ make prof-clean
$ make prof-use

```

这个时候你编译得到的PHP7，就是为你的项目量身打造的最高性能的编译版本。

[0]: https://pic4.zhimg.com/v2-e6f9adde9ec86fab19bb6f6ef09e88b8_1200x500.jpg