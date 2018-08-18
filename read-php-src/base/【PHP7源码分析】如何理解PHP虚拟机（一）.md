## 【PHP7源码分析】如何理解PHP虚拟机（一）

来源：[https://segmentfault.com/a/1190000015930021](https://segmentfault.com/a/1190000015930021)

顺风车运营研发团队 李乐
## 1.从物理机说起

虚拟机也是计算机，设计思想和物理机有很多相似之处；
## 1.1冯诺依曼体系结构

冯·诺依曼是当之无愧的数字计算机之父，当前计算机都采用的是冯诺依曼体系结构；设计思想主要包含以下几个方面：


* 指令和数据不加区别混合存储在同一个存储器中，它们都是内存中的数据。现代CPU的保护模式，每个内存段都有段描述符，这个描述符记录着这个内存段的访问权限(可读,可写,可执行)。这就变相的指定了哪些内存中存储的是指令哪些是数据）；
* 存储器是按地址访问的线性编址的一维结构，每个单元的位数是固定的；
* 数据以二进制表示；
* 指令由操作码和操作数组成。操作码指明本指令的操作类型,操作数指明操作数本身或者操作数的地址。操作数本身并无数据类型，它的数据类型由操作码确定；任何架构的计算机都会对外提供指令集合；
* 运算器通过执行指令直接发出控制信号控制计算机各项操作。由指令计数器指明待执行指令所在的内存地址。指令计数器只有一个，一般按顺序递增，但执行顺序可能因为运算结果或当时的外界条件而改变；


![][0]
## 1.2汇编语言简介

任何架构的计算机都会提供一组指令集合；

指令由操作码和操作数组成；操作码即操作类型，操作数可以是一个立即数或者一个存储地址；每条指令可以有0、1或2个操作数；

指令就是一串二进制；汇编语言是二进制指令的文本形式；

```
push   %ebx
mov    %eax, [%esp+8]
mov    %ebx, [%esp+12]
add    %eax, %ebx
pop    %ebx
```

push、mov、add、pop等就是操作码；
%ebx寄存器；[%esp+12]内存地址；
操作数只是一块可存取数据的存储区；操作数本身并无数据类型，它的数据类型由操作码确定；
如movb传送字节，movw传送字，movl传送双字等
## 1.3 函数调用栈

过程（函数）是对代码的封装，对外暴露的只是一组指定的参数和一个可选的返回值；可以在程序中不同的地方调用这个函数；假设过程P调用过程Q，Q执行后返回过程P；为了实现这一功能，需要考虑三点：


* 指令跳转：进入过程Q的时候，程序计数器必须被设置为Q的代码的起始地址；在返回时，程序计数器需要设置为P中调用Q后面那条指令的地址；
* 数据传递：P能够向Q提供一个或多个参数，Q能够向P返回一个值；
* 内存分配与释放：Q开始执行时，可能需要为局部变量分配内存空间，而在返回前，又需要释放这些内存空间；


大多数的语言过程调用都采用了栈数据结构提供的内存管理机制；如下图所示：

![][1]

函数的调用与返回即对应的是一系列的入栈与出栈操作；
函数在执行时，会有自己私有的栈帧，局部变量就是分配在函数私有栈帧上的；
平时遇到的栈溢出就是因为调用函数层级过深，不断入栈导致的；
## 2.PHP虚拟机

虚拟机也是计算机，参考物理机的设计，设计虚拟机时，首先应该考虑三个要素：指令，数据存储，函数栈帧；

下面从这三点详细分析PHP虚拟机的设计思路；
## 2.1指令
### 2.1.1 指令类型

任何架构的计算机都需要对外提供一组指令集，其代表计算机支持的一组操作类型；

PHP虚拟机对外提供186种指令，定义在zend_vm_opcodes.h文件中；

```c
//加、减、乘、除等
#define ZEND_ADD                               1
#define ZEND_SUB                               2
#define ZEND_MUL                               3
#define ZEND_DIV                               4
#define ZEND_MOD                               5
#define ZEND_SL                                6
#define ZEND_SR                                7
#define ZEND_CONCAT                            8
#define ZEND_BW_OR                             9
#define ZEND_BW_AND                           10
……………………
```
### 2.1.2 指令
#### 2.1.2.1指令的表示

指令由操作码和操作数组成；操作码指明本指令的操作类型，操作数指明操作数本身或者操作数的地址；

PHP虚拟机定义指令格式为：操作码 操作数1 操作数2 返回值；其使用结构体_zend_op表示一条指令：

```c
struct _zend_op {
    const void *handler;    //指针，指向当前指令的执行函数
    znode_op op1;           //操作数1         
    znode_op op2;           //操作数2
    znode_op result;        //返回值
    uint32_t extended_value;//扩展
    uint32_t lineno;        //行号
    zend_uchar opcode;      //指令类型
    zend_uchar op1_type;    //操作数1的类型（此类型并不代表字符串、数组等数据类型；其表示此操作数是常量，临时变量，编译变量等）
    zend_uchar op2_type;    //操作数2的类型
    zend_uchar result_type; //返回值的类型
};
```
#### 2.1.2.2 操作数的表示

从上面可以看到，操作数使用结构体znode_op表示，定义如下：

constant、var、num等都是uint32_t类型的，这怎么表示一个操作数呢？（既不是指针不能代表地址，也无法表示所有数据类型）；
其实，操作数大多情况采用的相对地址表示方式，constant等表示的是相对于执行栈帧首地址的偏移量；
另外，_znode_op结构体中有个zval *zv字段，其也可以表示一个操作数，这个字段是一个指针，指向的是zval结构体，PHP虚拟机支持的所有数据类型都使用zval结构体表示；

```c
typedef union _znode_op {
        uint32_t      constant;
        uint32_t      var;
        uint32_t      num;
        uint32_t      opline_num;
    #if ZEND_USE_ABS_JMP_ADDR
        zend_op       *jmp_addr;
    #else
        uint32_t      jmp_offset;
    #endif
    #if ZEND_USE_ABS_CONST_ADDR
        zval          *zv;
    #endif
} znode_op;
```
## 2.2 数据存储

PHP虚拟机支持多种数据类型：整型、浮点型、字符串、数组，对象等；PHP虚拟机如何存储和表示多种数据类型？

2.1.2.2节指出结构体_znode_op代表一个操作数；操作数可以是一个偏移量（计算得到一个地址，即zval结构体的首地址），或者一个zval指针；PHP虚拟机使用zval结构体表示和存储多种数据；

```c
struct _zval_struct {
    zend_value        value;            //存储实际的value值
    union {
        struct {                        //一些标志位
            ZEND_ENDIAN_LOHI_4(
                zend_uchar    type,         //重要；表示变量类型
                zend_uchar    type_flags,
                zend_uchar    const_flags,
                zend_uchar    reserved)     /* call info for EX(This) */
        } v;
        uint32_t type_info;
    } u1;
    union {                                 //其他有用信息
        uint32_t     next;                 /* hash collision chain */
        uint32_t     cache_slot;           /* literal cache slot */
        uint32_t     lineno;               /* line number (for ast nodes) */
        uint32_t     num_args;             /* arguments number for EX(This) */
        uint32_t     fe_pos;               /* foreach position */
        uint32_t     fe_iter_idx;          /* foreach iterator index */
        uint32_t     access_flags;         /* class constant access flags */
        uint32_t     property_guard;       /* single property guard */
    } u2;
};
```

zval.u1.type表示数据类型， zend_types.h文件定义了以下类型：

```c
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
…………
```

zend_value存储具体的数据内容，结构体定义如下：

_zend_value占16字节内存；long、double类型会直接存储在结构体；引用、字符串、数组等类型使用指针存储；

代码中根据zval.u1.type字段，判断数据类型，以此决定操作_zend_value结构体哪个字段；

可以看出，字符串使用zend_string表示，数组使用zend_array表示…

```c
typedef union _zend_value {
    zend_long         lval;            
    double            dval;            
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

如下图为PHP7中字符串结构图：

![][2]
## 2.3 再谈指令

2.1.2.1指出，指令使用结构体_zend_op表示；其中最主要2个属性：操作函数，操作数（两个操作数和一个返回值）；

操作数的类型（常量、临时变量等）不同，同一个指令对应的handler函数也会不同；操作数类型定义在 Zend/zend_compile.h文件：

```c
//常量
#define IS_CONST    (1<<0)
 
//临时变量，用于操作的中间结果；不能被其他指令对应的handler重复使用
#define IS_TMP_VAR  (1<<1)
 
//这个变量并不是PHP代码中声明的变量，常见的是返回的临时变量，比如$a=time()， 函数time返回值的类型就是IS_VAR，这种类型的变量是可以被其他指令对应的handler重复使用的
#define IS_VAR      (1<<2)
#define IS_UNUSED   (1<<3)  /* Unused variable */
 
//编译变量；即PHP中声明的变量；
#define IS_CV       (1<<4)  /* Compiled variable */
```

操作函数命名规则为：ZEND_[opcode]_SPEC_(操作数1类型)_(操作数2类型)_(返回值类型)_HANDLER

比如赋值语句就有以下多种操作函数：

```c
ZEND_ASSIGN_SPEC_VAR_CONST_RETVAL_UNUSED_HANDLER,
ZEND_ASSIGN_SPEC_VAR_TMP_RETVAL_UNUSED_HANDLER,
ZEND_ASSIGN_SPEC_VAR_VAR_RETVAL_UNUSED_HANDLER,
ZEND_ASSIGN_SPEC_VAR_CV_RETVAL_UNUSED_HANDLER,
…
```

对于$a=1，其操作函数为： ZEND_ASSIGN_SPEC_CV_CONST_RETVAL_UNUSED_HANDLER；函数实现为：

```c
static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ASSIGN_SPEC_CV_CONST_RETVAL_UNUSED_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
{
    USE_OPLINE
 
    zval *value;
    zval *variable_ptr;
 
    SAVE_OPLINE();
    //获取op2对应的值，也就是1
    value = EX_CONSTANT(opline->op2);
    //在execute_data中获取op1的位置，也就是$a（execute_data类似函数栈帧，后面详细分析）
    variable_ptr = _get_zval_ptr_cv_undef_BP_VAR_W(execute_data, opline->op1.var);
     
    //赋值
    value = zend_assign_to_variable(variable_ptr, value, IS_CONST);
    if (UNEXPECTED(0)) {
        ZVAL_COPY(EX_VAR(opline->result.var), value);
    }
 
    ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();
}
```
## 2.4 函数栈帧
### 2.4.1指令集

上面分析了指令的结构与表示，PHP虚拟机使用_zend_op_array表示指令的集合：

```c
struct _zend_op_array {
    …………
    //last表示指令总数；opcodes为存储指令的数组；
    uint32_t last;
    zend_op *opcodes;
    //变量类型为IS_CV的个数
    int last_var;
    //变量类型为IS_VAR和IS_TEMP_VAR的个数
    uint32_t T;
    //存放IS_CV类型变量的数组
    zend_string **vars;
 
    …………
     
    //静态变量
    HashTable *static_variables;
 
    //常量个数；常量数组
    int last_literal;
    zval *literals;
 
    …
};
```

注意： last_var代表IS_CV类型变量的个数，这种类型变量存放在vars数组中；在整个编译过程中，每次遇到一个IS_CV类型的变量（类似于$something），就会去遍历vars数组，检查是否已经存在，如果不存在，则插入到vars中，并将last_var的值设置为该变量的操作数；如果存在，则使用之前分配的操作数
### 2.4.2 函数栈帧

PHP虚拟机实现了与1.3节物理机类似的函数栈帧结构；

使用 _zend_vm_stack表示栈结构；多个栈之间使用prev字段形成单向链表；top和end指向栈低和栈顶，分别为zval类型的指针；

```c
struct _zend_vm_stack {
    zval *top;
    zval *end;
    zend_vm_stack prev;
};
```

考虑如何设计函数执行时候的帧结构：当前函数执行时，需要存储函数编译后的指令，需要存储函数内部的局部变量等（2.1.2.2节指出，操作数使用结构体znode_op表示，其内部使用uint32_t表示操作数，此时表示的就是当前zval变量相对于当前函数栈帧首地址的偏移量）；

PHP虚拟机使用结构体_zend_execute_data存储当前函数执行所需数据；

```c
struct _zend_execute_data {
    //当前指令指令
    const zend_op       *opline; 
    //当前函数执行栈帧
    zend_execute_data   *call; 
    //函数返回数据          
    zval                *return_value;
    zend_function       *func;            
    zval                 This;      /* this + call_info + num_args */
    //调用当前函数的栈帧       
    zend_execute_data   *prev_execute_data;
    //符号表
    zend_array          *symbol_table;
#if ZEND_EX_USE_RUN_TIME_CACHE
    void               **run_time_cache;  
#endif
#if ZEND_EX_USE_LITERALS
    //常量数组
    zval                *literals;        
#endif
};
```

函数开始执行时，需要为函数分配相应的函数栈帧并入栈，代码如下：

```c
static zend_always_inline zend_execute_data *zend_vm_stack_push_call_frame(uint32_t call_info, zend_function *func, uint32_t num_args, zend_class_entry *called_scope, zend_object *object)
{
    //计算当前函数栈帧需要内存空间大小
    uint32_t used_stack = zend_vm_calc_used_stack(num_args, func);
 
    //根据栈帧大小分配空间，入栈
    return zend_vm_stack_push_call_frame_ex(used_stack, call_info,
        func, num_args, called_scope, object);
}
 
//计算函数栈帧大小
static zend_always_inline uint32_t zend_vm_calc_used_stack(uint32_t num_args, zend_function *func)
{
    //_zend_execute_data大小（80字节/16字节=5）+参数数目
    uint32_t used_stack = ZEND_CALL_FRAME_SLOT + num_args;
 
    if (EXPECTED(ZEND_USER_CODE(func->type))) {
        //当前函数临时变量等数目
        used_stack += func->op_array.last_var + func->op_array.T - MIN(func->op_array.num_args, num_args);
    }
 
    //乘以16字节
    return used_stack * sizeof(zval);
}
 
//入栈
static zend_always_inline zend_execute_data *zend_vm_stack_push_call_frame_ex(uint32_t used_stack, uint32_t call_info, zend_function *func, uint32_t num_args, zend_class_entry *called_scope, zend_object *object)
{
    //上一个函数栈帧地址
    zend_execute_data *call = (zend_execute_data*)EG(vm_stack_top);
 
    //移动函数调用栈top指针
    EG(vm_stack_top) = (zval*)((char*)call + used_stack);
    //初始化当前函数栈帧
    zend_vm_init_call_frame(call, call_info, func, num_args, called_scope, object);
    //返回当前函数栈帧首地址
    return call;
}
```

从上面分析可以得到函数栈帧结构图如下所示：

![][3]
## 总结


* PHP虚拟机也是计算机，有三点是我们需要重点关注的：指令集（包含指令处理函数）、数据存储（zval）、函数栈帧；
* 此时虚拟机已可以接受指令并执行指令代码；
* 但是，PHP虚拟机是专用执行PHP代码的，PHP代码如何能转换为PHP虚拟机可以识别的指令呢——编译；
* PHP虚拟机同时提供了编译器，可以将PHP代码转换为其可以识别的指令集合；
* 理论上你可以自定义任何语言，只要实现编译器，能够将你自己的语言转换为PHP可以识别的指令代码，就能被PHP虚拟机执行；


[0]: ./img/bVbe0fq.png
[1]: ./img/bVbe0g1.png
[2]: ./img/bVbe0hw.png
[3]: ./img/bVbe0hO.png