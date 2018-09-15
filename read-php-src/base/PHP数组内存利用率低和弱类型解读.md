# PHP数组内存利用率低和弱类型解读

[Chuck_Hu][0] 关注 2017.08.08 23:41  字数 1021 

这两天任务提前完成，可以喘口气沉淀一下，深入学习学习PHP。其实本来是想了解一下PHP性能优化相关的东西，但被网上的一句“PHP数组内存利用率低，C语言100MB的内存数组，PHP里需要1G”惊到了。PHP真的这么耗内存么？于是借此机会了解了PHP的数据类型实现方式。  
先来做个测试：

```php
<?php  
echo memory_get_usage() , '<br>';  
$start = memory_get_usage();  
$a = Array();  
for ($i=0; $i<1000; $i++) {  
  $a[$i] = $i + $i;  
}  
$end =  memory_get_usage();  
echo memory_get_usage() , '<br>';  
echo 'argv:', ($end - $start)/1000 ,'bytes' , '<br>';  
```

所得结果：

    353352
    437848
    argv:84.416bytes

1000个元素的整数数组耗费内存(437848 - 353352)字节，约合82KB，也就是说每个元素所占内存84字节。在C语言中，一个int占位是4字节，整体相差了20倍。  
但是网上又说`memery_get_usage()`返回的结果不全是数组占用，还包括PHP本身的一些结构，因此，换种方式，采用PHP内置函数生成数组试试：

```php
<?php  
$start = memory_get_usage();  
$a = array_fill(0, 10000, 1);  
$end = memory_get_usage(); //10k elements array;  
echo 'argv:', ($end - $start )/10000,'byte' , '<br>';  
```

输出为：

      argv:54.5792byte
    

比刚才略好，但也54字节，确实差了10倍左右。  
究其原因，还得从PHP的底层实现说起。PHP是一种弱类型的语言，不分int，double，string之类的，统一一个'$'就能解决所有问题。PHP底层由C语言实现，`每个变量`都对应一个`zval结构`，其详细定义为：

```c
typedef struct _zval_struct zval;  
struct _zval_struct {  
    /* Variable information */  
    zvalue_value value;     /* The value 1 12字节(32位机是12，64位机需要8+4+4=16) */  
    zend_uint refcount__gc; /* The number of references to this value (for GC) 4字节 */  
    zend_uchar type;        /* The active type 1字节*/  
    zend_uchar is_ref__gc;  /* Whether this value is a reference (&) 1字节*/  
}; 
```


PHP使用`union结构`来存储`变量的值`，`zval`中`zvalue_value`类型的value变量即为一个union，定义如下：

```c
typedef union _zvalue_value {  
    long lval;                  /* long value */  
    double dval;                /* double value */  
    struct {                    /* string value */  
        char *val;  
        int len;  
    } str;   
    HashTable *ht;              /* hash table value */  
    zend_object_value obj;      /*object value */  
} zvalue_value;  
```


union类型占用内存的大小有其最大的成员所占的数据空间决定。在zvalue_value中，`str结构体`的int占4字节，char指针占4字节，故整个`zvalue_value`所占内存为**`8字节`**。  
zval的大小即为8 + 4 + 1 + 1 = 14字节。  
注意到zvalue_value中还有一个HashTable是做什么的？zval中，数组、字符串和对象还需要另外的存储结构，数组的存储结构即为HashTable。  
HashTable定义给出：

```c
typedef struct _hashtable {  
     uint nTableSize; //表长度，并非元素个数  
     uint nTableMask;//表的掩码，始终等于nTableSize-1  
     uint nNumOfElements;//存储的元素个数  
     ulong nNextFreeElement;//指向下一个空的元素位置  
     Bucket *pInternalPointer;//foreach循环时，用来记录当前遍历到的元素位置  
     Bucket *pListHead;  
     Bucket *pListTail;  
     Bucket **arBuckets;//存储的元素数组  
     dtor_func_t pDestructor;//析构函数  
     zend_bool persistent;//是否持久保存。从这可以发现，PHP数组是可以实现持久保存在内存中的，而无需每次请求都重新加载。  
     unsigned char nApplyCount;  
     zend_bool bApplyProtection;  
} HashTable; 
```


除了几个记录table大小，所含元素数量的属性变量外，Bucket被多次使用到，Bucket是如何定义的：

```c
typedef struct bucket {  
     ulong h; //数组索引  
     uint nKeyLength; //字符串索引的长度  
     void *pData; //实际数据的存储地址  
     void *pDataPtr; //引入的数据存储地址  
     struct bucket *pListNext;  
     struct bucket *pListLast;  
     struct bucket *pNext; //双向链表的下一个元素的地址  
     struct bucket *pLast;//双向链表的下一个元素地址  
     char arKey[1]; /* Must be last element */  
} Bucket; 
```


有点像一个链表，`Bucket`就像是一个链表节点，有具体的数据和指针，而`HashTable`就是一个array，保存着一串`Bucket`元素。PHP中多维数组的实现，不过就是`Bucket`里面存着另一个`HashTable`罢了。  

算一算`HashTable`需要占用39个字节，`Bucket`需要33个字节。一个空的数组就需要占用14 + 39 + 33 = 86个字节。`Bucket` 结构需要 33 个字节，键长超过四个字节的部分附加在 `Bucket` 后面，而元素值很可能是一个 `zval` 结构，另外每个数组会分配一个由 `arBuckets` 指向的 `Bucket` 指针数组， 虽然不能说每增加一个元素就需要一个指针，但是实际情况可能更糟。这么算来一个数组元素就会占用 54 个字节，与上面的估算几乎一样。  

从空间的角度来看，小型数组平均代价较大，当然一个脚本中不会充斥数量很大的小型数组，可以以较小的空间代价来获取编程上的快捷。但如果将数组当作容器来使用就是另一番景象了，实际应用经常会遇到多维数组，而且元素居多。比如10k个元素的一维数组大概消耗540k内存，而10k x 10 的二维数组理论上只需要 6M 左右的空间，但是按照 `memory_get_usage` 的结果则两倍于此，[10k,5,2]的三维数组居然消耗了23M，小型数组确实是划不来的。  

PHP数组内存利用率低的原因，讲到这里，接下来的文章将解读PHP数组操作的具体实现。

[0]: /u/9c5bba0cd82d