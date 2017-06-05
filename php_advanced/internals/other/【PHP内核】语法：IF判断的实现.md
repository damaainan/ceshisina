# [【PHP内核】语法：IF判断的实现][0]

 标签： [内核][1][php][2][php内核][3][zend引擎][4]

 2016-04-01 16:14  458人阅读  

 本文章已收录于：


 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

面试[PHP][9]时经常碰到一种判断各种类型的空值是否为true的题：

    $a = '';
    $a = null
    $a = false;
    
    if($a){...}
    if(isset($a)){...}
    if(empty($a)){...}
    ...


由下面的例子我们来简单看下zend引擎中对if是怎么处理的：

    <?php
    $a = ''; //array();
    if($a){
        echo "Y";
    }


这里例子比较简单，结果将什么也不输出。**(文中涉及代码均为php-7.0.4版本)**

之前的文章介绍过zend执行阶段的入口zend_execute函数，我们直接从这里开始，不熟悉的可以翻一下前面的文章。   
编译生成的opcodes如下：   
![这里写图片描述][10]

  
其中opcode=38是$a = ”的执行操作，opcode=43是if的操作，下面具体看这一步是如何执行的。   
根据opcode及两个操作数类型可以找到对应的handler为：**ZEND_JMPZ_SPEC_CV_HANDLER**

    //zend_vm_execute.h #28307
    static ZEND_OPCODE_HANDLER_RET ZEND_FASTCALL ZEND_JMPZ_SPEC_CV_HANDLER(ZEND_OPCODE_HANDLER_ARGS)
    {   
        USE_OPLINE
    
        zval *val;
    
        val = _get_zval_ptr_cv_undef(execute_data, opline->op1.var);
    
        if (Z_TYPE_INFO_P(val) == IS_TRUE) { 
            ZEND_VM_SET_NEXT_OPCODE(opline + 1);
            ZEND_VM_CONTINUE();
        } else if (EXPECTED(Z_TYPE_INFO_P(val) <= IS_TRUE)) {
            if (IS_CV == IS_CV && UNEXPECTED(Z_TYPE_INFO_P(val) == IS_UNDEF)) {
                SAVE_OPLINE();
                GET_OP1_UNDEF_CV(val, BP_VAR_R);
                ZEND_VM_JMP(OP_JMP_ADDR(opline, opline->op2));
            } else {
                ZEND_VM_SET_OPCODE(OP_JMP_ADDR(opline, opline->op2));
                ZEND_VM_CONTINUE();
            }
        }
    
        SAVE_OPLINE();
        if (i_zend_is_true(val)) {
            opline++;
        } else {
            opline = OP_JMP_ADDR(opline, opline->op2);
        }
    
        if (UNEXPECTED(EG(exception) != NULL)) {
            HANDLE_EXCEPTION();
        }
        ZEND_VM_JMP(opline);
    }


从这个函数可以看出if的执行过程：如果条件为true的话则opline++，顺序执行下一条opcode（即if内语句），否则进行跳转，跳过if内语句直接执行if外语句。

**i_zend_is_true**这个函数就是用来判断各种类型的值是否为真，前面那部分是判断是否为bool型，是的话则直接处理。

    //zend_operators.h #283
    static zend_always_inline int i_zend_is_true(zval *op)
    {   
        int result = 0;
    
    again:
        switch (Z_TYPE_P(op)) {
            case IS_TRUE:
                result = 1;
                break;
            //数值类型long、double直接判断即可，与c用法相同
            case IS_LONG:
                if (Z_LVAL_P(op)) {
                    result = 1;
                }
                break;
            case IS_DOUBLE:
                if (Z_DVAL_P(op)) {
                    result = 1;
                }
                break;
            //字符串类型根据长度判断：长度>1，或=1且不为'0'为true，所以上面那个例子'' => false
            case IS_STRING:
                if (Z_STRLEN_P(op) > 1 || (Z_STRLEN_P(op) && Z_STRVAL_P(op)[0] != '0')) {
                    result = 1;
                }
                break;
            //数组类型根据数组元素的个数判断：大于0即为真
            case IS_ARRAY:
                if (zend_hash_num_elements(Z_ARRVAL_P(op))) { // (Z_ARRVAL_P(op))->nNumOfElements
                    result = 1;
                }
                break;
            case IS_OBJECT:
                result = zend_object_is_true(op);
                break;
            //资源类型实际就是整形（后续会专门介绍资源类型），所以直接判断即可
            case IS_RESOURCE:
                if (EXPECTED(Z_RES_HANDLE_P(op))) {
                    result = 1;
                }
                break;
            //引用类型则根据指向的值判断
            case IS_REFERENCE:
                op = Z_REFVAL_P(op);
                goto again;
                break;
            default:
                break;
        }
        return result;
    }


isset、empty函数后续补充……

[0]: http://blog.csdn.net/pangudashu/article/details/51036842
[1]: http://www.csdn.net/tag/%e5%86%85%e6%a0%b8
[2]: http://www.csdn.net/tag/php
[3]: http://www.csdn.net/tag/php%e5%86%85%e6%a0%b8
[4]: http://www.csdn.net/tag/zend%e5%bc%95%e6%93%8e

[9]: http://lib.csdn.net/base/php
[10]: ../img/20160401153054416.png