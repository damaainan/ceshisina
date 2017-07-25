# [PHP内核--类和面向对象][0]

 2016-10-23 21:50  793人阅读  [评论][1](0)  [收藏][2]  [举报][3]

 本文章已收录于：

![][4]

 分类：

版权声明：本文为博主原创文章，转载请说明出处。

在最开始接触PHP的时候，都是面向过程的方法来自己做一些很简单的网站在玩，写PHP代码就是堆砌，拓展性与维护性太差改个逻辑极不方便。后来发现PHP是支持面向对象的，忽然觉得自己那是后还真是年轻，真是孤陋寡闻呀，毕竟PHP是用C来实现，也不足为奇。

 **前言：**

 > 从我们接触 [**> PHP**][5] > 开始，我们最先遇到的是函数：数组操作函数，字符串操作函数，文件操作函数等等。 这些函数是我们使用PHP的基础，也是PHP自出生就支持的面向过程编程。面向过程将一个个功能封装， 以一种模块化的思想解决问题。

 > 从PHP4起开始支持面向对象编程。但PHP4的面向对象支持不太完善。 从PHP5起，PHP引入了新的对象模型（Object Model），增加了许多新特性，包括访问控制、 抽象类和final类、类方法、魔术方法、接口、对象克隆和类型提示等。 **> 并且在近期发布的PHP5.3版本中，针对面向对象编程增加了命名空间、延迟静态绑定** > 以及增加了两个魔术方法__callStatic()和__invoke()。

 > 那么，在PHP底层，其是怎么实现的呢，其结构如何？

 **一。类的结构**

 引用TIPI的一个事例：

```php
    class ParentClass {
    }
     
    interface Ifce {
            public function iMethod();
    }
     
    final class Tipi extends ParentClass implements Ifce {
            public static $sa = 'aaa';
            const CA = 'bbb';
     
            public function __constrct() {
            }
     
            public function iMethod() {
            }
     
            private function _access() {
            }
     
            public static function access() {
            }
    }
```

> 这里定义了一个父类ParentClass，一个接口Ifce，一个子类Tipi。子类继承父类ParentClass， 实现接口Ifce，并且有一个静态变量$sa，一个类常量 CA，一个公用方法，一个私有方法和一个公用静态方法。 这些结构在Zend引擎内部是如何实现的？类的方法、成员变量是如何存储的？访问控制，静态成员是如何标记的？

> 首先，我们看看类的内部存储结构:


```c
struct _zend_class_entry {

    char type;     // 类型：ZEND_INTERNAL_CLASS / ZEND_USER_CLASS

    char *name;// 类名称

    zend_uint name_length;                  // 即sizeof(name) - 1

    struct　_zend_class_entry *parent; // 继承的父类

    int　refcount;  // 引用数

    zend_bool constants_updated;

    zend_uint ce_flags; // ZEND_ACC_IMPLICIT_ABSTRACT_CLASS: 类存在abstract方法

    // ZEND_ACC_EXPLICIT_ABSTRACT_CLASS: 在类名称前加了abstract关键字

    // ZEND_ACC_FINAL_CLASS

    // ZEND_ACC_INTERFACE

    HashTable function_table;      // 方法

    HashTable default_properties;          // 默认属性

    HashTable properties_info;     // 属性信息

    HashTable default_static_members;// 类本身所具有的静态变量

    HashTable *static_members; // type == ZEND_USER_CLASS时，取&default_static_members;

    // type == ZEND_INTERAL_CLASS时，设为NULL

    HashTable constants_table;     // 常量

    struct _zend_function_entry *builtin_functions;// 方法定义入口

    union _zend_function *constructor;

    union _zend_function *destructor;

    union _zend_function *clone;

    /* 魔术方法 */

    union _zend_function *__get;

    union _zend_function *__set;

    union _zend_function *__unset;

    union _zend_function *__isset;

    union _zend_function *__call;

    union _zend_function *__tostring;

    union _zend_function *serialize_func;

    union _zend_function *unserialize_func;

    zend_class_iterator_funcs iterator_funcs;// 迭代

    /* 类句柄 */

    zend_object_value (*create_object)(zend_class_entry *class_type TSRMLS_DC);

    zend_object_iterator *(*get_iterator)(zend_class_entry *ce, zval *object,

        intby_ref TSRMLS_DC);

    /* 类声明的接口 */

    int(*interface_gets_implemented)(zend_class_entry *iface,

            zend_class_entry *class_type TSRMLS_DC);

    /* 序列化回调函数指针 */

    int(*serialize)(zval *object， unsignedchar**buffer, zend_uint *buf_len,

             zend_serialize_data *data TSRMLS_DC);

    int(*unserialize)(zval **object, zend_class_entry *ce, constunsignedchar*buf,

            zend_uint buf_len, zend_unserialize_data *data TSRMLS_DC);

    zend_class_entry **interfaces;  //  类实现的接口

    zend_uint num_interfaces;   //  类实现的接口数

    char *filename; //  类的存放文件地址 绝对地址

    zend_uint line_start;   //  类定义的开始行

    zend_uint line_end; //  类定义的结束行

    char *doc_comment;

    zend_uint doc_comment_len;

    struct _zend_module_entry *module; // 类所在的模块入口：EG(current_module)

};
```
##### [来自CODE的代码片][75] snippet_file_0.txt

