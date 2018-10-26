## 稀里糊涂系列之list函数妙用

来源：[https://juejin.im/post/5bd12d8fe51d457a211c9200](https://juejin.im/post/5bd12d8fe51d457a211c9200)

时间 2018-10-25 13:44:38


实习也有小半年了，工作过程中真的学到了很多东西。今天一个在百词斩实习（前端）的死党跟我秀，说他昨天发的一篇文章是热榜第一，嘚瑟得都快上天了：[juejin.im/post/5bcd9e…][0]

我手痒了，所以把昨天工作过程中遇到的一个小问题拿出来抛砖引玉，如有不对的地方，请大佬斧正。


## 2、场景

我们在实现函数的时候，往往只有一个返回值，但有的时候这不能满足我们的需求。回想起当年c/c++的指针和引用，用得不亦乐乎，怎一个“爽”字了得啊。

当然，php也有引用，不知道为啥，反正我用得很不爽（强迫症）。但这不是本篇文章的重点，以后再说。

php语言的数组算是这门语言最有魅力的地方，在处理上述问题时，完全可以把所有的东西打包成一个数组返回。

例如：

```php
$x = null;
$y = null;
function foo($x, $y)
{
    $x = ['a', 'b', 'c'];
    $y = [23, 12, 8, 17];
    
    return [
        'x' => $x,
        'y' => $y
    ];
    
    // 或者这样：（这个函数也很方便，有兴趣的小盆友可以自己google）
    // return compact($x, $y);
}

$z = foo($x, $y);
$x = $z['x'];
$y = $z['y'];
```


## 3、list()函数

list()这个函数比较冷门吧，我是在看某个框架源码的时候发现的，大佬可以略过，菜鸟可以看一看。用法如下：

```php
array list ( mixed $var [, mixed $... ] )


```

官方文档中是这样写的：“    像`array()`一样，`list()`不是真正的函数，而是语言结构。`list()`可以在单次操作内就为一组变量赋值。    ”

Note:
list() 仅能用于数字索引的数组，并假定数字索引从 0 开始。
并且，php5.6 和 php7 版本的 list() 用法有变化

```php
// 5.6版本
list($z, $y, $x) = array('x', 'y', 'z');

// 7.0+版本
list($x, $y, $z) = array('x', 'y', 'z');
```

官方链接：


* [赋值操作的顺序发生了变化][1]    
* [list() 表达式不再可以完全为空][2]    
* [字符串无法再被拆包（unpack）][3]    
  


## 4、“茴”字的第二种写法

```php
$x = null;
$y = null;
function foo($x, $y)
{
    $x = ['a', 'b', 'c'];
    $y = [23, 12, 8, 17];
    
    return [$x, $y];
}

list($x, $y) = foo($x, $y);
```


## 5、后记

额，貌似也就只减少了几行代码......

其中提到了`compact`函数，还有与之对应的`extract`函数。

感兴趣的还有`explode`和`implode`函数......应该能或多或少提高点工作效率吧

写了一大堆又感觉啥都没写到，稀里糊涂的-_-！


[0]: https://link.juejin.im?target=https%3A%2F%2Fjuejin.im%2Fpost%2F5bcd9ebf6fb9a05d0f171688
[1]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Fmigration70.incompatible.php%23migration70.incompatible.variable-handling.list.order
[2]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Fmigration70.incompatible.php%23migration70.incompatible.variable-handling.list.empty
[3]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Fmigration70.incompatible.php%23migration70.incompatible.variable-handling.list.string