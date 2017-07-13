# Memcache-哈希表-源码分析

作者  [简单方式][0] 已关注 2016.10.30 21:51*  字数 898  

![][1]

memcached-version-1.4.25

### 介绍

memcache 的 hashtable 就是一块保存 item* 固定大小的内存区域，也就是一个固定大小的指针数组，在程序启动的时候会初始化这个 hashtable 数组的大小，主要核心涉及到的点就是 hashtable动态扩容、hashtable段锁等，对应的 hashtable 处理的文件为 hash.c 、hash.h 、 assoc.c、 assoc.h#### Memcache 哈希表

![][2]



memcache 哈希表

#### 源码实现

> 初始化设置 hash 算法，hash_init ()

    // 目前只提供2种hash算法 jenkins 和 murmur3
    enum hashfunc_type {
        JENKINS_HASH=0, MURMUR3_HASH  
    };
    //启动memcache的时候调用
    int hash_init(enum hashfunc_type type) {
        switch(type) {
            case JENKINS_HASH:
                hash = jenkins_hash; // jenkins_hash 函数指针
                settings.hash_algorithm = "jenkins";
                break;
            case MURMUR3_HASH:
                hash = MurmurHash3_x86_32;  // murmur3_hash 函数指针
                settings.hash_algorithm = "murmur3"; 
                break;
            default:
                return -1;
        }
        return 0;
    }

> 初始化 hashtable 表，assoc_init()

    settings.hashpower_init //hashtable的基准值，启动memcache的时候设定
    
    void assoc_init(const int hashtable_init) {
        // 如果存在则赋值 hashpower 没有的话就用默认的 hashpower = 16
        if (hashtable_init) {
            hashpower = hashtable_init;
        }
        // hashsize 根据传入的 hashpower 算出最终的 hashtable 长度
        // hashsize(n) -> #define hashsize(n) ((ub4)1<<(n)) 
        // 实际上就是进行右移运算，右移 hashpower 位 ( 1 << 16 = 65536 )
        // 申请内存 item** primary_hashtable -> hashtable头指针 
        primary_hashtable = calloc(hashsize(hashpower), sizeof(void *));
        if (! primary_hashtable) {
            fprintf(stderr, "Failed to init hashtable.\n");
            exit(EXIT_FAILURE);
        }
        STATS_LOCK();
        stats.hash_power_level = hashpower;
        stats.hash_bytes = hashsize(hashpower) * sizeof(void *); // hashtable 占了多少字节内存
        STATS_UNLOCK();
    }

### hashtable 段锁

在 memcache 初始化线程那部分，还会初始化一些互斥锁， 其中就包括了 hashtable 的段锁，什么是段锁，就是有可能会多个key对应一把锁，而不是每一个key都对应一把锁，因为不同的key可能hash出来的值一样，那么就都会对应这一把锁。

> 初始化 hashtable 锁

    // memcached_thread_init 函数其中一段代码
    
    void memcached_thread_init(int nthreads, struct event_base *main_base) {
        int         i;
        int         power;
    
        //.........
    
        //根据线程数设置 hashtable 锁的启动值 power 跟上面的 hashpower 同理
        if (nthreads < 3) {
            power = 10;
        } else if (nthreads < 4) {
            power = 11;
        } else if (nthreads < 5) {
            power = 12;
        } else {
            /* 8192 buckets, and central locks don't scale much past 5 threads */
            power = 13;
        }
        // power 小于 hashpower 因为 hashtable锁的数量 和 hashtable的数量 并不是一一对应的
        // 也就是说并不是 每一个hashtable的key都对应一把锁，memcache为了省内存，采用多个key
        // 对应一把锁，也就是段锁
        if (power >= hashpower) {
            fprintf(stderr, "Hash table power size (%d) cannot be equal to or less than item lock table (%d)\n", hashpower, power);
            fprintf(stderr, "Item lock table grows with `-t N` (worker threadcount)\n");
            fprintf(stderr, "Hash table grows with `-o hashpower=N` \n");
            exit(1);
        }
    
        //计算hashtable锁的长度, 默认4个线程 power = 12 、 1 << 12 = 4096/锁
        item_lock_count = hashsize(power); 
        item_lock_hashpower = power;
    
        //申请锁
        //之后这个item_locks锁并不会随着hashtable的扩容而扩容
        //也就是说无论之后hashtable变成多大，都会对应这 hashsize(power) -> 4096/锁 
        //这也会导致越来越多的key对应一把锁
        item_locks = calloc(item_lock_count, sizeof(pthread_mutex_t));
        if (! item_locks) {
            perror("Can't allocate item locks");
            exit(1);
        }
        //循环初始化item_locks锁
        for (i = 0; i < item_lock_count; i++) {
            pthread_mutex_init(&item_locks[i], NULL);
        }
    
        //.........
    }

