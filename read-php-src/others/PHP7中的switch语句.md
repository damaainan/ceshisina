## PHP7中的switch语句

来源：[https://fengyoulin.com/2018/03/11/the_switch_statement_in_php7/](https://fengyoulin.com/2018/03/11/the_switch_statement_in_php7/)

时间 2018-03-11 10:23:22


PHP像大多数编程语言一样，也有着if/else、for循环等常用的流程控制语句。在本文中我们通过使用    [zendump][0]
扩展来打印调试信息和OPCODES，来分析PHP7中switch语句的底层实现。

首先，使用安装了    [zendump][0]
扩展的PHP7运行如下代码：

```php
<?php
$a = 'hello';
switch($a) {
	case 'hello':
		echo "hello\n";
		break;
	case 'world':
		echo "world\n";
		break;
}
$b = 3;
switch($b) {
	case 0:
		echo "0\n";
		break;
	case 1:
		echo "1\n";
		break;
	case 2:
		echo "2\n";
		break;
	case 3:
		echo "3\n";
		break;
	case 14:
		echo "14\n";
		break;
}
zendump_opcodes();
zendump_literals();
```

得到如下输出：

```
hello
3
op_array("") refcount(1) addr(0x7f5ece083300) vars(2) T(6) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/control_structures/switch.php) line(1,67)
OPCODE                             OP1                                OP2                                RESULT                             
ZEND_ASSIGN                        $a                                 "hello"                                                               
ZEND_SWITCH_STRING                 $a                                 array:0x7f5ece063540                                                  
ZEND_CASE                          $a                                 "hello"                            #tmp1                              
ZEND_JMPNZ                         #tmp1                              3                                                                     
ZEND_CASE                          $a                                 "world"                            #tmp1                              
ZEND_JMPNZ                         #tmp1                              3                                                                     
ZEND_JMP                           4                                                                                                        
ZEND_ECHO                          "hello\n"                                                                                                
ZEND_JMP                           2                                                                                                        
ZEND_ECHO                          "world\n"                                                                                                
ZEND_JMP                           0                                                                                                        
ZEND_ASSIGN                        $b                                 3                                                                     
ZEND_SWITCH_LONG                   $b                                 array:0x7f5ece0635a0                                                  
ZEND_CASE                          $b                                 0                                  #tmp3                              
ZEND_JMPNZ                         #tmp3                              9                                                                     
ZEND_CASE                          $b                                 1                                  #tmp3                              
ZEND_JMPNZ                         #tmp3                              9                                                                     
ZEND_CASE                          $b                                 2                                  #tmp3                              
ZEND_JMPNZ                         #tmp3                              9                                                                     
ZEND_CASE                          $b                                 3                                  #tmp3                              
ZEND_JMPNZ                         #tmp3                              9                                                                     
ZEND_CASE                          $b                                 14                                 #tmp3                              
ZEND_JMPNZ                         #tmp3                              9                                                                     
ZEND_JMP                           10                                                                                                       
ZEND_ECHO                          "0\n"                                                                                                    
ZEND_JMP                           8                                                                                                        
ZEND_ECHO                          "1\n"                                                                                                    
ZEND_JMP                           6                                                                                                        
ZEND_ECHO                          "2\n"                                                                                                    
ZEND_JMP                           4                                                                                                        
ZEND_ECHO                          "3\n"                                                                                                    
ZEND_JMP                           2                                                                                                        
ZEND_ECHO                          "14\n"                                                                                                   
ZEND_JMP                           0                                                                                                        
ZEND_INIT_FCALL                                                       "zendump_opcodes"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_literals"                                                    
ZEND_DO_ICALL                                                                                                                               
ZEND_RETURN                        1                                                                                                        
literals(21): {
  zval(0x7f5ece08e000) -> string(5,"hello") addr(0x7f5ece0019c0) refcount(1)
  zval(0x7f5ece08e010) -> array(2) addr(0x7f5ece063540) refcount(1) hash(8,2) bucket(8,2) data(0x7f5ece06a7a0)
  {
    "hello" =>
    zval(0x7f5ece06a7a0) : long(192)
    "world" =>
    zval(0x7f5ece06a7c0) : long(256)
  }
  zval(0x7f5ece08e020) -> string(5,"hello") addr(0x7f5ece0019c0) refcount(1)
  zval(0x7f5ece08e030) -> string(5,"world") addr(0x7f5ece001ac0) refcount(1)
  zval(0x7f5ece08e040) -> string(6,"hello\n") addr(0x7f5ece001a80) refcount(1)
  zval(0x7f5ece08e050) -> string(6,"world\n") addr(0x7f5ece001b00) refcount(1)
  zval(0x7f5ece08e060) : long(3)
  zval(0x7f5ece08e070) -> array(5) addr(0x7f5ece0635a0) refcount(1) hash(8,5) bucket(8,5) data(0x7f5ece06a660)
  {
    [0] =>
    zval(0x7f5ece06a660) : long(384)
    [1] =>
    zval(0x7f5ece06a680) : long(448)
    [2] =>
    zval(0x7f5ece06a6a0) : long(512)
    [3] =>
    zval(0x7f5ece06a6c0) : long(576)
    [14] =>
    zval(0x7f5ece06a6e0) : long(640)
  }
  zval(0x7f5ece08e080) : long(0)
  zval(0x7f5ece08e090) : long(1)
  zval(0x7f5ece08e0a0) : long(2)
  zval(0x7f5ece08e0b0) : long(3)
  zval(0x7f5ece08e0c0) : long(14)
  zval(0x7f5ece08e0d0) -> string(2,"0\n") addr(0x7f5ece001bc0) refcount(1)
  zval(0x7f5ece08e0e0) -> string(2,"1\n") addr(0x7f5ece001c00) refcount(1)
  zval(0x7f5ece08e0f0) -> string(2,"2\n") addr(0x7f5ece001c40) refcount(1)
  zval(0x7f5ece08e100) -> string(2,"3\n") addr(0x7f5ece001c80) refcount(1)
  zval(0x7f5ece08e110) -> string(3,"14\n") addr(0x7f5ece001cc0) refcount(1)
  zval(0x7f5ece08e120) -> string(15,"zendump_opcodes") addr(0x160dc40) refcount(1)
  zval(0x7f5ece08e130) -> string(16,"zendump_literals") addr(0x160d760) refcount(1)
  zval(0x7f5ece08e140) : long(1)
}
```

上面我们使用`zendump_opcodes()`打印了当前op_array的OPCODES，还使用`zendump_literals()`打印出了当前op_array中的字面量列表。下面解释一下几条关键的OPCODE：



* ZEND_SWITCH_STRING指令，该指令有两个操作数，第一个就是写在switch关键字后面括号里的字符串变量或字面量，第二个是一个jump table即跳转表，我们可以看到这个跳转表实际上是地址0x7fcfc5e63540处的一个array。下面的`zendump_literals()`打印出了这个array的内容，其中每个key所对应的都是一个long类型的数值，就是当前op_array的指令指针需要向前移动的字节数。在我当前的amd64平台，每个OPCODE结构占用的大小为32字节，所以可以计算出要调过多少条指令。这里的跳转是相对于ZEND_SWITCH_STRING指令自身的起始地址，不同于ZEND_JMP指令是相对于下一条指令的起始地址。    
* ZEND_SWITCH_LONG指令，该指令与上面的ZEND_SWITCH_STRING指令的工作原理基本一致，不同之处在于参数的类型。ZEND_SWITCH_LONG指令的第一个参数需要是一个long类型。
* ZEND_CASE指令，其实就是一个比较指令，根据比较结果接下来的条件跳转指令会执行不同的代码分支。
  

我们会注意到上面代码中的所有ZEND_CASE指令都不会被执行，因为前面的ZEND_SWITCH_STRING或ZEND_SWITCH_LONG指令直接通过跳转表就跳到了指定的位置。那么为什么还要有ZEND_CASE这样的指令呢？通过测试发现，当字符串型switch的case个数少于2个或者整型switch的case个数少于5个的时候，是不会有ZEND_SWITCH_STRING或ZEND_SWITCH_LONG指令生成的，届时只能通过ZEND_CASE指令逐个比较。这是为什么呢？应该是Zend引擎的实现者考虑到当case个数很少的时候，跳转表带来的性能改善不足以抵消构建一个跳转表造成的开销。



[0]: https://github.com/php7th/zendump
[1]: https://github.com/php7th/zendump