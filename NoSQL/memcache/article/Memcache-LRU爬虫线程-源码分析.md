# Memcache-LRU爬虫线程-源码分析

作者  [简单方式][0] 已关注 2017.02.04 18:17*  字数 2292  

![][1]

memcached-version-1.4.25

### 介绍

memcache 中实现了内存管理模型用来存储数据，而在此基础上又实现了一套LRU爬虫模型来维护这些已使用的内存，因为如果一直使用这些内存而不去维护会占用大量的系统资源，所以提供这么一套机制来维护内存，目前有三条爬虫线程分别实现了维护item、维护slab区、维护LRU队列 等功能，由于这些爬虫线程是在内存模型基础上去实现的-[Memcache-内存模型-源码分析][2]-所以最好对内存模型有所了解.

### LRU 爬虫线程介绍

* [item 爬虫线程介绍][3]  
该爬虫线程的功能是自动删除过期的item，因为memcache默认是懒惰删除法，就是等客户端 get 这个 item 的时候去判断是否过期如果过期则删除但是如果客户端一直不去 get 这个 item，那么这个item就会一直占用资源不会被释放掉，所以本爬虫线程就是为了解决这个问题。
* [lru 爬虫线程介绍][4]  
每一个 slab[x] 对应三条队列，分别是 HOT_LRU、WARM_LRU、COLD_LRU 而该爬虫线程就是去不断调整这三条队列下的item链表，因为在获取一个item获取不到的时候，会去这三条队列下淘汰一个item来使用，所以需要不断的调整这三条队列，保证总被访问的item不被淘汰掉，不常访问或过期的item优先被淘汰.  
**HOT_LRU：** 新获取的item会添加在HOT_LRU队列，如果访问HOT_LRU队尾的item则挪到HOT_LRU队头，超出HOT_LRU队列限额之后在挪到COLD_LRU队列.  
**COLD_LRU：** 如果访问COLD_LRU队尾的item则挪到WARM_LRU队列。  
**WARM_LRU：** 如果访问WARM_LRU队尾的item则挪到WARM_LRU队头，超出WARM_LRU队列限额之后在挪到COLD_LRU队列。
* [slab 爬虫线程介绍][5]  
内存初始化会把内存划分成 slab[1~63]->chunk_1[1M]->item 这种的形式，每个slab区域( slab[1]=80K、slab[2]=120k )存放不同大小的item，但如果一直使用 slab[1] 这个内存区域，就会不断去内存池申请chunk[1M]，直到把内存池全部申请完，这样导致的后果就是如果想要在使用 slab[2] 这个内存区域，就无法在去内存池申请chunk[1M]，也就是无法在存储120k大小的item，只能一直使用80k的item，而该爬虫线程的作用就是解决上述出现的问题，如果发现每个slab区域空闲的item数量加在一起大于2.5个chunk，每个chunk由若干个item组成，也就是相当于有大于2.5个chunk是空闲的，这样则回收一个chunk，把回收的这个chunk放到slab[0]这个区域，这样的话就解决了上述所出现的问题如果去内存池申请chunk失败，则从slab[0]区域获取一个刚才回收的chunk来使用，所以本线程最终的作用就是维护内存块的.

### 源码实现

> 在 Memcache 启动的时候可以通过参数来控制是否启动爬虫线程

    int main(){
            //省略...
    
            //启动 item 爬虫线程
            if (start_lru_crawler && start_item_crawler_thread() != 0) {
                fprintf(stderr, "Failed to enable LRU crawler thread\n");
                exit(EXIT_FAILURE);
            }
    
            //启动 lru 维护爬虫线程
            if (start_lru_maintainer && start_lru_maintainer_thread() != 0) {
                fprintf(stderr, "Failed to enable LRU maintainer thread\n");
                return 1;
            }
    
            //启动 slab 维护爬虫线程
            if (settings.slab_reassign &&
                start_slab_maintenance_thread() == -1) {
                exit(EXIT_FAILURE);
            }
    
            //省略...
    }

> start_item_crawler_thread() 启动item爬虫线程

    int start_item_crawler_thread(void) {
        int ret;
    
        if (settings.lru_crawler)
            return -1;
        pthread_mutex_lock(&lru_crawler_lock);
        do_run_lru_crawler_thread = 1;
        //启动线程, 但是不会马上运行, 会处于挂起状态, 等待触发信号(后面介绍).
        if ((ret = pthread_create(&item_crawler_tid, NULL,
            item_crawler_thread, NULL)) != 0) {
            fprintf(stderr, "Can't create LRU crawler thread: %s\n",
                strerror(ret));
            pthread_mutex_unlock(&lru_crawler_lock);
            return -1;
        }
        // 等待 item_crawler_thread 线程启动完毕之后在退出
        pthread_cond_wait(&lru_crawler_cond, &lru_crawler_lock);
        pthread_mutex_unlock(&lru_crawler_lock);
    
        return 0;
    }

> start_slab_maintenance_thread() 启动slab维护爬虫线程

    int start_slab_maintenance_thread(void) {
        int ret;
        slab_rebalance_signal = 0;
        slab_rebal.slab_start = NULL;
    
        //在上面介绍的时候说过,如果空闲item超过2.5个chunk则回收一个chunk
        //在回收chunk的时候,会默认回收slab下第一个chunk,然后把该chunk下面
        //的item一个一个挪到后面空闲的其他chunk空间,这个值就是控制一次循环
        //最多移动多少个item,后面代码会有体现.
        char *env = getenv("MEMCACHED_SLAB_BULK_CHECK");
        if (env != NULL) {
            slab_bulk_check = atoi(env);
            if (slab_bulk_check == 0) {
                slab_bulk_check = DEFAULT_SLAB_BULK_CHECK;
            }
        }
    
        if (pthread_cond_init(&slab_rebalance_cond, NULL) != 0) {
            fprintf(stderr, "Can't intiialize rebalance condition\n");
            return -1;
        }
        pthread_mutex_init(&slabs_rebalance_lock, NULL);
    
        //启动线程, 但是不会马上运行, 会处于挂起状态, 等待触发信号(后面介绍).
        if ((ret = pthread_create(&rebalance_tid, NULL,
                                  slab_rebalance_thread, NULL)) != 0) {
            fprintf(stderr, "Can't create rebal thread: %s\n", strerror(ret));
            return -1;
        }
        return 0;
    }

> start_lru_maintainer_thread() 启动lru维护爬虫线程

    int start_lru_maintainer_thread(void) {
        int ret;
    
        pthread_mutex_lock(&lru_maintainer_lock);
    
        //启动线程标识
        do_run_lru_maintainer_thread = 1;
        settings.lru_maintainer_thread = true;
    
        //启动线程
        if ((ret = pthread_create(&lru_maintainer_tid, NULL,
            lru_maintainer_thread, NULL)) != 0) {
            fprintf(stderr, "Can't create LRU maintainer thread: %s\n",
                strerror(ret));
            pthread_mutex_unlock(&lru_maintainer_lock);
            return -1;
        }
        pthread_mutex_unlock(&lru_maintainer_lock);
    
        return 0;
    }

注意上面的代码在启动完线程之后并不会马上去运行，而是处于挂起状态，至于什么时候运行这些线程需要等待触发信号，而这个触发信号就是在lru维护线程函数里面去调用相关代码处理判断符合条件之后触发的，也就相当于通过lru维护线程函数统一去调度，看下面的代码.

