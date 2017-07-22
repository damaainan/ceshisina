# PHP内核源码阅读笔记 

[2017-01-12][0]这个工作一直拖延了很久，终于有时间拿出来写写。  
本文主要是根据“TIPI深入理解PHP内核”一书进行阅读和分析

# 一、概述

可以利用辅助的工具进行php代码阅读  
例如vim或者sublimetext，重量级的可以用eclipse或者phpstorm这样的工具  
本人用的是phpstorm进行代码阅读，CTRL+鼠标点击可以找到变量的定义位置，十分之方便

## PG
```c
#ifdef ZTS
# define PG(v) TSRMG(core_globals_id, php_core_globals *, v)
extern PHPAPI int core_globals_id;
#else
# define PG(v) (core_globals.v)
extern ZEND_API struct _php_core_globals core_globals;
#endif
```
`PG`用于定义或获取全局变量  
`ZTS`是线程安全的标记

在`main/php_globalsh`中定义了宏`PG`和结构体`_php_core_globals`，用于存放一些常用到的全局参数  
举例如下：
```c
struct _php_core_globals {
    ······省略
    
    char *user_ini_filename;    //  用户的ini文件名
    long user_ini_cache_ttl;    //  ini缓存过期限制
    
    char *request_order;    //  优先级比variables_order高，在request变量生成时用到，个人觉得是历史遗留问题
    
    zend_bool mail_x_header;    //  仅在ext/standard/mail.c文件中使用，
    char *mail_log;
    
    zend_bool in_error_log;
    
    ········省略
};
```
## SG

我们来看一下SG的定义
```c
#ifdef ZTS
# define SG(v) TSRMG(sapi_globals_id, sapi_globals_struct *, v)
SAPI_API extern int sapi_globals_id;
#else
# define SG(v) (sapi_globals.v)
extern SAPI_API sapi_globals_struct sapi_globals;
#endif
```
如同`PG`一样，ZTS是线程安全的标志  
`SG`主要是用来获取`SAPI`的所需要用到的全局变量的

## CG

`CG`定义在`Zend/zend_globals_macros.h`文件中

我们一起来看看`CG`的定义相关代码：
```c
/* Compiler */
#ifdef ZTS
# define CG(v) TSRMG(compiler_globals_id, zend_compiler_globals *, v)
int zendparse(void *compiler_globals);
#else
# define CG(v) (compiler_globals.v)
extern ZEND_API struct _zend_compiler_globals compiler_globals;
int zendparse(void);
#endif
```
可见`CG`是用于存取`compiler`需要用到的一些全局变量的

## EG
```c
#ifdef ZTS
# define EG(v) TSRMG(executor_globals_id, zend_executor_globals *, v)
#else
# define EG(v) (executor_globals.v)
extern ZEND_API zend_executor_globals executor_globals;
#endif
```
`EG`用于存取执行器需要用到的全局变量(executor_globals)

## EX

    #define EX(element) execute_data.element

## 关于PHPAPI

在源码中我们经常能见到`PHPAPI`这样的前缀,`__attribute__ ((packed))` 的作用就是告诉编译器取消结构在编译过程中的优化对齐,按照实际占用字节数进行对齐，是GCC特有的语法。这个功能是跟操作系统没关系，跟编译器有关，gcc编译器不是紧凑模式的，我在windows下，用vc的编译器也不是紧凑的，用tc的编译器就是紧凑的

他的定义如下：
```c
#   if defined(__GNUC__) && __GNUC__ >= 4
#       define PHPAPI __attribute__ ((visibility("default")))
#   else
#       define PHPAPI
#   endif
```
在PHP源码中随处到可以看到TSRM这个标记

* TSRM  
线程安全资源管理器(Thread Safe Resource Manager)，这是个尝尝被忽视，并很少被人说起的“层”(layer), 它在PHP源码的TSRM目录下。一般的情况下，这个层只会在被指明需要的时候才会被启用(比如,Apache2+worker MPM,一个基于线程的MPM)，对于Win32下的Apache来说，是基于多线程的，所以这个层在Win32下总是被启用的。
* ZTS  
Zend线程安全(Zend Thread Safety)，当TSRM被启用的时候，就会定义这个名为ZTS的宏。
* tsrm_ls  
TSRM存储器(TSRM Local Storage)，这个是在扩展和Zend中真正被实际使用的指代TSRM存储的变量名。

相关的定义如下
```c
#ifdef ZTS
#define TSRMLS_D    void ***tsrm_ls
#define TSRMLS_DC   , TSRMLS_D
#define TSRMLS_C    tsrm_ls
#define TSRMLS_CC   , TSRMLS_C
#else /* non ZTS */
#define TSRMLS_D    void
#define TSRMLS_DC
#define TSRMLS_C
#define TSRMLS_CC
#endif /* ZTS */
```
注意上面的逗号

相关原理介绍可以看一看PHP大牛鸟哥的这篇文章：[TSRM到底是什么?][1]

简单来说TSRM就是用来保证线程安全的，在编写代码的时候要记得加上

# 二、代码生成以及执行

![PHP代码运行示意图][2]

## PHP执行的生命周期和ZEND引擎

### PHP的单进程生命周期

![PHP生命周期][3]

步骤如下：

