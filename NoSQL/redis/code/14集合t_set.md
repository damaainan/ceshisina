# Redis源码剖析--集合t_set

Dec 22, 2016 | [Redis][0] | 91 Hits

文章目录

1. [1. Set概述][1]
1. [2. Set迭代器][2]
1. [3. 编码转换][3]
1. [4. Set基本接口][4]
1. [5. Set命令][5]
    1. [5.1. 集合运算][6]
1. [6. Set小结][7]

今天来看看Redis的另一个数据类型—集合set。在[RedisObject][8]一篇中，有介绍到集合对象的底层有两种编码形式，分别是OBJ_ENCODING_INTSET（底层数据结构为整数集合）和OBJ_ENCODING_HT（底层数据结构为字典），如果对[整数集合Intset][9]和[字典dict][10]不熟悉的，可以点击跳转去复习一下。下面，就一起去剖析一下set的实现源码吧。

# Set概述

关于集合set的源码在server.h和t_set.c文件中，与前面分析字符串和列表一样，先来看看它的一些定义。Redis采用RedisObject数据结构来表示它的所有对外开放的数据类型。
   
```c
typedef struct redisObject {
    unsigned type:4;  // 此字段为OBJ_SET
    unsigned encoding:4;  // 如果是set结构，编码为OBJ_ENCODING_INTSET或OBJ_ENCODING_HT
    unsigned lru:LRU_BITS; 
    int refcount;
    void *ptr;
} robj;
```
在RedisObject结构中，如果encoding字段为OBJ_ENCODING_INTSET或OBJ_ENCODING_HT时，就说明这个对象表示一个set对象。那么，ptr字段就指向一个整数集合或者字典结构。

# Set迭代器

每个数据类型都拥有其迭代器结构，用来遍历集合中的每一个元素，set的迭代器结构定义如下：

    
```c
typedef struct {
    robj *subject;  // 指向的set对象
    int encoding;  // 编码类型
    int ii; // 如果是intset，则用下标即可代表迭代器
    dictIterator *di; // 如果是ht编码，则采用字典的迭代器
} setTypeIterator;
```
有了迭代器结构之后，Redis为它提供了一些操作函数，用于迭代操作。
 
```c
// 初始化迭代器
setTypeIterator *setTypeInitIterator(robj *subject) {
    setTypeIterator *si = zmalloc(sizeof(setTypeIterator));
    si->subject = subject;
    si->encoding = subject->encoding;
    if (si->encoding == OBJ_ENCODING_HT) {
        si->di = dictGetIterator(subject->ptr);  // 获取字典迭代器
    } else if (si->encoding == OBJ_ENCODING_INTSET) {
        si->ii = 0;  // 整数集合的下标
    } else {
        serverPanic("Unknown set encoding");
    }
    return si;
}
// 释放迭代器
void setTypeReleaseIterator(setTypeIterator *si) {
    if (si->encoding == OBJ_ENCODING_HT)
        dictReleaseIterator(si->di);
    zfree(si);
}
// 指向下一个迭代器，返回迭代器的编码类型
// 另外objele传出迭代器指向的字典节点对象
// llele传出迭代器指向整数值
int setTypeNext(setTypeIterator *si, robj **objele, int64_t *llele) {
    if (si->encoding == OBJ_ENCODING_HT) {
        dictEntry *de = dictNext(si->di);
        if (de == NULL) return -1;
        *objele = dictGetKey(de);
        *llele = -123456789; /* Not needed. Defensive. */
    } else if (si->encoding == OBJ_ENCODING_INTSET) {
        if (!intsetGet(si->subject->ptr,si->ii++,llele))
            return -1;
        *objele = NULL; /* Not needed. Defensive. */
    } else {
        serverPanic("Wrong set encoding in setTypeNext");
    }
    return si->encoding;
}
```
# 编码转换

我们知道，当一个集合满足只保存整数元素且元素数量不多时，就采用整数集合来存放。所以，当往集合set中添加的元素不是整数类型或者数据量较多时，就需要将内部编码转换为字典结构。Redis的配置文件中提供了set-max-intset-entries参数用来表示整数集合中最大能存放的整数数量。

    set-max-intset-entries 512  // 默认整数集合intset的最多能存放512个整数

