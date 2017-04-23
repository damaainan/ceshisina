# Redis源码剖析--压缩列表ziplist

 时间 2016-12-13 17:30:03  ZeeCoder

_原文_[http://zcheng.ren/2016/12/13/TheAnnotatedRedisSourceZiplist/][1]


压缩列表（ziplist）是由 一系列特殊编码的内存块构成的列表，其是Redis的列表建和哈希键的底层实现之一。和整数集合一样，二者都是为Redis节省内存而开发的数据结构。

ziplist可以用来存放字符串或者整数，其存储数据的特点是：比较小的整数或比较短的字符串。Redis的列表建，哈希键，有序集合的底层实现都用到了ziplist。

## ziplist结构 

Redis的ziplist结构由三大部分组成，其分别是列表头（ziplist Header），数据节点（Entries）和列表尾（ziplist tail）,它们在内存上的布局如下：

![][4]

## 头尾结构 

ziplist的头部包含如下三个信息：

* zlbytes：表示压缩列表占总内存的字节数
* zltail：表示压缩列表头和尾之间的偏移量
* zllen：表示压缩列表中节点的数量

ziplist尾部的zlend则表示压缩列表结束，其值固定为0xFF。Redis提供了一个宏定义来表示ziplist header的大小。 

    // 总共10个字节
    #defineZIPLIST_HEADER_SIZE (sizeof(uint32_t)*2+sizeof(uint16_t))
    

## 节点结构 

数据节点部分由若干个节点紧密排列构成，每个节点也由三部分构成，分别是：

* prev_entry_length：编码前置节点的长度，用于从后往前遍历
* encoding：编码属性
* contents：负责保存节点的值

### prev_entry_length 

ziplist在编码前置节点长度的时候，采用以下规则：

* 如果前置节点的长度小于254字节，那么采用1个字节来保存这个长度值
* 如果前置节点的长度大于254字节，则采用5个字节来保存这个长度值，其中，第一个字节被设置为0xFE(254)，用于表示该长度大于254字节，后面四个字节则用来存储前置节点的长度值。

### encoding 

ziplist的节点可以保存字符串值和整数值，二者的编码属性下面一一道来。

* **（一）节点保存字符串值**

如果节点保存的是字符串值，那么该编码大小可能为1字节，2字节或5字节，这与字符串的长度有关。编码部分前两位为00，01或者10，分别对应上述的三种大小，后面的位表示长度大小值。

字符串大小 | 编码长度 | 编码 
-|-|-
<= 63 bytes | 1 bytes | 00bbbbbb 
<= 16383 bytes | 2 bytes | 01bbbbbb xxxxxxxx 
<= 4294967295 bytes | 5 bytes | 10 **____** aaaaaaaa bbbbbbbb cccccccc dddddddd 

* **（二）节点保存整数值**

如果节点保存的是整数值，那么其编码长度固定为1个字节，该字节的前两位固定为11，用于表示节点保存的是整数值。这里也用一个表来说明。

整数类型 | 编码长度 | 编码 
-|-|-
int16_t | （2 bytes）| 1 11000000 
int32_t | （4 bytes）| 1 11010000 
int64_t | （8 bytes）| 1 11100000 
24位有符整数 | 1 | 11110000 
8位有符整数 | 1 | 11111110 
0~12 | 1 | 1111xxxx 

上表中，当编码为1111xxxx时，表示没有内容部分，xxxx已经存放了当前的整数值，包括整数0~12，即xxxx可以表示0000~1101。编码为11111111代表ziplist的结尾。

Redis提供了如下的宏定义用于定义不同的encoding和计算encoding类型。

    // 定义不同的encoding
    // 字符串
    #define ZIP_STR_06B (0 << 6)
    #define ZIP_STR_14B (1 << 6)
    #define ZIP_STR_32B (2 << 6)
    // 整数
    #define ZIP_INT_16B (0xc0 | 0<<4) // '|'按位或，1&0=1，0&0=0
    #define ZIP_INT_32B (0xc0 | 1<<4)
    #define ZIP_INT_64B (0xc0 | 2<<4)
    
    // 计算encoding类型，true或者false
    #define ZIP_IS_STR(enc) (((enc) & 0xc0) < 0xc0) //'&'按位与，1&1=1，1&0=0
    #define ZIP_IS_INT(enc) (!ZIP_IS_STR(enc) && ((enc) & 0x30) < 0x30)
    

## 编码和解码 

在ziplist中，节点的数据结构定义如下： 

    typedef struct zlentry {
        unsigned int prevrawlensize, prevrawlen; // 前置节点长度和编码所需长度
        unsigned int lensize, len; // 当前节点长度和编码所需长度
        unsigned int headersize; // 头的大小
        unsigned char encoding; // 编码类型
        unsigned char *p; // 数据部分
    } zlentry;
    

很显然，内存上不能直接存放结构体，于是，Redis提供了一系列的编码和解码操作函数。这里以编码前置节点长度和解码前置节点长度的源码为例来讲解这一过程： 

    /****************************** 编码前置节点长度信息 *******************************/
    // 将长度信息len写入起止地址为p的内存
    staticunsignedintzipPrevEncodeLength(unsignedchar*p,unsignedintlen){
        if (p == NULL) {
            return (len < ZIP_BIGLEN) ? 1 : sizeof(len)+1;
        } else {
            // 如果长度小于254字节
            if (len < ZIP_BIGLEN) {
                // 采用1字节编码，该字节存放长度信息
                p[0] = len;
                return 1;
            } else {
                // 长度大于254字节
                // 第一个字节固定为0xFE
                p[0] = ZIP_BIGLEN;
                // 后四个字节用来存放长度值
                memcpy(p+1,&len,sizeof(len));
                memrev32ifbe(p+1);
                return 1+sizeof(len);
            }
        }
    }
    /****************************** 解码前置节点长度信息 ******************************/
    // 解压prevlensize编码所需长度
    #defineZIP_DECODE_PREVLENSIZE(ptr, prevlensize) do { \
        // 如果第一个字节的值小于254字节
        if ((ptr)[0] < ZIP_BIGLEN) {                                               \
            (prevlensize) = 1;                                                     \
        } else {                                                                   \
            (prevlensize) = 5;                                                     \
        }                                                                          \
    } while(0);
    // 解码前置节点长度信息
    #defineZIP_DECODE_PREVLEN(ptr, prevlensize, prevlen) do { \
        // 先判断编码类型，1字节或者5字节
        ZIP_DECODE_PREVLENSIZE(ptr, prevlensize);                                  \
        // 1字节的话直接读取长度值
        if ((prevlensize) == 1) {                                                  \
            (prevlen) = (ptr)[0];                                                  \
        // 5字节的话，读取后四个字节的值作为长度值
        } else if ((prevlensize) == 5) {                                           \
            assert(sizeof((prevlensize)) == 4);                                    \
            memcpy(&(prevlen), ((char*)(ptr)) + 1, 4);                             \
            memrev32ifbe(&prevlen);                                                \
        }                                                                          \
    } while(0);
    