* 启动
* 初始化若干全局变量
* 初始化若干常量
* 初始化Zend引擎和核心组件
* 解析php.ini
* 全局操作函数的初始化
* 初始化静态构建的模块和共享模块(MINIT)
* 禁用函数和类
* ACTIVATION
* 激活Zend引擎
* 激活SAPI
* 环境初始化
* 模块请求初始化
* 运行
* DEACTIVATION
* 结束
* flush
* 关闭Zend引擎

### 多进程的生命周期

![PHP多进程SAPI生命周期][4]

### 多线程的生命周期

![PHP多线程SAPI生命周期][5]

### 关于Zend引擎

Zend引擎是PHP实现的核心，提供了语言实现上的基础设施。例如：PHP的语法实现，脚本的编译运行环境， 扩展机制以及内存管理等，当然这里的PHP指的是官方的PHP实现(除了官方的实现， 目前比较知名的有facebook的hiphop实现，不过到目前为止，PHP还没有一个标准的语言规范)， 而PHP则提供了请求处理和其他Web服务器的接口(SAPI)。

## PHP的SAPI

SAPI提供了请求处理和其他Web Server的接口

PHP SAPI简单示意图

![PHP SAPI][6]

对应源码文件在/main/SAPI.h

整个SAPI类似于一个面向对象中的模板方法模式的应用。 SAPI.c和SAPI.h文件所包含的一些函数就是模板方法模式中的抽象模板， 各个服务器对于sapi_module的定义及相关实现则是一个个具体的模板。

`_sapi_module_struct`结构体的定义

```c
struct _sapi_module_struct {
    char *name;         //  名字（标识用）
    char *pretty_name;  //  更好理解的名字（自己翻译的）
 
    int (*startup)(struct _sapi_module_struct *sapi_module);    //  启动函数
    int (*shutdown)(struct _sapi_module_struct *sapi_module);   //  关闭方法
 
    int (*activate)(TSRMLS_D);  // 激活
    int (*deactivate)(TSRMLS_D);    //  停用
 
    int (*ub_write)(const char *str, unsigned int str_length TSRMLS_DC);
     //  不缓存的写操作(unbuffered write)
    void (*flush)(void *server_context);    //  flush
    struct stat *(*get_stat)(TSRMLS_D);     //  get uid
    char *(*getenv)(char *name, size_t name_len TSRMLS_DC); //  getenv
 
    void (*sapi_error)(int type, const char *error_msg, ...);   /* error handler */
 
    int (*header_handler)(sapi_header_struct *sapi_header, sapi_header_op_enum op,
        sapi_headers_struct *sapi_headers TSRMLS_DC);   /* header handler */
 
     /* send headers handler */
    int (*send_headers)(sapi_headers_struct *sapi_headers TSRMLS_DC);
 
    void (*send_header)(sapi_header_struct *sapi_header,
            void *server_context TSRMLS_DC);   /* send header handler */
 
    int (*read_post)(char *buffer, uint count_bytes TSRMLS_DC); /* read POST data */
    char *(*read_cookies)(TSRMLS_D);    /* read Cookies */
 
    /* register server variables */
    void (*register_server_variables)(zval *track_vars_array TSRMLS_DC);
 
    void (*log_message)(char *message);     /* Log message */
    time_t (*get_request_time)(TSRMLS_D);   /* Request Time */
    void (*terminate_process)(TSRMLS_D);    /* Child Terminate */
 
    char *php_ini_path_override;    //  覆盖的ini路径
 
    void (*block_interruptions)(void);
    void (*unblock_interruptions)(void);
    void (*default_post_reader)(TSRMLS_D);
    void (*treat_data)(int arg, char *str, zval *destArray TSRMLS_DC);
    char *executable_location;
    int php_ini_ignore;
    int php_ini_ignore_cwd; /* don't look for php.ini in the current directory */
    int (*get_fd)(int *fd TSRMLS_DC);
    int (*force_http_10)(TSRMLS_D);
    int (*get_target_uid)(uid_t * TSRMLS_DC);
    int (*get_target_gid)(gid_t * TSRMLS_DC);
    unsigned int (*input_filter)(int arg, char *var, char **val, unsigned int val_len, unsigned int *new_val_len TSRMLS_DC);
    
    void (*ini_defaults)(HashTable *configuration_hash);
    int phpinfo_as_text;
    char *ini_entries;
    const zend_function_entry *additional_functions;
    unsigned int (*input_filter_init)(TSRMLS_D);
};
```
还有存放全局变量的结构体：
```c
typedef struct _sapi_globals_struct {
    void *server_context;
    sapi_request_info request_info;
    sapi_headers_struct sapi_headers;
    int64_t read_post_bytes;
    unsigned char post_read;
    unsigned char headers_sent;
    struct stat global_stat;
    char *default_mimetype;
    char *default_charset;
    HashTable *rfc1867_uploaded_files;
    long post_max_size;
    int options;
    zend_bool sapi_started;
    double global_request_time;
    HashTable known_post_content_types;
    zval *callback_func;
    zend_fcall_info_cache fci_cache;
    zend_bool callback_run;
} sapi_globals_struct;
```
在apache中SAPI的定义
```
static sapi_module_struct apache2_sapi_module = {
    "apache2handler",
    "Apache 2.0 Handler",
 
    php_apache2_startup,                /* startup */
    php_module_shutdown_wrapper,            /* shutdown */
 
    ...
}
```
## PHP脚本的执行

