# Redis源码剖析--有序集合t_zset

Dec 24, 2016 | [Redis][0] | 65 Hits

文章目录

1. [1. Zset数据结构][1]
1. [2. Zset迭代器][2]
1. [3. Ziplist编码的zset][3]
1. [4. Skiplist编码的zset][4]
1. [5. 编码转换][5]
1. [6. Zset命令][6]
1. [7. zset小结][7]

今天来剖析一个比较有意思的数据类型—— 有序集合zset，说实话，它的源码真的是多，而且繁琐，不过，其中的一部分在[Redis源码剖析–跳跃表zskiplist][8]中分析过了。有序集合到底是什么呢？有序集合里面存放的元素都自带一个分值，根据这个分值来对元素进行排序，从而使其成为一个有序的集合。接下来，枯燥的Read Code时间到了。

# Zset数据结构

有序集合zset是由[RedisObject][9]来管理，当Object结构中的type字段为OBJ_ZSET，且编码字段为OBJ_ENCODING_ZIPLIST或OBJ_ENCODING_SKIPLIST。这样才能被称为是一个有序集合对象。

```c
/* RedisObject结构 */

typedef struct redisObject {

    unsigned type:4;  // OBJ_ZSET表示有序集合对象

    unsigned encoding:4;  // 编码字段为OBJ_ENCODING_ZIPLIST或OBJ_ENCODING_SKIPLIST

    unsigned lru:LRU_BITS; // LRU_BITS为24位

    int refcount;

    void *ptr;  // 指向数据部分

} robj;
```
通常涉及到两种编码结构时，都会在特定情况下，对底层数据结构进行转换，以达到效率和内存占用的平衡。zset规定了两个阈值如下：

```c
    #define OBJ_ZSET_MAX_ZIPLIST_ENTRIES 128  // ziplist中最多存放的节点数
    
    #define OBJ_ZSET_MAX_ZIPLIST_VALUE 64  // ziplist中最大存放的数据长度
```

当数据量很小且数据长度很小时，zset采用ziplist编码；一旦数据量超过规定的阈值（128）或者添加的元素长度大于规定的阈值（64）时，会将底层的数据结构转换为skiplist，从而提高效率。

# Zset迭代器

zset的迭代器用于范围性操作命令中遍历zset，Redis对于zset的迭代器的设计比较巧妙，采用union来设计。
    
```c
/* zset中的迭代器结构涉及，采用union可以节省内存 */
union _iterzset {
    // 编码为ziplist时的迭代器结构
    struct {
      unsigned char *zl;
      unsigned char *eptr, *sptr;
    } zl;
    // 编码为skiplist时的迭代器结构
    struct {
      zset *zs;
      zskiplistNode *node;
    } sl;
  } zset;
```
关于其迭代器的操作函数，本片博客就省略了，因为大体上模式都相同。另外，redis3.2.5版本中貌似有一些遗留性的代码，把set结构的迭代器也混合在一起了，各位看的时候可以忽略掉。

# Ziplist编码的zset

如果一个zset结构采用ziplist作为其底层数据，那么其结构的内存布局如下：

    | zlbytes | zltail | zllen | ele1 | score1 | .... | zlend

其中，每一个元素与其对应的分值都是成对出现的。如果对ziplist数据结构不熟悉的可以参考[Redis数据结构ziplist][10]。我们继续，Redis没有向其他结构那样通过encoding字段来控制其接口函数，而是为ziplist和skiplist编码的zset各自提供了一套接口函数。关于ziplist编码的zset有如下接口函数：
  
