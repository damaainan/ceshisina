## PHP-7.1 源代码学习：字节码生成 之 概述


### 前言

字节码生成（编译）的代码主要集中在 `zend_compile.c` ，文件中包含大量的 `zend_compile_xxx` 函数，基本上一个函数对应 语法规则文件 `zend_language_parser.y` 一个非终结符，`zend_compile_top_stmt` 函数是所有 `zend_compile_xxx` 函数的入口

### 数据结构

#### zend_op

`zend_op` 结构体是 PHP 字节码抽象

```c
    // zend_compile.h
    
    struct _zend_op {
        const void *handler;
        znode_op op1;
        znode_op op2;
        znode_op result;
        uint32_t extended_value;
        uint32_t lineno;
        zend_uchar opcode;
        zend_uchar op1_type;
        zend_uchar op2_type;
        zend_uchar result_type;
    }
```

#### zend_op_array

`zend_op_array` 结构体并没有像名字那样简单 `zend op array`，它包含了大量的字段供虚拟机在运行时使用

```c
    // zend_compile.h
    
    struct _zend_op_array {
    }
```

### zend_compile_top_stmt

`zend_compile_top_stmt` 一如既往的简单，直观，相比之前看 `ruby` 源代码而言，感觉 欧美工程师 可能真的比 岛国工程师 牛叉一些

```c
    // zend_compile.c
    
    void zend_compile_top_stmt(zend_ast *ast) {
        if (!ast) {
            return;
        }
    
        if (ast->kind == ZEND_AST_STMT_LIST) {
            zend_ast_list *list = zend_ast_get_list(ast);
            uint32_t i;
            for (i = 0; i < list->children; ++i) {
                zend_compile_top_stmt(list->child[i]);
            }
            return;
        }
    
        zend_compile_stmt(ast);
    
        if (ast->kind != ZEND_AST_NAMESPACE && ast->kind != ZEND_AST_HALT_COMPILER) {
            zend_verify_namespace();
        }
        if (ast->kind == ZEND_AST_FUNC_DECL || ast->kind == ZEND_AST_CLASS) {
            CG(zend_lineno) = ((zend_ast_decl *) ast)->end_lineno;
            zend_do_early_binding();
        }
    }
```

`ast` 是抽象语法树（AST）的根节点（参考 [PHP-7.1 源代码学习：语法分析 之 概述][0]），函数首先对 `ast` 进行参数验证，针对 `ZEND_AST_STMT_LIST` 节点类型进行递归调用，然后调用 `zend_compile_stmt` 编译 各个 `stmt`，这个流程和语法规则也是精确对应的：

```c
    start: top_statement_list { CG(ast) = $1 }
    top_statement_list:
            top_statement_list top_statement { $$ = zend_ast_list_add($1, $2) }
        |    /* empty */ { $$ = zend_ast_create_list(0, ZEND_AST_STMT_LIST); }
    top_statement:
        statement
        ...
    
```

#### zend_compile_stmt

`zend_compile_stmt` 函数基本上就是根据 `zend_ast` 类型（kind）进行 switch case

```c
    void zend_compile_stmt(zend_ast *ast) {
        if (!ast) {
            return;
        }
    
        CG(zend_lineno) = ast->lineno;
        ...
        switch (ast->kind) {
            case ZEND_AST_STMT_LIST:
                zend_compile_stmt_list(ast);
                break;
            case ZEND_AST_GLOBAL:
                zend_compile_global_var(ast);
                break;
            ...
            default:
            {
                znode result;
                zend_compile_expr(&result, ast);
                zend_do_free(&result);
            }
        }
        ...
    }
```

[0]: https://segmentfault.com/a/1190000008221706
