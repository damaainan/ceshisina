## 巧用 PHP 数组函数

来源：[https://juejin.im/post/5b67b50a6fb9a04fda4e3902](https://juejin.im/post/5b67b50a6fb9a04fda4e3902)

时间 2018-08-06 11:25:44


PHP 的数组是一种很强大的数据类型，与此同时 PHP 内置了一系列与数组相关的函数可以很轻易的实现日常开发的功能。但是我发现好像很多小伙伴都忽略了内置函数的作用（比如我自己就编写过一些有关数组操作的代码然后发现PHP自带了/(ㄒoㄒ)/），善用 PHP 内置函数能极大的提高开发效率和运行效率（内置函数都是用 C 写的效率比用 PHP 写的高很多），所以本文便总结了一些在常见场景中利用 PHP 内置函数的实现方法。此外如果想更深入的学习有关 PHP 数组函数最好还是去查 PHP 手册！    [点我看官方数组函数手册][0]


## 0x01 取指定键名

对于某些关联数组，有时候我们只想取指定键名的那部分，比如数组为`['id' => 1, 'name' => 'zane', 'password' => '123456']`此时若只想取包含 id 和 name 的部分该怎么实现呢？下面直接贴代码。

```php
<?php
$raw = ['id' => 1, 'name' => 'zane', 'password' => '123456'];
// 自己用 PHP 实现
function onlyKeys($raw, $keys) {
    $new = [];
    foreach ($raw as $key => $val) {
        if (in_array($key, $keys)) {
            $new[$key] = $val;
        }
    }
    
    return $new;
}
// 用 PHP 内置函数实现
function newOnlyKeys($array, $keys) {
    return array_intersect_key($array, array_flip($keys));
}
var_dump(onlyKeys($raw, ['id', 'name']));
// 结果 ['id' => 1, 'name' => 'zane']
var_dump(newOnlyKeys($raw, ['id', 'name']));
// 结果 ['id' => 1, 'name' => 'zane']
```

很明显简洁很多有木有！不过`array_intersect_key`和`array_flip`是什么鬼？这里简单的介绍一下这两个函数的作用，首先是`array_flip`函数，这个函数的功能是「将数组的键和值对调」，也就是键名变成值，值变成键名。我们传递的`$keys`参数经过这个函数便从`[0 => 'id', 1 => 'name']`转变为了`['id' => 0, 'name' => 1]`。这样做的目的是为了向`array_intersect_key`函数服务，`array_intersect_key`函数的功能是「使用键名比较计算数组的交集」，也就是返回第一个参数数组中与其他参数数组相同键名的值。这样便实现了取指定键名的功能 ~(≧▽≦)/~啦！当然要详细了解这两个函数的功能还是要查 PHP 官方手册：array_flip     [array_intersect_key][1]


## 0x02 移除指定键名

有了上一个例子做铺垫，这个就简单讲讲啦，道理是大同小异滴。

```php
<?php
$raw = ['id' => 1, 'name' => 'zane', 'password' => '123456'];
// 用 PHP 内置函数实现
function removeKeys($array, $keys) {
    return array_diff_key($array, array_flip($keys));
}
// 移除 id 键
var_dump(removeKeys($raw, ['id', 'password']));
// 结果 ['name' => 'zane']
```

和上一个例子相比本例只是将`array_intersect_key`函数改为`array_diff_key`，嗯……相信大家能猜出来这个函数的功能「使用键名比较计算数组的差集」，刚好和`array_intersect_key`的功能相反而已。官方手册：array_diff_key


## 0x03 数组去重

这个相信大家都有这个需求，当然 PHP 也内置了array_unique 函数供给大家使用，如下例：

```php
<?php
$input = ['you are' => 666, 'i am' => 233, 'he is' => 233, 'she is' => 666];
$result = array_unique($input);
var_dump($result);
// 结果 ['you are' => 666, 'i am' => 233]
```

嘿，用这个函数就能解决大部分问题了，但是有时候你可能会觉得它不够快，原因如下：

array_unique()先将值作为字符串排序，然后对每个值只保留第一个遇到的键名，接着忽略所有后面的键名。

因为这个函数会先将数组进行排序，所以速度可能在某些场景达不到预期的要求。

现在我们可以祭出我们的黑科技`array_flip`函数，众所周知 PHP 里数组的键名是唯一的，所以在键名和值对调后重复的值便被忽略了。试想一下我们连续调用两次`array_flip`函数是不是就相当于实现了`array_unique`函数的功能呢？示例代码如下：

```php
<?php
$input = ['you are' => 666, 'i am' => 233, 'he is' => 233, 'she is' => 666];
$result = array_flip(array_flip($input));
var_dump($result);
// 结果 ['she is' => 666, 'he is' => 233]
```

嗯哼？！结果和`array_unique`的不一样！为什么，我们可以从 PHP 官方手册得到答案：

如果同一个值出现多次，则最后一个键名将作为它的值，其它键会被丢弃。

总的来说就是`array_unique`保留第一个出现的键名，`array_flip`保留最后一个出现的键名。

注意：使用`array_flip`作为数组去重时数组的值必须能够作为键名（即为 string 类型或 integer 类型），否则这个值将被忽略。

此外，若不需要保留键名我们可以直接这样使用`array_values(array_flip($input))`。


## 0x04 重置索引

当我们想要对一个索引并不连续的数组进行重置时，比如数组：`[0 => 233, 99 => 666]`，对于这种数组我们只需要调用array_values 函数即可实现。如下例：

```php
<?php
$input = [0 => 233, 99 => 666];
var_dump(array_values($input));
// 结果 [0 => 233, 1 => 66]
```

需要注意的是`array_values`函数并不止重置数字索引还会将字符串键名也同样删除并重置。那如何在保留字符串键名的同时重置数字索引呢？答案就是array_slice 函数，代码示例如下：

```php
<?php
$input = ['hello' => 'world', 0 => 233, 99 => 666];
var_dump(array_slice($input, 0));
// 结果 ['hello' => 'world', 0 => 233, 1 => 66]
```
`array_slice`函数的功能是取出数组的中的一段，但它默认会重新排序并重置数组的数字索引，所以可以利用它重置数组中的数字索引。


## 0x05 清除空值

嘿，有时候我们想清除某个数组中的空值比如：`null`、`false`、`0`、`0.0`、`[]空数组`、`''空字符串`、`'0'字符串0`，这时array_filter 函数便能帮上大忙。代码如下：

```php
<?php
$input = ['foo', false, -1, null, '', []];
var_dump(array_filter($input));
// 结果 [0 => 'foo', 2 => -1]
```

为什么会出现这样的结果捏？`array_filter`的作用其实是「用回调函数过滤数组中的单元」，它的第二个参数其实是个回调函数，向数组的每个成员都执行这个回调函数，若回调函数的返回值为`true`便保留这个成员，为`false`则忽略。这个函数还有一个特性就是：

如果没有提供`callback`函数， 将删除`array`中所有等值为 **`FALSE`** 的条目。

等值为 false 就是转换为 bool 类型后值为 false 的意思，详细看文档：转换为布尔类型。

注意：如果不填写`callback`函数，`0`、`0.0`、`'0'字符串0`这些可能有意义的值会被删除。所以如果清除的规则有所不同还需要自行编写`callback`函数。


## 0x06 确认数组成员全部为真

有时候我们希望确认数组中的的值全部为`true`，比如：`['read' => true, 'write' => true, 'execute' => true]`，这时我们需要用一个循环判定吗？NO，NO，NO……只需要用array_product 函数便可以实现了。代码如下：

```php
<?php
$power = ['read' => true, 'write' => true, 'execute' => true];
var_dump((bool)array_product($power));
// 结果 true
$power = ['read' => true, 'write' => true, 'execute' => false];
var_dump((bool)array_product($power));
// 结果 false
```

为什么能实现这个功能呢？`array_product`函数本来的功能是「计算数组中所有值的乘积」，在累乘数组中所有成员的时候会将成员的值转为数值类型。当传递的参数为一个 bool  成员所组成的数组时，众所周知`true`会被转为 1，`false`会被转为 0。然后只要数组中出现一个`false`累乘的结果自然会变成 0，然后我们再将结果转为`bool`类型不就是`false`了嘛！

注意：使用`array_product`函数将在计算过程中将数组成员转为数值类型进行计算，所以请确保你了解数组成员转为数值类型后的值，否则会产生意料之外的结果。比如：

```php
<?php
$power = ['read' => true, 'write' => true, 'execute' => 'true'];
var_dump((bool)array_product($power));
// 结果 false
```

上例是因为`'true'`在计算过程中被转为 0。要想详细了解请点击这里。


## 0x07 获取指定键名之前 / 之后的数组

如果我们只想要关联数组中指定键名值之前的部分该怎么办呢？又用一个循环？当然不用我们可以通过array_keys、array_search 和array_slice 组合使用便能够实现！下面贴代码：

```php
<?php
$data = ['first' => 1, 'second' => 2, 'third' => 3];
function beforeKey($array, $key) {
    $keys = array_keys($array);
  	// $keys = [0 => 'first', 1 => 'second', 2 => 'third']
    $len = array_search($key, $keys);
    return array_slice($array, 0, $len);
}
var_dump(beforeKey($data, 'first'));
// 结果 []
var_dump(beforeKey($data, 'second'));
// 结果 ['first' => 1]
var_dump(beforeKey($data, 'third'));
// 结果 ['first' => 1, 'second' => 2]
```

思路解析，要实现这样的功能大部分同学都应该能想到`array_slice`函数，但这个函数取出部分数组是根据偏移量（可以理解为键名在数组中的顺序，从 0 开始）而不是根据键名的，而关联数组的键名却是是字符串或者是不按顺序的数字，此时要解决的问题便是「如何取到键名对应的偏移量？」，这是`array_keys`函数便帮了我们大忙，它的功能是「返回数组中部分的或所有的键名」默认返回全部键名，此外返回的键名数组是以数字索引的，也就是说返回的键名数组的索引就是偏移量！例子中的原数组变为：`[0 => 'first', 1 => 'second', 2 => 'third']`。然后我们通过`array_search`便可以获得指定键名的偏移量了，因为这个函数的功能是「在数组中搜索给定的值，如果成功则返回首个相应的键名」。有了偏移量我们直接调用`array_slice`函数便可以实现目的了。

上面的例子懂了，那获取指定键名之后的数组也就轻而易举了，略微修改`array_slice`即可。直接贴代码：

```php
<?php
$data = ['first' => 1, 'second' => 2, 'third' => 3];
function afterKey($array, $key) {
    $keys = array_keys($array);
    $offset = array_search($key, $keys);
    return array_slice($array, $offset + 1);
}
var_dump(afterKey($data, 'first'));
// 结果 ['second' => 2, 'third' => 3]
var_dump(afterKey($data, 'second'));
// 结果 ['third' => 3]
var_dump(afterKey($data, 'third'));
// 结果 []
```

那如何获取指定值之前或之后的数组呢？嘿，记得`array_search`的作用吧，其实我们只需要这样调用`beforeKey($data, array_search($value, $data))`不就实现了嘛！


## 0x08 数组中重复次数最多的值

敲黑板，划重点！据说这是一道面试题喔。假设有这样一个数组`[6, 11, 11, 2, 4, 4, 11, 6, 7, 4, 2, 11, 8]`，请问如何获取数组中重复次数最多的值？关键就在于array_count_values 函数。实例代码如下：

```php
<?php
$data = [6, 11, 11, 2, 4, 4, 11, 6, 7, 4, 2, 11, 8];
$cv = array_count_values($data);
// $cv = [6 => 2, 11 => 4, 2 => 2, 4 => 3, 7 => 1, 8 => 1]
arsort($cv);
$max = key($cv);
var_dump($max);
// 结果 11
```
`array_count_values`函数的功能是「统计数组中所有的值」，就是将原数组中的值作为返回数组的键名，值出现的次数作为返回数组的值。这样我们便可以通过arsort 函数对出现的次数进行降序排序并且保持索引关联。最后使用key 获得当前单元（当前单元默认为数组第一个成员）的键名，此时的键名即是原数组的值重复次数最多的值。


## 0x09 打广告时间

虽然 PHP 提供了很多和数组相关的函数，但使用起来还是不算太方便而且都是通过函数的调用方式而没有面向对象相关的实现，所以我最近在写一个开源的工具类项目    [zane/utils][2]
，封装了一些常用的方法并且支持链式调用，其中的 Ary 类实现 「获取数组中重复次数最多的值」只需一行，如下所示：

```php
$data = [6, 11, 11, 2, 4, 4, 11, 6, 7, 4, 2, 11, 8];
$max = Ary::new($data)->countValues()->maxKey();
var_dump($max);
// 结果 11
```


#### 欢迎大家给我提 issue 和 pr，另外如果你喜欢这个项目希望动动小手点个 star :-D

项目地址：    [github.com/zanemmm/uti…][3]



[0]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Fbook.array.php
[1]: https://link.juejin.im?target=http%3A%2F%2Fphp.net%2Fmanual%2Fzh%2Ffunction.array-intersect-key.php
[2]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fzanemmm%2Futils
[3]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fzanemmm%2Futils