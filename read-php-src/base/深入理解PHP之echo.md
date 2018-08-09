## 深入理解PHP之echo

来源：[https://juejin.im/post/5b60648e6fb9a04fcd586af1](https://juejin.im/post/5b60648e6fb9a04fcd586af1)

时间 2018-08-02 13:39:21

 `echo`作为PHP中的语言结构, 经常会被使用, 因此了解他的实现还是有必要的.
 
| 版本 | 源码地址 | 
|-|-|
| `PHP-7.2.8` | https://github.com/php/php-src/tree/PHP-7.2.8/ | 
 
 
## 2、官方文档(php.net)
 
### 2.1 输出一个或多个字符串
 
```
void echo ( string $arg1 [, string $... ] )
```
 
 
* [文档地址][3]  
 
 
### 2.2 说明
 `echo`不是一个函数，是一个PHP的语言结构，因此不一定要使用小括号来指明参数，单引号、双引号都行.`echo`不表现得像一个函数，所以不能总是使用一个函数的上下文。`echo`输出多个字符串的时候， 不能使用小括号。`echo`在php.ini中启用`short_open_tag`时，有一个快捷用法（view层）`<?= 'Hello World'; ?>` `echo`和`print`最主要的不同之处是，`echo`接受参数列表，并且没有返回值。
 
### 2.3 注释
 
Note: 因为是一个语言构造器而不是一个函数，不能被 可变函数 调用。
 
```
<?php

/**
 * Tip
 * 相对 echo 中拼接字符串而言，传递多个参数比较好，考虑到了 PHP 中连接运算符（“.”）的优先级。 传入多个参数，不需要圆括号保证优先级：
 */
echo "Sum: ", 1 + 2;
echo "Hello ", isset($name) ? $name : "John Doe", "!";

/** Tip
 * 如果是拼接的，相对于加号和三目元算符，连接运算符（“.”）具有更高优先级。为了正确性，必须使用圆括号：
 */
echo 'Sum: ' . (1 + 2);
echo 'Hello ' . (isset($name) ? $name : 'John Doe') . '!';
```
 
## 3、应用
 
### 3.1 输出基本数据类型
 
```
echo 123, 'abc', [12, 34];  // 123abcArray

echo "Sum: ", 1 + 2; // Sum: 3
echo 'Sum: ' . (1 + 2); // Sum: 3

echo "Hello ", isset($name) ? $name : "John Doe", "!"; // Hello John Doe!
echo 'Hello ' . (isset($name) ? $name : 'John Doe') . '!'; // Hello John Doe!
```
 
### 3.2 输出对象类型
 
```php
<?php 
class Customer {
    public function say() {
        return 'Hello World';
    }
}

echo (new Customer());
```
 
```
Catchable fatal error: Object of class Customer could not be converted to string in /usercode/file.php on line 8


```
 
输出对象时汇报以上错误, 所以如果需要输出对象, 一定要在其内部实现`__toString()`。
 
```php
<?php 
class Customer {
    public function say() {
        return 'Hello World';
    }
    
    /**
     * __toString() 方法用于一个类被当成字符串时应怎样回应。例如 echo $obj; 应该显示些什么。此方法必须返回一个字符串，否则将发出一条 E_RECOVERABLE_ERROR 级别的致命错误。
     */
    public function __toString(){
        return $this->say();
    }
}

echo (new Customer()); // Hello World
```
 
### 3.3 输出资源类型
 
```php
echo tmpfile(); // Resource id #1
```
 
## 4、源码
 
### 4.1 源码概述
 `php`是一门脚本语言, 所以所有的符号都会先经过词法解析和语法解析阶段, 这两个阶段由`lex`&`yacc`完成。
 
在计算机科学里面，`lex`是一个产生词法分析器的程序。`Lex`常常与`yacc`语法分析器产生程序一起使用。`Lex`是许多`UNIX系统`的标准词法分析器产生程序，而且这个工具所作的行为被详列为`POSIX标准`的一部分。`Lex`读进一个代表词法分析器规则的输入字符串流，然后输出以C语言实做的词法分析器源代码。 --维基百科
 
对应的文件在`Zend/zend_language_parser.y`和`Zend/zend_language_scanner.l`。
 
### 4.2 字符转标记(Zend/zend_language_scanner.l)
 
```
<ST_IN_SCRIPTING>"echo" {
	RETURN_TOKEN(T_ECHO);
}
```
 
ZEND引擎在读取一个PHP文件之后会先进行词法分析，就是用lex扫描，把对应的PHP字符转换成相应的标记（也叫token)，比如`echo $a;`在碰到这句首先会匹配到`echo`，符合上面的规则，然后就返回一个`T_ECHO`标记，这个在后面的语法分析会用上，也就是在`zend_language_parser.y`文件中
 
