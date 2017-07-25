# [php底层变量的实现][0]

* [php][1]

[**阿木**][2] 2014年09月16日发布 

* 推荐 **1** 推荐
* 收藏 **6** 收藏，**1.8k** 浏览

大家都知道php是一个弱类型的语言，变量的类型是随着赋值的类型变化的，php的底层是用C写的，C本身是一个强类型的语言，那php在底层是怎么实现类型的呢？

其实在底层，php是通过一个结构体来存储所有的变量的。结构体如下:

```c
    typedef struct _zval_struct zval
    
    typedef struct _zval_struct {
        /* Variable information */
        zvalue_value value;
        zend_uint refcount_gc;
        zend_uchar type;
        zend_uint is_ref_gc;
    }
```

解释一下几个变量的意义:  
zend_value value 储存的值，此处是一个指针，指到一个union的指针。php本身的值就是存储在这个联合体中。  
zend_uint is_refcount 存储的是引用计数  
zend_uchar type 存储变量的类型。  
zend_uint is_ref_gc 是否是引用传值。

php中所有的结构都是从用这个结构实现的。其中最关键的字段就是里面的type字段了。  
type字段总共有7个值，分别是IS_NULL,IS_BOOL,IS_LONG,IS_DOUBLE,IS_STRING,ISARRAY,IS_OBJECT,IS_RESOURCE。  
这个里面包含了所有的php基本类型：

        标量类型:IS_BOOL,IS_lONG,IS_DOUBLE,IS_STRING
        复合类型:IS_ARRAY,IS_OBJECT
        特殊类型:IS_RESOURCE,IS_NULL

zval结构根据不同的类型，其zval结构中的zval字段指向的联合体中存储不同的值.这个联合体就是php中同一个变量可以存储不同的值的关键.结构如下:

```c
    typedef union _zval_value{
        long *lval;
        double *dval;
        struct {
            char *val;
            int len;
        }str;
        HashTable *ht;
        zend_object_value obj;
    }
```

从这个结构里可以看出php中所有变量的痕迹：  
IS_BOOL(boolen),是存储在lval里面，和整数存储师一样的。这里大家应该想到==和===对于false和0处理的不同之处了。  
IS_LONG(整型)，存储在lval  
IS_DOUBLE(浮点型),存储在dval  
IS_STRING(字符串),存储在str  
IS_ARRAY(数组)，存储在*ht哈希table中  
IS_OBJECT(对象)，存储在zend_object_value  
IS_NULL,NULL值在这个结构中不用存储，直接在zval结构中的type字段进行判断。

简单的介绍一下字符串的存储:  
字符串的在联合体中使用结构体的形式出现，代码如下：

```c
    struct {
        char *val;
        int len;
    }str;
```

可以看到，php在存储字符串时，将字符串的内容和长度都存了起来，这是为了避免重复计算字符串的长度。php中的函数strlen,就是直接返回了这个长度。

[0]: /a/1190000000671650
[1]: /t/php/blogs
[2]: /u/forse