编码转换的功能由setTypeConvert函数实现，其利用set类型迭代器遍历intset中的每一个元素，然后添加到字典dict结构中。
  
```c
// 将set类型的intset编码转换成ht编码
void setTypeConvert(robj *setobj, int enc) {
    setTypeIterator *si;
    // 判断当前编码类型是否为intset
    serverAssertWithInfo(NULL,setobj,setobj->type == OBJ_SET &&
                             setobj->encoding == OBJ_ENCODING_INTSET);
    // 判断enc提供的编码类型是不是HT
    if (enc == OBJ_ENCODING_HT) {
        int64_t intele;
        dict *d = dictCreate(&setDictType,NULL);
        robj *element;
        // 扩张字典结构的大小，避免出现rehash过程
        dictExpand(d,intsetLen(setobj->ptr));
        // 初始化迭代器，遍历intset，然后依次加入到dict中
        si = setTypeInitIterator(setobj);
        while (setTypeNext(si,&element,&intele) != -1) {
            // 将整数类型转换成字符串对象
            element = createStringObjectFromLongLong(intele);
            serverAssertWithInfo(NULL,element,
                                dictAdd(d,element,NULL) == DICT_OK);
        }
        // 释放迭代器
        setTypeReleaseIterator(si);
        // 设定编码类型
        setobj->encoding = OBJ_ENCODING_HT;
        zfree(setobj->ptr);
        setobj->ptr = d;
    } else {
        serverPanic("Unsupported set conversion");
    }
}
```
# Set基本接口

set作为Redis的一个数据结构，必然会提供丰富的接口函数，其实现相对简单，和string，list一样，都是调用底层数据结构的接口来实现。下面罗列出所有的接口函数。
   
```c
// 创建set类型的Redis对象
robj *setTypeCreate(robj *value);
// 往set集合中添加数据
int setTypeAdd(robj *subject, robj *value);
// 移除set集合中的数据
int setTypeRemove(robj *setobj, robj *value);
// 判断指定的value是否存在于set集合中
int setTypeIsMember(robj *subject, robj *value);
// 随机返回set集合中的某个元素
int setTypeRandomElement(robj *setobj, robj **objele, int64_t *llele);
// 获取set集合的大小
unsigned long setTypeSize(robj *subject);
```
以setTypeAdd为例，来分析一下它的源码实现（其他接口函数的源码可以去t_set查看，这里就不再赘述，实现都比较简单）。
   
```c
// 添加数据到集合set中
// 如果底层编码是intset需要考虑元素个数是否超过了set_max_intset_entries
int setTypeAdd(robj *subject, robj *value) {
    long long llval;
    // 如果底层编码是HT，则直接调用字典的添加元素函数
    if (subject->encoding == OBJ_ENCODING_HT) {
        if (dictAdd(subject->ptr,value,NULL) == DICT_OK) {
            incrRefCount(value);
            return 1;
        }
    } else if (subject->encoding == OBJ_ENCODING_INTSET) {
        // 编码类型是iNTSET，则需要判断待添加元素的类型
        if (isObjectRepresentableAsLongLong(value,&llval) == C_OK) {
            // 待添加元素是整数类型
            uint8_t success = 0;
            // 调用intset的添加元素函数
            subject->ptr = intsetAdd(subject->ptr,llval,&success);
            if (success) {
                // 判断intset的长度是否超过了set_max_intset_entries
                if (intsetLen(subject->ptr) > server.set_max_intset_entries)
                    // 将底层编码转换成HT
                    setTypeConvert(subject,OBJ_ENCODING_HT);
                return 1;
            }
        } else {
            // 执行到此，说明待添加元素不为整数
            // 此时就需要将底层编码转换成HT，因为intset只能存放整数类型
            setTypeConvert(subject,OBJ_ENCODING_HT);
            // 一样，调用字典的添加函数
            serverAssertWithInfo(NULL,value,
                                dictAdd(subject->ptr,value,NULL) == DICT_OK);
            incrRefCount(value);
            return 1;
        }
    } else {
        serverPanic("Unknown set encoding");
    }
    return 0;
}
```
# Set命令