### 4.3 语法分析(Zend/zend_language_parser.y)
 
```
# %token Token就是一个个的“词块”
%token T_ECHO       "echo (T_ECHO)"

# statement T_ECHO echo_expr_list
statement:
		'{' inner_statement_list '}' { $$ = $2; }
	|	if_stmt { $$ = $1; }
	|	alt_if_stmt { $$ = $1; }
	|	T_WHILE '(' expr ')' while_statement
			{ $$ = zend_ast_create(ZEND_AST_WHILE, $3, $5); }
	|	T_DO statement T_WHILE '(' expr ')' ';'
			{ $$ = zend_ast_create(ZEND_AST_DO_WHILE, $2, $5); }
	|	T_FOR '(' for_exprs ';' for_exprs ';' for_exprs ')' for_statement
			{ $$ = zend_ast_create(ZEND_AST_FOR, $3, $5, $7, $9); }
	|	T_SWITCH '(' expr ')' switch_case_list
			{ $$ = zend_ast_create(ZEND_AST_SWITCH, $3, $5); }
	|	T_BREAK optional_expr ';'		{ $$ = zend_ast_create(ZEND_AST_BREAK, $2); }
	|	T_CONTINUE optional_expr ';'	{ $$ = zend_ast_create(ZEND_AST_CONTINUE, $2); }
	|	T_RETURN optional_expr ';'		{ $$ = zend_ast_create(ZEND_AST_RETURN, $2); }
	|	T_GLOBAL global_var_list ';'	{ $$ = $2; }
	|	T_STATIC static_var_list ';'	{ $$ = $2; }
	|	T_ECHO echo_expr_list ';'		{ $$ = $2; }
	|	T_INLINE_HTML { $$ = zend_ast_create(ZEND_AST_ECHO, $1); }
	|	expr ';' { $$ = $1; }
	|	T_UNSET '(' unset_variables ')' ';' { $$ = $3; }
	|	T_FOREACH '(' expr T_AS foreach_variable ')' foreach_statement
			{ $$ = zend_ast_create(ZEND_AST_FOREACH, $3, $5, NULL, $7); }
	|	T_FOREACH '(' expr T_AS foreach_variable T_DOUBLE_ARROW foreach_variable ')'
		foreach_statement
			{ $$ = zend_ast_create(ZEND_AST_FOREACH, $3, $7, $5, $9); }
	|	T_DECLARE '(' const_list ')'
			{ zend_handle_encoding_declaration($3); }
		declare_statement
			{ $$ = zend_ast_create(ZEND_AST_DECLARE, $3, $6); }
	|	';'	/* empty statement */ { $$ = NULL; }
	|	T_TRY '{' inner_statement_list '}' catch_list finally_statement
			{ $$ = zend_ast_create(ZEND_AST_TRY, $3, $5, $6); }
	|	T_THROW expr ';' { $$ = zend_ast_create(ZEND_AST_THROW, $2); }
	|	T_GOTO T_STRING ';' { $$ = zend_ast_create(ZEND_AST_GOTO, $2); }
	|	T_STRING ':' { $$ = zend_ast_create(ZEND_AST_LABEL, $1); }
;
```
 
