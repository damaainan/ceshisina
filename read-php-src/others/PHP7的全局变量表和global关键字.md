## PHP7的全局变量表和global关键字

来源：[https://fengyoulin.com/2018/03/08/php7_global_variables_binding/](https://fengyoulin.com/2018/03/08/php7_global_variables_binding/)

时间 2018-03-08 09:56:13

 
根据PHP的官方文档，在自定义的function中使用global关键字，可以访问全局作用域内的全局变量。这里我们来研究一下PHP的全局变量表以及global机制的实现原理。使用PHP 7.2.2版源代码。
 
### 一、全局变量表
 
PHP中每个自定义的function都会被zend引擎编译成一个zend_op_array结构，等到了执行阶段，zend引擎在真正执行一个op_array之前，会先根据op_array的参数个数、局部变量个数以及临时变量的个数等信息，为其分配一段大小合适的内存用于存放zend_execute_data和前面提到的各种变量，这段内存可以理解为zend引擎的栈帧。zend_execute_data存储在栈帧的头部，各种变量紧随其后内存对齐。
 
zend_compile.h第363行zend_op_array结构的定义，为了节省篇幅省略掉部分代码，其中的num_args表示参数个数，last_var为局部变量个数，T是临时变量的个数：
 
```c
struct _zend_op_array {
    ...
    uint32_t num_args;
    uint32_t required_num_args;
    ...

    int last_var;
    uint32_t T;
    zend_string **vars;

    ...
};
```
 
zend_compile.h第462行zend_execute_data结构的定义：
 
```c
struct _zend_execute_data {
    const zend_op       *opline;           /* executed opline                */
    zend_execute_data   *call;             /* current call                   */
    zval                *return_value;
    zend_function       *func;             /* executed function              */
    zval                 This;             /* this + call_info + num_args    */
    zend_execute_data   *prev_execute_data;
    zend_array          *symbol_table;
#if ZEND_EX_USE_RUN_TIME_CACHE
    void               **run_time_cache;   /* cache op_array->run_time_cache */
#endif
#if ZEND_EX_USE_LITERALS
    zval                *literals;         /* cache op_array->literals       */
#endif
};
```
 
zend_execute.c第2130行描述栈帧布局的一段代码注释：
 
```c
/*
 * Stack Frame Layout (the whole stack frame is allocated at once)
 * ==================
 *
 *                             +========================================+
 * EG(current_execute_data) -> | zend_execute_data                      |
 *                             +----------------------------------------+
 *     EX_CV_NUM(0) ---------> | VAR[0] = ARG[1]                        |
 *                             | ...                                    |
 *                             | VAR[op_array->num_args-1] = ARG[N]     |
 *                             | ...                                    |
 *                             | VAR[op_array->last_var-1]              |
 *                             | VAR[op_array->last_var] = TMP[0]       |
 *                             | ...                                    |
 *                             | VAR[op_array->last_var+op_array->T-1]  |
 *                             | ARG[N+1] (extra_args)                  |
 *                             | ...                                    |
 *                             +----------------------------------------+
 */
```
 
在zend_execute_data结构中有一个symbol_table指针，指向一个HashTable。顾名思义，我们能够知道这个symbol_table是一个符号表。这个符号表的用途就是在必要的时候，将局部变量的存储位置与其名称进行关联，或者添加可变变量（这个我们放到另一篇文章中去研究）。为什么要说“必要的时候”，因为一般情况下，函数使用局部变量是不用根据变量名字用符号表去查找的，函数都是直接按变量在栈帧中的offset找到变量，就像是通过上面布局注释中VAR数组的下标。
 
当函数根本没有用到symbol_table时，symbol_table指针为空。但是在某些情况下，symbol_table会被用到，symbol_table指针自然也就不会为空了。在什么情况下symbol_table会被用到？一种情况就是我们现在要讲的全局变量表。
 
其实不仅是自定义的function会被编译为op_array，最外层的入口代码也会被编译为一个op_array，那么最外层的这个栈帧里面symbol_table指针所指向的HashTable自然也就用作全局变量表了。
 
这时会有一个问题，全局作用域内直接声明的变量在编译期间就已经确定了，运行时会被分配在栈帧里zend_execute_data结构后面的VAR数组里，又如何把它们添加到全局变量表里呢？你会想到使用引用，但是这不是一个好办法，这样一来所有显式声明的全局变量就都变成引用了。那PHP7是如何实现的呢？下面来看一下zend_value结构中的zv指针，和IS_INDIRECT类型。
 
首先是zend_types.h第159行zend_value的定义：
 
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
```
 
其中的zv指针指向一个zval结构，我们已经知道HashTable的每个Bucket中都包含了一个zval，zend_types.h第228行：
 
```c
typedef struct _Bucket {
    zval              val;
    zend_ulong        h;                /* hash value (or numeric index)   */
    zend_string      *key;              /* string key or NULL for numerics */
} Bucket;
```
 
再看一下第641行有关IS_INDIRECT类型的宏定义：
 
```c
#define Z_INDIRECT(zval)            (zval).value.zv
#define Z_INDIRECT_P(zval_p)        Z_INDIRECT(*(zval_p))
```
 
现在我们明白了，PHP为全局作用域内那些分配在栈帧上的局部变量，为每个变量在全局变量表里创建了一个指向它的IS_INDIRECT型指针，用变量的名字作为key。这个IS_INDIRECT机制与引用机制有些类似，但是不会把zval转换为引用，更不会增加引用计数。但是就是因为其不会增加引用计数，所以只适合用在一些特定的场合，要严格确保生命周期的一致性。因为要避免IS_INDIRECT指针指向的变量已被释放，从而造成逻辑错误甚至运行时异常。就像这里，当分配在栈帧上的变量被回收时变量表自然也被销毁了。
 
按照上面的思路，画如下示意图：
 
![][0]
 
### 二、函数变量的global关键字
 
下面我们写一些测试代码，并打印出相关的调试信息，来研究global关键字的实现机制。打印调试信息使用了PHP7的 [zendump][2] 扩展。
 
我们使用安装了 [zendump][2] 扩展的PHP7运行如下代码：
 
```php
<?php
$a = 10;
function func01() {
    zendump_vars();
    global $a;
    global $b;
    zendump_vars();
}
zendump_symbols();
func01();
zendump_symbols();
zendump_function('func01');
```
 
得到如下输出：
 
``` 
symbols(8): {
  ... // 省略部分输出
  "a" =>
  zval(0x7f51008641e0) -> zval(0x7f510081e080) : long(10)
}
vars(2): {
  $a ->
  zval(0x7f510081e130) : undefined
  $b ->
  zval(0x7f510081e140) : undefined
}
vars(2): {
  $a ->
  zval(0x7f510081e130) -> reference(2) addr(0x7f51008620a8) zval(0x7f51008620b0) : long(10)
  $b ->
  zval(0x7f510081e140) -> reference(2) addr(0x7f51008620e0) zval(0x7f51008620e8) : null
}
symbols(9): {
  ... // 省略部分输出
  "a" =>
  zval(0x7f51008641e0) -> zval(0x7f510081e080) -> reference(1) addr(0x7f51008620a8) zval(0x7f51008620b0) : long(10)
  "b" =>
  zval(0x7f5100864200) -> reference(1) addr(0x7f51008620e0) zval(0x7f51008620e8) : null
}
op_array("func01") func01() refcount(1) addr(0x7f510080d0f8) vars(2) T(2) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/variables/variable_global02.php) line(3,8)
OPCODE                             OP1                                OP2                                RESULT                             
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_BIND_GLOBAL                   $a                                 "a"                                                                   
ZEND_BIND_GLOBAL                   $b                                 "b"                                                                   
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_RETURN                        null
```
 
为了节省篇幅，省略掉部分不相关的输出。我们结合代码的执行顺序来逐步分析一下上面的输出结果：
 

* 在全局作用域内使用`zendump_symbols()`打印变量表，此时变量表里只有一条key为”a”的记录，类型为IS_INDIRECT指向地址0x7f510081e080处的一个类型为long值为10的zval。  
* 在函数`func01`的第一条语句使用`zendump_vars()`打印所有静态声明的局部变量，此时$a和$b都还是undefined。  
* 在函数`func01`执行过`global $a;`和`global $b;`两句代码之后，再次使用`zendump_vars()`打印所有静态声明的局部变量，此时$a指向地址0x7f51008620a8处的引用，引用计数为2，其中zval的类型为long值为10。此时的$b指向地址0x7f51008620e0处的引用，引用计数为2，其中zval的类型为null。  
* 从函数`func01`返回后，再次在全局作用域内使用`zendump_symbols()`打印变量表，此时key为”a”的IS_INDIRECT指向的地址0x7f510081e080处的zval已经变成IS_REFERENCE类型，指向地址0x7f51008620a8处的引用，与函数`func01`里的$a相同，只是从函数返回后引用计数变成了1。而且变量表里多了一条key为”b”的记录，类型不是IS_INDIRECT指针，而是IS_REFERENCE引用指向地址0x7f51008620e0处，与函数`func01`里的$b相同，返回后引用计数也递减为1。这里之所以不是IS_INDIRECT指针，就是因为”b”没有直接在全局作用域内声明，而是在函数`func01`里用global关键字动态添加到全局变量表中的。IS_INDIRECT指针是PHP7为了把zend虚拟机最外层栈帧上静态声明（分配）的变量添加到全局变量表又不把它们转换成引用而引入的一种机制。运行时动态创建的变量不会分配在栈帧上，自然不需要也不能使用这种机制。  
* 使用`zendump_function()`函数打印`func01`的OPCODE，可以看到与global关键字对应的指令ZEND_BIND_GLOBAL，第一个操作数是指定的本地局部变量，第二个操作数是想要关联的全局变量名。而且根据上面的调试输出我们可以知道，当全局变量表里存在对应的key时，该指令会将本地变量用引用的方式与其关联，key如果不存在，该指令会使用指定的key在全局变量表里添加一个null值，再通过引用将本地变量与其关联。  
 

在函数`func01`执行过`global $a;`和`global $b;`两句代码之后，全局变量与函数局部变量关联示意图：
 
![][1]
 


[2]: https://github.com/php7th/zendump
[3]: https://github.com/php7th/zendump
[0]: ../img/nquq2qZ.png  
[1]: ../img/Yb6fi2V.png 