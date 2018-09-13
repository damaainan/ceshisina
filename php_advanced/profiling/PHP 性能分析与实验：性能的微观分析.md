# PHP 性能分析与实验：性能的微观分析

[舒铭][0]

1 年前

在[上一篇文章][1]中，我们从 PHP 是解释性语言、动态语言和底层实现等三个方面，探讨了 PHP 性能的问题。本文就深入到 PHP 的微观层面，我们来了解 PHP 在使用和编写代码过程中，性能方面，可能需要注意和提升的地方。

在开始分析之前，我们得掌握一些与性能分析相关的函数。这些函数让我们对程序性能有更好的分析和评测。

## 一、性能分析相关的函数与命令

### 1.1、时间度量函数

平时我们常用 time() 函数，但是返回的是秒数，对于某段代码的内部性能分析，到秒的精度是不够的。于是要用 microtime 函数。而 microtime 函数可以返回两种形式，一是字符串的形式，一是[浮点数][2]的形式。不过需要注意的是，在缺省的情况下，返回的精度只有4位小数。为了获得更高的精确度，我们需要配置 `precision`。

如下是 microtime 的使用结果。

```php
$start= microtime(true);
echo $start."/n";
$end = microtime(true);
echo $end."/n";
echo ($end-$start)."/n";
```

输出为：

```
    bash-3.2# php time.php
    
    1441360050.3286 
    1441360050.3292 
    0.00053000450134277
```

而在代码前面加上一行：

```
ini_set("precision", 16);
```

输出为：

```
    bash-3.2# php time.php
    
    1441360210.932628 
    1441360210.932831 
    0.0002031326293945312
```

除了 microtime 内部统计之外， 还可以使用 `getrusage` 来取得用户态的时长。在实际的操作中，也常用 time 命令来计算整个程序的运行时长，通过多次运行或者修改代码后运行，得到不同的时间长度以得到效率上的区别。 具体用法是：time phptime.php ，则在程序运行完成之后，不管是否正常结束退出，都会有相关的统计。

```
    bash-3.2# time php time.php
    
    1441360373.150756 
    1441360373.150959 
    0.0002031326293945312
    
    real 0m0.186s 
    user 0m0.072s 
    sys 0m0.077s
```

因为本文所讨论的性能问题，往往分析上百万次调用之后的差距与趋势，为了避免代码中存在一些时间统计代码，后面我们使用 time 命令居多。

### 1.2、内存使用相关函数

分析内存使用的函数有两个：`memory_get_usage`、`memory_get_peak_usage`，前者可以获得程序在调用的时间点，即当前所使用的内存，后者可以获得到目前为止高峰时期所使用的内存。所使用的内存以字节为单位。

```php
$base_memory= memory_get_usage();
echo "Hello,world!/n";
$end_memory= memory_get_usage();
$peak_memory= memory_get_peak_usage();

echo $base_memory,"/t",$end_memory,"/t",($end_memory-$base_memory),"/t", $peak_memory,"/n";
```

输出如下：

```
    bash-3.2# php helloworld.php
    
    Hello,world! 
    224400 224568 168 227424
```

可以看到，即使程序中间只输出了一句话，再加上变量存储，也消耗了168个字节的内存。

对于同一程序，不同 PHP 版本对内存的使用并不相同，甚至还差别很大。

```php
$baseMemory= memory_get_usage();
class User
{
    private $uid;
    function __construct($uid)
    {
        $this->uid= $uid;
    }
}

for($i=0;$i<100000;$i++)
{
    $obj= new User($i);
    if ( $i% 10000 === 0 )
    {
        echo sprintf( '%6d: ', $i), memory_get_usage(), " bytes/n";
    }
}
echo "  peak: ",memory_get_peak_usage(true), " bytes/n";
```

在 PHP 5.2 中，内存使用如下：

```
    [root@localhostphpperf]# php52 memory.php
    
    0: 93784 bytes 
    10000: 93784 bytes 
    …… 80000: 93784 bytes 
    90000: 93784 bytes 
    peak: 262144 bytes
```

PHP 5.3 中，内存使用如下

```
    [root@localhostphpperf]# php memory.php
    
    0: 634992 bytes 
    10000: 634992 bytes 
    …… 80000: 634992 bytes 
    90000: 634992 bytes 
    peak: 786432 bytes
```

可见 PHP 5.3 在内存使用上要粗放了一些。

PHP 5.4 – 5.6 差不多，有所优化：

```
    [root@localhostphpperf]# php56 memory.php
    
    0: 224944 bytes 
    10000: 224920 bytes 
    …… 80000: 224920 bytes 
    90000: 224920 bytes 
    peak: 262144 bytes
```

