# Memcache-内存模型-源码分析

作者  [简单方式][0] 已关注 2016.08.09 21:42*  字数 827  

![][1]



memcached-version-1.4.25

### 介绍

memcache 使用了 Slab Allocator 的内存分配机制， 按照预先规定的大小， 将待分配的内存划分不同的区域并分割成特定长度的块，每个区域块只存放相对应大小的数据，以达到解决内存碎片问题， 因为不断的 malloc() 不同大小的内存块会产生大量的内存碎片，所以 memcache 实现了自己的内存管理机制，下面就让我们看一下 memcache 内部是如何实现内存管理并划分不同长度的块.

### 数据结构

> 存放 key-value 数据的结构体 item

    typedef struct _stritem {
        struct _stritem *next;      /* next item */
        struct _stritem *prev;      /* prev item */
        struct _stritem *h_next;    /* hash chain next */
        rel_time_t      time;       /* least recent access */
        rel_time_t      exptime;    /* expire time */
        int             nbytes;     /* size of data */
        unsigned short  refcount;   /* 引用计数,只要有线程操作该item就会++1 */
        uint8_t         nsuffix;    /* length of flags-and-length string */
        uint8_t         it_flags;   /* ITEM_* above */
        uint8_t         slabs_clsid;/* which slab class we're in */
        uint8_t         nkey;       /* key length, w/terminating null and padding */
        /* this odd type prevents type-punning issues when we do
         * the little shuffle to save space when not using CAS. */
        union {
            uint64_t cas;
            char end;
        } data[];
        /* if it_flags & ITEM_CAS we have 8 bytes CAS */
        /* then null-terminated key */
        /* then " flags length\r\n" (no terminating null) */
        /* then data with terminating \r\n (no terminating null; it's binary!) */
    } item;

### slabclass 是什么？

memcache 内存模型会对初始化申请的 (内存区域) 进行切分，会切分成不同大小的item区域，比如切分成三块区域 item-24Byte -> item-48Byte -> item-96Byte 这样在每个切分的区域，只保存对应大小的item、而slabclass数组就是记录每个item区域的使用情况即详情.

### item 在对应大小的区域又是如何保存？

现在已经有对应大小的item区域了, 然后在该区域里面又会以 chunk 进行划分，默认每个chunk为1M，就是先有 slabclass 然后在每个 slabclass 指向区域划分chunk , 然后在chunk区域进行划分item  
例如:  
slabclass[1] -> chunk_1 -> [item-24Byte、item-24Byte、item-24Byte] chunk_2 -> [item-24Byte、item-24Byte、item-24Byte]> 记录每个item区域使用情况的结构体 slabclass 

    #define MAX_NUMBER_OF_SLAB_CLASSES (63 + 1)   slabclass 数组大小 , 最多不超过 64 
    static slabclass_t slabclass[MAX_NUMBER_OF_SLAB_CLASSES]; 
    
    typedef struct {
        unsigned int size;      /* item区域大小 */
    
        unsigned int perslab;   /* 每个chunk下可以保存item数量 */
    
        void *slots;            /* 空闲的item */
        unsigned int sl_curr;   /* 空闲的item数量 */
    
        unsigned int slabs;     /* chunk指针数组数量 */
    
        void **slab_list;       /* chunk指针数组 */
    
        unsigned int list_size; /* 预申请chunk指针数组的数量 */
    
        size_t requested; /* The number of requested bytes */
    } slabclass_t;

### memcache 内存模型

![][2]



memcache 内存模型

#### 三个主要的配置参数：

* settings.maxbytes 存放数据内存大小默认64M
* settings.factor 增长因子 1.25
* preallocate 是否预申请内存

#### 增长因子factor是什么?

因为 memcache 会对内存进行划分不同区域大小的块，但是会默认一个最小存放数据区域块大小 size = 80/Byte 而增长因子就是以最小区域块为基础，每次递增的倍数，但是最大递增不能超过 62 个且 size*factor < 1M，下面代码会有说明，就是保证我们最多有 62 个不同大小的内存区域块，每个区域块都是 factor 倍数，且最后一个区域块一定是 1M ， 所以我们可以根据实际使用情况来调节增长因子大小

> 例：  
> 按照默认 1.25 进行增长，一共初始化 43 个区域，且每个区域之间都是 1.25 倍数，倒数第二个区域乘于 1.25 一定小于 1M ， 因为最后一个区域等于 1M，这也说明Memcache存放数据的最大为1M.

![][3]



增长因子初始化内存区域大小

### 源码实现