在`statement`看到了`T_ECHO`, 后面跟着`echo_expr_list`，再搜这个字符串，找到如下代码:
 
```c
# echo_expr_list
echo_expr_list:
    echo_expr_list ',' echo_expr { $$ = zend_ast_list_add($1, $3); }
  | echo_expr { $$ = zend_ast_create_list(1, ZEND_AST_STMT_LIST, $1); }
;

echo_expr:
  expr { $$ = zend_ast_create(ZEND_AST_ECHO, $1); }
;

expr:
    variable              { $$ = $1; }
  | expr_without_variable { $$ = $1; }
;
```
 
词法分析后得到单独存在的词块不能表达完整的语义，还需要借助规则进行组织串联。语法分析器就是这个组织者。它会检查语法、匹配Token，对Token进行关联。PHP7中，组织串联的产物就是抽象语法树（`Abstract Syntax Tree`，`AST`）, 详情请查看相关源码: [抽象语法树（Abstract Syntax Tree，AST）][4]
 
这么看比较难理解，接下来我们从一个简单的例子看下最终生成的语法树。
 
```php
$a = 123;
$b = "hi~";

echo $a,$b;
```
 
具体解析过程这里不再解释，有兴趣的可以翻下zend_language_parse.y中，这个过程不太容易理解，需要多领悟几遍，最后生成的ast如下图：
 
 ![][0]
 
### 4.4 模块初始化(main/main.c)
 
通过`write_function`绑定PHP输出函 数`php_output_wrapper`至`zend_utility_functions`结构体, 此结构体会在xx被使用
 
```c
# php_module_startup

zend_utility_functions zuf;

// ...

gc_globals_ctor();

zuf.error_function = php_error_cb;
zuf.printf_function = php_printf;
zuf.write_function = php_output_wrapper;
zuf.fopen_function = php_fopen_wrapper_for_zend;
zuf.message_handler = php_message_handler_for_zend;
zuf.get_configuration_directive = php_get_configuration_directive_for_zend;
zuf.ticks_function = php_run_ticks;
zuf.on_timeout = php_on_timeout;
zuf.stream_open_function = php_stream_open_for_zend;
zuf.printf_to_smart_string_function = php_printf_to_smart_string;
zuf.printf_to_smart_str_function = php_printf_to_smart_str;
zuf.getenv_function = sapi_getenv;
zuf.resolve_path_function = php_resolve_path_for_zend;
zend_startup(&zuf, NULL);
```
 `zuf`是一个`zend_utility_functions`结构体，这样就把`php_output_wrapper`函数传给了`zuf.write_function`，后面还有好几层包装，最后的实现也是在`main/main.c`文件里面实现的，是下面这个函数：
 
```c
/* {{{ php_output_wrapper
 */
static size_t php_output_wrapper(const char *str, size_t str_length)
{
	return php_output_write(str, str_length);
}
```
 
在`php_out_wrapper`中调用的`php_output_write`在`main/output.c`中实现, 实现代码如下:
 
```c
/* {{{ int php_output_write(const char *str, size_t len)
 * Buffered write 
 * #define PHP_OUTPUT_ACTIVATED        0x100000 
 * 当flags=PHP_OUTPUT_ACTIVATED，会调用sapi_module.ub_write输出, 每个SAPI都有自已的实现, cli中是调用sapi_cli_single_write()
 *  php_output_write(); //输出，有buffer, 调用php_output_op()
 *  php_output_write_unbuffered();//输出，没有buffer，调用PHP_OUTPUT_ACTIVATED，会调用sapi_module.ub_write
 *  php_output_set_status(); //用于SAPI设置output.flags
 *  php_output_get_status(); //获取output.flags的值
 */
PHPAPI size_t php_output_write(const char *str, size_t len)
{
	if (OG(flags) & PHP_OUTPUT_ACTIVATED) {
		php_output_op(PHP_OUTPUT_HANDLER_WRITE, str, len);
		return len;
	}
	if (OG(flags) & PHP_OUTPUT_DISABLED) {
		return 0;
	}
	return php_output_direct(str, len);
}
/* }}} */
```
 
