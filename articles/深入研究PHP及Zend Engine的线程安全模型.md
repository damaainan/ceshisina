#[深入研究PHP及Zend Engine的线程安全模型][0]

作者 张洋 | 发布于 2011-11-04 

[PHP][1][线程安全][2][ZendEngine][3]

在阅读PHP源码和学习PHP扩展开发的过程中，我接触到大量含有“TSRM”字眼的宏。通过查阅资料，知道这些宏与Zend的线程安全机制有关，而绝大多数资料中都建议按照既定规则使用这些宏就可以，而没有说明这些宏的具体作用。不知道怎么回事总是令人不舒服的，因此我通过阅读源码和查阅有限的资料简要了解一下相关机制，本文是我对研究内容的总结。

本文首先解释了线程安全的概念及PHP中线程安全的背景，然后详细研究了PHP的线程安全机制ZTS（Zend Thread Safety）及具体的实现TSRM，研究内容包括相关数据结构、实现细节及运行机制，最后研究了Zend对于单线程和多线程环境的选择性编译问题。 

### 线程安全

线程安全问题，一言以蔽之就是多线程环境下如何安全存取公共资源。我们知道，每个线程只拥有一个私有栈，共享所属进程的堆。在C中，当一个变量被声明在任何函数之外时，就成为一个全局变量，这时这个变量会被分配到进程的共享存储空间，不同线程都引用同一个地址空间，因此一个线程如果修改了这个变量，就会影响到全部线程。这看似为线程共享数据提供了便利，但是PHP往往是每个线程处理一个请求，因此希望每个线程拥有一个全局变量的副本，而不希望请求间相互干扰。

早期的PHP往往用于单线程环境，每个进程只启动一个线程，因此不存在线程安全问题。后来出现了多线程环境下使用PHP的场景，因此Zend引入了Zend线程安全机制（Zend Thread Safety，简称ZTS）用于保证线程的安全。

### ZTS的基本原理及实现

#### 基本思想

说起来ZTS的基本思想是很直观的，不是就是需要每个全局变量在每个线程都拥有一个副本吗？那我就提供这样的机制：

在多线程环境下，申请全局变量不再是简单声明一个变量，而是整个进程在堆上分配一块内存空间用作“线程全局变量池”，在进程启动时初始化这个内存池，每当有线程需要申请全局变量时，通过相应方法调用TSRM（Thread Safe Resource Manager，ZTS的具体实现）并传递必要的参数（如变量大小等等），TSRM负责在内存池中分配相应内存区块并将这块内存的引用标识返回，这样下次这个线程需要读写此变量时，就可以通过将唯一的引用标识传递给TSRM，TSRM将负责真正的读写操作。这样就实现了线程安全的全局变量。下图给出了ZTS原理的示意图：

![][4]

Thread1和Thread2同属一个进程，其中各自需要一个全局变量Global Var，TSRM为两者在线程全局内存池中（黄色部分）各自分配了一个区域，并且通过唯一的ID进行标识，这样两个线程就可以通过TSRM存取自己的变量而互不干扰。

下面通过具体的代码片段看一下Zend具体是如何实现这个机制的。这里我用的是PHP5.3.8的源码。

TSRM的实现代码在PHP源码的“TSRM”目录下。

#### 数据结构

TSRM中比较重要的数据结构有两个：tsrm_tls_entry和tsrm_resource_type。下面先看tsrm_tls_entry。

tsrm_tls_entry定义在TSRM/TSRM.c中：
```c
typedef struct _tsrm_tls_entry tsrm_tls_entry;
 
struct _tsrm_tls_entry {
    void **storage;
    int count;
    THREAD_T thread_id;
    tsrm_tls_entry *next;
}
```
每个tsrm_tls_entry结构负责表示一个线程的所有全局变量资源，其中thread_id存储线程ID，count记录全局变量数，next指向下一个节点。storage可以看做指针数组，其中每个元素是一个指向本节点代表线程的一个全局变量。最终各个线程的tsrm_tls_entry被组成一个链表结构，并将链表头指针赋值给一个全局静态变量tsrm_tls_table。注意，因为tsrm_tls_table是一个货真价实的全局变量，所以所有线程会共享这个变量，这就实现了线程间的内存管理一致性。tsrm_tls_entry和tsrm_tls_table结构的示意图如下：

![][5]

tsrm_resource_type的内部结构相对简单一些：