```c
/* 获取zset对象中sptr指向的分值score */
double zzlGetScore(unsigned char *sptr);
/* 获取zset对象中sptr指向的元素，返回一个新的Redis string对象，该对象存放元素值*/
robj *ziplistGetObject(unsigned char *sptr);
/* 比较zset对象中eptr指向的元素与给定元素的大小*/
int zzlCompareElements(unsigned char *eptr, unsigned char *cstr, unsigned int clen);
/* 获取zset对象中eptr指向元素的下一个元素*/
void zzlNext(unsigned char *zl, unsigned char **eptr, unsigned char **sptr) ;
/* 获取zset对象中元素的个数，为ziplist中元素个数的一半*/
unsigned int zzlLength(unsigned char *zl);
/* 获取zset对象中eptr指向的元素的前一个元素*/
void zzlPrev(unsigned char *zl, unsigned char **eptr, unsigned char **sptr);
/* 如果给定ziplist中至少有一个节点在range范围内，返回1；反之返回0 */
int zzlIsInRange(unsigned char *zl, zrangespec *range);
/* 返回第一个score值在给定范围内的节点，没有则返回null */
unsigned char *zzlFirstInRange(unsigned char *zl, zrangespec *range);
/* 返回最后一个score值在给定范围内的节点，没有则返回NULL */
unsigned char *zzlLastInRange(unsigned char *zl, zrangespec *range);
/* 判断指定元素与给定范围的最小值的大小 */
static int zzlLexValueGteMin(unsigned char *p, zlexrangespec *spec);
/* 判断指定元素与给定范围的最大值的大小 */
static int zzlLexValueLteMax(unsigned char *p, zlexrangespec *spec);
/* 判断指定元素是否存在于给定范围内，与zzlIsInRange不同的是，前者比较元素，后者比较score分值*/
int zzlIsInLexRange(unsigned char *zl, zlexrangespec *range);
/* 返回ziplist中第一个存在于给定范围内的元素 */
unsigned char *zzlFirstInLexRange(unsigned char *zl, zlexrangespec *range);
/* 返回ziplist中最后一个存在于给定范围内的元素 */
unsigned char *zzlLastInLexRange(unsigned char *zl, zlexrangespec *range);
/* 查找ziplist中是否存在给定元素与分值 */
unsigned char *zzlFind(unsigned char *zl, robj *ele, double *score);
/* 删除元素及其分值(element,score) */
unsigned char *zzlDelete(unsigned char *zl, unsigned char *eptr);
/* 在指定位置插入一个元素及其分值(element score)
 * 如果eptr为空，插入到ziplist尾部 
 */
unsigned char *zzlInsertAt(unsigned char *zl, unsigned char *eptr, robj *ele, double score);
/* 在指定位置插入一个元素及其分值(element,score)
 * 假定该元素不存在于该ziplist中，其中元素按分值大小排序
 * 如果分值相同，则按字典序排序
 */
unsigned char *zzlInsert(unsigned char *zl, robj *ele, double score);
/* 删除给定score范围内的数据 */
unsigned char *zzlDeleteRangeByScore(unsigned char *zl, zrangespec *range, unsigned long *deleted);
/* 删除给定元素范围内的数据 */
unsigned char *zzlDeleteRangeByLex(unsigned char *zl, zlexrangespec *range, unsigned long *deleted);
/* 删除给定排名范围内的数据，zset根据分值排名，如分值相同根据字典序排名*/
unsigned char *zzlDeleteRangeByRank(unsigned char *zl, unsigned int start, unsigned int end, unsigned long *deleted)
```
看到这么多的接口函数，都傻眼了。挑个比较重要的来分析一下吧，其他的各位有兴趣的可以找源码看看。

```c
/* 在指定位置插入一个元素及其分值(element,score)
 * 假定该元素不存在于该ziplist中，其中元素按分值大小排序
 * 如果分值相同，则按字典序排序
 */
unsigned char *zzlInsert(unsigned char *zl, robj *ele, double score) {
    unsigned char *eptr = ziplistIndex(zl,0), *sptr;
    double s;
    // 从ele中解码出元素
    ele = getDecodedObject(ele);
    while (eptr != NULL) {
        // 得到eptr元素对应的分值对象score
        sptr = ziplistNext(zl,eptr);
        serverAssertWithInfo(NULL,ele,sptr != NULL);
        // 获取分值
        s = zzlGetScore(sptr);
        if (s > score) {
            /* ziplist本身是排序的，如果找到第一个分值大于score的元素，则
             * 表明给定元素应该插在当前找的元素的前面
             */
            zl = zzlInsertAt(zl,eptr,ele,score);
            break;
        } else if (s == score) {
            /* 如果分值相同，则按字典排列 */
            if (zzlCompareElements(eptr,ele->ptr,sdslen(ele->ptr)) > 0) {
                zl = zzlInsertAt(zl,eptr,ele,score);
                break;
            }
        }
        // 遍历到下一个元素
        eptr = ziplistNext(zl,sptr);
    }
    // 如果所有分值均小于score，则插入到ziplist末尾
    if (eptr == NULL)
        zl = zzlInsertAt(zl,NULL,ele,score);
    // 临时对象释放
    decrRefCount(ele);
    return zl;
}
```
# Skiplist编码的zset