### 4.5 输出的终点(main/output.c fwrite函数)
 
#### 不调用sapi_module的输出
 
```c
static size_t (*php_output_direct)(const char *str, size_t str_len) = php_output_stderr;

static size_t php_output_stderr(const char *str, size_t str_len)
{
	fwrite(str, 1, str_len, stderr);
/* See http://support.microsoft.com/kb/190351 */
#ifdef PHP_WIN32
	fflush(stderr);
#endif
	return str_len;
}
```
 
#### 调用sapi_module的输出
 
```c
sapi_module.ub_write(context.out.data, context.out.used);

if (OG(flags) & PHP_OUTPUT_IMPLICITFLUSH) {
	sapi_flush();
}
```
 `php_output_op`详细实现如下:
 
```c
/* {{{ static void php_output_op(int op, const char *str, size_t len)
 * Output op dispatcher, passes input and output handlers output through the output handler stack until it gets written to the SAPI 
 */
static inline void php_output_op(int op, const char *str, size_t len)
{
	php_output_context context;
	php_output_handler **active;
	int obh_cnt;

	if (php_output_lock_error(op)) {
		return;
	}

	php_output_context_init(&context, op);

	/*
	 * broken up for better performance:
	 *  - apply op to the one active handler; note that OG(active) might be popped off the stack on a flush
	 *  - or apply op to the handler stack
	 */
	if (OG(active) && (obh_cnt = zend_stack_count(&OG(handlers)))) {
		context.in.data = (char *) str;
		context.in.used = len;

		if (obh_cnt > 1) {
			zend_stack_apply_with_argument(&OG(handlers), ZEND_STACK_APPLY_TOPDOWN, php_output_stack_apply_op, &context);
		} else if ((active = zend_stack_top(&OG(handlers))) && (!((*active)->flags & PHP_OUTPUT_HANDLER_DISABLED))) {
			php_output_handler_op(*active, &context);
		} else {
			php_output_context_pass(&context);
		}
	} else {
		context.out.data = (char *) str;
		context.out.used = len;
	}

	if (context.out.data && context.out.used) {
		php_output_header();

		if (!(OG(flags) & PHP_OUTPUT_DISABLED)) {
#if PHP_OUTPUT_DEBUG
			fprintf(stderr, "::: sapi_write('%s', %zu)\n", context.out.data, context.out.used);
#endif
			sapi_module.ub_write(context.out.data, context.out.used);

			if (OG(flags) & PHP_OUTPUT_IMPLICITFLUSH) {
				sapi_flush();
			}

			OG(flags) |= PHP_OUTPUT_SENT;
		}
	}
	php_output_context_dtor(&context);
}
```
 
以上了解了PHP输出函数的实现, 接下来了解echo实现.
 
### 4.6 输出动作的ZEND引擎实现(Zend/zend_vm_def.h)
 
```c
ZEND_VM_HANDLER(40, ZEND_ECHO, CONST|TMPVAR|CV, ANY)
{
	USE_OPLINE
	zend_free_op free_op1;
	zval *z;

	SAVE_OPLINE();
	z = GET_OP1_ZVAL_PTR_UNDEF(BP_VAR_R);

	if (Z_TYPE_P(z) == IS_STRING) {
		zend_string *str = Z_STR_P(z);

		if (ZSTR_LEN(str) != 0) {
			zend_write(ZSTR_VAL(str), ZSTR_LEN(str));
		}
	} else {
		zend_string *str = _zval_get_string_func(z);

		if (ZSTR_LEN(str) != 0) {
			zend_write(ZSTR_VAL(str), ZSTR_LEN(str));
		} else if (OP1_TYPE == IS_CV && UNEXPECTED(Z_TYPE_P(z) == IS_UNDEF)) {
			GET_OP1_UNDEF_CV(z, BP_VAR_R);
		}
		zend_string_release(str);
	}

	FREE_OP1();
	ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();
}
```
 
