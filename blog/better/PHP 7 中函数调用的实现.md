# PHP 7 中函数调用的实现 

 04 November 2016

先看一个函数调用的OPCode：

    function foo($arg) {
        echo $arg;
    }
    $arg = "Hello World\n";
    foo($arg);
    
    filename:       /home/roketyyang/test.php
    function name:  (null)
    number of ops:  6
    compiled vars:  !0 = $arg
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   NOP                                                      
       5     1        ASSIGN                                                   !0, 'Hello+World%0A'
       6     2        INIT_FCALL                                               'foo'
             3        SEND_VAR                                                 !0
             4        DO_UCALL                                                 
             5      > RETURN                                                   1
    
    filename:       /home/roketyyang/test.php
    function name:  foo
    number of ops:  3
    compiled vars:  !0 = $arg
    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   RECV                                             !0      
       3     1        ECHO                                                     !0
       4     2      > RETURN                                                   null
    

第一个OPArray是/home/roketyyang/test.php文件的，第二个OPArray是foo函数的。

我们来看下/home/roketyyang/test.php是如何被Zend引擎执行的。

#### 堆栈与上下文

- - -

在CLI模式下，会调用zend_execute，当执行php /home/roketyyang/test.php时，op_array形参对应的实参就是/home/roketyyang/test.php文件的OPArray：

```c
    ZEND_API void zend_execute(zend_op_array *op_array, zval *return_value)
    {
        zend_execute_data *execute_data; /* 执行上下文 */
    
        if (EG(exception) != NULL) {
            return;
        }
    
        execute_data = zend_vm_stack_push_call_frame(ZEND_CALL_TOP_CODE, /* 从堆栈上分配一块内存给上下文 */
            (zend_function*)op_array, 0, zend_get_called_scope(EG(current_execute_data)), zend_get_this_object(EG(current_execute_data)));
        if (EG(current_execute_data)) {
            execute_data->symbol_table = zend_rebuild_symbol_table();
        } else {
            execute_data->symbol_table = &EG(symbol_table);
        }
        EX(prev_execute_data) = EG(current_execute_data);
        i_init_execute_data(execute_data, op_array, return_value); /* 初始化执行上下文 */
        zend_execute_ex(execute_data); /* 执行 上下文 */
        zend_vm_stack_free_call_frame(execute_data);
    }
```
zend_execute所做的事情主要就是从堆栈中创建一个上下文，初始化上下文环境，然后执行这个上下文，最后释放它。

堆栈是在虚拟机初始化的时候init_executor()调用zend_vm_stack_init()进行初始化的：
```c
    static zend_always_inline zend_vm_stack zend_vm_stack_new_page(size_t size, zend_vm_stack prev) {
        zend_vm_stack page = (zend_vm_stack)emalloc(size);
    
        page->top = ZEND_VM_STACK_ELEMETS(page);
        page->end = (zval*)((char*)page + size);
        page->prev = prev;
        return page;
    }
    
    ZEND_API void zend_vm_stack_init(void)
    {
        EG(vm_stack) = zend_vm_stack_new_page(ZEND_VM_STACK_PAGE_SIZE(0 /* main stack */), NULL); /* 向PHP的内存管理器申请内存 */
        EG(vm_stack)->top++;
        EG(vm_stack_top) = EG(vm_stack)->top;
        EG(vm_stack_end) = EG(vm_stack)->end;
    }
```
这里有一个EG宏需要了解下，其定义在Zend/zend_globals_macros.h中：

    #ifdef ZTS
    # define EG(v) ZEND_TSRMG(executor_globals_id, zend_executor_globals *, v)
    #else
    # define EG(v) (executor_globals.v)
    extern ZEND_API zend_executor_globals executor_globals;
    #endif
    

ZTS是在编译的时候开启线程安全选项的时候才有定义的，关于线程安全不进行讨论，executor_globals是一个全局变量，存储着许多信息（当前上下文、符号表、函数/类/常量表、堆栈等），EG宏就是用于访问executor_globals的某个成员。