> lru_maintainer_thread() lru维护线程函数

    static void *lru_maintainer_thread(void *arg) {
        int i;
        //每次循环执行之后延时时间
        useconds_t to_sleep = MIN_LRU_MAINTAINER_SLEEP;
        rel_time_t last_crawler_check = 0;
    
        pthread_mutex_lock(&lru_maintainer_lock);
        if (settings.verbose > 2)
            fprintf(stderr, "Starting LRU maintainer background thread\n");
    
        //死循环,不断循环执行
        while (do_run_lru_maintainer_thread) {
            int did_moves = 0;
            pthread_mutex_unlock(&lru_maintainer_lock);
    
            //每次while循环之后延迟执行时间
            usleep(to_sleep);
            pthread_mutex_lock(&lru_maintainer_lock);
    
            STATS_LOCK();
            stats.lru_maintainer_juggles++;
            STATS_UNLOCK();
    
            //搜索源代码发现lru_maintainer_check_clsid一直都等于0
            //所以默认应该不会命中该if条件
            if (lru_maintainer_check_clsid != 0) {
                did_moves = lru_maintainer_juggle(lru_maintainer_check_clsid);
                lru_maintainer_check_clsid = 0;
            } else {
                //循环获取 slab id 然后依次调用
                for (i = POWER_SMALLEST; i < MAX_NUMBER_OF_SLAB_CLASSES; i++) {
                    did_moves += lru_maintainer_juggle(i);
                }
            }
    
            //did_moves 等于本次循环所有 slab[1-63] 区共移除多少个 item
            //然后根据移除数量确定下次while循环延迟执行时间
            if (did_moves == 0) {
                if (to_sleep < MAX_LRU_MAINTAINER_SLEEP)
                    to_sleep += 1000;
            } else {
                to_sleep /= 2;
                if (to_sleep < MIN_LRU_MAINTAINER_SLEEP)
                    to_sleep = MIN_LRU_MAINTAINER_SLEEP;
            }
    
            //判断是否开启了item爬虫线程
            if (settings.lru_crawler && last_crawler_check != current_time) {
                //如果开启了则调用该函数执行,判断是否符合触发item爬虫线程条件
                //如果符合条件则触发信号
                lru_maintainer_crawler_check();
                last_crawler_check = current_time;
            }
        }
        pthread_mutex_unlock(&lru_maintainer_lock);
        if (settings.verbose > 2)
            fprintf(stderr, "LRU maintainer thread stopping\n");
    
        return NULL;
    }

> lru_maintainer_juggle()

    static int lru_maintainer_juggle(const int slabs_clsid) {
        int i;
        int did_moves = 0;
        bool mem_limit_reached = false;
        unsigned int total_chunks = 0;
        unsigned int chunks_perslab = 0;
        unsigned int chunks_free = 0;
    
        // 获取 slabs_clsid 下有多少空闲的item
        // chunks_free    空闲 item 数量
        // total_chunks   总 item 数量(已使用+未使用)
        // chunks_perslab 该 slabs_clsid 下的 chunk 最多能包含多少个 item
        chunks_free = slabs_available_chunks(slabs_clsid, &mem_limit_reached,
                &total_chunks, &chunks_perslab);
        if (settings.expirezero_does_not_evict)
            total_chunks -= noexp_lru_size(slabs_clsid);
    
    
        //settings.slab_automove 默认等于0
        //这里的chunks_free判断就是上面说的触发slab爬虫线程的关键
        //当空闲的item大于2.5个chunk则执行回收流程,上面介绍过.
        if (settings.slab_automove > 0 && chunks_free > (chunks_perslab * 2.5)) {
            //调用该函数,触发信号,执行slab爬虫线程
            slabs_reassign(slabs_clsid, SLAB_GLOBAL_PAGE_POOL);
        }
    
        //循环1000次调整该slabs_clsid下面的三条队列item
        for (i = 0; i < 1000; i++) {
            int do_more = 0;
            if (lru_pull_tail(slabs_clsid, HOT_LRU, total_chunks, false, 0) ||
                lru_pull_tail(slabs_clsid, WARM_LRU, total_chunks, false, 0)) {
                do_more++;
            }
            do_more += lru_pull_tail(slabs_clsid, COLD_LRU, total_chunks, false, 0);
            //如果一个item都没有被移除则跳出
            if (do_more == 0)
                break;
            did_moves++;
        }
        return did_moves;
    }

可以看到上面的代码已经体现出来了，lru维护爬虫线程函数先运行，然后统一去处理并调用相关的函数，最后触发对应的爬虫线程去执行.

#### item 爬虫线程，相关代码分析

![][6]



item爬虫

> lru_maintainer_crawler_check() 

    static void lru_maintainer_crawler_check(void) {
        int i;
        //保存每个 slab_id 爬虫的开始状态
        static rel_time_t last_crawls[MAX_NUMBER_OF_SLAB_CLASSES];
        static rel_time_t next_crawl_wait[MAX_NUMBER_OF_SLAB_CLASSES];
    
        //循环所有slab_id
        for (i = POWER_SMALLEST; i < MAX_NUMBER_OF_SLAB_CLASSES; i++) {
            //获取每个slab_id的爬虫状态
            crawlerstats_t *s = &crawlerstats[i];
            //如果该 slab_id 下爬虫状态等于0则代表该slab id下没有爬虫
            //不等于0则代表有爬虫
            if (last_crawls[i] == 0) {
                //添加一个爬虫到该slab_id下的三条队列之前也说过,每个slab_id都有三条队列
                //但是注意是开启lru模式才会有三条队列,不开启的话只有一条
                if (lru_crawler_start(i, 0) > 0) {
                    //记录该slab_id爬虫的开始状态,就是当前时间而已
                    last_crawls[i] = current_time;
                }
            }
            pthread_mutex_lock(&lru_crawler_stats_lock);
    
            //如果该slab_id下的爬虫爬取完毕,会将s->run_complete设置为true
            //而里面的代码就是确认该 slab id 是否可以重新再添加爬虫,因为在
            //添加完爬虫的时候会更改爬虫状态last_crawls[i] = current_time
            //如果想重新在添加爬虫需要在置为0。
            //注意下:
            //因为当前这是一个循环,所以在添加完第一个slab_id下的爬虫之后,去通知爬虫线程处理
            //但是爬虫线程(有可能)并不会马上爬取完毕,所以这里的条件就不会马上为true,可能
            //会等到下次lru维护线程在调用本函数,然后再循环到该slab_id下才会看到效果.
            if (s->run_complete) {
                int x;
                //seen:  未过期的item数量
                //noexp: 永不过期的item数量
                uint64_t possible_reclaims = s->seen - s->noexp;
                uint64_t available_reclaims = 0;
    
                //计算未过期item的百分比
                uint64_t low_watermark = (s->seen / 100) + 1;
    
                //当前时间减去爬取结束时间,就是计算一下当前时间距离爬取结束时间相距多少秒
                rel_time_t since_run = current_time - s->end_time;
                /* Don't bother if the payoff is too low. */
                if (settings.verbose > 1)
                    fprintf(stderr, "maint crawler: low_watermark: %llu, possible_reclaims: %llu, since_run: %u\n",
                            (unsigned long long)low_watermark, (unsigned long long)possible_reclaims,
                            (unsigned int)since_run);
    
                for (x = 0; x < 60; x++) {
                    //(0 * 60) + 60 = 60
                    //(1 * 60) + 60 = 120
                    //(2 * 60) + 60 = 180
    
                    //相当于判断当前时间距离爬取结束时间是否大于60s、120s、180s、等
                    if (since_run < (x * 60) + 60)
                        break;
    
                    //在爬取每个item的时候判断这个item是否过期,如果这个item没有过期,就会记录这个item在之后多少秒内要过期
                    //然后根据对应的时间范围找到s->histo的位置+1,仔细看应该是跟上面的时间对应,如果距离爬取结束时间60s
                    //之后那么就取出刚才在爬取item时候记录的在之后60s内过期的item数量。
                    //s->histo[0] = 0~59s 
                    //s->histo[1] = 60~119s
                    //s->histo[2] = 120~239s
    
                    //相当于记录当前时间到爬取结束时间这个范围内共有多少item即将过期
                    available_reclaims += s->histo[x];
                }
    
                //判断过期的item数量是否大于未过期的item数量百分之一
                if (available_reclaims > low_watermark) {
                    //如果大于则重新置为0,这样下次循环的时候将重新添加爬虫去爬取.
                    last_crawls[i] = 0;
                    if (next_crawl_wait[i] > 60)
                        next_crawl_wait[i] -= 60;
    
                //如果上面条件不满足,就会走下面的条件.
                //考虑一种情况:
                //假如since_run未过期的item数量有100000,那么low_watermark计算出来就等于1001
                //而同样这些未过期的item过期时间都大于3600s,不在s->histo这个时间范围内那么
                //available_reclaims计算出来就会一直小于low_watermark不会命中上面条件
                } else if (since_run > 5 && since_run > next_crawl_wait[i]) {
                    last_crawls[i] = 0;
                    if (next_crawl_wait[i] < MAX_MAINTCRAWL_WAIT)  //MAX_MAINTCRAWL_WAIT = (60 * 60)
                        next_crawl_wait[i] += 60;
                }
                if (settings.verbose > 1)
                    fprintf(stderr, "maint crawler: available reclaims: %llu, next_crawl: %u\n", (unsigned long long)available_reclaims, next_crawl_wait[i]);
            }
            pthread_mutex_unlock(&lru_crawler_stats_lock);
        }
    }