PHP是一边运行一边解析的脚本型语言，在解析的过程中生成了OP码，减少性能的损耗

在PHP的执行过程中，通过cli方式或者CGI方式传递给php程序需要执行的文件， php程序完成基本的准备工作后启动PHP及Zend引擎， 加载注册的扩展模块。  
初始化完成后读取脚本文件，Zend引擎对脚本文件进行词法分析，语法分析。然后编译成opcode执行。 如果安装了apc之类的opcode缓存， 编译环节可能会被跳过而直接从缓存中读取opcode执行。

PHP在读取到脚本文件后首先对代码进行词法分析，PHP的词法分析器是通过lex生成的， 词法规则文件在$PHP_SRC/Zend/zend_language_scanner.l， 这一阶段lex会会将源代码按照词法规则切分一个一个的标记(token)。PHP中提供了一个函数token_get_all()， 该函数接收一个字符串参数， 返回一个按照词法规则切分好的数组。 例如将上面的php代码作为参数传递给这个函数：

举个例子：
```php
<?php
$code =<<<PHP_CODE
<?php
$str = "Hello, Tipi\n";
echo $str;
PHP_CODE;
 
var_dump(token_get_all($code));

```
运行上述代码，即代码被按照标准分词
```
array (
  0 => 
  array (
    0 => 368,       // 脚本开始标记
    1 => '<?php     // 匹配到的字符串
',
    2 => 1,
  ),
  1 => 
  array (
    0 => 371,
    1 => ' ',
    2 => 2,
  ),
  2 => '=',
  3 => 
  array (
    0 => 371,
    1 => ' ',
    2 => 2,
  ),
  4 => 
  array (
    0 => 315,
    1 => '"Hello, Tipi
"',
    2 => 2,
  ),
  5 => ';',
  6 => 
  array (
    0 => 371,
    1 => '
',
    2 => 3,
  ),
  7 => 
  array (
    0 => 316,
    1 => 'echo',
    2 => 4,
  ),
  8 => 
  array (
    0 => 371,
    1 => ' ',
    2 => 4,
  ),
  9 => ';',

```
PHP脚本编译为opcode保存在op_array中，其内部存储的结构如下：
```c
struct _zend_op_array {
    /* Common elements */
    zend_uchar type;
    char *function_name;  // 如果是用户定义的函数则，这里将保存函数的名字
    zend_class_entry *scope;
    zend_uint fn_flags;
    union _zend_function *prototype;
    zend_uint num_args;
    zend_uint required_num_args;
    zend_arg_info *arg_info;
    zend_bool pass_rest_by_reference;
    unsigned char return_reference;
    /* END of common elements */
 
    zend_bool done_pass_two;
 
    zend_uint *refcount;
 
    zend_op *opcodes;  // opcode数组
 
    zend_uint last，size;
 
    zend_compiled_variable *vars;
    int last_var，size_var;
 
    // ...
}
```
如上面的注释，opcodes保存在这里，在执行的时候由下面的execute函数执行：
```c
ZEND_API void execute(zend_op_array *op_array TSRMLS_DC)
{
    // ... 循环执行op_array中的opcode或者执行其他op_array中的opcode
}
```
# 三、变量以及数据类型

从类型的维度来看，编程语言可以分为三大类：

* 静态类型语言，比如：C/Java等，在静态语言类型中，类型的检查是在编译期(compile-time)确定的， 也就是说在运行时变量的类型是不会发生变化的。
* 动态语言类型，比如：PHP，python等各种脚本语言，这类语言中的类型是在运行时确定的， 那么也就是说类型通常可以在运行时发生变化
* 无类型语言，比如：汇编语言，汇编语言操作的是底层存储，他们对类型毫无感知。

变量相关结构都在`Zend/zend.h`中定义

## 变量结构

zval的结构如下：
```c
struct _zval_struct {
    /* Variable information */
    zvalue_value value;     /* value */
    zend_uint refcount__gc;
    zend_uchar type;    /* active type */
    zend_uchar is_ref__gc;
};
```
其中`refcount__gc`是用来计算变量被引用的数量，`is_ref__gc`记录变量是否被引用  
这两个值都是用来辅助gc即内存回收机制的，当refcount为0的时候则回收内存

其中`zvalue_value value`用于存放变量的值，结构如下：

```c
typedef union _zvalue_value {
    long lval;                  /* long value */
    double dval;                /* double value */
    struct {
        char *val;
        int len;
    } str;
    HashTable *ht;              /* hash table value */
    zend_object_value obj;
    zend_ast *ast;
} zvalue_value;
```
union是共用体声明，即共享一块内存，取最大长度的值作为整个结构的大小。详细介绍可以看这里：[共用声明和共用一变量定义][7]

上述`HashTable`就是PHP的array的实现

object的结构如下：
```c
typedef struct _zend_object {
    zend_class_entry *ce;
    HashTable *properties;
    zval **properties_table;
    HashTable *guards; /* protects from __get/__set ... recursion */
} zend_object;
```
存在的问题

PHP5的zval定义是随着Zend Engine 2诞生的, 随着时间的推移, 当时设计的局限性也越来越明显:

首先这个结构体的大小是(在64位系统)24个字节, 我们仔细看这个`zval.value`联合体, 其中`zend_object_value`是最大的长板, 它导致整个value需要16个字节, 这个应该是很容易可以优化掉的, 比如把它挪出来, 用个指针代替,因为毕竟IS_OBJECT也不是最最常用的类型.