在编码解码当前节点的长度，ziplist提供了zipEncodeLength和ZIP_DECODE_LENGTH这两个配套函数来完成。这里就不加赘述了。

## ziplist基本操作 

## 创建空ziplist 

    // 创建一个空的ziplist
    unsignedchar*ziplistNew(void){
        // 空ziplist的大小为11个字节，头部10字节，尾部1字节
        unsigned int bytes = ZIPLIST_HEADER_SIZE+1;
        // 分配内存
        unsigned char *zl = zmalloc(bytes);
        // 设定ziplist的属性
        ZIPLIST_BYTES(zl) = intrev32ifbe(bytes); // 设定ziplist所占的字节数，如有必要进行大小端转换
        ZIPLIST_TAIL_OFFSET(zl) = intrev32ifbe(ZIPLIST_HEADER_SIZE); // 设定尾节点相对头部的偏移量
        ZIPLIST_LENGTH(zl) = 0; // 设定ziplist内的节点数
        // 设定尾部一个字节位0xFF
        zl[bytes-1] = ZIP_END;
        return zl;
    }
    

## 插入节点 

ziplist中插入节点操作由ziplistPush函数完成。 

    // ziplist插入节点只能往头或者尾部插入
    // zl: 待插入的ziplist
    // s，slen: 待插入节点和其长度
    // where: 带插入的位置，0代表头部插入，1代表尾部插入
    unsignedchar*ziplistPush(unsignedchar*zl,unsignedchar*s,unsignedintslen,intwhere){
        unsigned char *p;
        // 获取待插入位置的指针
        p = (where == ZIPLIST_HEAD) ? ZIPLIST_ENTRY_HEAD(zl) : ZIPLIST_ENTRY_END(zl);
        return __ziplistInsert(zl,p,s,slen);
    }
    