```c
typedef struct {
    size_t size;
    ts_allocate_ctor ctor;
    ts_allocate_dtor dtor;
    int done;
} tsrm_resource_type;
```
上文说过tsrm_tls_entry是以线程为单位的（每个线程一个节点），而tsrm_resource_type以资源（或者说全局变量）为单位，每次一个新的资源被分配时，就会创建一个tsrm_resource_type。所有tsrm_resource_type以数组（线性表）的方式组成tsrm_resource_table，其下标就是这个资源的ID。每个tsrm_resource_type存储了此资源的大小和构造、析构方法指针。某种程度上，tsrm_resource_table可以看做是一个哈希表，key是资源ID，value是tsrm_resource_type结构。

#### 实现细节

这一小节分析TSRM一些算法的实现细节。因为整个TSRM涉及代码比较多，这里拣其中具有代表性的两个函数分析。

第一个值得注意的是tsrm_startup函数，这个函数在进程起始阶段被sapi调用，用于初始化TSRM的环境。由于tsrm_startup略长，这里摘录出我认为应该注意的地方：
```c
/* Startup TSRM (call once for the entire process) */
TSRM_API int tsrm_startup(int expected_threads, int expected_resources, int debug_level, char *debug_filename)
{
    /* code... */
 
    tsrm_tls_table_size = expected_threads;
 
    tsrm_tls_table = (tsrm_tls_entry **) calloc(tsrm_tls_table_size, sizeof(tsrm_tls_entry *));
    if (!tsrm_tls_table) {
        TSRM_ERROR((TSRM_ERROR_LEVEL_ERROR, "Unable to allocate TLS table"));
        return 0;
    }
    id_count=0;
 
    resource_types_table_size = expected_resources;
    resource_types_table = (tsrm_resource_type *) calloc(resource_types_table_size, sizeof(tsrm_resource_type));
    if (!resource_types_table) {
        TSRM_ERROR((TSRM_ERROR_LEVEL_ERROR, "Unable to allocate resource types table"));
        free(tsrm_tls_table);
        tsrm_tls_table = NULL;
        return 0;
    }
 
    /* code... */
 
    return 1;
}
```
其实tsrm_startup的主要任务就是初始化上文提到的两个数据结构。第一个比较有意思的是它的前两个参数：expected_threads和expected_resources。这两个参数由sapi传入，表示预计的线程数和资源数，可以看到tsrm_startup会按照这两个参数预先分配空间（通过calloc）。因此TSRM会首先分配可容纳expected_threads个线程和expected_resources个资源的。要看各个sapi默认会传入什么，可以看各个sapi的源码（在sapi目录下），我简单看了一下：

![][6]

可以看到比较常用的sapi如mod_php5、php-fpm和cgi都是预分配一个线程和一个资源，这样是因为不愿浪费内存空间，而且多数情况下PHP还是运行于单线程环境。

这里还可以看到一个id_count变量，这个变量是一个全局静态变量，其作用就是通过自增产生资源ID，这个变量在这里被初始化为0。所以TSRM产生资源ID的方式非常简单：就是一个整形变量的自增。

第二个需要仔细分析的就是ts_allocate_id，编写过PHP扩展的朋友对这个函数肯定不陌生，这个函数用于在多线程环境下申请一个全局变量并返回资源ID。