第二, 这个结构体的每一个字段都有明确的含义定义, 没有预留任何的自定义字段, 导致在PHP5时代做很多的优化的时候, 需要存储一些和zval相关的信息的时候, 不得不采用其他结构体映射, 或者外部包装后打补丁的方式来扩充zval

第三, PHP的zval大部分都是按值传递, 写时拷贝的值, 但是有俩个例外, 就是对象和资源, 他们永远都是按引用传递, 这样就造成一个问题, 对象和资源在除了zval中的引用计数以外, 还需要一个全局的引用计数, 这样才能保证内存可以回收. 所以在PHP5的时代, 以对象为例, 它有俩套引用计数, 一个是zval中的, 另外一个是obj自身的计数

第四, 我们知道PHP中, 大量的计算都是面向字符串的, 然而因为引用计数是作用在zval的, 那么就会导致如果要拷贝一个字符串类型的zval, 我们别无他法只能复制这个字符串. 当我们把一个zval的字符串作为key添加到一个数组里的时候, 我们别无他法只能复制这个字符串. 虽然在PHP5.4的时候, 我们引入了INTERNED STRING, 但是还是不能根本解决这个问题.

以上zval的结构是在php5中的定义，php7中发生了变化并且进行了优化  
以下是php7中的zval结构
```c
struct _zval_struct { 
    union {
        zend_long         lval; /* long value */ 
        double            dval; /* double value */ 
        zend_refcounted  *counted;
        zend_string      *str;
        zend_array       *arr;.
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
    } value; 
    union { 
        struct { 
            ZEND_ENDIAN_LOHI_4(
            zend_uchar    type, /* active type */ 
            zend_uchar    type_flags,
            zend_uchar    const_flags,
            zend_uchar    reserved) /* call info for EX(This) */ 
        } v; 
        uint32_t type_info;
    } u1; 
    union { 
        uint32_t var_flags; 
        uint32_t next; /* hash collision chain */ 
        uint32_t cache_slot; /* literal cache slot */ 
        uint32_t lineno; /* line number (for ast nodes) */ 
        uint32_t num_args; /* arguments number for EX(This) */ 
        uint32_t fe_pos; /* foreach position */ 
        uint32_t fe_iter_idx; /* foreach iterator index */ 
    } u2;
};
```
虽然看起来变得好大, 但其实仔细看, 全部都是联合体, 这个新的zval在64位环境下,现在只需要16个字节(2个指针size), 它主要分为俩个部分, value和扩充字段, 而扩充字段又分为u1和u2俩个部分, 其中u1是type info, u2是各种辅助字段.

所有的复杂类型的定义, 开始的时候都是`zend_refcounted_h`结构, 这个结构里除了引用计数以外, 还有GC相关的结构. 从而在做GC回收的时候, GC不需要关心具体类型是什么, 所有的它都可以当做`zend_refcounted*`结构来处理.

## HashTable

PHP强大的数组就是利用HashTable实现的

关于HashTable的内容在我的博客之前也有提及，可以去瞧一瞧 [PHP内核中的HashTable][8]

## 常量

常量的定义放在`Zend/zend_constants.h`中
```c
typedef struct _zend_constant {
    zval value;
    int flags;
    char *name;
    uint name_len;
    int module_number;
} zend_constant;
```
定义结构十分之简单  
`define()`内置函数的实现过程如下：
```c

/* {{{ proto bool define(string constant_name, mixed value, boolean case_insensitive=false)
   Define a new constant */
ZEND_FUNCTION(define)
{
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sz|b", &name,
            &name_len, &val, &non_cs) == FAILURE) {
            return;
    }
    ... // 类常量定义 此处不做介绍
    ... // 值类型判断和处理
    c.value = *val;
    zval_copy_ctor(&c.value);
    if (val_free) {
            zval_ptr_dtor(&val_free);
    }
    c.flags = case_sensitive; /* non persistent */
    c.name = zend_strndup(name, name_len);
    c.name_len = name_len+1;
    c.module_number = PHP_USER_CONSTANT;
    if (zend_register_constant(&c TSRMLS_CC) == SUCCESS) {
            RETURN_TRUE;
    } else {
            RETURN_FALSE;
    }
}
/* }}} */
```
上面的代码已经对对象和类常量做了简化处理， 其实现上是一个将传递的参数传递给新建的zend_constant结构，并将这个结构体注册到常量列表中的过程。 关于大小写敏感，函数的第三个参数表示是否大小不敏感，默认为false（大小写敏感）。 这个参数最后会赋值给zend_constant结构体的flags字段。

## 预定义变量

在某个局部函数中使用类似于$GLOBALS变量这样的预定义变量， 如果在此函数中有改变的它们的值的话，这些变量在其它局部函数调用时会发现也会同步变化。 为什么呢？是否是这些变量存放在一个集中存储的地方？ 从PHP中间代码的执行来看，这些变量是存储在一个集中的地方：EG(symbol_table)。

在模块初始化时，$GLOBALS在zend_startup函数中通过调用zend_register_auto_global将GLOBALS注册为预定义变量。 $_GET、$_POST等在php_startup_auto_globals函数中通过zend_register_auto_global将_GET、_POST等注册为预定义变量。