可以看到在`zend vm`中通过调用`zend_write`来实现输出，接下来看下`zend_write`的实现。
 
### 4.7 zend_write实现(Zend/zend.c)
 
```
# Zend/zend.h
typedef int (*zend_write_func_t)(const char *str, size_t str_length);

# Zend/zend.c
ZEND_API zend_write_func_t zend_write;

# 如下图所示, zend_write的初始化是在zend_startup()函数里面，这是zend引擎启动的时候需要做的一些初始化工作，有下面一句：

zend_write = (zend_write_func_t) utility_functions->write_function; // php_output_wrapper
```
 `zend_utility_functions *utility_functions`在`main/main.c``php_module_startup()`的`zuf`中被定义:
 
```
zuf.write_function = php_output_wrapper;
```
 
 ![][1]
 
## 5、php(echo)加速
 
### 5.1 PHP echo 真的慢么?
 
 ![][2]
 `echo`输出大字符串(500K)的时候，执行时间会明显变长，所以会被认为PHP的`echo`性能很差, 实际上这并不是语言(`PHP`)问题, 而是一个IO问题(IO的速度限制了输出的速度)。
 
但是在某些时候`echo`执行时间过长, 会影响其他的服务, 进而影响整个系统。
 
那么使用`apache`时如何优化使的`echo`变快， 让PHP的请求处理过程尽快结束？
 
### 5.2 还是可以优化的: 打开输出缓存
 `echo`慢是在等待“写数据”成功返回， 所以可打开输出缓存：
 
```php
# 编辑php.ini
output_buffering = 4096 //bytes

# 调用ob_start()
ob_start();
echo $hugeString;
ob_end_flush();
```
 `ob_start()`会开辟一块4096大小的buffer，所以如果`$hugeString`大于 4096，将不会起到加速作用。
 `echo`会立即执行成功返回, 因为数据暂时写到了我们的输出缓存中，如果buffer足够大，那么内容会等到脚本的最后，才一次性的发送给客户端(严格的说是发给webserver)。
 
## 6、输出时的类型转换
 
### 6.1 输出时的类型转换规则
 
| input | output | desc | code | 
|-|-|-|-|
| Boolean | String | 1 或 0 | `echo true; // 1` | 
| Integer | Integer | 不转换 | `echo 123; // 123` | 
| Float | Float | 不转换, 注意精度问题 | `echo 123.234; // 123.234` | 
| String | String | 不转换 | `echo 'abcd'; // abcd` | 
| Array | Array | - | `echo [12, 34]; // Array` | 
| Object | Catchable fatal error | Object of class stdClass could not be converted to string in file.php on line * | `echo json_decode(json_encode(['a' => 'b']));` | 
| Resource | Resource id #1 | - | `echo tmpfile(); // Resource id #1` | 
| NULL | string | 转为空字符串 | `echo null; // 空字符串` | 
 
 
### 6.2 输出时的类型转换源码(Zend/zend_operators.h & Zend/zend_operators.c)
 
