# PHP内核哈希表实现 

[2016-11-01][0]

### 哈希表结构

先看看哈希表的结构

```c
typedef struct _hashtable { 
    uint nTableSize;        // hash Bucket的大小，最小为8，以2x增长。
    uint nTableMask;        // nTableSize-1 ， 索引取值的优化
    uint nNumOfElements;    // hash Bucket中当前存在的元素个数，count()函数会直接返回此值 
    ulong nNextFreeElement; // 下一个数字索引的位置，当数组成员没有指定键值时则采用该数字索引
    Bucket *pInternalPointer;   // 当前遍历的指针（foreach比for快的原因之一）
    Bucket *pListHead;          // 存储数组头元素指针
    Bucket *pListTail;          // 存储数组尾元素指针
    Bucket **arBuckets;         // 存储hash数组
    dtor_func_t pDestructor;    // 在删除元素时执行的回调函数，用于资源的释放
    zend_bool persistent;       //指出了Bucket内存分配的方式。如果persisient为TRUE，则使用操作系统本身的内存分配函数为Bucket分配内存，否则使用PHP的内存分配函数。
    unsigned char nApplyCount; // 标记当前hash Bucket被递归访问的次数（防止多次递归）
    zend_bool bApplyProtection;// 标记当前hash桶允许不允许多次访问，不允许时，最多只能递归3次
#if ZEND_DEBUG
    int inconsistent;
#endif
} HashTable;
```

PHP的GC（内存回收）机制就是根据nNumOfElements大小来进行判断，当采取unset()时，该字段的值减一，在字段值为0时进行内存回收  
首先初始化HashTable的大小为8

### 初始化

```c
ZEND_API int _zend_hash_init(HashTable *ht, uint nSize, hash_func_t pHashFunction,
                    dtor_func_t pDestructor, zend_bool persistent ZEND_FILE_LINE_DC)
{
    uint i = 3;
    //...
    if (nSize >= 0x80000000) {
        /* prevent overflow */
        ht->nTableSize = 0x80000000;
    } else {
        while ((1U << i) < nSize) {
            i++;
        }
        ht->nTableSize = 1 << i;
    }
    // ...
    ht->nTableMask = ht->nTableSize - 1;
 
    /* Uses ecalloc() so that Bucket* == NULL */
    if (persistent) {
        tmp = (Bucket **) calloc(ht->nTableSize, sizeof(Bucket *));
        if (!tmp) {
            return FAILURE;
        }
        ht->arBuckets = tmp;
    } else {
        tmp = (Bucket **) ecalloc_rel(ht->nTableSize, sizeof(Bucket *));
        if (tmp) {
            ht->arBuckets = tmp;
        }
    }
 
    return SUCCESS;
}
```

例如如果设置初始大小为10，则上面的算法将会将大小调整为16。也就是始终将大小调整为接近初始大小的2的整数次方。  
为什么会做这样的调整呢？我们先看看HashTable将哈希值映射到槽位的方法，上一小节我们使用了取模的方式来将哈希值映射到槽位，例如大小为8的哈希表，哈希值为100， 则映射的槽位索引为: 100 % 8 = 4，由于索引通常从0开始，所以槽位的索引值为3，在PHP中使用如下的方式计算索引：

h = zend_inline_hash_func(arKey, nKeyLength);  
nIndex = h & ht->nTableMask;  
从上面的_zend_hash_init()函数中可知，ht->nTableMask的大小为ht->nTableSize -1。这里使用&操作而不是使用取模，这是因为是相对来说取模操作的消耗和按位与的操作大很多。

### Bucket结构

```c
typedef struct bucket {
    ulong h;            // 对char *key进行hash后的值，或者是用户指定的数字索引值
    uint nKeyLength;    // hash关键字的长度，如果数组索引为数字，此值为0
    void *pData;        // 指向value，一般是用户数据的副本，如果是指针数据，则指向pDataPtr
    void *pDataPtr;     //如果是指针数据，此值会指向真正的value，同时上面pData会指向此值
    struct bucket *pListNext;   // 整个hash表的下一元素
    struct bucket *pListLast;   // 整个哈希表该元素的上一个元素
    struct bucket *pNext;       // 存放在同一个hash Bucket内的下一个元素
    struct bucket *pLast;       // 同一个哈希bucket的上一个元素
    // 保存当前值所对于的key字符串，这个字段只能定义在最后，实现变长结构体
    char arKey[1];              
} Bucket;
```

