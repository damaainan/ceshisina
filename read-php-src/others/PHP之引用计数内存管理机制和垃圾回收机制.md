## PHP之引用计数内存管理机制和垃圾回收机制

来源：[https://blog.csdn.net/luyaran/article/details/79758002](https://blog.csdn.net/luyaran/article/details/79758002)

时间 2018-03-30 15:13:19

 
## 引用赋值
 
```php
$a = 'apple';
$b = &$a;
```
 
上述代码中，我将一个字符串赋值给变量a，然后将a的引用赋值给了变量b。显然，这个时候的内存指向应该是这样的：
 
```php
$a -> 'apple' <- $b
```
 
a和b指向了同一块内存区域(变量容器 zval  ），我们通过`var_dump($a, $b)`得到`string(5) "apple" string(5) "apple"`，这是我们预期的结果。
 
## unset函数 与 引用计数
 
### unset 函数
 
假如我想将`'apple'`这个字符串从内存中释放掉。我是这么做的：
 
```php
unset($a);
```
 
但是通过再次打印`$a``$b`两变量的信息，我得到了这样的结果：`Notice: Undefined variable: a`和`string(5) "apple"`。奇怪，`$a``$b`指向同一个变量容器，又明明将`$a`释放了，为什么$b还是`'apple'`。
 
其实是这样的，`unset()`只是将一个变量符号`a`（指针）销毁了，并没有释放掉那个变量容器，所以执行完操作之后，内存指向只是变成了这样：
 
```
'apple' <- $b
```
 
### 引用计数
 
引用计数 (reference count)是每个变量容器中都会存放的一条信息，它表示当前变量容器正被多少个变量符号所引用。
 
正如之前的例子，unset（）并没有释放变量所指向的变量容器，而只是将变量符号销毁了。同时，将变量容器中的 引用计数  减1，当引用计数为0时，也就是说当变量容器不被任何变量引用时，
 
 <del>
  便会触发php的垃圾回收（错误）
 </del> 
，它便会被释放（正确）。
 
更正上述的一个小错误： 这种单纯的引用计数方式是 php 5.2 之前的内存管理机制，称不上是垃圾回收机制，垃圾回收机制是 php 5.3 才引入的，垃圾回收机制为的是解决这种单纯的引用计数内存管理机制的缺陷（即 循环引用导致的内存泄漏，下文会进行讲解）
 
回到正题，我们用代码来验证一下先前的结论：
 
```php
$a = 'apple';
$b = &$a;

$before = memory_get_usage();
unset($a);
$after = memory_get_usage();

var_dump($before - $after);  // 结果为int(0)，变量容器的引用计数为1，没有释放
```
 
```php
$a = 'apple';
$b = &$a;

$before = memory_get_usage();
unset($a, $b);
$after = memory_get_usage();

var_dump($before - $after);  // 结果为int(24)，变量容器的引用计数为0，得到释放
```
 
## 直接释放
 
那要怎样做才能真正释放掉`'apple'`所占用的内存呢？
 
利用上述方法，我们可以在`unset($a)`之后再`unset($b)`，将变量容器的所有引用都销毁，引用计数减为0了，自然就被释放掉了。
 
当然，还有更直接的方法：
 
```php
$a = null;
```
 
直接赋值`null`会将`$a`所指向的内存区域置空，并将引用计数归零，内存便被释放。
 
## 脚本执行结束后的内存
 
对于一般的web程序来说（fpm模式下），php的执行是单线程同步阻塞型的，当脚本执行结束之后，脚本内使用的所有内存都会被释放。那么，我们手动去释放内存到底有意义吗？
 
其实关于这个问题，早有解答，推荐大家看一下鸟哥 [@laruence][2] 2012年发表的一篇文章：
 
[请手动释放你的资源(Please release resources manually)][3]
 
## 引用计数内存管理机制的缺陷：循环引用
 
现在我们来讲讲之前提到的引用计数内存管理机制的缺陷。
 
当一个变量容器的引用计数为0时，php会进行垃圾回收。但是，你可想过，有一种情况会导致一个变量容器的引用计数永远不会被减为0，举个例子：
 
```php
$a = ['one'];
$a[] = &$a;
```
 
我们看到，`$a`数组第二个元素就是它本身。那么，存放数组的这个变量容器的引用计数为2，一个引用是变量`a`，另一个引用是这个数组的第二个元素 - 索引`1`。
 
![][0]
 
那么，如果这时我们`unset($a)`，存放数组的变量容器的引用计数会减1，但还有1个引用，就是数组的元素`1`，现在引用结构变成了这样：
 
![][1]
 
由于变量容器的引用计数没有变为0，所以不能被释放，而且这时又没有外部其他变量符号引用它，用户也没有办法去清除这个结构，这时它就会一直驻留在内存之中。
 
所以如果代码中存在大量的这种结构和操作，最终会导致内存损耗甚至泄漏。这就是 **`循环引用`**  带来的内存无法释放的问题。
 
庆幸的是，fpm模式下，当请求的脚本执行结束，php会释放所有脚本中使用到的内存，包括这个结构。但是，如果是守护进程下的php程序呢？比如swoole。
 
 <del>
  这个php需要解决的急迫问题
 </del> 
（已经解决，见下文）。
 
## PHP 5.3.0 引入的同步算法
 
传统上，像以前的 php 用到的引用计数内存机制，无法处理循环引用的内存泄漏。然而 5.3.0 PHP 使用文章 [» 引用计数系统中的同步周期回收(Concurrent Cycle Collection in Reference Counted Systems)][4] 中的同步算法，解决了这个内存泄漏问题，这种算法就是PHP的垃圾回收机制。
 
具体算法的实现和流程有些许复杂，请阅读官方文档，这里不再赘述，另附上几个算法流程讲解的文章链接，讲得比较直白：
 
  
[http://php.net/manual/zh/feat...][5] 官方文档
 
[http://www.cnblogs.com/leoo2s...][6]
 
  [https://blog.csdn.net/phpkern...][7] 
 
 
最后，还是引用鸟哥文章的这两段来说明问题：
 
在PHP5.2以前, PHP使用引用计数(Reference count)来做资源管理, 当一个zval的引用计数为0的时候, 它就会被释放. 虽然存在循环引用(Cycle reference), 但这样的设计对于开发Web脚本来说, 没什么问题, 因为Web脚本的特点和它追求的目标就是执行时间短, 不会长期运行. 对于循环引用造成的资源泄露, 会在请求结束时释放掉. 也就是说, 请求结束时释放资源, 是一种补救措施(backup).
 
然而, 随着PHP被越来越多的人使用, 就有很多人在一些后台脚本使用PHP, 这些脚本的特点是长期运行, 如果存在循环引用, 导致引用计数无法及时释放不用的资源, 则这个脚本最终会内存耗尽退出.
 
所以在PHP5.3以后, 我们引入了GC, 也就是说, 我们引入GC是为了解决用户无法解决的问题.
 


[2]: https://segmentfault.com/u/laruence
[3]: http://www.laruence.com/2012/07/25/2662.html
[4]: https://researcher.watson.ibm.com/researcher/files/us-bacon/Bacon01Concurrent.pdf
[5]: http://php.net/manual/zh/features.gc.collecting-cycles.php
[6]: http://www.cnblogs.com/leoo2sk/archive/2011/02/27/php-gc.html
[7]: https://blog.csdn.net/phpkernel/article/details/5734743
[0]: https://img2.tuicool.com/RvIVJz2.png
[1]: https://img2.tuicool.com/7zQVviF.png