> 至此通过上面的代码已经将 memcache hashtable 初始化完毕

### 插入 、 扩容 hashtable

memcache 的 hashtable 扩容处理的方式是，在程序启动阶段的时候开一个线程处于待命状态，当需要扩容的时候触发该线程，动态的进行扩容处理，而且在扩容操作的时候也不是表锁，而是利用上面说的段锁

###### 扩容流程:

在 hashtable 扩容的时候 memcache 会把当前 primary_hashtable （哈希表）复制一份给 old_hashtable（哈希表），然后对 primary_hashtable 进行扩容 (1 << hashpower + 1)，如果当前正处于 hashtable 扩容阶段， 同时有请求要访问 key，则会判断当前的 key - hash 之后的索引位置是否小于当前迁移的索引位置，如果小于则代表已经迁移到新的 primary_hashtable 的索引位置了，如果大于则代表还未迁移到则在新的 primary_hashtable，所以就还在老的 old_hashtable 位置操作，扩容完成之后会释放 old_hashtable，然后就全部都在 primary_hashtable 操作了。

###### 说明:

在迁移 hashtable 的时候，会从小到大一个一个索引位置进行迁移，而这个索引位置就是hash之后的值，所以在迁移每一个索引位置的时候都会对当前的索引位置加锁，这个锁用的就是上面说的段锁，但可能锁的数量有限，会出现很多索引位置共用同一个锁的情况。  
例如:  
0 & (4096-1) = 0 、4096 & (4096-1) = 0 、8192 & (4096-1) = 0 这几个hash索引位置都会用到 0 这把锁  
如果这个时候一个key正好要访问，同时这个key-hash之后的值正好跟我们迁移的这个索引位置对应则会堵塞，因为已经加锁，其他的key如果不命中我们正在迁移的这个位置则正常访问，至于访问 primary_hashtable 还是 old_hashtable 则依据上面（扩容流程）说的条件。

> 插入 hashtable 函数， assoc_insert()

    // hv = hash(key, nkey); 哈希值
    
    int assoc_insert(item *it, const uint32_t hv) {
        unsigned int oldbucket;
    
        //判断是否正在扩容
        if (expanding &&
            (oldbucket = (hv & hashmask(hashpower - 1))) >= expand_bucket)
        {
            //插入到hashtable
            it->h_next = old_hashtable[oldbucket];
            old_hashtable[oldbucket] = it;
        } else {
           //插入到hashtable
           it->h_next = primary_hashtable[hv & hashmask(hashpower)];
           primary_hashtable[hv & hashmask(hashpower)] = it;
        }
    
        // 加锁，防止多个线程同时触发扩容操作
        pthread_mutex_lock(&hash_items_counter_lock);
        hash_items++;
        // 判断是否需要扩容
        if (! expanding && hash_items > (hashsize(hashpower) * 3) / 2) {
            //触发扩容操作
            assoc_start_expand();
        }
        pthread_mutex_unlock(&hash_items_counter_lock);
    
        MEMCACHED_ASSOC_INSERT(ITEM_key(it), it->nkey, hash_items);
        return 1;
    }

> 触发扩容操作 ， assoc_start_expand()

    static void assoc_start_expand(void) {
        if (started_expanding)
            return;
    
        started_expanding = true;
        // 唤醒 hashtable 扩容线程
        pthread_cond_signal(&maintenance_cond);
    }