真正的插入操作由__ziplistInsert完成。 

    static unsigned char *__ziplistInsert(unsigned char *zl, unsigned char *p, unsigned char *s, unsigned int slen) {
        size_t curlen = intrev32ifbe(ZIPLIST_BYTES(zl)), reqlen; // 当前长度和插入节点后需要的长度
        unsigned int prevlensize, prevlen = 0; // 前置节点长度和编码该长度值所需的长度
        size_t offset;
        int nextdiff = 0;
        unsigned char encoding = 0;
        long long value = 123456789; // 为了避免警告，初始化其值
        zlentry tail;
    
        // 找出待插入节点的前置节点长度
        // 如果p[0]不指向列表末端，说明列表非空，并且p指向其中一个节点
        if (p[0] != ZIP_END) {
            // 解码前置节点p的长度和编码该长度需要的字节
            ZIP_DECODE_PREVLEN(p, prevlensize, prevlen);
        } else {
            // 如果p指向列表末端，表示列表为空
            unsigned char *ptail = ZIPLIST_ENTRY_TAIL(zl);
            
            if (ptail[0] != ZIP_END) {
                // 计算尾节点的长度
                prevlen = zipRawEntryLength(ptail);
            }
    
        }
    
        // 判断是否能够编码为整数
        if (zipTryEncoding(s,slen,&value,&encoding)) {
            // 该节点已经编码为整数，通过encoding来获取编码长度
            reqlen = zipIntSize(encoding);
        } else {
            // 采用字符串来编码该节点
            reqlen = slen;
        }
        // 获取前置节点的编码长度
        reqlen += zipPrevEncodeLength(NULL,prevlen);
        // 获取当前节点的编码长度
        reqlen += zipEncodeLength(NULL,encoding,slen);
    
        // 只要不是插入到列表的末端，都需要判断当前p所指向的节点header是否能存放新节点的长度编码
        // nextdiff保存新旧编码之间的字节大小差，如果这个值大于0
        // 那就说明当前p指向的节点的header进行扩展
        nextdiff = (p[0] != ZIP_END) ? zipPrevLenByteDiff(p,reqlen) : 0;
    
        // 存储p相对于列表zl的偏移地址
        offset = p-zl;
        // 重新分配空间，curlen当前列表的长度
        // reqlen 新节点的全部长度
        // nextdiff 新节点的后继节点扩展header的长度
        zl = ziplistResize(zl,curlen+reqlen+nextdiff);
        // 重新获取p的值
        p = zl+offset;
    
        // 非表尾插入，需要重新计算表尾的偏移量
        if (p[0] != ZIP_END) {
            // 移动现有元素，为新元素的插入提供空间
            memmove(p+reqlen,p-nextdiff,curlen-offset-1+nextdiff);
    
            // p+reqlen为新节点前置节点移动后的位置，将新节点的长度编码至前置节点
            zipPrevEncodeLength(p+reqlen,reqlen);
    
            // 更新列表尾相对于表头的偏移量，将新节点的长度算上
            ZIPLIST_TAIL_OFFSET(zl) =
                intrev32ifbe(intrev32ifbe(ZIPLIST_TAIL_OFFSET(zl))+reqlen);
    
            // 如果新节点后面有多个节点，那么表尾的偏移量需要算上nextdiff的值
            zipEntry(p+reqlen, &tail);
            if (p[reqlen+tail.headersize+tail.len] != ZIP_END) {
                ZIPLIST_TAIL_OFFSET(zl) =
                    intrev32ifbe(intrev32ifbe(ZIPLIST_TAIL_OFFSET(zl))+nextdiff);
            }
        } else {
            // 表尾插入，直接计算偏移量
            ZIPLIST_TAIL_OFFSET(zl) = intrev32ifbe(p-zl);
        }
    
        // 当nextdiff不为0时，表示需要新节点的后继节点对头部进行扩展
        if (nextdiff != 0) {
            offset = p-zl;
            // 需要对p所指向的机电header进行扩展更新
            // 有可能会引起连锁更新
            zl = __ziplistCascadeUpdate(zl,p+reqlen);
            p = zl+offset;
        }
    
        // 将新节点前置节点的长度写入新节点的header
        p += zipPrevEncodeLength(p,prevlen);
        // 将新节点的值长度写入新节点的header
        p += zipEncodeLength(p,encoding,slen);
        // 写入节点值
        if (ZIP_IS_STR(encoding)) {
            memcpy(p,s,slen);
        } else {
            zipSaveInteger(p,value,encoding);
        }
        // 更新列表节点计数
        ZIPLIST_INCR_LENGTH(zl,1);
        return zl;
    }
    

