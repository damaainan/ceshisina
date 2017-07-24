# 认识PHP 7虚拟机 

 04 November 2016

本文内容大部分翻译自[Getting into the Zend Execution engine (PHP 5)][0]，并做了一些调整，原文基于PHP 5，本文基于PHP 7。

#### PHP : 一门解释型语言

- - -

PHP被称为脚本语言或解释型语言。为何？ PHP语言没有被直接编译为机器指令，而是编译为一种中间代码的形式，很显然它无法直接在CPU上执行。 所以PHP的执行需要在进程级虚拟机上（见[Virtual machine][1]中的Process virtual machines，下文简称虚拟机）。

PHP语言，包括其他的解释型语言，其实是一个跨平台的被设计用来执行抽象指令的程序。PHP主要用于解决WEB开发相关的问题。

诸如Java, Python, C#, Ruby, Pascal, Lua, Perl, Javascript等编程语言所编写的程序，都需要在虚拟机上执行。虚拟机可以通过JIT编译技术将一部分虚拟机指令编译为机器指令以提高性能。鸟哥已经在进行PHP加入JIT支持的开发了。

使用解释型语言的优点：

* 代码编写简单，能够快速开发
* 自动的内存管理
* 抽象的数据类型，程序可移植性高

缺点：

* 无法直接地进行内存管理和使用进程资源
* 比编译为机器指令的语言速度慢：通常需要更多的CPU周期来完成相同的任务（JIT试图缩小差距，但永远不能完全消除）
* 抽象了太多东西，以至于当程序出问题时，许多程序员难以解释其根本原因

最后一条缺点是作者之所以写这篇文章的原因，作者觉得程序员应该去了解一些底层的东西。

作者希望能够通过这篇文章向读者讲明白PHP是如何运行的。本文所提到的关于PHP虚拟机的知识同样可以应用于其他解释型语言。通常，不同虚拟机实现上的最大不同点在于：是否使用JIT、并行的虚拟机指令（一般使用多线程实现，PHP没有使用这一技术）、内存管理/垃圾回收算法。

Zend虚拟机分为两大部分：

* 编译：将PHP代码转换为虚拟机指令（OPCode）
* 执行：执行生成的虚拟机指令

本文不会涉及到编译部分，主要关注Zend虚拟机的执行引擎。PHP7版本的执行引擎做了一部分重构，使得PHP代码的执行堆栈更加简单清晰，性能也得到了一些提升。

本文以PHP 7.0.7为示例。

#### OPCode

- - -

维基百科对于OPCode的解释：

> Opcodes can also be found in so-called byte codes and other representations intended for a software interpreter rather than a hardware device. These software based instruction sets often employ slightly higher-level data types and operations than most hardware counterparts, but are nevertheless constructed along similar lines.

OPCode与ByteCode在概念上是不同的。

我的个人理解：OPCode作为一条指令，表明要怎么做，而ByteCode由一序列的OPCode/数据组成，表明要做什么。以一个加法为例子，OPCode是告诉执行引擎将参数1和参数2相加，而ByteCode则告诉执行引擎将45和56相加。

参考：[Difference between Opcode and Bytecode][2]和[Difference between: Opcode, byte code, mnemonics, machine code and assembly][3]

在PHP中，Zend/zend_vm_opcodes.h源码文件列出了所有支持的OPCode。通常，每个OPCode的名字都描述了其含义，比如：

* ZEND_ADD：对两个操作数执行加法操作
* ZEND_NEW：创建一个对象
* ZEND_FETCH_DIM_R：读取操作数中某个维度的值，比如执行echo $foo[0]语句时，需要获取$foo数组索引为0的值

OPCode以zend_op结构体表示：

```c
    struct _zend_op {
        const void *handler; /* 执行该OPCode的C函数 */
        znode_op op1; /* 操作数1 */
        znode_op op2; /* 操作数2 */
        znode_op result; /* 结果 */
        uint32_t extended_value; /* 额外的信息 */
        uint32_t lineno; /* 该OPCode对应PHP源码所在的行 */
        zend_uchar opcode; /* OPCode对应的数值 */
        zend_uchar op1_type; /* 操作数1类型 */
        zend_uchar op2_type; /* 操作数2类型 */
        zend_uchar result_type; /* 结果类型 */
    };
```
每一条OPcode都以相同的方式执行：OPCode有其对应的C函数，执行该C函数时，可能会用到0、1或2个操作数（op1，op2），最后将结果存储在result中，可能还会有一些额外的信息存储在extended_value。