> (一) slabs_init 初始化内存

    void slabs_init(const size_t limit, const double factor, const bool prealloc) {
        int i = POWER_SMALLEST - 1; //#define POWER_SMALLEST 1
    
        //最小数据块size
        //sizeof(item) 存放数据的结构体 = 32 
        //settings.chunk_size 默认存放物理数据大小 = 48
        //size = 48 + 32 = 80/Byte 
        unsigned int size = sizeof(item) + settings.chunk_size;
    
        //申请的内存总大小默认64M
        mem_limit = limit;
    
        //是否预申请一块内存区域,并直接指向该内存区域
        if (prealloc) {
            /* Allocate everything in a big chunk with malloc */
            mem_base = malloc(mem_limit);
            if (mem_base != NULL) {
                mem_current = mem_base;
                mem_avail = mem_limit;
            } else {
                //.......
            }
        }
    
        //slabclass数组置空
        memset(slabclass, 0, sizeof(slabclass));
    
        //按照 size * factor 填充 slabclass 数组 
        //不能超过 MAX_NUMBER_OF_SLAB_CLASSES - 1 &&  保证 size * factor 不能大于 settings.item_size_max
        while (++i < MAX_NUMBER_OF_SLAB_CLASSES-1 && size <= settings.item_size_max / factor) {
            /* Make sure items are always n-byte aligned */
            if (size % CHUNK_ALIGN_BYTES) //8字节对其
                size += CHUNK_ALIGN_BYTES - (size % CHUNK_ALIGN_BYTES);
    
            //每个slabclass组可存放item的大小
            slabclass[i].size = size;
            //每个chunk下可以保存item数量
            slabclass[i].perslab = settings.item_size_max / slabclass[i].size;
            //乘与增长因子继续填充
            size *= factor;
            //.....
        }
    
        //保存最后一个元素的索引位置
        power_largest = i;
        //保证slab组最后一个可存放的item大小为settings.item_size_max 也就是1M
        slabclass[power_largest].size = settings.item_size_max;
        slabclass[power_largest].perslab = 1;
        //.....
    
        //为测试提供的，模拟先占用多少内存
        /* for the test suite:  faking of how much we've already malloc'd */
        {
            char *t_initial_malloc = getenv("T_MEMD_INITIAL_MALLOC");
            if (t_initial_malloc) {
                mem_malloced = (size_t)atol(t_initial_malloc);
            }
        }
    
        //如果是预申请则按照每个 slabclass[i].size 区域大小去划分
        //chunk_1 -> [item-24Byte、item-24Byte、item-24Byte]
        //chunk_1 -> [item-48Byte、item-48Byte、item-48Byte]
        if (prealloc) {
            slabs_preallocate(power_largest);
        }
    }

> (二) slabs_preallocate 对预申请的内存进行划分 

    static void slabs_preallocate (const unsigned int maxslabs) {
        int i;
        unsigned int prealloc = 0;
    
        //循环执行
        for (i = POWER_SMALLEST; i < MAX_NUMBER_OF_SLAB_CLASSES; i++) {
            // 判断是否超出当前slabclass最大索引
            if (++prealloc > maxslabs)
                return;
            //一个一个进行划分
            if (do_slabs_newslab(i) == 0) {
                fprintf(stderr, "Error while preallocating slab memory!\n"
                   "If using -L or other prealloc options, max memory must be "
                    "at least %d megabytes.\n", power_largest);
                exit(1);
            }
        }
    }

> (三) do_slabs_newslab 根据每个slabclass区域大小进行划分

    static int do_slabs_newslab(const unsigned int id) {
    
        slabclass_t *p = &slabclass[id]; //根据索引取出slabclass
        slabclass_t *g = &slabclass[SLAB_GLOBAL_PAGE_POOL];
    
        // 获取待申请chunk大小，理论上每个 chunk <= 1M(1048576/Byte)
        // 但是有些情况 size * perslab 不会正好等于 1M 而是小于 1M
        // 那么我们按照1M申请就会有一些字节浪费掉.
        // 比如第一个slabclass的区域是 80/Byte 如果按每个chunk为1M 那么 perslab = 1M/80 = 13107/item 
        // 就是一个chunk里面会有13107个item , 但是 13107 * 80 = 1048560/Byte 小于 1M(1048576/Byte)
        // 所以这里的判断就是按照什么方式去申请这chunk空间，如果不想有字节浪费掉就    p->size * p->perslab
    
        int len = settings.slab_reassign ? settings.item_size_max
            : p->size * p->perslab;
    
        char *ptr;
    
        // 判断内存使用是否超过最大设定
        if ((mem_limit && mem_malloced + len > mem_limit && p->slabs > 0
             && g->slabs == 0)) {
            mem_limit_reached = true;
           MEMCACHED_SLABS_SLABCLASS_ALLOCATE_FAILED(id);
            return 0;
        }
    
        // grow_slab_list 获取chunk指针数组，就是 void **slab_list 、 list_size
        // get_page_from_global_pool 忽略.
        // memory_allocate 申请一块 chunk 区域,并更新内存使用量
        if ((grow_slab_list(id) == 0) ||
            (((ptr = get_page_from_global_pool()) == NULL) &&
            ((ptr = memory_allocate((size_t)len)) == 0))) {
    
            MEMCACHED_SLABS_SLABCLASS_ALLOCATE_FAILED(id);
            return 0;
        }
    
        // chunk指针初始化置空
        memset(ptr, 0, (size_t)len);
    
        // chunk区域有了，就在chunk中进行划分item
        split_slab_page_into_freelist(ptr, id);
    
        // 保存当前chunk的指针, 并更新 p->slabs++ 
        p->slab_list[p->slabs++] = ptr;
        MEMCACHED_SLABS_SLABCLASS_ALLOCATE(id);
    
        return 1;
    }