> lru_crawler_start() 

    static int lru_crawler_start(uint32_t id, uint32_t remaining) {
        int starts;
        //这里说明下,刚才在上面的代码调用这个函数是在一个for循环,循环所有的 slab id
        //看着像是依次添加所有slab id下的爬虫,但是根据规则不是这样的,一次只能保证添加
        //成功一个slab id下的爬虫,因为第一个爬虫添加完之后爬虫线程就会运行然后加锁,而当
        //循环到第二个 slab id 的时候在调用本函数添加爬虫就会添加失败,因为获取不到锁了
        //只有等第一个 slab id 爬虫处理完毕之后释放了锁,才可以再添加,但是在添加哪个slab id
        //下的爬虫就看当前循环到那个slab id了(有点类似于随机添加了)
    
        //例如：
        //第一次循环所有slab id：
        // 【slab_id_1 添加成功、slab_id_2 添加失败、slab_id_3 添加失败、slab_id_4 添加成功(slab_id_1释放了锁)】
        //第二次循环所有slab id：
        // 【slab_id_1 不参与、slab_id_2 添加成功、slab_id_3 添加失败、slab_id_4 不参与】
        //第三次循环所有slab id：
        // 【slab_id_1 添加成功、slab_id_2不参与、slab_id_3添加成功(slab_id_1释放了锁)、slab_id_4 添加失败】
    
        //不参与:就是添加成功之后修改爬虫状态last_crawls[i]不等于0了,所以只有等重新置为0之后在参与.
    
        if (pthread_mutex_trylock(&lru_crawler_lock) != 0) {
            return 0;
        }
        //添加爬虫
        starts = do_lru_crawler_start(id, remaining);
        if (starts) {
            //添加完毕之后通知 item爬虫线程 运行
            pthread_cond_signal(&lru_crawler_cond);
        }
        pthread_mutex_unlock(&lru_crawler_lock);
        return starts;
    }

> do_lru_crawler_start()

    static int do_lru_crawler_start(uint32_t id, uint32_t remaining) {
        int i;
        uint32_t sid;
        uint32_t tocrawl[3];
        int starts = 0;
    
        //获取当前 slab id 下每个lru队列位置的索引
        tocrawl[0] = id | HOT_LRU;
        tocrawl[1] = id | WARM_LRU;
        tocrawl[2] = id | COLD_LRU;
    
        for (i = 0; i < 3; i++) {
            //获取第一个队列id
            sid = tocrawl[i];
            //只对当前slab id下的 sid队列加锁, 把锁的颗粒度尽可能的降低
            pthread_mutex_lock(&lru_locks[sid]);
            //判断队列是否有值
            if (tails[sid] != NULL) {
                if (settings.verbose > 2)
                    fprintf(stderr, "Kicking LRU crawler off for LRU %d\n", sid);
                //初始化一个爬虫item结构体
                crawlers[sid].nbytes = 0;
                crawlers[sid].nkey = 0;
                crawlers[sid].it_flags = 1; /* 1:开启爬虫  0:关闭爬虫 */
                crawlers[sid].next = 0;
                crawlers[sid].prev = 0;
                crawlers[sid].time = 0;
                crawlers[sid].remaining = remaining;
                crawlers[sid].slabs_clsid = sid;
                //把这个爬虫item插入到当前队列的尾部,因为到时候item爬虫线程
                //要去不断移动这个爬虫item,以达到获取到其他的item作用,直到移动
                //到队列头部结束.
                crawler_link_q((item *)&crawlers[sid]);
                //记录要处理的lru队列数
                crawler_count++;
                starts++;
            }
            pthread_mutex_unlock(&lru_locks[sid]);
        }
        if (starts) {
            //统计
            STATS_LOCK();
            stats.lru_crawler_running = true;
            stats.lru_crawler_starts++;
            STATS_UNLOCK();
            pthread_mutex_lock(&lru_crawler_stats_lock);
            memset(&crawlerstats[id], 0, sizeof(crawlerstats_t));
            //记录下开始时间
            crawlerstats[id].start_time = current_time;
            pthread_mutex_unlock(&lru_crawler_stats_lock);
        }
        return starts;
    }

##### 爬虫item结构体

跟正常的item结构体差不多，只不过这个爬虫item结构体只用做标识作用，因为这个爬虫item最终是要插入到要爬取的lru队列中，然后在队列里面去不断移动它，从后往前进行移动，移动到什么位置就固定在什么位置，不会随其他item删除和添加进行变化，所以就是起到一个标识定位作用，而且用到的主要字段也就是 next、prev、it_flags    typedef struct {
        struct _stritem *next;
        struct _stritem *prev;
        struct _stritem *h_next;    /* hash chain next */
        rel_time_t      time;       /* least recent access */
        rel_time_t      exptime;    /* expire time */
        int             nbytes;     /* size of data */
        unsigned short  refcount;
        uint8_t         nsuffix;    /* length of flags-and-length string */
        uint8_t         it_flags;   /* ITEM_* above */
        uint8_t         slabs_clsid;/* which slab class we're in */
        uint8_t         nkey;       /* key length, w/terminating null and padding */
        uint32_t        remaining;  /* Max keys to crawl per slab per invocation */
    } crawler;

> crawler_link_q() 爬虫item插入到lru队列尾部

    static void crawler_link_q(item *it) { /* item is the new tail */
        item **head, **tail;
        assert(it->it_flags == 1);
        assert(it->nbytes == 0);
    
        //获取 slabs_clsid 下的队列 head、tail 对应的 item 地址
        head = &heads[it->slabs_clsid];
        tail = &tails[it->slabs_clsid];
        assert(*tail != 0);
        assert(it != *tail);
        assert((*head && *tail) || (*head == 0 && *tail == 0));
    
        //更换一下位置
        it->prev = *tail;
        it->next = 0;
        if (it->prev) {
            assert(it->prev->next == 0);
            it->prev->next = it;
        }
        //插入到队列尾部
        *tail = it;
        if (*head == 0) *head = it;
        return;
    }