看下ZEND_ADD的OPCode长什么样子，在Zend/zend_vm_def.h源码文件中：

```c
    ZEND_VM_HANDLER(1, ZEND_ADD, CONST|TMPVAR|CV, CONST|TMPVAR|CV)                                                                                      
    {
        USE_OPLINE
        zend_free_op free_op1, free_op2;
        zval *op1, *op2, *result;
    
        op1 = GET_OP1_ZVAL_PTR_UNDEF(BP_VAR_R);
        op2 = GET_OP2_ZVAL_PTR_UNDEF(BP_VAR_R);
        if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_LONG)) {
            if (EXPECTED(Z_TYPE_INFO_P(op2) == IS_LONG)) {
                result = EX_VAR(opline->result.var);
                fast_long_add_function(result, op1, op2);
                ZEND_VM_NEXT_OPCODE();
            } else if (EXPECTED(Z_TYPE_INFO_P(op2) == IS_DOUBLE)) {
                result = EX_VAR(opline->result.var);
                ZVAL_DOUBLE(result, ((double)Z_LVAL_P(op1)) + Z_DVAL_P(op2));
                ZEND_VM_NEXT_OPCODE();
            }    
        } else if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_DOUBLE)) {
            if (EXPECTED(Z_TYPE_INFO_P(op2) == IS_DOUBLE)) {
                result = EX_VAR(opline->result.var);
                ZVAL_DOUBLE(result, Z_DVAL_P(op1) + Z_DVAL_P(op2));
                ZEND_VM_NEXT_OPCODE();
            } else if (EXPECTED(Z_TYPE_INFO_P(op2) == IS_LONG)) {
                result = EX_VAR(opline->result.var);
                ZVAL_DOUBLE(result, Z_DVAL_P(op1) + ((double)Z_LVAL_P(op2)));
                ZEND_VM_NEXT_OPCODE();
            }    
        }
    
        SAVE_OPLINE();
        if (OP1_TYPE == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op1) == IS_UNDEF)) {
            op1 = GET_OP1_UNDEF_CV(op1, BP_VAR_R);
        }
        if (OP2_TYPE == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op2) == IS_UNDEF)) {
            op2 = GET_OP2_UNDEF_CV(op2, BP_VAR_R);
        }
        add_function(EX_VAR(opline->result.var), op1, op2);
        FREE_OP1();
        FREE_OP2();
        ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();
    }
```

可以看出这其实不是一个合法的C代码，可以把它看成代码模板。稍微解读下这个代码模板：1 就是在Zend/zend_vm_opcodes.h中define定义的ZEND_ADD的值；ZEND_ADD接收两个操作数，如果两个操作数都为IS_LONG类型，那么就调用fast_long_add_function（该函数内部使用汇编实现加法操作）；如果两个操作数，都为IS_DOUBLE类型或者1个是IS_DOUBLE类型，另1个是IS_LONG类型，那么就直接执行double的加法操作；如果存在1个操作数不是IS_LONG或IS_DOUBLE类型，那么就调用add_function（比如两个数组做加法操作）；最后检查是否有异常接着执行下一条OPCode。

在Zend/zend_vm_def.h源码文件中的内容其实是OPCode的代码模板，在该源文件的开头处可以看到这样一段注释：

    /* If you change this file, please regenerate the zend_vm_execute.h and
     * zend_vm_opcodes.h files by running:
     * php zend_vm_gen.php
     */

说明zend_vm_execute.h和zend_vm_opcodes.h，实际上包括zend_vm_opcodes.c中的C代码正是从Zend/zend_vm_def.h的代码模板生成的。

#### 操作数类型

- - -