当zset对象的encoding字段为OBJ_ENCODING_SKIPLIST时，其底层的数据结构为skiplist。如果对跳跃表skiplist不熟悉的话可以跳转到[Redis源码分析—跳跃表skiplist][8]。Redis为skiplist编码的有序列表提供了下面的结构体定义。
    
```c
typedef struct zset {

    dict *dict;  // 字典结构

    zskiplist *zsl;  // 跳跃表

} zset;
```
这里为什么要给跳跃表加上一个字典结构呢？我们知道跳跃表在插入、删除和查找操作上都可以做到O(logn)的时间复杂度，但是，zset还需要支持获取给定元素的分值、判断某元素是否存在于zset中等操作，这些如果在skiplist的基础上做就相对较复杂，效率不高，所以zset维护了一个字典结构，用来快速的获取给定元素的分值以及判断元素值是否存在于zset中等操作，这样可以提高zset的效率。

底层数据结构为跳跃表的zset相关操作函数均在skiplist中分析过，这里简单罗列一下：
   
```c
/* 向skiplist插入给定的(element,score)对 */
zskiplistNode *zslInsert(zskiplist *zsl, double score, robj *obj);
/* 删除sjiplist中的给定的(element,score)对 */
int zslDelete(zskiplist *zsl, double score, robj *obj);
/* 判断skiplist中的元素是否存在于range范围内，其中range为分值范围*/
int zslIsInRange(zskiplist *zsl, zrangespec *range);
/* 返回skiplist中第一个存在于range分值范围内的节点*/
zskiplistNode *zslFirstInRange(zskiplist *zsl, zrangespec *range);
/* 返回skiplist中最后一个存在于range分值范围内的节点*/
zskiplistNode *zslLastInRange(zskiplist *zsl, zrangespec *range);
/* 删除skiplist中给定分值范围内的节点*/
unsigned long zslDeleteRangeByScore(zskiplist *zsl, zrangespec *range, dict *dict);
/* 删除skiplist中给定元素范围内的节点 */
unsigned long zslDeleteRangeByLex(zskiplist *zsl, zlexrangespec *range, dict *dict);
/* 删除skiplist中给定排名范围内的节点 */
unsigned long zslDeleteRangeByRank(zskiplist *zsl, unsigned int start, unsigned int end, dict *dict) ;
/* 获取给定节点(element,score)对的排名*/
unsigned long zslGetRank(zskiplist *zsl, double score, robj *o);
/* 根据排名获取节点 */
zskiplistNode* zslGetElementByRank(zskiplist *zsl, unsigned long rank);
/* 判断skiplist中的节点是否存在与对象范围range中*/
int zslIsInLexRange(zskiplist *zsl, zlexrangespec *range);
/* 返回skiplist中第一个存在于对象范围range中的节点*/
zskiplistNode *zslFirstInLexRange(zskiplist *zsl, zlexrangespec *range);
/* 返回skiplist中最后一个存在于对象范围range中的节点*/
zskiplistNode *zslLastInLexRange(zskiplist *zsl, zlexrangespec *range);
```
# 编码转换

前面提到，当数据量超过规定的阈值或者添加的数据长度超过规定阈值的话，就需要改变zset的底层数据结构。那么，转换的操作怎么实现的呢？我们找到了zsetConvert函数，下面一起看看源代码吧。
   
