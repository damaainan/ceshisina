# [Redis源码分析（三十六）--- Redis中的11大优秀设计](http://blog.csdn.net/androidlushangderen/article/details/40918317)

原创  2014年11月08日 10:16:37


 坚持了一个月左右的时间，从最开始的对Redis的代码做分类，从struct结构体分析开始，到最后分析main主程序结束，中间，各大模块的代码逐个击破，学习，总之，收获了非常多，好久没有这么久的耐心把一个框架学透，学习一个框架，会用那只是小小的一部分，能把背后的原理吃透才是真功夫。在这个学习的最后阶段，是时候要来点干货了，我把这1个多月来的一些总结的一些比较好的代码，和设计思想总结出来了，原本想凑成10大精彩设计的，可后来感觉每个点都挺精彩的，还是做成了11大优秀设计，包证让你打开研究，这里无关语言，重在一种编程的思想和设计，希望大家能好好领会。(下面的排序无关紧要，我只是按照时间顺序下来。后面的链接为我写的相关文章，如果想具体了解，请点击请入)

1.hyperloglog基量统计算法的实现([http://blog.csdn.net/androidlushangderen/article/details/40683763][6])。说到这个，比较搞笑的一点是，我刚刚开始竟然以为是某种类型的日志，和slowLog一样，后来才明白，这是一种基量统计算法，类似的算法还有LLC，HLLC是他的升级版本。

2.zmalloc内存分配的重新实现(http://blog.csdn.net/androidlushangderen/article/details/40659331)。Redis的作者在内存分配上显然是早有准备，不会傻傻的还是调用系统的mallo和free方法，人家在这里做了一个小小的封装，便于管理者更方便的控制系统的内存，下面是一个小小的结构体的声明，看到这个大家估计会明白。

```c
    /* 调用zmalloc申请size个大小的空间 */
    void *zmalloc(size_t size) {
        //实际调用的还是malloc函数
        void *ptr = malloc(size+PREFIX_SIZE);
        
        //如果申请的结果为null，说明发生了oom,调用oom的处理方法
        if (!ptr) zmalloc_oom_handler(size);
    #ifdef HAVE_MALLOC_SIZE
        //更新used_memory的大小
        update_zmalloc_stat_alloc(zmalloc_size(ptr));
        return ptr;
    #else
        *((size_t*)ptr) = size;
        update_zmalloc_stat_alloc(size+PREFIX_SIZE);
        return (char*)ptr+PREFIX_SIZE;
    #endif
    }
```

3.multi事务操作(http://blog.csdn.net/androidlushangderen/article/details/40392209)。Redis中的事务操作给我一种焕然一新的感觉，作者在做此设计的时候，用到了key，和watch key的概念，一个key维护了一个所有watch他的所有Client列表，一个Client自身也拥有一个他所监视的所有key，如果一个key被touch了，所有同样见识此key的客户端的下一步操作统统失效，具体怎么实现，请猛点后面的链接。 

4.redis-benchmark性能测试(http://blog.csdn.net/androidlushangderen/article/details/40211907)。Redis在这里出现了一个性能统计的概念，比较高大上的感觉，与调用了很多latency延时类的方法，就是判断延时的情况来看性能的好坏的。

5.zipmap压缩结构的设计(http://blog.csdn.net/androidlushangderen/article/details/39994599)。Redis在内存处理上可谓是想尽了办法，ziplist压缩列表和zipmap压缩图就是非常典型的设计。与往常的结构体内直接放一个int64类型的整形变量，这样就占了8个字节，但是一般情况下，我们保存的数值都比较小，1个字节差不多就够了，所有就浪费了7个字节，所以zip压缩系列结构体，就可以动态分配字节应对不同的情况，这个设计非常精彩，要确定这个key-value 的位置，通过前面保留的长度做偏移量的定位。

6.sparkline微线图的重新设计(http://blog.csdn.net/androidlushangderen/article/details/39964591)。Redis的sparkline的出现应该又是帮我扫盲了，人家可以用字符串的形式输出一张类似折线图的表，利用了采集的很多歌Sample的样本点，这个类多用于分析统计中出现，比如latency.c延时分析类中用到了。

7.对象引用计数实现内存管理(http://blog.csdn.net/androidlushangderen/article/details/40716469)。我们知道管理对象的生命周期一般有2种方法，1个是根搜索法（JVM中用的就是这个），另一个就是引用计数法，而Redis就给我们对此方法的实现，下面是对象增引用和减少引用的实现:

```c
    /* robj对象增减引用计数,递增robj中的refcount的值 */
    void incrRefCount(robj *o) {
        //递增robj中的refcount的值
        o->refcount++;
    }
```

```c
    /* 递减robj中的引用计数，引用到0后，释放对象 */
    void decrRefCount(robj *o) {
        //如果之前的引用计数已经<=0了，说明出现异常情况了
        if (o->refcount <= 0) redisPanic("decrRefCount against refcount <= 0");
        if (o->refcount == 1) {
            //如果之前的引用计数为1，再递减一次，恰好内有被任何对象引用了，所以就可以释放对象了
            switch(o->type) {
            case REDIS_STRING: freeStringObject(o); break;
            case REDIS_LIST: freeListObject(o); break;
            case REDIS_SET: freeSetObject(o); break;
            case REDIS_ZSET: freeZsetObject(o); break;
            case REDIS_HASH: freeHashObject(o); break;
            default: redisPanic("Unknown object type"); break;
            }
            zfree(o);
        } else {
            //其他对于>1的引用计数的情况，只需要按常规的递减引用计数即可
            o->refcount--;
        }
    }
```
减少引用的方法实现是重点。 

8.fork子进程实现后台程序(http://blog.csdn.net/androidlushangderen/article/details/40266579)。fork创建子线程实现后台程序的操作，我还是第一次见能这么用的，以前完全不知道fork能怎么使用的，这次真的是涨知识了。里面关键的一点是fork方法在子线程和父线程中的返回值不同做处理，父线程返回子线程的PID号，在子线程中返回的是0.

```c
    /* 后台进行rbd保存操作 */
    int rdbSaveBackground(char *filename) {
        pid_t childpid;
        long long start;
    
        if (server.rdb_child_pid != -1) return REDIS_ERR;
    
        server.dirty_before_bgsave = server.dirty;
        server.lastbgsave_try = time(NULL);
    
        start = ustime();
        //利用fork()创建子进程用来实现rdb的保存操作
        //此时有2个进程在执行这段函数的代码，在子进行程返回的pid为0,
        //所以会执行下面的代码，在父进程中返回的代码为孩子的pid,不为0，所以执行else分支的代码
        //在父进程中放返回-1代表创建子进程失败
        if ((childpid = fork()) == 0) {
            //在这个if判断的代码就是在子线程中后执行的操作
            int retval;
    
            /* Child */
            closeListeningSockets(0);
            redisSetProcTitle("redis-rdb-bgsave");
            //这个就是刚刚说的rdbSave()操作
            retval = rdbSave(filename);
            if (retval == REDIS_OK) {
                size_t private_dirty = zmalloc_get_private_dirty();
    
                if (private_dirty) {
                    redisLog(REDIS_NOTICE,
                        "RDB: %zu MB of memory used by copy-on-write",
                        private_dirty/(1024*1024));
                }
            }
            exitFromChild((retval == REDIS_OK) ? 0 : 1);
        } else {
            //执行父线程的后续操作
            /* Parent */
            server.stat_fork_time = ustime()-start;
            server.stat_fork_rate = (double) zmalloc_used_memory() * 1000000 / server.stat_fork_time / (1024*1024*1024); /* GB per second. */
            latencyAddSampleIfNeeded("fork",server.stat_fork_time/1000);
            if (childpid == -1) {
                server.lastbgsave_status = REDIS_ERR;
                redisLog(REDIS_WARNING,"Can't save in background: fork: %s",
                    strerror(errno));
                return REDIS_ERR;
            }
            redisLog(REDIS_NOTICE,"Background saving started by pid %d",childpid);
            server.rdb_save_time_start = time(NULL);
            server.rdb_child_pid = childpid;
            updateDictResizePolicy();
            return REDIS_OK;
        }
        return REDIS_OK; /* unreached */
    }
```

9.long long 类型转为String类型方法([http://blog.csdn.net/androidlushangderen/article/details/40649623][8])。以前做过很多字符串转数值和数值转字符串的算法实现，也许你的功能是实现了，但是效率呢，但面对的是非常长的long long类型的数字时，效率可能会更低。Redis在这里给我们提供了一个很好的思路，平时我们/10的计算，再%1o求余数，人家直接来了个/100的，然后直接通过字符串数组和余数值直接的映射，进行计算。算法如下；

```c
    /* Convert a long long into a string. Returns the number of
     * characters needed to represent the number.
     * If the buffer is not big enough to store the string, 0 is returned.
     *
     * Based on the following article (that apparently does not provide a
     * novel approach but only publicizes an already used technique):
     *
     * https://www.facebook.com/notes/facebook-engineering/three-optimization-tips-for-c/10151361643253920
     *
     * Modified in order to handle signed integers since the original code was
     * designed for unsigned integers. */
    /* long long类型转化为string类型 */
    int ll2string(char* dst, size_t dstlen, long long svalue) {
        static const char digits[201] =
            "0001020304050607080910111213141516171819"
            "2021222324252627282930313233343536373839"
            "4041424344454647484950515253545556575859"
            "6061626364656667686970717273747576777879"
            "8081828384858687888990919293949596979899";
        int negative;
        unsigned long long value;
    
        /* The main loop works with 64bit unsigned integers for simplicity, so
         * we convert the number here and remember if it is negative. */
        /* 在这里做正负号的判断处理 */
        if (svalue < 0) {
            if (svalue != LLONG_MIN) {
                value = -svalue;
            } else {
                value = ((unsigned long long) LLONG_MAX)+1;
            }
            negative = 1;
        } else {
            value = svalue;
            negative = 0;
        }
    
        /* Check length. */
        uint32_t const length = digits10(value)+negative;
        if (length >= dstlen) return 0;
    
        /* Null term. */
        uint32_t next = length;
        dst[next] = '\0';
        next--;
        while (value >= 100) {
            //做值的换算
            int const i = (value % 100) * 2;
            value /= 100;
            //i所代表的余数值用digits字符数组中的对应数字代替了
            dst[next] = digits[i + 1];
            dst[next - 1] = digits[i];
            next -= 2;
        }
    
        /* Handle last 1-2 digits. */
        if (value < 10) {
            dst[next] = '0' + (uint32_t) value;
        } else {
            int i = (uint32_t) value * 2;
            dst[next] = digits[i + 1];
            dst[next - 1] = digits[i];
        }
    
        /* Add sign. */
        if (negative) dst[0] = '-';
        return length;
    }
```

10.正则表达式的实现算法([http://blog.csdn.net/androidlushangderen/article/details/40649623][8])。正则表达式在我们平时用的可是非常多的，可有多少知道，正则表达式是如何实现通过简单的模式进程匹配，背后的原理实现到底怎么样呢，为什么?就可以代表任何一个字符接着往后匹配，*代表的是所有字符，要实现这样一个算法，也不是那么容易的哦，Redis就实现了这么一个算法，算是捡到宝了吧。

```c
    /* Glob-style pattern matching. */
    /*支持glob-style的通配符格式,如*表示任意一个或多个字符,?表示任意字符,[abc]表示方括号中任意一个字母。*/
    int stringmatchlen(const char *pattern, int patternLen,
            const char *string, int stringLen, int nocase)
    {
        while(patternLen) {
            switch(pattern[0]) {
            case '*':
                while (pattern[1] == '*') {
                    //如果出现的是**，说明一定匹配
                    pattern++;
                    patternLen--;
                }
                if (patternLen == 1)
                    return 1; /* match */
                while(stringLen) {
                    if (stringmatchlen(pattern+1, patternLen-1,
                                string, stringLen, nocase))
                        return 1; /* match */
                    string++;
                    stringLen--;
                }
                return 0; /* no match */
                break;
            case '?':
                if (stringLen == 0)
                    return 0; /* no match */
                /* 因为？能代表任何字符，所以，匹配的字符再往后挪一个字符 */
                string++;
                stringLen--;
                break;
            case '[':
            {
                int not, match;
    
                pattern++;
                patternLen--;
                not = pattern[0] == '^';
                if (not) {
                    pattern++;
                    patternLen--;
                }
                match = 0;
                while(1) {
                    if (pattern[0] == '\\') {
                        //如果遇到转义符，则模式字符往后移一个位置
                        pattern++;
                        patternLen--;
                        if (pattern[0] == string[0])
                            match = 1;
                    } else if (pattern[0] == ']') {
                        //直到遇到另外一个我中括号，则停止
                        break;
                    } else if (patternLen == 0) {
                        pattern--;
                        patternLen++;
                        break;
                    } else if (pattern[1] == '-' && patternLen >= 3) {
                        int start = pattern[0];
                        int end = pattern[2];
                        int c = string[0];
                        if (start > end) {
                            int t = start;
                            start = end;
                            end = t;
                        }
                        if (nocase) {
                            start = tolower(start);
                            end = tolower(end);
                            c = tolower(c);
                        }
                        pattern += 2;
                        patternLen -= 2;
                        if (c >= start && c <= end)
                            match = 1;
                    } else {
                        if (!nocase) {
                            if (pattern[0] == string[0])
                                match = 1;
                        } else {
                            if (tolower((int)pattern[0]) == tolower((int)string[0]))
                                match = 1;
                        }
                    }
                    pattern++;
                    patternLen--;
                }
                if (not)
                    match = !match;
                if (!match)
                    return 0; /* no match */
                string++;
                stringLen--;
                break;
            }
            case '\\':
                if (patternLen >= 2) {
                    pattern++;
                    patternLen--;
                }
                /* fall through */
            default:
                /* 如果没有正则表达式的关键字符，则直接比较 */
                if (!nocase) {
                    if (pattern[0] != string[0])
                        //不相等，直接不匹配
                        return 0; /* no match */
                } else {
                    if (tolower((int)pattern[0]) != tolower((int)string[0]))
                        return 0; /* no match */
                }
                string++;
                stringLen--;
                break;
            }
            pattern++;
            patternLen--;
            if (stringLen == 0) {
                while(*pattern == '*') {
                    pattern++;
                    patternLen--;
                }
                break;
            }
        }
        if (patternLen == 0 && stringLen == 0)
            //如果匹配字符和模式字符匹配的长度都减少到0了，说明匹配成功了
            return 1;
        return 0;
    }
```

11.Redis的drand48()随机算法重实现([http://blog.csdn.net/androidlushangderen/article/details/40582189][9])。Redis随机算法的实现作为11大设计的最后一个，并不是说这个设计相比前面有多么的烂，因为我觉得比较有特点，我就追加了一个上去。由于Redis的作者考虑到随机算法的在不同的操作系统可能会表现出不同的特性，所以不建议采用math.rand()方法，而是基于drand48()的算法重新实现了一个。具体什么叫drand48().请猛点链接处。 好了，以上就是我印象中的Redis中比较优秀的设计。其实在Redis的很多还有很多优秀代码的痕迹，由于篇幅有限，等待着读者自己去学习，发现。

[0]: http://so.csdn.net/so/search/s.do?q=nosql数据库&t=blog
[1]: http://so.csdn.net/so/search/s.do?q=源码&t=blog
[2]: http://so.csdn.net/so/search/s.do?q=redis&t=blog
[3]: http://so.csdn.net/so/search/s.do?q=框架&t=blog
[4]: http://so.csdn.net/so/search/s.do?q=设计&t=blog
[5]: http://write.blog.csdn.net/postedit/40918317
[6]: http://blog.csdn.net/androidlushangderen/article/details/40683763
[7]: #
[8]: http://blog.csdn.net/androidlushangderen/article/details/40649623
[9]: http://blog.csdn.net/androidlushangderen/article/details/40582189