# Redis源码解析——内存管理

 时间 2016-12-08 20:23:15  方亮的专栏

原文[http://blog.csdn.net/breaksoftware/article/details/53437634][2]



在 [《Redis源码解析——源码工程结构》][4] 一文中，我们介绍了Redis可能会根据环境或用户指定选择不同的内存管理库。在linux系统中，Redis默认使用jemalloc库。当然用户可以指定使用tcmalloc或者libc的原生内存管理库。本文介绍的内容是在这些库的基础上，Redis封装的功能。 [（转载请指明出于breaksoftware的csdn博客）][5]

## 统一函数名

首先Redis需要判断最终选择的内存管理库是否可以满足它的基础需求。比如Redis需要能够通过一个堆上分配的指针知晓其空间大小。但是并不是所有内存管理库的每个版本都有这个方法。于是对于不满足的就报错

    #if defined(USE_TCMALLOC)
    #define ZMALLOC_LIB ("tcmalloc-" __xstr(TC_VERSION_MAJOR) "." __xstr(TC_VERSION_MINOR))
    #include <google/tcmalloc.h>
    #if (TC_VERSION_MAJOR == 1 && TC_VERSION_MINOR >= 6) || (TC_VERSION_MAJOR > 1)
    #define HAVE_MALLOC_SIZE 1
    #define zmalloc_size(p) tc_malloc_size(p)
    #else
    #error "Newer version of tcmalloc required"
    #endif
    
    #elif defined(USE_JEMALLOC)
    #define ZMALLOC_LIB ("jemalloc-" __xstr(JEMALLOC_VERSION_MAJOR) "." __xstr(JEMALLOC_VERSION_MINOR) "." __xstr(JEMALLOC_VERSION_BUGFIX))
    #include <jemalloc/jemalloc.h>
    #if (JEMALLOC_VERSION_MAJOR == 2 && JEMALLOC_VERSION_MINOR >= 1) || (JEMALLOC_VERSION_MAJOR > 2)
    #define HAVE_MALLOC_SIZE 1
    #define zmalloc_size(p) je_malloc_usable_size(p)
    #else
    #error "Newer version of jemalloc required"
    #endif
    
    #elif defined(__APPLE__)
    #include <malloc/malloc.h>
    #define HAVE_MALLOC_SIZE 1
    #define zmalloc_size(p) malloc_size(p)
    #endif
    
    #ifndef HAVE_MALLOC_SIZE
    size_t zmalloc_size(void *ptr) {
        void *realptr = (char*)ptr-PREFIX_SIZE;
        size_t size = *((size_t*)realptr);
        /* Assume at least that all the allocations are padded at sizeof(long) by
         * the underlying allocator. */
        if (size&(sizeof(long)-1)) size += sizeof(long)-(size&(sizeof(long)-1));
        return size+PREFIX_SIZE;
    }
    #endif

上面这段代码除了判断内存库的支持能力，还顺带统一zmalloc_size方法的实现。其实需要统一的方法不止这一个。比如libc的malloc方法在jemalloc中叫做je_malloc，而在tcmalloc中叫tc_malloc。这些基础方法并不多，它们分别是单片内存分配的malloc方法、多片内存分配calloc方法、内存重分配的realloc方法和内存释放函数free。经过统一命令后，之后使用这些方法的地方就不用考虑基础库不同的问题了。

    #if defined(USE_TCMALLOC)
    #define malloc(size) tc_malloc(size)
    #define calloc(count,size) tc_calloc(count,size)
    #define realloc(ptr,size) tc_realloc(ptr,size)
    #define free(ptr) tc_free(ptr)
    #elif defined(USE_JEMALLOC)
    #define malloc(size) je_malloc(size)
    #define calloc(count,size) je_calloc(count,size)
    #define realloc(ptr,size) je_realloc(ptr,size)
    #define free(ptr) je_free(ptr)
    #endif

## 记录堆空间申请大小

Redis内存管理模块需要实时知道已经申请了多少空间，它通过一个全局变量保存：

    static size_t used_memory = 0;

由于内存分配可能发生在各个线程中，所以对这个数据的管理要做到原子性。但是不同平台原子性操作的方法不同，有的甚至不支持原子操作，这个时候Redis就要统一它们的行为

    pthread_mutex_t used_memory_mutex = PTHREAD_MUTEX_INITIALIZER;
    ……
    #if defined(__ATOMIC_RELAXED)
    #define update_zmalloc_stat_add(__n) __atomic_add_fetch(&used_memory, (__n), __ATOMIC_RELAXED)
    #define update_zmalloc_stat_sub(__n) __atomic_sub_fetch(&used_memory, (__n), __ATOMIC_RELAXED)
    #elif defined(HAVE_ATOMIC)
    #define update_zmalloc_stat_add(__n) __sync_add_and_fetch(&used_memory, (__n))
    #define update_zmalloc_stat_sub(__n) __sync_sub_and_fetch(&used_memory, (__n))
    #else
    #define update_zmalloc_stat_add(__n) do { \
        pthread_mutex_lock(&used_memory_mutex); \
        used_memory += (__n); \
        pthread_mutex_unlock(&used_memory_mutex); \
    } while(0)
    
    #define update_zmalloc_stat_sub(__n) do { \
        pthread_mutex_lock(&used_memory_mutex); \
        used_memory -= (__n); \
        pthread_mutex_unlock(&used_memory_mutex); \
    } while(0)
    
    #endif

