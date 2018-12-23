## PHP7源码中的优雅设计

来源：[http://fivezh.github.io/2018/11/02/graceful-design-in-php7-src/](http://fivezh.github.io/2018/11/02/graceful-design-in-php7-src/)

时间 2018-12-03 02:32:35

 
团队内分享PHP7源码，重读代码过程中发现其中不少优秀设计之处，整理一篇其源码中的优雅设计。
 
  
阅读要求：对PHP7源码实现有一定了解，具备一定的源码分析能力
 
推荐几篇优秀的文章，建议先行阅读：

 
* Array/HashTable实现，推荐阅读[Julien Pauli-PHP 7 Arrays : HashTables][3]  
* 鸟哥Laruence的slide：[The secret of PHP7’s Performance][4]  
  

 
## Array如何保证有序 
 
问题：在PHP中Array数组是通过HashTable哈希来实现，但由于Hash的特性是高效访问、但数据无序，因此面临数组遍历时顺序的问题？
 
### 先来看看数组Array的实现 
 
数组的两个重要结构体：

 
* `Bucket`：单个元素的存储单元  
* `_zend_array`别名`HashTable`：数组的上层封装 
    
     
```c
typedef struct _Bucket {
	zval              val;
	zend_ulong        h; /* hash value (or numeric index)   */
	zend_string      *key; /* string key or NULL for numerics */
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
	uint32_t          nTableMask;
	Bucket           *arData;
	uint32_t          nNumUsed;
	uint32_t          nNumOfElements;
	uint32_t          nTableSize;
	uint32_t          nInternalPointer;
	zend_long         nNextFreeElement;
	dtor_func_t       pDestructor;
};


```

 
老生常谈`_zend_array`：

 
* `gc`：引用计数  
* `u`：联合体`flags`或`v`标志位  
* `nTableMask`：掩码, = -nTableSize  
* `*arData`：指向数据元素存储Bucket地址  
* `nNumUsed`：数组内已使用空间数量（unset元素后nNumUsed不变，nNumOfElements减少）  
* `nNumOfElements`：数组内有效元素个数  
* `nTableSize`：数组空间开辟大小  
* `nInternalPointer`：待补充  
* `nNextFreeElement`：下一个可用元素位置  
* `pDestructor`：析构时处理  
 
 
### HashTable巧妙之处：`nTableMask`
 
* `nTableMask = -nTableSize`：为什么同样一个`nTableSize`数值，额外用`nTableMask`冗余一份呢？ 
 
 
* 通过位运算计算nIndex`nIndex = p->h | ht->nTableMask` 
* `h`是`key`进行hash计算后的哈希值，与`nTableMask`(补码表示，`nTableSize`反码+1)或运算，取值范围`[0, nTableSize-1]` 
* 实现效果与`nIndex = p->h % ht->nTableSize`相同，但 **`位或运算效率比模运算高`**  很多  
* 空间 VS 时间 效率的博弈，这里冗余一个字段，打打提升频繁`nIndex`计算的效率  
   

 
### HashTable巧妙之处：`nNumUsed`和`nNumOfElemets`
 
* `nNumUsed`和`nNumOfElemets`为何区分开？ 
 
 
* 释放中间元素时不做内存处理，保证高效，仅标记元素`p->val->u1.v.type=IS_UNDEF` 
* 在`resize()`或`rehash()`时将已删除的`IS_UNDEF`元素进行内存重整  
   

 
### Array巧妙之处：`arData`、`nIndex`、`idx`
 
* Array底层使用HashTable存储，如何保证插入数组元素的有序性？ 
 
 
* 先重点看下arData指向的Bucket内部结构如下： 

![][0]
  
* 上图例子数据写入过程： 
   
 
* `nTableSize=8`，`nTableMask = -nTableSize = -8` 
* 数组首次写入元素`$array['bar] = 'bar-val'`时，`h`为`bar`经过`Time33`算法计算后的数值，`nIndex = h | nTableMask = -3` 
* `idx=nNumUsed++`、`arData[nIdex] = idx`，从而写入映射表`arData[-3] = 0`，数据写入`arData[idx]=Bucket{key,h,val}`也就是`arData[0]={'bar',hash(bar),'bar-var'}` 
* 相同的，插入`$array['foo'] = 42`时，写入映射表`arData[-5]=1`，数据写入`arData[idx]=Bucket{key,h,val}`也就是`arData[1]={'foo',hash(foo),42}` 
* ![][1]

  
* `arData`指向区域包含两部分：`hash映射表`和`数据存储Buckets`，后者Buckets为数据存储区。如直接hash取模的方式存储(散列值跳跃且分散)，则遍历时无法保证顺序，因此衍生出通过`hash映射表`来实现的方式  
* `arData`指向Buckets存储区的起始位置，而`hash映射表`在其负值索引位置上，nIndex为负值，通过数组的负值索引快速访问`arData[nIndex]`值  
* 具体来说，根据`nNumUsed`确定首个可用Buckets索引地址idx，继而计算nIndex(`nIndex = h | nTableMask`)，将数据在Buckets区的存储索引idx保存到映射表：`arData[nIndex] = idx` 
* 索引查找时，按照`h->nIndex->idx`的顺序查找数据，几乎是O(1)复杂度的  
* 顺序遍历时，按照Buckets区逐一遍历即使插入时顺序 
* **`巧妙的`**  ，这里将映射表和数据区连续内存空间存储，且nIndex通过`h|nTableMask`的方式快速计算获得，极大保证计算效率；连续分配，释放、扩容时都是简单高效的处理方式  
   

 
  
最终数组的存储结构：(图片来源鸟哥分享slide)

![][2]

 
## zend_string中变长数组 
 `zend_string`结构体定义：

```c
struct _zend_string {
	zend_refcounted_h gc;
	zend_ulong        h; /* hash value */
	size_t            len;
	char              val[1];
};


```

```
gc
h
len
val[1]


```
 
###`zend_string`巧妙之处：`val[1]`
 
* 变长数组([Variable-length array][5] )是在[ISO C99][6] 之后才支持的特性，使用此特性需要编译器支持C99标准。  
* [零长数组][7] 是GNU C版本编译器支持，并引导C99最终支持变长数组的经典案例，但不同版本实现的编译器可能[不支持零长数组][8] 。  
* 在PHP7源码中为了兼容不同版本编译器、利用变长数组特性，使用`val[1]`来实现`固定头部的可变对象`的存储形式。  
 
 
  
后续补充：
 巧妙之处： `IS_UNDEF`
 
TODO：删除时设置为`IS_UNDEF`，在需要时统一进行内存整理提高单次操作性能。


[3]: http://blog.jpauli.tech/2016/04/08/hashtables.html
[4]: https://www.slideshare.net/laruence/the-secret-of-php7s-performance
[5]: https://en.wikipedia.org/wiki/Variable-length_array
[6]: https://en.wikipedia.org/wiki/C99
[7]: https://gcc.gnu.org/onlinedocs/gcc-4.1.1/gcc/Zero-Length.html#Zero-Length
[8]: https://coolshell.cn/articles/11377.html
[0]: ./img/VV3EVr3.png 
[1]: ./img/BNfyeiU.png 
[2]: ./img/iMZVFrN.png 