```c
void zsetConvert(robj *zobj, int encoding) {
    zset *zs;
    zskiplistNode *node, *next;
    robj *ele;
    double score;
    // 如果当前编码类型与待转换的类型一直，不需要处理
    if (zobj->encoding == encoding) return;
    // 从ziplist转换成skiplist编码
    if (zobj->encoding == OBJ_ENCODING_ZIPLIST) {
        unsigned char *zl = zobj->ptr;
        unsigned char *eptr, *sptr;
        unsigned char *vstr;
        unsigned int vlen;
        long long vlong;
        // 检查给定的编码类型是否为OBJ_ENCODING_SKIPLIST
        if (encoding != OBJ_ENCODING_SKIPLIST)
            serverPanic("Unknown target encoding");
        // 创建一个新的skiplist编码的zset
        zs = zmalloc(sizeof(*zs));
        zs->dict = dictCreate(&zsetDictType,NULL);
        zs->zsl = zslCreate();
        eptr = ziplistIndex(zl,0);
        serverAssertWithInfo(NULL,zobj,eptr != NULL);
        sptr = ziplistNext(zl,eptr);
        serverAssertWithInfo(NULL,zobj,sptr != NULL);
        // 遍历ziplist将元素添加到skiplist中
        while (eptr != NULL) {
            // 获取分值
            score = zzlGetScore(sptr);
            serverAssertWithInfo(NULL,zobj,ziplistGet(eptr,&vstr,&vlen,&vlong));
            if (vstr == NULL)
                ele = createStringObjectFromLongLong(vlong);
            else
                ele = createStringObject((char*)vstr,vlen);
            // 插入元素到skiplist
            node = zslInsert(zs->zsl,score,ele);
            // 插入元素和分值对到字典中
            serverAssertWithInfo(NULL,zobj,dictAdd(zs->dict,ele,&node->score) == DICT_OK);
            incrRefCount(ele); 
            zzlNext(zl,&eptr,&sptr);
        }
        zfree(zobj->ptr);
        zobj->ptr = zs;
        zobj->encoding = OBJ_ENCODING_SKIPLIST;
    // 从skiplist转换成ziplist编码
    } else if (zobj->encoding == OBJ_ENCODING_SKIPLIST) {
        unsigned char *zl = ziplistNew();
        // 检查给定编码类型是否为OBJ_ENCODING_ZIPLIST
        if (encoding != OBJ_ENCODING_ZIPLIST)
            serverPanic("Unknown target encoding");
        // 获取skiplist数据部分
        zs = zobj->ptr;
        // 释放字典
        dictRelease(zs->dict);
        // 取skiplist头节点
        node = zs->zsl->header->level[0].forward;
        // 释放跳跃表表头
        zfree(zs->zsl->header);
        zfree(zs->zsl);
        // 遍历跳跃表，取出里面的元素，并将它们添加到ziplist
        while (node) {
            // 取出解码后的值对象
            ele = getDecodedObject(node->obj);
            // 插入到ziplist中
            zl = zzlInsertAt(zl,NULL,ele,node->score);
            // 释放临时对象
            decrRefCount(ele);
            // 沿着跳跃表的第0层遍历
            next = node->level[0].forward;
            zslFreeNode(node);
            node = next;
        }
        zfree(zs);
        // 更新zset对象的数据
        zobj->ptr = zl;
        // 更新编码类型
        zobj->encoding = OBJ_ENCODING_ZIPLIST;
    } else {
        serverPanic("Unknown sorted set encoding");
    }
}
```
另外，Redis提供了一个函数，用来在需要的时候将skiplist转换成ziplist编码。
    
```c
/* 在需要的时候讲skiplist转换成ziplist编码，用来节省内存*/
void zsetConvertToZiplistIfNeeded(robj *zobj, size_t maxelelen) {
    if (zobj->encoding == OBJ_ENCODING_ZIPLIST) return;
    zset *zset = zobj->ptr;
    // 当节点个数小于给定阈值或者元素的最大长度小于给定阈值时，转换成ziplist编码
    if (zset->zsl->length <= server.zset_max_ziplist_entries &&
        maxelelen <= server.zset_max_ziplist_value)
            zsetConvert(zobj,OBJ_ENCODING_ZIPLIST);
}
```
# Zset命令

Redis为客户端提供了丰富的命令，用来操作zset对象，我们首先来看看添加元素的命令。

    127.0.0.1:6379> zadd sort 100 num1 1000 num2

    (integer) 2

此命令表示向sort这个有序集合中添加两个[element, score]对，通过查资料，我得到了它的命令原型。

    ZADD key [NX|XX] [CH] [INCR] score member [score member ...]

