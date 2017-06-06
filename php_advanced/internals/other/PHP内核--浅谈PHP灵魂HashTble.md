# [PHP内核--浅谈PHP灵魂HashTble][0]

 2016-10-24 00:42  1369人阅读  [评论][1](0)  [收藏][2]  [举报][3]

 本文章已收录于：

![][4]

 分类：

版权声明：本文为博主原创文章，转载请说明出处。

 **一。前言**

HashTable是PHP的灵魂，因为在Zend引擎中 大量的使用了HashTable，如变量表，常量表，函数表等，这些都是 适应HashTable保存的，另外，PHP的数组也是通过使用HashTble实现的，所以， 了解PHP的HashTable才能真正了解PHP 。

![][5]

为了方便阅读，这里列举一下HashTable实现中出现的基本概念。 哈希表是一种通过哈希函数，将特定的键映射到特定值的一种数据结构，它维护键和值之间一一对应关系。

* 键(key)：用于操作数据的标示，例如PHP数组中的索引，或者字符串键等等。
* 槽(slot/bucket)：哈希表中用于保存数据的一个单元，也就是数据真正存放的容器。
* 哈希函数(hash function)：将key映射(map)到数据应该存放的slot所在位置的函数。
* 哈希冲突(hash collision)：哈希函数将两个不同的key映射到同一个索引的情况。
PHP中的哈希表实现在Zend/zend_hash.h中，先看看PHP实现中的数据结构， PHP使用如下两个数据结构来实现哈希表， HashTable结构体用于保存整个哈希表需要的基本信息， 而Bucket结构体用于保存具体的数据内容 ，(具体源码见最后)

**二。举例**

那么，以创建一个变量为例，在底层到底发生了什么呢？

创建变量的步骤: $str = "hello";

1:创建zval结构,并设置其类型 IS_STRING

2:设置其值为 hello

3:将其加入符号表 



    {    
    zval *fooval;     
    MAKE_STD_ZVAL(fooval);    
    ZVAL_STRING(fooval, "hello", 1);    
    ZEND_SET_SYMBOL( EG(active_symbol_table) ,  "foo" , fooval);
    }

前两步在上一篇的变量结构中有提到过， 详见 [PHP内核的存储机制（分离/改变）][7]

符号表是什么?

答:符号表是一张哈希表,里面存储了变量名->变量的zval结构体的地址

// zend/zend_globals.h 161行 符号表


    struct _zend_executor_globals { 
          ...   
          ...   
    HashTable *active_symbol_table; /*活动符号表*/   
    HashTable symbol_table;     /* 全局符号表 */ 
    HashTable included_files;   /* files already included */
         ...
    }

> 当执行到函数时,会生成函数的"执行环境结构体",包含函数名,参数,执行步骤,所在的类(如果是方法),以及为这个函数生成一个符号表.符号表统一放在栈上.并把active_symbol_table指向刚产生的符号表

Zend/zend_compiles.h 384行，执行环境结构体：



    struct _zend_execute_data {
        struct _zend_op *opline;
        zend_function_state function_state;
        zend_op_array *op_array;//函数编译后的执行逻辑，编译后的opcode二进制代码，称为op_array
        zval *object;
        HashTable *symbol_table;//此函数的符号表地址
        struct _zend_execute_data *prev_execute_data;
        zval *old_error_reporting;
        zend_bool nested;
        zval **original_return_value;
        zend_class_entry *current_scope;
        zend_class_entry *current_called_scope;
        zval *current_this;  
        struct _zend_op *fast_ret; /* used by FAST_CALL/FAST_RET (finally keyword) */
        call_slot *call_slots;
        call_slot *call;
    };

上面这个,是当前函数执行时的符号表。

**通过下边例子，来描述下函数在执行中，PHP对各个存储空间的分配，以及解释了为什么PHP的静态变量可以共享。**

> 当执行到函数时,会生成函数的"执行环境结构体",包含函数名,参数,执行步骤,所在的类(如果是方法),以及为这个函数生成一个符号表.符号表统一放在栈上.并把active_symbol_table指向刚产生的符号表

![][8]

解释：

1.执行t1时，形成t1的环境结构体，t1调入到执行栈，t1也有自己的符号表，符号表里边存储的变量对应这个t1环境(局部变量嘛)

2.执行t1到第三行，执行了t2，形成t2的环境结构体，t2入栈，t2也有自己的变量自己的符号表，与t1互不影响。

3.假使t1函数内部出现了递归调用t1，此时会生成第二个t1环境结构体，和【1】中是两个结构体，互不影响

函数执行时的栈变化

