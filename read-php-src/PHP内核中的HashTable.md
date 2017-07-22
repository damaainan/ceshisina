# PHP内核中的HashTable 

[2016-11-01][0]# PHP内核中的HashTable原理

## 简易的结构

```c
typedef struct _Bucket
{
    char *key;
    void *value;
    struct _Bucket *next;
} Bucket;
 
typedef struct _HashTable
{
    int size;
    int elem_num;
    Bucket** buckets;
} HashTable;
```

这个是一个简化过的哈希表结构  
Bucket是一个链表，而_HashTable用于存储hash值并指向真正的数据储存单位

## 解决冲突算法DJBX33A

而链表是为了解决冲突用的，冲突解决采用DJBX33A算法，算法的内容如下

```c
inline unsigned time33(char const*str, int len) 
{ 
     unsigned long hash = 5381; 
     /* variant with the hash unrolled eight times */ 
     for (; len >= 8; len -= 8) { 
         hash = ((hash << 5) + hash) + *str++; 
         hash = ((hash << 5) + hash) + *str++; 
         hash = ((hash << 5) + hash) + *str++; 
         hash = ((hash << 5) + hash) + *str++; 
        hash = ((hash << 5) + hash) + *str++; 
        hash = ((hash << 5) + hash) + *str++; 
        hash = ((hash << 5) + hash) + *str++; 
        hash = ((hash << 5) + hash) + *str++; 
    } 
    switch (len) { 
        case 7: hash = ((hash << 5) + hash) + *str++; /* fallthrough... */ 
        case 6: hash = ((hash << 5) + hash) + *str++; /* fallthrough... */ 
        case 5: hash = ((hash << 5) + hash) + *str++; /* fallthrough... */ 
        case 4: hash = ((hash << 5) + hash) + *str++; /* fallthrough... */ 
        case 3: hash = ((hash << 5) + hash) + *str++; /* fallthrough... */ 
        case 2: hash = ((hash << 5) + hash) + *str++; /* fallthrough... */ 
        case 1: hash = ((hash << 5) + hash) + *str++; break; 
        case 0: break; 
    } 
    return hash; 
}
```

![PHP的HashTalble][1]

## HashTable的初始化

初始化，申请空间并且设置初始化值
```c
int hash_init(HashTable *ht)
{
    ht->size        = HASH_TABLE_INIT_SIZE;
    ht->elem_num    = 0;
    ht->buckets     = (Bucket **)calloc(ht->size, sizeof(Bucket *));
 
    if(ht->buckets == NULL) return FAILED;
 
    LOG_MSG("[init]\tsize: %i\n", ht->size);
 
    return SUCCESS;
}
```
## HashTable的插入

插入函数，插入时验证key是否存在，存在更新value值，不存在并取发生冲突则创建新节点并插入到原有链表的头部

    
```c
int hash_insert(HashTable *ht, char *key, void *value)
{
    // check if we need to resize the hashtable
    resize_hash_table_if_needed(ht);
 
    int index = HASH_INDEX(ht, key);
 
    Bucket *org_bucket = ht->buckets[index];
    Bucket *tmp_bucket = org_bucket;
 
    // check if the key exits already
    while(tmp_bucket)
    {
        if(strcmp(key, tmp_bucket->key) == 0)
        {
            LOG_MSG("[update]\tkey: %s\n", key);
            tmp_bucket->value = value;
 
            return SUCCESS;
        }
 
        tmp_bucket = tmp_bucket->next;
    }
 
    Bucket *bucket = (Bucket *)malloc(sizeof(Bucket));
 
    bucket->key   = key;
    bucket->value = value;
    bucket->next  = NULL;
 
    ht->elem_num += 1;
 
    if(org_bucket != NULL)
    {
        LOG_MSG("[collision]\tindex:%d key:%s\n", index, key);
        bucket->next = org_bucket;
    }
 
    ht->buckets[index]= bucket;
 
    LOG_MSG("[insert]\tindex:%d key:%s\tht(num:%d)\n",
        index, key, ht->elem_num);
 
    return SUCCESS;
}
```

## HashTable的扩容

当Hash表容量满了的时候，Hash表的性能会下降，这时候需要对Hash表进行扩容  
先把原来Hash表容量变成两倍，然后对其进行重新插入操作，时间复杂度为O(n)

```c
static void resize_hash_table_if_needed(HashTable *ht)
{
    if(ht->size - ht->elem_num < 1)
    {
        hash_resize(ht);
    }
}
 
static int hash_resize(HashTable *ht)
{
    // double the size
    int org_size = ht->size;
    ht->size = ht->size * 2;
    ht->elem_num = 0;
 
    LOG_MSG("[resize]\torg size: %i\tnew size: %i\n", org_size, ht->size);
 
    Bucket **buckets = (Bucket **)calloc(ht->size, sizeof(Bucket *));
 
    Bucket **org_buckets = ht->buckets;
    ht->buckets = buckets;
 
    int i = 0;
    for(i=0; i < org_size; ++i)
    {
        Bucket *cur = org_buckets[i];
        Bucket *tmp;
        while(cur)
        {
            // rehash: insert again
            hash_insert(ht, cur->key, cur->value);
 
            // free the org bucket, but not the element
            tmp = cur;
            cur = cur->next;
            free(tmp);
        }
    }
    free(org_buckets);
 
    LOG_MSG("[resize] done\n");
 
    return SUCCESS;
}
```

## HashTable的查找

元素的查找和插入采取相同的策略，都是先获得hash值，然后取得bucket链表，随后比较键名进行查找

```c
int hash_lookup(HashTable *ht, char *key, void **result)
{
    int index = HASH_INDEX(ht, key);
    Bucket *bucket = ht->buckets[index];
 
    if(bucket == NULL) goto failed;
 
    while(bucket)
    {
        if(strcmp(bucket->key, key) == 0)
        { 
            LOG_MSG("[lookup]\t found %s\tindex:%i value: %p\n",
                key, index, bucket->value);
            *result = bucket->value;
 
            return SUCCESS;
        } 
 
        bucket = bucket->next;
    }
 
failed:
    LOG_MSG("[lookup]\t key:%s\tfailed\t\n", key);
    return FAILED;
}
```

# 参考

[哈希表(HashTable)][2]

[0]: https://www.jwlchina.cn/2016/11/01/PHP内核中的HashTable/
[1]: ./img/PHP的HashTable.png
[2]: http://www.kancloud.cn/kancloud/php-internals/42760