**上面已经把爬虫添加到对应的队列了，并且也已经信号通知 item爬虫线程了，剩下就是看爬虫线程如何去爬取了**

> item_crawler_thread() item爬虫线程函数

    static void *item_crawler_thread(void *arg) {
        int i;
        //延时执行时间,默认1000
        int crawls_persleep = settings.crawls_persleep; 
    
        pthread_mutex_lock(&lru_crawler_lock);
        pthread_cond_signal(&lru_crawler_cond);
        settings.lru_crawler = true;
        if (settings.verbose > 2)
            fprintf(stderr, "Starting LRU crawler background thread\n");
        while (do_run_lru_crawler_thread) {
    
        //这里就是在刚开始启动线程的时候处于挂起状态,直到上面添加完爬虫并且信号通知才会运行.
        pthread_cond_wait(&lru_crawler_cond, &lru_crawler_lock);
    
        //不断循环,直到处理完所有的队列crawler_count
        while (crawler_count) {
            item *search = NULL;
            void *hold_lock = NULL;
    
            //每次从0开始循环所有的队列,当那个队列发现有爬虫则处理
            //但是只会处理一次,也就是相当于每次只移动一下当前lru队列的item爬虫
            //然后继续循环处理第二个队列,在去移动第二条队列的item爬虫,为什么要这样
            //因为我们在处理这条队列的时候会加锁,如果从尾部不断移动爬虫item到头部
            //这个时间可能会比较长,同样锁的时间也会比较长,会导致其他线程在处理这个队列
            //的时候处于堵塞状态,大大降低的并发度,所以这样去实现,每个队列发现有爬虫之后
            //只处理移动一次,然后马上释放锁,等下次循环的时候再继续移动处理,降低锁的开销。
            for (i = POWER_SMALLEST; i < LARGEST_ID; i++) {
                if (crawlers[i].it_flags != 1) {
                    continue;
                }
                //加lru队列锁
                pthread_mutex_lock(&lru_locks[i]);
    
                //移动爬虫item,就是把当前爬虫item往上移动一位,然后把爬虫item下面的item返回
                //item_1 -> item_2 -> crawler_item
                //item_1 -> crawler_item -> item_2 
                search = crawler_crawl_q((item *)&crawlers[i]);
    
                //如果等于空则代表移动到头部了
                if (search == NULL ||
                    (crawlers[i].remaining && --crawlers[i].remaining < 1)) {
                    if (settings.verbose > 2)
                        fprintf(stderr, "Nothing left to crawl for %d\n", i);
                    //把当前lru队列的爬虫状态置为0
                    crawlers[i].it_flags = 0;
                    //已经处理完一条队列了,所以待处理的队列数减一
                    crawler_count--;
                    //把爬虫item从队列里面删除
                    crawler_unlink_q((item *)&crawlers[i]);
                    //解锁lru队列锁
                    pthread_mutex_unlock(&lru_locks[i]);
    
                    pthread_mutex_lock(&lru_crawler_stats_lock);
                    //更新下爬取结束时间
                    crawlerstats[CLEAR_LRU(i)].end_time = current_time;
                    //之前在lru_maintainer_crawler_check这个函数应该记得这个状态
                    //代表已经爬取完毕
                    crawlerstats[CLEAR_LRU(i)].run_complete = true;
                    pthread_mutex_unlock(&lru_crawler_stats_lock);
    
                    continue;
                }
    
                //获取hash值
                uint32_t hv = hash(ITEM_key(search), search->nkey);
    
                //对当前hash出的值加锁,就是段锁.
                if ((hold_lock = item_trylock(hv)) == NULL) {
                    pthread_mutex_unlock(&lru_locks[i]);
                    continue;
                }
    
                //引用+1,如果不等于1,则代表当前item可能正在忙
                if (refcount_incr(&search->refcount) != 2) {
                    refcount_decr(&search->refcount);
                    if (hold_lock)
                        item_trylock_unlock(hold_lock);
                    pthread_mutex_unlock(&lru_locks[i]);
                    continue;
                }
    
                pthread_mutex_lock(&lru_crawler_stats_lock);
                //主要做检查item是否过期删除操作处理等
                item_crawler_evaluate(search, hv, i);
                pthread_mutex_unlock(&lru_crawler_stats_lock);
    
                if (hold_lock)
                    item_trylock_unlock(hold_lock);
                pthread_mutex_unlock(&lru_locks[i]);
    
                //循环完一次,需要延时多久再继续,如果设置了的话
                if (crawls_persleep <= 0 && settings.lru_crawler_sleep) {
                    usleep(settings.lru_crawler_sleep);
                    crawls_persleep = settings.crawls_persleep;
                }
            }
        }
        if (settings.verbose > 2)
            fprintf(stderr, "LRU crawler thread sleeping\n");
        STATS_LOCK();
        stats.lru_crawler_running = false;
        STATS_UNLOCK();
        }
        pthread_mutex_unlock(&lru_crawler_lock);
        if (settings.verbose > 2)
            fprintf(stderr, "LRU crawler thread stopping\n");
    
        return NULL;
    }

> item_crawler_evaluate()

    static void item_crawler_evaluate(item *search, uint32_t hv, int i) {
        int slab_id = CLEAR_LRU(i);
        //获取slab_id下的爬虫统计信息
        crawlerstats_t *s = &crawlerstats[slab_id];
        itemstats[i].crawler_items_checked++;
    
        //判断当前item是否过期
        if ((search->exptime != 0 && search->exptime < current_time)
            || item_is_flushed(search)) {
            itemstats[i].crawler_reclaimed++;
            s->reclaimed++;
    
            if (settings.verbose > 1) {
                int ii;
                char *key = ITEM_key(search);
                fprintf(stderr, "LRU crawler found an expired item (flags: %d, slab: %d): ",
                    search->it_flags, search->slabs_clsid);
                for (ii = 0; ii < search->nkey; ++ii) {
                    fprintf(stderr, "%c", key[ii]);
                }
                fprintf(stderr, "\n");
            }
            if ((search->it_flags & ITEM_FETCHED) == 0) {
                itemstats[i].expired_unfetched++;
            }
            //释放资源
            do_item_unlink_nolock(search, hv);
            do_item_remove(search);
            assert(search->slabs_clsid == 0);
        } else {
            //如果没有过期item则记录数量,之前在lru_maintainer_crawler_check这个函数也有体现这块
            s->seen++;
            refcount_decr(&search->refcount);
    
            if (search->exptime == 0) {
                s->noexp++; //永不过期数量
            } else if (search->exptime - current_time > 3599) {
                s->ttl_hourplus++; //大于3600s之后过期数量
            } else {
                //记录在多久之后过期的item数量 例如: 1~59s、60~119s、120~179s
                //按对应的时间段记录
                rel_time_t ttl_remain = search->exptime - current_time;
                int bucket = ttl_remain / 60;
                s->histo[bucket]++;
            }
        }
    }