而 PHP 7 在少量使用时，高峰内存的使用，增大很多。

```
    [root@localhostphpperf]# php7 memory.php
    
    0: 353912 bytes 
    10000: 353912 bytes 
    …… 80000: 353912 bytes 
    90000: 353912 bytes 
    peak: 2097152 bytes
```

从上面也看到，以上所使用的 PHP 都有比较好的垃圾回收机制，10万次初始化,并没有随着对象初始化的增多而增加内存的使用。PHP7 的高峰内存使用最多，达到了接近 2M。

下面再来看一个例子，在上面的代码的基础上，我们加上一行，如下：

```
$obj->self = $obj;
```

代码如下：

```php
$baseMemory= memory_get_usage();
class User
{
    private $uid;
    function __construct($uid)
    {
        $this->uid= $uid;
    }
}

for($i=0;$i<100000;$i++)
{
    $obj= new User($i);
    $obj->self = $obj;
    if ( $i% 5000 === 0 )
    {
        echo sprintf( '%6d: ', $i), memory_get_usage(), " bytes/n";
    }
}
echo "  peak: ",memory_get_peak_usage(true), " bytes/n";
```

这时候再来看看内存的使用情况，中间表格主体部分为内存使用量，单位为字节。

图表如下：

PHP 5.2 并没有合适的垃圾回收机制，导致内存使用越来越多。而5.3 以后内存回收机制导致内存稳定在一个区间。而也可以看见 PHP7 内存使用最少。把 PHP 5.2 的图形去掉了之后，对比更为明显。

可见 PHP7 不仅是在算法效率上，有大幅度的提升，在大批量内存使用上也有大幅度的优化（尽管小程序的高峰内存比历史版本所用内存更多）。

### 1.3、垃圾回收相关函数

在 PHP 中，内存回收是可以控制的，我们可以显式地关闭或者打开垃圾回收，一种方法是通过修改配置，zend.enable_gc=Off 就可以关掉垃圾回收。缺省情况下是 On 的。另外一种手段是通过 gc _enable()和gc _disable()函数分别打开和关闭垃圾回收。

比如在上面的例子的基础上，我们关闭垃圾回收，就可以得到如下数据表格和图表。

代码如下：

```php
gc_disable();
$baseMemory= memory_get_usage();
class User
{
    private $uid;
    function __construct($uid)
    {
        $this->uid= $uid;
    }
}

for($i=0;$i<100000;$i++)
{
    $obj= new User($i);
    $obj->self = $obj;
    if ( $i% 5000 === 0 )
    {
        echo sprintf( '%6d: ', $i), memory_get_usage(), " bytes/n";
    }
}
echo "  peak: ",memory_get_peak_usage(true), " bytes/n";
```

分别在 PHP 5.3、PHP5.4 、PHP5.5、PHP5.6 、PHP7 下运行，得到如下内存使用统计表。

图表如下，PHP7 还是内存使用效率最优的。

从上面的例子也可以看出来，尽管在第一个例子中，PHP7 的高峰内存使用数是最多的，但是当内存使用得多时，PHP7 的内存优化就体现出来了。

这里值得一提的是垃圾回收，尽管会使内存减少，但是会导致速度降低，因为垃圾回收也是需要消耗 CPU 等其他系统资源的。Composer 项目就曾经因为在计算依赖前关闭垃圾回收，带来成倍性能提升，引发广大网友关注。详见：

