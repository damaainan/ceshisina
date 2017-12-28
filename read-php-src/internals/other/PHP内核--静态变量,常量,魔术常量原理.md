#  [PHP内核--静态变量,常量,魔术常量原理][0]

 2016-10-21 00:04  880人阅读  

 分类：

版权声明：本文为博主原创文章，转载请说明出处。

本篇通过PHP源码，从 结构入手 来对静态变量，常量，魔术常量进行分析。

- - -

**1.静态变量**

我们都知道，静态变量是在PHP脚本载入时就加载了，即1.不用new其对象就可以直接调用，2.并且静态变量存储在公共区域同一类的多个对象共同操作一个静态变量，3.静态变量只有在脚本结束后内存才会释放，针对这三个问题，想问一句，为什么？

**下边展开叙述**

先看其结构，更好进行分析了解。

静态变量是存储在函数结构体_zend_execute_data 中的，

而这个结构体中，有两个很关键的结构体，op_array和 symbol_table

1.*symbol_table 存放此类里边的各种变量等，每次new对象时，会开辟新的环境空间，详见[PHP内核--浅谈PHP灵魂HashTble][5]事例二，

2.函数编译后的opcode存储在*op_array;结构中,存储的也就是这个函数的逻辑，每次new对象时，公用一个空间逻辑，不会自己在独立开辟环境空间【很关键，实现静态根本原因】

**Zend/zend_compiles.h 384行，执行环境结构体**

```c
    struct _zend_execute_data {
        struct _zend_op *opline;
        zend_function_state function_state;
        zend_op_array *op_array;//！！！！！函数编译后的执行逻辑，编译后的opcode二进制代码，称为op_array，公用一个逻辑
        zval *object;
        HashTable *symbol_table;//！！！！！此函数的符号表地址，每次new会开辟一个新的空间《---------
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
```

**Zend/zend_compiles.h 261行,op_array结构代码**   
 
```c
    struct _zend_op_array {
        /* Common elements */
        zend_uchar type;
    ...
    /* static variables support */
        HashTable *static_variables;//294行 ,静态变量
    ...
    ｝
```

举例：

```c
    t1() {
    $a +=1 ;
    static $b +=1;
    t1();
    t1();
    } //加自身共调用3次
```
结果$a每回都是1，而$b = 1,2,3

原因如下：

三次调用函数开辟的符号表【3份 】

[t_3 execute_data] ---->[symbol_table_3]

[t_2 execute_data] ---->[symbol_table_2]

[t_1 execute_data] ---->[symbol_table_1]

*op_array->*静态变量表 【一份 】

![][7]

**结论 ：**

类的变量是存储在 *symbol_table中的，每个都有其作用域，每次实例化都会在开辟一个环境空间（详见Hashtable第二部分的举例）；而静态变量不同，如代码所示，它存储在op_array里边，op_array是什么，编译生成的opcode代码，存储的是函数的逻辑，不管new多少个对象，这个逻辑是公用的，而静态变量也存储在这个结构中，所以实现了同一类的不同对象可以公用一个静态变量，也解释了在PHP层面，静态变量为什么不用new就直接调用。解释了问题一二，

因为静态变量存储在op_array里边，op_array是在脚本执行结束后释放，所以其也在这个时候释放.，解释问题三。

- - -

**2.常量**

首先看下常量与变量的区别，常量是在变量的zval结构的基础上添加了一额外的元素。如下所示为PHP中常量的内部结构。

常量的结构 (Zend/zend_constants.h文件的33行）

```c
    typedef struct _zend_constant {
        zval value; /* zval结构，PHP内部变量的存储结构 */
        char *name; /* 常量名称 */
        uint name_len;  
        int flags;  /* 常量的标记如 CONST_PERSISTENT | CONST_CS */
        int module_number;  /* 模块号 */
    } zend_constant;
```

结构体如上，name,name_len一目了然，值得一提的是zval与变量中存储的zval结构一模一样，(详见[PHP内核的存储机制（分离/改变）][8])

主要解释下 flag 与 module_number

**1.flags:**  
c.flags = case_sensitive / case insensitive ; // 1,0  
  赋值给结构体字段是否开启大小写敏感

**2.module_number:**  
1.PHP_USER_CONSTANT:用户定义的常量  
（define函数定义的常量的模块编号都是）  
2.REGISTER_MAIN_LONG_CONSTANT：PHP内置定义常量  
比如错误报告级别E_ALL, E_WARNING,PHP_VERSION等常量，都是持久化常量，最后才销毁

- - -

**3.魔术常量**

说是常量，其实每次值在不同位置，可能是不相同的，原因是为什么呢？

PHP内核会在词法解析时将这些常量的内容赋值进行替换，而不是在运行时进行分析。 如下PHP代码：

![][9]

以__FUNCTION__为例， 在Zend/zend_language_scanner.l文件中，__FUNCTION__是一个需要分析的元标记（token）：

![][10]

就是这里，当当前中间代码处于一个函数中时，则将当前函数名赋值给zendlval(也就是token T_FUNC_C的值内容)， 如果没有，则将空字符串赋值给zendlval(因此在顶级作用域名中直接打印__FUNCTION__会输出空格)。 这个值在语法解析时会直接赋值给返回值。这样我们就在生成的中间代码中看到了这些常量的位置都已经赋值好了。

 只要记住，上边代码实现的功能，将__FUNCTION__在词法分析时，转换成当时对应的值。

（php中有CG和EG两个宏，分别获取compile_global数据和excutor_global的数据，它们分别有各自的function_table和class_table，  
另外php中的require是作为函数来执行的，因此这个时候需要知道EG和CG之间是如何转换的。）

[0]: http://blog.csdn.net/ty_hf/article/details/52878294
[5]: http://blog.csdn.net/ty_hf/article/details/52906459
[6]: #
[7]: ../img/20161020232312059.png
[8]: http://blog.csdn.net/ty_hf/article/details/51057954
[9]: ../img/20161021000103268.png
[10]: ../img/20161021000111514.png