在通过$获取变量时，PHP内核都会通过这些变量名区分是否为全局变量（`ZEND_FETCH_GLOBAL`）， 其调用的判断函数为`zend_is_auto_global`，这个过程是在生成中间代码过程中实现的。 如果是`ZEND_FETCH_GLOBAL`或`ZEND_FETCH_GLOBAL_LOCK`(global语句后的效果)， 则在获取获取变量表时(zend_get_target_symbol_table)， 直接返回EG(symbol_table)。则这些变量的所有操作都会在全局变量表进行。

## 类型提示实现

![PHP类型提示][9]

## 变量作用域

对于全局变量，Zend引擎有一个`_zend_executor_globals`结构（EG），该结构中的symbol_table就是全局符号表， 其中保存了在顶层作用域中的变量。同样，函数或者对象的方法在被调用时会创建active_symbol_table来保存局部变量。 当程序在顶层中使用某个变量时，Zend Engine就会在symbol_table中进行遍历， 同理，如果程序运行于某个函数中，Zend引擎会遍历查询与其对应的active_symbol_table， 而每个函数的`active_symbol_table`是相对独立的，由此而实现的作用域的独立。
```c
struct _zend_execute_data {
    struct _zend_op *opline;
    zend_function_state function_state;
    zend_function *fbc; /* Function Being Called */
    zend_class_entry *called_scope;
    zend_op_array *op_array;
    zval *object;
    union _temp_variable *Ts;
    zval ***CVs;
    HashTable *symbol_table;
    struct _zend_execute_data *prev_execute_data;
    zval *old_error_reporting;
    zend_bool nested;
    zval **original_return_value;
    zend_class_entry *current_scope;
    zend_class_entry *current_called_scope;
    zval *current_this;
    zval *current_object;
    struct _zend_op *call_opline;
};
```
函数中的局部变量就存储在`_zend_execute_data`的`symbol_table`中，在执行当前函数的op_array时， 全局`zend_executor_globals`中的`*active_symbol_table`会指向当前`_zend_execute_data`中的`*symbol_table`。 因为每个函数调用开始时都会重新初始化EG(active_symbol_table)为NULL， 在这个函数的所有opcode的执行过程中这个全局变量会一直存在，并且所有的局部变量修改都是在它上面操作完成的，如前面的赋值操作等。 而此时，其他函数中的symbol_table会存放在栈中，将当前函数执行完并返回时，程序会将之前保存的zend_execute_data恢复， 从而其他函数中的变量也就不会被找到，局部变量的作用域就是以这种方式来实现的。

## 类型转换

可以参照文件`ext/standard/type.c`，里面包含了类型转换需要用到的函数

PHP的标准扩展中提供了两个有用的方法settype()以及gettype()方法，前者可以动态的改变变量的数据类型， gettype()方法则是返回变量的数据类型

# 四、函数的实现

PHP函数分为以下几种：

* 用户定义的函数
* 内部函数：如我们常见的count、strpos、implode等函数，这些都是标准函数，它们都是由标准扩展提供的； 如我们经常用到的isset、empty、eval等函数，这些结构被称之为语言结构。 还有一些函数需要和特定的PHP扩展模块一起编译并开启，否则无法使用。也就是有些扩展是可选的。
* 匿名函数：Closure
* 变量函数：
 ```php
 $func = 'print_r';
 $func('i am print_r function.');
 ```
* zend_function可以与zend_op_array互换
* zend_function可以与zend_internal_function互换
但是一个zend_op_array结构转换成zend_function是不能再次转变成zend_internal_function结构的，反之亦然。  
其实zend_function就是一个混合的数据结构，这种结构在一定程序上节省了内存空间。

函数的结构体包含一些公共的元素，即Common elements，所以它们之间可以比较方便地实现转换

## 函数定义

**词法分析->语法分析->生成中间代码(zend_op)->执行中间代码**  
执行代码的过程在文件`Zend/zend_vm_execute.h`其中`zend_op`的定义如下：
```c
struct _zend_op {
    opcode_handler_t handler;
    znode_op op1;
    znode_op op2;
    znode_op result;
    ulong extended_value;
    uint lineno;
    zend_uchar opcode;
    zend_uchar op1_type;
    zend_uchar op2_type;
    zend_uchar result_type;
};
```
## 函数的参数

函数的参数存放在`zend_arg_info`中，其定义放在文件`Zend/zend.compile.h`中
```c
typedef struct _zend_arg_info {
    const char *name;   /* 参数的名称*/
    zend_uint name_len;     /* 参数名称的长度*/
    const char *class_name; /* 类名 */
    zend_uint class_name_len;   /* 类名长度*/
    zend_bool array_type_hint;  /* 数组类型提示 */
    zend_bool allow_null;   /* 是否允许为NULL　*/
    zend_bool pass_by_reference;    /*　是否引用传递 */
    zend_bool return_reference; 
    int required_num_args;  
} zend_arg_info;
```
## 函数的返回值

在PHP中，函数都有返回值，分两种情况，使用return语句明确的返回和没有return语句返回NULL。