[https:// github.com/composer/com poser/commit/ac676f47f7bbc619678a29deae097b6b0710b799][3]

在常见的代码和性能分析中，出了以上三类函数之外，还常使用的有堆栈跟踪函数、输出函数，这里不再赘述。

## 二、PHP 性能分析10则

下面我们根据小程序来验证一些常见的性能差别。

### 2.1、使用 echo 还是 print

在有的建议规则中，会建议使用 echo ，而不使用 print。说 print 是函数，而 echo 是语法结构。实际上并不是如此，print 也是语法结构，类似的语法结构，还有多个，比如 list、isset、require 等。不过对于 PHP 7 以下 PHP 版本而言，两者确实有性能上的差别。如下两份代码：

```php
for($i=0; $i<1000000; $i++)
{
    echo("Hello,World!");
}
for($i=0; $i<1000000; $i++)
{
    print ("Hello,World!");
}
```

在 PHP 5.3 中运行速度分别如下（各2次）：

```
    [root@localhostphpperf]# time php echo1.php > /dev/null
    real 0m0.233s 
    user 0m0.153s 
    sys 0m0.080s 
    [root@localhostphpperf]# time php echo1.php > /dev/null
    real 0m0.234s 
    user 0m0.159s 
    sys 0m0.073s 
    [root@localhostphpperf]# time php echo.php> /dev/null
    real 0m0.203s 
    user 0m0.130s 
    sys 0m0.072s 
    [root@localhostphpperf]# time php echo.php> /dev/null
    real 0m0.203s 
    user 0m0.128s 
    sys 0m0.075s
```

在 PHP5.3 版中效率差距10%以上。而在 PHP5.4 以上的版本中，区别不大，如下是 PHP7 中的运行效率。

```
    [root@localhostphpperf]# time php7 echo.php> /dev/null
    real 0m0.151s 
    user 0m0.088s 
    sys 0m0.062s 
    [root@localhostphpperf]# time php7 echo.php> /dev/null
    real 0m0.145s 
    user 0m0.084s 
    sys 0m0.061s
    [root@localhostphpperf]# time php7 echo1.php > /dev/null
    real 0m0.140s 
    user 0m0.075s 
    sys 0m0.064s 
    [root@localhostphpperf]# time php7 echo1.php > /dev/null
    real 0m0.146s 
    user 0m0.077s 
    sys 0m0.069s
```

正如浏览器前端的一些优化准则一样，没有啥特别通用的原则，往往根据不同的情况和版本，规则也会存在不同。

### 2.2、require 还是 require_once？

在一些常规的优化规则中，会提到，建议使用 require_once 而不是 require，现由是 require_once 会去检测是否重复，而 require 则不需要重复检测。

在大量不同文件的包含中，require_once 略慢于 require。但是 **`require_once 的检测是一项内存中的行为`**，也就是说即使有数个需要加载的文件，检测也只是内存中的比较。而 **`require 的每次重新加载，都会从文件系统中去读取分析`**。因而 require_once 会比 require 更佳。咱们也使用一个例子来看一下。

```php
// str.php
global$str;
$str= "China has a large population";
require.php
for($i=0; $i<100000; $i++) {
    require "str.php";
}
require_once.php
for($i=0; $i<100000; $i++) {
    require_once"str.php";
}
```

上面的例子，在 PHP7 中，**require_once.php 的运行速度是 require.php 的30倍**！在其他版本也能得到大致相同的结果。

```
    [root@localhostphpperf]# time php7 require.php
    real 0m1.712s 
    user 0m1.126s 
    sys 0m0.569s 
    [root@localhostphpperf]# time php7 require.php
    real 0m1.640s 
    user 0m1.113s 
    sys 0m0.515s 
    [root@localhostphpperf]# time php7 require_once.php
    real 0m0.066s 
    user 0m0.063s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 require_once.php
    real 0m0.057s 
    user 0m0.052s 
    sys 0m0.004s
```

从上可以看到，如果存在大量的重复加载的话，**`require_once 明显优于 require`**，因为重复的文件不再有 IO 操作。即使不是大量重复的加载，也建议使用 require_once，因为在一个程序中，一般不会存在数以千百计的文件包含，100次内存比较的速度差距，一个文件包含就相当了。

### 2.3、单引号还是双引号？

单引号，还是双引号，是一个问题。一般的建议是能使用单引号的地方，就不要使用双引号，因为字符串中的单引号，不会引起解析，从而效率更高。那来看一下实际的差别。

```php
class User
{
    private $uid;
    private $username;
    private $age;
    function  __construct($uid, $username,$age){
        $this->uid= $uid;
        $this->username = $username;
        $this->age = $age;
    }
    function getUserInfo()
    {
        return "UID:".$this->uid." UserName:".$this->username." Age:".$this->age;
    }
    function getUserInfoSingle()
    {
        return 'UID:'.$this->uid.' UserName:'.$this->username.' Age'.$this->age;
    }
    function getUserInfoOnce()
    {
        return "UID:{$this->uid}UserName:{$this->username} Age:{$this->age}";
    }
    function getUserInfoSingle2()
    {
        return 'UID:{$this->uid} UserName:{$this->username} Age:{$this->age}';
    }
}
for($i=0; $i<1000000;$i++) {
    $user = new User($i, "name".$i, $i%100);
    $user->getUserInfoSingle();
}
```

在上面的 User 类中，有四个不同的方法,完成一样的功能，就是拼接信息返回，看看这四个不同的方法的区别。

第一个、getUserInfo ，使用双引号和属性相拼接

```
    [root@localhostphpperf]# time php7 string.php
    real 0m0.670s 
    user 0m0.665s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.692s 
    user 0m0.689s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.683s 
    user 0m0.672s 
    sys 0m0.004s
```

第二个、getUserInfoSingle ，使用单引号和属性相拼接

```
    [root@localhostphpperf]# time php7 string.php
    real 0m0.686s 
    user 0m0.683s 
    sys 0m0.001s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.671s 
    user 0m0.666s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.669s 
    user 0m0.666s 
    sys 0m0.002s
```

可见**`在拼接中，单双引号并无明显差别`**。

第三个、getUserInfoOnce，不再使用`句号.`连接，而是直接引入在字符串中解析。

```
    [root@localhostphpperf]# time php7 string.php
    real 0m0.564s 
    user 0m0.556s 
    sys 0m0.006s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.592s 
    user 0m0.587s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.563s 
    user 0m0.559s 
    sys 0m0.003s
```

从上面可见，速度提高了0.06s-0.10s，有10%-20%的效率提升。可见连缀效率更低一些。

第四个、getUserInfoSingle2 虽然没有达到我们真正想要的效果，功能是不正确的，但是在字符串中，不再需要解析变量和获取变量值，所以效率确实有大幅度提升。

```
    [root@localhostphpperf]# time php7 string.php
    real 0m0.379s 
    user 0m0.375s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.399s 
    user 0m0.394s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.377s 
    user 0m0.371s 
    sys 0m0.004s
```

效率确实有了大的提升，快了50%。

那么这个快，是由于不需要变量引用解析带来的，还是只要加入`$`天然的呢？我们再试着写了一个方法。

```php
function getUserInfoSingle3()
{
    return "UID:{\$this->uid} UserName:{\$this->username} Age:{\$this->age}";
}
```

得到如下运行时间：

```
    [root@localhostphpperf]# time php7 string.php
    real 0m0.385s 
    user 0m0.381s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.382s 
    user 0m0.380s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php7 string.php
    real 0m0.386s 
    user 0m0.380s 
    sys 0m0.004s
```

发现转义后的字符串，效率跟单引号是一致的，从这里也可以看见，单引号还是双引号包含，如果不存在需要解析的变量，几乎没有差别。如果有需要解析的变量，你也不能光用单引号，要么使用单引号和连缀，要么使用内部插值，所以在这条规则上，不用太过纠结。

### 2.4、错误应该打开还是关闭？

在 PHP 中，有多种错误消息，错误消息的开启是否会带来性能上的影响呢？从直觉觉得，由于错误消息，本身会涉及到 IO 输出，无论是输出到终端或者 error_log，都是如此，所以肯定会影响性能。我们来看看这个影响有多大。

```php
error_reporting(E_ERROR);
for($i=0; $i<1000000;$i++) {
    $str= "通常，$PHP中的垃圾回收机制，仅仅在循环回收算法确实运行时会有时间消耗上的增加。但是在平常的(更小的)脚本中应根本就没有性能影响。然而，在平常脚本中有循环回收机制运行的情况下，内存的节省将允许更多这种脚本同时运行在你的服务器上。因为总共使用的内存没达到上限。";
}
```

在上面的代码中，我们涉及到一个不存在的变量，所以会报出 Notice 错误:

```
    Notice: Undefined variable: PHP 中的垃圾回收机制，仅仅在循环回收算法确实运行时会有时间消耗上的增加。但是在平常的 in xxxx/string2.php on line 10
```

如果把 `E_ERROR` 改成 `E_ALL` 就能看到大量的上述错误输出。

我们先执行 E_ERROR 版，这个时候没有任何错误日志输出。得到如下数据：

```
    [root@localhostphpperf]# time php7 string2.php
    real 0m0.442s 
    user 0m0.434s 
    sys 0m0.005s 
    [root@localhostphpperf]# time php7 string2.php
    real 0m0.487s 
    user 0m0.484s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php7 string2.php
    real 0m0.476s 
    user 0m0.471s 
    sys 0m0.003s
```

再执行 E_ALL 版，有大量的错误日志输出，我们把输出重定向到/dev/null

```
    [root@localhostphpperf]# time php7 string2.php > /dev/null
    real 0m0.928s 
    user 0m0.873s 
    sys 0m0.051s 
    [root@localhostphpperf]# time php7 string2.php > /dev/null
    real 0m0.984s 
    user 0m0.917s 
    sys 0m0.064s 
    [root@localhostphpperf]# time php7 string2.php > /dev/null
    real 0m0.945s 
    user 0m0.887s 
    sys 0m0.056s
```

可见慢了将近一倍。

如上可见，即使输出没有正式写入文件，错误级别打开的影响也是巨大的。在线上我们应该将错误级别调到 E_ERROR 这个级别，同时将错误写入 error_log，既减少了不必要的错误信息输出，又避免泄漏路径等信息，造成安全隐患。

### 2.5、正则表达式和普通字符串操作

在字符串操作中，有一条常见的规则，即是能使用普通字符串操作方法替代的，就不要使用正则表达式来处理，用 C 语言操作 PCRE 做过正则表达式处理的童鞋应该清楚，需要先 compile，再 exec，也就是说是一个相对复杂的过程。现在就比较一下两者的差别。

对于简单的分隔，我们可以使用 explode 来实现，也可以使用正则表达式，比如下面的例子：

```php
ini_set("precision", 16);
function microtime_ex()
{
    list($usec, $sec) = explode(" ", microtime());
    return $sec+$usec;
}
for($i=0; $i<1000000; $i++) {
    microtime_ex();
}
```

耗时在0.93-1S之间。

```
    [root@localhostphpperf]# time php7 pregstring.php
    real 0m0.941s 
    user 0m0.931s 
    sys 0m0.007s 
    [root@localhostphpperf]# time php7 pregstring.php
    real 0m0.986s 
    user 0m0.980s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 pregstring.php
    real 0m1.004s 
    user 0m0.998s 
    sys 0m0.003s
```

我们再将分隔语句替换成：

```php
list($usec, $sec) = preg_split("#\s#", microtime());
```

得到如下数据，慢了近10-20%。

```
    [root@localhostphpperf]# time php7 pregstring1.php
    real 0m1.195s 
    user 0m1.182s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 pregstring1.php
    real 0m1.222s 
    user 0m1.217s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 pregstring1.php
    real 0m1.101s 
    user 0m1.091s 
    sys 0m0.005s
```

再将语句替换成：

```php
list($usec, $sec) = preg_split("#\s+#", microtime());
```

即匹配一到多个空格，并没有太多的影响。除了分隔外，查找我们也来看一个例子。

第一段代码：

```php
$str= "China has a Large population";
for($i=0; $i<1000000; $i++) {
    if(preg_match("#l#i", $str))
    {
    }
}
```

第二段代码：

```php
$str= "China has a large population";
for($i=0; $i<1000000; $i++) {
    if(stripos($str, "l")!==false)
    {
    }
}
```

这两段代码达到的效果相同，都是查找字符串中有无 l 或者 L 字符。

在 PHP 7 下运行效果如下：

```
    [root@localhostphpperf]# time php7 pregstring2.php
    real 0m0.172s 
    user 0m0.167s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 pregstring2.php
    real 0m0.199s 
    user 0m0.196s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php7 pregstring3.php
    real 0m0.185s 
    user 0m0.182s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 pregstring3.php
    real 0m0.184s 
    user 0m0.181s 
    sys 0m0.003s
```

两者区别不大。再看看在 PHP5.6 中的表现。

```
    [root@localhostphpperf]# time php56 pregstring2.php
    real 0m0.470s 
    user 0m0.456s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php56 pregstring2.php
    real 0m0.506s 
    user 0m0.500s 
    sys 0m0.005s 
    [root@localhostphpperf]# time php56 pregstring3.php
    real 0m0.348s 
    user 0m0.342s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php56 pregstring3.php
    real 0m0.376s 
    user 0m0.364s 
    sys 0m0.003s
```

可见在 PHP 5.6 中表现还是非常明显的，使用正则表达式慢了20%。PHP7 难道是对已使用过的正则表达式做了缓存？我们调整一下代码如下：

```php
$str= "China has a Large population";
for($i=0; $i<1000000; $i++) {
    $pattern = "#".chr(ord('a')+$i%26)."#i";
    if($ret = preg_match($pattern, $str)!==false)
    {
    }
}
```

这是一个动态编译的 pattern。

```php
$str= "China has a large population";
for($i=0; $i<1000000; $i++) {
    $pattern = "".chr(ord('a')+$i%26)."";
    if($ret = stripos($str, $pattern)!==false)
    {
    }
}
```

在 PHP7 中，得到了如下结果：

```
    [root@localhostphpperf]# time php7 pregstring2.php
    real 0m0.351s 
    user 0m0.346s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 pregstring2.php
    real 0m0.359s 
    user 0m0.352s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 pregstring3.php
    real 0m0.375s 
    user 0m0.369s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 pregstring3.php
    real 0m0.370s 
    user 0m0.365s 
    sys 0m0.005s
```

可见两者并不明显。而在 PHP 5.6 中，同样的代码：

```
    [root@localhostphpperf]# time php56 pregstring2.php
    real 0m1.022s 
    user 0m1.015s 
    sys 0m0.005s 
    [root@localhostphpperf]# time php56 pregstring2.php
    real 0m1.049s 
    user 0m1.041s 
    sys 0m0.005s 
    [root@localhostphpperf]# time php56 pregstring3.php
    real 0m0.923s 
    user 0m0.821s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php56 pregstring3.php
    real 0m0.838s 
    user 0m0.831s 
    sys 0m0.004s
```

在 PHP 5.6 中，stripos 版明显要快于正则表达式版，由上两例可见，PHP7对正则表达式的优化还是相当惊人的。其次也建议，能用普通字符串操作的地方，可以避免使用正则表达式。因为在其他版本中，这个规则还是适用的。某 zend 大牛官方的分享给出如下数据：

* `stripos('http://', $website)` 速度是`preg_match('/http:\/\//i', $website)` 的两倍
* `ctype_alnum()`速度是`preg_match('/^\s*$/')`的5倍;
* `if ($test == (int)$test)` 比 `preg_match('/^\d*$/')`快5倍

可以相见，正则表达式是相对低效的。

### 2.6、数组元素定位查找

在数组元素的查找中，有一个关键的注意点就是数组值和键的查找速度，差异非常大。了解过 PHP 扩展开发的朋友，应该清楚，数组在底层其实是 Hash 表。所以键是以快速定位的，而值却未必。下面来看例子。

首先们构造一个数组：

```php
$a= array();
for($i=0;$i<100000;$i++){
    $a[$i] = $i;
}
```

在这个数组中，我们测试查找值和查找键的效率差别。

第一种方法用 array_search，第二种用 array_key_exists，第三种用 isset 语法结构。 代码分别如下：

```php
//查找值
foreach($a as $i)
{
    array_search($i, $a);
}
//查找键
foreach($a as $i)
{
    array_key_exists($i, $a);
}
//判定键是否存在
foreach($a as $i)
{
    if(isset($a[$i]));
}
```

运行结果如下：

```
    [root@localhostphpperf]# time php7 array.php
    real 0m9.026s 
    user 0m8.965s 
    sys 0m0.007s 
    [root@localhostphpperf]# time php7 array.php
    real 0m9.063s 
    user 0m8.965s 
    sys 0m0.005s 
    [root@localhostphpperf]# time php7 array1.php
    real 0m0.018s 
    user 0m0.016s 
    sys 0m0.001s 
    [root@localhostphpperf]# time php7 array1.php
    real 0m0.021s 
    user 0m0.015s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 array2.php
    real 0m0.020s 
    user 0m0.014s 
    sys 0m0.006s 
    [root@localhostphpperf]# time php7 array2.php
    real 0m0.016s 
    user 0m0.009s 
    sys 0m0.006s
```

由上例子可见，**`键值查找的速度比值查找的速度有百倍以上的效率差别`**。因而如果能用键值定位的地方，尽量用**键值定位**，而不是值查找。

### 2.7、对象与数组

在 PHP 中，数组就是字典，字典可以存储属性和属性值，而且无论是键还是值，都不要求数据类型统一，所以对象数据存储，既能用对象数据结构的属性存储数据，也能使用数组的元素存储数据。那么两者有何差别呢？

使用对象：

```php
class User
{
    public $uid;
    public $username;
    public $age;
    function getUserInfo()
    {
        return "UID:".$this->uid." UserName:".$this->username." Age:".$this->age;
    }
}
for($i=0; $i<1000000;$i++) {
    $user = new User();
    $user->uid= $i;
    $user->age = $i%100;
    $user->username="User".$i;
    $user->getUserInfo();
}
```

使用数组：

```php
function getUserInfo($user)
{
    return "UID:".$user['uid']." UserName:".$user['username']." Age:".$user['age'];
}
for($i=0; $i<1000000;$i++) {
    $user = array("uid"=>$i,"age" =>$i%100,"username"=>"User".$i);
    getUserInfo($user);
}
```

我们分别在 PHP5.3、PHP 5.6 和 PHP 7 中运行这两段代码。

```
    [root@localhostphpperf]# time phpobject.php
    real 0m2.144s 
    user 0m2.119s 
    sys 0m0.009s 
    [root@localhostphpperf]# time phpobject.php
    real 0m2.106s 
    user 0m2.089s 
    sys 0m0.013s 
    [root@localhostphpperf]# time php object1.php
    real 0m1.421s 
    user 0m1.402s 
    sys 0m0.016s 
    [root@localhostphpperf]# time php object1.php
    real 0m1.431s 
    user 0m1.410s 
    sys 0m0.012s
```

在 PHP 5.3 中，`数组版比对象版快了近30%`。

```
    [root@localhostphpperf]# time php56 object.php
    real 0m1.323s 
    user 0m1.319s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php56 object.php
    real 0m1.414s 
    user 0m1.400s 
    sys 0m0.006s 
    [root@localhostphpperf]# time php56 object1.php
    real 0m1.356s 
    user 0m1.352s 
    sys 0m0.002s 
    [root@localhostphpperf]# time php56 object1.php
    real 0m1.364s 
    user 0m1.349s 
    sys 0m0.006s 
    [root@localhostphpperf]# time php7 object.php
    real 0m0.642s 
    user 0m0.638s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 object.php
    real 0m0.606s 
    user 0m0.602s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 object1.php
    real 0m0.615s 
    user 0m0.613s 
    sys 0m0.000s 
    [root@localhostphpperf]# time php7 object1.php
    real 0m0.615s 
    user 0m0.611s 
    sys 0m0.003s
```

到了 PHP 5.6 和 PHP7 中，两个版本基本没有差别，而在 PHP7 中的速度是 PHP5.6 中的2倍。在新的版本中，差别已几乎没有，那么为了清楚起见我们当然应该声明类，实例化类来存储对象数据。

### 2.8、getter 和 setter

从 Java 转过来学习 PHP 的朋友，在对象声明时，可能习惯使用 getter 和 setter，那么，在 PHP 中，使用 getter 和 setter 是否会带来性能上的损失呢？同样，先上例子。

无 setter版：

```php
class User
{
    public $uid;
    public $username;
    public $age;
    function getUserInfo()
    {
        return "UID:".$this->uid." UserName:".$this->username." Age:".$this->age;
    }
}
for($i=0; $i<1000000;$i++) {
    $user = new User();
    $user->uid= $i;
    $user->age = $i%100;
    $user->username="User".$i;
    $user->getUserInfo();
}
```

有 setter版：

```php
class User
{
    public $uid;
    private $username;
    public $age;
    function setUserName($name)
    {
        $this->username = $name;
    }
    function getUserInfo()
    {
        return "UID:".$this->uid." UserName:".$this->username." Age:".$this->age;
    }
}
for($i=0; $i<1000000;$i++) {
    $user = new User();
    $user->uid= $i;
    $user->age = $i%100;
    $user->setUserName("User".$i);
    $user->getUserInfo();
}
```

这里只增加了一个 setter。运行结果如下：

```
    [root@localhostphpperf]# time php7 object.php
    real 0m0.607s 
    user 0m0.602s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 object.php
    real 0m0.598s 
    user 0m0.596s 
    sys 0m0.000s 
    [root@localhostphpperf]# time php7 object2.php
    real 0m0.673s 
    user 0m0.669s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 object2.php
    real 0m0.668s 
    user 0m0.664s 
    sys 0m0.004s
```

从上面可以看到，**`增加了一个 setter，带来了近10%的效率损失`**。可见这个性能损失是相当大的，在 PHP 中，我们没有必要再来做 setter 和 getter了。需要引用的属性，直接使用即可。

### 2.9、类属性该声明还是不声明

PHP 本身支持属性可以在使用时增加，也就是不声明属性，可以在运行时添加属性。那么问题来了，事先声明属性与事后增加属性，是否会有性能上的差别。这里也举一个例子探讨一下。

事先声明了属性的代码就是2.8节中，无 setter 的代码，不再重复。而无属性声明的代码如下：

```php
class User
{ 
    function getUserInfo()
    {
        return "UID:".$this->uid." UserName:".$this->username." Age:".$this->age;
    }
}
for($i=0; $i<1000000;$i++) {
    $user = new User();
    $user->uid= $i;
    $user->age = $i%100;
    $user->username="User".$i;
    $user->getUserInfo();
}
```

两段代码，运行结果如下：

```
    [root@localhostphpperf]# time php7 object.php
    real 0m0.608s 
    user 0m0.604s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 object.php
    real 0m0.615s 
    user 0m0.605s 
    sys 0m0.003s 
    [root@localhostphpperf]# time php7 object3.php
    real 0m0.733s 
    user 0m0.728s 
    sys 0m0.004s 
    [root@localhostphpperf]# time php7 object3.php
    real 0m0.727s 
    user 0m0.720s 
    sys 0m0.004s
```

从上面的运行可以看到，**`无属性声明的代码慢了20%`**。可以推断出来的就是对于对象的属性，如果事先知道的话，我们还是**`事先声明的好`**，这一方面是效率问题，另一方面，也有助于提高代码的可读性呢。

### 2.10、图片操作 API 的效率差别

在图片处理操作中，一个非常常见的操作是将图片缩放成小图。缩放成小图的办法有多种，有使用 API 的，有使用命令行的。在 PHP 中，有 i[Magic][4]k 和 gmagick 两个扩展可供操作，而命令行则一般使用 convert 命令来处理。我们这里来讨论使用 imagick 扩展中的 API 处理图片的效率差别。

先上代码：

```php
function imagick_resize($filename, $outname)
{
    $thumbnail = new Imagick($filename);
    $thumbnail->resizeImage(200, 200, imagick::FILTER_LANCZOS, 1);
    $thumbnail->writeImage($outname);
    unset($thumbnail);
}
function imagick_scale($filename, $outname)
{
    $thumbnail = new Imagick($filename);
    $thumbnail->scaleImage(200, 200);
    $thumbnail->writeImage($outname);
    unset($thumbnail);
}
function convert($func)
{
    $cmd= "find /var/data/ppt |grep jpg";
    $start = microtime(true);
    exec($cmd, $files);
    $index = 0;
    foreach($files as $key =>$filename)
    {
        $outname= " /tmp/$func"."_"."$key.jpg";
        $func($filename, $outname);
        $index++;
    }
    $end = microtime(true);
    echo "$func $index files: " . ($end- $start) . "s\n";
}
convert("imagick_resize");
convert("imagick_scale");
```

在上面的代码中，我们分别使用了 resizeImage 和 scaleImage 来进行图片的压缩，压缩的是常见的 1-3M 之间的数码相机图片，得到如下运行结果：

```
    [root@localhostphpperf]# php55 imagick.php
    imagick_ resize 169 files: 5.0612308979034s 
    imagick_ scale 169 files: 3.1105840206146s
    [root@localhostphpperf]# php55 imagick.php
    imagick_ resize 169 files: 4.4953861236572s 
    imagick_ scale 169 files: 3.1514940261841s
    [root@localhostphpperf]# php55 imagick.php
    imagick_ resize 169 files: 4.5400381088257s 
    imagick_ scale 169 files: 3.2625908851624s
```

169张图片压缩，使用 resizeImage 压缩，速度在4.5S以上，而使用 scaleImage 则在 3.2S 左右，快了将近50%，压缩的效果，用肉眼看不出明显区别。当然 resizeImage 的控制能力更强，不过对于批量处理而言，使用 scaleImage 是更好的选择，尤其对头像压缩这种频繁大量的操作。本节只是例举了图片压缩 API 作为例子，也正像 explode 和 preg_ split 一样，在 PHP 中，完成同样一件事情，往往有多种手法。建议采用效率高的做法。

以上就是关于 PHP 开发的10个方面的对比，这些点涉及到 PHP 语法、写法以及 API 的使用。有些策略随着 PHP 的发展，有的已经不再适用，有些策略则会一直有用。

有童鞋也许会说，在现实的开发应用中，上面的某些观点和解决策略，有点「然并卵」。为什么这么说呢？因为在一个程序的性能瓶颈中，最为核心的瓶颈，往往并不在 PHP 语言本身。即使是跟 PHP 代码中暴露出来的性能瓶颈，也常在外部资源和程序的不良写法导致的瓶颈上。于是为了做好性能分析，我们需要向 PHP 的上下游戏延伸，比如延伸到后端的服务上去，比如延伸到前端的优化规则。在这两块，都有了相当多的积累和分析，雅虎也据此提出了多达35条前端优化规则，这些同 PHP 本身的性能分析构成了一个整体，就是降低用户的访问延时。

所以前面两部分所述的性能分析，只是有助于大家了解 PHP 开发本身，写出更好的 PHP 程序，为你成为一个资深的 PHP [程序员][5]打下基础，对于实际生产中程序的效率提升，往往帮助也不是特别显著，因为大家也看到，在文章的实例中，很多操作往往是百万次才能看出明显的性能差别。在现实的页面中，每一个请求很快执行完成，对这些基础代码的调用，往往不会有这么多次调用。不过了解这些，总是好的。

那么，对于一个程序而言，其他的性能瓶颈可能存在哪里？我们将深入探讨。所以在本系列的下两篇，我们将探讨 PHP 程序的外围效源的效率问题和前端效率问题，敬请期待。

[0]: https://www.zhihu.com/people/phpgod
[1]: http://link.zhihu.com/?target=http%3A//www.codeceo.com/article/php-performance-analis.html
[2]: http://link.zhihu.com/?target=http%3A//www.codeceo.com/article/float-number.html
[3]: http://link.zhihu.com/?target=https%3A//github.com/composer/composer/commit/ac676f47f7bbc619678a29deae097b6b0710b799
[4]: http://link.zhihu.com/?target=http%3A//www.codeceo.com/article/magic-javascript-ui.html
[5]: http://link.zhihu.com/?target=http%3A//www.codeceo.com/