## PHP7变量的内部结构

来源：[https://fengyoulin.com/2018/03/06/php7_zval_structure/](https://fengyoulin.com/2018/03/06/php7_zval_structure/)

时间 2018-03-06 13:39:36

 
要了解PHP7变量的内部存储结构以及工作原理，就必须从源代码开始分析。这里使用7.2.2版源代码，zend_types.h中第84行对zval类型进行了如下定义：
 
```c
typedef struct _zval_struct     zval;
```
 
继续看一下zend_types.h第159行起相关的结构定义：
 
```c
typedef union _zend_value {
    zend_long         lval;                /* long value */
    double            dval;                /* double value */
    zend_refcounted  *counted;
    zend_string      *str;
    zend_array       *arr;
    zend_object      *obj;
    zend_resource    *res;
    zend_reference   *ref;
    zend_ast_ref     *ast;
    zval             *zv;
    void             *ptr;
    zend_class_entry *ce;
    zend_function    *func;
    struct {
        uint32_t w1;
        uint32_t w2;
    } ww;
} zend_value;

struct _zval_struct {
    zend_value        value;            /* value */
    union {
        struct {
            ZEND_ENDIAN_LOHI_4(
                zend_uchar    type,            /* active type */
                zend_uchar    type_flags,
                zend_uchar    const_flags,
                zend_uchar    reserved)        /* call info for EX(This) */
        } v;
        uint32_t type_info;
    } u1;
    union {
        uint32_t     next;                 /* hash collision chain */
        uint32_t     cache_slot;           /* literal cache slot */
        uint32_t     lineno;               /* line number (for ast nodes) */
        uint32_t     num_args;             /* arguments number for EX(This) */
        uint32_t     fe_pos;               /* foreach position */
        uint32_t     fe_iter_idx;          /* foreach iterator index */
        uint32_t     access_flags;         /* class constant access flags */
        uint32_t     property_guard;       /* single property guard */
        uint32_t     extra;                /* not further specified */
    } u2;
};
```
 
其中zend_value是一个C语言中的共用体或者叫联合体，用来存储变量的值，或者是指向复杂对象的指针。
 
### 一、变量的类型
 
比较基础的整型和浮点类型，直接存储在lval或者dval里，对于字符串、数组、对象、资源和引用类型，则是存储了指向真实数据结构的指针。PHP7里对布尔类型的存储方式做了一些修改，看一下zend_types.h第361行开始的这些宏定义：
 
```c
/* regular data types */
#define IS_UNDEF                    0
#define IS_NULL                     1
#define IS_FALSE                    2
#define IS_TRUE                     3
#define IS_LONG                     4
#define IS_DOUBLE                   5
#define IS_STRING                   6
#define IS_ARRAY                    7
#define IS_OBJECT                   8
#define IS_RESOURCE                 9
#define IS_REFERENCE                10
```
 
我们可以看到FALSE和TRUE分别用两种不同的类型来表示，而不像PHP5中使用IS_BOOL表示布尔型，还需要lval存储1或0来表示TRUE和FALSE，提高了运行时效率。
 
变量的类型存储在`zval.u1.v.type`中，为了代码书写方便，zend_types.h第399行定义了如下宏：
 
```c
#define Z_TYPE(zval)                zval_get_type(&(zval))
#define Z_TYPE_P(zval_p)            Z_TYPE(*(zval_p))
```
 
其中`zval_get_type`的定义在第389行：
 
```c
static zend_always_inline zend_uchar zval_get_type(const zval* pz) {
    return pz->u1.v.type;
}
```
 
其实就是直接返回type的值。
 
### 二、引用类型
 
PHP7的zval结构中移除了引用计数字段，对于复杂类型如字符串、数组、对象、资源和引用类型，zend_value中分别提供了相应类型的指针。而且在这几个结构定义的头部都包含了zend_refcounted_h结构，zend_types.h第204行：
 
```c
typedef struct _zend_refcounted_h {
    uint32_t         refcount;            /* reference counter 32-bit */
    union {
        struct {
            ZEND_ENDIAN_LOHI_3(
                zend_uchar    type,
                zend_uchar    flags,    /* used for strings & objects */
                uint16_t      gc_info)  /* keeps GC root number (or 0) and color */
        } v;
        uint32_t type_info;
    } u;
} zend_refcounted_h;
```
 
我们可以看到，该结构中定义了引用计数字段refcount。回过头看zend_value结构，其中还提供了一个zend_refcounted类型的指针，zend_refcounted结构的定义在第217行：
 
```c
struct _zend_refcounted {
    zend_refcounted_h gc;
};
```
 
其中只包含了一个zend_refcounted_h头。所以zend_value中的counted指针可以用来统一处理所有引用类型的头部，比如读取引用计数。
 
需要重点看一下PHP7中新添加的zend_reference结构，其定义在第351行：
 
```c
struct _zend_reference {
    zend_refcounted_h gc;
    zval              val;
};
```
 
因为PHP7中zval结构不再包含引用计数字段，所以使用该结构来处理PHP代码中的变量引用，例如：
 
```php
<?php
$a = 10;
```
 
以上代码中的变量a为整型，在内存中的存储结构如下图：
 
![][0]
 
当又有一个变量b引用变量a的时候，
 
```php
<?php
$a = 10;
$b = &$a;
```
 
内存中的存储结构如下图所示：
 
![][1]
 
当`$b = &$a;`这句代码执行的时候，PHP所做的事情：
 
 
* 分配一个zend_reference结构。 
* 将$a所对应的zval结构中的类型和值赋给新分配的zend_reference结构中的val。 
* 将新分配的zend_reference结构中的refcount赋值为1，并且将$a转换为IS_REFERENCE类型，并且将ref指针指向新分配的zend_reference结构。 
* 将$b所对应的zval结构的类型置为IS_REFERENCE，将其中的ref指针指向$a所指向的同一个zend_reference结构，并且将结构头部的refcount字段增一。 
 
 
具体执行顺序可能稍有不同，但是思路是一样的。
 
### 三、常用宏定义
 
为了方便编写代码，zend_types.h文件中还定义了一些适用于zval操作的宏，使用方便也便于理解，例如从第560行开始：
 
```c
#define Z_ISREF(zval)                (Z_TYPE(zval) == IS_REFERENCE)
#define Z_ISREF_P(zval_p)            Z_ISREF(*(zval_p))

#define Z_ISUNDEF(zval)                (Z_TYPE(zval) == IS_UNDEF)
#define Z_ISUNDEF_P(zval_p)            Z_ISUNDEF(*(zval_p))

#define Z_ISNULL(zval)                (Z_TYPE(zval) == IS_NULL)
#define Z_ISNULL_P(zval_p)            Z_ISNULL(*(zval_p))

... // 为节省篇幅，省略后续代码，请自行查看PHP源码
```
 


[0]: ./a_zval_long10.png
[1]: ./ab_zval_ref_long10.png