如上面各字段的注释。h字段保存哈希表key哈希后的值。这里保存的哈希值而不是在哈希表中的索引值，这是因为索引值和哈希表的容量有直接关系，如果哈希表扩容了，那么这些索引还得重新进行哈希在进行索引映射，这也是一种优化手段。在PHP中可以使用字符串或者数字作为数组的索引。数字索引直接就可以作为哈希表的索引，数字也无需进行哈希处理。h字段后面的nKeyLength字段是作为key长度的标示，如果索引是数字的话，则nKeyLength为0。在PHP数组中如果索引字符串可以被转换成数字也会被转换成数字索引。_所以在PHP中例如’10’，’11’这类的字符索引和数字索引10， 11没有区别_。

### HashTable接口的调用

这里介绍的是插入操作，对于数组的添加操作，其最终调用的都是该接口  
所以这里主要针对插入接口进行介绍

```c
ZEND_API int _zend_hash_add_or_update(HashTable *ht, const char *arKey, uint nKeyLength, void *pData, uint nDataSize, void **pDest, int flag ZEND_FILE_LINE_DC)
{
     //...省略变量初始化和nKeyLength <=0 的异常处理
 
    h = zend_inline_hash_func(arKey, nKeyLength);
    nIndex = h & ht->nTableMask;
 
    p = ht->arBuckets[nIndex];
    while (p != NULL) {
        if ((p->h == h) && (p->nKeyLength == nKeyLength)) {
            if (!memcmp(p->arKey, arKey, nKeyLength)) { // 更新操作
                if (flag & HASH_ADD) {
                    return FAILURE;
                }
                HANDLE_BLOCK_INTERRUPTIONS();
 
                //..省略debug输出
                if (ht->pDestructor) {
                    ht->pDestructor(p->pData);
                }
                UPDATE_DATA(ht, p, pData, nDataSize);
                if (pDest) {
                    *pDest = p->pData;
                }
                HANDLE_UNBLOCK_INTERRUPTIONS();
                return SUCCESS;
            }
        }
        p = p->pNext;
    }
 
    p = (Bucket *) pemalloc(sizeof(Bucket) - 1 + nKeyLength, ht->persistent);
    if (!p) {
        return FAILURE;
    }
    memcpy(p->arKey, arKey, nKeyLength);
    p->nKeyLength = nKeyLength;
    INIT_DATA(ht, p, pData, nDataSize);
    p->h = h;
    CONNECT_TO_BUCKET_DLLIST(p, ht->arBuckets[nIndex]); //Bucket双向链表操作
    if (pDest) {
        *pDest = p->pData;
    }
 
    HANDLE_BLOCK_INTERRUPTIONS();
    CONNECT_TO_GLOBAL_DLLIST(p, ht);    // 将新的Bucket元素添加到数组的链接表的最后面
    ht->arBuckets[nIndex] = p;
    HANDLE_UNBLOCK_INTERRUPTIONS();
 
    ht->nNumOfElements++;
    ZEND_HASH_IF_FULL_DO_RESIZE(ht);        /* 如果此时数组的容量满了，则对其进行扩容。*/
    return SUCCESS;
}

```

1. 生成hash值，通过与nTableMask执行与操作，获取在arBuckets数组中的Bucket。
1. 如果Bucket中已经存在元素，则遍历整个Bucket，查找是否存在相同的key值元素，如果有并且是update调用，则执行update数据操作。
1. 创建新的Bucket元素，初始化数据，并将新元素添加到当前hash值对应的Bucket链表的最前面（CONNECT_TO_BUCKET_DLLIST）。
1. 将新的Bucket元素添加到数组的链接表的最后面（CONNECT_TO_GLOBAL_DLLIST）。
1. 将元素个数加1，如果此时数组的容量满了，则对其进行扩容。这里的判断是依据nNumOfElements和nTableSize的大小。如果nNumOfElements > nTableSize则会调用zend_hash_do_resize以2X的方式扩容（nTableSize << 1）。

更多HashTable的具体接口操作可以查看上一节内容

### 参考资料

[PHP的哈希表实现][1]

[0]: https://www.jwlchina.cn/2016/11/01/PHP内核哈希表实现/
[1]: http://www.kancloud.cn/kancloud/php-internals/42760