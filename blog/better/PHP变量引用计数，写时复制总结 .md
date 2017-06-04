# PHP变量引用计数，写时复制总结 

 28 January 2014

_学习自[php-variables][0]_

在PHP的变量存储结构ZVAL中，有两个成员：refcount__gc和is_ref_gc用于实现引用计数和写时复制，这样既能节省内存，又能减少计算量。

当执行如下代码时：

    


    <?php
    $a = "this is";
    $b = "variable";
    $c = 42;
    ?>

三个变量各自拥有一个ZVAL，如下图所示：  
![demo][1]

当执行如下代码时，将会发生写时复制：



    <?php
    $a = "this is";
    $b = $a;
    $c = $a;
    $c = 42;
    unset($b);
    unset($c);
    ?>

$a、$b、$c先指向同一个ZVAL，在修改$c的值时，就需要为$c复制一个ZVAL了，当执行unset()操作时，将会断开变量和ZVAL的”关联“。refcount__gc减1，如果refcount__gc为0，那么PHP的就会将其当作垃圾，在适当的时候回收。如图：  
![demo][2]

当变量作为参数传递给函数时，想想会怎样：

    

 

    <?php
    function do_something($s)
    {
        $s = 'was';
        return $s;
    }
    
    $a = 'this is';
    $b = do_something($a);
    ?>

其实变量作为参数传递时，也可以看作是一个变量赋值过程（$s = $a），当这时，refcount__gc却要加2，为什么？看下图：  
![demo][3]

再来看看使用&引用变量的例子：



    <?php
    $a = "this is";
    $b = &$a;
    $c = &$a;
    $b = 42;
    unset($c);
    unset($a);
    ?>

当使用&引用变量时，refcount__gc加1。另外，is_ref__gc会被**设置**为1，不管有1个还是更多个变量使用&引用了同一个ZVAL。看图：  
![demo][4]

还有两个有趣的例子，&操作符也会引起ZVAL复制：

    


    <?php
    $a = "this is";
    $b = $a;
    $c = &$b;
    ?>

![demo][5]

    


    <?php
    $a = "this is";
    $b = &$a;
    $c = $a;
    ?>

![demo][6]

那么如果通过引用传递参数，又会如何？



    <?php
    function do_something(&$s)
    {
        $s = 'was';
        return $s;
    }
    $a = 'this is';
    $b = do_something($a);
    ?>

从PHP 5.4开始已经不允许使用foo(&$var)形式的函数调用了，这将会导致致命错误！see [Passing by Reference][7]  
![demo][8]

那么通过引用返回时，又会怎样？

   
    <?php
    function &find_node($key, &$node)
    {
        $item = & $node[$key];
        return $item;
    }
    
    $tree = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
    );
    $node = & find_node(3, $tree);
    $node = 'drie';
    ?>

一张很长的图：  
![demo][9]

![demo][10]

![demo][11]

对于在函数内使用global关键字引用的全局变量：


    <?php
    $var = 'one';
    
    function update_var($val)
    {
        global $var;
        unset($var);
        global $var;
        $var = $val;
    }
    
    update_var("four");
    ?>

![demo][12]

![demo][13]

![demo][14]

最后这个例子是为了说明，在PHP里，有些时候定义函数返回引用是毫无意义的，并不能达到节省内存的目的，所以要慎用函数引用返回，先看代码：

    

    <?php
    function &definition()
    {
        $def = array('id' => 'name', 'def' => 42);
        return $def;
    }
    
    $def = &definition();
    ?>

如果对引用计数，写时复制有了充分了解，那么应该能看出上面的代码实际执行时和不使用引用返回是一样的效果，并不会多消耗ZVAL。  
![demo][15]

[0]: http://derickrethans.nl/talks/phparch-php-variables-article.pdf
[1]: ./img/201401280101.png
[2]: ./img/201401280102.png
[3]: ./img/201401280103.png
[4]: ./img/201401280104.png
[5]: ./img/201401280105.png
[6]: ./img/201401280106.png
[7]: http://cn2.php.net/manual/en/language.references.pass.php
[8]: ./img/201401280107.png
[9]: ./img/201401280108.png
[10]: ./img/201401280111.png
[11]: ./img/201401280112.png
[12]: ./img/201401280109.png
[13]: ./img/201401280113.png
[14]: ./img/201401280114.png
[15]: ./img/201401280110.png