当我们启动一个Redis客户端的时候，如果输入如下指令：

    127.0.0.1:6379> SADD numbers 1 3 5  // 往集合键中添加元素

那么，此时的编码类型我们也可以查看到：

    127.0.0.1:6379> OBJECT ENCODING numbers
    
    "intset"

知道SADD指令怎么使用之后，就要深入到源码中来了解它的具体实现。SADD命令的实现由saddCommand函数实现。


```c
// 往集合键中添加元素，如果该元素存在，则不做任何处理；反之则添加
void saddCommand(client *c) {
    robj *set;
    int j, added = 0;
    // 查看数据库中是否存在该集合键
    set = lookupKeyWrite(c->db,c->argv[1]);
    if (set == NULL) {
        // 不存在就创建一个
        set = setTypeCreate(c->argv[2]);
        dbAdd(c->db,c->argv[1],set);
    } else {
        // 存在就检查它是否是一个集合类型的对象
        if (set->type != OBJ_SET) {
            addReply(c,shared.wrongtypeerr);
            return;
        }
    }
    // 从第三个参数开始，为待添加的函数
    for (j = 2; j < c->argc; j++) {
        // 试图将待添加的元素编码成字符串类型，以节省内存
        c->argv[j] = tryObjectEncoding(c->argv[j]);
        // 调用集合set的添加函数
        if (setTypeAdd(set,c->argv[j])) added++;
    }
    // 如果至少添加成功了一个元素
    if (added) {
        // 发送通知
        signalModifiedKey(c->db,c->argv[1]);
        // 发送事件通知
        notifyKeyspaceEvent(NOTIFY_SET,"sadd",c->argv[1],c->db->id);
    }
    server.dirty += added;
    addReplyLongLong(c,added);
}
```
添加元素的底层实现是不是很简单！是不是！基本上就是一些逻辑判断，调用底层函数等。那么，Redis到底为set提供了多少操作命令呢？我们用一张表格来汇总一下。

命令样式 | 命令描述 
-|-
SADD key number1 [number2…] | 向集合键中添加一个或多个元素 
SCARD key | 返回集合键中的元素个数 
SMEMBERS key | 返回集合中的所有成员 
SISMEMBER key member | 判断元素是否是集合成员 
SPOP key | 随机返回并移除一个元素 
SRANDMEMBER key [count] | 随机返回一个或多个元素 
SREM key member [member …] | 移除指定的元素 
SMOVE source destination member | 将元素从集合移至另一个集合 
SDIFF key [key …] | 将一或多个集合的差集保存至另一集合 
SINTER key [key …] | 将一或多个集合的交集保存至另一集合 
SINTERSTORE destination key [key …] | 将一或多个集合的交集存储到新集合 
SUNION key [key …] | 返回集合的并集 
SUNIONSTORE destination key [key …] | 将集合的并集插入新集合 

这里，我挑两个集合运算的命令进行剖析一下，SDIFF和SUNION，其他的函数都相对比较简单。

## 集合运算

集合运算包括交集和并集运算，在客户端输入下面的指令会得到一个或多个集合的交集或并集。

```
// 设定集合key1的元素
127.0.0.1:6379> SADD key1 1 2 3 4 5
(integer) 5
// 设定集合key2的元素
127.0.0.1:6379> SADD key2 3 4 5 6 7
(integer) 5
// 求集合key1和key2的差集
127.0.0.1:6379> SDIFF key1 key2
1) "1"
2) "2"
// 求集合key1和key2的并集
127.0.0.1:6379> SUNION key1 key2
1) "1"
2) "2"
3) "3"
4) "4"
5) "5"
6) "6"
7) "7"
```
这两个命令分别由底层函数sdiffCommand和sunionCommand实现，源码如下：
    
```c
// 并集运算
void sunionCommand(client *c) {
    sunionDiffGenericCommand(c,c->argv+1,c->argc-1,NULL,SET_OP_UNION);
}
// 差集运算
void sdiffCommand(client *c) {
    sunionDiffGenericCommand(c,c->argv+1,c->argc-1,NULL,SET_OP_DIFF);
}
```
从源码中我们可以看到，并集和差集运算都调用了同一个底层函数，也就是并集差集的泛型运算函数。
    