> hashtable 扩容线程函数，assoc_maintenance_thread()

    // 默认会堵塞在这个位置 pthread_cond_wait(&maintenance_cond, &maintenance_lock);
    // 直到上面 pthread_cond_signal 唤醒操作, 然后往下执行.
    
    static void *assoc_maintenance_thread(void *arg) {
    
        mutex_lock(&maintenance_lock);
    
        // 死循环
        while (do_run_maintenance_thread) {
            int ii = 0;
    
            /* 循环迁移 old_hashtable ->  primary_hashtable */
            for (ii = 0; ii < hash_bulk_move && expanding; ++ii) {
                item *it, *next;
                int bucket;
                void *item_lock = NULL;
    
                // 把当前正在迁移的索引位置加锁
                if ((item_lock = item_trylock(expand_bucket))) {
                        // 循环处理当前索引位置的所有item
                        // 因为在当前索引位置可能会存在多个item
                        // 就是hash冲突链式解决
                        for (it = old_hashtable[expand_bucket]; NULL != it; it = next) {
                            // 获取下一个item
                            next = it->h_next;
                            // 按照新的hashtable长度重新定位一个索引位置
                            bucket = hash(ITEM_key(it), it->nkey) & hashmask(hashpower);
                            // 赋值保存
                            it->h_next = primary_hashtable[bucket];
                            primary_hashtable[bucket] = it;
                        }
                        // 当前的索引位置item都迁移完毕之后，将之前的hashtable索引位置至空
                        old_hashtable[expand_bucket] = NULL;
                        // 更新当前索引位置
                        expand_bucket++;
                        // 判断是否全部迁移完毕
                        if (expand_bucket == hashsize(hashpower - 1)) {
                            expanding = false; // 关闭正在扩容hashtable状态
                            free(old_hashtable); // 释放 old_hashtable
                            STATS_LOCK();
                            stats.hash_bytes -= hashsize(hashpower - 1) * sizeof(void *);
                            stats.hash_is_expanding = 0;
                            STATS_UNLOCK();
                            if (settings.verbose > 1)
                                fprintf(stderr, "Hash table expansion done\n");
                        }
    
                } else {
                    // 如果加锁失败，可能存在别的线程正在操作hashtable当前索引位置的item
                    // 所以延时等待，直到抢到锁为止
                    usleep(10*1000);
                }
                // 释放锁
                if (item_lock) {
                    item_trylock_unlock(item_lock);
                    item_lock = NULL;
                }
            }
    
            if (!expanding) {
                /* We are done expanding.. just wait for next invocation */
                started_expanding = false;
                // 等待唤醒
                pthread_cond_wait(&maintenance_cond, &maintenance_lock);
                pause_threads(PAUSE_ALL_THREADS);
                // 扩容 hashtable 操作
                assoc_expand();
                //往下执行while循环
                pause_threads(RESUME_ALL_THREADS);
            }
        }
        return NULL;
    }

> 扩容 hashtable 函数，assoc_expand()

    static void assoc_expand(void) {
        // 保存一份当前的 hashtable
        old_hashtable = primary_hashtable;
    
        // 扩容，每次扩容 hashpower + 1 
        // (1 << 16) = 65536、(1 << 17) = 131072
        primary_hashtable = calloc(hashsize(hashpower + 1), sizeof(void *));
        if (primary_hashtable) {
            if (settings.verbose > 1)
                fprintf(stderr, "Hash table expansion starting\n");
            hashpower++; // hashpower++
            expanding = true; // 表示当前正在扩容
            expand_bucket = 0; // 当前迁移到primary_hashtable索引位置
            STATS_LOCK();
            stats.hash_power_level = hashpower;
            stats.hash_bytes += hashsize(hashpower) * sizeof(void *);
            stats.hash_is_expanding = 1;
            STATS_UNLOCK();
        } else {
            primary_hashtable = old_hashtable;
            /* Bad news, but we can keep running. */
        }
    }

> 查找 hashtable 函数，assoc_find()

    item *assoc_find(const char *key, const size_t nkey, const uint32_t hv) {
        item *it;
        unsigned int oldbucket;
    
        // 是否正处于扩容操作
        if (expanding &&
            (oldbucket = (hv & hashmask(hashpower - 1))) >= expand_bucket)
        {
            // 获取item
            it = old_hashtable[oldbucket];
        } else {
            // 获取item
            it = primary_hashtable[hv & hashmask(hashpower)];
        }
    
        item *ret = NULL;
        int depth = 0;
        //循环判断，因为hash冲突采用链式解决法，所以需要遍历这个链，找到key相等的
        while (it) {
            if ((nkey == it->nkey) && (memcmp(key, ITEM_key(it), nkey) == 0)) {
                ret = it;
                break;
            }
            it = it->h_next;
            ++depth;
        }
        MEMCACHED_ASSOC_FIND(key, nkey, depth);
        return ret;
    }

### 结束

以上就是 Memcache 的哈希表以及对应的操作，由于是多线程模型，所以无论是 (插入 、查找、 扩容) 都会对当前操作的key对应的索引位置加锁 item_locks，所以随着 hashtable 不断的扩大，锁的争抢也会越来越大，性能可能也会存在一些影响.

[0]: /u/9642a0c8db39
[1]: ../img/2416964-6fab1585488960e3.jpg
[2]: ../img/2416964-6a7e33314c810c03.png