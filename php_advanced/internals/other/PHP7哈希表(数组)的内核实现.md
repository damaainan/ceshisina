# [PHP7哈希表(数组)的内核实现][0]

 标签： [php内核][1][zend][2][php数组][3][哈希表][4]

 2016-12-01 12:06  817人阅读  

 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [数据结构][10]
1. [索引数组][11]
1. [哈希碰撞][12]
1. [插入查找删除][13]
1. [扩容][14]
1. [重建索引][15]

PHP7+内部哈希表，即[PHP][16]强大array结构的内核实现。

哈希表是PHP内部非常重要的[数据结构][17]，除了PHP用户空间的Array，内核也随处用到，比如function、class的索引、符号表等等都用到了哈希表。

关于哈希结构PHP7+与PHP5+的区别可以翻下[[nikic]][18]早些时候写的一篇文章，这里不作讨论。

示例中的HastTable实现并不是将PHP实际的实现摘出来的，而是根据PHP的实现按照自己的思路具体实现的，可以作为参考。【[https://github.com/pangudashu/anywork/tree/master/hashtable][19]】

## 数据结构

    //zend_types.h
    
    typedef struct _Bucket {
        zval              val;
        zend_ulong        h;                /* hash value (or numeric index)   */
        zend_string      *key;              /* string key or NULL for numerics */
    } Bucket;
    
    typedef struct _zend_array HashTable;
    
    struct _zend_array {
        zend_refcounted_h gc;
        union {
            struct {
                ZEND_ENDIAN_LOHI_4(
                        zend_uchar    flags,
                        zend_uchar    nApplyCount,
                        zend_uchar    nIteratorsCount,
                        zend_uchar    reserve)
            } v;
            uint32_t flags;
        } u;
        uint32_t          nTableMask; //哈希值计算掩码，等于nTableSize的负值(nTableMask = ~nTableSize + 1)
        Bucket           *arData; //存储元素数组，指向第一个Bucket
        uint32_t          nNumUsed; //已用Bucket数
        uint32_t          nNumOfElements; //哈希表已有元素数
        uint32_t          nTableSize; //哈希表总大小，为2的n次方
        uint32_t          nInternalPointer;
        zend_long         nNextFreeElement; //下一个可用的数值索引,如:arr[] = 1;arr["a"] = 2;arr[] = 3;  则nNextFreeElement = 2;
        dtor_func_t       pDestructor;
    };



![HashTable][20]

HashTable中有两个非常相近的值:nNumUsed、nNumOfElements，nNumOfElements表示哈希表已有元素数，那这个值不跟nNumUsed一样吗？为什么要定义两个呢？实际上它们有不同的含义，当将一个元素从哈希表删除时并不会将对应的Bucket移除，而是将Bucket存储的zval标示为IS_UNDEF，只有扩容时发现nNumOfElements与nNumUsed相差达到一定数量(这个数量是:ht->nNumUsed - ht->nNumOfElements > (ht->nNumOfElements >> 5))时才会将已删除的元素全部移除，重新构建哈希表。所以nNumUsed>=nNumOfElements。

HashTable中另外一个非常重要的值arData，这个值指向存储元素数组的第一个Bucket，插入元素时按顺序依次插入数组，比如第一个元素在arData[0]、第二个在arData[1]…arData[nNumUsed]。PHP数组的有序性正是通过arData保证的。

哈希表实现的关键是有一个数组存储哈希值与Bucket的映射，但是HashTable中并没有这样一个索引数组。

实际上这个索引数组包含在arData中，索引数组与Bucket列表一起分配，arData指向了Bucket列表的起始位置，而索引数组可以通过arData指针向前移动访问到，即arData[-1]、arData[-2]、arData[-3]……索引数组的结构是uint32_t,它存储的是Bucket元素在arData中的位置。

所以，整体来看HashTable主要依赖arData实现元素的存储、索引。插入一个元素时先将元素插入Bucket数组，位置是idx，再根据key的哈希值与nTableMask计算出索引数组的位置，将idx存入这个位置；查找时先根据key的哈希值与nTableMask计算出索引数组的位置，获得元素在Bucket数组的位置idx，再从Bucket数组中取出元素。

## 索引数组

索引数组类型是uint32_t[]，存储的值为元素在Bucket数组中的位置

索引位置(nIndex)是如何得到的？我们一般根据哈希值与数组大小取模得到，即key->h % ht->nTableSize，但是PHP是这么计算的：

    nIndex = key->h | ht->nTableMask;


显然位运算要比取模更快。

nTableMask为nTableSize的负数，即:nTableMask = -nTableSize，因为nTableSize等于2^n，所以nTableMask二进制位右侧全部为0，也就保证了|nIndex| <= nTableSize：

    11111111 11111111 11111111 11111000   -8
    11111111 11111111 11111111 11110000   -16
    11111111 11111111 11111111 11100000   -32
    11111111 11111111 11111111 11000000   -64
    11111111 11111111 11111111 10000000   -128


## 哈希碰撞

哈希碰撞是指不同的key可能计算得到相同的哈希值(数值索引的哈希值直接就是数值本身)，但是这些值又需要插入同一个哈希表。一般解决方法是将Bucket串成链表，查找时遍历链表比较key。

PHP的实现也是类似，只是指向冲突元素的指针并没有直接存在Bucket中，而是存在嵌入的zval中，zval的结构：

    struct _zval_struct {
        zend_value        value;            /* value */
        union {
            struct {
                ZEND_ENDIAN_LOHI_4(
                        zend_uchar    type,         /* active type */
                        zend_uchar    type_flags,
                        zend_uchar    const_flags,
                        zend_uchar    reserved)     /* call info for EX(This) */
            } v;
            uint32_t type_info;
        } u1;
        union {
            uint32_t     var_flags;
            uint32_t     next;                 /* hash collision chain */
            uint32_t     cache_slot;           /* literal cache slot */
            uint32_t     lineno;               /* line number (for ast nodes) */
            uint32_t     num_args;             /* arguments number for EX(This) */
            uint32_t     fe_pos;               /* foreach position */
            uint32_t     fe_iter_idx;          /* foreach iterator index */
        } u2;
    };


zval.u2.next存的就是冲突元素在Bucket数组中的位置，所以查找过程类似：

    zend_ulong h = zend_string_hash_val(key);
    uint32_t idx = ht->arHash[h & ht->nTableMask];
    while (idx != INVALID_IDX) {
        Bucket *b = &ht->arData[idx];
        if (b->h == h && zend_string_equals(b->key, key)) {
            return b;
        }
        idx = Z_NEXT(b->val); // b->val.u2.next
    }
    return NULL;


## 插入、查找、删除

这几个基本操作比较简单，不再赘述，定位到元素所在Bucket位置后的操作类似单链表的插入、删除、查找。

## 扩容

哈希表的大小为2^n，插入时如果容量不够则首先检查已删除元素所占比例，如果达到阈值(ht->nNumUsed - ht->nNumOfElements > (ht->nNumOfElements >> 5)，则将已删除元素移除，重建索引，如果未到阈值则进行扩容操作，扩大为当前大小的2倍，将当前Bucket数组复制到新的空间，然后重建索引。

    //zend_hash.c
    static void ZEND_FASTCALL zend_hash_do_resize(HashTable *ht)
    {
    
        IS_CONSISTENT(ht);
        HT_ASSERT(GC_REFCOUNT(ht) == 1);
    
        if (ht->nNumUsed > ht->nNumOfElements + (ht->nNumOfElements >> 5)) { //只有到一定阈值才进行rehash操作
            HANDLE_BLOCK_INTERRUPTIONS();
            zend_hash_rehash(ht); //重建索引数组
            HANDLE_UNBLOCK_INTERRUPTIONS();
        } else if (ht->nTableSize < HT_MAX_SIZE) {  //扩大为两倍
            void *new_data, *old_data = HT_GET_DATA_ADDR(ht);
            uint32_t nSize = ht->nTableSize + ht->nTableSize;
            Bucket *old_buckets = ht->arData;
    
            HANDLE_BLOCK_INTERRUPTIONS();
            new_data = pemalloc(HT_SIZE_EX(nSize, -nSize), ht->u.flags & HASH_FLAG_PERSISTENT); //新分配arData空间，大小为:(sizeof(Bucket) + sizeof(uint32_t)) * nSize
            ht->nTableSize = nSize;
            ht->nTableMask = -ht->nTableSize; //nTableSize负值
            HT_SET_DATA_ADDR(ht, new_data); //将arData指针偏移到Bucket数组起始位置
            memcpy(ht->arData, old_buckets, sizeof(Bucket) * ht->nNumUsed); //将旧的Bucket数组拷到新空间
            pefree(old_data, ht->u.flags & HASH_FLAG_PERSISTENT); //释放旧空间
            zend_hash_rehash(ht); //重建索引数组
            HANDLE_UNBLOCK_INTERRUPTIONS();
        } else {
            zend_error_noreturn(E_ERROR, "Possible integer overflow in memory allocation (%zu * %zu + %zu)", ht->nTableSize * 2, sizeof(Bucket) + sizeof(uint32_t), sizeof(Bucket));
        }
    }
    
    #define HT_SET_DATA_ADDR(ht, ptr) do { \
            (ht)->arData = (Bucket*)(((char*)(ptr)) + HT_HASH_SIZE((ht)->nTableMask)); \
        } while (0)


## 重建索引

当删除元素达到一定数量或扩容后都需要进行索引数组的重建，因为元素所在Bucket位置移动了或哈希数组nTableSize变化了导致原哈希索引变化，已删除的元素将重新可以分配。

![rehash][21]

    //zend_hash.c
    ZEND_API int ZEND_FASTCALL zend_hash_rehash(HashTable *ht)
    {
        Bucket *p;
        uint32_t nIndex, i;
    
        ...
    
        i = 0;
        p = ht->arData;
        if (ht->nNumUsed == ht->nNumOfElements) { //没有已删除的直接遍历Bucket数组重新插入索引数组即可
            do {
                nIndex = p->h | ht->nTableMask;
                Z_NEXT(p->val) = HT_HASH(ht, nIndex);
                HT_HASH(ht, nIndex) = HT_IDX_TO_HASH(i);
                p++;
            } while (++i < ht->nNumUsed);
        } else {
            do {
                if (UNEXPECTED(Z_TYPE(p->val) == IS_UNDEF)) {//有已删除元素需要将其移到后面，压实Bucket数组
    
                    ......
    
                        while (++i < ht->nNumUsed) {
                            p++;
                            if (EXPECTED(Z_TYPE_INFO(p->val) != IS_UNDEF)) {
                                ZVAL_COPY_VALUE(&q->val, &p->val);
                                q->h = p->h;
                                nIndex = q->h | ht->nTableMask;
                                q->key = p->key;
                                Z_NEXT(q->val) = HT_HASH(ht, nIndex);
                                HT_HASH(ht, nIndex) = HT_IDX_TO_HASH(j);
                                if (UNEXPECTED(ht->nInternalPointer == i)) {
                                    ht->nInternalPointer = j;
                                }
                                q++;
                                j++;
                            }
                        }
    
                    ......
    
                    ht->nNumUsed = j;
                    break;
                }
    
                nIndex = p->h | ht->nTableMask;
                Z_NEXT(p->val) = HT_HASH(ht, nIndex);
                HT_HASH(ht, nIndex) = HT_IDX_TO_HASH(i);
                p++;
            }while(++i < ht->nNumUsed);
        }
    
    }

[0]: /pangudashu/article/details/53419992
[1]: http://www.csdn.net/tag/php%e5%86%85%e6%a0%b8
[2]: http://www.csdn.net/tag/zend
[3]: http://www.csdn.net/tag/php%e6%95%b0%e7%bb%84
[4]: http://www.csdn.net/tag/%e5%93%88%e5%b8%8c%e8%a1%a8
[10]: #t0
[11]: #t1
[12]: #t2
[13]: #t3
[14]: #t4
[15]: #t5
[16]: http://lib.csdn.net/base/php
[17]: http://lib.csdn.net/base/datastructure
[18]: http://nikic.github.io/2014/12/22/PHPs-new-hashtable-implementation.html
[19]: https://github.com/pangudashu/anywork/tree/master/hashtable
[20]: ../img//ht.jpg
[21]: ../img//rehash.jpg