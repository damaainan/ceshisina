## (PHP7内核剖析-5) PHP代码的编译

来源：[https://segmentfault.com/a/1190000014234234](https://segmentfault.com/a/1190000014234234)

 **`1.PHP代码的编译`** 

PHP的解析过程任务就是将PHP代码转化为opcode数组，代码里的所有信息都保存在opcode中，然后将opcode数组交给zend引擎执行，opcode就是内核具体执行的命令，比如赋值、加减操作、函数调用等，每一条opcode都对应一个处理handle，这些handler是提前定义好的C函数。
![][0]


 **`2.PHP代码->抽象语法树(AST)`** 

```
PHP使用re2c、bison完成这个阶段的工作:
    re2c: 词法分析器，将输入分割为一个个有意义的词块，称为token
    bison: 语法分析器，确定词法分析器分割出的token是如何彼此关联的
```

![][1]

```
词法、语法解析过程

1.yyparse调用yylex，当读取到一个合法的token时，返回值为token类型
2.yylex调用lex_scan将合法的token值存储在zval上
3.yyparse将token类型与token值构造抽象语法树,最后将根节点保存到CG(compiler_globals ，Zend编译器相关的全局变量)的ast中
```


 **`3.AST节点结构`** 

```c
typedef struct _zend_ast   zend_ast;

//普通节点类型
struct _zend_ast {
    zend_ast_kind kind;  //节点类型
    zend_ast_attr attr;  //节点附加属性
    uint32_t lineno;    //行号
    zend_ast *child[1];  //子节点数组
};

//普通节点类型，但有子节点的个数
typedef struct _zend_ast_list {
    zend_ast_kind kind; //节点类型
    zend_ast_attr attr; //节点附加属性
    uint32_t lineno; //行号
    uint32_t children; //子节点数量
    zend_ast *child[1];//子节点数组
} zend_ast_list;

//函数、类的ast节点结构
typedef struct _zend_ast_decl {
    zend_ast_kind kind; //节点类型
    zend_ast_attr attr; //节点附加属性
    uint32_t start_lineno; //开始行号
    uint32_t end_lineno;   //结束行号
    uint32_t flags;
    unsigned char *lex_pos;
    zend_string *doc_comment;
    zend_string *name;
    zend_ast *child[4]; //类中会将继承的父类、实现的接口以及类中的语句解析保存在child中
} zend_ast_decl;
```


 **`4.zend_op_array`** 

![][2]

```c
zend_op *opcodes; //opcode指令数组
zval *literals; //字面量(常量)数组，这些都是在PHP代码定义的一些值
zend_string **vars; //PHP变量名数组,根据变量编号可以获取相应的变量
```

```c
//opcode指令结构
struct _zend_op {
    const void *handler; //指令执行handler
    znode_op op1;   //操作数1
    znode_op op2;   //操作数2
    znode_op result; //返回值
    uint32_t extended_value; 
    uint32_t lineno; 
    zend_uchar opcode;  //opcode指令
    zend_uchar op1_type; //操作数1类型
    zend_uchar op2_type; //操作数2类型
    zend_uchar result_type; //返回值类型
};
```


 **`5.handler处理函数`** 

handler为每条opcode对应的C语言编写的处理过程,所有opcode对应的处理过程定义在zend_vm_def.h中,opcode的处理过程有三种不同的提供形式：CALL、SWITCH、GOTO，默认方式为CALL
```c
CALL:把每种opcode负责的工作封装成不同的function，然后执行器循环调用执行
SWITCH:把所有的处理方式写到一个switch下，然后通过case不同的opcode执行具体的操作
GOTO:把所有opcode的处理方式通过C语言里面的label标签区分开，然后执行器执行的时候goto到相应的位置处理
```


 **`6.抽象语法树->Opcodes`** 

```c
void zend_compile_top_stmt(zend_ast *ast){
    ....
    if (ast->kind == ZEND_AST_STMT_LIST) { //第一次进来一定是这种类型
        zend_ast_list *list = zend_ast_get_list(ast);
        uint32_t i;
        for (i = 0; i < list->children; ++i) {
            zend_compile_top_stmt(list->child[i]);//list各child语句相互独立，递归编译
        }
        return;
    }
    //各语句编译入口
    zend_compile_stmt(ast);
    ....
}

1.zend_compile_top_stmt接收语法树，首先判断节点类型是否为ZEND_AST_STMT_LIST(表示当前节点下
有多个独立的节点),若是则进行递归
2.当递归结束后，调用zend_compile_stmt进行编译成opcodes
```

[0]: ./img/bVU0Uf.png
[1]: ./img/bV7Szf.png
[2]: ./img/bV7SHH.png