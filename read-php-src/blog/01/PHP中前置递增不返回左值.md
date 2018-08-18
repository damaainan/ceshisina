## PHP中前置递增不返回左值

来源：[https://fengyoulin.com/2018/04/24/php_pre_increment_operator_do_not_return_lvalue/](https://fengyoulin.com/2018/04/24/php_pre_increment_operator_do_not_return_lvalue/)

时间 2018-04-24 10:28:39


首先来讲，一般我们对“左值”的理解就是可以出现在赋值运算符的左侧的标识符，也就是可以被赋值。这样讲也许并不十分确切，在不同的语言中对左值的定义也不尽相同。在这里我们讨论前置递增（和递减）运算符的场景下，说前置递增需要返回左值，更简明的来讲就是要返回变量自身，或者自身的引用。


### 一、分析问题

在PHP中遇到这个问题，最初是因为写了类似如下的代码：

```php
<?php
function func01(&$a) {
    echo $a . PHP_EOL;
    $a += 10;
}
$n = 0;
func01(++$n);
echo $n . PHP_EOL;
```

按照写C++的经验，上面代码应该打印出1和11，但是PHP出乎意料的打印出了1和1。为了一探究竟，我使用    [zendump][0]
扩展中的`zendump_opcodes()`函数打印出上面代码的OPCODES：

```
[root@c962bf018141 php-7.2.2]# sapi/cli/php -f ~/php/func_arg_pre_inc_by_ref.php 
1
1
op_array("") refcount(1) addr(0x7f7445c812a0) vars(1) T(5) filename(/root/php/func_arg_pre_inc_by_ref.php) line(1,12)
OPCODE                             OP1                                OP2                                RESULT                             EXTENDED                           
ZEND_NOP                                                                                                                                                                       
ZEND_ASSIGN                        $n                                 0                                                                                                        
ZEND_INIT_FCALL                    128                                "func01"                                                              1                                  
ZEND_PRE_INC                       $n                                                                    #var1                                                                 
ZEND_SEND_VAR_NO_REF               #var1                              1                                                                                                        
ZEND_DO_UCALL                                                                                                                                                                  
ZEND_CONCAT                        $n                                 "\n"                               #tmp3                                                                 
ZEND_ECHO                          #tmp3                                                                                                                                       
ZEND_INIT_FCALL                    80                                 "zendump_opcodes"                                                     0                                  
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_RETURN                        1
```

通过OPCODES来看，主要问题因该是在`ZEND_PRE_INC`这条指令上，因为其返回值是`#var1`而不是`$n`，因为OPCODE和虚拟机栈上的变量布局是在编译阶段确定的，也就是说Zend引擎在编译时并没有使用`$n`自身作为返回值。通过查看zend_vm_def.h中`ZEND_PRE_INC`和`ZEND_PRE_DEC`指令的具体实现，可以发现运行时返回的`#var1`也并不是`$n`的引用，而是使用`ZVAL_COPY_VALUE`和`ZVAL_COPY`宏进行了值拷贝。

看一下这个2012年的Bug：    [https://bugs.php.net/bug.php?id=62778][1]
，看起来也是一个有C++经验的开发者提交的。当时是PHP 5.4，至今仍处于Open状态，看来官方并不准备修复此Bug，或许认为这并不是一个Bug。因为PHP并没有标准化委员会，也没有语法白皮书，所以还真不好说这到底是不是一个Bug。正因如此，有的时候遇到一些出乎意料的现象也不好找到权威的资料，只能去研究其实现。


### 二、尝试修改

因为不太习惯这种实现，就尝试着自己进行修改，我在PHP 7.2.2的源码中对`ZEND_PRE_INC`和`ZEND_PRE_DEC`两条指令做了如下修改，主要思路就是如果左操作数不是引用类型的话，将其转换为引用类型（`ZVAL_MAKE_REF`宏会判断），然后让指令的结果操作数引用左操作数：

```
[root@c962bf018141 Zend]# diff zend_vm_def.h zend_vm_def.h.bak 
1211c1211
<       zval *var_ptr, *varptr;
---
>       zval *var_ptr;
1213c1213
<       varptr = var_ptr = GET_OP1_ZVAL_PTR_PTR_UNDEF(BP_VAR_RW);
---
>       var_ptr = GET_OP1_ZVAL_PTR_PTR_UNDEF(BP_VAR_RW);
1218,1219c1218
<                       ZVAL_MAKE_REF(varptr);
<                       ZVAL_COPY(EX_VAR(opline->result.var), varptr);
---
>                       ZVAL_COPY_VALUE(EX_VAR(opline->result.var), var_ptr);
1241,1242c1240
<               ZVAL_MAKE_REF(varptr);
<               ZVAL_COPY(EX_VAR(opline->result.var), varptr);
---
>               ZVAL_COPY(EX_VAR(opline->result.var), var_ptr);
1253c1251
<       zval *var_ptr, *varptr;
---
>       zval *var_ptr;
1255c1253
<       varptr = var_ptr = GET_OP1_ZVAL_PTR_PTR_UNDEF(BP_VAR_RW);
---
>       var_ptr = GET_OP1_ZVAL_PTR_PTR_UNDEF(BP_VAR_RW);
1260,1261c1258
<                       ZVAL_MAKE_REF(varptr);
<                       ZVAL_COPY(EX_VAR(opline->result.var), varptr);
---
>                       ZVAL_COPY_VALUE(EX_VAR(opline->result.var), var_ptr);
1283,1284c1280
<               ZVAL_MAKE_REF(varptr);
<               ZVAL_COPY(EX_VAR(opline->result.var), varptr);
---
>               ZVAL_COPY(EX_VAR(opline->result.var), var_ptr);
```

这样修改主要是受到了Zend引擎实现`global`和`static`变量方式的启发，这非常类似于我们在C++中为一个class重载++运算符，最后要返回自身的引用。我也曾尝试使用`IS_INDIRECT`类型指针，但是会引起core dump，似乎`IS_INDIRECT`类型在Zend引擎中只是被用在某些特定的场景下，不像引用类型这样得到广泛支持。

修改完成后使用zend_vm_gen.php重新生成代码并成功make，再回去执行上面的代码，确实如预期的输出了1和11：

```
[root@c962bf018141 php-7.2.2]# sapi/cli/php -f ~/php/func_arg_pre_inc_by_ref.php 
1
11
vars(1): {
  $n ->
  zval(0x7fd182c1d080) -> reference(1) addr(0x7fd182c5f078) zval(0x7fd182c5f080) : long(11)
}
```

使用    [zendump][0]
扩展中的`zendump_vars()`函数来打印局部变量，可以发现`$n`确实被转成了引用类型。


### 三、验证修改

现在担心的就是如此修改会不会引入什么Bug，尤其是PHP会不会有什么特性依赖于不返回左值的那种实现。我在修改过的和未经修改的PHP工程下分别执行了make test，并对结果做了对比，发现确实有两个test没有通过：

```
Check key execution order with new. [tests/lang/engine_assignExecutionOrder_007.phpt]
Execution ordering with comparison operators. [tests/lang/engine_assignExecutionOrder_009.phpt]
```

进一步分析没有通过的测试代码，发现这两个test中都在同一个语句内使用了多个前置递增运算符，如下所示：

```php
<?php
$a[2][3] = 'stdClass';
$a[$i=0][++$i] = new $a[++$i][++$i];
print_r($a);

$o = new stdClass;
$o->a = new $a[$i=2][++$i];
$o->a->b = new $a[$i=2][++$i];
print_r($o);
```

再次使用`zendump_opcodes()`函数打印出OPCODES：

```
[root@c962bf018141 php-7.2.2]# sapi/cli/php -f php/testcase007.php 
op_array("") refcount(1) addr(0x7fba8347f2a0) vars(3) T(36) filename(/root/php/testcase007.php) line(1,13)
OPCODE                             OP1                                OP2                                RESULT                             EXTENDED                           
ZEND_INIT_FCALL                    80                                 "zendump_opcodes"                                                     0                                  
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_FETCH_DIM_W                   $a                                 2                                  #var1                                                                 
ZEND_ASSIGN_DIM                    #var1                              3                                                                                                        
ZEND_OP_DATA                       "stdClass"                                                                                                                                  
ZEND_ASSIGN                        $i                                 0                                  #var3                                                                 
ZEND_PRE_INC                       $i                                                                    #var5                                                                 
ZEND_PRE_INC                       $i                                                                    #var7                                                                 
ZEND_PRE_INC                       $i                                                                    #var9                                                                 
ZEND_FETCH_DIM_R                   $a                                 #var7                              #var8                                                                 
ZEND_FETCH_DIM_R                   #var8                              #var9                              #var10                                                                
ZEND_FETCH_CLASS                                                      #var10                             #var11                             
ZEND_NEW                           #var11                                                                #var12                             0                                  
ZEND_DO_FCALL                                                                                                                                                                  
ZEND_FETCH_DIM_W                   $a                                 #var3                              #var4                                                                 
ZEND_ASSIGN_DIM                    #var4                              #var5                                                                                                    
ZEND_OP_DATA                       #var12                                                                                                                                      
ZEND_INIT_FCALL                    96                                 "print_r"                                                             1                                  
ZEND_SEND_VAR                      $a                                 1                                                                                                        
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_NEW                           "stdClass"                                                            #var15                             0                                  
ZEND_DO_FCALL                                                                                                                                                                  
ZEND_ASSIGN                        $o                                 #var15                                                                                                   
ZEND_ASSIGN                        $i                                 2                                  #var19                                                                
ZEND_PRE_INC                       $i                                                                    #var21                                                                
ZEND_FETCH_DIM_R                   $a                                 #var19                             #var20                                                                
ZEND_FETCH_DIM_R                   #var20                             #var21                             #var22                                                                
ZEND_FETCH_CLASS                                                      #var22                             #var23                             
ZEND_NEW                           #var23                                                                #var24                             0                                  
ZEND_DO_FCALL                                                                                                                                                                  
ZEND_ASSIGN_OBJ                    $o                                 "a"                                                                                                      
ZEND_OP_DATA                       #var24                                                                                                                                      
ZEND_ASSIGN                        $i                                 2                                  #var28                                                                
ZEND_PRE_INC                       $i                                                                    #var30                                                                
ZEND_FETCH_DIM_R                   $a                                 #var28                             #var29                                                                
ZEND_FETCH_DIM_R                   #var29                             #var30                             #var31                                                                
ZEND_FETCH_CLASS                                                      #var31                             #var32                             
ZEND_NEW                           #var32                                                                #var33                             0                                  
ZEND_DO_FCALL                                                                                                                                                                  
ZEND_FETCH_OBJ_W                   $o                                 "a"                                #var26                                                                
ZEND_ASSIGN_OBJ                    #var26                             "b"                                                                                                      
ZEND_OP_DATA                       #var33                                                                                                                                      
ZEND_INIT_FCALL                    96                                 "print_r"                                                             1                                  
ZEND_SEND_VAR                      $o                                 1                                                                                                        
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_RETURN                        1
```

上面紧跟在`ZEND_ASSIGN`后面的`ZEND_PRE_INC`指令和3条紧邻的`ZEND_PRE_INC`指令，足够说明问题。说明Zend引擎在编译的时候，首先对中括号内的数组下标进行求值，按照从左往右的顺序，然后才对外层的表达式进行求值。如果前置递增运算符返回变量引用的话，像上面这样赋值之后立刻执行前置递增指令，或者连续执行3条前置递增指令，得到的结果操作数都引用同一个变量，值也就都是最后一次递增后的值，所以后续的逻辑自然就不对了。至于Zend引擎为什么这样实现，目前我也不得而知，猜测可能是为了让语法解析器实现起来更加简单。


### 总结

为了能让前置递增、递减运算符返回变量引用，还要让以上特性能够正常工作，就要修改Zend引擎的编译器，对于上面这种场景使其按照合理的顺序生成指令代码。但是修改编译器牵涉太大，会带来多少问题就更难预期了。所以对于这个问题的探索就暂时告一段落，起码我们对Zend引擎的了解又深入了一些。

就算是能让前置递增、递减运算符返回变量引用，其适用场景也是十分有限的，比如像下面这样的语句，在PHP中是根本无法通过编译的，如果不修改编译器还是无法真正体现返回引用在语法层面带来的便利。或许我们也可以认为，没必要为了这不是很常用的语法而引入太多的复杂性。

```php
$b = &++$a;
++$a += 10;
++(++$b);
```



[0]: https://github.com/php7th/zendump
[1]: https://bugs.php.net/bug.php?id=62778
[2]: https://github.com/php7th/zendump