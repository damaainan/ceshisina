# Redis源码剖析--快速列表quicklist

 时间 2016-12-20 22:49:37  ZeeCoder

_原文_[http://zcheng.ren/2016/12/19/TheAnnotatedRedisSourceQuicklist/][1]


在RedisObject这一篇博客中，有介绍到list结构的底层编码类型有OBJ_ENCODING_QUICKLIST，当时就发现这个底层数据结构被我遗漏了。昨天花了点时间补了补这个知识，看完发现这货就跟STL中的deque的思想一样，顿时觉得又是一个实现超级繁琐但很实用的数据结构。今天就带大家一起来看看这个“二合一”的数据结构。 

quicklist是Redis在3.2版本加入的新数据结构，其是list列表的底层数据结构。

## quicklist简介 

为什么说quicklist是“二合一”呢？如果你看过STL中的deque的实现，就会知道deque是由一个map中控器和一个数组组成的数据结构，它既具有链表头尾插入便捷的优点，又有数组连续内存存储，支持下标访问的优点。Redis中是采用sdlist和ziplist来实现quicklist的，其中sdlist充当map中控器的作用，ziplist充当占用连续内存空间数组的作用。quicklist本身是一个双向无环链表，它的每一个节点都是一个ziplist。为什么这么设计呢？

* 双向链表在插入节点上复杂度很低，但它的内存开销很大，每个节点的地址不连续，容易产生内存碎片。
* ziplist是存储在一段连续的内存上，存储效率高，但是它不利于修改操作，插入和删除数都很麻烦，复杂度高，而且其需要频繁的申请释放内存，特别是ziplist中数据较多的情况下，搬移内存数据太费时！

Redis综合了双向链表和ziplist的优点，设计了quicklist这个数据结构，使它作为list键的底层实现。接下来，就要考虑每一个ziplist中存放的元素个数。

* 如果每一个ziplist中的元素个数过少，内存碎片就会增多。可以按照极端情况双向链表来考虑。
* 如果每一个ziplist中的元素个数过多，那么ziplist分配大块连续内存空间的难度就增大，同样会影响效率。

Redis的配置文件中，给出了每个ziplist中的元素个数设定，考虑使用场景需求，我们可以选择不同的元素个数。该参数设置格式如下：

    list-max-ziplist-size -2
    

后面的数字可正可负，正、负代表不同函数，其中，如果参数为正，表示按照数据项个数来限定每个节点中的元素个数，比如3代表每个节点中存放的元素个数不能超过3；反之，如果参数为负，表示按照字节数来限定每个节点中的元素个数，它只能取-1~-5这五个数，其含义如下：

* -1 每个节点的ziplist字节大小不能超过4kb
* -2 每个节点的ziplist字节大小不能超过8kb
* -3 每个节点的ziplist字节大小不能超过16kb
* -4 每个节点的ziplist字节大小不能超过32kb
* -5 每个节点的ziplist字节大小不能超过64kb

另外，在quicklist的源码中提到了一个LZF的压缩算法，该算法用于对quicklist的节点进行压缩操作。list的设计目的是能够存放很长的数据列表，当列表很长时，必然会占用很高的内存空间，且list中最容易访问的是两端的数据，中间的数据访问率较低，于是就可以从这个出发点来进一步节省内存用于其他操作。Redis提供了一下的配置参数，用于表示中间节点是否压缩。

    list-compress-depth 0
    

参数list-compress-depth的取值和含义对应如下：

* 0 特殊值，表示不压缩
* 1 表示quicklist两端各有一个节点不压缩，中间的节点压缩
* 2 表示quicklist两端各有两个节点不压缩，中间的节点压缩
* 3 表示quicklist两端各有三个节点不压缩，中间的节点压缩
* 以此类推。

## quicklist的数据结构 

quicklist的数据结构定义在quicklist.c文件中。

    typedef struct quicklist {
        quicklistNode *head;        // 指向quicklist的头部
        quicklistNode *tail;        // 指向quicklist的尾部
        unsigned long count;        // 列表中所有数据项的个数总和
        unsigned int len;           // quicklist节点的个数，即ziplist的个数
        int fill : 16;              // ziplist大小限定，由list-max-ziplist-size给定
        unsigned int compress : 16; // 节点压缩深度设置，由list-compress-depth给定
    } quicklist;
    

每个quicklist结构占用32个字节的空间，下面来看看quicklist节点的数据结构。

    typedef struct quicklistNode {
        struct quicklistNode *prev;  // 指向上一个ziplist节点
        struct quicklistNode *next;  // 指向下一个ziplist节点
        unsigned char *zl;           // 数据指针，如果没有被压缩，就指向ziplist结构，反之指向quicklistLZF结构
        unsigned int sz;             // 表示指向ziplist结构的总长度(内存占用长度)
        unsigned int count : 16;     // 表示ziplist中的数据项个数
        unsigned int encoding : 2;   // 编码方式，1--ziplist，2--quicklistLZF
        unsigned int container : 2;  // 预留字段，存放数据的方式，1--NONE，2--ziplist
        unsigned int recompress : 1; // 解压标记，当查看一个被压缩的数据时，需要暂时解压，标记此参数为1，之后再重新进行压缩
        unsigned int attempted_compress : 1; // 测试相关
        unsigned int extra : 10; // 扩展字段，暂时没用
    } quicklistNode;
    

每个quicklistnode也占用32个字节，上面介绍了，每个节点的数据存放格式有ziplist和quicklistLZF，后者是一种采用LZF压缩算法压缩的数据结构，其定义如下：

    typedef struct quicklistLZF {
        unsigned int sz; // LZF压缩后占用的字节数
        char compressed[]; // 柔性数组，指向数据部分
    } quicklistLZF;
    

分析到这里，quicklist的大体结构以及呈现在我们面前了，请看下图，是不是豁然开朗：

![][4]

另外，quicklist还提供了迭代器结构以及指向ziplist中的节点结构

    // quicklist的迭代器结构
    typedef struct quicklistIter {
        const quicklist *quicklist;  // 指向所在quicklist的指针
        quicklistNode *current;  // 指向当前节点的指针
        unsigned char *zi;  // 指向当前节点的ziplist
        long offset; // 当前ziplist中的偏移地址
        int direction; // 迭代器的方向
    } quicklistIter;
    // 表示quicklist节点中ziplist里的一个节点结构
    typedef struct quicklistEntry {
        const quicklist *quicklist;  // 指向所在quicklist的指针
        quicklistNode *node;  // 指向当前节点的指针
        unsigned char *zi;  // 指向当前节点的ziplist
        unsigned char *value;  // 当前指向的ziplist中的节点的字符串值
        long long longval;  // 当前指向的ziplist中的节点的整型值
        unsigned int sz;  // 当前指向的ziplist中的节点的字节大小
        int offset;  // 当前指向的ziplist中的节点相对于ziplist的偏移量
    } quicklistEntry;
    

## quicklist基本接口 

Redis为每一个底层数据结构都提供了丰富的接口，以供上层数据结构调用，quicklist也不例外。为了篇幅不会过长，本博客仅仅剖析一下部分关键的接口的实现。

## 创建quicklist及其节点 

创建一个quicklist需要为其设定各种参数，其由quicklistCreate函数实现。

    quicklist *quicklistCreate(void){
        struct quicklist *quicklist;  // 声明指针
    
        quicklist = zmalloc(sizeof(*quicklist));  // 分配内存
        quicklist->head = quicklist->tail = NULL;   // 设定头尾指针
        quicklist->len = 0;  // 设定长度
        quicklist->count = 0;  // 设定数据项总和
        quicklist->compress = 0;  // 设定压缩深度
        quicklist->fill = -2;  // 设定ziplist大小限定
        return quicklist;
    }
    

创建完quicklist，接下来就是创建一个quicklist节点。

    REDIS_STATIC quicklistNode *quicklistCreateNode(void){
        quicklistNode *node;
        node = zmalloc(sizeof(*node));  // 申请内存
        node->zl = NULL;  // 初始化指向ziplist的指针
        node->count = 0;  // 初始化数据项个数
        node->sz = 0;  // 初始化ziplist大小
        node->next = node->prev = NULL;  // 初始化prev和next指针
        node->encoding = QUICKLIST_NODE_ENCODING_RAW;  // 初始化节点编码方式
        node->container = QUICKLIST_NODE_CONTAINER_ZIPLIST;  // 初始化存放数据的方式
        node->recompress = 0;  // 初始化再压缩标记
        return node;
    }
    

## PUSH操作 

quicklist最重要的操作就是首尾插入节点，此操作由quicklistPush函数实现。PUSH操作不管是头部还是尾部压入都包含两个步骤：

* 如果插入节点中的ziplist大小没有超过限制（list-max-ziplist-size），那么直接调用ziplistPush函数压入
* 如果插入节点中的ziplist大小超过了限制，则新建一个quicklist节点（自然会创建一个新的ziplist），新的数据项会压入到新的ziplist，新的quicklist节点插入到原有的quicklist上

    // push操作，需要判断是头部插入还是尾部插入
    voidquicklistPush(quicklist *quicklist,void*value,constsize_tsz,
                       int where) {
        if (where == QUICKLIST_HEAD) {
            quicklistPushHead(quicklist, value, sz);
        } else if (where == QUICKLIST_TAIL) {
            quicklistPushTail(quicklist, value, sz);
        }
    }
    // 将新的数据项push到头部
    intquicklistPushHead(quicklist *quicklist,void*value,size_tsz){
        quicklistNode *orig_head = quicklist->head;
        // likely()是linux提供给程序员的编译优化方法
        // 目的是将“分支转移”的信息提供给编译器，这样编译器可以对代码进行优化，以减少指令跳转带来的性能下降
        // 此处表示节点没有满发生的概率比较大，也就是数据项直接插入到当前节点的可能性大，
        // likely()属于编译器级别的优化
        if (likely(
                // 判断该头部节点是否允许插入，计算头部节点中的大小和fill参数设置的大小相比较
                _quicklistNodeAllowInsert(quicklist->head, quicklist->fill, sz))) {
            // 执行到此，说明允许插入，直接调用ziplistpush插入节点即可
            quicklist->head->zl =
                ziplistPush(quicklist->head->zl, value, sz, ZIPLIST_HEAD);
            // 更新头部大小
            quicklistNodeUpdateSz(quicklist->head);
        } else {
            // 执行到此，说明头部节点已经满了，需要重新创建一个节点
            quicklistNode *node = quicklistCreateNode();
            // 将新节点压入新创建的ziplist中，并与新创建的quicklist节点关联起来
            node->zl = ziplistPush(ziplistNew(), value, sz, ZIPLIST_HEAD);
            // 更新大小
            quicklistNodeUpdateSz(node);
            // 将新创建的quicklist节点关联到quicklist中
            _quicklistInsertNodeBefore(quicklist, quicklist->head, node);
        }
        // 更新total数据项个数
        quicklist->count++;
        // 更新头结点的数据项个数
        quicklist->head->count++;
        // 如果尾部quicklist节点指针没变，返回0；
        // 反之返回1
        return (orig_head != quicklist->head);
    }
    // 将新数据项push到尾部
    intquicklistPushTail(quicklist *quicklist,void*value,size_tsz){
        quicklistNode *orig_tail = quicklist->tail;
        if (likely(
                // 判断该尾部节点是否允许插入，计算尾部节点中的大小和fill参数设置的大小相比较
                _quicklistNodeAllowInsert(quicklist->tail, quicklist->fill, sz))) {
            // 执行到此，说明允许插入，直接调用ziplistpush插入节点即可
            quicklist->tail->zl =
                // 将新数据项push到ziplist的尾部
                ziplistPush(quicklist->tail->zl, value, sz, ZIPLIST_TAIL);
            // 更新尾部节点大小
            quicklistNodeUpdateSz(quicklist->tail);
        } else {
            // 执行到此，说明尾部节点已经满了，需要重新创建一个节点
            quicklistNode *node = quicklistCreateNode();
            // 创建一个新的ziplist，并将新数据项插入，然后与新创建的quicklist节点关联起来
            node->zl = ziplistPush(ziplistNew(), value, sz, ZIPLIST_TAIL);
            // 更新该quicklist节点的大小
            quicklistNodeUpdateSz(node);
            // 将新创建的quicklist与quicklist关联起来
            _quicklistInsertNodeAfter(quicklist, quicklist->tail, node);
        }
        // 更新quicklist的数据项个数
        quicklist->count++;
        // 更新尾部节点的数据项个数
        quicklist->tail->count++;
        // 如果尾部quicklist节点指针没变，返回0；
        // 反之返回1
        return (orig_tail != quicklist->tail);
    }
    

## POP操作 

与PUSH操作对应的是POP操作，POP操作可以弹出首尾节点。

    // 接口函数，执行POP操作
    // 执行成功返回1，反之0
    // 如果弹出节点是字符串值，data，sz存放弹出节点的字符串值
    // 如果弹出节点是整型值，slong存放弹出节点的整型值
    int quicklistPop(quicklist *quicklist, int where, unsigned char **data,
                     unsigned int *sz, long long *slong) {
        unsigned char *vstr;
        unsigned int vlen;
        long long vlong;
        // 没有数据项，直接返回
        if (quicklist->count == 0)
            return 0;
        // 调用底层实现函数
        // 传入的_quicklistSaver是一个函数指针，用于深拷贝节点的值，用于返回
        int ret = quicklistPopCustom(quicklist, where, &vstr, &vlen, &vlong,
                                     _quicklistSaver);
        // 给data，sz，slong赋值
        if (data)
            *data = vstr;
        if (slong)
            *slong = vlong;
        if (sz)
            *sz = vlen;
        return ret;
    }
    // pop操作的底层实现函数
    // 执行成功返回1，反之0
    // 如果弹出节点是字符串值，data，sz存放弹出节点的字符串值
    // 如果弹出节点是整型值，slong存放弹出节点的整型值
    int quicklistPopCustom(quicklist *quicklist, int where, unsigned char **data,
                           unsigned int *sz, long long *sval,
                           void *(*saver)(unsigned char *data, unsigned int sz)) {
        unsigned char *p;
        unsigned char *vstr;
        unsigned int vlen;
        long long vlong;
        // 判断弹出位置，首部或者尾部
        int pos = (where == QUICKLIST_HEAD) ? 0 : -1;
        // 没有数据
        if (quicklist->count == 0)
            return 0;
        // 
        if (data)
            *data = NULL;
        if (sz)
            *sz = 0;
        if (sval)
            *sval = -123456789;
        // 获取quicklist节点
        quicklistNode *node;
        if (where == QUICKLIST_HEAD && quicklist->head) {
            node = quicklist->head;
        } else if (where == QUICKLIST_TAIL && quicklist->tail) {
            node = quicklist->tail;
        } else {
            return 0;
        }
        // 获取ziplist中的节点
        p = ziplistIndex(node->zl, pos);
        // 获取该节点的值
        if (ziplistGet(p, &vstr, &vlen, &vlong)) {
            // 如果是字符串值
            if (vstr) {
                if (data)
                    // _quicklistSaver函数用于深拷贝取出返回值
                    *data = saver(vstr, vlen);
                if (sz)
                    *sz = vlen;  // 字符串的长度
            } else {
                // 如果存放的是整型值
                if (data)
                    *data = NULL;  // 字符串设为NULL
                if (sval)
                    *sval = vlong;  // 弹出节点的整型值
            }
            // 删除该节点
            quicklistDelIndex(quicklist, node, &p);
            return 1;
        }
        return 0;
    }
    // 返回一个字符串副本，深拷贝
    // 这里深拷贝的用意是避免二次释放
    REDIS_STATIC void *_quicklistSaver(unsigned char *data, unsigned int sz) {
        unsigned char *vstr;
        if (data) {
            vstr = zmalloc(sz);
            memcpy(vstr, data, sz);
            return vstr;
        }
        return NULL;
    }
    

## 其他接口函数 

Redis关于quicklist还提供了很多接口函数，这里只罗列出接口，没有具体实现。

    // 在quicklist尾部追加指针zl指向的ziplist
    voidquicklistAppendZiplist(quicklist *quicklist,unsignedchar*zl);
    // 将ziplist数据转换成quicklist
    quicklist *quicklistCreateFromZiplist(intfill,intcompress,
                                          unsigned char *zl);
    // 在node节点后添加一个值valiue
    voidquicklistInsertAfter(quicklist *quicklist, quicklistEntry *node,
                              void *value, const size_t sz);
    // 在node节点前面添加一个值value
    voidquicklistInsertBefore(quicklist *quicklist, quicklistEntry *node,
                               void *value, const size_t sz);
    // 删除ziplist节点entry
    voidquicklistDelEntry(quicklistIter *iter, quicklistEntry *entry);
    // 翻转quicklist
    voidquicklistRotate(quicklist *quicklist);
    // 返回quicklist列表中所有数据项的个数总和
    unsignedintquicklistCount(quicklist *ql);
    // 比较两个quicklist结构数据
    intquicklistCompare(unsignedchar*p1,unsignedchar*p2,intp2_len);
    // 从节点node中取出LZF压缩编码后的数据
    size_t quicklistGetLzf(const quicklistNode *node, void **data);
    

## quicklist小结 

quicklist将sdlist和ziplist两者的优点结合起来，在时间和空间上做了一个均衡，能较大程度上提高Redis的效率。压入和弹出操作的时间复杂度都很理想。在源码中，还给出了很多接口函数，有兴趣的读者可以去quicklist.c文件中查看，如果对博客中的讲述觉得有问题的，可以在下方留言，多多交流，互相学习！

[1]: http://zcheng.ren/2016/12/19/TheAnnotatedRedisSourceQuicklist/?utm_source=tuicool&utm_medium=referral
[4]: http://img1.tuicool.com/AZj6zme.png!web