* 什么是连锁更新？

插入节点的时候，有时会引起连锁更新。我们知道，当新节点插入后，需要改变新节点后继节点的header信息中的保存前置节点长度的部分，如果这个pre_entry_length原存放的长度小于254字节，也就是只用了一个字节，现在新节点的长度大于254字节，需要用5个字节保存，这样就要对这个pre_entry_length进行扩展。试想一下，如果扩展之后该节点的整体长度大于254字节了，那么该节点的后继节点是不是也需要更新header信息呢？答案是肯定的，这样就引发了连锁更新，导致新节点后面的一连串节点都需要对header进行扩容。这就是连锁更新。

连锁更新的实现由如下函数完成。 

    // 检查并修复后续节点的空间问题
    static unsigned char *__ziplistCascadeUpdate(unsigned char *zl, unsigned char *p) {
        size_t curlen = intrev32ifbe(ZIPLIST_BYTES(zl)), rawlen, rawlensize;
        size_t offset, noffset, extra;
        unsigned char *np;
        zlentry cur, next;
        
        while (p[0] != ZIP_END) {
            // 将p所指向节点的信息保存到cur结构体中
            zipEntry(p, &cur);
    
            // 当前节点的长度
            rawlen = cur.headersize + cur.len;
            // 编码当前节点的长度所需的字节数
            rawlensize = zipPrevEncodeLength(NULL,rawlen);
    
            // 如果没有后续节点需要更新了，就退出
            if (p[rawlen] == ZIP_END) break;
            // 去除后续节点的信息保存到next结构体中
            zipEntry(p+rawlen, &next);
    
            // 当后续节点的空间已经足够了，就直接退出
            if (next.prevrawlen == rawlen) break;
    
            // 当后续节点的空间不足够，则需要进行扩容操作
            if (next.prevrawlensize < rawlensize) {
                // 记录p的偏移值
                offset = p-zl;
                // 记录需要增加的长度
                extra = rawlensize-next.prevrawlensize;
                // 扩展zl的大小
                zl = ziplistResize(zl,curlen+extra);
                // 获取p相对于新的zl的值
                p = zl+offset;
    
                //记录下一个节点的偏移量
                np = p+rawlen;
                noffset = np-zl;
    
                // 当 next 节点不是表尾节点时，更新列表到表尾节点的偏移量
                if ((zl+intrev32ifbe(ZIPLIST_TAIL_OFFSET(zl))) != np) {
                    ZIPLIST_TAIL_OFFSET(zl) =
                        intrev32ifbe(intrev32ifbe(ZIPLIST_TAIL_OFFSET(zl))+extra);
                }
    
                // 向后移动cur节点之后的数据，为新的header腾出空间
                memmove(np+rawlensize,
                    np+next.prevrawlensize,
                    curlen-noffset-next.prevrawlensize-1);
                zipPrevEncodeLength(np,rawlen);
    
                // 移动指针，继续处理下一个节点
                p += rawlen;
                curlen += extra;
            } else {
                if (next.prevrawlensize > rawlensize) {
                    // 执行到这里，next节点编码前置节点的header空间有5个字节
                    // 但是此时只需要一个字节
                    // Redis不提供缩小操作，而是直接将长度强制性写入五个字节中
                    zipPrevEncodeLengthForceLarge(p+rawlen,rawlen);
                } else {
                    // 运行到这里，说明刚好可以存放
                    zipPrevEncodeLength(p+rawlen,rawlen);
                }
    
                // 退出，代表空间足够，后续空间不需要更改
                break;
            }
        }
        return zl;
    }
    

## 获取指定索引上的节点 

    // 根据index的值，获取压缩列表第index个节点
    unsignedchar*ziplistIndex(unsignedchar*zl,intindex){
        unsigned char *p;
        unsigned int prevlensize, prevlen = 0;
        // index为负，从尾部开始遍历
        if (index < 0) {
            index = (-index)-1;
            // 获取尾指针
            p = ZIPLIST_ENTRY_TAIL(zl);
            if (p[0] != ZIP_END) {
                // 解码前置节点长度
                ZIP_DECODE_PREVLEN(p, prevlensize, prevlen);
                while (prevlen > 0 && index--) {
                    p -= prevlen;
                    // 解码前置节点长度
                    ZIP_DECODE_PREVLEN(p, prevlensize, prevlen);
                }
            }
        } else {        // index为正，从头部开始遍历
            p = ZIPLIST_ENTRY_HEAD(zl);
            while (p[0] != ZIP_END && index--) {
                // 获取当前节点的整体长度，包括pre_entry_length，encoding，contents三部分
                p += zipRawEntryLength(p);
            }
        }
        return (p[0] == ZIP_END || index > 0) ? NULL : p;
    }
    