一般来说，锁操作比原子操作慢。但是在不支持原子操作的系统上只能使用锁机制了。

但是作为一个基础库，它不能仅仅考虑到多线程的问题。比如用户系统上不支持原子操作，而用户也不希望拥有多线程安全特性（可能它只有一个线程在运行），那么上述接口在计算时就必须使用锁机制，这样对于性能有苛刻要求的场景是不能接受的。于是Redis暴露了一个方法用于让用户指定是否需要启用线程安全特性

    static int zmalloc_thread_safe = 0;
    
    void zmalloc_enable_thread_safeness(void) {
        zmalloc_thread_safe = 1;
    }

相应的，线程安全的方法update_zmalloc_stat_add和update_zmalloc_stat_free需要被封装，以满足不同模式：

    #define update_zmalloc_stat_alloc(__n) do { \
        size_t _n = (__n); \
        if (_n&(sizeof(long)-1)) _n += sizeof(long)-(_n&(sizeof(long)-1)); \
        if (zmalloc_thread_safe) { \
            update_zmalloc_stat_add(_n); \
        } else { \
            used_memory += _n; \
        } \
    } while(0)
    
    #define update_zmalloc_stat_free(__n) do { \
        size_t _n = (__n); \
        if (_n&(sizeof(long)-1)) _n += sizeof(long)-(_n&(sizeof(long)-1)); \
        if (zmalloc_thread_safe) { \
            update_zmalloc_stat_sub(_n); \
        } else { \
            used_memory -= _n; \
        } \
    } while(0)

之后我们在堆上分配释放空间时，就需要使用update_zmalloc_stat_alloc和update_zmalloc_stat_free方法实时更新堆空间申请的情况。而获取其值则需要下面的方法：

    size_t zmalloc_used_memory(void) {
        size_t um;
    
        if (zmalloc_thread_safe) {
    #if defined(__ATOMIC_RELAXED) || defined(HAVE_ATOMIC)
            um = update_zmalloc_stat_add(0);
    #else
            pthread_mutex_lock(&used_memory_mutex);
            um = used_memory;
            pthread_mutex_unlock(&used_memory_mutex);
    #endif
        }
        else {
            um = used_memory;
        }
    
        return um;
    }

## 内存分配和释放

之前我们讲过，Redis的内存分配库需要底层库支持通过堆上指针获取该空间大小的功能，但是一些低版本的内存管理库并不支持。针对这种场景Redis还是做了兼容，它设计的内存结构是Header+Body。在Header中保存了该堆空间Body的大小信息，而Body则用于返回给内存申请者。我们看下malloc的例子：

    void *zmalloc(size_t size) {
        void *ptr = malloc(size+PREFIX_SIZE);
    
        if (!ptr) zmalloc_oom_handler(size);
    #ifdef HAVE_MALLOC_SIZE
        update_zmalloc_stat_alloc(zmalloc_size(ptr));
        return ptr;
    #else
        *((size_t*)ptr) = size;
        update_zmalloc_stat_alloc(size+PREFIX_SIZE);
        return (char*)ptr+PREFIX_SIZE;
    #endif
    }

一开始时，zmalloc直接分配了一个比申请空间大的空间，这就意味着无论是否支持获取申请空间大小的内存库，它都一视同仁了——实际申请比用户要求大一点。

如果内存库支持，则通过zmalloc_size获取刚分配的空间大小，并累计到记录整个程序申请的堆空间大小上，然后返回申请了的地址。此时虽然用户申请的只是size的大小，但是实际给了size+PREFIX_SIZE的大小。

如果内存库不支持，则在申请的内存前sizeof(size_t)大小的空间里保存用户需要申请的空间大小size。累计到记录整个程序申请堆空间大小上的也是实际申请的大小。最后返回的是偏移了头大小的内存地址。此时用户拿到的空间就是自己要求申请的空间大小。

![][6]

