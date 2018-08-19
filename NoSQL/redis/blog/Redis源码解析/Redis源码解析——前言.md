# Redis源码解析——前言

 时间 2016-12-08 20:21:10  方亮的专栏

原文[http://blog.csdn.net/breaksoftware/article/details/53435940][2]

今天开启Redis源码的阅读之旅。对于一些没有接触过开源代码分析的同学来说，可能这是一件很麻烦的事。但是我总觉得做一件事，不管有多大多难，我们首先要在战略上蔑视它，但是要在战术上重视它。除了一些高大上的技术，我们一般人都能用比较简单的方式描述它是干什么的。比如Redis，它不就是一个可以通过网络访问的KV型数据库嘛。在没有源码的情况下，可以想象出它应该是通过网络服务、指令解析、特殊的内存结构设计（方便增删改查）、持久化等技术构成。然后我们在战术上要重视它各个技术的实现，特别是一些我们没想到的一些技术。 [（转载请指明出于breaksoftware的csdn博客）][4]

首先，我会先粗略阅读一遍代码。这个过程不会花费比较多的时间，因为只是简单看看。很多技术细节、算法等都是一带而过。这个过程只为了达到一个效果：大致清楚每个文件都是为了解决哪类问题的。其实第一遍“扫”代码并不能完全达到这个效果，但是能预判出我们确定需要使用的技术对应的文件就行了。剩下的一些未知文件可能就是我们在预估这个工程构成时没有考虑到的，它们往往隐藏了一些特殊功能。 

然后，我会选择阅读Makefile文件，看看它是由哪些文件编译生成，以及它依赖的一些技术。再之后就按我们预估的技术点去看看它们的实现。在阅读的过程中，我们可能会遇到之前我们没有预估到的一些技术点，这个时候我们要采用广度优先方法去阅读还是深度优先方法去阅读就要看各自的技术能力和爱好了。 

现在回到Redis上来。我准备阅读的源码地址是http://download.redis.io/releases/redis-3.2.5.tar.gz。它是目前稳定的3.2版本。解压完之后，可以发现

![][5]

一般来说，我们可以通过文件命名猜测出它们的功能。但是阅读过程也是要“大胆猜测，小心求证”。比如上图中，Copying文件可以猜测出它是版本信息文件，我们可以不关心。Runtest、Runtest-cluster、Runtest-sentinel分别对应于“整体测试”、“分布式测试”和“主从切换测试”的脚本，这个时候我们就知道“分布式”和“主从切换”是Redis的重要功能，这样就补齐了我们对其技术点构成的认识。Redis.conf和Seninel.conf分别对应于Redis的配置和主从配置，此时看这些配置还没用，前期我们可以略过，等到用到时再回来看看。Deps应该是depends的英文缩写，即它应该是依赖的库

![][6]

一般来说，依赖库都是开源的第三方库。上图可见Redis需要在内部使用到： 

* Lua脚本引擎。Redis内嵌Lua脚本引擎，那么说明Redis需要Lua语言的解析能力。那么可以进一步猜测应该是用户可以定制Lua脚本让Reids去执行，这相当于Redis开放了一个非常自由的接口供外部使用。
* Linenoise是一个命令行编辑库。这个正是我们之前预估的Redis基础功能之一。它的相关资料可见https://github.com/antirez/linenoise
* Jemalloc是内存管理库。很多开源项目不使用glibc自带的ptmalloc，而是使用Jemalloc或者Tcmalloc这类更高效的内存管理库。
* Hiredis是Redis数据库的C接口。这块和Redis相关性比较大，我们之后也会重点关注下。
* Geohash-int是一种地理编码算法。它将二维经纬度信息转换成Int型数据。

上面这些第三方库，我们不会全部去看。比如Lua脚本引擎，这块技术非常独立，我们在阅读Redis代码时应该是不会深入阅读的。再比如Jemalloc库，它也是非常基础的库，未来我应该会分析ptmalloc、tcmalloc和jemalloc这三种内存管理库，但是在分析Redis代码时也不会去深入阅读。Geohash-int是一套算法，除非它提供的特性对redis非常重要，否则之后应该也不会去阅读。Linenoise是用于命令行编辑的，它也非Redis主要功能，可以不用去看。Hiredis可能和Redis的相关性大一些，这个模块应该会被关注。

退到上一层，再看看Tests，它是测试相关的目录。里面都是各种测试Redis的脚本。

![][7]

可见测试脚本是一些后缀为tcl的文件。它的内容这是一种被广泛使用的脚本测试语言——TCL语言。这块我们应该也不会过多涉及。

Utils从命名看，它应该是一些工具

![][8]

从上图看，我们可以发现其内容大部分是一些脚本语言。所以我们之后也不会太多涉及。

然后我们关注下Makefile文件

    # Top level makefile, the real shit is at src/Makefile
    
    default: all
    
    .DEFAULT:
        cd src && $(MAKE) $@
    
    install:
        cd src && $(MAKE) $@
    
    .PHONY: install

它进入到src目录，然后再make。于是我们也进入最最重要的redis源码目录——src去一看究竟。

进入Src后，我们仍然关注Makffile文件。最开始除了一些编译参数和依赖项定义外，还有就是内存管理库的使用问题

    # Default allocator
    ifeq ($(uname_S),Linux)
        MALLOC=jemalloc
    else
        MALLOC=libc
    endif
    
    # Backwards compatibility for selecting an allocator
    ifeq ($(USE_TCMALLOC),yes)
        MALLOC=tcmalloc
    endif
    
    ifeq ($(USE_TCMALLOC_MINIMAL),yes)
        MALLOC=tcmalloc_minimal
    endif
    
    ifeq ($(USE_JEMALLOC),yes)
        MALLOC=jemalloc
    endif
    
    ifeq ($(USE_JEMALLOC),no)
        MALLOC=libc
    
    endif

linux系统上，Redis默认选择的内存管理库是jemalloc，其他系统则是选择libc的ptmalloc。当然还可以通过指定库来修改内存管理库。如上可以见我们还可以选择tcmalloc或者tcmalloc_minimal。

之后还是一些编译对象组合

    REDIS_SERVER_NAME=redis-server
    REDIS_SENTINEL_NAME=redis-sentinel
    REDIS_SERVER_OBJ=adlist.o quicklist.o ae.o anet.o dict.o server.o sds.o zmalloc.o lzf_c.o lzf_d.o pqsort.o zipmap.o sha1.o ziplist.o release.o networking.o util.o object.o db.o replication.o rdb.o t_string.o t_list.o t_set.o t_zset.o t_hash.o config.o aof.o pubsub.o multi.o debug.o sort.o intset.o syncio.o cluster.o crc16.o endianconv.o slowlog.o scripting.o bio.o rio.o rand.o memtest.o crc64.o bitops.o sentinel.o notify.o setproctitle.o blocked.o hyperloglog.o latency.o sparkline.o redis-check-rdb.o geo.o
    REDIS_GEOHASH_OBJ=../deps/geohash-int/geohash.o ../deps/geohash-int/geohash_helper.o
    REDIS_CLI_NAME=redis-cli
    REDIS_CLI_OBJ=anet.o adlist.o redis-cli.o zmalloc.o release.o anet.o ae.o crc64.o
    REDIS_BENCHMARK_NAME=redis-benchmark
    REDIS_BENCHMARK_OBJ=ae.o anet.o redis-benchmark.o adlist.o zmalloc.o redis-benchmark.o
    REDIS_CHECK_RDB_NAME=redis-check-rdb
    REDIS_CHECK_AOF_NAME=redis-check-aof
    REDIS_CHECK_AOF_OBJ=redis-check-aof.o
    …………………………………………
    # redis-server
    $(REDIS_SERVER_NAME): $(REDIS_SERVER_OBJ)
        $(REDIS_LD) -o $@ $^ ../deps/hiredis/libhiredis.a ../deps/lua/src/liblua.a $(REDIS_GEOHASH_OBJ) $(FINAL_LIBS)
    
    # redis-sentinel
    $(REDIS_SENTINEL_NAME): $(REDIS_SERVER_NAME)
        $(REDIS_INSTALL) $(REDIS_SERVER_NAME) $(REDIS_SENTINEL_NAME)
    
    # redis-check-rdb
    $(REDIS_CHECK_RDB_NAME): $(REDIS_SERVER_NAME)
        $(REDIS_INSTALL) $(REDIS_SERVER_NAME) $(REDIS_CHECK_RDB_NAME)
    
    # redis-cli
    $(REDIS_CLI_NAME): $(REDIS_CLI_OBJ)
        $(REDIS_LD) -o $@ $^ ../deps/hiredis/libhiredis.a ../deps/linenoise/linenoise.o $(FINAL_LIBS)
    
    # redis-benchmark
    $(REDIS_BENCHMARK_NAME): $(REDIS_BENCHMARK_OBJ)
        $(REDIS_LD) -o $@ $^ ../deps/hiredis/libhiredis.a $(FINAL_LIBS)
    
    # redis-check-aof
    $(REDIS_CHECK_AOF_NAME): $(REDIS_CHECK_AOF_OBJ)
    
    $(REDIS_LD) -o $@ $^ $(FINAL_LIBS)

上面脚本可以见这个Makefile可以编译出6个不同的最终产物。

其中最核心的应该是REDIS_SERVER_NAME对应的编译内容。我们从其需要链接的文件（REDIS_SERVER_OBJ中的内容）来看，程序的入口函数main应该位于server.o文件中。我们继续查看server.c文件，果然发现了它。

之后的代码我采用深度优先的方法去阅读，但是这种方式是需要在一条主线的基础之上进行的。这样就会导致主线的逻辑被打的很零散。所以我决定还是要分章节，从最基础的一些代码开始分析。如果模块和Redis不是强关联的，我将以该模块名为分析博文的标题，比如之前介绍的SDS字符串管理库，它的相关介绍名称为 [《Simple Dynamic Strings(SDS)源码解析和使用说明一》][9] 和 [《Simple Dynamic Strings(SDS)源码解析和使用说明二》][10]。而和Redis强关联的模块，我将以《Redis源码解析——XXXXX》形式命名。


[2]: http://blog.csdn.net/breaksoftware/article/details/53435940

[4]: http://blog.csdn.net/breaksoftware/article/details/53435940
[5]: ./img/2eINza6.png
[6]: ./img/6byQ7zM.png
[7]: ./img/Jj22ieN.png
[8]: ./img/6VVZRzQ.png
[9]: http://blog.csdn.net/breaksoftware/article/details/53393191
[10]: http://blog.csdn.net/breaksoftware/article/details/53397458