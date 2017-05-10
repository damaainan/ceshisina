## php Glob() 使用

1. 函数的任意数目的参数

你可能知道PHP允许你定义一个默认参数的函数。但你可能并不知道PHP还允许你定义一个完全任意的参数的函数

下面是一个示例向你展示了默认参数的函数：

// 两个默认参数的函数
```php
function foo($arg1 = '', $arg2 = '') {

echo "arg1: $arg1\n";

echo "arg2: $arg2\n";

}

foo('hello','world');

foo();
```
现在我们来看一看一个不定参数的函数，其使用到了?func_get_args()方法：
```php
// 是的，形参列表为空

function foo() {

// 取得所有的传入参数的数组

$args = func_get_args();

foreach ($args as $k => $v) {

echo "arg".($k+1).": $v\n";

}

}

foo();

foo('hello');

foo('hello', 'world', 'again');
```
2. 使用 Glob() 查找文件

很多PHP的函数都有一个比较长的自解释的函数名，但是，当你看到?glob() 的时候，你可能并不知道这个函数是用来干什么的，除非你对它已经很熟悉了。

你可以认为这个函数就好? **scandir()** 一样，其可以用来查找文件。
```php
// 取得所有的后缀为PHP的文件

$files = glob('*.php');

print_r($files);
```
你还可以查找多种后缀名
```php
// 取PHP文件和TXT文件

$files = glob('*.{php,txt}', GLOB_BRACE);

print_r($files);
```
你还可以加上路径：
```php
$files = glob('../images/a*.jpg');

print_r($files);
```
如果你想得到绝对路径，你可以调用?realpath() 函数：
```php
$files = glob('../images/a*.jpg');

// applies the function to each array element

$files = array_map('realpath',$files);

print_r($files);
```
3. 内存使用信息

观察你程序的内存使用能够让你更好的优化你的代码。

PHP 是有垃圾回收机制的，而且有一套很复杂的内存管理机制。你可以知道你的脚本所使用的内存情况。要知道当前内存使用情况，你可以使 用?memory_get_usage() 函数，如果你想知道使用内存的峰值，你可以调用memory_get_peak_usage() 函数。
```php
echo "Initial: ".memory_get_usage()." bytes \n";

// 使用内存

for ($i = 0; $i < 100000; $i++) {

$array []= md5($i);

}

// 删除一半的内存

for ($i = 0; $i < 100000; $i++) {

unset($array[$i]);

}

echo "Final: ".memory_get_usage()." bytes \n";

echo "Peak: ".memory_get_peak_usage()." bytes \n";
```
4. CPU使用信息

使用?getrusage() 函数可以让你知道CPU的使用情况。注意，这个功能在Windows下不可用。

    print_r(getrusage());

这个结构看上出很晦涩，除非你对CPU很了解。下面一些解释：
```
ru_oublock: 块输出操作

ru_inblock: 块输入操作

ru_msgsnd: 发送的message

ru_msgrcv: 收到的message

ru_maxrss: 最大驻留集大小

ru_ixrss: 全部共享内存大小

ru_idrss:全部非共享内存大小

ru_minflt: 页回收

ru_majflt: 页失效

ru_nsignals: 收到的信号

ru_nvcsw: 主动上下文切换

ru_nivcsw: 被动上下文切换

ru_nswap: 交换区

ru_utime.tv_usec: 用户态时间 (microseconds)

ru_utime.tv_sec: 用户态时间(seconds)

ru_stime.tv_usec: 系统内核时间 (microseconds)

ru_stime.tv_sec: 系统内核时间?(seconds)
```
要看到你的脚本消耗了多少CPU，我们需要看看“用户态的时间”和“系统内核时间”的值。秒和微秒部分是分别提供的，您可以把微秒值除以100万，并把它添加到秒的值后，可以得到有小数部分的秒数。
```php
// sleep for 3 seconds (non-busy)

sleep(3);

$data = getrusage();

echo "User time: ".

($data['ru_utime.tv_sec'] +

$data['ru_utime.tv_usec'] / 1000000);

echo "System time: ".

($data['ru_stime.tv_sec'] +

$data['ru_stime.tv_usec'] / 1000000);

sleep是不占用系统时间的，我们可以来看下面的一个例子：

// loop 10 million times (busy)

for($i=0;$i<10000000;$i++) {

}

$data = getrusage();

echo "User time: ".

($data['ru_utime.tv_sec'] +

$data['ru_utime.tv_usec'] / 1000000);

echo "System time: ".

($data['ru_stime.tv_sec'] +

$data['ru_stime.tv_usec'] / 1000000);
```
这花了大约14秒的CPU时间，几乎所有的都是用户的时间，因为没有系统调用。

系统时间是CPU花费在系统调用上的上执行内核指令的时间。下面是一个例子：
```php
$start = microtime(true);

// keep calling microtime for about 3 seconds

while(microtime(true) - $start < 3) {

}

$data = getrusage();

echo "User time: ".

($data['ru_utime.tv_sec'] +

$data['ru_utime.tv_usec'] / 1000000);

echo "System time: ".

($data['ru_stime.tv_sec'] +

$data['ru_stime.tv_usec'] / 1000000);
```
我们可以看到上面这个例子更耗CPU。

5. 系统常量