```c
# Zend/zend_operators.h
ZEND_API zend_string* ZEND_FASTCALL _zval_get_string_func(zval *op);

# Zend/zend_operators.c
ZEND_API zend_string* ZEND_FASTCALL _zval_get_string_func(zval *op) /* {{{ */
{
try_again:
	switch (Z_TYPE_P(op)) {
		case IS_UNDEF:
		case IS_NULL:
		case IS_FALSE:
			return ZSTR_EMPTY_ALLOC();
		case IS_TRUE:
			if (CG(one_char_string)['1']) {
				return CG(one_char_string)['1'];
			} else {
				return zend_string_init("1", 1, 0);
			}
		case IS_RESOURCE: {
			char buf[sizeof("Resource id #") + MAX_LENGTH_OF_LONG];
			int len;

			len = snprintf(buf, sizeof(buf), "Resource id #" ZEND_LONG_FMT, (zend_long)Z_RES_HANDLE_P(op));
			return zend_string_init(buf, len, 0);
		}
		case IS_LONG: {
			return zend_long_to_str(Z_LVAL_P(op));
		}
		case IS_DOUBLE: {
			return zend_strpprintf(0, "%.*G", (int) EG(precision), Z_DVAL_P(op));
		}
		case IS_ARRAY:
			zend_error(E_NOTICE, "Array to string conversion");
			return zend_string_init("Array", sizeof("Array")-1, 0);
		case IS_OBJECT: {
			zval tmp;
			if (Z_OBJ_HT_P(op)->cast_object) {
				if (Z_OBJ_HT_P(op)->cast_object(op, &tmp, IS_STRING) == SUCCESS) {
					return Z_STR(tmp);
				}
			} else if (Z_OBJ_HT_P(op)->get) {
				zval *z = Z_OBJ_HT_P(op)->get(op, &tmp);
				if (Z_TYPE_P(z) != IS_OBJECT) {
					zend_string *str = zval_get_string(z);
					zval_ptr_dtor(z);
					return str;
				}
				zval_ptr_dtor(z);
			}
			zend_error(EG(exception) ? E_ERROR : E_RECOVERABLE_ERROR, "Object of class %s could not be converted to string", ZSTR_VAL(Z_OBJCE_P(op)->name));
			return ZSTR_EMPTY_ALLOC();
		}
		case IS_REFERENCE:
			op = Z_REFVAL_P(op);
			goto try_again;
		case IS_STRING:
			return zend_string_copy(Z_STR_P(op));
		EMPTY_SWITCH_DEFAULT_CASE()
	}
	return NULL;
}
/* }}} */
```
 
## 7、Zend/zend_compile.c对echo的解析
 
### 7.1 源码地址
 
 
* [PHP源码地址 zend_compile.h][5]  
* [PHP源码地址 zend_compile.c][6]  
 
 
### 7.2`zend_compile_expr`实现 
 