函数结束时需要调用`zend_do_return`：
```c

void zend_do_return(znode *expr, int do_end_vparse TSRMLS_DC) /* {{{ */
{
    zend_op *opline;
    int start_op_number, end_op_number;
 
    if (do_end_vparse) {
        if (CG(active_op_array)->return_reference
                && !zend_is_function_or_method_call(expr)) {
            zend_do_end_variable_parse(expr, BP_VAR_W, 0 TSRMLS_CC);/* 处理返回引用 */
        } else {
            zend_do_end_variable_parse(expr, BP_VAR_R, 0 TSRMLS_CC);/* 处理常规变量返回 */
        }
    }
 
   ...// 省略  取其它中间代码操作
 
    opline->opcode = ZEND_RETURN;
 
    if (expr) {
        opline->op1 = *expr;
 
        if (do_end_vparse && zend_is_function_or_method_call(expr)) {
            opline->extended_value = ZEND_RETURNS_FUNCTION;
        }
    } else {
        opline->op1.op_type = IS_CONST;
        INIT_ZVAL(opline->op1.u.constant);
    }
 
    SET_UNUSED(opline->op2);
}
```
可见生成的中间代码为`ZEND_RETURNZEND_RETURN`中间代码会执行 `ZEND_RETURN_SPEC_CONST_HANDLER`， `ZEND_RETURN_SPEC_TMP_HANDLER`或`ZEND_RETURN_SPEC_TMP_HANDLER`。 这三个函数的执行流程基本类似，包括对一些错误的处理。 

这里我们看看`ZEND_RETURN_SPEC_CONST_HANDLER`是如何执行的：
```c
static int ZEND_FASTCALL  ZEND_RETURN_SPEC_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
{
    zend_op *opline = EX(opline);
    zval *retval_ptr;
    zval **retval_ptr_ptr;
 
 
    if (EG(active_op_array)->return_reference == ZEND_RETURN_REF) {
 
        //  返回引用时不允许常量和临时变量
        if (IS_CONST == IS_CONST || IS_CONST == IS_TMP_VAR) {   
            /* Not supposed to happen, but we'll allow it */
            zend_error(E_NOTICE, "Only variable references \
                should be returned by reference");
            goto return_by_value;
        }
 
        retval_ptr_ptr = NULL;  //  返回值
 
        if (IS_CONST == IS_VAR && !retval_ptr_ptr) {
            zend_error_noreturn(E_ERROR, "Cannot return string offsets by reference");
        }
 
        if (IS_CONST == IS_VAR && !Z_ISREF_PP(retval_ptr_ptr)) {
            if (opline->extended_value == ZEND_RETURNS_FUNCTION &&
                EX_T(opline->op1.u.var).var.fcall_returned_reference) {
            } else if (EX_T(opline->op1.u.var).var.ptr_ptr ==
                    &EX_T(opline->op1.u.var).var.ptr) {
                if (IS_CONST == IS_VAR && !0) {
                      /* undo the effect of get_zval_ptr_ptr() */
                    PZVAL_LOCK(*retval_ptr_ptr);
                }
                zend_error(E_NOTICE, "Only variable references \
                 should be returned by reference");
                goto return_by_value;
            }
        }
 
        if (EG(return_value_ptr_ptr)) { //  返回引用
            SEPARATE_ZVAL_TO_MAKE_IS_REF(retval_ptr_ptr);   //  is_ref__gc设置为1
            Z_ADDREF_PP(retval_ptr_ptr);    //  refcount__gc计数加1
 
            (*EG(return_value_ptr_ptr)) = (*retval_ptr_ptr);
        }
    } else {
return_by_value:
 
        retval_ptr = &opline->op1.u.constant;
 
        if (!EG(return_value_ptr_ptr)) {
            if (IS_CONST == IS_TMP_VAR) {
 
            }
        } else if (!0) { /* Not a temp var */
            if (IS_CONST == IS_CONST ||
                EG(active_op_array)->return_reference == ZEND_RETURN_REF ||
                (PZVAL_IS_REF(retval_ptr) && Z_REFCOUNT_P(retval_ptr) > 0)) {
                zval *ret;
 
                ALLOC_ZVAL(ret);
                INIT_PZVAL_COPY(ret, retval_ptr);   //  复制一份给返回值 
                zval_copy_ctor(ret);
                *EG(return_value_ptr_ptr) = ret;
            } else {
                *EG(return_value_ptr_ptr) = retval_ptr; //  直接赋值
                Z_ADDREF_P(retval_ptr);
            }
        } else {
            zval *ret;
 
            ALLOC_ZVAL(ret);
            INIT_PZVAL_COPY(ret, retval_ptr);    //  复制一份给返回值 
            *EG(return_value_ptr_ptr) = ret;    
        }
    }
 
    return zend_leave_helper_SPEC(ZEND_OPCODE_HANDLER_ARGS_PASSTHRU);   //  返回前执行收尾工作
}
```
在没有声明返回值时：

    zend_do_return(NULL, 0 TSRMLS_CC);

zend引擎“自动”返回一个NULL## 函数的调用与执行

### 函数的调用

Zend在调用执行PHP代码之前先要把代码转换为opcode，然后再进行执行。  
我们先来看看一个PHP实例以及其生成的opcode
```php
<?php
    function foo(){
        echo "I'm foo!";
    }   
    foo();
?>
```