以上介绍的就是 Memcache 的 item爬虫 核心代码实现！！ 开启爬虫也确实有对应的好处，如果系统有大量过期的item不回收，占用大量资源不说，有可能还会导致item不够用的情况然后马上就会进行lru淘汰这就可能会导致把未过期的item淘汰，还有就是hash表会随着item增多不断的自动扩容，消耗更多的内存，而且也会加大其迁移工作量，毕竟一直没有删除这些过期的item，那么就会一直占用其位置，但开启爬虫也有不好的地方，加大服务器负担、占用内存、锁争抢、等问题。

#### slab 爬虫线程，相关代码分析

![][7]



Memcache-slab回收chunk

> 上面在 lru_maintainer_juggle 函数里有这么一块代码，就是触发slab维护爬虫线程的

    static int lru_maintainer_juggle(const int slabs_clsid) {
    
        //....
        chunks_free = slabs_available_chunks(slabs_clsid, &mem_limit_reached,
                &total_chunks, &chunks_perslab);
    
        if (settings.slab_automove > 0 && chunks_free > (chunks_perslab * 2.5)) {
            slabs_reassign(slabs_clsid, SLAB_GLOBAL_PAGE_POOL);
        }
    
        //....
    }

> slabs_reassign()

    enum reassign_result_type slabs_reassign(int src, int dst) {
        enum reassign_result_type ret;
        if (pthread_mutex_trylock(&slabs_rebalance_lock) != 0) {
            return REASSIGN_RUNNING;
        }
        //src : 要回收哪个 slab_id 下的 chunk
        //dst : 将回收的 chunk 移动到 slab_id = 0 的位置（这个是固定的位置）
        ret = do_slabs_reassign(src, dst);
        pthread_mutex_unlock(&slabs_rebalance_lock);
        return ret;
    }

> do_slabs_reassign()

    static enum reassign_result_type do_slabs_reassign(int src, int dst) {
        //是否 slab 线程正在工作,如果正在工作则不在通知该线程进行处理了
        if (slab_rebalance_signal != 0)
            return REASSIGN_RUNNING;
    
        if (src == dst)
            return REASSIGN_SRC_DST_SAME;
    
        /* Special indicator to choose ourselves. */
        if (src == -1) {
            src = slabs_reassign_pick_any(dst);
            /* TODO: If we end up back at -1, return a new error type */
        }
    
        if (src < POWER_SMALLEST        || src > power_largest ||
            dst < SLAB_GLOBAL_PAGE_POOL || dst > power_largest)
            return REASSIGN_BADCLASS;
    
        //如果该 slab id 下的 chunk 小于2块则不回收了.
        if (slabclass[src].slabs < 2)
            return REASSIGN_NOSPARE;
    
        //赋值全局变量，在slab爬虫线程中会获取
        //s_clsid 要回收的 slab id
        //d_clsid 回收之后移动到该 slab id 下
        slab_rebal.s_clsid = src;
        slab_rebal.d_clsid = dst;
    
        //修改状态,跟上面判断对应,代表已经通知slab爬虫线程了
        slab_rebalance_signal = 1;
        //通知slab爬虫线程信号
        pthread_cond_signal(&slab_rebalance_cond);
    
        return REASSIGN_OK;
    }

> slab_rebalance_thread slab 爬虫线程函数

    static void *slab_rebalance_thread(void *arg) {
        int was_busy = 0;
        /* So we first pass into cond_wait with the mutex held */
        mutex_lock(&slabs_rebalance_lock);
    
        //死循环
        while (do_run_slab_rebalance_thread) {
            //在第一次循环的时候会命中此条件,因为刚才上面的函数已经把该变量设置为1了
            if (slab_rebalance_signal == 1) {
                //获取当前要回收的 slab id 信息
                if (slab_rebalance_start() < 0) {
                    /* Handle errors with more specifity as required. */
                    slab_rebalance_signal = 0;
                }
                was_busy = 0;
            //如果slab_rebalance_start()函数执行成功会把slab_rebalance_signal修改成2
            //所以在下次循环的时候就会命中此条件
            } else if (slab_rebalance_signal && slab_rebal.slab_start != NULL) {
                //上面获取到要回收的 slab id 信息之后,马上去回收一块 chunk
                was_busy = slab_rebalance_move();
            }
            //是否回收完毕
            if (slab_rebal.done) {
                //做一些收尾清理工作
                slab_rebalance_finish();
            } else if (was_busy) {
                /* Stuck waiting for some items to unlock, so slow down a bit
                 * to give them a chance to free up */
                usleep(50);
            }
    
            //如果等于0重新处于挂起状态
            if (slab_rebalance_signal == 0) {
                /* always hold this lock while we're running */
                pthread_cond_wait(&slab_rebalance_cond, &slabs_rebalance_lock);
            }
        }
        return NULL;
    }

> slab_rebalance_start()

    static int slab_rebalance_start(void) {
        slabclass_t *s_cls;
        int no_go = 0;
    
        //只要对slab区操作就要加锁,这里的加锁是针对全局的 slab_id[1~63]
        //所以锁的开销稍微大一些
        pthread_mutex_lock(&slabs_lock);
    
        if (slab_rebal.s_clsid < POWER_SMALLEST ||
            slab_rebal.s_clsid > power_largest  ||
            slab_rebal.d_clsid < SLAB_GLOBAL_PAGE_POOL ||
            slab_rebal.d_clsid > power_largest  ||
            slab_rebal.s_clsid == slab_rebal.d_clsid)
            no_go = -2;
    
        //取出s_clsid信息
        s_cls = &slabclass[slab_rebal.s_clsid];
    
        //之前在内存模型源码分析文章中已经说明此函数了,就是申请一块内存来保存chunk地址
        //而这个就是对d_clsid这个区申请内存来保存chunk地址,因为最后chunk回收完都会移动d_clsid这个区
        if (!grow_slab_list(slab_rebal.d_clsid)) {
            no_go = -1;
        }
    
        //在判断一次当前slab下的 chunk 是否小于2块
        if (s_cls->slabs < 2)
            no_go = -3;
    
        if (no_go != 0) {
            pthread_mutex_unlock(&slabs_lock);
            return no_go; /* Should use a wrapper function... */
        }
    
        //获取s_cls下第一块chunk的开始地址
        slab_rebal.slab_start = s_cls->slab_list[0];
        //获取s_cls下第一块chunk的结束地址
        slab_rebal.slab_end   = (char *)slab_rebal.slab_start +
            (s_cls->size * s_cls->perslab);
        //移动下标,因为在回收chunk的时候,需要把这个chunk里面已使用的item
        //全部复制到其他的chunk内,好把当前的chunk腾出来,所以就需要靠这个
        //下标根据item的大小,不断的往后移动指针以达到获取每一个item的作用,直到结束。
        slab_rebal.slab_pos   = slab_rebal.slab_start;
        //回收状态 1:已回收 0:未回收
        slab_rebal.done       = 0;
    
        //更改状态为2
        slab_rebalance_signal = 2;
    
        if (settings.verbose > 1) {
            fprintf(stderr, "Started a slab rebalance\n");
        }
    
        //解锁
        pthread_mutex_unlock(&slabs_lock);
    
        STATS_LOCK();
        stats.slab_reassign_running = true;
        STATS_UNLOCK();
    
        return 0;
    }