```c
# Zend/zend_compile.h
void zend_compile_expr(znode *node, zend_ast *ast);

# Zend/zend_compile.c
void zend_compile_expr(znode *result, zend_ast *ast) /* {{{ */
{
	/* CG(zend_lineno) = ast->lineno; */
	CG(zend_lineno) = zend_ast_get_lineno(ast);

	switch (ast->kind) {
		case ZEND_AST_ZVAL:
			ZVAL_COPY(&result->u.constant, zend_ast_get_zval(ast));
			result->op_type = IS_CONST;
			return;
		case ZEND_AST_ZNODE:
			*result = *zend_ast_get_znode(ast);
			return;
		case ZEND_AST_VAR:
		case ZEND_AST_DIM:
		case ZEND_AST_PROP:
		case ZEND_AST_STATIC_PROP:
		case ZEND_AST_CALL:
		case ZEND_AST_METHOD_CALL:
		case ZEND_AST_STATIC_CALL:
			zend_compile_var(result, ast, BP_VAR_R);
			return;
		case ZEND_AST_ASSIGN:
			zend_compile_assign(result, ast);
			return;
		case ZEND_AST_ASSIGN_REF:
			zend_compile_assign_ref(result, ast);
			return;
		case ZEND_AST_NEW:
			zend_compile_new(result, ast);
			return;
		case ZEND_AST_CLONE:
			zend_compile_clone(result, ast);
			return;
		case ZEND_AST_ASSIGN_OP:
			zend_compile_compound_assign(result, ast);
			return;
		case ZEND_AST_BINARY_OP:
			zend_compile_binary_op(result, ast);
			return;
		case ZEND_AST_GREATER:
		case ZEND_AST_GREATER_EQUAL:
			zend_compile_greater(result, ast);
			return;
		case ZEND_AST_UNARY_OP:
			zend_compile_unary_op(result, ast);
			return;
		case ZEND_AST_UNARY_PLUS:
		case ZEND_AST_UNARY_MINUS:
			zend_compile_unary_pm(result, ast);
			return;
		case ZEND_AST_AND:
		case ZEND_AST_OR:
			zend_compile_short_circuiting(result, ast);
			return;
		case ZEND_AST_POST_INC:
		case ZEND_AST_POST_DEC:
			zend_compile_post_incdec(result, ast);
			return;
		case ZEND_AST_PRE_INC:
		case ZEND_AST_PRE_DEC:
			zend_compile_pre_incdec(result, ast);
			return;
		case ZEND_AST_CAST:
			zend_compile_cast(result, ast);
			return;
		case ZEND_AST_CONDITIONAL:
			zend_compile_conditional(result, ast);
			return;
		case ZEND_AST_COALESCE:
			zend_compile_coalesce(result, ast);
			return;
		case ZEND_AST_PRINT:
			zend_compile_print(result, ast);
			return;
		case ZEND_AST_EXIT:
			zend_compile_exit(result, ast);
			return;
		case ZEND_AST_YIELD:
			zend_compile_yield(result, ast);
			return;
		case ZEND_AST_YIELD_FROM:
			zend_compile_yield_from(result, ast);
			return;
		case ZEND_AST_INSTANCEOF:
			zend_compile_instanceof(result, ast);
			return;
		case ZEND_AST_INCLUDE_OR_EVAL:
			zend_compile_include_or_eval(result, ast);
			return;
		case ZEND_AST_ISSET:
		case ZEND_AST_EMPTY:
			zend_compile_isset_or_empty(result, ast);
			return;
		case ZEND_AST_SILENCE:
			zend_compile_silence(result, ast);
			return;
		case ZEND_AST_SHELL_EXEC:
			zend_compile_shell_exec(result, ast);
			return;
		case ZEND_AST_ARRAY:
			zend_compile_array(result, ast);
			return;
		case ZEND_AST_CONST:
			zend_compile_const(result, ast);
			return;
		case ZEND_AST_CLASS_CONST:
			zend_compile_class_const(result, ast);
			return;
		case ZEND_AST_ENCAPS_LIST:
			zend_compile_encaps_list(result, ast);
			return;
		case ZEND_AST_MAGIC_CONST:
			zend_compile_magic_const(result, ast);
			return;
		case ZEND_AST_CLOSURE:
			zend_compile_func_decl(result, ast);
			return;
		default:
			ZEND_ASSERT(0 /* not supported */);
	}
}
/* }}} */
```
 
### 7.3`zend_compile_echo`实现 
 
```c
# Zend/zend_compile.c
void zend_compile_echo(zend_ast *ast) /* {{{ */
{
	zend_op *opline;
	zend_ast *expr_ast = ast->child[0];

	znode expr_node;
	zend_compile_expr(&expr_node, expr_ast);

	opline = zend_emit_op(NULL, ZEND_ECHO, &expr_node, NULL);
	opline->extended_value = 0;
}
```
 
## 8、参考
 
 
* [@Laruence 加速PHP的ECHO][7]  
* [@Laruence PHP是无辜的][8]  
 
 


[3]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Ffunction.echo.php
[4]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fphp%2Fphp-src%2Fblob%2FPHP-7.2.8%2FZend%2Fzend_ast.c
[5]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fphp%2Fphp-src%2Ftree%2FPHP-7.2.8%2FZend%2Fzend_compile.h
[6]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fphp%2Fphp-src%2Ftree%2FPHP-7.2.8%2FZend%2Fzend_compile.c
[7]: https://link.juejin.im?target=http%3A%2F%2Fwww.laruence.com%2F2011%2F02%2F13%2F1870.html
[8]: https://link.juejin.im?target=http%3A%2F%2Fwww.laruence.com%2F2010%2F12%2F17%2F1833.html
[0]: ./img/mqA3iyI.png
[1]: ./img/z2Unq2A.png
[2]: ./img/ZnAZ7vM.png