# Hacking PHP7

  [Wenzhi Xu][0]   [04/17/201604/05/2017][1]  [PHP Internal][2]  [No Comments][3]

题目虽然有点夸张，但也恰到好处。本篇文章主要内容取自[Joe Watkins][4]的一篇博客[Hacking PHP7][5](但并不是完全照搬过来，取其精华，呵呵)，如果你正在学习PHP内核相关的知识，这个过程会相当枯燥，并且我猜你并不知道如何下手，我认为这篇文章会给你一个非常不错的路线图！

  
**准备工作**

1. PHP源码一份
1. 可以正常工作的Linux环境
1. 先不要归根究底，迅速走一遍流程

**PHP7中代码的执行流程**  
之所以说是PHP7，是因为PHP7的改动还是很大的，所以和之前的版本略有不同，想知道PHP之前版本文件的执行流程可以移步看下这里[深入理解PHP代码的执行的过程][6]。

- - -

所以你现在对PHP代码的执行流程已经有了最初的认识，为了能够再出一个更加清晰的介绍，我准备了一张流程图来加深你的印象。  
[![php code execute process](https://i2.wp.com/xuwenzhi.com/wp-content/uploads/2016/04/php-code-execute-process.png?resize=710%2C394)](https://i2.wp.com/xuwenzhi.com/wp-content/uploads/2016/04/php-code-execute-process.png)

此图也正是PHP5代码执行的流程，之所以这么说是因为PHP7的代码执行流程稍有不同，PHP7中多加了一道工序，叫做抽象语法树AST(参考[何为抽象语法树][7])，那到底这道工序加在哪里，就在Parsing之后也就是语法分析之后和编译之前，对于加了这道工序之后会不会导致PHP7程序执行变慢的问题，可以参考[PHP7 的抽象语法树（AST）带来的变化][8]。

- - -

好了，既然对于PHP7的执行过程都已经了如指掌，接下来就深入PHP7内核中去，在本例子中我们将在内核中实现一个新的函数，该函数的功能基本和print()函数的功能一样，功能虽然简单，但是对于理解PHP7内核很有意义，耐心走下去吧~

最终成果:

    ./sapi/cli/php -r "echo hackphp('hacking PHP7');"
    

**1.构造词法分析片段**  
编辑Zend/zend_language_scanner.l文件，该文件中你会发现大量T_开头的变量，你可以试着搜索一下你T_IF、T_ECHO或者T_INCLUDE，所以不难想象，在该文件中定义了PHP中的常用的关键词的片段，加入你在PHP代码中使用了if判断，那么词法分析就会映射到T_IF，为下一步语法分析做准备，好到此为止我打住，在如下位置添加代码(只需添加//…start和//…end之间的代码即可，后面同样)

```c
    <ST_IN_SCRIPTING>"else" {
        RETURN_TOKEN(T_ELSE);
    }
    //...start
    <ST_IN_SCRIPTING>"hackphp" {
        RETURN_TOKEN(T_HACKPHP);
    }
    //...end
    <ST_IN_SCRIPTING>"while" {
        RETURN_TOKEN(T_WHILE);
    }
```

**2.构造语法表达式**  
编辑Zend/zend_language_parser.y，在此文件中依然可以找到类似于T_IF这样的变量，此文件中将为我们的hackphp()函数构造一些表达式和一些语法解析规则，并为下一步生成语法树做准备!

```c
    %token T_INCLUDE      "include (T_INCLUDE)"
    //...start
    %token T_HACKPHP "hackphp (T_HACKPHP)" 
    //...end
    %token T_INCLUDE_ONCE "include_once (T_INCLUDE_ONCE)"
    
    //...省略
    
    %left '&'
    //...start
    %nonassoc T_HACKPHP
    //...end
    %nonassoc T_IS_EQUAL T_IS_NOT_EQUAL T_IS_IDENTICAL T_IS_NOT_IDENTICAL T_SPACESHIP
    
    //...省略
    
        |   T_STATIC function returns_ref backup_doc_comment '(' parameter_list ')' lexical_vars
            return_type '{' inner_statement_list '}'
                { $$ = zend_ast_create_decl(ZEND_AST_CLOSURE, $3 | ZEND_ACC_STATIC, $2, $4,
                      zend_string_init("{closure}", sizeof("{closure}") - 1, 0),
                      $6, $8, $11, $9); }
    //...start
        |   T_HACKPHP '(' expr ')' { $$ = zend_ast_create(ZEND_AST_HACKPHP, $3); }
    //...end
    ;
    
    function:
        T_FUNCTION { $$ = CG(zend_lineno); }
    ;
```

**3.通知AST解析我们的hackphp()函数**  
编辑Zend/zend_ast.h，在枚举变量_zend_ast_kind中添加

```c
    ZEND_AST_CONTINUE,
    //...start
    ZEND_AST_HACKPHP,
    //...end
```

**4.通知Zend VM编译我们的hackphp()函数**  
下面我们将通知Zend VM来编译我们的hackphp()，编辑Zend/zend_compile.c文件，首先找到zend_compile_expr()函数，增加一种case ZEND_AST_HACKPHP

```c
    //...省略
            case ZEND_AST_CLOSURE:
                zend_compile_func_decl(result, ast);
                return;
    //...start
            case ZEND_AST_HACKPHP:  
                zend_compile_hackphp(result, ast);  
                return;  
    //...end
            default:
                ZEND_ASSERT(0 /* not supported */);
        }    
    }
```

然后在zend_compile_expr()函数上方或者下方定义zend_compile_hackphp()函数

```c
    void zend_compile_hackphp(znode *result, zend_ast *ast) /* {{{ */
    {
           zend_ast *expr_ast = ast->child[0];
           znode expr_node;
           zend_compile_expr(&expr_node, expr_ast);
           zend_emit_op(result, ZEND_HACKPHP, &expr_node, NULL);
    } /* }}} */
```

**5.让Zend VM可以认识并执行hackphp()函数生成的OPCODE**

编辑Zend/zend_vm_def.h文件，我的建议是首先定位到文件底部，然后添加如下函数

```c
    ZEND_VM_HANDLER(184, ZEND_HACKPHP, ANY, ANY)   
    {  
     USE_OPLINE  
     zend_free_op free_op1;  
     zval *op1;  
    
     SAVE_OPLINE();  
     op1 = GET_OP1_ZVAL_PTR_UNDEF(BP_VAR_R);  
    
     if (Z_TYPE_P(op1) != IS_STRING) {  
      zend_throw_exception_ex(NULL, 0,  
       "hackphp expects a string !");  
      HANDLE_EXCEPTION();  
     }  
     ZVAL_COPY(EX_VAR(opline->result.var), op1);  
    
     FREE_OP1();  
     ZEND_VM_NEXT_OPCODE_CHECK_EXCEPTION();  
    }  
```

如果走到现在，就证明我们已经将所需要的所有代码都添加好了，如果你注意到了的话，代码的处理顺序也正好按照PHP7代码的执行顺序走的，所以也加深了你对PHP代码执行流程的印象，好，接下来我们将试验我们的hackphp()函数是否能够正常工作!

- - -

**编译PHP7**

按照如下顺序编译PHP7，如果报错的话，那么请回顾一下刚刚的过程看看有没有纰漏~

```
    ./buildconf
    ./configure
    make
```

**验证hackphp()!**

```
    ./sapi/cli/php -r "echo hackphp('hacking PHP7!');"
```

- - -

**结语**  
如果看到输出hacking PHP7!，那么恭喜你，你已经了解了PHP代码的执行流程并且了解了PHP7内核的一些很重要的知识。虽然对于大量的复制粘贴了一堆C代码，而且并不知道是什么意思，但是来个快速例子不得不说很爽快对吧！不过细想一下还是有收获的。通过此试验，对于如何学习PHP内核知识及阅读PHP源代码我相信你也许有了答案。

再次感谢Joe Watkins大神奉献了这么牛逼的博客!  
**参考网址**  
[Hacking PHP 7][5]

[0]: http://xuwenzhi.com/author/xuwenzhi/
[1]: http://xuwenzhi.com/2016/04/17/hacking-php7/
[2]: http://xuwenzhi.com/category/php/php-internal/
[3]: http://xuwenzhi.com/2016/04/17/hacking-php7/#respond
[4]: https://github.com/krakjoe
[5]: http://blog.krakjoe.ninja/2016/03/hacking-php-7.html?showComment=1460821720197#c1125948120735452921
[6]: http://www.colabug.com/thread-1024603-1-1.html
[7]: http://www.jianshu.com/p/8fa61a552ecf
[8]: http://www.tuicool.com/articles/iYJNB3V