其中，有几个参数需要解释一下：

* XX：表示只有当元素存在的时候才更新其分值，不存在时不添加新元素
* NX：表示只添加新元素，如果存在则不作处理
* CH：修改返回值为发生变化的成员总数，原始是返回新添加成员的总数。更改的成员是新添加的成员，已经存在的成员更新分数。所以在命令中指定的成员有相同的分数的话，不计算在内。注：在通常情况下，ZADD返回值只计算新添加成员的数量。
* INCR：当ZADD指定这个选项时，成员的操作就等同ZINCRBY命令，对成员的分数进行递增操作

接下来，理解了上述参数的意义之后，就可以好好的看代码了。Redis定义了下面几个宏定义，用来标记上述的命令类型。
```c
#define ZADD_NONE 0
#define ZADD_INCR (1<<0)    /* Increment the score instead of setting it. */
#define ZADD_NX (1<<1)      /* Don't touch elements not already existing. */
#define ZADD_XX (1<<2)      /* Only touch elements already exisitng. */
#define ZADD_CH (1<<3)      /* Return num of elements added or updated. */
```
ZADD命令由zaddCommand函数实现，其调用了zaddGenericCommand这个泛型函数来完成添加操作。
```c
/* ZADD命令的实现 */
void zaddCommand(client *c) {
    zaddGenericCommand(c,ZADD_NONE);
}
/* 添加元素的泛型实现 */
void zaddGenericCommand(client *c, int flags) {
    static char *nanerr = "resulting score is not a number (NaN)";
    robj *key = c->argv[1];
    robj *ele;
    robj *zobj;
    robj *curobj;
    double score = 0, *scores = NULL, curscore = 0.0;
    int j, elements;
    int scoreidx = 0;
    // 以下变量用来标记何种命令被执行，为了给客户端回复，发送事件通知等
    int added = 0;      // 添加的元素个数
    int updated = 0;    // 更新分值的元素个数
    int processed = 0;  // 被处理过的元素个数，XX命令下可能为0
    // 解析设置的参数，scoreidx最后为第一个元素的下标
    scoreidx = 2;
    while(scoreidx < c->argc) {
        char *opt = c->argv[scoreidx]->ptr;
        if (!strcasecmp(opt,"nx")) flags |= ZADD_NX;
        else if (!strcasecmp(opt,"xx")) flags |= ZADD_XX;
        else if (!strcasecmp(opt,"ch")) flags |= ZADD_CH;
        else if (!strcasecmp(opt,"incr")) flags |= ZADD_INCR;
        else break;
        scoreidx++;
    }
    // 将操作命令标识flags转换为简单的变量
    int incr = (flags & ZADD_INCR) != 0;
    int nx = (flags & ZADD_NX) != 0;
    int xx = (flags & ZADD_XX) != 0;
    int ch = (flags & ZADD_CH) != 0;
    /* After the options, we expect to have an even number of args, since
     * we expect any number of score-element pairs. */
    // 命令中的元素和分值总个数
    elements = c->argc-scoreidx;
    // 验证元素个数是否成对出现
    if (elements % 2) {
        addReply(c,shared.syntaxerr);
        return;
    }
    elements /= 2; // 此时，elements才表示所有的元素分值对的个数
    // 检查nx和xx不能同事设定
    if (nx && xx) {
        addReplyError(c,
            "XX and NX options at the same time are not compatible");
        return;
    }
    // 检查：incr操作时，[element，scores]对只能有一个
    if (incr && elements > 1) {
        addReplyError(c,
            "INCR option supports a single increment-element pair");
        return;
    }
    // 开始解析所有的分数，验证分数对象是double类型的整数，否则直接退出
    scores = zmalloc(sizeof(double)*elements);
    for (j = 0; j < elements; j++) {
        // 从对象中得到double类型的分数
        if (getDoubleFromObjectOrReply(c,c->argv[scoreidx+j*2],&scores[j],NULL)
            != C_OK) goto cleanup;
    }
    // 检查zset键是否存在
    zobj = lookupKeyWrite(c->db,key);
    if (zobj == NULL) {
        // 如果键不存在，且设置了xx，则直接回复给客户端，不做任何处理
        if (xx) goto reply_to_client;
        // 检查配置参数
        if (server.zset_max_ziplist_entries == 0 ||
            server.zset_max_ziplist_value < sdslen(c->argv[scoreidx+1]->ptr))
        {
            // 创建以skiplist编码的zset对象
            zobj = createZsetObject();
        } else {
            // 执行到此，说明ziplist最大能存储的节点个数大于0且待添加的元素的长度没有
            // 超过设定的阈值zset_max_ziplist_value
            zobj = createZsetZiplistObject();
        }
        // 添加键值对到数据库
        dbAdd(c->db,key,zobj);
    } else {
        // 如果键存在，但不是有序集合键，清理临时变量，回复，返回
        if (zobj->type != OBJ_ZSET) {
            addReply(c,shared.wrongtypeerr);
            goto cleanup;
        }
    }
    // 开始处理每一对[element，score]
    for (j = 0; j < elements; j++) {
        score = scores[j];
        // 底层编码为ziplist的情况
        if (zobj->encoding == OBJ_ENCODING_ZIPLIST) {
            unsigned char *eptr;
            // 当编码类型为ziplist，首选非编码元素
            ele = c->argv[scoreidx+1+j*2];
            if ((eptr = zzlFind(zobj->ptr,ele,&curscore)) != NULL) {
                // 执行到此，说明该元素存在
                if (nx) continue; // nx选项被设定时，不处理当前元素，继续处理下一个元素
                if (incr) {
                    // incr选项被设定，表示需要递增分数
                    score += curscore;
                    // isnan用来判断score是不是一个数
                    if (isnan(score)) {
                        addReplyError(c,nanerr);
                        goto cleanup;
                    }
                }
                // 当分数发生变化的时候，需要删除旧的元素及其分数
                // 然后添加新的元素和分值
                if (score != curscore) {
                    zobj->ptr = zzlDelete(zobj->ptr,eptr);
                    zobj->ptr = zzlInsert(zobj->ptr,ele,score);
                    server.dirty++;
                    updated++;  // 更新元素的个数加1
                }
                processed++; // 处理的元素个数加1
            } else if (!xx) {
                // 执行到此，说明该元素不存在，且没有设定xx选项
                // 添加新元素及其分数到ziplist
                zobj->ptr = zzlInsert(zobj->ptr,ele,score);
                // 检查ziplist中的元素个数是否超出设定值，如超出则需要转换成skiplist编码
                if (zzlLength(zobj->ptr) > server.zset_max_ziplist_entries)
                    zsetConvert(zobj,OBJ_ENCODING_SKIPLIST);
                // 检查待添加元素的长度是否超过规定阈值，如超出则需要转换成skiplist编码
                if (sdslen(ele->ptr) > server.zset_max_ziplist_value)
                    zsetConvert(zobj,OBJ_ENCODING_SKIPLIST);
                server.dirty++;
                added++;
                processed++;
            }
        } else if (zobj->encoding == OBJ_ENCODING_SKIPLIST) {
            // 底层编码为skiplist的情况
            zset *zs = zobj->ptr;
            zskiplistNode *znode;
            dictEntry *de;
            ele = c->argv[scoreidx+1+j*2] =
                tryObjectEncoding(c->argv[scoreidx+1+j*2]);
            // 检查该元素是否存在，skiplist编码时，用一个字典结构保存了元素和分值
            de = dictFind(zs->dict,ele);
            if (de != NULL) {
                // 该元素存在
                if (nx) continue; // nx选项被设定，元素存在的时候不做处理
                // 获取当前元素的节点和分数
                curobj = dictGetKey(de);
                curscore = *(double*)dictGetVal(de);
                // 如果设定的incr选项
                if (incr) {
                    score += curscore;
                    if (isnan(score)) { // 检查score是否是一个数
                        addReplyError(c,nanerr);
                        // 不需要检查有序列表是否为空，因为我们知道至少存在一个元素
                        goto cleanup;
                    }
                }
                // 在skiplist中移除旧元素和分数，添加新元素及其分数
                // 字典中不需要移除，只需要更新分数即可
                if (score != curscore) {
                    serverAssertWithInfo(c,curobj,zslDelete(zs->zsl,curscore,curobj));
                    znode = zslInsert(zs->zsl,score,curobj);
                    incrRefCount(curobj); 
                    dictGetVal(de) = &znode->score;
                    server.dirty++;
                    updated++;
                }
                processed++;
            } else if (!xx) {
                // 如果键不存在，且没有设定xx选项
                // 直接插入新的元素和分数
                znode = zslInsert(zs->zsl,score,ele);
                incrRefCount(ele);  // 添加成功，引用计数加1
                // 新的元素和分值添加到字典结构中
                serverAssertWithInfo(c,NULL,dictAdd(zs->dict,ele,&znode->score) == DICT_OK); 
                incrRefCount(ele); // 添加成功，引用计数加1
                server.dirty++;
                added++;
                processed++;
            }
        } else {
            serverPanic("Unknown sorted set encoding");
        }
    }
reply_to_client:
    // 给客户端回复
    if (incr) { // INCR命令或者设定了incr选项
        if (processed)
            addReplyDouble(c,score);
        else
            addReply(c,shared.nullbulk);
    } else { // 回复执行了add命令
        addReplyLongLong(c,ch ? added+updated : added);
    }
cleanup:
    // 释放临时变量
    zfree(scores);
    if (added || updated) {
        // 标记修改的键
        signalModifiedKey(c->db,key);
        // 发送事件通知
        notifyKeyspaceEvent(NOTIFY_ZSET,
            incr ? "zincr" : "zadd", key, c->db->id);
    }
}
```
至此，有序集合的ZADD命令的执行流程已经完全弄清楚了，其他的命令这里就不再赘述了，我仅罗列出操作命令的形式及其功能，源码部分感兴趣的朋友可以去看看，反正我看完了，哈哈。