当函数调用时,为此函数生成了一个”执行环境变量”的结构体,里面存储了当前函数的名称,参数,对应的类....等等信息.称为_zend_execute_data {}结构体



    struct _zend_execute_data {
        struct _zend_op *opline;
        zend_function_state function_state;
        zend_op_array *op_array;//函数编译后的执行逻辑，编译后的opcode二进制代码，称为op_array
        zval *object;
        HashTable *symbol_table;//此函数的符号表地址
        struct _zend_execute_data *prev_execute_data;
        zval *old_error_reporting;
        zend_bool nested;
        zval **original_return_value;
        zend_class_entry *current_scope;
        zend_class_entry *current_called_scope;
        zval *current_this;  
        struct _zend_op *fast_ret; /* used by FAST_CALL/FAST_RET (finally keyword) */
        call_slot *call_slots;
        call_slot *call;
    };

这个结构体中,有2个重要的信息需要注意！:

{

*op_array ------>是函数的执行步骤，公用（静态变量字段存储于此！所以改一次依赖于此逻辑的函数全修改！）

*hash_table---->symbol_table 这个函数对应的符号表

}

![][9]

思考一下: 1个函数,递归调用自己3次, 如t1

问：在栈上,肯定要有3个 execute_data生成.但是,这3个execute_data--->对应几个*op_array;

答:函数编译完了,生成一份*op_array,因为函数的执行逻辑是固定的.

问：生成了几个 symbol_table?

答：生成3个符号表.

**结论：**

1. 每一个函数调用是都会生成自己的环境栈和符号表栈，不同的环境栈对应了自己的符号表栈，所以每个函数中的变量常量等，他们是有对应函数内的作用域限制

2. 虽然每次会生成不同的环境栈与作用域，但是如果调用的是同一个函数，其 *op_array;是公用1份的，换句话说，t1递归调用自己，每次都会开辟一个环境栈区分独立，但是他们是同一个函数逻辑，所以op_array是一样的，而

**三。其他**

通过一个哈希算法，它总有碰撞的时候吧。PHP中的哈希表是使用拉链法来解决冲突 (具体点讲就是使用链表来存储哈希到同一个槽位的数据,Zend为了保存数据之间的关系使用了双向链表来链接元素)。

对于HashTable的初始化_zend_hash_init，

插入_zend_hash_add_or_update，

元素访问_zend_hash_add_or_find等操作，源码中有就不再这里叙述。

这样回头一想，变量表，常量表，函数表等，他们在PHP中都是靠HashTable来实现的，如[二]中叙述，hashtable是不是很强大呢？

Zend引擎哈希表结构和关系：

![][10]

Zend/zend_hash.h 55行



    typedef struct bucket {
        ulong h;                        /* Used for numeric indexing */
        uint nKeyLength;
        void *pData;
        void *pDataPtr;
        struct bucket *pListNext;
        struct bucket *pListLast;
        struct bucket *pNext;
        struct bucket *pLast;
        const char *arKey;
    } Bucket;
    
    typedef struct _hashtable { 
        uint nTableSize;        // hash Bucket的大小，最小为8，以2x增长。
        uint nTableMask;        // nTableSize-1 ， 索引取值的优化
        uint nNumOfElements;    // hash Bucket中当前存在的元素个数，count()函数会直接返回此值 
        ulong nNextFreeElement; // 下一个数字索引的位置
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
    

Zend/zend_compiles.h 261行,op_array结构代码

```c
struct _zend_op_array {  
    /* Common elements */  
    zend_uchar type;  
    const char *function_name;        
    zend_class_entry *scope;  
    zend_uint fn_flags;  
    union _zend_function *prototype;  
    zend_uint num_args;  
    zend_uint required_num_args;  
    zend_arg_info *arg_info;  
    /* END of common elements */  
  
    zend_uint *refcount;  
  
    zend_op *opcodes;  
    zend_uint last;  
  
    zend_compiled_variable *vars;  
    int last_var;  
  
    zend_uint T;  
  
    zend_uint nested_calls;  
    zend_uint used_stack;  
  
    zend_brk_cont_element *brk_cont_array;  
    int last_brk_cont;  
  
    zend_try_catch_element *try_catch_array;  
    int last_try_catch;  
    zend_bool has_finally_block;  
  
    /* static variables support */  
    HashTable *static_variables;  
  
    zend_uint this_var;  
  
    const char *filename;  
    zend_uint line_start;  
    zend_uint line_end;  
    const char *doc_comment;  
    zend_uint doc_comment_len;  
    zend_uint early_binding; /* the linked list of delayed declarations */  
  
    zend_literal *literals;  
    int last_literal;  
  
    void **run_time_cache;  
    int  last_cache_slot;  
  
    void *reserved[ZEND_MAX_RESERVED_RESOURCES];  
};  
```

[0]: http://blog.csdn.net/ty_hf/article/details/52906459
[5]: ../img/20161024003508247.png
[6]: #
[7]: http://blog.csdn.net/ty_hf/article/details/51057954
[8]: ../img/20161024002942389.png
[9]: ../img/20161024004225006.png
[10]: ../img/20161024003220587.png