```c
/* allocates a new thread-safe-resource id */
TSRM_API ts_rsrc_id ts_allocate_id(ts_rsrc_id *rsrc_id, size_t size, ts_allocate_ctor ctor, ts_allocate_dtor dtor)
{
    int i;
 
    TSRM_ERROR((TSRM_ERROR_LEVEL_CORE, "Obtaining a new resource id, %d bytes", size));
 
    tsrm_mutex_lock(tsmm_mutex);
 
    /* obtain a resource id */
    *rsrc_id = TSRM_SHUFFLE_RSRC_ID(id_count++);
    TSRM_ERROR((TSRM_ERROR_LEVEL_CORE, "Obtained resource id %d", *rsrc_id));
 
    /* store the new resource type in the resource sizes table */
    if (resource_types_table_size < id_count) {
        resource_types_table = (tsrm_resource_type *) realloc(resource_types_table, sizeof(tsrm_resource_type)*id_count);
        if (!resource_types_table) {
            tsrm_mutex_unlock(tsmm_mutex);
            TSRM_ERROR((TSRM_ERROR_LEVEL_ERROR, "Unable to allocate storage for resource"));
            *rsrc_id = 0;
            return 0;
        }
        resource_types_table_size = id_count;
    }
    resource_types_table[TSRM_UNSHUFFLE_RSRC_ID(*rsrc_id)].size = size;
    resource_types_table[TSRM_UNSHUFFLE_RSRC_ID(*rsrc_id)].ctor = ctor;
    resource_types_table[TSRM_UNSHUFFLE_RSRC_ID(*rsrc_id)].dtor = dtor;
    resource_types_table[TSRM_UNSHUFFLE_RSRC_ID(*rsrc_id)].done = 0;
 
    /* enlarge the arrays for the already active threads */
    for (i=0; i<tsrm_tls_table_size; i++) {
        tsrm_tls_entry *p = tsrm_tls_table[i];
 
        while (p) {
            if (p->count < id_count) {
                int j;
 
                p->storage = (void *) realloc(p->storage, sizeof(void *)*id_count);
                for (j=p->count; j<id_count; j++) {
                    p->storage[j] = (void *) malloc(resource_types_table[j].size);
                    if (resource_types_table[j].ctor) {
                        resource_types_table[j].ctor(p->storage[j], &p->storage);
                    }
                }
                p->count = id_count;
            }
            p = p->next;
        }
    }
    tsrm_mutex_unlock(tsmm_mutex);
 
    TSRM_ERROR((TSRM_ERROR_LEVEL_CORE, "Successfully allocated new resource id %d", *rsrc_id));
    return *rsrc_id;
}
```
rsrc_id最终存放的就是新资源的ID。其实这个函数的一些实现方式让我比较费解，首先是返回ID的方式。因为rsrc_id是按引入传入的，所以最终也就应该包含资源ID，那么最后完全不必在return *rsrc_id，可以返回一个预订整数表示成功或失败（例如1成功，0失败），这里有点费两遍事的意思，而且多了一次寻址。另外“*rsrc_id = TSRM_SHUFFLE_RSRC_ID(id_count++); ”让我感觉很奇怪，因为TSRM_SHUFFLE_RSRC_ID被定义为“((rsrc_id)+1)”，那么这里展开就是：

    *rsrc_id = ((id_count++)+1)
为什么不写成这样呢：

    *rsrc_id = ++id_count
真是怪哉。

好的，且不管实现是否合理，我们先继续研究这个函数吧。

首先要将id_count自增，生成一个新的资源ID，然后为这个新资源创建一个tsrm_resource_type并放入resource_type_table，接着遍历所有线程（注意是所有）为每一个线程的tsrm_tls_entry分配这个线程全局变量需要的内存空间（p->storage[j] = (void *) malloc(resource_types_table[j].size); ）。

这里需要注意，对于每一次ts_allocate_id调用，Zend会遍历所有线程并为每一个线程分配相应资源，因为ts_allocate_id实际是在MINIT阶段被调用，而不是在请求处理阶段被调用的。换言之，TSRM会在进程建立时统一分配好线程全局资源，关于这个下文会专门描述。

抽象来看，可以将整个线程全局资源池看做一个矩阵，一个维度为线程，一个维度为id_count，因此任意时刻所有线程全局变量的数量为“线程数*id_count”。tsrm_tls_entry和tsrm_resource_type可以看做这个矩阵在两个维度上的索引。

通过分析可以看出，每次调用ts_allocate_id的代价是很大的，由于ts_allocate_id并没有预先分配算法，每次在id_count维度申请一个新的变量，就涉及两次realloc和N次malloc（N为线程数），申请M个全局变量的代价为：

2 * M * t(realloc) + N * M * t(malloc)

因此要尽量减少ts_allocate_id的调用次数。正因这个原因，在PHP扩展开发中提倡将一个模块所需的全局变量声明为一个结构体然后一次性申请，而不要分开申请。

### ZTS与生命周期

这里需要简单提一下PHP的生命周期。

PHP的具体生命周期模式取决于sapi的实现，但一般都会有MINIT、RINIT、SCRIPT、RSHUTDOWN和MSHUTDOWN五个典型阶段，不同的只是各个阶段的执行次数不同。例如在CLI或CGI模式下，这五个阶段顺序执行一次，而在Apache或FastCGI模式下往往一个MINIT和MSHUTDOWN中间对应多个RINIT、SCRIPT、RSHUTDOWN。关于PHP生命周期的话题我回头写文单独研究，这里只是简单说一下。