命令格式 | 功能 
-|-
ZCARD key | 返回key的有序集元素个数 
ZCOUNT key min max | 返回指定分数范围内的元素个数 
ZINCRBY key increment member | 为指定zset中的指定元素的分数加上一个增量 
ZLEXCOUNT key min max | 计算有序集合中指定成员之间的成员数量 
ZRANGE key start stop [WITHSCORES] | 返回指定排名范围内的元素(可选是否返回分数) 
ZRANGEBYLEX key min max [LIMIT offset count] | 返回指定成员区间内的元素 
ZRANGEBYSCOREkey min max [WITHSCORES][ LIMIT offset count] | 返回指定分数范围内的元素 
ZRANK key member | 返回指定元素的排名 
ZREM key member [member …] | 移除一个或多个元素 
ZREMRANGEBYLEX key min max | 删除名称按字典由低到高排序成员之间的所有成员(注：不要在分值不同的有序集合中使用此命令) 
ZREMRANGEBYRANK key start stop | 删除[start,stop]排名内的所有元素 
ZREMRANGEBYSCORE key min max | 删除[min,max]分数范围内的所有元素 
ZUNIONSTORE destination numkeys key [key …][WEIGHTS weight][SUM\MIN\MAX] | 计算一个或多个集合的并集 

有了这些命令的格式，还不快去在自己的机子上跑一跑试试效果！「我现在就去一个一个试试」，对了，如果对指令还是不怎么了解的话，可以去[redis官网][11]查看详细的命令操作示例。

# zset小结

本篇博客介绍了Redis中有序集合的相关知识，了解了命令的执行过程和典型命令的源码实现，对底层编码类型何时转换何时采用何种编码等都有了一个较深的理解。由于时间和篇幅有限，没有分析到每一个命令的实现源码，各位可以自行去源代码中阅读。如有疑惑的地方可以在下方留言，期待和大家一起交流学习Redis！

[0]: /categories/Redis/
[1]: #Zset数据结构
[2]: #Zset迭代器
[3]: #Ziplist编码的zset
[4]: #Skiplist编码的zset
[5]: #编码转换
[6]: #Zset命令
[7]: #zset小结
[8]: http://zcheng.ren/2016/12/06/TheAnnotatedRedisSourceZskiplist/
[9]: http://zcheng.ren/2016/12/14/TheAnnotatedRedisSourceObject/
[10]: http://zcheng.ren/2016/12/13/TheAnnotatedRedisSourceZiplist/
[11]: https://redis.io/commands