> slab_rebalance_move() 

    static int slab_rebalance_move(void) {
        slabclass_t *s_cls;
        int x;
        int was_busy = 0;
        int refcount = 0;
        uint32_t hv;
        void *hold_lock;
        enum move_status status = MOVE_PASS;
    
        //加锁
        pthread_mutex_lock(&slabs_lock);
    
        //取出slab信息
        s_cls = &slabclass[slab_rebal.s_clsid];
    
        //这里默认一次只循环一次,也就是在移除chunk里面item的时候,一次只移除一个item
        //不过可以在启动的时候设定这个slab_bulk_check循环次数 
        //尽可能的还是把这个变量设置小一些,保证只循环一次就退出循环,因为这样可以减小锁的颗粒度
        //可以看到上面slab是全局锁,如果我们当前这个slab id一直占用锁,会导致其它的slab id
        //也都无法操作,所以这里每次循环处理完一个item之后马上释放锁,然后在获取锁在进行处理.
        for (x = 0; x < slab_bulk_check; x++) {
            hv = 0;
            hold_lock = NULL;
            //获取item
            item *it = slab_rebal.slab_pos;
            status = MOVE_PASS;
    
            //判断 it_flags 是不是不等于这两个状态组合,默认情况不会等于
            //会等到该item移除完毕之后才会赋值成这两个状态组合
            if (it->it_flags != (ITEM_SLABBED|ITEM_FETCHED)) {
                //是否空闲的item
                if (it->it_flags & ITEM_SLABBED) {
                    //把当前item从空闲s_cls->slots链表移动出来，因为我们要回收这个chunk
                    //所以这个chunk里面的item就不能在被当前slab引用了.
                    if (s_cls->slots == it) {
                        s_cls->slots = it->next;
                    }
                    if (it->next) it->next->prev = it->prev;
                    if (it->prev) it->prev->next = it->next;
                    //减一下空闲item数量
                    s_cls->sl_curr--;
                    status = MOVE_FROM_SLAB;
                //是否被使用的item
                } else if ((it->it_flags & ITEM_LINKED) != 0) {
                    //获取hash锁
                    hv = hash(ITEM_key(it), it->nkey);
                    if ((hold_lock = item_trylock(hv)) == NULL) {
                        //如果没有抢到锁,则代表这个item正在忙
                        status = MOVE_LOCKED;
                    } else {
                        //引用+1
                        refcount = refcount_incr(&it->refcount);
                        //如果等于 2 则代表目前没有其他线程在使用这个item
                        if (refcount == 2) {
                            //在判断一次是否被使用的item
                            if ((it->it_flags & ITEM_LINKED) != 0) {
                                //把这个被使用的item复制到其他chunk下
                                status = MOVE_FROM_LRU;
                            } else {
                                //如果不是则可能刚巧同一时间被删除了,所以改成正在忙的状态,下次循环再看一次
                                status = MOVE_BUSY;
                            }
                        } else {
                            if (settings.verbose > 2) {
                                fprintf(stderr, "Slab reassign hit a busy item: refcount: %d (%d -> %d)\n",
                                    it->refcount, slab_rebal.s_clsid, slab_rebal.d_clsid);
                            }
                            //如果引用+1不等于2则代表其他线程正在操作该item,正在忙
                            status = MOVE_BUSY;
                        }
                        /* Item lock must be held while modifying refcount */
                        if (status == MOVE_BUSY) {
                            //引用-1
                            refcount_decr(&it->refcount);
                            //释放hash锁
                            item_trylock_unlock(hold_lock);
                        }
                    }
                } else {
                    /* See above comment. No ITEM_SLABBED or ITEM_LINKED. Mark
                     * busy and wait for item to complete its upload. */
                    status = MOVE_BUSY;
                }
            }
    
            int save_item = 0;
            item *new_it = NULL;
            size_t ntotal = 0;
            switch (status) {
                case MOVE_FROM_LRU:
                    //当前item总占用字节数
                    ntotal = ITEM_ntotal(it);
                    //判断是否过期了
                    if ((it->exptime != 0 && it->exptime < current_time)
                        || item_is_flushed(it)) {
                        /* TODO: maybe we only want to save if item is in HOT or
                         * WARM LRU?
                         */
                        save_item = 0;
                    //去其他chunk下获取一个空闲的item
                    } else if ((new_it = slab_rebalance_alloc(ntotal, slab_rebal.s_clsid)) == NULL) {
                        save_item = 0;
                        slab_rebal.evictions_nomem++;
                    } else {
                        save_item = 1;
                    }
                    pthread_mutex_unlock(&slabs_lock);
                    if (save_item) {
                        //把当前的item内容copy到新的new_it下
                        memcpy(new_it, it, ntotal);
                        new_it->prev = 0;
                        new_it->next = 0;
                        new_it->h_next = 0;
                        /* These are definitely required. else fails assert */
                        new_it->it_flags &= ~ITEM_LINKED;
                        new_it->refcount = 0;
                        //把当前 item 引用全部释放掉
                        //在把新的 new_it 全部引用上
                        //这样就相当于把当前 item 移动copy到其他 chunk 下了,把当前 item 位置腾出来了
                        do_item_replace(it, new_it, hv);
                        slab_rebal.rescues++;
                    } else {
                        //把当前 item 引用全部释放掉
                        do_item_unlink(it, hv);
                    }
    
                    /*
    
                        如果仔细观察代码的会发现一个疑点,这个item在上面会引用+1操作 refcount_incr(&it->refcount)
                        但是在释放item的时候并没有先去引用减一,这就可能导致我们这个item不会被全部释放掉,因为全部释放掉
                        的条件是引用减一等于0才可以,但是很显然我们目前item的引用等于2,所以减一肯定不等于0,这样的话就只
                        会释放掉 hash表引用、lru队列引用,而不会重新把这个item加入到空闲的item链表slots去, 所以并没有全
                        部释放掉.
    
                        为什么要这样做?
    
                        实际上就是故意不让它全部释放掉的,如果全部释放掉,就会把我们这个item重新加入到空闲的item链表里面去
                        这就导致我们好不容易把当前这个正在使用的item复制到别的chunk下面去了,然后释放掉了,但是马上又被使用
                        了,因为加入到空闲item链表里面去了啊,所以故意引用+1之后不在引用-1,这就就能保证我们这个item不会再被
                        使用了,也就相当于把这个item彻底移除了.
    
                    */
    
                    //释放hash锁
                    item_trylock_unlock(hold_lock);
                    //释放slab锁
                    pthread_mutex_lock(&slabs_lock);
                    //把当前item占用的字节数从总占用字节数里面减去
                    s_cls->requested -= ntotal;
                case MOVE_FROM_SLAB:
                    it->refcount = 0;
                    it->it_flags = ITEM_SLABBED|ITEM_FETCHED; //更新flags状态
    #ifdef DEBUG_SLAB_MOVER
                    memcpy(ITEM_key(it), "deadbeef", 8);
    #endif
                    break;
                case MOVE_BUSY:
                case MOVE_LOCKED:
                    //记录一下正在忙的item数量
                    slab_rebal.busy_items++;
                    was_busy++;
                    break;
                case MOVE_PASS:
                    break;
            }
    
            //如果本次 item 正在忙没有移动走, 那么也会把指针移动到下个 item 的位置, 处理下个 item
            //等这一圈全部循环完之后,回过头来发现刚才有正在忙的item没有移动走,那么会再继续循环一轮
            //直到把chunk内所有item全部移除完毕。
            slab_rebal.slab_pos = (char *)slab_rebal.slab_pos + s_cls->size;
            if (slab_rebal.slab_pos >= slab_rebal.slab_end)
                break;
        }
    
        //判断是否处理完所有item
        if (slab_rebal.slab_pos >= slab_rebal.slab_end) {
            // 判断是否有正在忙的item
            if (slab_rebal.busy_items) {
                //如果有则重新把slab_pos指针重置到开始位置,然后重新一轮循环处理
                slab_rebal.slab_pos = slab_rebal.slab_start;
                STATS_LOCK();
                stats.slab_reassign_busy_items += slab_rebal.busy_items;
                STATS_UNLOCK();
                //清零
                slab_rebal.busy_items = 0;
            } else {
                //当前chunk内所有item移除完毕
                slab_rebal.done++;
            }
        }
    
        pthread_mutex_unlock(&slabs_lock);
    
        return was_busy;
    }