> (四) grow_slab_list 获取chunk指针数组，不存在则创建，存在且空间不够则扩容

    static int grow_slab_list (const unsigned int id) {
        slabclass_t *p = &slabclass[id];
        // 判断当前 chunk指针数组索引 是否等于 list_size 如果等于就会进行扩容
        // 初始化情况会等于
        if (p->slabs == p->list_size) {
            // 默认 slab_list 数组大小 16 
            // 之后在扩充每次2的倍数进行扩容
            size_t new_size =  (p->list_size != 0) ? p->list_size * 2 : 16;
            void *new_list = realloc(p->slab_list, new_size * sizeof(void *));
            if (new_list == 0) return 0;
            // 预申请 chunk 指针数组的数量
            p->list_size = new_size;
            // 指向该数组
            p->slab_list = new_list;
        }
       return 1;
    }

> (五) memory_allocate 申请一块 chunk 区域 , 并更新内存使用量

    static void *memory_allocate(size_t size) {
        void *ret;
    
        // 判断是否为预申请模式，如果不是则每次 malloc 申请 1M
        if (mem_base == NULL) {
            /* We are not using a preallocated large memory chunk */
           ret = malloc(size);
        } else {
    
            //当前内存使用位置
            ret = mem_current;
    
            // size 不能大于最大的mem_avail内存块
            if (size > mem_avail) {
                return NULL;
            }
    
            /* mem_current pointer _must_ be aligned!!! */
            if (size % CHUNK_ALIGN_BYTES) {
                size += CHUNK_ALIGN_BYTES - (size % CHUNK_ALIGN_BYTES);
            }
    
            // 获取一块size大小内存,并更新内存使用位置
            mem_current = ((char*)mem_current) + size;
    
            // 更新一下mem_avail，就是还剩多少内存
            if (size < mem_avail) {
                mem_avail -= size;
            } else {
               mem_avail = 0;
           }
        }
    
        //更新一下内存使用量， 就是已使用了多少内存 
        mem_malloced += size;
    
        // 返回当前申请的内存，也就是 chunk 区域 
        return ret;
    }

> (六) split_slab_page_into_freelist 根据给定的 chunk区域指针 进行划分item 

    static void split_slab_page_into_freelist(char *ptr, const unsigned int id) {
        slabclass_t *p = &slabclass[id];
        int x;
        // 当前chunk区域共有多少 perslab 就是 item
        for (x = 0; x < p->perslab; x++) {
            // 一个一个进行划分
            do_slabs_free(ptr, 0, id);
            ptr += p->size;
        }
    }

> (七) do_slabs_free 划分item

    static void do_slabs_free(void *ptr, const size_t size, unsigned int id) {
        slabclass_t *p;
        item *it;
    
        assert(id >= POWER_SMALLEST && id <= power_largest);
        if (id < POWER_SMALLEST || id > power_largest)
            return;
    
        MEMCACHED_SLABS_FREE(size, id, ptr);
        p = &slabclass[id];
    
        it = (item *)ptr; //强制转换成item结构体指针
        it->it_flags = ITEM_SLABBED; 
        it->slabs_clsid = 0;
        // 每一个item都已双向链表形式连接
        it->prev = 0;
        it->next = p->slots;
        if (it->next) it->next->prev = it;
    
        // slots 一直指向这个空闲item链表
        p->slots = it;
    
        // 更新一下当前可使用item数量
        p->sl_curr++;
        p->requested -= size;
        return;
    }

### 结束

上面介绍的函数就是Memcache启动的时候，初始化内存所涉及到的所有核心函数实现

[0]: /u/9642a0c8db39
[1]: ../img/2416964-6fab1585488960e3.jpg
[2]: ../img/2416964-d74bf66951078641.png
[3]: ../img/2416964-1b9c177b5bc3680c.png