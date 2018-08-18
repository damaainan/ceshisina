## PHP的可变变量

来源：[https://fengyoulin.com/2018/03/10/php_variable_variables/](https://fengyoulin.com/2018/03/10/php_variable_variables/)

时间 2018-03-10 20:49:31

 
根据PHP官方手册上可变变量一节所讲，可变变量就是变量的名字本身也是个变量，可以用两个$符的写法进行创建。下面我们使用 [zendump][2] 对可变变量的工作原理进行一些研究分析。
 
### 一、全局作用域
 
使用安装了 [zendump][2] 扩展的PHP7运行如下代码：
 
```php
<?php
$a = 'hello';
$$a = 10;
zendump_vars();
zendump_symbols();
zendump_opcodes();
```
 
得到如下输出：
 
```c
vars(1): {
  $a ->
  zval(0x7f685821e080) -> string(5,"hello") addr(0x7f68582019c0) refcount(1)
}
symbols(9): {
  ...
  "a" =>
  zval(0x7f68582641e0) -> zval(0x7f685821e080) -> string(5,"hello") addr(0x7f68582019c0) refcount(1)
  "hello" =>
  zval(0x7f6858264200) : long(10)
}
op_array("") refcount(1) addr(0x7f6858283300) vars(1) T(6) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/variables/variable_variables01.php) line(1,7)
OPCODE                             OP1                                OP2                                RESULT                             
ZEND_ASSIGN                        $a                                 "hello"                                                               
ZEND_FETCH_W                       $a                                                                    #var1                              
ZEND_ASSIGN                        #var1                              10                                                                    
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_symbols"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_opcodes"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_RETURN                        1
```
 
我们使用3个zendump调试函数输出相关的数据：
 
 
* `zendump_vars()`打印出编译阶段静态分配的变量，可以确定编译时分配的只有$a。  
* `zendump_symbols()`打印当前作用域对应的变量表，其中”a”通过IS_INDIRECT类型的指针关联到栈帧上的$a变量，”hello”就是我们通过`$$a = 10;`语句运行时动态创建的可变变量，类型为long值为10。  
* `zendump_opcodes()`打印出当前op_array的OPCODES。  
 
 
在OPCODES中有一个ZEND_FETCH_W指令，该指令根据指定的名称在变量表里查找或新建相应的变量用于接下来的写操作，也就是下面的赋值指令。但是根据指令的参数可以发现，#var1应该是分配在栈帧上的，不应该是在变量表里吗？而且通过上面打印出来的变量地址也能够确定”hello”所指向的long(10)确实不在栈帧上。那么#var1应该就是指向地址0x7f6858264200处的一个IS_INDIRECT类型的指针，这也就是ZEND_FETCH_W指令的工作原理。
 
可变变量创建后，结构图如下所示：
 
![][0]
 
### 二、函数内部
 
使用安装了 [zendump][2] 扩展的PHP7运行如下代码：
 
```php
<?php
function func01() {
    zendump_symbols();
    zendump_vars();
    $b = 'world';
    $$b = 20;
    zendump_symbols();
    zendump_vars();
}
func01();
zendump_function('func01');
```
 
得到如下输出：
 
```
null
vars(1): {
  $b ->
  zval(0x7fdeafe1e0f0) : undefined
}
symbols(2): {
  "b" =>
  zval(0x7fdeafe6a660) -> zval(0x7fdeafe1e0f0) -> string(5,"world") addr(0x7fdeafe01a00) refcount(1)
  "world" =>
  zval(0x7fdeafe6a680) : long(20)
}
vars(1): {
  $b ->
  zval(0x7fdeafe1e0f0) -> string(5,"world") addr(0x7fdeafe01a00) refcount(1)
}
op_array("func01") func01() refcount(1) addr(0x7fdeafe0d0f8) vars(1) T(7) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/variables/variable_variables02.php) line(2,9)
OPCODE                             OP1                                OP2                                RESULT                             
ZEND_INIT_FCALL                                                       "zendump_symbols"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_ASSIGN                        $b                                 "world"                                                               
ZEND_FETCH_W                       $b                                                                    #var3                              
ZEND_ASSIGN                        #var3                              20                                                                    
ZEND_INIT_FCALL                                                       "zendump_symbols"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_RETURN                        null
```
 
结合输出对代码逐步进行分析：
 
 
* 函数`func01`第一行代码使用`zendump_symbols()`打印当前作用域的变量表结果为空，此时函数的变量表还没有创建。  
* 第二行语句使用`zendump_vars()`打印栈帧上静态分配的变量，只有$b，为undefined。  
* 执行`$b = 'world';`和`$$b = 20;`语句。  
* 再次使用`zendump_symbols()`打印当前作用域的变量表，发现”b”为IS_INDIRECT指针，指向栈帧上的$b，”world”为我们使用`$$b = 20;`语句运行时动态创建的可变变量，类型为long值为20。  
* 再次使用`zendump_vars()`打印栈帧上静态分配的变量，依然只有$b，只不过已经赋值。  
* 最后使用`zendump_function()`打印`func01`的OPCODES。  
 
 
我们在OPCODES中又发现了ZEND_FETCH_W指令，工作原理上面已经讲过，#var3应该为IS_INDIRECT类型。本例与上面全局作用域不太相同的地方在于，函数的变量表如果没有用到默认是不会被创建出来的，也就是说因为ZEND_FETCH_W指令的需要才创建了变量表，这点不同于全局变量表。不像ZEND_FETCH_W这种比较高级的指令，更一般的指令是通过变量在栈帧上的offset来进行访问的，不需使用变量表。如果我们把上面的`$$b = 20;`一句代码注释掉，那么第二次打印变量表时还会为null。
 
### 三、对象的属性
 
官方手册中也提及了对象的属性，虽然具体实现上与上面两种情况不完全相同，但是原理十分相似。使用安装了 [zendump][2] 扩展的PHP7运行如下代码：
 
```php
<?php
class ExampleClass {
    public $a;
}
$o = new ExampleClass;
zendump($o);
$a = 'a';
$o->$a = 'hello';
$o->b = 'world';
zendump($o);
zendump_opcodes();
```
 
得到如下输出：
 
```
zval(0x7fabace1e190) -> object(ExampleClass) addr(0x7fabace634e0) refcount(2) {
  default_properties(1) {
    $a =>
    zval(0x7fabace63508) : null
  }
}
zval(0x7fabace1e190) -> object(ExampleClass) addr(0x7fabace634e0) refcount(2) {
  default_properties(1) {
    $a =>
    zval(0x7fabace63508) -> string(5,"hello") addr(0x7fabace01b80) refcount(1)
  }
  properties(2) {
    "a" =>
    zval(0x7fabace6a660) -> zval(0x7fabace63508) -> string(5,"hello") addr(0x7fabace01b80) refcount(1)
    "b" =>
    zval(0x7fabace6a680) -> string(5,"world") addr(0x7fabace01c40) refcount(1)
  }
}
op_array("") refcount(1) addr(0x7fabace83300) vars(2) T(10) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/variables/variable_variables03.php) line(1,14)
OPCODE                             OP1                                OP2                                RESULT                             
ZEND_NOP                                                                                                                                    
ZEND_NEW                           "ExampleClass"                                                        #var1                              
ZEND_DO_FCALL                                                                                                                               
ZEND_ASSIGN                        $o                                 #var1                                                                 
ZEND_INIT_FCALL                                                       "zendump"                                                             
ZEND_SEND_VAR                      $o                                                                                                       
ZEND_DO_ICALL                                                                                                                               
ZEND_ASSIGN                        $a                                 "a"                                                                   
ZEND_ASSIGN_OBJ                    $o                                 $a                                                                    
ZEND_OP_DATA                       "hello"                                                                                                  
ZEND_ASSIGN_OBJ                    $o                                 "b"                                                                   
ZEND_OP_DATA                       "world"                                                                                                  
ZEND_INIT_FCALL                                                       "zendump"                                                             
ZEND_SEND_VAR                      $o                                                                                                       
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_opcodes"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_RETURN                        1
```
 
逐步进行分析：
 
 
* 使用`zendump()`函数打印对象$o，其中在类定义中显式声明的$a属于默认属性，未赋值时默认为null。  
* 使用可变属性为$o的a属性赋值，并且为类中没有定义的属性b赋值。 
* 再次使用`zendump()`函数打印对象$o，可以看到默认属性$a已经被赋值，并且此时对象$o的属性表已经被创建，其中键”a”的值是一个IS_INDIRECT类型的指针，指向默认属性$a,键”b”为`$o->b = 'world';`语句运行时动态创建的属性。  
* 最后使用`zendump_opcodes()`打印出当前上下文的OPCODES。  
 
 
因为运行时添加了新的属性，所以对象的属性表被创建。ZEND_ASSIGN_OBJ和ZEND_OP_DATA指令是一个固定组合，起到了同上面ZEND_FETCH_W和ZEND_ASSIGN指令组合类似的功能。
 
对象$o的属性在内存中的结构如下图所示：
 
![][1]
 


[2]: https://github.com/php7th/zendump
[3]: https://github.com/php7th/zendump
[4]: https://github.com/php7th/zendump
[5]: https://github.com/php7th/zendump
[0]: ./php7_variable_variables_layout.png
[1]: ./php7_object_properties_layout.png