每个OPCode最多使用两个操作数：op1和op2。每个操作数代表着OPCode的“形参”。例如ZEND_ASSIGN OPCode将op2的值赋值给op1代表的PHP变量，而其result则没有使用到。

操作数的类型（与PHP变量的类型不同）决定了其含义以及使用方式：

* IS_CV：Compiled Variable，说明该操作数是一个PHP变量
* IS_TMP_VAR ：虚拟机使用的临时内部PHP变量，不能够在不同OPCode中复用（复用的这一点我并不清楚，还没去研究过）
* IS_VAR：虚拟机使用的内部PHP变量，能够在不同OPCode中复用（复用的这一点我并不清楚，还没去研究过）
* IS_CONST：代表一个常量值
* IS_UNUSED：该操作数没有任何意义，忽略该操作数

操作数的类型对性能优化和内存管理很重要。当一个OPCode的Handler需要读写操作数时，会根据操作数的类型通过不同的方式读写。

以加法例子，说明操作数类型：

    $a + $b;  // IS_CV + IS_CV
    1 + $a;   // IS_CONST + IS_CV
    $$b + 3   // IS_VAR + IS_CONST
    !$a + 3;  // IS_TMP_VAR + IS_CONST
    

#### OPCode Handler

- - -

我们已经知道每个OPCode Handler最多接收2个操作数，并且会根据操作数的类型读写操作数的值。如果在Handler中，通过switch判断类型，然后再读写操作数的值，那么对性能会有很大损耗，因为存在太多的分支判断了（[Why is it good to avoid instruction branching where possible?][4]），如下面的伪代码所示：

    int ZEND_ADD(zend_op *op1, zend_op *op2)
    {
        void *op1_value;
        void *op2_value;
    
        switch (op1->type) {
            case IS_CV:
                op1_value = read_op_as_a_cv(op1);
            break;
            case IS_VAR:
                op1_value = read_op_as_a_var(op1);
            break;
            case IS_CONST:
                op1_value = read_op_as_a_const(op1);
            break;
            case IS_TMP_VAR:
                op1_value = read_op_as_a_tmp(op1);
            break;
            case IS_UNUSED:
                op1_value = NULL;
            break;
        }
        /* ... same thing to do for op2 .../
    
        /* do something with op1_value and op2_value (perform a math addition ?) */
    }

要知道OPCode Handler在PHP执行过程中是会被调用成千上万次的，所以在Handler中对op1、op2做类型判断，对性能并不好。

重新看下ZEND_ADD的代码模板：

    ZEND_VM_HANDLER(1, ZEND_ADD, CONST|TMPVAR|CV, CONST|TMPVAR|CV)

这说明ZEND_ADD接收op1和op2为CONST或TMPVAR或CV类型的操作数。

前面已经提到zend_vm_execute.h和zend_vm_opcodes.h中的C代码是从Zend/zend_vm_def.h的代码模板生成的。通过查看zend_vm_execute.h，可以看到每个OPCode对应的Handler（C函数），大部分OPCode会对应多个Handler。以ZEND_ADD为例：

    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CONST_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CONST_CV_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CONST_TMPVAR_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CV_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CV_CV_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CV_TMPVAR_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_TMPVAR_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_TMPVAR_CV_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_TMPVAR_TMPVAR_HANDLER(ZEND_OPCODE_HANDLER_ARGS)

ZEND_ADD的op1和op2的类型都有3种，所以一共生成了9个Handler，每个Handler的命名规范：ZEND_{OPCODE-NAME}_SPEC_{OP1-TYPE}_{OP2-TYPE}_HANDLER()。在编译阶段，操作数的类型是已知的，也就确定了每个编译出来的OPCode对应的Handler了。

那么这些Handler之间有什么不同呢？最大的不同应该就是获取操作数的方式：