## 删除给定节点 

    // 删除给定节点，输入压缩列表zl和指向删除节点的指针p
    unsignedchar*ziplistDelete(unsignedchar*zl,unsignedchar**p){
        size_t offset = *p-zl;
        // 调用底层函数__ziplistDelete进行删除操作
        zl = __ziplistDelete(zl,*p,1);
    
        // 删除操作可能会改变zl，因为会重新分配内存
        *p = zl+offset;
        return zl;
    }
    

删除操作的底层实现由__ziplistDelete函数实现。 

    // 删除压缩列表zl中以p起始的num个节点
    static unsigned char *__ziplistDelete(unsigned char *zl, unsigned char *p, unsigned int num) {
        unsigned int i, totlen, deleted = 0;
        size_t offset;
        int nextdiff = 0;
        zlentry first, tail;
        // 获取p指向的节点信息
        zipEntry(p, &first);
        // 计算num个节点占用的内存
        for (i = 0; p[0] != ZIP_END && i < num; i++) {
            p += zipRawEntryLength(p);
            deleted++;
        }
    
        totlen = p-first.p;
        if (totlen > 0) {
            if (p[0] != ZIP_END) {
                // 执行到这里，表示被删除节点后面还存在节点
                
                // 判断最后一个被删除的节点的后继节点的header中的存放前置节点长度的空间
                // 能不能容纳第一个被删除节点的前置节点的长度
                nextdiff = zipPrevLenByteDiff(p,first.prevrawlen);
                p -= nextdiff;
                zipPrevEncodeLength(p,first.prevrawlen);
    
                // 更新尾部相对于头部的便宜
                ZIPLIST_TAIL_OFFSET(zl) =
                    intrev32ifbe(intrev32ifbe(ZIPLIST_TAIL_OFFSET(zl))-totlen);
    
                // 如果被删除节点后面还存在节点，就需要将nextdiff计算在内
                zipEntry(p, &tail);
                if (p[tail.headersize+tail.len] != ZIP_END) {
                    ZIPLIST_TAIL_OFFSET(zl) =
                       intrev32ifbe(intrev32ifbe(ZIPLIST_TAIL_OFFSET(zl))+nextdiff);
                }
    
                // 将被删除节点后面的内存空间移动到删除的节点之后
                memmove(first.p,p,
                    intrev32ifbe(ZIPLIST_BYTES(zl))-(p-zl)-1);
            } else {
                // 执行到这里，表示被删除节点后面没有节点了
                ZIPLIST_TAIL_OFFSET(zl) =
                    intrev32ifbe((first.p-zl)-first.prevrawlen);
            }
    
            // 缩小内存并更新ziplist的长度
            offset = first.p-zl;
            zl = ziplistResize(zl, intrev32ifbe(ZIPLIST_BYTES(zl))-totlen+nextdiff);
            ZIPLIST_INCR_LENGTH(zl,-deleted);
            p = zl+offset;
    
            // 如果nextdiff不等于0，说明被删除节点后面节点的header信息还需要更改
            if (nextdiff != 0)
                // 连锁更新
                zl = __ziplistCascadeUpdate(zl,p);
        }
        return zl;
    }
    

## ziplist小结 

ziplist实际上就可以理解为一个双向列表，其每一个节点都包含了前置指针和后置指针，只是经过了特殊的编码步骤了的。连锁更新是ziplist的一大特点，为了节省内存，ziplist需要存放在一段连续的内存空间上，所以必然会引起节点空间不足的问题。但是，连锁更新发生的概率比较小，不会影响其效率！

到此，Redis的基本数据结构已经全部分析完了，按照预先指定的计划，下一阶段主要剖析Redis各种键的实现，这一部分和Redis的交互相关。


[1]: http://zcheng.ren/2016/12/13/TheAnnotatedRedisSourceZiplist/?utm_source=tuicool&utm_medium=referral

[4]: http://img1.tuicool.com/RRZVfaB.png!web