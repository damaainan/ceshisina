# 详解 PHP 数组的底层实现：HashTable

## 前言

PHP 中的数组是一种强大且灵活的数据类型。在讲解它的底层实现之前，让我们先来看看它在实际使用中都有哪些重要的特性：
```php
    // 可以使用数字下标的形式定义数组
    $arr= ['Mike', 2 => 'JoJo'];
    echo $arr[0], $arr[2];
    
    // 也可以使用字符串下标定义数组
    $arr = ['name' => 'Mike', 'age' => 22];
    
    // 可以顺序读取数组中的数据
    foreach ($arr as $key => $value) {
        // Do Something
    }
    echo current($arr);
    echo next($arr);
    
    // 也可以随机读取数组中的数据
    $arr = ['name' => 'Mike', 'age' => 22];
    echo $arr['name'];
    
    // 数组的长度是可变的
    $arr = [1, 2, 3];
    $arr[] = 4;
    array_push($arr, 5);
```
基于这些特性，我们可以很轻易的使用 PHP 中的数组实现集合、栈、列表、字典等多种数据结构。那么这些特性在底层是如何实现的呢？且听我细细道来。

## 数据结构

> PHP 中的数组实际上是一个有序映射。映射是一种把 values 关联到 keys 的类型。—— [> PHP手册][0]

在 PHP 中，这种映射关系是使用散列表（HashTable）实现的，在 C 语言中，只能通过数字下标访问数组元素，而通过 HashTable，我们可以使用 **String Key** 作为下标来访问数组元素。简单地说，HashTable 通过映射函数将一个 Strring Key 转化为一个普通的数字下标，并将对应的 Value 值储存到下标对应的数组元素中。

PHP 中的 HashTable 由 zend_array 定义，它的数据结构如下：
```c
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
            uint32_t flags;           /* 通过 32 个可用标识，设置散列表的属性 */
        } u;
        uint32_t     nTableMask;       /* 值为 nTableSize 的负数 */
        Bucket      *arData;           /* 用来储存数据 */
        uint32_t     nNumUsed;         /* arData 中的已用空间大小 */
        uint32_t     nNumOfElements;   /* 数组中的元素个数 */
        uint32_t     nTableSize;       /* 数组大小，总是 2 幂次方 */
        uint32_t     nInternalPointer; /* 下一个数据元素的指针，用于迭代（foreach） */
        zend_long    nNextFreeElement; /* 下一个可用的数值索引 */
        dtor_func_t  pDestructor;      /* 数据析构函数（句柄） */
    };
```
该结构中的 Bucket 即储存元素的数组，arData 指向数组的起始位置，使用映射函数对 key 值进行映射后可以得到**偏移值**，通过**内存起始位置 + 偏移值**即可在散列表中进行寻址操作。Bucket 的数据结构如下：
```c
    typedef struct _Bucket {
        zval              val; /* 值 */
        zend_ulong        h;   /* 使用 time 33 算法对 key 进行计算后得到的哈希值（或为数字索引）   */
        zend_string      *key; /* 当 key 值为字符串时，指向该字符串对应的 zend_string（使用数字索引时该值为 NULL） */
    } Bucket;
```
## 基本实现

散列表主要由**储存元素的数组**（Bucket）和**散列函数**两部分构成。

### 随机读

当指定一个 Key-Value 映射关系时，如果 Key 为 String 类型，则先通过 Time 33 算法将其转换为一个 Int 类型的整数，然后再先通过 PHP 中某种特定的散列算法将该 Int 映射为 Bucket 数组中的一个下标，最终将 Value 储存到该下标对应的元素中。 通过 Key 访问数组时，只需要使用相同的算法计算出对应下标，然后取出对应元素中的 Value 值，即可实现**随机读取**。

![散列函数随机读的基本实现][1]

### 顺序读

由上面所讲可知，储存在 HashTable 中的元素是无序的，而 PHP 中的数组是有序的，PHP 是如何解决这个问题的呢？

为了实现 HashTable 的有序性，PHP 为其增加了一张**中间映射表**，该表是一个大小与 Bucket 相同的数组，数组中储存整形数据，用于保存元素实际储存的 Value 在 Bucekt 中的下标。注意，加入了中间映射表后，**Bucekt 中的数据是有序的，而中间映射表中的数据是无序的**。这样顺序读取时只需要访问 Bucket 中的数据即可。