> 取上面这个结构的部分字段，我们分析文章最开始的那段PHP代码在内核中的表现。 如下 > 所示：

字段名 | 字段说明 | ParentClass类 | Ifce接口 | Tipi类
-|-|-|-|-
name | 类名 | ParentClass | Ifce | Tipi
type | 类别 | 2(用户自定义) | 2 (用户自定义) | 2 (用户自定义,1为系统内置类)
parent | 父类 | 空 | 空 | ParentClass类
refcount | 引用计数 | 1 | 1 | 2
ce_flags | 类的类型 | 0 | 144 | 524352
interfaces | 接口列表 | 空 | 空 | Ifce接口 接口数为1
filename | 存放文件地址 | /tipi.php | /tipi.php | /ipi.php
line_start | 类开始行数 | 15 | 18 | 22
line_end | 类结束行数 | 16 | 203 | 8

function_table 函数列表 空 function_name=iMethod | type=2 | fn_flags=258  
function_name=__construct | type=2 | fn_flags=8448
function_name=iMethod | type=2 | fn_flags=65800
function_name=_access | type=2 | fn_flags=66560
function_name=access | type=2 | fn_flags=257

**二。变量与成员变量**

如[PHP内核的存储机制（分离/改变）][76]所介绍，

变量要么是定义在全局范围中，叫做全局变量，要么是定义在某个函数中， 叫做局部变量。

成员变量是定义在类里面，并和成员方法处于同一层次。如下一个简单的PHP代码示例，定义了一个类， 并且这个类有一个成员变量。

    class Tipi { 
        public $var;
    }

1.成员变量的访问：

访问这个成员变量当然是通过对象来访问。

2.成员变量的规则：

> 1.接口中不允许使用成员变量

> 2.成员变量不能拥有抽象属性

> 3.不能声明成员变量为final

> 4.不能重复声明属性

在声明类的时候初始化了类的成员变量所在的HashTable，之后如果有新的成员变量声明时，在编译时zend_do_declare_property。函数首先检查成员变量不允许的这4 条情况。

比如：.

    class Tipi { 
        public final $var;
    }

运行程序将报错，违反了第三条：Fatal error: Cannot declare property Tipi::$var final, the final modifier is allowed only for methods and classes in .. 这个错误由zend_do_declare_property函数抛出

**三。函数与成员方法**

成员方法从本质上来讲也是一种函数，所以其存储结构也和常规函数一样，存储在zend_function结构体中。 

![][77]

对于一个类的多个成员方法，它是以HashTable的数据结构存储了多个zend_function结构体。 和前面的成员变量一样，在类声明时成员方法也通过调用zend_initialize_class_data方法，初始化了整个方法列表所在的HashTable。 在类中我们如果要定义一个成员方法，格式如下：

    class Tipi{ 
         public function t() {echo 1; }
    }

除去访问控制关键字，一个成员方法和常规函数是一样的，从语法解析中调用的函数一样（都是zend_do_begin_function_declaration函数）， 但是其调用的参数有一些不同，第三个参数is_method，成员方法的赋值为1，表示它作为成员方法的属性。 在这个函数中会有一系统的编译判断，比如 **在接口中不能声明私有的成员方法** 。 看这样一段代码：

    interface Ifce { 
        private function method();
    }

如果直接运行，程序会报错：Fatal error: Access type for interface method Ifce::method() must be omitted in 这段代码对应到zend_do_begin_function_declaration函数中的代码。

**四。方法(Function)与函数(Method)的异同**

在前面介绍了函数的实现，函数与方法的本质是比较相似的，都是将一系列的逻辑放到一个集合里执行, 但二者在使用中也存在很多的不同，这里我们讨论一下二者的实现。 从实现的角度来看，二者内部代码都被最终解释为op_array，其执行是没有区别的（除非使用了$this/self等对象特有的变法或方法）, 而二者的不同体现在两个方面：

> 1.是定义（注册）的实现；

> 2.是调用的实现；

**定义（注册）方式的实现**

 函数和方法都是在编译阶段注册到compiler_globals变量中的，二者都使用相同的内核处理函数zend_do_begin_function_declaration() 和zend_do_end_function_declaration()来完成这一过程。 二者的内部内容会被最终解释并存储为一个op_codes数组，但编译后“挂载”的位置不同，如下图：

![][78]

PHP中函数与方法的注册位置

****

**调用方式的实现**

定义位置的不同，以及性质的不同，决定了方法比函数要进行更多的验证工作, 方法的调用比函数的调用多一个名为 **ZEND_INIT_METHOD_CALL的OPCODE** ，

其作用是把方法注册到execute_data.fbc , 然后就可以使用与函数相同的处理函数 **ZEND_DO_FCALL_BY_NAME** 进行处理。

[0]: http://blog.csdn.net/ty_hf/article/details/52904803
[5]: http://lib.csdn.net/base/php
[6]: #
[75]: https://code.csdn.net/snippets/1945024
[76]: http://blog.csdn.net/ty_hf/article/details/51057954
[77]: ../img/20161023214936851.png
[78]: ../img/20161023214953539.png