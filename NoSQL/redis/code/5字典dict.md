# Redis源码剖析--字典dict

 时间 2016-12-05 18:19:00  ZeeCoder

原文[http://zcheng.ren/2016/12/04/TheAnnotatedRedisSourceDict/][2]



字典是Redis中的一个非常重要的底层数据结构，其应用相当广泛。Redis的数据库就是使用字典作为底层实现的，对数据库的增、删、查、改都是建立在对字典的操作上。此外，字典还是Redis中哈希键的底层实现，当一个哈希键包含的键值对比较多，或者键值对中的元素都是比较长的字符串时，Redis就会使用字典作为哈希键的底层实现。

Redis中的字典采用哈希表作为底层实现，在Redis源码文件中，字典的实现代码在dict.c和dict.h文件中。 

## dict数据结构 

Redis定义了dictEntry，dictType，dictht和dict四个结构体来实现字典结构，下面来分别介绍这四个结构体。

## 哈希表节点（dictEntry） 

字典中每一对键值都以dictEntry节点的形式存放，其结构体实现如下： 

```c
    typedef struct dictEntry {
        void *key;  // 键
        union {   
            void *val;
            uint64_t u64;
            int64_t s64;
            double d;
        } v;  // 值
        struct dictEntry *next;  // 指向下一个哈希表节点
        // 此处可以看出字典采用了开链法才解决哈希冲突
    } dictEntry;
```

## 哈希表dictht 

```c
    typedef struct dictht {
        dictEntry **table; // 哈希表数组
        unsigned long size;  // 哈希表大小
        unsigned long sizemask; // 哈希表大小掩码，用于计算索引值
        unsigned long used;  // 该哈希表中已有节点的数量
    } dictht;
```

## 字典dict 

```c
    typedef struct dict {
        dictType *type; // 字典类型，保存一些用于操作特定类型键值对的函数
        void *privdata; // 私有数据，保存需要传给那些类型特定函数的可选数据
        dictht ht[2];  // 一个字典结构包括两个哈希表
        long rehashidx; // rehash索引，不进行rehash时其值为-1
        int iterators; // 当前正在使用的迭代器数量
    } dict;
```

## 字典类型函数dictType 

```c
    typedef struct dictType {
        // 计算哈希值的函数
        unsigned int (*hashFunction)(const void *key);
        // 复制键的函数
        void *(*keyDup)(void *privdata, const void *key);
        // 复制值的函数
        void *(*valDup)(void *privdata, const void *obj);
        // 比较键的函数
        int (*keyCompare)(void *privdata, const void *key1, const void *key2);
        // 销毁键的函数
        void (*keyDestructor)(void *privdata, void *key);
        // 销毁值的函数
        void (*valDestructor)(void *privdata, void *obj);
    } dictType;
```

看完这四个结构的源码之后，脑海中应该有字典的一个模糊结构了。下面用一张图来帮助大家弄清楚Redis字典的结构。

![][5]

## 哈希算法 

当往字典中添加键值对时，需要根据键的大小计算出哈希值和索引值，然后再根据索引值，将包含新键值对的哈希表节点放到哈希表数组的指定索引上面。

```c
    // 计算哈希值
    h = dictHashKey(d, key);
    // 调用哈希算法计算哈希值
    #defined ictHashKey(d, key) (d)->type->hashFunction(key)
```

Redis提供了三种计算哈希值的函数，其分别是：

* Thomas Wang’s 32 bit Mix函数，对一个整数进行哈希，该方法在dictIntHashFunction中实现
* 使用MurmurHash2哈希算法对字符串进行哈希，该方法在dictGenHashFunction中实现
* 使用基于djb哈希的一种简单的哈希算法，该方法在dictGenCaseHashFunction中实现。

以上三种哈希算法本博客不做过多解释，具体可以参考源码。

计算出哈希值之后，需要计算其索引。Redis采用下列算式来计算索引值。 

```c
    // 举例：h为5，哈希表的大小初始化为4，sizemask则为size-1，
    // 于是h&sizemask = 2，
    // 所以该键值对就存放在索引为2的位置
    idx = h & d->ht[table].sizemask;
```

## rehash算法 

rehash是Redis字典实现的一个重要操作。dict采用链地址法来处理哈希冲突，那么随着数据存放量的增加，必然会造成冲突链表越来越长，最终会导致字典的查找效率显著下降。这种情况下，就需要对字典进行扩容。另外，当字典中键值对过少时，就需要对字典进行收缩来节省空间，这些扩容和收缩的过程就采用rehash来实现。

通常情况下，字典的键值对数据都存放在ht[0]里面，如果此时需要对字典进行rehash，会进行如下步骤：

* 为ht[1]哈希表分配空间，空间的大小取决于要执行的操作和字典中键值对的个数
* 将保存在ht[0]中的键值对重新计算哈希值和索引，然后存放到ht[1]中。
* 当ht[0]中的数据全部迁移到ht[1]之后，将ht[1]设为ht[0]，并为ht[1]新创建一个空白哈希表，为下一次rehash做准备。

rehash算法的源码如下： 

```c
    // 执行N步渐进式的rehash操作，如果仍存在旧表中的数据迁移到新表，则返回1，反之返回0
    // 每一步操作移动一个索引值下的键值对到新表
    int dictRehash(dict *d,int n){
        int empty_visits = n*10; // 最大允许访问的空桶值，也就是该索引下没有键值对
        if (!dictIsRehashing(d)) return 0;
    
        while(n-- && d->ht[0].used != 0) {
            dictEntry *de, *nextde;
    
            // rehashidx不能大于哈希表的大小
            assert(d->ht[0].size > (unsigned long)d->rehashidx);
            while(d->ht[0].table[d->rehashidx] == NULL) {
                d->rehashidx++;
                if (--empty_visits == 0) return 1;
            }
            // 获取需要rehash的索引值下的链表
            de = d->ht[0].table[d->rehashidx];
            // 将该索引下的键值对全部转移到新表
            while(de) {
                unsigned int h;
    
                nextde = de->next;
                // 计算该键值对在新表中的索引值
                h = dictHashKey(d, de->key) & d->ht[1].sizemask;
                de->next = d->ht[1].table[h];
                d->ht[1].table[h] = de;
                d->ht[0].used--;
                d->ht[1].used++;
                de = nextde;
            }
            d->ht[0].table[d->rehashidx] = NULL;
            d->rehashidx++;
        }
    
        // 键值是否整个表都迁移完成
        if (d->ht[0].used == 0) {
            // 清除ht[0]
            zfree(d->ht[0].table);
            // 将ht[1]转移到ht[0]
            d->ht[0] = d->ht[1];
            // 重置ht[1]为空哈希表
            _dictReset(&d->ht[1]);
            // 完成rehash，-1代表没有进行rehash操作
            d->rehashidx = -1;
            return 0;
        }
    
        // 如果没有完成则返回1
        return 1;
    }
```

Redis中rehash的操作不是一次完成，而是渐进式完成，每次只移动若干个索引下的键值对链表到新表（在ht[0]中采用rehashidx参数来记录当前需要rehash的索引值）。为此，Redis提供了两种渐进式的操作来进行rehash。

一种是按按索引值，每次只移动一个索引值下的键值对数据到新哈希表里。 

```c
    // 在执行查询和更新操作时，如果符合rehash条件就会触发一次rehash操作，每次执行一步
    static void _dictRehashStep(dict *d) {
        if (d->iterators == 0) dictRehash(d,1);
    }
```

另一种是按照时间，每次执行一段固定的时间。 

```c
    // 获取当前的时间戳（一毫秒为单位）
    long long timeInMilliseconds(void){
        struct timeval tv;
    
        gettimeofday(&tv,NULL);
        return (((long long)tv.tv_sec)*1000)+(tv.tv_usec/1000);
    }
    // rehash操作每次执行ms时间就退出
    int dictRehashMilliseconds(dict *d,int ms){
        long long start = timeInMilliseconds();
        int rehashes = 0;
    
        while(dictRehash(d,100)) {  // 每次执行100步
            rehashes += 100;
            if (timeInMilliseconds()-start > ms) break; // 如果时间超过ms就退出
        }
        return rehashes;
    }
```

分析到这里，可能最想弄清楚的就是什么时候需要rehash，Redis定义了一个负载因子dict_force_resize_ratio，该因子的初始值为5，如果满足一下条件，则需要进行rehash操作。 

```c
    // 哈希表中键值对的数量与哈希表的大小的比大于负载因子
    d->ht[0].used/d->ht[0].size > dict_force_resize_ratio
```

到此，Redis的整个rehash操作基本上理清楚了。

## dict基本操作 

## dict创建 

Redis调用dictCreate来创建一个空字典 

```c
    dict *dictCreate(dictType *type,
            void *privDataPtr)
    {
        dict *d = zmalloc(sizeof(*d));
        // 字典初始化
        _dictInit(d,type,privDataPtr);
        return d;
    }
    
    int _dictInit(dict *d, dictType *type,
            void *privDataPtr)
    {
        _dictReset(&d->ht[0]);
        _dictReset(&d->ht[1]);
        d->type = type; // 设定字典类型
        d->privdata = privDataPtr;
        d->rehashidx = -1; // 初始化为-1，未进行rehash操作
        d->iterators = 0; // 正在使用的迭代器数量
        return DICT_OK;
    }
```

## 添加键值对 

向字典中添加键值对时需要考虑如下情况：

* 如果此时没有进行rehash操作，直接计算出索引添加到ht[0]中
* 如果此刻正在进行rehash操作，则根据ht[1]的参数计算出索引值，添加到ht[1]中

添加键值对的功能由dictAdd函数来实现，其源码如下： 

```c
    int dictAdd(dict *d,void *key,void *val)
    {
        // 往字典中添加一个只有key的键值对
        dictEntry *entry = dictAddRaw(d,key);
    
        // 如果添加失败，则返回错误
        if (!entry) return DICT_ERR;
        // 为添加的只有key键值对设定值
        dictSetVal(d, entry, val);
        return DICT_OK;
    }
    // 添加只有key的键值对，如果成功则返回该键值对，反之则返回空
    dictEntry *dictAddRaw(dict *d,void *key)
    {
        int index;
        dictEntry *entry;
        dictht *ht;
    
        // 如果正在进行rehash操作，则先执行rehash操作
        if (dictIsRehashing(d)) _dictRehashStep(d);
    
        // 获取新键值对的索引值，如果key存在则返回-1
        if ((index = _dictKeyIndex(d, key)) == -1)
            return NULL;
    
        // 如果正在进行rehash则添加到ht[1]，反之则添加到ht[0]
        ht = dictIsRehashing(d) ? &d->ht[1] : &d->ht[0];
        // 申请内存，存储新键值对
        entry = zmalloc(sizeof(*entry));
        // 使用开链法来处理哈希冲突
        entry->next = ht->table[index];
        ht->table[index] = entry;
        ht->used++;
    
        // 设定键的大小
        dictSetKey(d, entry, key);
        return entry;
    }
```

上述添加方式在，在存在该key的时候，直接返回NULL，Redis还提供了另一种添加键值对的函数，它在处理存在相同key的情况时，直接用新键值对来替换旧键值对。其实现如下： 

```c
    int dictReplace(dict *d,void *key,void *val)
    {
        dictEntry *entry, auxentry;
    
        // 直接调用dictAdd函数，如果添加成功就表示没有存在相同的key
        if (dictAdd(d, key, val) == DICT_OK)
            return 1;
        // 如果存在相同的key，则先获取该键值对
        entry = dictFind(d, key);
        // 然后用新的value来替换旧value
        auxentry = *entry;
        dictSetVal(d, entry, val);
        dictFreeVal(d, &auxentry);
        return 0;
    }
```

## 查找键值对 

根据键值对的键大小在字典中查找对应的键值对。 

```c
    dictEntry *dictFind(dict *d,const void *key)
    {
        dictEntry *he;
        unsigned int h, idx, table;
        // 字典为空，返回NULL
        if (d->ht[0].used + d->ht[1].used == 0) return NULL; 
        // 如果正在进行rehash，则执行rehash操作
        if (dictIsRehashing(d)) _dictRehashStep(d);
        // 计算哈希值
        h = dictHashKey(d, key);
        // 在两个表中查找对应的键值对
        for (table = 0; table <= 1; table++) {
            // 根据掩码来计算索引值
            idx = h & d->ht[table].sizemask;
            // 得到该索引值下的存放的键值对链表
            he = d->ht[table].table[idx];
            while(he) {
                // 如果找到该key直接返回
                if (key==he->key || dictCompareKeys(d, key, he->key))
                    return he;
                // 找下一个
                he = he->next;
            }
            // 如果没有进行rehash，则直接返回
            if (!dictIsRehashing(d)) return NULL;
        }
        return NULL;
    }
```

Redis还定义了dictFetchValue函数，用来返回给定键的值，底层实现还是调用dictFind函数。 

```c
    void *dictFetchValue(dict *d,const void *key){
        dictEntry *he;
        // 获取该键值对
        he = dictFind(d,key);
        // 返回该key对应的value
        return he ? dictGetVal(he) : NULL;
    }
```

此外，对于字典的查找，Redis还定义了一个函数，用于从字典中随机返回一个键值对。 

```c
    dictEntry *dictGetRandomKey(dict *d)
    {
        dictEntry *he, *orighe;
        unsigned int h;
        int listlen, listele;
        // 哈希表为空，直接返回NULL
        if (dictSize(d) == 0) return NULL;
        // 如果正在进行rehash，则执行一次rehash操作
        if (dictIsRehashing(d)) _dictRehashStep(d);
        // 随机返回一个键的具体操作是：先随机选取一个索引值，然后在该索引值
        // 对应的键值对链表中随机选取一个键值对返回
        if (dictIsRehashing(d)) {
            do {
                // 如果正在进行rehash，则需要考虑两个哈希表中的数据
                h = d->rehashidx + (random() % (d->ht[0].size +
                                                d->ht[1].size -
                                                d->rehashidx));
                he = (h >= d->ht[0].size) ? d->ht[1].table[h - d->ht[0].size] :
                                          d->ht[0].table[h];
            } while(he == NULL);
        } else {
            do {
                h = random() & d->ht[0].sizemask;
                he = d->ht[0].table[h];
            } while(he == NULL);
        }
    
        // 到这里，就随机选取了一个非空的键值对链表
        // 然后随机从这个拥有相同索引值的链表中随机选取一个键值对
        listlen = 0;
        orighe = he;
        while(he) {
            he = he->next;
            listlen++;
        }
        listele = random() % listlen;
        he = orighe;
        while(listele--) he = he->next;
        return he;
    }
```

## 删除键值对 

dictDelete函数用于从字典中删除给定键所对应的键值对，其有两种形式： 

```c
    // 删除该键值对，并释放键和值
    int dictDelete(dict *ht,const void *key){
        return dictGenericDelete(ht,key,0);
    }
    // 删除该键值对，不释放键和值
    int dictDeleteNoFree(dict *ht,const void *key){
        return dictGenericDelete(ht,key,1);
    }
```

这两个函数的底层实现均由dictGenericDelete函数来实现： 

```c
    // 查找并删除指定键对应的键值对
    static int dictGenericDelete(dict *d,const void *key,int nofree)
    {
        unsigned int h, idx;
        dictEntry *he, *prevHe;
        int table;
        // 字典为空
        if (d->ht[0].size == 0) return DICT_ERR; 
        // 如果正在进行rehash，则出发一次rehash操作
        if (dictIsRehashing(d)) _dictRehashStep(d);
        // 计算哈希值
        h = dictHashKey(d, key);
    
        for (table = 0; table <= 1; table++) {
            // 计算索引值
            idx = h & d->ht[table].sizemask;
            he = d->ht[table].table[idx];
            prevHe = NULL;
            // 执行在链表中删除某个节点的操作
            while(he) {
                if (key==he->key || dictCompareKeys(d, key, he->key)) {
                    /* Unlink the element from the list */
                    if (prevHe)
                        prevHe->next = he->next;
                    else
                        d->ht[table].table[idx] = he->next;
                    if (!nofree) {
                        // 释放键和值
                        dictFreeKey(d, he);
                        dictFreeVal(d, he);
                    }
                    zfree(he);
                    d->ht[table].used--;
                    return DICT_OK;
                }
                prevHe = he;
                he = he->next;
            }
            // 如果没有进行rehash操作，则没必要对ht[1]进行查找
            if (!dictIsRehashing(d)) break;
        }
        return DICT_ERR; /* not found */
    }
```

## 字典删除 

dictRelease函数用于删除和释放整个字典结构。 

```c
    void dictRelease(dict *d)
    {
        _dictClear(d,&d->ht[0],NULL); // 清除哈希表ht[0]
        _dictClear(d,&d->ht[1],NULL); // 清除哈希表ht[1]
        zfree(d); // 释放字典
    }
```

其中，释放哈希表的操作由_dictClear底层函数实现。 

```c
    int _dictClear(dict *d, dictht *ht, void(callback)(void *)) {
        unsigned long i;
    
        // 清除和释放所有元素
        for (i = 0; i < ht->size && ht->used > 0; i++) {
            dictEntry *he, *nextHe;
    
            if (callback && (i & 65535) == 0) callback(d->privdata);
    
            if ((he = ht->table[i]) == NULL) continue;
            while(he) {
                nextHe = he->next;
                dictFreeKey(d, he); // 释放键
                dictFreeVal(d, he); // 释放值
                zfree(he); // 释放键值对结构
                ht->used--;
                he = nextHe;
            }
        }
        // 释放哈希表
        zfree(ht->table);
        // 重置哈希表
        _dictReset(ht);
        return DICT_OK; /* never fails */
    }
```

## dict小结 

Redis字典结构采用哈希表作为底层实现，每个字典包括两个哈希表，一个用来平常使用，另一个在rehash的时候使用。Redis提供了三种哈希算法，对整数，字符串等类型的键都能较好的处理。Redis的哈希表采用了链地址法来解决哈希冲突。最有特点的是，Redis在对字典进行扩容和收缩时，需要对哈希表中的所有键值对rehash到新哈希表里面，这个rehash操作不是一次性完成的，而是采用渐进式完成，这一措施使得rehash过程不会影响Redis对字典进行增删查改操作的效率。


[2]: http://zcheng.ren/2016/12/04/TheAnnotatedRedisSourceDict/
[5]: ../img/FJVbaiE.png