```c
#define SET_OP_UNION 0  // 并集运算
#define SET_OP_DIFF 1  // 差集运算
// 并集差集运算的泛型实现
void sunionDiffGenericCommand(client *c, robj **setkeys, int setnum,
                              robj *dstkey, int op) {
    robj **sets = zmalloc(sizeof(robj*)*setnum);
    setTypeIterator *si;
    robj *ele, *dstset = NULL;
    int j, cardinality = 0;
    int diff_algo = 1;
    // 取出所有集合对象，并添加到集合数组中
    for (j = 0; j < setnum; j++) {
        robj *setobj = dstkey ?
            lookupKeyWrite(c->db,setkeys[j]) :
            lookupKeyRead(c->db,setkeys[j]);
        // 不存在的集合当做NULL处理
        if (!setobj) {
            sets[j] = NULL;
            continue;
        }
        // 检查取出的对象的类型，有对象不是集合则停止执行，并清理集合数组
        if (checkType(c,setobj,OBJ_SET)) {
            zfree(sets);
            return;
        }
        // 记录对象
        sets[j] = setobj;
    }
    // Redis提供了两个算法来进行差集运算
    // 算法1的复杂度为O(N*M)，N表示第一个集合中的元素个数，M表示集合数组中的集合个数
    // 算法2的复杂度为O(N)，N表示所有集合中的元素个数总和
    // 此处需要计算输入来考察选用何种算法比较好
    if (op == SET_OP_DIFF && sets[0]) {
        long long algo_one_work = 0, algo_two_work = 0;
        // 遍历所有集合
        for (j = 0; j < setnum; j++) {
            if (sets[j] == NULL) continue;
            // 计算N*M，也就是setNum*（set[0]中集合的个数）
            algo_one_work += setTypeSize(sets[0]);
            // 计算所有集合的元素总个数
            algo_two_work += setTypeSize(sets[j]);
        }
        // 算法1的常数比较低，优先选用算法1
        algo_one_work /= 2;
        diff_algo = (algo_one_work <= algo_two_work) ? 1 : 2;
        if (diff_algo == 1 && setnum > 1) {
            // 如果选用算法1的话，对除set[0]以外的集合进行排序，这样有助于优化算法的性能
            // 如下是比较函数，其实就是比较每个集合的大小来进行排序
            /* int qsortCompareSetsByRevCardinality(const void *s1, const void *s2) {
             *      robj *o1 = *(robj**)s1, *o2 = *(robj**)s2;
             *
             *      return  (o2 ? setTypeSize(o2) : 0) - (o1 ? setTypeSize(o1) : 0);
             *  }
             */
            qsort(sets+1,setnum-1,sizeof(robj*),
                qsortCompareSetsByRevCardinality);
        }
    }
    // 使用一个临时集合来保存结果集
    dstset = createIntsetObject();
    if (op == SET_OP_UNION) {
        // 执行到此，说明执行的是并集计算
        for (j = 0; j < setnum; j++) {
            if (!sets[j]) continue; // 空集的话直接跳过
            si = setTypeInitIterator(sets[j]);
            while((ele = setTypeNextObject(si)) != NULL) {
                // 直接把元素加入到结果集，如果元素不存在就添加，返回1，反之不作处理并返回0
                if (setTypeAdd(dstset,ele)) cardinality++;
                // 引用计数减1，此处会销毁ele
                decrRefCount(ele);
            }
            setTypeReleaseIterator(si);
        }
    } else if (op == SET_OP_DIFF && sets[0] && diff_algo == 1) {
        // 执行到此说明采用算法1来求差集
        // 程序遍历集合set[0]中的所有元素
        // 并将这个元素和其他集合中的所有元素进行对比
        // 只有这个元素不存在于其他集合时，才会将它添加到结果集中
        si = setTypeInitIterator(sets[0]);
        while((ele = setTypeNextObject(si)) != NULL) {
            for (j = 1; j < setnum; j++) {
                if (!sets[j]) continue; // 空集合直接跳过
                if (sets[j] == sets[0]) break; // 相同的集合直接跳过
                if (setTypeIsMember(sets[j],ele)) break; // 查看ele是否存在于此集合
            }
            if (j == setnum) {
                // 此元素不存在其他任何集合中，添加到结果集
                setTypeAdd(dstset,ele);
                cardinality++;
            }
            decrRefCount(ele);
        }
        setTypeReleaseIterator(si);
    } else if (op == SET_OP_DIFF && sets[0] && diff_algo == 2) {
        // 算法2，遍历所有的集合
        // 如果是集合0，则添加到结果集合
        // 如果非集合0，则将相同的元素从结果集合中移除
        // 算法复杂度为O(N)，N为所有集合元素的总个数
        for (j = 0; j < setnum; j++) {
            if (!sets[j]) continue; // 跳过空集
            si = setTypeInitIterator(sets[j]);
            while((ele = setTypeNextObject(si)) != NULL) {
                if (j == 0) {
                    // 将set[0]添加到结果集
                    if (setTypeAdd(dstset,ele)) cardinality++;
                } else {
                    // 将相同的元素从结果集中移除
                    if (setTypeRemove(dstset,ele)) cardinality--;
                }
                decrRefCount(ele);
            }
            setTypeReleaseIterator(si);
            // 如果结果集是空的话就退出，因为set[0]中的元素都出现到其他集合中
            // 后面的运算也没意义了
            if (cardinality == 0) break;
        }
    }
    /* Output the content of the resulting set, if not in STORE mode */
    // 输出结果集合
    if (!dstkey) {
        // 执行到此，说明执行的是SDIFF或SUNION
        addReplyMultiBulkLen(c,cardinality);
        si = setTypeInitIterator(dstset);
        // 遍历并返回结果集合dstset中所有的元素
        while((ele = setTypeNextObject(si)) != NULL) {
            addReplyBulk(c,ele);
            decrRefCount(ele);
        }
        setTypeReleaseIterator(si);
        decrRefCount(dstset);
    } else {
        // 执行到此，说明执行的是SDIFFSTORE或SUNIONSTORE
        int deleted = dbDelete(c->db,dstkey);  // 删除数据库中的dstkey
        if (setTypeSize(dstset) > 0) {
            // 如果结果集合不为空，则关联到数据库
            dbAdd(c->db,dstkey,dstset);
            addReplyLongLong(c,setTypeSize(dstset));
            notifyKeyspaceEvent(NOTIFY_SET,
                op == SET_OP_UNION ? "sunionstore" : "sdiffstore",
                dstkey,c->db->id);
        } else {
            // 如果结果集合为空，删除
            decrRefCount(dstset);
            addReply(c,shared.czero);
            if (deleted)
                // 发送事件通知
                notifyKeyspaceEvent(NOTIFY_GENERIC,"del",
                    dstkey,c->db->id);
        }
        // 发送消息
        signalModifiedKey(c->db,dstkey);
        server.dirty++;
    }
    zfree(sets);
}
```
上述代码比较长，但是认真看的话就很好懂了，算法的主要思路都写出来了，如有不理解可以在下方留言。
# Set小结

本篇博客分析了Set集合的接口函数，迭代器实现以及命令的源码实现，并在最后对交集并集运算的源码进行了分析。目前，我们对Redis的命令执行源码已经有很深的理解了，但是源码中出现的关于通知，网络，数据库这一块，还是一片模糊，只能靠函数名来稍微理解是什么意思，没事，不急，现在我们先知其然，后面必然会知其所以然。还是一样，大家如果有什么疑惑或者建议，可以在留言区留言，一起学习和讨论Redis！

[0]: /categories/Redis/
[1]: #Set概述
[2]: #Set迭代器
[3]: #编码转换
[4]: #Set基本接口
[5]: #Set命令
[6]: #集合运算
[7]: #Set小结
[8]: http://zcheng.ren/2016/12/14/TheAnnotatedRedisSourceObject/
[9]: http://zcheng.ren/2016/12/09/TheAnnotatedRedisSourceIntset/
[10]: http://zcheng.ren/2016/12/04/TheAnnotatedRedisSourceDict/