我们看下表示堆栈的结构体，堆栈的设计跟PHP 5类似：

    struct _zend_vm_stack {
        zval *top; /* 指向堆栈的顶端 */
        zval *end; /* 指向堆栈的底端 */
        zend_vm_stack prev; /* 指向上一个堆栈，当前堆栈剩余空间不足时，会向内存管理器申请新的内存创建新的堆栈 */
    };
    
                        /* 创建堆栈 */
                                  +-----------------+<----- EG(vm_stack)
                      prev        |                 |
    +----------+<-----------------+ vm stack header |
    |          |                  |                 |
    |          |       top  +---> +-----------------+
    |          |                  |                 |
    +----------+                  |                 |
                                  |                 |
                                  |                 |
                                  |                 |
                                  |                 |
                                  |                 |
                                  |                 |
                       end  +---> +-----------------+
    
    

上下文的创建，zend_vm_stack_push_call_frame()->zend_vm_stack_push_call_frame_ex()：
```c
    static zend_always_inline zend_execute_data *zend_vm_stack_push_call_frame_ex(uint32_t used_stack, uint32_t call_info, zend_function *func, uint32_t num_args, zend_class_entry *called_scope, zend_object *object)
    {
        zend_execute_data *call = (zend_execute_data*)EG(vm_stack_top); /* 从堆栈顶端分配内存 */
    
        ZEND_ASSERT_VM_STACK_GLOBAL;
    
        if (UNEXPECTED(used_stack > (size_t)(((char*)EG(vm_stack_end)) - (char*)call))) { /* 如果堆栈剩余内存不足used_stack，则创建新的堆栈 */
            call = (zend_execute_data*)zend_vm_stack_extend(used_stack);
            ZEND_SET_CALL_INFO(call, call_info | ZEND_CALL_ALLOCATED);
        } else {
            EG(vm_stack_top) = (zval*)((char*)call + used_stack); /* 将堆栈顶指针设置为空闲内存顶端 */
            ZEND_SET_CALL_INFO(call, call_info);
        }
    
        ZEND_ASSERT_VM_STACK_GLOBAL;
    
        call->func = func;
        Z_OBJ(call->This) = object;
        ZEND_CALL_NUM_ARGS(call) = num_args;
        call->called_scope = called_scope;
        return call;
    }
```
我们看下表示上下文的结构体，与PHP 5相比更简洁了，关于PHP 5的execute_data可以看下这篇文章[PHP execute_data][0]：
```c
    struct _zend_execute_data {
        const zend_op       *opline;           /* executed opline                */
        zend_execute_data   *call;             /* current call                   */
        zval                *return_value;
        zend_function       *func;             /* executed function              */
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
在zend_execute中调用zend_vm_stack_push_call_frame时，对op_array进行了强制类型转换，转换为zend_function*类型，看下其结构体类型：
```c
    union _zend_function {
        zend_uchar type;    /* MUST be the first element of this struct! */
    
        struct {
            zend_uchar type;  /* never used */
            zend_uchar arg_flags[3]; /* bitset of arg_info.pass_by_reference */
            uint32_t fn_flags;
            zend_string *function_name;
            zend_class_entry *scope;
            union _zend_function *prototype;
            uint32_t num_args;
            uint32_t required_num_args;
            zend_arg_info *arg_info;
        } common;
    
        zend_op_array op_array;
        zend_internal_function internal_function;
    };
    
    struct _zend_op_array {                                                                                                                             
        /* Common elements */
        zend_uchar type;
        zend_uchar arg_flags[3]; /* bitset of arg_info.pass_by_reference */
        uint32_t fn_flags;
        zend_string *function_name;
        zend_class_entry *scope;
        zend_function *prototype;
        uint32_t num_args;
        uint32_t required_num_args;
        zend_arg_info *arg_info;
        /* END of common elements */
    
        uint32_t *refcount;
    
        uint32_t this_var;
    
        uint32_t last;
        zend_op *opcodes;               /* 当前OPArray包含的所有opcode */
        int last_var;                   /* 当前OPArray中有多少个PHP变量 */
        uint32_t T;                     /* 执行当前的OPArray需要用到多少个临时变量 */
        zend_string **vars;
    
        int last_brk_cont;
        int last_try_catch;
        zend_brk_cont_element *brk_cont_array;
        zend_try_catch_element *try_catch_array;
    
        /* static variables support */
        HashTable *static_variables;
    
        zend_string *filename;
        uint32_t line_start;
        uint32_t line_end;
        zend_string *doc_comment;
        uint32_t early_binding; /* the linked list of delayed declarations */
    
        int last_literal;
        zval *literals;
    
        int  cache_size;
        void **run_time_cache;
    
        void *reserved[ZEND_MAX_RESERVED_RESOURCES];
    };
    
    typedef struct _zend_internal_function {
        /* Common elements */
        zend_uchar type;
        zend_uchar arg_flags[3]; /* bitset of arg_info.pass_by_reference */
        uint32_t fn_flags;
        zend_string* function_name;
        zend_class_entry *scope;
        zend_function *prototype;
        uint32_t num_args;
        uint32_t required_num_args;
        zend_internal_arg_info *arg_info;
        /* END of common elements */
    
        void (*handler)(INTERNAL_FUNCTION_PARAMETERS);
        struct _zend_module_entry *module;
        void *reserved[ZEND_MAX_RESERVED_RESOURCES];
    } zend_internal_function;
