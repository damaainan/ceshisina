# [【PHP内核】运算符：instanceof的内核实现][0]

 标签： [php][1][内核][2][zend][3][zend引擎][4][php内核][5]

 2016-04-07 20:39  570人阅读  

 本文章已收录于：



 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [ZEND_INSTANCEOF_SPEC_CV_CONST_HANDLER][11]
  1. [A如果右值为接口instanceof interface][12]
  1. [B如果右值为普通类instanceof 非interface][13]
1. [ZEND_INSTANCEOF_SPEC_CV_VAR_HANDLER][14]
1. [总结][15]

**（文中涉及所有代码均为:[PHP][16]-7.0.4版本）**

PHP中有个类型运算符instanceof 用于确定一个 PHP 变量是否属于某一类 class，引用官方文档中的例子：

```php
    <?php
    class MyClass
    {
    }
    
    class NotMyClass
    {
    }
    $a = new MyClass;
    
    var_dump($a instanceof MyClass);
    var_dump($a instanceof NotMyClass);
    ?>
    ==============================
    bool(true)
    bool(false)
```

instanceof也可用来确定一个变量是不是继承自某一父类的子类，或是确定一个变量是不是实现了某个接口的对象。关于详细的instanceof可以查看：[http://php.net/manual/zh/language.operators.type.php][17]

这里要分析的是instanceof的内核实现过程。

我们还是从一个简单的示例入手：

```php
    <?php
    class MyClass
    {
    }
    
    $a = new MyClass;
    $b = new MyClass;
    
    var_dump($a instanceof MyClass);
    var_dump($a instanceof $b);
    ================================
    bool(true)
    bool(true)
```

编译完成后的opcodes见下图：

![这里写图片描述][18]

这个例子opcode比较多，忽略其它opcode，这里这讨论ZEND_INSTANCEOF，对于“$a instanceof MyClass”，根据op1（16）、op2（1）计算：

```c
    //zend_vm_execute.h #49720
    zend_opcode_handlers[opcode * 25 + zend_vm_decode[op->op1_type] * 5 + zend_vm_decode[op->op2_type]];
    
    138*25 + 4*5 + 0 = 3470
```

得到此opcode的处理handler为：**ZEND_INSTANCEOF_SPEC_CV_CONST_HANDLER**

同样可以算出 a i n s t a n c e o f b对应的handler为：**ZEND_INSTANCEOF_SPEC_CV_VAR_HANDLER**

下面分别来看下这两个函数是如何判断的。

## 1、ZEND_INSTANCEOF_SPEC_CV_CONST_HANDLER

```c
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_INSTANCEOF_SPEC_CV_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        USE_OPLINE
    
        zval *expr;
        zend_bool result;
    
        SAVE_OPLINE();
        expr = _get_zval_ptr_cv_undef(execute_data, opline->op1.var);
    
        if (Z_TYPE_P(expr) == IS_OBJECT) {
            zend_class_entry *ce;
            ...
            ce = zend_fetch_class_by_name(Z_STR_P(EX_CONSTANT(opline->op2)), EX_CONSTANT(opline->op2) + 1, ZEND_FETCH_CLASS_NO_AUTOLOAD);
            ...
            result = ce && instanceof_function(Z_OBJCE_P(expr), ce);
        }
    }
```

zend_fetch_class_by_name这个函数是从EG(class_table)中查找对应的class结构，它返回一个zend_class_entry指针，用户定义的类在内核中就是对应一个 zend_class_entry，而object结构中会保存它所属的class，所以可以猜测instanceof的实现：要判断两个变量是否是同一个类实例化的对象只需要判断一下这两个对象指向的class是否为同一个即可。

下面是class与object的结构：

```c
    //zend.h #131
    struct _zend_class_entry {
        char type;
        zend_string *name;
        struct _zend_class_entry *parent;
        int refcount;
        uint32_t ce_flags;
    
        int default_properties_count;
        int default_static_members_count;
        zval *default_properties_table;
        zval *default_static_members_table;
        zval *static_members_table;
        HashTable function_table; //class method列表
        HashTable properties_info; //class属性列表
        HashTable constants_table; //class静态信息列表
        ...
        uint32_t num_interfaces; //此class实现的接口数量(class可以实现多个interface)
        zend_class_entry **interfaces; //实现的接口列表
        ...
    }
    
    //zend_type.h #275
    struct _zend_object {
        zend_refcounted_h gc;
        uint32_t          handle; //对象id
        zend_class_entry *ce; //所属class指针
        const zend_object_handlers *handlers;
        HashTable        *properties;
        zval              properties_table[1];
    };
```

具体的判断逻辑在instanceof_function()函数中：

```c
    //zend_operators.c #2129
    ZEND_API zend_bool ZEND_FASTCALL instanceof_function(const zend_class_entry *instance_ce, const zend_class_entry *ce)
    {
        if (ce->ce_flags & ZEND_ACC_INTERFACE) {
            return instanceof_interface(instance_ce, ce);
        } else {
            return instanceof_class(instance_ce, ce);
        }
    }
```

这里将分情况判断：

### A.如果右值为接口：instanceof interface

即判断左值所属class是否实现了右值的interface。

```php
    <?php
    interface Type{}
    
    class myClass implements Type{}
    
    $obj = new myClass();
    
    var_dump($obj instanceof Type);
    ?>
    ===============================
    bool(true)
```

这种情况由instanceof_interface()处理：

```c
    //zend_operators.c #2098
    static zend_bool ZEND_FASTCALL instanceof_interface(const zend_class_entry *instance_ce, const zend_class_entry *ce)
    {
        uint32_t i;
    
        //遍历左值class所有实现的接口，如果找到了与右值相同的则说明匹配成功，返回true
        for (i = 0; i < instance_ce->num_interfaces; i++) {
            if (instanceof_interface(instance_ce->interfaces[i], ce)) {
                return 1;
            }   
        }
        //实际还是通过下面这个方法判断的，上面只是将左值实现的接口遍历了一遍，逐个比较           
        return instanceof_class(instance_ce, ce);
    }
```

这时候你可能会问：如果左值class继承的父类实现了右值interface呢？这种情况是否为true呢？就像下面这个例子：

```php
    <?php
    interface Type{}
    
    class myClassParent implements Type{}
    
    class myClass extends myClassParent{}
    
    $obj = new myClass();
    
    var_dump($obj instanceof Type);
    ==============================
    bool(true)
```

答案是肯定的，从上面的源码可以看出实际还是由instanceof_class()函数判断的，这种情况与右值不是interface的相同，下面一起讨论。

### B.如果右值为普通类：instanceof 非interface

上面那种情况实际最终也是调的instanceof_class()进行判断的：

```c
    //zend_operators.c #2086
    static zend_always_inline zend_bool instanceof_class(const zend_class_entry *instance_ce, const zend_class_entry *ce)
    {   
        while (instance_ce) {
            if (instance_ce == ce) {
                return 1;
            }
            //迭代父类进行比较
            instance_ce = instance_ce->parent;
        }
        return 0;
    }
```

从这个方法可以很清楚的看到只要左值所属class及其父类中有一个与右值class相同就表示instanceof为true。

## 2、ZEND_INSTANCEOF_SPEC_CV_VAR_HANDLER

这种情况比较简单，即A instanceof B，A、B都是object的情况，这种判断的依据是比较A所属class及所有父类与B所属class是否相同，也就是说不考虑B的父类，只依据B所属的class，例如：

```php
    <?php
    interface Type{}
    
    class myClassParent implements Type{}
    
    class A extends myClassParent{}
    class B extends myClassParent{}
    
    $a = new A();
    $b = new B();
    
    var_dump($a instanceof $b);
    ===========================
    bool(false)
```

虽然AB都继承了myClassParent，但是判断的时候是这个条件：（A == B || myClassParent == B）。
```php
    <?php
    interface Type{}
    
    class myClassParent implements Type{}
    
    class A extends myClassParent{}
    class B extends myClassParent{}
    
    $a = new A();
    $b = new myClassParent();
    
    var_dump($a instanceof $b);
    ===========================
    bool(true)
```

这个判断的是：（A == myClassParent || myClassParent == myClassParent）。

## 3、总结

A instanceof B

可以按照这个规则判断：找出A所属的class、所有父类、所有实现的接口这三部分，其中只要有一个与B（或者B所属class）相等，那么(A instanceof B) == true.

[0]: http://blog.csdn.net/pangudashu/article/details/51039281
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e5%86%85%e6%a0%b8
[3]: http://www.csdn.net/tag/zend
[4]: http://www.csdn.net/tag/zend%e5%bc%95%e6%93%8e
[5]: http://www.csdn.net/tag/php%e5%86%85%e6%a0%b8
[10]: #
[11]: #t0
[12]: #t1
[13]: #t2
[14]: #t3
[15]: #t4
[16]: http://lib.csdn.net/base/php
[17]: http://php.net/manual/zh/language.operators.type.php
[18]: ../img/20160401202934471.png