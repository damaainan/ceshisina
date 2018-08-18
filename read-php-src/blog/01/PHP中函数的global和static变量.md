## PHP中函数的global和static变量

来源：[https://fengyoulin.com/2018/03/06/php_global_static_variables/](https://fengyoulin.com/2018/03/06/php_global_static_variables/)

时间 2018-03-06 16:46:55


根据PHP官方文档，我们在自定义的function中，可以通过global关键字来访问全局作用域里声明的变量，还可以通过static关键字定义静态变量，即使代码执行到function之外，static变量也不会被销毁。接下来我们研究一下global和static在PHP7中的具体实现方式，演示代码用到了调试扩展    [zendump][0]
。


### 一、函数中的global变量

我们尝试在安装了    [zendump][0]
扩展的PHP7环境中执行如下代码：

```php
<?php
$a = 10;
function func01() {
    zendump_vars();
    global $a;
    ++$a;
    zendump_vars();
}
zendump_vars();
func01();
zendump_vars();
zendump_function('func01');
```

得到如下的输出结果：

``` 
vars(1): {
  $a ->
  zval(0x7f62a7e1e080) : long(10)
}
vars(1): {
  $a ->
  zval(0x7f62a7e1e140) : undefined
}
vars(1): {
  $a ->
  zval(0x7f62a7e1e140) -> reference(2) addr(0x7f62a7e620a8) zval(0x7f62a7e620b0) : long(11)
}
vars(1): {
  $a ->
  zval(0x7f62a7e1e080) -> reference(1) addr(0x7f62a7e620a8) zval(0x7f62a7e620b0) : long(11)
}
op_array("func01") func01() refcount(1) addr(0x7f62a7e0d0f8) vars(1) T(3) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/variables/variable_global.php) line(3,8)
OPCODE                             OP1                                OP2                                RESULT                             
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_BIND_GLOBAL                   $a                                 "a"                                                                   
ZEND_PRE_INC                       $a                                                                                                       
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_RETURN                        null
```

我们按照输出结果一步步进行分析，



* 在调用`func01`之前，首先在全局作用域内使用`zendump_vars()`函数打印所有局部变量，其实也就是全局变量。此时$a存储在地址0x7f62a7e1e080处，类型为long，值为10。    
* 调用`func01`函数，函数内第一条语句使用`zendump_vars()`函数打印所有局部变量，此时函数内部$a存储在地址0x7f62a7e1e140处，为undefined。    
* 函数执行过`global $a;`和`++$a;`语句后，通过`zendump_vars()`函数再次打印所有局部变量，此时函数内部$a的存储地址没有变化，但是变成了引用指向地址0x7f62a7e620a8处的引用结构，引用结构的引用计数为2，结构中包含一个值为11的long类型变量。    
* 函数返回后，再次在全局作用域内使用`zendump_vars()`函数打印所有局部变量，此时全局作用域内的$a也变成了指向地址0x7f62a7e620a8处的引用，此时引用结构中的引用计数为1。    
* 使用`zendump_function`函数打印`func01`的OPCODE。    
  

查看一下`func01`的OPCODE，去掉调用zendump调试函数而生成的OPCODE，我们发现与global关键字相对应的OPCODE为ZEND_BIND_GLOBAL，其有两个操作数，第一个是要声明的局部变量，第二个是对应的全局变量的名字。这个OPCODE具体做的操作就是通过全局变量表找到对应的全局变量，然后通过创建引用的方式将局部变量与对应的全局变量关联起来，所以上面函数中关联后的引用计数为2，当函数返回后引用计数变为1。

关于unset，在函数中unset掉一个global变量并不会销毁全局作用域内的变量，就是因为unset只是销毁了函数中的引用链接，而不会去销毁真实的数据。

有兴趣的话可以通过`zendump_symbols()`在全局作用域内打印变量表，其实全局变量就是全局作用域内的局部变量，有兴趣的话可以去看PHP7源码中zend_op_array的实现。PHP为了能够通过全局变量表查找这些全局范围内的局部变量，而又不把它们变成引用类型，还引入了IS_INDIRECT指针。这些都是PHP7中zend引擎的实现，强烈建议去看看。


### 二、函数中的static变量

我们尝试在安装了    [zendump][0]
扩展的PHP7环境中执行如下代码：

```php
<?php
function func02() {
    zendump_statics();
    zendump_vars();
    static $a = 10;
    ++$a;
    zendump_statics();
    zendump_vars();
}
func02();
func02();
zendump_function('func02');
```

得到如下的输出结果：

``` 
statics(1): {
  "a" =>
  zval(0x7f8035e6aa20) : long(10)
}
vars(1): {
  $a ->
  zval(0x7f8035e1e110) : undefined
}
statics(1): {
  "a" =>
  zval(0x7f8035e6aa20) -> reference(2) addr(0x7f8035e620a8) zval(0x7f8035e620b0) : long(11)
}
vars(1): {
  $a ->
  zval(0x7f8035e1e110) -> reference(2) addr(0x7f8035e620a8) zval(0x7f8035e620b0) : long(11)
}
statics(1): {
  "a" =>
  zval(0x7f8035e6aa20) -> reference(1) addr(0x7f8035e620a8) zval(0x7f8035e620b0) : long(11)
}
vars(1): {
  $a ->
  zval(0x7f8035e1e110) : undefined
}
statics(1): {
  "a" =>
  zval(0x7f8035e6aa20) -> reference(2) addr(0x7f8035e620a8) zval(0x7f8035e620b0) : long(12)
}
vars(1): {
  $a ->
  zval(0x7f8035e1e110) -> reference(2) addr(0x7f8035e620a8) zval(0x7f8035e620b0) : long(12)
}
op_array("func02") func02() refcount(1) addr(0x7f8035e0d0f8) vars(1) T(5) filename(/home/kylin/Downloads/php-7.2.2/ext/zendump/example/variables/variable_static.php) line(2,9)
OPCODE                             OP1                                OP2                                RESULT                             
ZEND_INIT_FCALL                                                       "zendump_statics"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_BIND_STATIC                   $a                                 "a"                                                                   
ZEND_PRE_INC                       $a                                                                                                       
ZEND_INIT_FCALL                                                       "zendump_statics"                                                     
ZEND_DO_ICALL                                                                                                                               
ZEND_INIT_FCALL                                                       "zendump_vars"                                                        
ZEND_DO_ICALL                                                                                                                               
ZEND_RETURN                        null
```

我们按照输出结果一步步进行分析，



* 第一次调用`func02`时，函数第一条语句使用`zendump_statics()`打印函数的静态变量表，这时表中key为”a”的变量已经存在，在地址0x7f8035e6aa20处，类型为long值为10。    
* 而`zendump_vars()`打印的局部变量$a，在地址0x7f8035e1e110处，尚未初始化，为undefined。    
* 函数执行过`static $a;`和`++$a;`语句后，通过`zendump_statics()`打印函数的静态变量表，key为”a”的变量地址没有改变，但是已经变成了引用，指向地址0x7f8035e620a8出的引用结构，引用计数为2，引用结构中的long类型值为11。    
* 通过`zendump_vars()`打印的局部变量$a，指向同一个引用结构。    
* 第二次调用`func02`时，函数第一条语句使用`zendump_statics()`打印函数的静态变量表，key为”a”的变量地址和指向的引用结构地址都没有变，引用计数为1。    
* 而`zendump_vars()`打印的局部变量$a，在地址0x7f8035e1e110处，尚未初始化，为undefined，与第一次调用时一样。    
* 函数执行过`static $a;`和`++$a;`语句后，通过`zendump_statics()`打印函数的静态变量表，key为”a”的变量地址和指向的引用结构地址都没有变，引用计数变为2，long的值为12。    
* 通过`zendump_vars()`打印的局部变量$a，指向同一个引用结构。    
* 函数返回后使用`zendump_function`函数打印`func02`的OPCODE。    
  

查看一下`func02`的OPCODE，去掉调用zendump调试函数而生成的OPCODE，我们发现与static关键字相对应的OPCODE为ZEND_BIND_STATIC，其有两个操作数，第一个是要声明的局部变量，第二个是对应的全局变量的名字。这个OPCODE具体做的操作就是通过op_array的静态变量表找到对应的静态变量，然后通过创建引用的方式将局部变量与对应的静态变量关联起来，所以上面函数中关联后的引用计数为2，在函数开始还没有关联的时候引用计数为1。

结合`zendump_statics()`的输出结果和OPCODE，其中并没有为静态变量赋初值的指令。我们可以确定，函数static变量的创建和赋初值是在编译期间就已经完成了的，运行时只是进行绑定操作。这也就说明了为什么PHP不允许使用一个函数的返回值来给静态变量赋初值，如官方文档中`static $int = sqrt(121); // wrong (as it is a function)`，因为初值需要在编译期间就能确定，所以不能为函数的返回值。

关于unset，在函数中unset掉一个static变量并不会真正的销毁静态变量，就是因为unset只是销毁了函数中的引用链接，而不会去销毁静态变量表中真实的数据。



[0]: https://github.com/php7th/zendump
[1]: https://github.com/php7th/zendump
[2]: https://github.com/php7th/zendump