```
可以看出，zend_function作为union类型，能够表示：

1. 非函数的OPArray，例如上文中提到的/home/roketyyang/test.php文件的OPArray
1. 函数的OPArray，例如上文中提到的foo函数
1. PHP扩展中的函数

在堆栈中创建上下文如下：

                        /* 创建上下文 */
                                  +-----------------+<----+ EG(vm_stack)
                      prev        |                 |
    +----------+<-----------------+ vm stack header |
    |          |                  |                 |
    |          |                  +-----------------+
    |          |                  |  execute_data   |
    +----------+                  |                 |
                                  +-----------------+
                                  |       CV        |
                                  +-----------------+
                                  |     TMP_VAR     |
                       top  +---> +-----------------+
                                  |                 |
                       end  +---> +-----------------+
    
    

除了分配execute_data的存储空间外，还分配了CV（compiled variable，即PHP变量）、TMP_VAR（临时变量，例如执行if (!$a) echo 'a';，就需要一个临时变量来存储!$a的结果）的存储空间。

从i_init_execute_data中看下execute_data的初始化，其中EX宏是用于访问execute_data的成员：
```c
    static zend_always_inline void i_init_execute_data(zend_execute_data *execute_data, zend_op_array *op_array, zval *return_value)
    {
        ZEND_ASSERT(EX(func) == (zend_function*)op_array);
    
        EX(opline) = op_array->opcodes; /* execute_data->opline 指向了要执行的第一个opcode */
        EX(call) = NULL;
        EX(return_value) = return_value;
    
        /* 对符号表、CV、函数参数进行处理，这部分展开来的话内容较多，可以先不了解 */
    
        if (!op_array->run_time_cache) {
            if (op_array->function_name) {
                op_array->run_time_cache = zend_arena_alloc(&CG(arena), op_array->cache_size);
            } else {
                op_array->run_time_cache = emalloc(op_array->cache_size);
            }
            memset(op_array->run_time_cache, 0, op_array->cache_size);
        }
        EX_LOAD_RUN_TIME_CACHE(op_array); /* EX(run_time_cache) = (op_array)->run_time_cache */
        EX_LOAD_LITERALS(op_array); /* EX(literals) = (op_array)->literals */
    
        EG(current_execute_data) = execute_data;
        ZEND_VM_INTERRUPT_CHECK();
    }
```
初始化完execute_data后，就调用execute_ex执行opcode了：
```c
    //删除了预处理语句
    ZEND_API void execute_ex(zend_execute_data *ex)
    {
        DCL_OPLINE
    
        const zend_op *orig_opline = opline;
        zend_execute_data *orig_execute_data = execute_data; /* execute_data是一个全局变量 */
        execute_data = ex; 
    
    
        LOAD_OPLINE();
    
        while (1) {
            ((opcode_handler_t)OPLINE->handler)(ZEND_OPCODE_HANDLER_ARGS_PASSTHRU); //执行OPCode对应的C函数，OPLine是一个全局变量
            if (UNEXPECTED(!OPLINE)) { //当前OPArray执行完
                execute_data = orig_execute_data;
                opline = orig_opline;
                return;
            }
        }
        zend_error_noreturn(E_CORE_ERROR, "Arrived at end of main loop which shouldn't happen");
    }
