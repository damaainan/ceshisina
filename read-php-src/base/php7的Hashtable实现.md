# php7的Hashtable实现

 时间 2017-07-20 11:10:07  鱼儿的博客

原文[https://yuerblog.cc/2017/07/20/php7-hashtable/][1]


今天修复 [PHP-X项目][3] 的一个BUG时，顺便把php7的`hashtable`实现原理简单过了一下。 

代码来源于`PHP-X`项目里的一个数组迭代器，它里面涉及了如何遍历一个`hashtable`，以及`间接zval`的访问：

```c
class ArrayIterator
{
public:
    ArrayIterator(Bucket *p)
    {
        _ptr = p;
        _key = _ptr->key;
        _val = &_ptr->val;
        _index = _ptr->h;
        pe = p;
    }
    ArrayIterator(Bucket *p, Bucket *_pe)
    {
        _ptr = p;
        _key = _ptr->key;
        _val = &_ptr->val;
        _index = _ptr->h;
        pe = _pe;
    }
    void operator ++(int i)
    {
        while (++_ptr != pe)
        {
            _val = &_ptr->val;
            if (_val && Z_TYPE_P(_val) == IS_INDIRECT)
            {
                _val = Z_INDIRECT_P(_val);
            }
            if (UNEXPECTED(Z_TYPE_P(_val) == IS_UNDEF))
            {
                continue;
            }
            if (_ptr->key)
            {
                _key = _ptr->key;
                _index = 0;
            }
            else
            {
                _index = _ptr->h;
                _key = NULL;
            }
            break;
        }
    }
    bool operator !=(ArrayIterator b)
    {
        return b.ptr() != _ptr;
    }
    Variant key()
    {
        if (_key)
        {
            return Variant(_key->val, _key->len);
        }
        else
        {
            return Variant((long) _index);
        }
    }
    Variant value()
    {
        return Variant(_val);
    }
    Bucket *ptr()
    {
        return _ptr;
    }
private:
    zval *_val;
    zend_string *_key;
    Bucket *_ptr;
    Bucket *pe;
    zend_ulong _index;
};
```

生成迭代器的代码如下：

```c
ArrayIterator begin()
{
    return ArrayIterator(Z_ARRVAL_P(ptr())->arData, Z_ARRVAL_P(ptr())->arData + Z_ARRVAL_P(ptr())->nNumUsed);
}
ArrayIterator end()
{
    return ArrayIterator(Z_ARRVAL_P(ptr())->arData + Z_ARRVAL_P(ptr())->nNumUsed);
}
```

## Hashtable

什么是B`ucket`？为什么关联数组和非关联数组都可以通过顺序访问Bucket数组来实现遍历呢？

我找到一篇很好的博客讲解了PHP7中hashtable的实现， [点击阅读][4] 。 

## 间接zval

另外值得一提是的”`间接zval`”，也就是这段代码背后的含义：

```c
            if (_val && Z_TYPE_P(_val) == IS_INDIRECT)
            {
                _val = Z_INDIRECT_P(_val);
            }
```

这是PHP7的一个优化措施，宏观的理解起来不是很困难，下面慢慢道来。

透过`Z_INDIRECT_P`这个宏，我们就能知道背后的大概实现原理，所以我们进入zend源码来分析。

```c
#define Z_INDIRECT(zval)                        (zval).value.zv
#define Z_INDIRECT_P(zval_p)            Z_INDIRECT(*(zval_p))
```

这个宏简单的取出`zval对象`的value属性，我们先看看php7中`zval`的样子：

```c
struct _zval_struct {
        zend_value        value;                        /* value */
        union {
                struct {
                        ZEND_ENDIAN_LOHI_4(
                                zend_uchar    type,                     /* active type */
                                zend_uchar    type_flags,
                                zend_uchar    const_flags,
                                zend_uchar    reserved)     /* call info for EX(This) */
                } v;
                uint32_t type_info;
        } u1;
        union {
                uint32_t     next;                 /* hash collision chain */
                uint32_t     cache_slot;           /* literal cache slot */
                uint32_t     lineno;               /* line number (for ast nodes) */
                uint32_t     num_args;             /* arguments number for EX(This) */
                uint32_t     fe_pos;               /* foreach position */
                uint32_t     fe_iter_idx;          /* foreach iterator index */
                uint32_t     access_flags;         /* class constant access flags */
                uint32_t     property_guard;       /* single property guard */
                uint32_t     extra;                /* not further specified */
        } u2;
};
```

一个zval有固定的类型，上面也是通过宏来获取的，先看一下：

```c
    static zend_always_inline zend_uchar zval_get_type(const zval* pz) {
            return pz->u1.v.type;
    }
     
    /* we should never set just Z_TYPE, we should set Z_TYPE_INFO */
    #define Z_TYPE(zval)                            zval_get_type(&(zval))
    #define Z_TYPE_P(zval_p)                        Z_TYPE(*(zval_p))
```

可见，通过`zval.u1.v.type`就可以知道这个zval是什么类型，它存储的值大概是这些：

```c
    /* regular data types */
    #define IS_UNDEF                                        0
    #define IS_NULL                                         1
    #define IS_FALSE                                        2
    #define IS_TRUE                                         3
    #define IS_LONG                                         4
    #define IS_DOUBLE                                       5
    #define IS_STRING                                       6
    #define IS_ARRAY                                        7
    #define IS_OBJECT                                       8
    #define IS_RESOURCE                                     9
    #define IS_REFERENCE                            10
     
    /* constant expressions */
    #define IS_CONSTANT                                     11
    #define IS_CONSTANT_AST                         12
     
    /* fake types */
    #define _IS_BOOL                                        13
    #define IS_CALLABLE                                     14
    #define IS_ITERABLE                                     19
    #define IS_VOID                                         18
     
    /* internal types */
    #define IS_INDIRECT                     15
    #define IS_PTR                                          17
    #define _IS_ERROR                                       20
```

上面那些基础类型都是直接zval，只有`IS_INDIRECT`表示这是一个”`间接zval`”，那么何为”间接”呢？

我们回到`zval.value`这个属性，它的类型是`zend_value`：

```c
typedef union _zend_value {
        zend_long         lval;                         /* long value */
        double            dval;                         /* double value */
        zend_refcounted  *counted;
        zend_string      *str;
        zend_array       *arr;
        zend_object      *obj;
        zend_resource    *res;
        zend_reference   *ref;
        zend_ast_ref     *ast;
        zval             *zv;
        void             *ptr;
        zend_class_entry *ce;
        zend_function    *func;
        struct {
                uint32_t w1;
                uint32_t w2;
        } ww;
} zend_value;
```

它是一个`union`，也就是根据`zval`的类型，决定了`zval.value`里面使用哪个字段保存具体类型的值（的地址），而具体值的引用计数是保存在具体类型里的（整形，布尔这种不需要引用计数），比如`zend_string`的定义中有这样一个`zend_refcounted_h gc`的引用计数的字段：

```c
struct _zend_string {
        zend_refcounted_h gc;
        zend_ulong        h;                /* hash value */
        size_t            len;
        char              val[1];
};
```

这代表着，你对zval进行浅拷贝是不会修改引用计数的，必须通过`zend api`对`zval.value`内的具体对象进行引用计数操作，这一块我是顺便扯一下，我们还是回到”间接zval”。

间接zval的value中保存的不是`zend_string*`，也不是`zend_arrary*`等等，它保存的是`zval *zv`，也就是记录了另外一个zval对象的地址，这是很奇怪的，因为php7已经把zval设计为栈存储了，为什么zval内又保存了一个zval的指针呢？ **下面是重点！**

这里要说一下PHP7的`CV表`，其全拼是`compiled variable`，也就是编译时可以确定的变量。只要你是通过$a，$b这样在代码里定义的变量都会在编译时刻保存在一个全局的table里，你可以理解为`zval cv[100000]`这样一个大数组里，每一个zval对应编译时确定的变量，也就是`cv[0]`是$a，`cv[1]`是$b，这个cv表一旦解析为`opcode`就固定了，其中的每个zval的内存永久存在，当然你可以删除$a，比如unset($a)，这样带来的效果只是`cv[0].u1.v.type == IS_UNDEF`而已！

那么我们也知道，PHP允许这样玩：

```php
$var_nane = "a";
$$var_name = "b";
```

那么`$var_name`就是cv表里的，编译时刻可以确定的zval，它的内存永久有效。而`$$var_name`是运行时才能确定的变量（`$$var_name`效果等于访问$a），不会存在cv表里。

关注的重点是理解cv表，至于`$$var_name`这种用法不是重点。重点是，cv表中的zval其生命期伴随PHP脚本执行一直存在，所以优化就是我们完全可以定义一个”间接zval”来指向cv表中的zval，不需要管理引用计数，就是这么回事。


[1]: https://yuerblog.cc/2017/07/20/php7-hashtable/

[3]: https://github.com/swoole/PHP-X
[4]: http://gywbd.github.io/posts/2014/12/php7-new-hashtable-implementation.html