> slab_rebalance_finish() 

    static void slab_rebalance_finish(void) {
        slabclass_t *s_cls;
        slabclass_t *d_cls;
        int x;
        uint32_t rescues;
        uint32_t evictions_nomem;
        uint32_t inline_reclaim;
    
        pthread_mutex_lock(&slabs_lock);
    
        s_cls = &slabclass[slab_rebal.s_clsid];
        d_cls = &slabclass[slab_rebal.d_clsid];
    
        //因为我们回收了一个chunk所以把chunk数量减一
        s_cls->slabs--;
    
        //更新保存chunk地址,由于第一个chunk地址被回收,所以第二个chunk地址挪到数组的第一个位置,以此类推
        for (x = 0; x < s_cls->slabs; x++) {
            s_cls->slab_list[x] = s_cls->slab_list[x+1];
        }
        //把刚才回收的chunk地址保存到d_clsid下面
        d_cls->slab_list[d_cls->slabs++] = slab_rebal.slab_start;
    
        /* Don't need to split the page into chunks if we're just storing it */
        if (slab_rebal.d_clsid > SLAB_GLOBAL_PAGE_POOL) {
            memset(slab_rebal.slab_start, 0, (size_t)settings.item_size_max);
            split_slab_page_into_freelist(slab_rebal.slab_start,
                slab_rebal.d_clsid);
        }
    
        //因为已经回收完毕一个chunk,所以重置下
        slab_rebal.done       = 0;
        slab_rebal.s_clsid    = 0;
        slab_rebal.d_clsid    = 0;
        slab_rebal.slab_start = NULL;
        slab_rebal.slab_end   = NULL;
        slab_rebal.slab_pos   = NULL;
        evictions_nomem    = slab_rebal.evictions_nomem;
        inline_reclaim = slab_rebal.inline_reclaim;
        rescues   = slab_rebal.rescues;
        slab_rebal.evictions_nomem    = 0;
        slab_rebal.inline_reclaim = 0;
        slab_rebal.rescues  = 0;
    
        //爬虫运行状态重新改为0
        slab_rebalance_signal = 0;
    
        pthread_mutex_unlock(&slabs_lock);
    
        //统计
        STATS_LOCK();
        stats.slab_reassign_running = false;
        stats.slabs_moved++;
        stats.slab_reassign_rescues += rescues;
        stats.slab_reassign_evictions_nomem += evictions_nomem;
        stats.slab_reassign_inline_reclaim += inline_reclaim;
        STATS_UNLOCK();
    
        if (settings.verbose > 1) {
            fprintf(stderr, "finished a slab move\n");
        }
    }

**上面就是回收指定slab下chunk的流程，但是回收的这个chunk什么时候会在被使用到？ 其实在获取一个slab id下的item来使用的时候，如果空闲item链表被全部消耗没了，就会在去内存池申请一个chunk重新划分item，但是如果我们回收的chunk存在，就会优先去使用这个回收的chunk**

> do_slabs_newslab()

    //获取一个slab id下的 chunk
    static int do_slabs_newslab(const unsigned int id) {
        slabclass_t *p = &slabclass[id];
        slabclass_t *g = &slabclass[SLAB_GLOBAL_PAGE_POOL];
        int len = settings.slab_reassign ? settings.item_size_max
            : p->size * p->perslab;
        char *ptr;
    
        if ((mem_limit && mem_malloced + len > mem_limit && p->slabs > 0
             && g->slabs == 0)) {
            mem_limit_reached = true;
            MEMCACHED_SLABS_SLABCLASS_ALLOCATE_FAILED(id);
            return 0;
        }
    
        //get_page_from_global_pool 这个函数就是获取回收的chunk
        //如果没有就会调用 memory_allocate() 去内存池申请一个chunk
        //可以看到优先去使用回收的chunk
        if ((grow_slab_list(id) == 0) ||
            (((ptr = get_page_from_global_pool()) == NULL) &&
            ((ptr = memory_allocate((size_t)len)) == 0))) {
    
            MEMCACHED_SLABS_SLABCLASS_ALLOCATE_FAILED(id);
            return 0;
        }
    
        memset(ptr, 0, (size_t)len);
        split_slab_page_into_freelist(ptr, id);
    
        p->slab_list[p->slabs++] = ptr;
        MEMCACHED_SLABS_SLABCLASS_ALLOCATE(id);
    
        return 1;
    }

> get_page_from_global_pool()

    static void *get_page_from_global_pool(void) {
        //#define SLAB_GLOBAL_PAGE_POOL 0 
        //就是保存我们回收chunk的slab区
        slabclass_t *p = &slabclass[SLAB_GLOBAL_PAGE_POOL]; 
        if (p->slabs < 1) {
            return NULL;
        }
        //获取一个回收的chunk地址
        char *ret = p->slab_list[p->slabs - 1];
        p->slabs--;
    
        //返回地址
        return ret;
    }

以上介绍的就是 Memcache 的 slab爬虫 核心代码实现！！ 如果开启的话就会帮我们不断的维护slab下chunk块，不会出现所有的chunk都被一个slab区占用的情况因为当空闲item数量达到一定的阀值就会回收一个chunk做预留，如果其他slab区要使用就可以获取这个预留的来使用。

#### lru 维护爬虫线程, 相关代码分析

> 还是在 lru_maintainer_juggle 这个函数里面去调用相关代码处理，之前在介绍这个函数的时候会去触发slab维护爬虫线程，触发完了之后就会调用以下代码进行lru队列维护工作

    static int lru_maintainer_juggle(const int slabs_clsid) {
    
        //.......
    
        //循环1000次调整指定 slabs_clsid 下面的三条队列的 item
        for (i = 0; i < 1000; i++) {
            int do_more = 0;
    
            //先去调整HOT_LRU队列如果没有被移出的则马上在调整WARM_LRU队列
            if (lru_pull_tail(slabs_clsid, HOT_LRU, total_chunks, false, 0) ||
                lru_pull_tail(slabs_clsid, WARM_LRU, total_chunks, false, 0)) {
                do_more++;
            }
    
            //最后调整COLD_LRU队列
            do_more += lru_pull_tail(slabs_clsid, COLD_LRU, total_chunks, false, 0);
            //如果一个item都没有被移除则跳出
            if (do_more == 0)
                break;
    
            //调整次数
            did_moves++;
        }
    
        return did_moves;
    }