```
以文章开头的例子看看OPCode执行时上下文的变化情况和函数调用是怎么实现的：

在初始化完execute_data时：

![][1]

跳过NOP、ASSIGN，直接看INIT_CALL：
```c
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_INIT_FCALL_SPEC_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        USE_OPLINE
    
        zval *fname = EX_CONSTANT(opline->op2); /* 函数名 */
        zval *func;
        zend_function *fbc; 
        zend_execute_data *call;
    
        fbc = CACHED_PTR(Z_CACHE_SLOT_P(fname)); /* 查看EX(run_time_cache)是否已缓存了函数引用 */
        if (UNEXPECTED(fbc == NULL)) {
            func = zend_hash_find(EG(function_table), Z_STR_P(fname)); /* 尚未缓存，在函数表中查找 */
            if (UNEXPECTED(func == NULL)) {
                SAVE_OPLINE();
                zend_throw_error(NULL, "Call to undefined function %s()", Z_STRVAL_P(fname)); /* 函数表中没找到 */
                HANDLE_EXCEPTION();
            }     
            fbc = Z_FUNC_P(func);
            CACHE_PTR(Z_CACHE_SLOT_P(fname), fbc); /* 函数表中找到了，进行缓存 */
        }
    
        call = zend_vm_stack_push_call_frame_ex(        /* 创建新的execute_data */
            opline->op1.num, ZEND_CALL_NESTED_FUNCTION,
            fbc, opline->extended_value, NULL, NULL);
        call->prev_execute_data = EX(call);
        EX(call) = call;
    
        ZEND_VM_NEXT_OPCODE();
```
此时execute_data情况：

![][2]

跳过SEND_VAR（传参），看DO_UCALL：
```c
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_DO_UCALL_SPEC_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {
        USE_OPLINE
        zend_execute_data *call = EX(call); /* 被调用的函数 */
        zend_function *fbc = call->func; /* 被调用函数的OPArray */
        zval *ret;
    
        SAVE_OPLINE();
        EX(call) = call->prev_execute_data;                                                                                                
    
        EG(scope) = NULL;
        ret = NULL;
        call->symbol_table = NULL;
        if (RETURN_VALUE_USED(opline)) { /* 检查返回值是否有用到 */
            ret = EX_VAR(opline->result.var);
            ZVAL_NULL(ret);
            Z_VAR_FLAGS_P(ret) = 0;
        }
    
        call->prev_execute_data = execute_data;
        i_init_func_execute_data(call, &fbc->op_array, ret, 0); /* 初始化被调用函数的上下文 */
    
        ZEND_VM_ENTER(); /* execute_data = EG(current_execute_data); opline = EX(opline); return; */
    }
```
此时execute_data情况：

![][3]

echo输出后，进入return：
```c
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL zend_leave_helper_SPEC(ZEND_OPCODE_HANDLER_ARGS)
    {
        zend_execute_data *old_execute_data;
        uint32_t call_info = EX_CALL_INFO();
    
        if (EXPECTED(ZEND_CALL_KIND_EX(call_info) == ZEND_CALL_NESTED_FUNCTION)) {
            zend_object *object;
    
            i_free_compiled_variables(execute_data); /* 释放OPArray中的PHP变量 */
            /* 省略 */
            old_execute_data = execute_data;
            execute_data = EG(current_execute_data) = EX(prev_execute_data); /* 将当前的execute_data设置为上一个 */
            
            /* 省略 */
    
            zend_vm_stack_free_call_frame_ex(call_info, old_execute_data); /* 释放在堆栈上分配的空间 */
    
            /* 省略 */
    
            LOAD_NEXT_OPLINE(); /* opline = EX(opline) + 1 */
            ZEND_VM_LEAVE(); /* return */
        }
        /* 省略 */
    }
```
此时execute_data的情况：

![][4]

#### 总结

- - -

希望通过本文，读者能够大概地了解到PHP OPCode执行时，当发生函数调用时的上下文切换情况，对于其他的许多细节问题，比如符号表/临时变量等，尚未研究过。

[0]: http://km.oa.com/articles/show/272362?kmref=kb_categories
[1]: ./img/201611030101.png
[2]: ./img/201611030102.png
[3]: ./img/201611030103.png
[4]: ./img/201611030104.png