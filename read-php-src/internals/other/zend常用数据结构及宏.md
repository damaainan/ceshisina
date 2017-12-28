# [【PHP笔记】 zend常用数据结构及宏][0]

 2016-03-13 16:15  537人阅读  

 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

 1、zend_execute_data:opcode执行期间非常重要的一个结构，记录着当前执行的zend_op、返回值、所属函数/对象指针、符号表等 

```c
    struct _zend_execute_data {
        const zend_op       *opline;           /* executed opline 指向第一条opcode */
        zend_execute_data   *call;             /* current call                   */
        zval                *return_value;
        zend_function       *func;             /* executed op_array              */
        zval                 This;
    #if ZEND_EX_USE_RUN_TIME_CACHE
        void               **run_time_cache;
    #endif
    #if ZEND_EX_USE_LITERALS
        zval                *literals;
    #endif
        zend_class_entry    *called_scope;
        zend_execute_data   *prev_execute_data;
        zend_array          *symbol_table;
    };
```

  
 2、zend_op:zend指令   


```c
//zend.compile.h  
struct _zend_op {  
    const void *handler;  //该指令调用的处理函数  
    znode_op op1; //操作数1  
    znode_op op2; //操作数2  
    znode_op result;   
    uint32_t extended_value;  
    uint32_t lineno;  
    zend_uchar opcode; //opcode指令编号  
    zend_uchar op1_type; //操作数1类型  
    zend_uchar op2_type;   
    zend_uchar result_type;  
};  
```


[0]: http://blog.csdn.net/pangudashu/article/details/50878488
[5]: #