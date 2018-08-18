## PHP条件函数和函数中的函数

来源：[https://fengyoulin.com/2018/03/20/conditional_functions_and_functions_within_functions/](https://fengyoulin.com/2018/03/20/conditional_functions_and_functions_within_functions/)

时间 2018-03-20 11:14:29


在PHP用户手册中【语言参考】->【函数】->【用户定义的函数】一节，给出了“条件函数”和“函数中函数”的示例。本文中我们联合OPCODES查看和PHP源码的调试分析，来探索一下这两种示例在Zend引擎中的具体实现。

首先，我们准备如下的PHP脚本代码，然后使用安装了    [zendump][0]
扩展的PHP7.2.2 CLI来运行：

```php
<?php
$condition = false;
if($condition) {
	function func01() {
		echo 'Hello, ';
	}
}
function func02() {
	function func03() {
		echo 'world!' . PHP_EOL;
	}
}
if($condition) {
	func01();
	func02();
	func03();
}
zendump_opcodes();
zendump_function('func02');
```

得到的输出结果如下：

``` 
op_array("") refcount(1) addr(0x7f5377683300) vars(1) T(6) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/functions/conditional_functions.php) line(1,20)
OPCODE                             OP1                                OP2                                RESULT                             EXTENDED                           
ZEND_ASSIGN                        $condition                         false                                                                                                    
ZEND_JMPZ                          $condition                         1                                                                                                        
ZEND_DECLARE_FUNCTION              "func01"                                                                                                                                    
ZEND_NOP                                                                                                                                                                       
ZEND_JMPZ                          $condition                         6                                                                                                        
ZEND_INIT_FCALL_BY_NAME                                               "func01"                                                              0                                  
ZEND_DO_FCALL_BY_NAME                                                                                                                                                          
ZEND_INIT_FCALL                    80                                 "func02"                                                              0                                  
ZEND_DO_UCALL                                                                                                                                                                  
ZEND_INIT_FCALL_BY_NAME                                               "func03"                                                              0                                  
ZEND_DO_FCALL_BY_NAME                                                                                                                                                          
ZEND_INIT_FCALL                    80                                 "zendump_opcodes"                                                     0                                  
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_INIT_FCALL                    96                                 "zendump_function"                                                    1                                  
ZEND_SEND_VAL                      "func02"                           1                                                                                                        
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_RETURN                        1                                                                                                                                           
op_array("func02") func02() refcount(1) addr(0x7f537760d2b8) vars(0) T(0) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/functions/conditional_functions.php) line(8,12)
OPCODE                             OP1                                OP2                                RESULT                             EXTENDED                           
ZEND_DECLARE_FUNCTION              "func03"                                                                                                                                    
ZEND_RETURN                        null
```

通过分析以上OPCODES可以发现，跟我们本次探索有关的核心指令为ZEND_DECLARE_FUNCTION，该指令接受一个左操作数，看上去应该是函数的名字，我们到PHP的源码中去找一下该指令的代码，在PHP7.2.2源码中zend_vm_def.h第6836行：

```c
ZEND_VM_HANDLER(141, ZEND_DECLARE_FUNCTION, ANY, ANY)
{
	USE_OPLINE

	SAVE_OPLINE();
	do_bind_function(&EX(func)->op_array, opline, EG(function_table), 0);
	ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();
}
```

此段代码只是将当前正在执行的op_array、opline和executor_globals.function_table还有0作为参数调用了do_bind_function函数，其实现在zend_compile.c文件的第1076行：

```c
ZEND_API int do_bind_function(const zend_op_array *op_array, const zend_op *opline, HashTable *function_table, zend_bool compile_time) /* {{{ */
{
	zend_function *function, *new_function;
	zval *lcname, *rtd_key;

	if (compile_time) {
		lcname = CT_CONSTANT_EX(op_array, opline->op1.constant);
		rtd_key = lcname + 1;
	} else {
		lcname = RT_CONSTANT(op_array, opline->op1);
		rtd_key = lcname + 1;
	}

	function = zend_hash_find_ptr(function_table, Z_STR_P(rtd_key));
	new_function = zend_arena_alloc(&CG(arena), sizeof(zend_op_array));
	memcpy(new_function, function, sizeof(zend_op_array));
	if (zend_hash_add_ptr(function_table, Z_STR_P(lcname), new_function) == NULL) {
		int error_level = compile_time ? E_COMPILE_ERROR : E_ERROR;
		zend_function *old_function;

		if ((old_function = zend_hash_find_ptr(function_table, Z_STR_P(lcname))) != NULL
			&& old_function->type == ZEND_USER_FUNCTION
			&& old_function->op_array.last > 0) {
			zend_error_noreturn(error_level, "Cannot redeclare %s() (previously declared in %s:%d)",
						ZSTR_VAL(function->common.function_name),
						ZSTR_VAL(old_function->op_array.filename),
						old_function->op_array.opcodes[0].lineno);
		} else {
			zend_error_noreturn(error_level, "Cannot redeclare %s()", ZSTR_VAL(function->common.function_name));
		}
		return FAILURE;
	} else {
		if (function->op_array.refcount) {
			(*function->op_array.refcount)++;
		}
		function->op_array.static_variables = NULL; /* NULL out the unbound function */
		return SUCCESS;
	}
}
```

分析上述函数的实现：



* 首先通过最后一个参数为0，表示这是运行阶段而非编译阶段，然后使用合适的方式取得lcname，此时的lcname为zval指针类型，通过指针运算加一的方式得到rtd_key，说明二者在内存中是连续存放的。关于rtd_key，我认为应该是Runtime Declare Key的缩写形式。
* 然后通过rtd_key在function_table里查找对应的zend_function，找到后复制一份新的zend_function。
* 最后使用lcname作为key，将上一部中复制的zend_function添加到function_table中，根据是否成功进行后续处理。
  

这个流程已经很清楚了，运行时动态声明的函数其实在编译时期就已经被添加到了function_table里，只是使用了一个不同的名字，即上面代码中的rtd_key，我们下面就来看一下这个rtd_key长什么样子。

使用gdb调试PHP CLI，在do_bind_function设置断点：

``` 
(gdb) p (char*)lcname->value.str->val 
$1 = 0x7ffff5e01b58 "func02"
(gdb) n
1089		function = zend_hash_find_ptr(function_table, Z_STR_P(rtd_key));
(gdb) p (char*)rtd_key->value.str->val
$2 = 0x7ffff5e7e318 ""
(gdb) p rtd_key->value.str->len 
$3 = 108
(gdb) p (char*)(rtd_key->value.str->val+1)
$4 = 0x7ffff5e7e319 "func02/home/kylin/Downloads/php-7.2.2/ext/zendump/example/functions/conditional_functions.php0x7ffff7fe409e"
(gdb)
```

我们看到lcname确实是我们要声明的函数名字”func02″，而rtd_key则是以\0开头的一个长串”\0func02/home/kylin/Downloads/php-7.2.2/ext/zendump/example/functions/conditional_functions.php0x7ffff7fe409e”，Zend引擎中很多内部使用的key name都是这个样子，以\0开头，有的中间还包含\0，因为PHP的zend_string结构有存储字符串的长度，所以字符串中可以包含\0，PHP自己能够正确处理。不过这一点在我们开发扩展时需要加强注意，要判断zend_string的长度，不要像一般C语言中习惯的那样，遇到\0就认为是字符串的结尾。



[0]: https://github.com/php7th/zendump