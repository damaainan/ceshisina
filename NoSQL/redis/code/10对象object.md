# Redis源码剖析--对象object

 时间 2016-12-15 11:55:40  ZeeCoder

_原文_[http://zcheng.ren/2016/12/14/TheAnnotatedRedisSourceObject/][1]



前面一系列的博客分析了Redis的基本数据结构，有动态字符串sds、 [双端链表sdlist][4] 、 [字典dict][5] 、 [跳跃表skiplist][6] 、 [整数集合intset][7] 和压缩列表ziplist等，这些数据结构对于用户来说是不可见的。 

Redis在这些数据结构的基础上构建了对用户可见的五种类型，分别是string、hash、list、set和zset，为了更方便的使用这五种数据类型，Redis定义了RedisObject结构体来表示它们。今天，我们就一起来看看RedisObject是如何构建的！（如果底层结构不熟悉的，可以点击上述）

## RedisObject数据结构 

在server.h文件中，给出了RedisObject的结构体定义，我们一起来看看。 

    typedef struct redisObject {
        unsigned type:4;
        unsigned encoding:4;
        unsigned lru:LRU_BITS; // LRU_BITS为24
        int refcount;
        void *ptr;
    } robj;
    

其中，ptr指向对象中实际存放的值，这里不需要过多解释，针对其他四个结构体参数，作如下说明：

## 类型type 

Redis的对象有五种类型，分别是string、hash、list、set和zset，type属性就是用来标识着五种数据类型。type占用4个bit位，其取值和类型对应如下： 

    #defineOBJ_STRING 0
    #defineOBJ_LIST 1
    #defineOBJ_SET 2
    #defineOBJ_ZSET 3
    #defineOBJ_HASH 4
    

## 编码类型encoding 

Redis对象的编码方式由encoding参数指定，也就是表示ptr指向的数据以何种数据结构作为底层实现。该字段也占用4个bit位。其取值和对应类型对应如下： 

    #defineOBJ_ENCODING_RAW 0/* Raw representation */
    #defineOBJ_ENCODING_INT 1/* Encoded as integer */
    #defineOBJ_ENCODING_HT 2/* Encoded as hash table */
    #defineOBJ_ENCODING_ZIPMAP 3/* Encoded as zipmap */
    #defineOBJ_ENCODING_LINKEDLIST 4/* Encoded as regular linked list */
    #defineOBJ_ENCODING_ZIPLIST 5/* Encoded as ziplist */
    #defineOBJ_ENCODING_INTSET 6/* Encoded as intset */
    #defineOBJ_ENCODING_SKIPLIST 7/* Encoded as skiplist */
    #defineOBJ_ENCODING_EMBSTR 8/* Embedded sds string encoding */
    #defineOBJ_ENCODING_QUICKLIST 9/* Encoded as linked list of ziplists */
    

在Redis3.2.5版本中，zipmap已不再使用，此处也不再讨论。

上述编码类型对应的底层数据结构实现如下表所示：

编码类型 | 底层实现 
-|-
OBJ_ENCODING_RAW | 简单动态字符串sds 
OBJ_ENCODING_INT | long类型的整数 
OBJ_ENCODING_HT | 字典dict 
OBJ_ENCODING_LINKEDLIST | 双端队列sdlist 
OBJ_ENCODING_ZIPLIST | 压缩列表ziplist 
OBJ_ENCODING_INTSET | 整数集合intset 
OBJ_ENCODING_SKIPLIST | 跳跃表skiplist和字典dict 
OBJ_ENCODING_EMBSTR | EMBSTR编码的简单动态字符串sds 
OBJ_ENCODING_QUICKLIST | 由双端链表和压缩列表构成的快速列表 

Redis的每一种对象类型可以对应不同的编码方式，这就极大地提升了Redis的灵活性和效率。Redis可以根据不同的使用场景，来选择合适的编码方式，五种对象类型对应的底层编码方式如下表所示：

对象类型 | 编码方式 
-|-
OBJ_STRING | OBJ_ENCODING_RAW ,OBJ_ENCODING_INT ,OBJ_ENCODING_EMBSTR 
OBJ_LIST | OBJ_ENCODING_LINKEDLIST ,OBJ_ENCODING_ZIPLIST ,OBJ_ENCODING_QUICKLIST 
OBJ_SET | OBJ_ENCODING_INTSET ,OBJ_ENCODING_HT 
OBJ_ZSET | OBJ_ENCODING_ZIPLIST ,OBJ_ENCODING_SKIPLIST 
OBJ_HASH | OBJ_ENCODING_ZIPLIST ,OBJ_ENCODING_HT 

## 访问时间lru 

lru表示该对象最后一次被访问的时间，其占用24个bit位。保存该值的目的是为了计算该对象的空转时长，便于后续根据空转时长来决定是否释放该键，回收内存。

## 引用计数refcount 

C语言不具备自动内存回收机制，所以Redis对每一个对象设定了引用计数refcount字段，程序通过该字段的信息，在适当的时候自动释放内存进行内存回收。此功能与C++的智能指针相似。

* 当创建一个对象时，其引用计数初始化为1；
* 当这个对象被一个新程序使用时，其引用计数加1；
* 当这个对象不再被一个程序使用时，其引用计数减1；
* 当引用计数为0时，释放该对象，回收内存。

## 对象的基本操作 

Redis关于对象的操作函数主要在server.h和object.c文件中。

## 对象创建 

redis提供以下函数用于创建不同类型的对象。 

    robj *createObject(inttype,void*ptr); // 创建对象，设定其参数
    robj *createStringObject(constchar*ptr,size_tlen); // 创建字符串对象
    robj *createRawStringObject(constchar*ptr,size_tlen); // 创建简单动态字符串编码的字符串对象
    robj *createEmbeddedStringObject(constchar*ptr,size_tlen); // 创建EMBSTR编码的字符串对象
    robj *createStringObjectFromLongLong(longlongvalue); // 根据传入的longlong整型值，创建一个字符串对象
    robj *createStringObjectFromLongDouble(longdoublevalue,inthumanfriendly); // 根据传入的long double类型值，创建一个字符串对象
    robj *createQuicklistObject(void); // 创建快速链表编码的列表对象
    robj *createZiplistObject(void); // 创建压缩列表编码的列表对象
    robj *createSetObject(void); // 创建集合对象
    robj *createIntsetObject(void); // 创建整型集合编码的集合对象
    robj *createHashObject(void); // 创建hash对象
    robj *createZsetObject(void); // 创建zset对象
    robj *createZsetZiplistObject(void); //创建压缩列表编码的zset对象
    

以创建字符串对象为例，来说明整个redisobject的创建过程。 

    /*********************************创建字符串对象************************************/
    #defineOBJ_ENCODING_EMBSTR_SIZE_LIMIT 44
    robj *createStringObject(constchar*ptr,size_tlen){
        if (len <= OBJ_ENCODING_EMBSTR_SIZE_LIMIT)
            // 短字符采用特殊的EMBSTR编码
            return createEmbeddedStringObject(ptr,len);
        else
            // 长字符采用RAW编码
            return createRawStringObject(ptr,len);
    }
    /******************************创建RAW编码的字符串对象********************************/
    // RAW编码需要调用两次内存分配函数
    // 一是为redisObject分内内存，二是为sds字符串分配内存
    robj *createRawStringObject(constchar*ptr,size_tlen){
        // sdsnewlen函数用于创建一个长度为len的sds字符串
        return createObject(OBJ_STRING,sdsnewlen(ptr,len));
    }
    // 通用创建redis对象的函数，采用raw编码方式
    robj *createObject(inttype,void*ptr){
        robj *o = zmalloc(sizeof(*o));
        o->type = type;
        o->encoding = OBJ_ENCODING_RAW;
        o->ptr = ptr;
        o->refcount = 1;
    
        /* Set the LRU to the current lruclock (minutes resolution). */
        o->lru = LRU_CLOCK();
        return o;
    }
    /***************************创建EMBSTR编码的字符串对象********************************/
    // EMRSTR编码只需要调用一次内存分配函数
    // 它的redisobject和sds是放在一段连续的内存空间上
    robj *createEmbeddedStringObject(constchar*ptr,size_tlen){
        robj *o = zmalloc(sizeof(robj)+sizeof(struct sdshdr8)+len+1);
        // sds的起始地址sh
        struct sdshdr8 *sh = (void*)(o+1);
        // 设定redisObject的参数
        o->type = OBJ_STRING;
        o->encoding = OBJ_ENCODING_EMBSTR;
        o->ptr = sh+1;
        o->refcount = 1;
        o->lru = LRU_CLOCK();
        // 设定sds字符串的参数
        sh->len = len;
        sh->alloc = len;
        sh->flags = SDS_TYPE_8;
        if (ptr) {
            memcpy(sh->buf,ptr,len);
            sh->buf[len] = '\0';
        } else {
            memset(sh->buf,0,len+1);
        }
        return o;
    }
    

## 对象释放 

Redis不提供释放整个redis对象的函数。每一个redis对象都有一个引用计数，在引用计数变为0的时候对其整体进行释放，下面五个函数分别用来释放对象中存放的数据，其释放过程中需要判断数据的编码类型，根据不同的编码类型调用不同的底层函数。 

    voidfreeStringObject(robj *o); // 释放字符串对象
    voidfreeListObject(robj *o); // 释放链表对象
    voidfreeSetObject(robj *o); // 释放集合对象
    voidfreeZsetObject(robj *o); // 释放有序集合对象
    voidfreeHashObject(robj *o); // 释放哈希对象
    

我们还是以字符串对象为例，来看看对象的释放过程。 

    // 释放字符串对象
    // 无论是embstr编码还是raw编码，其内存上存放的都是sds字符串
    // 所以只用调用sdsfree就可以对其进行释放
    voidfreeStringObject(robj *o){
        if (o->encoding == OBJ_ENCODING_RAW) {
            sdsfree(o->ptr);
        }
    }
    

字符串对象的释放可能看不出来需要根据编码方式来选择不同的底层释放函数，下面来看看集合的释放函数。 

    voidfreeSetObject(robj *o){
        switch (o->encoding) {
        case OBJ_ENCODING_HT:  // 如果编码方式为哈希
            dictRelease((dict*) o->ptr);
            break;
        case OBJ_ENCODING_INTSET: // 如果编码方式为整数集合
            zfree(o->ptr);
            break;
        default:
            serverPanic("Unknown set encoding type");
        }
    }
    

那么，什么时候释放整个Redis对象呢？答案在下面函数。 

    // 引用计数减1
    voiddecrRefCount(robj *o){
        // 引用计数为小于等于0，报错
        if (o->refcount <= 0) serverPanic("decrRefCount against refcount <= 0");
        // 引用计数等于1，减1后为0
        // 需要释放整个redis对象
        if (o->refcount == 1) {
            switch(o->type) {
            // 根据对象类型，调用不同的底层函数对对象中存放的数据结构进行释放
            case OBJ_STRING: freeStringObject(o); break;
            case OBJ_LIST: freeListObject(o); break;
            case OBJ_SET: freeSetObject(o); break;
            case OBJ_ZSET: freeZsetObject(o); break;
            case OBJ_HASH: freeHashObject(o); break;
            default: serverPanic("Unknown object type"); break;
            }
            // 释放redis对象
            zfree(o);
        } else {
            // 引用计数减1
            o->refcount--;
        }
    }
    

同样，关于引用计数，redis还提供了增加引用计数的函数，这里也一并说了。 

    // 增加对象的引用计数+1
    voidincrRefCount(robj *o){
        o->refcount++; // 引用计数加1
    }
    

## 其他操作函数 

redis在object.c文件中还提供了很多API接口函数。下面只罗列出函数名和功能，具体实现也比较简单，这里就不赘述。 

    // 复制一个字符串对象
    robj *dupStringObject(robj *o);
    // 判断一个对象是否能够用longlong型整数表示
    intisObjectRepresentableAsLongLong(robj *o,longlong*llongval);
    // 尝试对一个对象进行压缩以节省内存，如果无法压缩则增加引用计数后返回
    robj *tryObjectEncoding(robj *o);
    // 对一个对象进行解码，如果不能解码则增加其引用计数并返回，反则返回一个新对象
    robj *getDecodedObject(robj *o);
    // 获取字符串对象的长度
    size_t stringObjectLen(robj *o);
    // getLongLongFromObject函数的封装，如果发生错误可以发回指定响应消息
    intgetLongFromObjectOrReply(client *c, robj *o,long*target,constchar*msg);
    // 检查o的类型是否与type一致
    intcheckType(client *c, robj *o,inttype);
    // getLongLongFromObject的封装，如果发生错误则可以发出指定的错误消息
    intgetLongLongFromObjectOrReply(client *c, robj *o,longlong*target,constchar*msg);
    // 从字符串对象中解码出一个double类型的整数
    intgetDoubleFromObjectOrReply(client *c, robj *o,double*target,constchar*msg);
    // 从字符串对象中解码出一个long long类型的整数
    intgetLongLongFromObject(robj *o,longlong*target);
    // 从字符串对象中解码出一个long double类型的整数
    intgetLongDoubleFromObject(robj *o,longdouble*target);
    // getLongDoubleFromObject的封装，如果发生错误则可以发出指定的错误消息
    intgetLongDoubleFromObjectOrReply(client *c, robj *o,longdouble*target,constchar*msg);
    // 返回编码的字符串表示，如OBJ_ENCODING_RAW编码就返回raw
    char*strEncoding(intencoding);
    // 以二进制方式比较两个字符串对象
    intcompareStringObjects(robj *a, robj *b);
    // 以本地指定的文字排列次序coll方式比较两个字符串
    intcollateStringObjects(robj *a, robj *b);
    // 比较两个字符串对象是否相同
    intequalStringObjects(robj *a, robj *b);
    // 计算给定对象的闲置时长，使用近似LRU算法
    unsignedlonglongestimateObjectIdleTime(robj *o);
    

## Object交互指令 

Redis提供了三个命令用于获取对象的一些参数。其命令形式如下：

* object refcount <key> 返回key所指的对象的引用计数
* object encoding <key> 返回key所指的对象中存放的数据的编码方式
* object idletime <key> 返回key所指的对象的空转时长

这些交互指令的实现由如下函数完成。 

    voidobjectCommand(client *c){
        robj *o;
        // 返回key所指的对象的引用计数
        if (!strcasecmp(c->argv[1]->ptr,"refcount") && c->argc == 3) {
            if ((o = objectCommandLookupOrReply(c,c->argv[2],shared.nullbulk))
                    == NULL) return;
            addReplyLongLong(c,o->refcount);
        } else if (!strcasecmp(c->argv[1]->ptr,"encoding") && c->argc == 3) {
            // 返回key所指的对象中存放的数据的编码方式
            if ((o = objectCommandLookupOrReply(c,c->argv[2],shared.nullbulk))
                    == NULL) return;
            addReplyBulkCString(c,strEncoding(o->encoding));
        } else if (!strcasecmp(c->argv[1]->ptr,"idletime") && c->argc == 3) {
            // 返回key所指的对象的空转时长
            if ((o = objectCommandLookupOrReply(c,c->argv[2],shared.nullbulk))
                    == NULL) return;
            addReplyLongLong(c,estimateObjectIdleTime(o)/1000);
        } else {
            // 指令错误，返回提示
            addReplyError(c,"Syntax error. Try OBJECT (refcount|encoding|idletime)");
        }
    }
    

## redisObject小结 

Redis为用户提供了五种数据结构，分别是string，hash，list，set和zset，每种数据结构的内部都至少有两种编码方式，不同的编码方式适用于不同的使用场景。Redis的对象带有引用计数功能，当一个对象不再被使用时（即引用计数为0），对象所占的内存就会被自动释放。同时，Redis还会对每一个对象记录其最近被使用的时间，从而计算对象的空转时长，便于程序在适当的时候释放内存。


[1]: http://zcheng.ren/2016/12/14/TheAnnotatedRedisSourceObject/?utm_source=tuicool&utm_medium=referral

[4]: http://zcheng.ren/2016/12/03/TheAnnotatedRedisSourceSdlist/
[5]: http://zcheng.ren/2016/12/04/TheAnnotatedRedisSourceDict/
[6]: http://zcheng.ren/2016/12/06/TheAnnotatedRedisSourceZskiplist/
[7]: http://zcheng.ren/2016/12/09/TheAnnotatedRedisSourceIntset/