```

function name:  (null)
line     # *  op                           fetch          ext  return  operands
---------------------------------------------------------------------------------
              DO_FCALL                                      0          'foo'
              NOP                                                      
            > RETURN                                                   1
 
function name:  foo
line     # *  op                           fetch          ext  return  operands
---------------------------------------------------------------------------------
   4     0  >   ECHO                                                     'I%27m+foo%21'
   5     1    > RETURN
```
可以看到，上部主要集中在调用函数foo上面，PHP对函数名统一采用`strtolower`操作，所以对大小写是不敏感的  
`Zend Engine`会在`function_table`中根据函数名，若找不到，则抛出错误提示；若找到该名，则返回函数zend_function的结构指针，然后通过`function.type`的值来判断函数是内部函数还是用户定义的函数，调用`zend_execute_internal（zend_internal_function.handler）`或者直接 调用`zend_execute`来执行这个函数包含的zend_op_array

### 函数的执行

内部函数的执行与用户函数不同。用户函数是php语句一条条“翻译”成op_line组成的一个op_array，而内部函数则是用C来实现的，因为执行环境也是C环境， 所以可以直接调用

看看这个例子：

```php
<?php
    $foo = 'test';
    print_r($foo);
?>
```
其对应的opcode
```
line     # *  op                           fetch          ext  return  operands
---------------------------------------------------------------------------------
   2     0  >   ASSIGN                                                   !0, 'test'
   3     1      SEND_VAR                                                 !0
         2      DO_FCALL                                      1          'print_r'
   4     3    > RETURN                                                   1
```
先将EG下的This，scope等暂时缓存起来（这些在后面会都恢复到此时缓存的数据）。在此之后，对于用户自定义的函数， 程序会依据`zend_execute`是否等于`execute`并且是否为异常来判断是返回，还是直接执行函数定义的op_array：
```c
if (zend_execute == execute && !EG(exception)) {
    EX(call_opline) = opline;
    ZEND_VM_ENTER();
} else {
    zend_execute(EG(active_op_array) TSRMLS_CC);
}
```
而在Zend/zend.c文件的zend_startup函数中，已将zend_execute赋值为：

    ret = EX(opline)->handler(execute_data TSRMLS_CC)

调用每个opcode的处理函数。而execute_data在execute函数开始时就已经给其分配了空间，这就是这个函数的执行环境。

### 匿名函数

PHP匿名函数的实现主要有：create_function()函数的使用、__invoke、闭包

#### create_function

先介绍一下`create_function()`

`create_function()`可以创建一个匿名函数

看看官方手册上关于[create_function][10]的介绍：

> string create_function ( string $args , string $code )  
> create_function — Create an anonymous (lambda-style) function  
> 该函数主要就是用于创建匿名函数用的

我们来一起看看一个例子

```php
<?php
$func = create_function('', 'echo "Function created dynamic";');
echo $func; // lambda_1
 
$func();    // Function created dynamic
 
$my_func = 'lambda_1';
$my_func(); // 不存在这个函数
lambda_1(); // 不存在这个函数
```
从上面例子中可以看到，创建一个匿名函数其实他是**“有名”**的，但是为什么这里会提示找不到函数呢  
我们一起来看看`debug_zval_dump`的结果
```php
<?php
$func = create_function('', 'echo "Hello";');
 
$my_func_name = 'lambda_1';
debug_zval_dump($func);         // string(9) "lambda_1" refcount(2)
debug_zval_dump($my_func_name); // string(8) "lambda_1" refcount(2)

```
可见匿名函数的长度是9而实际上`lambda_1`的长度只有8

```c
#define LAMBDA_TEMP_FUNCNAME    "__lambda_func"
 
ZEND_FUNCTION(create_function)
{
    // ... 省去无关代码
    function_name = (char *) emalloc(sizeof("0lambda_")+MAX_LENGTH_OF_LONG);
    function_name[0] = '\0';  // <--- 这里
    do {
        function_name_length = 1 + sprintf(function_name + 1, "lambda_%d", ++EG(lambda_count));
    } while (zend_hash_add(EG(function_table), function_name, function_name_length+1, &new_function, sizeof(zend_function), NULL)==FAILURE);
    zend_hash_del(EG(function_table), LAMBDA_TEMP_FUNCNAME, sizeof(LAMBDA_TEMP_FUNCNAME));
    RETURN_STRINGL(function_name, function_name_length, 0);
}
```
可以见到函数在名字的前面多加了一个`'\0'`的空字符，并且利用`count`来进行函数名的编号  
所以我们可以通过在函数名前加一个“空字符”来调用匿名函数：
```  
<?php
$my_func = chr(0) . "lambda_1";  //chr()可以转换生成ascii字符
$my_func(); // Hello
```
这种创建”匿名函数”的方式有一些缺点:

* 函数的定义是通过字符串动态eval的， 这就无法进行基本的语法检查;
* 这类函数和普通函数没有本质区别， 无法实现闭包的效果.

#### 真正的匿名函数

##### __invoke

如果定义了`__invoke()`魔术方法的话那么在对象被当作函数调用时则会被调用  
这个和C++中的重载有点类似
```php
<?php
class Callme {
    public function __invoke($phone_num) {
        echo "Hello: $phone_num";
    }
}
$call = new Callme();
$call(13810688888); // "Hello: 13810688888
```
##### 匿名函数的实现

其实匿名函数也只是一个普通的类而已
```php
<?php

$func = function() {
    echo "Hello, anonymous function";
}

echo gettype($func);    // object
echo get_class($func);  // Closure
```
##### 闭包的实现