```c
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CONST_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        USE_OPLINE
    
        zval *op1, *op2, *result;
    
        op1 = EX_CONSTANT(opline->op1);
        op2 = EX_CONSTANT(opline->op2);
        if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_LONG)) {
           /* 省略 */
        } else if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_DOUBLE)) {
            /* 省略 */
        }
    
        SAVE_OPLINE();
        if (IS_CONST == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op1) == IS_UNDEF)) { //<-------- 这部分代码会被编译器优化掉
            op1 = GET_OP1_UNDEF_CV(op1, BP_VAR_R);
        }
        if (IS_CONST == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op2) == IS_UNDEF)) { //<-------- 这部分代码会被编译器优化掉
            op2 = GET_OP2_UNDEF_CV(op2, BP_VAR_R);
        }
        add_function(EX_VAR(opline->result.var), op1, op2);
    
    
        ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();
    }
    
    
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CONST_CV_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        USE_OPLINE
    
        zval *op1, *op2, *result;
    
        op1 = EX_CONSTANT(opline->op1);
        op2 = _get_zval_ptr_cv_undef(execute_data, opline->op2.var);    //<-------- op2的获取方式与上面的CONST不同
        if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_LONG)) {
            /* 省略 */
        } else if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_DOUBLE)) {
            /* 省略 */
        }
    
        SAVE_OPLINE();
        if (IS_CONST == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op1) == IS_UNDEF)) { //<-------- 这部分代码会被编译器优化掉
            op1 = GET_OP1_UNDEF_CV(op1, BP_VAR_R);
        }
        if (IS_CV == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op2) == IS_UNDEF)) { //<-------- IS_CV == IS_CV && 也会被编译器优化掉
            op2 = GET_OP2_UNDEF_CV(op2, BP_VAR_R);
        }
        add_function(EX_VAR(opline->result.var), op1, op2);
    
        ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();
    }
```
#### OPArray

- - -

OPArray是指一个包含许多要被顺序执行的OPCode的数组，如下图：

![][5]

OPArray由结构体_zend_op_array表示：

    struct _zend_op_array {
        /* Common elements */
        /* 省略 */
        /* END of common elements */
    
        /* 省略 */
        zend_op *opcodes; //<------ 存储着OPCode的数组
        /* 省略 */
    };

在PHP中，每个PHP用户函数或者PHP脚本、传递给eval()的参数，会被编译为一个OPArray。

OPArray中包含了许多静态的信息，能够帮助执行引擎更高效地执行PHP代码。部分重要的信息如下：

* 当前脚本的文件名，OPArray对应的PHP代码在脚本中起始和终止的行号
* /**的代码注释信息
* refcount引用计数，OPArray是可共享的
* try-catch-finally的跳转信息
* break-continue的跳转信息
* 当前作用域所有PHP变量的名称
* 函数中用到的静态变量
* literals（字面量），编译阶段已知的值，例如字符串“foo”，或者整数42
* 运行时缓存槽，引擎会缓存一些后续执行需要用到的东西

一个简单的例子：

    $a = 8;
    $b = 'foo';
    echo $a + $b;
    

OPArray中的部分成员其内容如下：

![][6]

OPArray包含的信息越多，即在编译期间尽量的将已知的信息计算好存储到OPArray中，执行引擎就能够更高效地执行。我们可以看到每个字面量都已经被编译为zval并存储到literals数组中（你可能发现这里多了一个整型值1，其实这是用于ZEND_RETURN OPCode的，PHP文件的OPArray默认会返回1，但函数的OPArray默认返回null）。OPArray所使用到的PHP变量的名字信息也被编译为zend_string存储到vars数组中，编译后的OPCode则存储到opcodes数组中。

#### OPCode的执行

- - -

OPCode的执行是通过一个while循环去做的：

```c
    //删除了预处理语句
    ZEND_API void execute_ex(zend_execute_data *ex)
    {
        DCL_OPLINE
    
        const zend_op *orig_opline = opline;
        zend_execute_data *orig_execute_data = execute_data;
        execute_data = ex; 
    
    
        LOAD_OPLINE();
    
        while (1) {
            ((opcode_handler_t)OPLINE->handler)(ZEND_OPCODE_HANDLER_ARGS_PASSTHRU); //执行OPCode对应的C函数
            if (UNEXPECTED(!OPLINE)) { //当前OPArray执行完
                execute_data = orig_execute_data;
                opline = orig_opline;
                return;
            }
        }
        zend_error_noreturn(E_CORE_ERROR, "Arrived at end of main loop which shouldn't happen");
    }
```

那么是如何切换到下一个OPCode去执行的呢？每个OPCode的Handler中都会调用到一个宏：

```c
    #define ZEND_VM_NEXT_OPCODE_EX(check_exception, skip) \
        CHECK_SYMBOL_TABLES() \
        if (check_exception) { \
            OPLINE = EX(opline) + (skip); \
        } else { \
            OPLINE = opline + (skip); \
        } \
        ZEND_VM_CONTINUE()
```

该宏会把当前的opline+skip（skip通常是1），将opline指向下一条OPCode。opline是一个全局变量，指向当前执行的OPCode。

#### 额外的一些东西

- - -

##### 编译器优化

在Zend/zend_vm_execute.h中，会看到如下奇怪的代码：

```c
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_INIT_ARRAY_SPEC_CONST_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        /* 省略 */
    
        if (IS_CONST == IS_UNUSED) {
            ZEND_VM_NEXT_OPCODE();
    #if 0 || (IS_CONST != IS_UNUSED)
        } else {
            ZEND_VM_TAIL_CALL(ZEND_ADD_ARRAY_ELEMENT_SPEC_CONST_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS_PASSTHRU));
    #endif
        }
    }
