# 浅析global与$GLOBALS的区别

[胖虎小李][0] 关注 2017.06.22 00:09  字数 819  

前两天看到一道关于global与$GLOBALS的面试题，觉得挺有趣，废话不多说，直接上代码（在简书编辑器上手打代码太凌乱了，所以就贴图啦~\(≧▽≦)/~）：

```php
<?php 
$var1 = 5;
$var2 = 0;
$var3 = 0;

function test_global(){
    global $var1,$var2;
    $var2 = &$var1;
}
function test_globals(){
    $GLOBALS['var3'] = & $GLOBALS['var1'];
}

test_global();
print $var2."<br/>";

test_globals();
print $var3."<br/>";

```

相信大家看完代码，都一定会有自己的答案，我在看完代码之后，斩钉截铁、毫不犹豫、毅然决然地表示，输出结果是**5**和**5**，骨感的现实告诉我，我还是 too young too simple，正确的答案其实是**0**和**5，**这道题其实就是考察`global`与`$GLOBALS`之间的区别，下面进行一下分析：

我们首先来看一个例子（抱歉，又贴图啦）

```php
<?php 
$a='a';
funvtion demo(){
    global $a;
    unset($a);
}
demo();
echo $a;
```

**执行结果为：a，**我就纳闷了，不是`unset`了吗，怎么还输出a呀，难道`unset`失效？我们在demo函数中，`echo $a`看一下，`unset`是否生效。

```php
<?php 
$a='a';
funvtion demo(){
    global $a;
    unset($a);
    echo $a;
}
demo();
echo $a;
```

**执行结果为：Notice: Undefined variable: a in G:\wamp\www\test.php on line8**，`unset`是生效的，这说明，demo中的`$a`与函数外的`$a`，其实是不一样的。经过查询PHP文档得知，函数内`global`的变量与函数外的全局变量，**其实是不一样的变量**，既然是不一样的变量，我们为了描述方便，分别给它们取不同的名字：**demo->$a**（函数内global的变量），**$a**（全局变量），`demo->$a`和$a指向的是**同一个物理内存地址**，当`unset demo->$a`时，对`$a`是没有影响的，`$a`还是指向那个内存地址，内存地址存放的值，还是之前的字符串a。当我们把demo函数中的 `global $a`替换成`$GLOBALS['a']`时，如下

```php
<?php 
$a='a';
funvtion demo(){
    unset($GLOBALS['a']);
}
demo();
echo $a;
```

**执行结果为：Notice: Undefined variable: a in G:\wamp\www\test.php on line**10,说明`$GLOBALS['a']`与函数外的`$a`就是同一个变量，不是双胞胎，不是亲兄弟，他就是同一个人呀！！！，所以在函数中`unset`，函数外就直接报错了。总结一下：函数内`global`和全局变量其实还是两个不一样的变量，只是两个变量之间是**引用关系**（推了推黑框眼镜），而`$GLOBALS['a']`和全局变量`$a`，就是同一个变量。有了这个结论，我们再来看上面的面试题，就豁然开朗啦（为了大家不用再倒回去看代码，此处再贴一次代码）

```php
<?php 
$var1 = 5;
$var2 = 0;
$var3 = 0;

function test_global(){
    global $var1,$var2;
    $var2 = &$var1;
}
function test_globals(){
    $GLOBALS['var3'] = & $GLOBALS['var1'];
}

test_global();
print $var2."<br/>";

test_globals();
print $var3."<br/>";
```

test_global函数中，用的是global，所以test_global->$var1, $var2（global $var1, $var2）和函数外的$var1, $var2其实是不同的变量，只是存在引用关系。test_global函数中，改变的只是test_global->$var1, $var2（global $var1, $var2）的指向关系，并不影响函数外的$var1, $var2的指向，所以第一个输出是**0**。test_globals函数，用的是$GLOBALS['var3']，上文已经说过，`$GLOBALS['var3']`和`$var3`就是同一个变量，那改变了`$GLOBALS['var3']`的指向，就是改变`$var3`的指向了嘛，所以，第二个输出就是**5**。

[0]: /u/f2c2d3d12d98