PHP 提供非常有用的系统常量 可以让你得到当前的行号 (`__LINE__`)，文件 (`__FILE__`)，目录 (`__DIR__`)，函数名 (`__FUNCTION__`)，类名(`__CLASS__`)，方法名(`__METHOD__`) 和名字空间 (`__NAMESPACE__`)，很像C语言。

我们可以以为这些东西主要是用于调试，当也不一定，比如我们可以在include其它文件的时候使用?`__FILE__` (当然，你也可以在 PHP 5.3以后使用 `__DIR__` )，下面是一个例子。

```php
// this is relative to the loaded script's path

// it may cause problems when running scripts from different directories

require_once('config/database.php');

// this is always relative to this file's path

// no matter where it was included from

require_once(dirname(__FILE__) . '/config/database.php');
```
下面是使用 __LINE__ 来输出一些debug的信息，这样有助于你调试程序：
```php
// some code

// ...

my_debug("some debug message", __LINE__);

// some more code

// ...

my_debug("another debug message", __LINE__);

function my_debug($msg, $line) {

echo "Line $line: $msg\n";

}
```
6.生成唯一的ID

有很多人使用 md5() 来生成一个唯一的ID，如下所示：
```php
// generate unique string

echo md5(time() . mt_rand(1,1000000));

其实，PHP中有一个叫?uniqid() 的函数是专门用来干这个的：

// generate unique string

echo uniqid();

// generate another unique string

echo uniqid();
```
可能你会注意到生成出来的ID前几位是一样的，这是因为生成器依赖于系统的时间，这其实是一个非常不错的功能，因为你是很容易为你的这些ID排序的。这点MD5是做不到的。

你还可以加上前缀避免重名：
```php
// 前缀

echo uniqid('foo_');

// 有更多的熵

echo uniqid('',true);

// 都有

echo uniqid('bar_',true);
```
而且，生成出来的ID会比MD5生成的要短，这会让你节省很多空间。

7. 序列化

你是否会把一个比较复杂的数据结构存到数据库或是文件中?你并不需要自己去写自己的算法。PHP早已为你做好了，其提供了两个函数：?serialize() 和 unserialize():
```php
// 一个复杂的数组

$myvar = array(

'hello',

42,

array(1,'two'),

'apple'

);

// 序列化

$string = serialize($myvar);

echo $string;

// 反序例化

$newvar = unserialize($string);

print_r($newvar);
```
这是PHP的原生函数，然而在今天JSON越来越流行，所以在PHP5.2以后，PHP开始支持JSON，你可以使用 json_encode() 和 json_decode() 函数
```php
// a complex array

$myvar = array(

'hello',

42,

array(1,'two'),

'apple'

);

// convert to a string

$string = json_encode($myvar);

echo $string;

// you can reproduce the original variable

$newvar = json_decode($string);

print_r($newvar);
```
这看起来更为紧凑一些了，而且还兼容于Javascript和其它语言。但是对于一些非常复杂的数据结构，可能会造成数据丢失。

8. 字符串压缩

当我们说到压缩，我们可能会想到文件压缩，其实，字符串也是可以压缩的。PHP提供了?gzcompress() 和 gzuncompress() 函数：
```php
$string =

"Lorem ipsum dolor sit amet, consectetur

adipiscing elit. Nunc ut elit id mi ultricies

adipiscing. Nulla facilisi. Praesent pulvinar,

sapien vel feugiat vestibulum, nulla dui pretium orci,

non ultricies elit lacus quis ante. Lorem ipsum dolor

sit amet, consectetur adipiscing elit. Aliquam

pretium ullamcorper urna quis iaculis. Etiam ac massa

sed turpis tempor luctus. Curabitur sed nibh eu elit

mollis congue. Praesent ipsum diam, consectetur vitae

ornare a, aliquam a nunc. In id magna pellentesque

tellus posuere adipiscing. Sed non mi metus, at lacinia

augue. Sed magna nisi, ornare in mollis in, mollis

sed nunc. Etiam at justo in leo congue mollis.

Nullam in neque eget metus hendrerit scelerisque

eu non enim. Ut malesuada lacus eu nulla bibendum

id euismod urna sodales. ";

$compressed = gzcompress($string);

echo "Original size: ". strlen($string)."\n";

echo "Compressed size: ". strlen($compressed)."\n";

// 解压缩

$original = gzuncompress($compressed);
```
几乎有50% 压缩比率。同时，你还可以使用?gzencode() 和 gzdecode() 函数来压缩，只不用其用了不同的压缩算法。

9. 注册停止函数

有一个函数叫做?register_shutdown_function()，可以让你在整个脚本停时前运行代码。让我们看下面的一个示例：

```php
// capture the start time

$start_time = microtime(true);

// do some stuff

// ...

// display how long the script took

echo "execution took: ".

(microtime(true) - $start_time).

" seconds.";
```
上面这个示例只不过是用来计算某个函数运行的时间。然后，如果你在函数中间调用?exit() 函数，那么你的最后的代码将不会被运行到。并且，如果该脚本在浏览器终止(用户按停止按钮)，其也无法被运行。

而当我们使用了register_shutdown_function()后，你的程序就算是在脚本被停止后也会被运行：
```php
$start_time = microtime(true);

register_shutdown_function('my_shutdown');

// do some stuff

// ...

function my_shutdown() {

global $start_time;

echo "execution took: ".

(microtime(true) - $start_time).

" seconds.";

}
```