多片分配空间的zcalloc函数实现也是类似的，稍微有点区别的是重新分配空间的zrealloc方法，它需要在统计程序以申请堆空间大小的数据上减去以前该块的大小，再加上新申请的空间大小

    void *zrealloc(void *ptr, size_t size) {
    #ifndef HAVE_MALLOC_SIZE
        void *realptr;
    #endif
        size_t oldsize;
        void *newptr;
    
        if (ptr == NULL) return zmalloc(size);
    #ifdef HAVE_MALLOC_SIZE
        oldsize = zmalloc_size(ptr);
        newptr = realloc(ptr,size);
        if (!newptr) zmalloc_oom_handler(size);
    
        update_zmalloc_stat_free(oldsize);
        update_zmalloc_stat_alloc(zmalloc_size(newptr));
        return newptr;
    #else
        realptr = (char*)ptr-PREFIX_SIZE;
        oldsize = *((size_t*)realptr);
        newptr = realloc(realptr,size+PREFIX_SIZE);
        if (!newptr) zmalloc_oom_handler(size);
    
        *((size_t*)newptr) = size;
        update_zmalloc_stat_free(oldsize);
        update_zmalloc_stat_alloc(size);
        return (char*)newptr+PREFIX_SIZE;
    #endif
    }

还有就是zfree函数的实现，它需要释放的空间起始地址要视库的支持能力决定。如果库不支持获取区块大小，则需要将传入的指针前移PREFIX_SIZE，然后释放该起始地址的空间。

    void zfree(void *ptr) {
    #ifndef HAVE_MALLOC_SIZE
        void *realptr;
        size_t oldsize;
    #endif
    
        if (ptr == NULL) return;
    #ifdef HAVE_MALLOC_SIZE
        update_zmalloc_stat_free(zmalloc_size(ptr));
        free(ptr);
    #else
        realptr = (char*)ptr-PREFIX_SIZE;
        oldsize = *((size_t*)realptr);
        update_zmalloc_stat_free(oldsize+PREFIX_SIZE);
        free(realptr);
    #endif
    }

最后我们看下Redis在内存分配时处理内存溢出的处理。它提供了一个接口，让用户处理内存溢出问题。当然它也有自己默认的处理逻辑：

    static void (*zmalloc_oom_handler)(size_t) = zmalloc_default_oom;
    
    static void zmalloc_default_oom(size_t size) {
        fprintf(stderr, "zmalloc: Out of memory trying to allocate %zu bytes\n",
            size);
        fflush(stderr);
        abort();
    }
    
    void zmalloc_set_oom_handler(void (*oom_handler)(size_t)) {
        zmalloc_oom_handler = oom_handler;
    }

## 获取进程内存信息

Redis不仅在代码层面要统计已申请的堆空间，还要通过其他方法获取本进程中一些内存信息。比如它要通过zmalloc_get_rss方法获取当前进程的实际使用物理内存。这个也要按系统支持来区分实现，比如支持/proc/%pid%/stat的使用：

    size_t zmalloc_get_rss(void) {
        int page = sysconf(_SC_PAGESIZE);
        size_t rss;
        char buf[4096];
        char filename[256];
        int fd, count;
        char *p, *x;
    
        snprintf(filename,256,"/proc/%d/stat",getpid());
        if ((fd = open(filename,O_RDONLY)) == -1) return 0;
        if (read(fd,buf,4096) <= 0) {
            close(fd);
            return 0;
        }
        close(fd);
    
        p = buf;
        count = 23; /* RSS is the 24th field in /proc/<pid>/stat */
        while(p && count--) {
            p = strchr(p,' ');
            if (p) p++;
        }
        if (!p) return 0;
        x = strchr(p,' ');
        if (!x) return 0;
        *x = '\0';
    
        rss = strtoll(p,NULL,10);
        rss *= page;
        return rss;
    }

如果支持使用task_for_pid方法的则使用：

    size_t zmalloc_get_rss(void) {
        task_t task = MACH_PORT_NULL;
        struct task_basic_info t_info;
        mach_msg_type_number_t t_info_count = TASK_BASIC_INFO_COUNT;
    
        if (task_for_pid(current_task(), getpid(), &task) != KERN_SUCCESS)
            return 0;
        task_info(task, TASK_BASIC_INFO, (task_info_t)&t_info, &t_info_count);
    
        return t_info.resident_size;
    }

获取完物理内存数据后，可以通过和累计的分配内存大小相除，算出内存使用效率：

    float zmalloc_get_fragmentation_ratio(size_t rss) {
        return (float)rss/zmalloc_used_memory();
    }

Redis源码说明上指出上述获取RSS信息的方法是不高效的。可以通过RedisEstimateRSS()方法高效获取。

除了上面这些方法，Redis还有获取已被修改的私有页面大小函数zmalloc_get_private_dirty以及获取物理内存（(RAM)）大小的zmalloc_get_memory_size方法。这些方法都是些系统性方法，我就不在这儿做说明了。


[2]: http://blog.csdn.net/breaksoftware/article/details/53437634

[4]: http://blog.csdn.net/breaksoftware/article/details/53435940
[5]: http://blog.csdn.net/breaksoftware/article/details/53437634
[6]: ./img/QzuUFny.png