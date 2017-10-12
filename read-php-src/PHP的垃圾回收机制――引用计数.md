# PHP的垃圾回收机制――引用计数

 时间 2017-10-12 12:41:08  

原文[http://developer.51cto.com/art/201710/553823.htm][1]


![][4]

每个php变量存在一个叫"zval"的变量容器中。一个zval变量容器，除了包含变量的类型和值，还包括两个字节的额外信息。第一个是"is_ref"，是个bool值，用来标识这个变量是否是属于引用集合(reference set)。通过这个字节，php引擎才能把普通变量和引用变量区分开来，由于php允许用户通过使用&来使用自定义引用，zval变量容器中还有一个内部引用计数机制，来优化内存使用。第二个额外字节是"refcount"，用以表示指向这个zval变量容器的变量(也称符号即symbol)个数。所有的符号存在一个符号表中，其中每个符号都有作用域(scope)，那些主脚本(比如：通过浏览器请求的的脚本)和每个函数或者方法也都有作用域。

当一个变量被赋常量值时，就会生成一个zval变量容器，如下例这样：

#### 例1 生成一个新的zval容器

```php
    <?php  
     
    $a = "new string"; 
     
    ?> 
```

在上例中，新的变量a，是在当前作用域中生成的。并且生成了类型为 string 和值为new string的变量容器。在额外的两个字节信息中，"is_ref"被默认设置为 FALSE，因为没有任何自定义的引用生成。"refcount" 被设定为 1，因为这里只有一个变量使用这个变量容器. 注意到当"refcount"的值是1时，"is_ref"的值总是FALSE. 如果你已经安装了» Xdebug，你能通过调用函数 xdebug_debug_zval()显示"refcount"和"is_ref"的值。

#### 例2 显示zval信息

```php
    <?php  
     
    xdebug_debug_zval('a'); 
     
    ?> 
```

以上例程会输出：

    a: (refcount=1, is_ref=0)='new string' 
    

把一个变量赋值给另一变量将增加引用次数(refcount).

#### 例3 增加一个zval的引用计数

```php
    <?php 
     $a = "new string"; 
     
    $b = $a; 
     
    xdebug_debug_zval( 'a' ); 
     
    ?> 
```

以上例程会输出：

    a: (refcount=2, is_ref=0)='new string' 
    

这时，引用次数是2，因为同一个变量容器被变量 a 和变量 b关联.当没必要时，php不会去复制已生成的变量容器。变量容器在”refcount“变成0时就被销毁. 当任何关联到某个变量容器的变量离开它的作用域(比如：函数执行结束)，或者对变量调用了函数 unset()时，”refcount“就会减1，下面的例子就能说明:

#### 例4 减少引用计数

```php
    <?php  
     
    $a = "new string"; 
     
    $c = $b = $a; 
     
    xdebug_debug_zval( 'a' ); 
     
    unset( $b, $c ); 
     
    xdebug_debug_zval( 'a' ); 
     
    ?> 
```

以上例程会输出：

    a: (refcount=3, is_ref=0)='new string' 
     
    a: (refcount=1, is_ref=0)='new string' 
    

如果我们现在执行 unset($a);，包含类型和值的这个变量容器就会从内存中删除。

复合类型(Compound Types)

当考虑像 array和object这样的复合类型时，事情就稍微有点复杂. 与 标量(scalar)类型的值不同，array和 object类型的变量把它们的成员或属性存在自己的符号表中。这意味着下面的例子将生成三个zval变量容器。

#### 例5 Creating a array zval

```php
    <?php  
     
    $a = array( 'meaning' => 'life', 'number' => 42 ); 
     
    xdebug_debug_zval( 'a' ); 
     
    ?> 
```

以上例程的输出类似于：

    a: (refcount=1, is_ref=0)=array ( 
       'meaning' => (refcount=1, is_ref=0)='life', 
       'number' => (refcount=1, is_ref=0)=42 
    ) 
    

图示:

![][5]

一个简单数组的zval

这三个zval变量容器是: a，meaning和 number。增加和减少”refcount”的规则和上面提到的一样. 下面, 我们在数组中再添加一个元素,并且把它的值设为数组中已存在元素的值:

#### 例6 添加一个已经存在的元素到数组中

```php
    <?php  
     
    $a = array( 'meaning' => 'life', 'number' => 42 ); 
     
    $a['life'] = $a['meaning']; 
     
    xdebug_debug_zval( 'a' ); 
     
    ?> 
```

以上例程的输出类似于：

    a: (refcount=1, is_ref=0)=array ( 
     
    'meaning' => (refcount=2, is_ref=0)='life', 
     
    'number' => (refcount=1, is_ref=0)=42, 
     
    'life' => (refcount=2, is_ref=0)='life' 
     
    ) 
    

图示：

![][6]

带有引用的简单数组的zval

从以上的xdebug输出信息，我们看到原有的数组元素和新添加的数组元素关联到同一个"refcount"2的zval变量容器. 尽管 Xdebug的输出显示两个值为'life'的 zval 变量容器，其实是同一个。 函数xdebug_debug_zval()不显示这个信息，但是你能通过显示内存指针信息来看到。

删除数组中的一个元素，就是类似于从作用域中删除一个变量. 删除后,数组中的这个元素所在的容器的“refcount”值减少，同样，当“refcount”为0时，这个变量容器就从内存中被删除，下面又一个例子可以说明：

#### 例7 从数组中删除一个元素

```php
    <?php  
     
    $a = array( 'meaning' => 'life', 'number' => 42 ); 
     
    $a['life'] = $a['meaning']; 
     
    unset( $a['meaning'], $a['number'] ); 
     
    xdebug_debug_zval( 'a' ); 
     
    ?> 
```

以上例程的输出类似于：

    a: (refcount=1, is_ref=0)=array ( 
     
    'life' => (refcount=1, is_ref=0)='life' 
     
    ) 
    

现在，当我们添加一个数组本身作为这个数组的元素时，事情就变得有趣，下个例子将说明这个。例中我们加入了引用操作符，否则php将生成一个复制。

#### 例8 把数组作为一个元素添加到自己

```php
    <?php  
     
    $a = array( 'one' ); 
     
    $a[] =&$a; 
     
    xdebug_debug_zval( 'a' ); 
     
    ?> 
```

以上例程的输出类似于：

    a: (refcount=2, is_ref=1)=array ( 
     
    0 => (refcount=1, is_ref=0)='one', 
     
    1 => (refcount=2, is_ref=1)=... 
     
    ) 
    

图示：

![][7]

自引用(curcular reference,自己是自己的一个元素)的数组的zval

能看到数组变量 (a) 同时也是这个数组的第二个元素(1) 指向的变量容器中“refcount”为 2。上面的输出结果中的"..."说明发生了递归操作, 显然在这种情况下意味着"..."指向原始数组。

跟刚刚一样，对一个变量调用unset，将删除这个符号，且它指向的变量容器中的引用次数也减1。所以，如果我们在执行完上面的代码后，对变量$a调用unset, 那么变量 $a 和数组元素 "1" 所指向的变量容器的引用次数减1, 从"2"变成"1". 下例可以说明:

#### 例9 Unsetting $a

    (refcount=1, is_ref=1)=array ( 
       0 => (refcount=1, is_ref=0)='one', 
       1 => (refcount=1, is_ref=1)=... 
    ) 
    

图示：

![][8]

Zvals after removal of array with a circular reference demonstrating the memory leak

清理变量容器的问题(Cleanup Problems)

尽管不再有某个作用域中的任何符号指向这个结构(就是变量容器)，由于数组元素“1”仍然指向数组本身，所以这个容器不能被清除 。因为没有另外的符号指向它，用户没有办法清除这个结构，结果就会导致内存泄漏。庆幸的是，php将在脚本执行结束时清除这个数据结构，但是在php清除之前，将耗费不少内存。如果你要实现分析算法，或者要做其他像一个子元素指向它的父元素这样的事情，这种情况就会经常发生。当然，同样的情况也会发生在对象上，实际上对象更有可能出现这种情况，因为对象总是隐式的被引用。

如果上面的情况发生仅仅一两次倒没什么，但是如果出现几千次，甚至几十万次的内存泄漏，这显然是个大问题。这样的问题往往发生在长时间运行的脚本中，比如请求基本上不会结束的守护进程(deamons)或者单元测试中的大的套件(sets)中。后者的例子：在给巨大的eZ(一个知名的PHP Library) 组件库的模板组件做单元测试时，就可能会出现问题。有时测试可能需要耗用2GB的内存，而测试服务器很可能没有这么大的内存。


[1]: http://developer.51cto.com/art/201710/553823.htm

[4]: ./img/Z7vEFvy.jpg
[5]: ./img/ANfIN3Q.jpg
[6]: ./img/eeiaqmQ.jpg
[7]: ./img/7Fv2M3F.jpg
[8]: ./img/uqMnEzN.jpg