MINIT和MSHUTDOWN是PHP Module的初始化和清理阶段，往往在进程起始后和结束前执行，在这两个阶段各个模块的MINIT和MSHUTDOWN方法会被调用。而RINIT、SCRIPT、RSHUTDOWN是每一次请求都会触发的一个小周期。在多线程模式中，PHP的生命周期如下：

![][7]

在这种模式下，进程启动后仅执行一次MINIT。之所以要强调这一点，是因为TSRM的全局变量资源分配就是在MINIT阶段完成的，后续阶段只获取而不会再请求新的全局变量，这就不难理解为什么在ts_allocate_id中每次id_count加一需要遍历所有线程为每个线程分配相同的资源。到这里，终于可以看清TSRM分配线程全局变量的全貌：

进程启动后，在MINIT阶段启动TSRM（通过sapi调用tsrm_startup），然后在遍历模块时调用每一个模块的MINIT方法，模块在MINIT中告知TSRM要申请多少全局变量及大小（通过ts_allocate_id），TSRM在内存池中分配并做好登记工作（tsrm_tls_table和resource_types_table），然后将凭证（资源ID）返回给模块，告诉模块以后拿着这个凭证来取你的全局变量。

### ZTS在单线程和多线程环境的选择性编译

上文说过，很多情况下PHP还是被用于单线程环境，这时如果还是遵循上述行为，显然过于折腾。因为在单线程环境下不存在线程安全问题，全局变量只要简单声明使用就好，没必要搞那么一大堆动作。PHP的设计者考虑到的这一点，允许在编译时指定是否开启多线程支持，只有当在configure是指定--enable-maintainer-zts选项或启用多线程sapi时，PHP才会编译线程安全的代码。具体来说，当启用线程安全编译时，一个叫ZTS的常量被定义，PHP代码在每个与线程安全相关的地方通过#ifdef检查是否编译线程安全代码。

在探究相关细节前我先说一些自己的看法，对于ZTS多线程和单线程环境选择性编译设计上，我个人觉得是非常失败的。因为良好的设计应该隔离变化，换言之ZTS有义务将选择性编译相关的东西隔离起来，而不让其污染到模块的编写，这个机制对模块开发应该是透明的。但是ZTS的设计者仿佛生怕大家不知道有这个东西，让其完全污染了整个PHP，模块开发者不得不面对一堆奇奇怪怪的TSRM宏，着实让人非常不爽。所以下面我就带着悲愤的心情研究一下这块内容。

为了看看模块是如何实现选择性编译代码的，我们建立一个空的PHP扩展模块。到PHP源码的ext目录下执行如下命令：

    ./ext_skel --extname=zts_research

ext_skel是一个脚手架程序，用于创建PHP扩展模块。此时会看到ext目录下多了个zts_research目录。ext_skel为为什么生成了一个模块的架子，并附带了很多提示性注释。在这个目录下找到php_zts_research.h并打开，比较有趣的是一下一段代码：
```
/* 
    Declare any global variables you may need between the BEGIN
    and END macros here:     
 
    ZEND_BEGIN_MODULE_GLOBALS(zts_research)
    long  global_value;
    char *global_string;
    ZEND_END_MODULE_GLOBALS(zts_research)
*/
```
很明显这里提示了定义全局变量的方法：用ZEND_BEGIN_MODULE_GLOBALS和ZEND_END_MODULE_GLOBALS两个宏包住所有全局变量。下面看一下这两个宏，这两个宏定义在Zend/zend_API.h文件里：
```
#define ZEND_BEGIN_MODULE_GLOBALS(module_name)     \
typedef struct _zend_##module_name##_globals {
#define ZEND_END_MODULE_GLOBALS(module_name)        \
} zend_##module_name##_globals;
```

原来这两个宏只是将一个模块的所有全局变量封装为一个结构体定义，名称为zend_module_name_globals。关于为什么要封装成结构体，上文有提到。

php_zts_research.h另外比较有意思的一处就是：

```c
#ifdef ZTS
#define ZTS_RESEARCH_G(v) TSRMG(zts_research_globals_id, zend_zts_research_globals *, v)
#else
#define ZTS_RESEARCH_G(v) (zts_research_globals.v)
#endif
```
zts_research_globals是zts_research模块全局变量结构的变量名称，类型为zend_module_name_globals，在哪定义的稍后会研究。这里ZTS_RESEARCH_G就是这个模块获取全局变量的宏，如果ZTS没有定义（非线程安全时），就直接从这个结构中获取相应字段，如果线程安全开启时，则使用TSRMG这个宏。

    #define TSRMG(id, type, element)   (((type) (*((void ***) tsrm_ls))[TSRM_UNSHUFFLE_RSRC_ID(id)])->element)
