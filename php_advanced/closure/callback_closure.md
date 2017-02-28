### 回调函数

> 回调函数：Callback （即call then back 被主函数调用运算后会返回主函数），是指通过函数参数传递到其它代码的，某一块可执行代码的引用。

通俗的解释就是把函数作为参数传入进另一个函数中使用；PHP中有许多 “需求参数为函数” 的函数，像array_map,usort,call_user_func_array之类，他们执行传入的函数，然后直接将结果返回主函数。好处是函数作为值使用起来方便，而且代码简洁，可读性强。

### 匿名函数：

匿名函数，顾名思义，是没有一个确定函数名的函数，PHP将匿名函数和闭包视作相同的概念（匿名函数在PHP中也叫作闭包函数）。它的用法，当然只能被当作变量来使用了。

PHP中将一个函数赋值给一个变量的方式有四种：

* 我们经常会用到的：函数在外部定义/或PHP内置，直接将函数名作为字符串参数传入。注意：如果是类静态函数的话以CLASS::FUNC_NAME的方式传入。
* 使用create_function($args, $func_code);创建函数，会返回一个函数名。 $func_code为代码体，$args为参数字符串，以','分隔；
* 直接赋值: $func_name = function($arg){statement}；
* 直接使用匿名函数，在参数处直接定义函数，不赋给具体的变量值；


第一种方式因为是平常所用，不再多提；第二种类似eval()方法的用法，也被PHP官方列为不推荐使用的方式，而且其定义方式太不直观，我除了测试外，也没有在其他地方使用过，也略过不提。在这里重点说一下第三种和第四种用法；

后两种创建的函数就被称为匿名函数，也就是闭包函数， 第三种赋值法方式创建的函数非常灵活，可以通过变量引用。可以用 is_callable($func_name) 来测试此函数是否可以被调用， 也可以通过$func_name($var)来直接调用；而第四种方式创建的函数比较类似于JS中的回调函数，不需要变量赋值，直接使用；

另外要特别介绍的是 **use 关键词**，它可以在定义函数时，**用来引用父作用域中的变量***；用法为 function($arg) use($outside_arg) {function_statement}。其中$outside_arg 为父作用域中的变量，可以在function_statement使用。

这种用法用在回调函数**“ 参数值数量确定 ”**的函数中。 如usort需求$callback的参数值为两项，可是我们需要引入别的参数来影响排序怎么办呢？使用use()关键词就很方便地把一个新的变量引入$callback内部使用了。

- - -

# array_map/array_filter/array_walk:

把这三个函数放在一块是因为这三个函数在执行逻辑上比较类似，类似于下面的代码：

    $result = [];
    foreach($vars as $key=>$val){
        $item = callback();
        $result[] = $item;
    }
    return $result;

### array_walk($vars, $callback)

其callback应如下：

    $callback = function(&$val, $key[, $arg]){    
                doSomething($val);
            }

array_walk返回执行是否成功，是一个 布尔值 。对$value添加引用符号可以在函数内改变$value值，以达到改变$vars数组的效果。由于其$callback对参数数量要求为两项，array_walk不能传入strtolower/array_filter之类的$callback,若想实现类似功能，可以使用接下来要说的array_map()。

### array_walk_recursive($arr, $callback);

返回值和执行机制类似于array_walk;

其callback同array_walk，不同的是，如果$val是数组，函数会递归地向下处理$val；需要注意的是这样的话$val为数组的$key就会被忽略掉了。

### array_filter($vars, $callback, $flag);

其$callback类似于：

    $callback = function($var){
                　　return true or false;         
                }

array_filter会 过滤掉$callback执行时返回为false 的项目，array_filter返回过滤完成后的数组。

第三个参数 $flag决定其callback形参$var的值，不过这个可能是PHP高版本的特性，我的PHP5.5.3不支持，大家可以自行测试。默认传入数组每项的value,当flag为ARRAY_FILTER_USE_KEY传入数组每项的key，ARRAY_FILTER_USE_BOTH传入键和值;

### array_map($callback, &$var_as [,$var_bs...]);

其$callback类似于：

    $callback = function($var_a[, $var_b...]){
                doSomething($var_a, $var_b);
            }

返回$var_as经过callback处理后的数组（会改变原数组）；如果有多个数组的时候将两个数组同样顺序的项目传入处理，执行次数为参数数组中项目最多的个数；

- - -

# usort/array_reduce

把这两个函数放在一块，因为他们的执行机制都有些特殊。

### usort(&$vars, $callback)

$callback应该如下：

        callback = function($left, $right){
            $res = compare($left, $right);
            return $res;
        }

usort返回执行成功与否，bool值。用户自定义方法 比较$left 和 $right，其中$left和$right是$vars中的任意两项；

$left > $right时返回 正整数， $left < $right时返回 负整数， $left = $right时返回0；

$vars中的元素会被取出会被由小到大升序排序。 想实现降序排列，将$callback的 返回值反一下 就行了。

### array_reduce($vars ,$callable [, mixed $initial = NULL])

$callback应该如下：

        $callback = function($initial, $var){
            $initial = calculate($initail, $var);
            return $initial;
        }

初始值$initial默认为null，返回经过迭代后的initial；一定要将$initial返回，这样才能不停地改变$initial的值，实现迭代的效果。

这里顺便说一下map和reduce的不同：

map：将数组中的成员遍历处理，每次返回处理后的一个值，最后结果值为所有处理后值组成的 **多项数组** ；

reduce：遍历数组成员，每次使用数组成员结合初始值处理，并将初始值返回，即使用上一次执行的结果，配合下一次的输入继续产生结果，结果值为 **一项** ；

- - -

# call_user_func/call_user_func_array

### call_user_func[_array]($callback, $param)

$callback形如：

        $callback = function($param){
            $result = statement(); 
            return $result;
        }

返回值多种，具体看$callback。

可用此函数实现PHP的事件机制，其实并不高深，在判断条件达成，或程序执行到某一步后 call_user_func()就OK了。这个我在之前的博客中也有介绍到：[搭建自己的PHP框架心得（二）][1]

- - -

# 总结

其实以上$callback不用单独定义并使用变量引用，使用上面说过的第四种函数定义方式，直接在函数内定义，使用‘完全’匿名函数就行了。 如：

    usort($records, function mySortFunc($arg) use ($order){
        func_statement;
    });

是不是逼格满满呢？

OK，介绍了几个用法~希望对大家有帮助，如果有问题，欢迎指出，如果您喜欢，可以点下推荐~

博客持续更新，欢迎大家关注。

[0]: http://www.cnblogs.com/zhenbianshu/p/6063340.html
[1]: http://www.cnblogs.com/zhenbianshu/p/5352643.html