![散列函数顺序读的基本实现][2]

zend_array 中并没有单独定义中间映射表，而是将其与 arData 放在一起，数组初始化时并不只分配 Bucket 大小的内存，同时还会分配相同大小空间的数据来作为中间映射表，其实现方式如图：

![中间映射表在 PHP 中的实现][3]

## 散列函数

由上一节可知，散列函数实际上是先将 hash code 映射到中间映射表中，再由中间映射表指向实际存储 Value 的元素。

PHP 中采用如下方式对 hash code 进行散列：
```
    nIndex = key->h | nTableMask;
```
因为散列表的大小恒为 2 的幂次方，所以散列后的值会位于 [nTableMask, -1] 之间，即中间映射表之中。

## Hash 冲突

任何散列函数都会出现哈希冲突的问题，常见的解决哈希冲突的方法有以下几种：

* 开放定址法
* 链地址法
* 重哈希法

PHP 采用的是其中的链地址法，将冲突的 Bucket 串成链表，这样中间映射表映射出的就不是某一个元素，而是一个 Bucket 链表，通过散列函数定位到对应的 Bucket 链表时，需要遍历链表，逐个对比 Key 值，继而找到目标元素。

新元素 Hash 冲突时的插入分为以下两步：

* 将旧元素的下标储存到新元素的 next 中
* 将新元素的下标储存到中间映射表中

可以看出，PHP 在 Bucket 原有的数组结构上，实现了静态链表，从而解决了哈希冲突的问题。

## 查找

HashTable 中的查找过程其实已经在上面说完了：

1. 使用 time 33 算法对 key 值计算得到 hash code
1. 使用散列函数计算 hash code 得到散列值 nIndex，即元素在中间映射表的下标
1. 通过 nIndex 从中间映射表中取出元素在 Bucket 中的下标 idx
1. 通过 idx 访问 Bucket 中对应的数组元素，该元素同时也是一个静态链表的头结点
1. 遍历链表，分别判断每个元素中的 key 值是否与我们要查找的 key 值相同
1. 如果相同，终止遍历

## 扩容

在 C 语言中，数组的长度是定长的，那么如果空间已满还需继续插入的时候怎么办呢？PHP 的数组在底层实现了自动扩容机制，当插入一个元素且没有空闲空间时，就会触发**自动扩容**机制，扩容后再执行插入。

需要提出的一点是，当删除某一个数组元素时，会先使用标志位对该元素进行**逻辑删除**，而不会立即删除该元素所在的 Bucket，因为后者在每次删除时进行一次排列操作，从而造成不必要的性能开销。

扩容的过程为：

1. 如果已删除元素所占比例达到阈值，则会移除已被**逻辑删除**的 Bucket，然后将后面的 Bucket 向前补上空缺的 Bucket，因为 Bucket 的下标发生了变动，所以还需要更改每个元素在中间映射表中储存的实际下标值。
1. 如果未达到阈值，PHP 则会申请一个大小是原数组两倍的新数组，并将旧数组中的数据复制到新数组中，因为数组长度发生了改变，所以 key-value 的映射关系需要重新计算，这个步骤为**重建索引**。

_注：因为在重建索引时需要重新计算映射关系，所以将旧数组复制到新数组中时，中间映射表的数据是无需复制的。_

## 总结

* PHP 中的数组是使用 **HashTable** 实现的
* HashTable 的占用空间是 **2 的幂次方**
* HashTable 通过 Key-Value 映射关系实现随机读取
* HashTable 通过**中间映射表**实现顺序读取，中间映射表和元素数组（Bucket）使用连续的内存空间
* PHP 通过链地址法解决 HashTable 中的哈希冲突
* 在空间已满时，会触发自动扩容机制，导致**重建索引**

## 参考资料

* 《PHP7 内核剖析》
* [PHP 7 Arrays : HashTables][4]

[0]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Flanguage.types.array.php
[1]: ./img/1669d1e25d5da376.png
[2]: ./img/1669d1e262a8a716.png
[3]: ./img/1669d1e25c701709.png
[4]: https://link.juejin.im?target=http%3A%2F%2Fblog.jpauli.tech%2F2016%2F04%2F08%2Fhashtables.html