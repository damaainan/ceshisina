## PHP 垃圾回收与内存管理指引

来源：[https://segmentfault.com/a/1190000015941080](https://segmentfault.com/a/1190000015941080)

![][0]

本文首发于 [PHP 垃圾回收与内存管理指引][5]，转载请注明出处。
本文将要讲述 PHP 发展历程中的垃圾回收及内存管理相关内容，文末给出 PHP 发展在各个阶段有关内存管理及垃圾回收（内核）参考资料值得阅读。
## 引用计数

在 PHP 5.2 及以前的版本中，PHP 的垃圾回收采用的是 **`[引用计数][6]`**  算法。
### 引用计数基础知识

[引用计数基础知识][7]

php 的变量存储在「zval」变量容器（数据结构）中，「zval」属性包含如下信息：


* 当前变量的数据类型；
* 当前变量的值；
* 用于标识变量是否为引用传递的 `is_ref` 布尔类型标识；
* 指向该「zval」变量容器的变量个数的 `refcount` 标识符（即这个 zval 被引用的次数，注意这里的引用不是指引用传值，注意区分）。


当一个变量被赋值时，就会生成一个对应的「zavl」变量容器。
### 查看变量 zval 容器信息

要查看变量的「zval」容器信息（即查看变量的 `is_ref` 和 `refcount`），可以使用 [XDebug][8] 调试工具的 **`xdebug_debug_zval()`**  函数。

安装 XDebug 扩展插件的方法可以查看 [这个教程][9]，有关XDebug 使用方法请阅读 [官方文档][10]。

假设，我们已经成功安装好 XDebug 工具，现在就可以来对变量进行调试了。

* 查看普通变量的 zval 信息

如果我们的 PHP 语句只是对变量进行简单赋值时，is_ref 标识值为 0，refcount 值为 1；若将这个变量作为值赋值给另一个变量时，则增加 zval 变量容器的 refcount 计数；同理，销毁（unset）变量时，「refcount」相应的减去 1。

请看下面的示例：

```php
<?php
// 变量赋值时，refcount 值等于 1
$name = 'liugongzi';
xdebug_debug_zval('name'); // (refcount=1, is_ref=0)string 'liugongzi' (length=9)

// $name 作为值赋值给另一个变量， refcount 值增加 1
$copy = $name;
xdebug_debug_zval('name'); // (refcount=2, is_ref=0)string 'liugongzi' (length=9)

// 销毁变量，refcount 值减掉 1
unset($copy);
xdebug_debug_zval('name'); // (refcount=1, is_ref=0)string 'liugongzi' (length=9)
```

* 写时复制


[写时复制（Copy On Write：COW）][11]，简单描述为：如果通过赋值的方式赋值给变量时不会申请新内存来存放新变量所保存的值，而是简单的通过一个计数器来共用内存，只有在其中的一个引用指向变量的值发生变化时，才申请新空间来保存值内容以减少对内存的占用。 - [TPIP 写时复制][12]

通过前面的简单变量的 zval 信息我们知道 **`&dollar;copy`**  和 **`&dollar;name`**  共用 zval 变量容器（内存），然后通过 **`refcount`**  来表示当前这个 zval 被多少个变量使用。

看个实例：

```php
<?php
$name = 'liugongzi';
xdebug_debug_zval('name'); // name: (refcount=1, is_ref=0)string 'liugongzi' (length=9)

$copy = $name;
xdebug_debug_zval('name'); // name: (refcount=2, is_ref=0)string 'liugongzi' (length=9)

// 将新的值赋值给变量 $copy
$copy = 'liugongzi handsome';
xdebug_debug_zval('name'); // name: (refcount=1, is_ref=0)string 'liugongzi' (length=9)
xdebug_debug_zval('copy'); // copy: (refcount=1, is_ref=0)='liugongzi handsome'
```

注意到没有，当将值 **`liugongzi handsome`**  赋值给变量 $copy 时，name 和 copy 的 refcount 值都变成了 1，在这个过程中发生以下几个操作：


* 将 $copy 从 $name 的 zval（内从）中分离出来（即复制）；
* 将 $name 的 refcount 减去 1；
* 对 $copy 的 zval 进行修改（重新赋值和修改 refcount）；


这里只是简单对「写时复制」进行介绍，感兴趣的朋友可以阅读文末给出的参考资料进行更加深入的研究。

* 查看引用传递变量的 zval 信息

引用传值（&）的「引用计数」规则同普通赋值语句一样，只是 **`is_ref`**  标识的值为 **`1`**  表示该变量是引用传值类型。

我们现在来看看引用传值的示例：

```php
<?php
$age = 'liugongzi';
xdebug_debug_zval('age'); // (refcount=1, is_ref=0)string 'liugongzi' (length=9)

$copy = &$age;
xdebug_debug_zval('age'); // (refcount=2, is_ref=1)string 'liugongzi' (length=9)

unset($copy);
xdebug_debug_zval('age'); // (refcount=1, is_ref=1)string 'liugongzi' (length=9)
```

* 复合类型的引用计数

与标量类型（整型、浮点型、布尔型等）不同，数组（array）和对象（object）这种符合类型的引用计数规则会稍复杂一些。

为了更好的说明，还是先看看数组的引用计数示例：

```php
$a = array( 'meaning' => 'life', 'number' => 42 );
xdebug_debug_zval( 'a' );

// a:
// (refcount=1, is_ref=0)
// array (size=2)
//  'meaning' => (refcount=1, is_ref=0)string 'life' (length=4)
//  'number' => (refcount=1, is_ref=0)int 42
```

上面的引用计数示意图如下：

![][1]

从图中我们发现复合类型的引用计数规则基本上同标量的计数规则一样，就给出的示例来说，PHP 会创建 3 个 zval 变量容器，一个用于存储数组本身，另外两个用于存储数组中的元素。

添加一个已经存在的元素到数组中时，它的引用计数器 refcount 会增加 1。

```php
$a = array( 'meaning' => 'life', 'number' => 42 );
xdebug_debug_zval( 'a' );
$a['life'] = $a['meaning'];
xdebug_debug_zval( 'a' );

// a:
// (refcount=1, is_ref=0)
// array (size=3)
//  'meaning' => (refcount=2, is_ref=0)string 'life' (length=4)
//  'number' => (refcount=0, is_ref=0)int 42
//  'life' => (refcount=2, is_ref=0)string 'life' (length=4)
```

大致示意图如下：

![][2] 。

* 内存泄露

虽然，复合类型的引用计数规则同标量类型大致相同，但是如果引用的值为变量自身（即循环应用），在处理不当时，就有可能会造成内存泄露的问题。

让我们来看看下面这个对数组进行引用传值的示例：

```php
<?php
// @link http://php.net/manual/zh/function.memory-get-usage.php#96280
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

// 注意：有用的地方从这里开始
$memory = memory_get_usage();

$a = array( 'one' );

// 引用自身（循环引用）
$a[] =&$a;

xdebug_debug_zval( 'a' );

var_dump(convert(memory_get_usage() - $memory)); // 296 b

unset($a); // 删除变量 $a，由于 $a 中的元素引用了自身（循环引用）最终导致 $a 所使用的内存无法被回收

var_dump(convert(memory_get_usage() - $memory)); // 568 b
```

从内存占用结果上看，虽然我们执行了 **`unset(&dollar;a)`**  方法来销毁 **`&dollar;a`**  数组，但内存并没有被回收，整个处理过程的示意图如下：

![][3]

可以看到对于这块内存，再也没有符合表（变量）指向了，所以 PHP 无法完成内存回收，官方给出的解释如下：

尽管不再有某个作用域中的任何符号指向这个结构 (就是变量容器)，由于数组元素 “1” 仍然指向数组本身，所以这个容器不能被清除 。因为没有另外的符号指向它，用户没有办法清除这个结构，结果就会导致内存泄漏。庆幸的是，php 将在脚本执行结束时清除这个数据结构，但是在 php 清除之前，将耗费不少内存。如果你要实现分析算法，或者要做其他像一个子元素指向它的父元素这样的事情，这种情况就会经常发生。当然，同样的情况也会发生在对象上，实际上对象更有可能出现这种情况，因为对象总是隐式的被引用。 - 摘自 [官方文档 Cleanup Problems][13]

简单来说就是「引用计数」算法无法检测并释放循环引用所使用的内存，最终导致内存泄露。
## 引用计数系统的同步周期回收

由于引用计数算法存在无法回收循环应用导致的内存泄露问题，在 PHP 5.3 之后对内存回收的实现做了优化，通过采用 [引用计数系统的同步周期回收][14] 算法实现内存管理。引用计数系统的同步周期回收算法是一个改良版本的引用计数算法，它在引用基础上做出了如下几个方面的增强：


* 引入了可能根（possible root）的概念：通过引用计数相关学习，我们知道如果一个变量（zval）被引用，要么是被全局符号表中的符号引用（即变量），要么被复杂类型（如数组）的 zval 中的符号（数组的元素）引用，那么这个 zval 变量容器就是「可能根」。
* 引入根缓冲区（root buffer）的概念：根缓冲区用于存放所有「可能根」，它是固定大小的，默认可存 10000 个可能根，如需修改可以通过修改 PHP 源码文件 **`Zend/zend_gc.c`**  中的常量 **`GC_ROOT_BUFFER_MAX_ENTRIES`** ，再重新编译。
* 回收周期：当缓冲区满时，对缓冲区中的所有可能根进行垃圾回收处理。


下图（来自 [PHP 手册][15]）,展示了新的回收算法执行过程：

![][4]
### 引用计数系统的同步周期回收过程


* 缓冲区（紫色框部分，称为疑似垃圾），存储所有可能根（步骤 A）；
* 采用深度优先算法遍历「根缓冲区」中所有的「可能根（即 zval 遍历容器）」，并对每个 zval 的 `refcount` 减 1，为了避免遍历时对同一个 zval 多次减 1（因为不同的根可能遍历到同一个 zval）将这个 zvel 标记为「已减」（步骤 B）；
* 再次采用深度优先遍历算法遍历「可能根 zval」。当 zval 的 `refcount` 值不为 0 时，对其加 1,否则保持为 0。并请已遍历的 zval 变量容器标记为「已恢复」（即步骤 B 的逆运算）。那些 zval 的 `refcount` 值为 0 （蓝色框标记）的就是应该被回收的变量（步骤 C）；
* 删除所有 `refcount` 为 0 的可能根（步骤 D）。


整个过程为：

采用深度优先算法执行： **`默认删除 > 模拟恢复 > 执行删除`**  达到内存回收的目的。
### 优化后的引用计数算法优势


* 将内存泄露控制在阀值内，这个由缓存区实现，达到缓冲区大小执行新一轮垃圾回收；
* 提升了垃圾回收性能，不是每次 `refcount` 减 1 都执行回收处理，而是等到根缓冲区满时才开始执行垃圾回收。


你可以从 [PHP 手册 的回收周期][16] 了解更多，也可以阅读文末给出的参考资料。
## PHP 7 的内存管理

PHP 5 中 zval 实现上的主要问题：


* zval **`总是单独`**  从堆中分配内存；
* zval **`总是存储引用计数和循环回收`**  的信息，即使是整型（bool / null）这种可能并不需要此类信息的数据；
* 在使用对象或者资源时，直接引用会导致两次计数；
* 某些间接访问需要一个更好的处理方式。比如现在访问存储在变量中的对象间接使用了四个指针（指针链的长度为四）；
* 直接计数也就意味着数值只能在 zval 之间共享。如果想在 zval 和 hashtable key 之间共享一个字符串就不行（除非 hashtable key 也是 zval）。


PHP 7 中的 zval 数据结构实现的调整：

最基础的变化就是 zval 需要的内存 **`不再是单独从堆上分配`** ，不再由 zval 存储引用计数。
复杂数据类型（比如字符串、数组和对象）的引用计数由其自身来存储。 - 摘自 [Internal value representation in PHP 7 - Part 1][17]【[译][18]】
这种实现的优势：


* 简单数据类型不需要单独分配内存，也不需要计数；
* 不会再有两次计数的情况。在对象中，只有对象自身存储的计数是有效的；
* 由于现在计数由数值自身存储（PHP 有 zval 变量容器存储），所以也就可以和非 zval 结构的数据共享，比如 zval 和 hashtable key 之间；
* 间接访问需要的指针数减少了。


更具体的有关 PHP 7 zval 实现和内存优化细节可以阅读 [深入理解 PHP7 内核之 zval][19] 和 [Internal value representation in PHP 7 - Part 1][17][译][18]。
## 参考资料

[深入理解 PHP7 内核之 zval][19]

[Internal value representation in PHP 7 - Part 1][17]【[译][18]】

[Internal value representation in PHP 7 - Part 2][25]【[译][26]】

[TPIP：第六节 写时复制（Copy On Write）][27]

[TPIP：内存管理][28]

[PHP7 内核之 zval][29]

[浅谈 PHP5 中垃圾回收算法 (Garbage Collection) 的演化][30]

[Confusion about PHP 7 refcount][31]

[引用计数系统中的同步周期回收 (Concurrent Cycle Collection in Reference Counted Systems) 论文][32]

[PHP7 革新与性能优化][33]

[5]: http://blog.phpzendo.com/?p=448
[6]: https://en.wikipedia.org/wiki/Reference_counting
[7]: http://php.net/manual/zh/features.gc.refcounting-basics.php
[8]: https://xdebug.org/
[9]: https://github.com/huliuqing/phpnotes/issues/58
[10]: https://xdebug.org/docs/
[11]: https://en.wikipedia.org/wiki/Copy-on-write
[12]: http://www.php-internals.com/book/?p=chapt06/06-06-copy-on-write
[13]: http://php.net/manual/zh/features.gc.refcounting-basics.php
[14]: https://researcher.watson.ibm.com/researcher/files/us-bacon/Bacon01Concurrent.pdf
[15]: http://php.net/manual/zh/features.gc.collecting-cycles.php
[16]: http://php.net/manual/zh/features.gc.collecting-cycles.php
[17]: https://nikic.github.io/2015/05/05/Internal-value-representation-in-PHP-7-part-1.html
[18]: https://0x1.im/blog/php/Internal-value-representation-in-PHP-7-part-1.html
[19]: http://www.laruence.com/2018/04/08/3170.html
[20]: https://nikic.github.io/2015/05/05/Internal-value-representation-in-PHP-7-part-1.html
[21]: https://0x1.im/blog/php/Internal-value-representation-in-PHP-7-part-1.html
[22]: http://www.laruence.com/2018/04/08/3170.html
[23]: https://nikic.github.io/2015/05/05/Internal-value-representation-in-PHP-7-part-1.html
[24]: https://0x1.im/blog/php/Internal-value-representation-in-PHP-7-part-1.html
[25]: https://nikic.github.io/2015/05/05/Internal-value-representation-in-PHP-7-part-1.html
[26]: https://0x1.im/blog/php/Internal-value-representation-in-PHP-7-part-2.html
[27]: http://www.php-internals.com/book/?p=chapt06/06-06-copy-on-write
[28]: http://www.php-internals.com/book/?p=chapt06/06-00-memory-management
[29]: https://github.com/pangudashu/php7-internal/blob/master/5/gc.md
[30]: http://www.cnblogs.com/leoo2sk/archive/2011/02/27/php-gc.html
[31]: https://stackoverflow.com/questions/34764119/confusion-about-php-7-refcount
[32]: https://researcher.watson.ibm.com/researcher/files/us-bacon/Bacon01Concurrent.pdf
[33]: http://hansionxu.blog.163.com/blog/static/24169810920158704014772/
[0]: ../img/bVbe29F.png
[1]: ../img/1460000015941083.png
[2]: ../img/1460000015941084.png
[3]: ../img/1460000015941085.png
[4]: ../img/1460000015941086.png