```

你可能会对if (IS_CONST == IS_UNUSED)和#if 0 || (IS_CONST != IS_UNUSED)感到奇怪。看下其对应的模板代码：
```c
    ZEND_VM_HANDLER(71, ZEND_INIT_ARRAY, CONST|TMP|VAR|UNUSED|CV, CONST|TMPVAR|UNUSED|CV)
    {
        zval *array;
        uint32_t size;
        USE_OPLINE
    
        array = EX_VAR(opline->result.var);
        if (OP1_TYPE != IS_UNUSED) {
            size = opline->extended_value >> ZEND_ARRAY_SIZE_SHIFT;
        } else {
            size = 0;
        }
        ZVAL_NEW_ARR(array);
        zend_hash_init(Z_ARRVAL_P(array), size, NULL, ZVAL_PTR_DTOR, 0);
    
        if (OP1_TYPE != IS_UNUSED) {
            /* Explicitly initialize array as not-packed if flag is set */
            if (opline->extended_value & ZEND_ARRAY_NOT_PACKED) {
                zend_hash_real_init(Z_ARRVAL_P(array), 0);
            }
        }
    
        if (OP1_TYPE == IS_UNUSED) {
            ZEND_VM_NEXT_OPCODE();
    #if !defined(ZEND_VM_SPEC) || (OP1_TYPE != IS_UNUSED)
        } else {
            ZEND_VM_DISPATCH_TO_HANDLER(ZEND_ADD_ARRAY_ELEMENT);
    #endif
        }
    }
```

php zend_vm_gen.php在生成zend_vm_execute.h时，会把OP1_TYPE替换为op1的类型，从而生成这样子的代码：if (IS_CONST == IS_UNUSED)，但C编译器会把这些代码优化掉。

##### 自定义Zend执行引擎的生成

zend_vm_gen.php支持传入参数--without-specializer，当使用该参数时，每个OPCode只会生成一个与之对应的Handler，该Handler中会对操作数做类型判断，然后再对操作数进行读写。

另一个参数是--with-vm-kind=CALL|SWITCH|GOTO，CALL是默认参数。

前面已提到执行引擎是通过一个while循环执行OPCode，每个OPCode中将opline增加1（通常情况下），然后回到while循环中，继续执行下一个OPCode，直到遇到ZEND_RETURN。

如果使用GOTO执行策略：
```c
    /* GOTO策略下，execute_ex是一个超大的函数 */
    ZEND_API void execute_ex(zend_execute_data *ex)
    {
        /* 省略 */
    
        while (1) {
            /* 省略 */
            goto *(void**)(OPLINE->handler);
            /* 省略 */
        }
    
        /* 省略 */
    }
