## PHP-7.1 源代码学习：语法分析 之 概述


### 前言

php 使用 `lex` 和 `bison` 进行语法分析和词法分析，本文以 `bison` 语法定义文件为起点，使用 find, grep 等命令行工具搜索相关源码，以此来展示探索 PHP 语法分析源码思路

### bison 语法定义文件

在 源代码 根目录下通过 `find` 命令查找 `*.y` 文件

```
    # find . -name *.y
    ./sapi/phpdbg/phpdbg_parser.y
    ./ext/json/json_parser.y
    ./Zend/zend_ini_parser.y
    ./Zend/zend_language_parser.y
```

我们找到了 `zend_language_parser.y` 文件，里面定义了 PHP 脚本 的语法

### 语法分析树（AST）

#### AST 节点类型： YYSTYPE

在查看具体的语法规则前，我们先看看 PHP 使用什么样的数据结构表示 `AST` 根节点，使用 grep 命令 搜索 `YYSTYPE`

    # grep -rin --color --include=*.h --include=*.c "#define YYSTYPE"
    Zend/zeng_language_parser.c:108:#define YYSTYPE zend_parser_stack_elem

#### zend_parser_stack_elem

`grep zend_parser_stack_elem` 结构体定义

    # grep -rin --color --include=*.h --include=*.c "zend_parser_stack_elem"
    Zend/zend_compile.h:126:typedef union _zend_parser_stack_elem

打开 `zend_compile.h` 文件

```c
    typedef union _zend_parser_stack_elem {
        zend_ast *ast;
        zend_string *str;
        zend_ulong num;
    } zend_parser_stack_elem;
```

`zend_parser_stack_elem` 是一个联合体，一个 `AST` 节点可能是 `num`（数值），`str`（字符串）或者 `ast`（非终结符）

##### zend_ast