看看一段PHP闭包代码的执行过程吧：
```php

<?php
$i=100;
$counter = function() use($i) {
    debug_zval_dump($i);
};  
$counter();
```
再看看VLD生成的结果
```
$ php -dvld.active=1 closure.php
 
vars:  !0 = $i, !1 = $counter
# *  op                           fetch          ext  return  operands
------------------------------------------------------------------------
0  >   ASSIGN                                                   !0, 100
1      ZEND_DECLARE_LAMBDA_FUNCTION                             '%00%7Bclosure
2      ASSIGN                                                   !1, ~1
3      INIT_FCALL_BY_NAME                                       !1
4      DO_FCALL_BY_NAME                              0          
5    > RETURN                                                   1
 
function name:  {closure}
number of ops:  5
compiled vars:  !0 = $i
line     # *  op                           fetch          ext  return  operands
--------------------------------------------------------------------------------
  3     0  >   FETCH_R                      static              $0      'i'
        1      ASSIGN                                                   !0, $0
  4     2      SEND_VAR                                                 !0
        3      DO_FCALL                                      1          'debug_zval_dump'
  5     4    > RETURN                                                   null
```
上面根据情况去掉了一些无关的输出， 从上到下， 第1开始将100赋值给!0也就是变量$i， 随后执行ZEND_DECLARE_LAMBDA_FUNCTION， 那我们去相关的opcode执行函数中看看这里是怎么执行的， 这个opcode的处理函数位于`Zend/zend_vm_execute.h`中:
```c

static int ZEND_FASTCALL  ZEND_DECLARE_LAMBDA_FUNCTION_SPEC_CONST_CONST_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
{
    zend_op *opline = EX(opline);
    zend_function *op_array;
 
    if (zend_hash_quick_find(EG(function_table), Z_STRVAL(opline->op1.u.constant), Z_STRLEN(opline->op1.u.constant), Z_LVAL(opline->op2.u.constant), (void *) &op_arra
y) == FAILURE ||
        op_array->type != ZEND_USER_FUNCTION) {
        zend_error_noreturn(E_ERROR, "Base lambda function for closure not found");
    }
 
    zend_create_closure(&EX_T(opline->result.u.var).tmp_var, op_array TSRMLS_CC);
 
    ZEND_VM_NEXT_OPCODE();
}
```
看看创建闭包的函数，在`Zend/zend_closures.c`中：

```c

ZEND_API void zend_create_closure(zval *res, zend_function *func TSRMLS_DC)
{
    zend_closure *closure;
 
    object_init_ex(res, zend_ce_closure);
 
    closure = (zend_closure *)zend_object_store_get_object(res TSRMLS_CC);
 
    closure->func = *func;
 
    if (closure->func.type == ZEND_USER_FUNCTION) { // 如果是用户定义的匿名函数
        if (closure->func.op_array.static_variables) {
            HashTable *static_variables = closure->func.op_array.static_variables;
 
            // 为函数申请存储静态变量的哈希表空间
            ALLOC_HASHTABLE(closure->func.op_array.static_variables); 
            zend_hash_init(closure->func.op_array.static_variables, zend_hash_num_elements(static_variables), NULL, ZVAL_PTR_DTOR, 0);
 
            // 循环当前静态变量列表， 使用zval_copy_static_var方法处理
            zend_hash_apply_with_arguments(static_variables TSRMLS_CC, (apply_func_args_t)zval_copy_static_var, 1, closure->func.op_array.static_variables);
        }
        (*closure->func.op_array.refcount)++;
    }
 
    closure->func.common.scope = NULL;
}
```
# 类和面向对象

## 类的结构和实现

### 类的结构
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
 
 
    zend_class_entry **traits;
    zend_uint num_traits;
    zend_trait_alias **trait_aliases;
    zend_trait_precedence **trait_precedences;
    union {
        struct {
            const char *filename;
            zend_uint line_start;
            zend_uint line_end;
            const char *doc_comment;
            zend_uint doc_comment_len;
        } user;
        struct {
            const struct _zend_function_entry *builtin_functions;
            struct _zend_module_entry *module;
        } internal;
    } info;
};
```
### 类的实现

# 参考

[TIPI深入理解PHP内核][11]  
[深入理解PHP7之zval][12]  
[TSRM到底是什么?][1]

[0]: https://www.jwlchina.cn/2017/01/12/PHP内核源码阅读笔记/
[1]: http://blog.csdn.net/laruence/article/details/2761219
[2]: ./img/PHP代码运行示意图.png
[3]: ./img/PHP生命周期.png
[4]: ./img/PHP多进程SAPI生命周期.png
[5]: ./img/PHP多线程SAPI生命周期.png
[6]: ./img/PHPsapi.png
[7]: http://baike.baidu.com/link?url=VrJLQVen7ATX2agXwTDQbHSexslQ7JXCTPu0S5KFeYRXvTfLtZI_alAV3DlagagSLRYCt5G439Q9xFYeVolokq
[8]: http://www.jwlchina.cn/2016/11/01/PHP%E5%86%85%E6%A0%B8%E4%B8%AD%E7%9A%84HashTable/
[9]: ./img/PHP类型提示.jpg
[10]: http://php.net/create_function
[11]: http://www.php-internals.com/
[12]: http://www.open-open.com/lib/view/open1449893072613.html