```
这里的goto并没有直接使用符号名，其实是goto一个特殊的用法：[Labels as Values][7]。

##### 执行引擎中的跳转

当PHP脚本中出现if语句时，是如何跳转到相应的OPCode然后继续执行的？看下面简单的例子：

    $a = 8;
    if ($a == 9) {
        echo "foo";
    } else {
        echo "bar";
    }
    
    number of ops:  7
    compiled vars:  !0 = $a
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   ASSIGN                                                   !0, 8
       3     1        IS_EQUAL                                         ~2      !0, 9
             2      > JMPZ                                                     ~2, ->5
       4     3    >   ECHO                                                     'foo'
             4      > JMP                                                      ->6
       6     5    >   ECHO                                                     'bar'
             6    > > RETURN                                                   1
    

当$a != 9时，JMPZ会使当前执行跳转到第5个OPCode，否则JMP会使当前执行跳转到第6个OPCode。其实就是对当前的opline赋值为跳转目标OPCode的地址。

#### 一些性能Tips

- - -

这部分内容将展示如何通过查看生成的OPCode优化PHP代码。

##### echo a concatenation

示例代码：

    $foo = 'foo';
    $bar = 'bar';
    
    echo $foo . $bar;
    

OPArray：

    number of ops:  5
    compiled vars:  !0 = $foo, !1 = $bar
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   ASSIGN                                                   !0, 'foo'
       3     1        ASSIGN                                                   !1, 'bar'
       5     2        CONCAT                                           ~4      !0, !1
             3        ECHO                                                     ~4
             4      > RETURN                                                   1
    

$a和$b的值会被ZEND_CONCAT连接后存储到一个临时变量~4中，然后再echo输出。

CONCAT操作需要分配一块临时的内存，然后做内存拷贝，echo输出后，又要回收这块临时内存。如果把代码改为如下可消除CONCAT：

    $foo = 'foo';
    $bar = 'bar';
    
    echo $foo , $bar;
    

OPArray：

    number of ops:  5
    compiled vars:  !0 = $foo, !1 = $bar
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   ASSIGN                                                   !0, 'foo'
       3     1        ASSIGN                                                   !1, 'bar'
       5     2        ECHO                                                     !0
             3        ECHO                                                     !1
             4      > RETURN                                                   1
    

##### define()和const

PHP 5.3引入了const关键字。

简单地说：

* define()是一个函数调用
* conast是关键字，不会产生函数调用，要比define()轻量许多

qqq

    define('FOO', 'foo');
    echo FOO;
    
    number of ops:  7
    compiled vars:  none
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   INIT_FCALL                                               'define'
             1        SEND_VAL                                                 'FOO'
             2        SEND_VAL                                                 'foo'
             3        DO_ICALL                                                 
       3     4        FETCH_CONSTANT                                   ~1      'FOO'
             5        ECHO                                                     ~1
             6      > RETURN                                                   1
    

如果使用const：

    const FOO = 'foo';
    echo FOO;
    
    number of ops:  4
    compiled vars:  none
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   DECLARE_CONST                                            'FOO', 'foo'
       3     1        FETCH_CONSTANT                                   ~0      'FOO'
             2        ECHO                                                     ~0
             3      > RETURN                                                   1
    

然而const在使用上有一些限制：

* const关键字定义常量必须处于最顶端的作用区域，这就意味着不能在函数内，循环内以及if语句之内用const 来定义常量
* const的操作数必须为IS_CONST类型

##### 动态函数调用

尽量不要使用动态的函数名去调用函数：

    function foo() { }
    foo();
    
    number of ops:  4
    compiled vars:  none
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   NOP                                                      
       3     1        INIT_FCALL                                               'foo'
             2        DO_UCALL                                                 
             3      > RETURN                                                   1
    

NOP表示不做任何操作，只是将当前opline指向下一条OPCode，编译器产生这条指令是由于历史原因。为何到PHP7还不移除它呢= =

看看使用动态的函数名去调用函数：

    function foo() { }
    $a = 'foo';
    $a();
    
    number of ops:  5
    compiled vars:  !0 = $a
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   NOP                                                      
       3     1        ASSIGN                                                   !0, 'foo'
       4     2        INIT_DYNAMIC_CALL                                        !0
             3        DO_FCALL                                      0          
             4      > RETURN                                                   1
    

不同点在于INIT_FCALL和INIT_DYNAMIC_CALL，看下两个函数的源码：


```c
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_INIT_FCALL_SPEC_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        USE_OPLINE
    
        zval *fname = EX_CONSTANT(opline->op2);
        zval *func;
        zend_function *fbc;
        zend_execute_data *call;
    
        fbc = CACHED_PTR(Z_CACHE_SLOT_P(fname)); /* 看下是否已经在缓存中了 */
        if (UNEXPECTED(fbc == NULL)) {
            func = zend_hash_find(EG(function_table), Z_STR_P(fname)); /* 根据函数名查找函数 */
            if (UNEXPECTED(func == NULL)) {
                SAVE_OPLINE();
                zend_throw_error(NULL, "Call to undefined function %s()", Z_STRVAL_P(fname));
                HANDLE_EXCEPTION();
            }
            fbc = Z_FUNC_P(func);
            CACHE_PTR(Z_CACHE_SLOT_P(fname), fbc); /* 缓存查找结果 */
        }
    
        call = zend_vm_stack_push_call_frame_ex(
            opline->op1.num, ZEND_CALL_NESTED_FUNCTION,
            fbc, opline->extended_value, NULL, NULL);
        call->prev_execute_data = EX(call);
        EX(call) = call;
    
        ZEND_VM_NEXT_OPCODE();
    }
    
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_INIT_DYNAMIC_CALL_SPEC_CV_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        /* 200多行代码，就不贴出来了，会根据CV的类型（字符串、对象、数组）做不同的函数查找 */
    }