这个宏就不具体细究了，因为实在太难懂了，基本思想就是使用上文提到的TSRM机制从线程全局变量池中获取对应的数据，其中tsrm_ls可以看作是线程全局变量池的指针，获取变量的凭证就是资源ID。

看到这里可能还有点晕，例如zts_research_globals这个变量哪来的？zts_research_globals_id又是哪来的？为了弄清这个问题，需要打开ext/zts_research/zts_research.c这个文件，其中有这样的代码：

    /* If you declare any globals in php_zts_research.h uncomment this:
    ZEND_DECLARE_MODULE_GLOBALS(zts_research)
    */
提示很清楚，如果在php_zts_research.h中定义了任何全局变量则将这段代码的注释消除，看来这个ZEND_DECLARE_MODULE_GLOBALS宏就是关键了。然后在Zend/zend_API中有这样的代码：

```c
#ifdef ZTS
 
#define ZEND_DECLARE_MODULE_GLOBALS(module_name)                            \
ts_rsrc_id module_name##_globals_id;
 
/* code... */
 
#else
 
#define ZEND_DECLARE_MODULE_GLOBALS(module_name)                            \
zend_##module_name##_globals module_name##_globals;
 
/* code... */
 
#endif
```


当线程安全开启时，这里实际定义了一个整形的资源ID（ts_rsrc_id 被typedef定义为int），而当线程安全不开启时，则直接定义一个结构体。在这个模块中分别对应zts_research_globals_id和zts_research_globals。

到这里思路基本理顺了：如果ZTS没有被启用，则直接声明一个全局变量结构体，并直接通过存取其字段实现全局变量存取；如果ZTS开启，则定义一个整形变量作为资源ID，然后通过ts_allocate_id函数向TSRM申请一块内存放置结构体（需要程序员手工在MINIT函数中实现，脚手架生成的程序中没有），并通过TSRM存取数据。

最后一个疑问：tsrm_ls在哪里？如果通过上述方法从TSRM中取数据，那么一定要知道线程全局变量内存池的指针tsrm_ls。这就是我说过的最污染PHP的地方，如果你阅读过PHP源码或编写过模块，对以下四个宏肯定眼熟：TSRMLS_D，TSRMLS_DC，TSRMLS_DTSRMLS_C，TSRMLS_CC。实际在PHP内部每次定义方法或调用方法时都要在参数列表最后加上其中的一个宏，其实就是为了将tsrm_ls传给函数以便存取全局变量，这四个宏的定义如下：

```c
#ifdef ZTS
 
#define TSRMLS_D    void ***tsrm_ls
#define TSRMLS_DC   , TSRMLS_D
#define TSRMLS_C    tsrm_ls
#define TSRMLS_CC   , TSRMLS_C
 
#else
 
#define TSRMLS_D    void
#define TSRMLS_DC
#define TSRMLS_C
#define TSRMLS_CC
 
#endif
```


在没有开启ZTS时，四个宏被定义为空，但这时在定义PHP方法或调用方法时依旧将宏加在参数列表后，这是为了保持代码的一致性，当然，因为在非ZTS环境下根本不会用到tsrm_ls，所以没有任何问题。

### 总结

本文研究了PHP和Zend的线程安全模型，应该说我个人觉得Zend内核中ZTS的实现巧妙但不够优雅，但目前在开发PHP模块时总免不了常与之打交道。这块内容相对偏门，几乎没有资料对ZTS和TSRM进行详细解释，但是透彻了解ZTS机制对于在PHP模块开发中正确合理使用全局变量是很重要的。希望本文对读者有所帮助。

[0]: http://blog.codinglabs.org/articles/zend-thread-safety.html
[1]: http://blog.codinglabs.org/tag.html#PHP
[2]: http://blog.codinglabs.org/tag.html#线程安全
[3]: http://blog.codinglabs.org/tag.html#ZendEngine
[4]: ./img/zend-thread-safety1.png
[5]: ./img/zend-thread-safety2.png
[6]: ./img/zend-thread-safety3.png
[7]: ./img/zend-thread-safety4.png