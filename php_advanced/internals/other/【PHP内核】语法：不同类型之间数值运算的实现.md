# [【PHP内核】语法：不同类型之间数值运算的实现][0] ![][1]

 标签： [php][2][zend][3][内核][4]

 2016-03-23 14:41  830人阅读  

 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [opcode及zend_execute_data][10]
1. [弱类型运算][11]

我们都知道[PHP][12]属于弱类型的语言，不同类型之间可以直接进行运算，比如加减乘除，但是php是构建在[C语言][13]之上的，它是如何实现这种复合类型运算的呢？很显然，内核帮我们作了类型转化，下面我们就从一个简单的例子具体看下zend引擎中都干了哪些事。(文中涉及的代码均来自php-7.0.4)

    //a.php
    <?php
    $str = "6";
    $a = $str + 5;
    
    echo $a;


    $ php a.php
    $ 11


# opcode及zend_execute_data

cli下执行一个php脚本的主要的流程是：main() -> do_cli() -> php_execute_script() -> zend_execute_scripts() -> （解析、编译）compile_file() ->（执行） zend_execute()

我们直接跳过前面词法、语法分析、AST的生成过程，直接从zend_execute开始看。

    //zend_vm_execute.h #441
    ZEND_API void zend_execute(zend_op_array *op_array, zval *return_value)


这个方法比较简单，只有两个参数：编译阶段生成的opcode array、返回值指针，这个方法是vm执行的入口，所有的php脚本最终都是在这里开始执行的。opcode是zend引擎的执行指令，比如加减、赋值、调用函数等等，所有的opcode定义在zend_vm_opcodes.h中。下面是opcode指令具体的结构：

