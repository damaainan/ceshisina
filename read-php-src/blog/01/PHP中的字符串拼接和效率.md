## PHP中的字符串拼接和效率

来源：[https://fengyoulin.com/2018/03/19/php_string_concat_rope/](https://fengyoulin.com/2018/03/19/php_string_concat_rope/)

时间 2018-03-19 17:45:43

 
在PHP中很多地方需要用到字符串与变量的拼接操作，可以在双引号或者Heredoc的字符串字面值中插入变量，还可以在运行时使用字符串拼接运算符“.”进行字符串的拼接。这两种方式在PHP中的实现原理稍有不同，也有着不太相同的适用场景，前者更类似于字符串的格式化，适用于在确定的字符串模板中嵌入变量，后者就是一个单纯的拼接运算符，更简单也更加灵活。本文主要探索一下两者运行时效率的差异。
 
### 一、字符串嵌入变量
 
我们首先使用Heredoc的方式，在字符串中嵌入4个变量，循环执行一千万次，计算出运行耗时。最后打印出OPCODES用来研究：
 
```php
<?php
$a = 'string';
$b = 'byte';
$c = 256;
$d = 'Unicode';
$t = time();
$i = 10000000;
while(--$i) {
	$str = <<<EOT
A $a is series of characters, where a character is the same as a $b. This means that PHP only supports a $c-character set, and hence does not offer native $d support.
EOT;
}
echo time() - $t . "s\n";
zendump_opcodes();
```
 
运行结果如下：
 
``` 
4s
op_array("") refcount(1) addr(0x7fbc9ea83300) vars(7) T(19) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/types/strings03.php) line(1,15)
OPCODE                             OP1                                OP2                                RESULT                             EXTENDED                           
ZEND_ASSIGN                        $a                                 "string"                                                                                                 
ZEND_ASSIGN                        $b                                 "byte"                                                                                                   
ZEND_ASSIGN                        $c                                 256                                                                                                      
ZEND_ASSIGN                        $d                                 "Unicode"                                                                                                
ZEND_INIT_FCALL                    80                                 "time"                                                                0                                  
ZEND_DO_ICALL                                                                                            #var4                                                                 
ZEND_ASSIGN                        $t                                 #var4                                                                                                    
ZEND_ASSIGN                        $i                                 10000000                                                                                                 
ZEND_JMP                           10                                                                                                                                          
ZEND_ROPE_INIT                                                        "A "                               #tmp8                              9                                  
ZEND_ROPE_ADD                      #tmp8                              $a                                 #tmp8                              1                                  
ZEND_ROPE_ADD                      #tmp8                              " is series of characters, where a character is the same as a "#tmp8                              2                                  
ZEND_ROPE_ADD                      #tmp8                              $b                                 #tmp8                              3                                  
ZEND_ROPE_ADD                      #tmp8                              ". This means that PHP only supports a "#tmp8                              4                                  
ZEND_ROPE_ADD                      #tmp8                              $c                                 #tmp8                              5                                  
ZEND_ROPE_ADD                      #tmp8                              "-character set, and hence does not offer native "#tmp8                              6                                  
ZEND_ROPE_ADD                      #tmp8                              $d                                 #tmp8                              7                                  
ZEND_ROPE_END                      #tmp8                              " support."                        #tmp7                              8                                  
ZEND_ASSIGN                        $str                               #tmp7                                                                                                    
ZEND_PRE_DEC                       $i                                                                    #var14                                                                
ZEND_JMPNZ                         #var14                             -12                                                                                                      
ZEND_INIT_FCALL                    80                                 "time"                                                                0                                  
ZEND_DO_ICALL                                                                                            #var15                                                                
ZEND_SUB                           #var15                             $t                                 #tmp16                                                                
ZEND_CONCAT                        #tmp16                             "s\n"                              #tmp17                                                                
ZEND_ECHO                          #tmp17                                                                                                                                      
ZEND_INIT_FCALL                    80                                 "zendump_opcodes"                                                     0                                  
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_RETURN                        1
```
 
循环执行耗时约为4秒，从OPCODES中筛选出与字符串操作相关的几个指令：ZEND_ROPE_INIT、ZEND_ROPE_ADD和ZEND_ROPE_END。从PHP7.2.2的源码中解读相关实现：
 
Zend引擎在编译阶段，会把上面这种字符串模板中嵌入变量的这种语句，按照在语句中出现的顺序，把被变量分割的字符串模板提取成一段段单独的字面量，然后根据分割出来字符串的个数和模板中嵌入的变量个数之和，分配一个zend_string指针数组，例如上例中总个数为9:
 
![][0]
 
下面解读3条指令的逻辑：
 
 
* ZEND_ROPE_INIT：通过结果操作数取得编译时分配的指针数组，然后取得右操作数并判断类型，按需进行必要的转换，最后将得到的zend_string指针填充到指针数组的第一个元素。 
 
![][1]
  
* ZEND_ROPE_ADD：通过左操作数取得编译时分配的指针数组，通过extended_value取得要操作的数组元素下标，再取得右操作数按需进行转换，将得到的zend_string指针填充到指针数组中。上例中的多条ZEND_ROPE_ADD指令逐渐填充整个指针数组。 
* ZEND_ROPE_END：前半部分包含ZEND_ROPE_ADD指令的所有逻辑，此时填充的应该是指针数组的组后一个元素，接下来将指针数组中的所有zend_string拼接成一个（也就是ROPE操作的最终结果），释放指针数组中的各个zend_string，最后返回拼接得到的结果。 
 
 
之所以如此设计，就是为了得到更好的运行时性能。因为频繁的字符串拼接带来的就是频繁的内存分配与释放，会影响性能。上面这种字符串模板嵌入变量，在编译时期就能确定元素的总个数，所以可以使用先缓存指针，最后一次拼接完成的方式来优化性能。
 
### 二、字符串拼接运算
 
我们将上例中的代码稍作修改，改成使用“.”运算符拼接整个字符串，来看一下效果：
 
```php
<?php
$a = 'string';
$b = 'byte';
$c = 256;
$d = 'Unicode';
$t = time();
$i = 10000000;
while(--$i) {
	$str = 'A ' . $a . ' is series of characters, where a character is the same as a ' . $b . '. This means that PHP only supports a ' . $c . '-character set, and hence does not offer native ' . $d . ' support.';
}
echo time() - $t . "s\n";
zendump_opcodes();
```
 
输出结果如下：
 
``` 
10s
op_array("") refcount(1) addr(0x7f2bc1a83300) vars(7) T(21) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/types/strings04.php) line(1,13)
OPCODE                             OP1                                OP2                                RESULT                             EXTENDED                           
ZEND_ASSIGN                        $a                                 "string"                                                                                                 
ZEND_ASSIGN                        $b                                 "byte"                                                                                                   
ZEND_ASSIGN                        $c                                 256                                                                                                      
ZEND_ASSIGN                        $d                                 "Unicode"                                                                                                
ZEND_INIT_FCALL                    80                                 "time"                                                                0                                  
ZEND_DO_ICALL                                                                                            #var4                                                                 
ZEND_ASSIGN                        $t                                 #var4                                                                                                    
ZEND_ASSIGN                        $i                                 10000000                                                                                                 
ZEND_JMP                           9                                                                                                                                           
ZEND_CONCAT                        "A "                               $a                                 #tmp7                                                                 
ZEND_CONCAT                        #tmp7                              " is series of characters, where a character is the same as a "#tmp8                                                                 
ZEND_CONCAT                        #tmp8                              $b                                 #tmp9                                                                 
ZEND_CONCAT                        #tmp9                              ". This means that PHP only supports a "#tmp10                                                                
ZEND_CONCAT                        #tmp10                             $c                                 #tmp11                                                                
ZEND_CONCAT                        #tmp11                             "-character set, and hence does not offer native "#tmp12                                                                
ZEND_CONCAT                        #tmp12                             $d                                 #tmp13                                                                
ZEND_CONCAT                        #tmp13                             " support."                        #tmp14                                                                
ZEND_ASSIGN                        $str                               #tmp14                                                                                                   
ZEND_PRE_DEC                       $i                                                                    #var16                                                                
ZEND_JMPNZ                         #var16                             -11                                                                                                      
ZEND_INIT_FCALL                    80                                 "time"                                                                0                                  
ZEND_DO_ICALL                                                                                            #var17                                                                
ZEND_SUB                           #var17                             $t                                 #tmp18                                                                
ZEND_CONCAT                        #tmp18                             "s\n"                              #tmp19                                                                
ZEND_ECHO                          #tmp19                                                                                                                                      
ZEND_INIT_FCALL                    80                                 "zendump_opcodes"                                                     0                                  
ZEND_DO_ICALL                                                                                                                                                                  
ZEND_RETURN                        1
```
 
这次大约耗时10秒，通过OPCODES发现，“.”运算符会被编译成ZEND_CONCAT指令，该指令除了会对操作数进行类型转换外，每次都会进行拼接操作，也就是内存的分配与释放，所以与上面的ROPE指令比起来，性能差一些。而且，ROPE操作中，无论嵌入多少个变量，最终都只有一次拼接，如果嵌入的变量不需要转换类型的话，执行时间应该不会随变量的个数增加而有显著增长。CONCAT操作则不然，应该会随“.”运算符的个数呈近似线性增长。
 


[0]: ./php_string_rope_buffer.png
[1]: ./php_zend_rope_init.png