```

很显然INIT_FCALL相比INIT_DYNAMIC_CALL要轻量许多。

##### 类的延迟绑定

简单地说，类A继承类B，类B最好先于类A被定义。

    class Bar { }
    class Foo extends Bar { }
    
    number of ops:  4
    compiled vars:  none
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   NOP
       3     1        NOP
             2        NOP
             3      > RETURN                                                   1
    

从生成的OPCode可以看出，上述PHP代码在运行时，执行引擎不需要做任何操作。类的定义是比较耗性能的工作，例如解析类的继承关系，将父类的方法/属性添加进来，但编译器已经做完了这些繁重的工作。

如果类A先于类B被定义：

    class Foo extends Bar { }
    class Bar { }
    
    number of ops:  4
    compiled vars:  none
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   FETCH_CLASS                                   0  :0      'Bar'
             1        DECLARE_INHERITED_CLASS                                  '%00foo%2Fhome%2Froketyyang%2Ftest.php0x7fb192b7101f', 'foo'
       3     2        NOP
             3      > RETURN                                                   1
    

这里定义了Foo继承自Bar，但当编译器读取到Foo的定义时，编译器并不知道任何关于Bar的情况，所以编译器就生成相应的OPCode，使其定义延迟到执行时。在一些其他的动态类型的语言中，可能会产生错误：Parse error : class not found。

除了类的延迟绑定，像接口、traits都存在延迟绑定耗性能的问题。

对于定位PHP性能问题，通常都是先用xhprof或xdebug profile进行定位，需要通过查看OPCode定位性能问题的场景还是比较少的。

#### 总结

- - -

希望通过这篇文章，能让你了解到PHP虚拟机大致是如何工作的。具体opcode的执行，以及函数调用涉及到的上下文切换，有许多细节性的东西，限于本文篇幅，在另一篇文章：[PHP 7 中函数调用的实现][8]进行讲解。

[0]: http://jpauli.github.io//2015/02/05/zend-vm-executor.html
[1]: https://en.wikipedia.org/wiki/Virtual_machine
[2]: http://www.differencebetween.info/difference-between-opcode-and-bytecode
[3]: http://stackoverflow.com/questions/17638888/difference-between-opcode-byte-code-mnemonics-machine-code-and-assembly
[4]: http://stackoverflow.com/questions/5662261/why-is-it-good-to-avoid-instruction-branching-where-possible
[5]: ./img/201611040201.png
[6]: ./img/201611040202.png
[7]: https://gcc.gnu.org/onlinedocs/gcc/Labels-as-Values.html
[8]: http://yangxikun.github.io/php/2016/11/04/php-7-func-call.html