> lru_pull_tail() 调整lru队列链表

    static int lru_pull_tail(const int orig_id, const int cur_lru,
            const unsigned int total_chunks, const bool do_evict, const uint32_t cur_hv) {
        item *it = NULL;
        int id = orig_id;
        int removed = 0;
        if (id == 0)
            return 0;
    
        //下面for循环次数
        int tries = 5;
    
        item *search;
        item *next_it;
        void *hold_lock = NULL;
        unsigned int move_to_lru = 0;
        uint64_t limit;
    
        //通过当前 slab id 计算出当前 lru 队列的 id
        id |= cur_lru;
    
        //lru队列加锁
        pthread_mutex_lock(&lru_locks[id]);
        //获取当前队列最后一个item
        search = tails[id];
    
        //共循环5次,也就代表可以调整当前队列下5个item
        for (; tries > 0 && search != NULL; tries--, search=next_it) {
            //获取上一个item
            next_it = search->prev;
            if (search->nbytes == 0 && search->nkey == 0 && search->it_flags == 1) {
                /* We are a crawler, ignore it. */
                tries++;
                continue;
            }
            //获取hash值
            uint32_t hv = hash(ITEM_key(search), search->nkey);
            //加锁,如果加不上锁则代表别的线程正在访问该item,忽略
            if (hv == cur_hv || (hold_lock = item_trylock(hv)) == NULL)
                continue;
    
            //正常该item引用+1应该等于2,但是如果引用+1之后不等于2可能当前item就有问题了
            //因为有可能这种情况,当前一个线程获取一个item然后引用+1,但是由于某些原因,最
            //后结束的时候并没引用-1,所以这里判断就有可能不等于2而等于3的情况
            if (refcount_incr(&search->refcount) != 2) {
                itemstats[id].lrutail_reflocked++;
    
                //然后判断一下该item访问时间加上settings.tail_repair_time是否小于当前时间
                //如果小于当前时间则代表这个item在这一段时间内并没有引用-1,所以是个异常退
                //出的item
                if (settings.tail_repair_time &&
                        search->time + settings.tail_repair_time < current_time) {
                    itemstats[id].tailrepairs++;
                    search->refcount = 1;
                    //释放掉该item,就是删除
                    do_item_unlink_nolock(search, hv);
                    //hash解锁
                    item_trylock_unlock(hold_lock);
                    continue;
                }
            }
    
            //判断item是否过期
            if ((search->exptime != 0 && search->exptime < current_time)
                || item_is_flushed(search)) {
                itemstats[id].reclaimed++;
                if ((search->it_flags & ITEM_FETCHED) == 0) {
                    itemstats[id].expired_unfetched++;
                }
    
                //删除
                /* refcnt 2 -> 1 */ 
                do_item_unlink_nolock(search, hv);
                /* refcnt 1 -> 0 -> item_free */
                do_item_remove(search);
    
                //hash解锁
                item_trylock_unlock(hold_lock);
                removed++;
    
                /* If all we're finding are expired, can keep going */
                continue;
            }
    
            /* If we're HOT_LRU or WARM_LRU and over size limit, send to COLD_LRU.
             * If we're COLD_LRU, send to WARM_LRU unless we need to evict
             */
            switch (cur_lru) {
                case HOT_LRU:
                    //获取 HOT_LRU 队列的 item 比例阀值
                    limit = total_chunks * settings.hot_lru_pct / 100;
                case WARM_LRU:
                    //获取 WARM_LRU 队列的 item 比例阀值
                    limit = total_chunks * settings.warm_lru_pct / 100;
                    //如果超过阀值则把当前item移动到COLD_LRU队列
                    if (sizes[id] > limit) {
                        itemstats[id].moves_to_cold++;
                        //代表把当前item插入到COLD_LRU队列
                        move_to_lru = COLD_LRU;
                        //从当前队列删除
                        do_item_unlink_q(search);
                        //记录下当前item的指针
                        it = search;
                        //移除数量+1
                        removed++;
                        break;
                    //判断当前item是否正在活动的item
                    } else if ((search->it_flags & ITEM_ACTIVE) != 0) {
                        itemstats[id].moves_within_lru++;
                        //把正在活动的状态去除
                        search->it_flags &= ~ITEM_ACTIVE;
                        //把当前item从当前队列尾部删除然后重新插入到队头
                        //因为这个item被访问了是个热数据,所以移动到队头去
                        //防止当前队列超过阀值队尾的item被移动冷队列COLD_LRU
                        do_item_update_nolock(search);
                        //上面引用+1了所以调用这个函数只会引用-1操作,不会被释放掉
                        do_item_remove(search);
                        //hash解锁
                        item_trylock_unlock(hold_lock);
                    } else {
                        /* Don't want to move to COLD, not active, bail out */
                        it = search;
                    }
                    break;
                case COLD_LRU:
                    it = search; /* No matter what, we're stopping */
                    //如果do_evict为true则强制把当前item淘汰,这块一般用在
                    //获取一个空闲item获取不到的情况,然后强制逐出一个
                    if (do_evict) {
                        if (settings.evict_to_free == 0) {
                            /* Don't think we need a counter for this. It'll OOM.  */
                            break;
                        }
                        itemstats[id].evicted++;
                        itemstats[id].evicted_time = current_time - search->time;
                        if (search->exptime != 0)
                            itemstats[id].evicted_nonzero++;
                        if ((search->it_flags & ITEM_FETCHED) == 0) {
                            itemstats[id].evicted_unfetched++;
                        }
                        //删除
                        do_item_unlink_nolock(search, hv);
                        removed++;
                        if (settings.slab_automove == 2) {
                            slabs_reassign(-1, orig_id);
                        }
                    //判断当前item是否正在活动的item
                    } else if ((search->it_flags & ITEM_ACTIVE) != 0
                            && settings.lru_maintainer_thread) {
                        itemstats[id].moves_to_warm++;
                        //去掉活动状态
                        search->it_flags &= ~ITEM_ACTIVE;
                        //代表把当前item插入到WARM_LRU队列
                        move_to_lru = WARM_LRU;
                        //从当前队列删除
                        do_item_unlink_q(search);
                        removed++;
                    }
                    break;
            }
            if (it != NULL)
                break;
        }
    
        //lru队列解锁
        pthread_mutex_unlock(&lru_locks[id]);
    
        if (it != NULL) {
            //这块就是上面由当前队列移动到其他队列的状态值
            if (move_to_lru) {
                it->slabs_clsid = ITEM_clsid(it);
                //把当前item在lru队列的id修改至move_to_lru这个队列的id
                it->slabs_clsid |= move_to_lru;
                //插入队列
                item_link_q(it);
            }
            //引用-1或者直接删除
            do_item_remove(it);
            //hash解锁
            item_trylock_unlock(hold_lock);
        }
    
        return removed;
    }

以上介绍的就是 Memcache 的 lru维护爬虫 核心代码实现！！ 核心代码就是上面这个函数，不断的维护每个slab下的三条队列，前提是开启lru维护线程的情况，然后系统就会帮我们去不断的维护区分冷热数据，这样在强制淘汰item的时候，尽可能的不把热数据淘汰掉只淘汰冷数据，所以根据实际情况选择开启还是不开启即可。

### 结束

上面已经把 Memcache 这几个爬虫线程全部介绍完毕，想开启那个线程根据实际情况确认开启即可，这里系统可以自动去调用并通知对应的爬虫线程工作，但前提是开启lru维护线程的情况，由lru维护线程统一去调度，但同时也可以由客户端通过发送命令去触发对应的爬虫线程工作，我们上边讲的是在lru维护线程里面自动去触发其它的爬虫线程，也就相当于是系统自动去调用并触发，大概的流程就是先执行lru维护线程，然后触发slab维护爬虫线程，在触发item爬虫线程，最后执行lru队列维护就是这么一个流程，因为代码逻辑很难用文字表达特别清楚，所以以上有说错或者描述不准确的地方请告知，这里一定会及时修改。

[0]: /u/9642a0c8db39
[1]: ../img/2416964-6fab1585488960e3.jpg
[2]: http://www.jianshu.com/p/a824ae00d9bb
[3]: #1F
[4]: #2F
[5]: #3F
[6]: ../img/2416964-001299d85c8de9a2.png
[7]: ../img/2416964-a6cd3e474024f331.png