```c
    //zend_compile.h #155
    struct _zend_op {
        const void *handler; //该指令的处理函数
        znode_op op1; //操作数1，与op2用于存储具体操作的左右值，如赋值操作，在编译时将等号左右的两个值分别存于op1、op2
        znode_op op2; //操作数2，可以不用
        znode_op result; //返回值
        uint32_t extended_value;
        uint32_t lineno;
        zend_uchar opcode; //opcode编码
        zend_uchar op1_type; //操作数的类型:IS_CONST、IS_TMP_VAR、IS_VAR、IS_UNUSED、IS_CV
        zend_uchar op2_type;
        zend_uchar result_type;
    };
    //#73
    typedef union _znode_op {
        uint32_t      constant;
        uint32_t      var; //存储值在CV数组中的位置，所以是一个int，临时变量存在一个CV数组中
        uint32_t      num;
        uint32_t      opline_num; /*  Needs to be signed */
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

php脚本编译的过程就是从AST生成一个个zend_op的结构，然后将opcodes数组传给zend_execute()执行。   
执行的过程中有一个非常核心的结构：zend_execute_data，这个结构定义也定义在zend_compile.h中：

```c
    #430
    struct _zend_execute_data {
        const zend_op       *opline;           /* executed opline                */
        zend_execute_data   *call;             /* current call                   */
        zval                *return_value;
        zend_function       *func;             /* executed funcrion              */
        zval                 This;             /* this + call_info + num_args    */
        zend_class_entry    *called_scope;
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
zend_execute_data这个结构可以简单的认为是一个运行栈，它记录着执行过程中的opcode、符号表等等，最终执行的过程就是从zend_execute_data->opline开始，然后zend_execute_data->opline++执行下一条指令。

函数调用会新开辟一个zend_execute_data，接着初始化，返回ZEND_VM_ENTER进入新的execute，然后开始从新的zend_execute_data->opline开始执行函数内部的opcode，执行完再将之前的zend_execute_data指针还原，接着执行下面的操作。关于函数、类的执行机制这里不多说，后续会有专门的介绍。

下面回到文章一开始提到的那个例子。

# 弱类型运算

用gdb调试zend_execute()，根据传入的op_array得到所有的opcode：

![opcodes][14]

handler是根据opcode、op1_type、op2_type确定的，换句话说，每一个opcode都可以根据不同的操作数类型定义不同的handler，所以一个opcode最多有5x5=25个handler，在定义的时候也需要定义25个，当然定义为null，具体的对应方法见：

```c
    //zend_vm_execute.h #49741
    ZEND_API void zend_vm_set_opcode_handler(zend_op* op)
    {
        op->handler = zend_vm_get_opcode_handler(zend_user_opcodes[op->opcode], op);
    }
    //#49717
    static const void *zend_vm_get_opcode_handler(zend_uchar opcode, const zend_op* op)
    {
        .....
        return zend_opcode_handlers[opcode * 25 + zend_vm_decode[op->op1_type] * 5 + zend_vm_decode[op->op2_type]];
    }
```

opcode handler也全部定   
义在zend_vm_execute.h，从php的代码可以看出各语句对应的opcode：

    <?php
    $str = "6";     => ZEND_ASSIGN
    $a = $str + 5;  => ZEND_ADD & ZEND_ASSIGN
    
    echo $a;        => ZEND_ECHO


string + int的加法运算opcode就是ZEND_ADD，对应的handler是**ZEND_ADD_SPEC_CV_CONST_HANDLER**：

```c
    //zend_vm_execute.h #29773
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_ADD_SPEC_CV_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        USE_OPLINE
    
        zval *op1, *op2, *result;
    
        op1 = _get_zval_ptr_cv_undef(execute_data, opline->op1.var);
        op2 = EX_CONSTANT(opline->op2);
        //这里是针对long、double类型的直接处理(数值类型之间转化比较简单，本文不对这种情况讨论)
        if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_LONG)) {
            ...
        } else if (EXPECTED(Z_TYPE_INFO_P(op1) == IS_DOUBLE)) {
            ...
        }
    
        SAVE_OPLINE();
        if (IS_CV == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op1) == IS_UNDEF)) {
            op1 = GET_OP1_UNDEF_CV(op1, BP_VAR_R);
        }
        if (IS_CONST == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(op2) == IS_UNDEF)) {
            op2 = GET_OP2_UNDEF_CV(op2, BP_VAR_R);
        }
        //非数值类型将走到这里处理
        add_function(EX_VAR(opline->result.var), op1, op2);
    
        ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();
    }
```

示例中是一个string + int的操作，有非数值类型，所以会由add_function()处理：

```c
    //zend_operators.c #865
    ZEND_API int ZEND_FASTCALL add_function(zval *result, zval *op1, zval *op2)
    {
        while (1) {
            switch (TYPE_PAIR(Z_TYPE_P(op1), Z_TYPE_P(op2))) {
                ...
                defualt:
                    ...
                    ZEND_TRY_BINARY_OBJECT_OPERATION(ZEND_ADD, add_function);
                    zendi_convert_scalar_to_number(op1, op1_copy, result);
                    zendi_convert_scalar_to_number(op2, op2_copy, result);
            }
       }
    }
```

看到了吧zendi_convert_scalar_to_number()，这就是内核帮我们转化类型的地方，到这里我们应该就能明白php不同类型之间运算的实现方式了吧，再具体追下zendi_convert_scalar_to_number，这其实是个宏：

```c
    //zend_operators.c #190
    #define zendi_convert_scalar_to_number(op, holder, result)          \
        if (op==result) {                                               \
            if (Z_TYPE_P(op) != IS_LONG) {                              \
                convert_scalar_to_number(op);                   \
            }                                                           \
        } else {                                                        \
            switch (Z_TYPE_P(op)) {                                     \
                case IS_STRING:                                         \
                    {                                                   \
                        if ((Z_TYPE_INFO(holder)=is_numeric_string(Z_STRVAL_P(op), Z_STRLEN_P(op), &Z_LVAL(holder), &Z_DVAL(holder), 1)) == 0) {    \
                            ZVAL_LONG(&(holder), 0);                            \
                        }                                                       \
                        (op) = &(holder);                                       \
                        break;                                                  \
                    }                                                           \
                case IS_NULL:  
                ...
```

op2是IS_LONG，不需要处理，这里只有op1从string -> long，这个宏传了三个参数：op1，op1_copy，result，转化为long的值放到了op1_copy中，然后替换为op1，这时候add_function()下一次循环就到“case TYPE_PAIR(IS_LONG, IS_LONG)： ”处理了，从这里我们看出**内核是对变量转化后的新值进行的运算，对原变量并没有作处理**。

具体的类型转化可以看is_numeric_string()方法，这里是根据字符(+、-、.)确定是转为long还是double的，具体过程有兴趣的可以仔细看下[算法][15]：

```c
    //zend_operators.h #138
    static zend_always_inline zend_uchar is_numeric_string_ex(const char *str, size_t length, zend_long *lval, double *dval, int allow_errors, int *oflow_info)
    {
        if (*str > '9') {
            return 0;
        }
        return _is_numeric_string_ex(str, length, lval, dval, allow_errors, oflow_info);
    }
    
    static zend_always_inline zend_uchar is_numeric_string(const char *str, size_t length, zend_long *lval, double *dval, int allow_errors) {
        return is_numeric_string_ex(str, length, lval, dval, allow_errors, NULL);
    }
    
    //zend_operators.c #2753
    ZEND_API zend_uchar ZEND_FASTCALL _is_numeric_string_ex(const char *str, size_t length, zend_long *lval, double *dval, int allow_errors, int *oflow_info)
    {
        ...
    }
```
[0]: http://blog.csdn.net/pangudashu/article/details/50961686
[1]: http://static.blog.csdn.net/images/bole_recommd_logo.png
[2]: http://www.csdn.net/tag/php
[3]: http://www.csdn.net/tag/zend
[4]: http://www.csdn.net/tag/%e5%86%85%e6%a0%b8
[9]: #
[10]: #t0
[11]: #t1
[12]: http://lib.csdn.net/base/php
[13]: http://lib.csdn.net/base/c
[14]: ../img/20160323134222549.png
[15]: http://lib.csdn.net/base/datastructure