搜索 `_zend_ast` 结构体定义

    # grep -rin --color --include=*.h --include=*.c _zend_ast *
    Zend/zend_ast.h:153:struct _zend_ast {

打开 `zend_ast.h` 文件

```c
    struct _zend_ast {
        zend_ast_kind kind;
        zend_ast_attr attire;
        uint32_t linen;
        zend_ast *child[1];
    }
```

###### kind

Type of the node（ZEND_AST_* enum constant）  
`zend_ast.h` 文件头部 `enum` 枚举类型包含了各个 `ZEND_AST_*` 定义

```c
    enum _zend_ast_kind {
        /* special nodes */
        ZEND_AST_ZVAL = 1 << ZEND_AST_SPECIAL_SHIFT,
        ZEND_AST_ZNODE,
    
        /* declaration nodes */
        ZEND_AST_FUNC_DECL,
        ZEND_AST_CLOSURE,
        ZEND_AST_METHOD,
        ZEND_AST_CLASS,
        ...
    }
```

###### attr

Additional attribute，use depending on node type

###### linen

Line number

###### child

Array of children（using struct hack）

#### zend_ast 其它子类

zend_ast.h 文件中还包含其它 和 zend_ast 在结构上类似的结构，类似 OOP 中的 子类

##### zend_ast_list

##### zend_ast_zval

##### zend_ast_decl

#### 创建 zend_ast

`zend_ast.c` 中有一系列的函数用于创建 `zend_ast`, `zend_list`

* `zend_ast_create`
* `zend_ast_create_ex`
* `zend_ast_create_list`

##### zend_ast_create

`zend_ast_create` 函数根据 `kind` 和一个或多个 `child zend_ast` 创建一个新的 `zend_ast`，它在内部调用 `zend_ast_create_from_va_list`

```c
    ZEND_API zend_ast *zend_ast_create(zend_ast_kind kind, ...) {
        va_list va;
        zend_ast *ast;
    
        va_start(va, kind);
        ast = zend_ast_create_from_va_list(kind, 0, va);
        va_end(va);
    
        return ast;
    }
```

###### zend_ast_create_from_va_list

### yyparse

`bison` 语法分析工具一般以 `yyparse` 函数为入口

    # grep --color -rinw --include=*.h --include=*.c yyparse *
    Zend/zend_language_parser.c:63:#define yyparse zendparse

看来 PHP 给 `yyparse` 起了个别名，我们再看看代码里面哪些地方引用了 `zendparse`

    # grep --color -rinw --include=*.h --include=*.c zendparse *
    Zend/zend_language_scanner.c:587: if (!zendparse()) {
    Zend/zend_language_parser.c:436:int zendparse (void);

我们看到 `zend_language_scanner.c` 文件中使用了 `zenparse()`

```c
    static zend_op_array *zend_compile(int type) {
        ...
        if (!zendparse()) {
            ...
        }
        ...
    }
```

PHP 命名规范还是不错的，从 `zend_compile` 可以推测出这个函数应该是用来编译一段 PHP 代码，返回值 `zend_op_array` 估计是 PHP 虚拟机字节码数组

    # grep --color -rinw --include=*.h --include=*.c zend_compile
    
    Zend/zend_language_scanner.c:637: op_array = zend_compile
    Zend/zend_language_scanner.c:769: op_array = zend_compile

我们在 `zend_language_scanner.c` 的 637 和 769 行找到了两处对 `zend_compile` 的引用

    623 ZEND_API zend_op_array *compile_file(...)
    645 zend_op_array *compile_filename(int type, zval *filename)

顺藤摸瓜，我们接着查找 `compile_file`，`compile_filename`

    # grep --color -rinw --include=*.h --include=*.c compile_file
    Zend/zend.c:705 zend_compile_file = compile_file;
    Zend/zend.c:711 zend_compile_file = compile_file;

`zend.c` 705 在函数 `zend_startup` 内

    int zend_startup(zend_utility_functions *utility_functions, char **extensions)

### zend_compile

#### compiler globals（CG）

语法分析和中间代码生成过程中使用了 全局变量 `CG` 来保存中间结果（AST），搜索 `CG` 宏定义

```c
    /* Compiler */
    #ifdef ZTS
    # define CG(v) ZEND_TSRMG(compiler_globals_id, zend_compiler_globals *, v)
    #else
    # define CG(v) (compiler_globals.v)
    extern ZEND_API struct _zend_compiler_globals compiler_globals;
    #endif
```

这里有一个条件编译开关 `ZTS`，PHP 老鸟因该都知道 "要使用 `pthreads` 扩展，需要构建 PHP 时启用 ZTS （Zend Thread Safety）"，很自然想到：因为 `CG` 是一个全局变量，所以为了在多线程环境下保证线程安全，需要使用特殊机制（类似 Java 中的 TLS，thread local storage）访问 CG 中的字段

#### 实现

有了关于 `CG` 的铺垫，我们马上来看 `zend_compile` 函数的实现

```c
    // zend_language_scanner.c
    
    static zend_op_array *zend_compile(int type) {
        // 编译生成的字节码数组
        zend_op_array *op_array = NULL;
        zend_bool original_in_compilation = CG(in_compilation);
    
        CG(in_compilation) = 1;
        CG(ast) = NULL;
        CG(ast_arena) = zend_arena_create(1024 * 32);
        if (!zendparse()) {
            ...
            op_array = emalloc(sizeof(zend_op_array));
            init_op_array(op_array, type, INITIAL_OP_ARRAY_SIZE);
            ...
            zend_compile_top_stmt(CG(ast));
            zend_emit_final_return(type == ZEND_USER_FUNCTION);
            pass_two(op_array);
    
            CG(active_op_array) = original_active_op_array;
        }
        ...
        CG(in_compilation) = original_in_compilation;
    
        return op_array;
    }
```

这里忽略了一些细节，突出语法分析和字节码生成的流程

* 调用 `zend_parse` 进行语法分析，生成的 `AST` 根节点保存在 `GC(ast)` 中
* 为字节码数组分配内存
* 调用 `zend_compile_top_stmt` 根据 AST 生成字节码数组保存在 `GC(active_op_array)` 中
