# PHP魔法函数性能分析

Posted on 3月 4 2017 · [php][0]

# 前言

曾经记得鸟哥Laruence提过不建议使用”魔术方法”，自此之后一旦涉及使用魔术方法的地方，我都会下意识的想一下，这样写真的好吗？由于这一到两年来一直忙于工作和学习新的知识，所以在这道坎儿上一直没有做深入的探索一直恍恍惚惚过去了，今年是我进行深入学习的一年，因此现在必须对这个问题做个了结了。我们先来看看鸟哥Laruence博客曾经提到的：

> 当我把PPT分享给公司的同事的时候, 会有人质疑, 魔术方法都不让用?  
> 优化的建议, 是建议, 是防止大家滥用, 肆无忌惮的用. 如果你能在写代码的时候, 能意识到, 什么慢, 什么快, 从而避免一些没有必要的对魔术方法的调用, 那就是这个优化建议所追求的效果了

# 疑惑

1. 魔术方法真的性能比较差吗？
1. PHP7里使用魔术方法的性能还是存在问题吗？
1. 我们应该如何合理的使用魔术方法？

# 方案

面对我的疑惑，我的方案是:

* 统计对比使用魔术方法和不使用魔术方法脚本执行的时间差异
* PHP5.6.26-1 下连续执行脚本n次
* 统计执行时间的平均值/最小值/最大值
* PHP7.0.12-2 下连续执行脚本n次
* 统计执行时间的平均值/最小值/最大值

目前个人能力有限，只能通过这种方式，如果你有更好的方案或者建议可以告诉我，谢谢，haha~

# 测试

### __construct

首先我们先来看看构造函数`__construct`的实验，php脚本如下：

    
```php
<?php

/**

 * 魔术方法性能探索

 *

 * 构造函数

 *

 * @author TIGERB <https://github.com/TIGERB>

 */

require('./function.php');

if (!isset($argv[1])) {
    die('error: variable is_use_magic is empty');

}

$is_use_magic = $argv[1];

/**

 * 构造函数使用类名

 */

class ClassOne

{
    public function classOne()
    {
        # code...
    }

}

/**

 * 构造函数使用魔术函数__construct

 */

class ClassTwo

{
    public function __construct()
    {
        # code...
    }

}

$a = getmicrotime();

if ($is_use_magic === 'no_magic') {
    new ClassOne();

}else {
    new ClassTwo();

}

$b = getmicrotime();

echo  ($b-$a) . "\n";
```
* PHP5.6不使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 no_magic php5 construct

// 运行数据统计脚本

sh analysis ./logs/__construct_no_magic_php5.log 10000

// 结果

avg: 34μs

max: 483μs

min: 26μs
```
* PHP5.6使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 magic php5 construct

// 运行数据统计脚本

sh analysis ./logs/__construct_magic_php5.log 10000

// 结果

avg: 28μs

max: 896μs

min: 20μs
```
* PHP7.0不使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 no_magic php construct

// 运行数据统计脚本

sh analysis ./logs/__construct_no_magic_php.log 10000

// 结果

avg: 19μs

max: 819μs

min: 13μs
```
* PHP7.0使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 magic php construct

// 运行数据统计脚本

sh analysis ./logs/__construct_magic_php.log 10000

// 结果

avg: 14μs

max: 157μs

min: 10μs
```
通过上面的数据我们可以看出：

> 使用__construct作为构造函数的脚本执行的平均时间是要快于使用类名作为构造函数的，**> 大概快5到6微秒**> ，不论是在php5.6还是php7.0中。

### __call

接着，我们来看看`__call`的实验，php脚本如下：


```php
<?php

/**

 * 魔术方法性能探索

 *

 * 构造函数

 *

 * @author TIGERB <https://github.com/TIGERB>

 */

require('./function.php');

if (!isset($argv[1])) {
    die('error: variable is_use_magic is empty');

}

$is_use_magic = $argv[1];

/**

 * 构造函数使用类名

 */

class ClassOne

{
    public function __construct()
    {
        # code...
    }
    public function test()
    {
        # code...
    }

}

/**

 * 构造函数使用魔术函数__construct

 */

class ClassTwo

{
    public function __construct()
    {
        # code...
    }
    public function __call($method, $argus)
    {
        # code...
    }

}

$a = getmicrotime();

if ($is_use_magic === 'no_magic') {
    $instance = new ClassOne();
    $instance->test();

}else {
    $instance = new ClassTwo();
    $instance->test();

}

$b = getmicrotime();

echo  ($b-$a) . "\n";
```
* PHP5.6不使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 no_magic php5 call

// 运行数据统计脚本

sh analysis ./logs/__call_no_magic_php5.log 10000

// 结果

avg: 27μs

max: 206μs

min: 20μs
```
* PHP5.6使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 magic php5 call

// 运行数据统计脚本

sh analysis ./logs/__call_magic_php5.log 10000

// 结果

avg: 29μs

max: 392μs

min: 22μs
```
* PHP7.0不使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 no_magic php call

// 运行数据统计脚本

sh analysis ./logs/__call_no_magic_php.log 10000

// 结果

avg: 16μs

max: 256μs

min: 10μs
```
* PHP7.0使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 magic php call

// 运行数据统计脚本

sh analysis ./logs/__call_magic_php.log 10000

// 结果

avg: 18μs

max: 2459μs

min: 11μs
```
通过上面的数据我们可以看出：

> 使用`__call`的脚本执行的平均时间是要慢于不使用，**大概慢2微秒**，不论是在php5.6还是php7.0中。

### __callStatic

接着，我们来看看`__callStatic`的实验，php脚本如下：

    
```php
<?php

/**

 * 魔术方法性能探索

 *

 * 静态重载函数

 *

 * @author TIGERB <https://github.com/TIGERB>

 */

require('./function.php');

if (!isset($argv[1])) {
    die('error: variable is_use_magic is empty');

}

$is_use_magic = $argv[1];

/**

 * 存在test静态方法

 */

class ClassOne

{
    public function __construct()
    {
        # code...
    }
    public static function test()
    {
        # code...
    }

}

/**

 * 使用重载实现test

 */

class ClassTwo

{
    public function __construct()
    {
        # code...
    }
    public static function __callStatic($method, $argus)
    {
        # code...
    }

}

$a = getmicrotime();

if ($is_use_magic === 'no_magic') {
    ClassOne::test();

}else {
    ClassTwo::test();

}

$b = getmicrotime();

echo  ($b-$a) . "\n";
```
* PHP5.6不使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 no_magic php5 callStatic

// 运行数据统计脚本

sh analysis ./logs/__callStatic_no_magic_php5.log 10000

// 结果

avg: 25μs

max: 129μs

min: 19μs
```
* PHP5.6使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 magic php5 callStatic

// 运行数据统计脚本

sh analysis ./logs/__callStatic_magic_php5.log 10000

// 结果

avg: 28μs

max: 580μs

min: 20μs
```
* PHP7.0不使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 no_magic php callStatic

// 运行数据统计脚本

sh analysis ./logs/__callStatic_no_magic_php.log 10000

// 结果

avg: 14μs

max: 130μs

min: 9μs
```
* PHP7.0使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 magic php callStatic

// 运行数据统计脚本

sh analysis ./logs/__callStatic_magic_php.log 10000

// 结果

avg: 14μs

max: 159μs

min: 10μs
```
通过上面的数据我们可以看出：

> 在php5.6中使用`__callStatic`的脚本执行的平均时间是要慢于不使用，**大概慢3微秒**;在php7.0中使用`__callStatic`的脚本执行的平均时间是要大致等于不使用`__callStatic`的；

### __set

接着，我们来看看`__set`的实验，php脚本如下：

    
```php
<?php

/**

 * 魔术方法性能探索

 *

 * 设置私有属性__set

 *

 * @author TIGERB <https://github.com/TIGERB>

 */

require('./function.php');

if (!isset($argv[1])) {
    die('error: variable is_use_magic is empty');

}

$is_use_magic = $argv[1];

/**

 * 实现公共方法设置私有属性

 */

class ClassOne

{
    /**
     * 私有属性
     *
     * @var string
     */
    private $someVariable = 'private';
    public function __construct()
    {
        # code...
    }
    public function setSomeVariable($value = '')
    {
        $this->someVariable = $value;
    }

}

/**

 * 使用_set设置私有属性

 */

class ClassTwo

{
    /**
     * 私有属性
     *
     * @var string
     */
    private $someVariable = 'private';
    public function __construct()
    {
        # code...
    }
    public function __set($name = '', $value = '')
    {
        $this->$name = $value;
    }

}

$a = getmicrotime();

if ($is_use_magic === 'no_magic') {
    $instance = new ClassOne();
    $instance->setSomeVariable('public');

}else {
    $instance = new ClassTwo();
    $instance->someVariable = 'public';

}

$b = getmicrotime();

echo  ($b-$a) . "\n";
```
* PHP5.6不使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 no_magic php5 set

// 运行数据统计脚本

sh analysis ./logs/__set_no_magic_php5.log 10000

// 结果

avg: 31μs

max: 110μs

min: 24μs
```
* PHP5.6使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 magic php5 set

// 运行数据统计脚本

sh analysis ./logs/__set_magic_php5.log 10000

// 结果

avg: 33μs

max: 138μs

min: 25μs
```
* PHP7.0不使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 no_magic php set

// 运行数据统计脚本

sh analysis ./logs/__set_no_magic_php.log 10000

// 结果

avg: 15μs

max: 441μs

min: 11μs
```
* PHP7.0使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 magic php set

// 运行数据统计脚本

sh analysis ./logs/__set_magic_php.log 10000

// 结果

avg: 17μs

max: 120μs

min: 11μs
```
通过上面的数据我们可以看出：

> 使用`__set`的脚本执行的平均时间是要慢于不使用，**大概慢2微秒**，不论是在php5.6还是php7.0中。

### __get

接着，我们来看看`__get`的实验，php脚本如下：
    
    
```php
<?php

/**

 * 魔术方法性能探索

 *

 * 读取私有属性__get

 *

 * @author TIGERB <https://github.com/TIGERB>

 */

require('./function.php');

if (!isset($argv[1])) {
    die('error: variable is_use_magic is empty');

}

$is_use_magic = $argv[1];

/**

 * 实现公共方法获取私有属性

 */

class ClassOne

{
    /**
     * 私有属性
     *
     * @var string
     */
    private $someVariable = 'private';
    public function __construct()
    {
        # code...
    }
    public function getSomeVariable()
    {
        return $this->someVariable;
    }

}

/**

 * 使用_get获取私有属性

 */

class ClassTwo

{
    /**
     * 私有属性
     *
     * @var string
     */
    private $someVariable = 'private';
    public function __construct()
    {
        # code...
    }
    public function __get($name = '')
    {
        return $this->$name;
    }

}

$a = getmicrotime();

if ($is_use_magic === 'no_magic') {
    $instance = new ClassOne();
    $instance->getSomeVariable();

}else {
    $instance = new ClassTwo();
    $instance->someVariable;

}

$b = getmicrotime();

echo  ($b-$a) . "\n";
```
* PHP5.6不使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 no_magic php5 get

// 运行数据统计脚本

sh analysis ./logs/__get_no_magic_php5.log 10000

// 结果

avg: 28μs

max: 590μs

min: 20μs
```
* PHP5.6使用魔术方法数据如下，单位微秒μs


```
// PHP5.6中连续调用脚本10000次

sh test 10000 magic php5 get

// 运行数据统计脚本

sh analysis ./logs/__get_magic_php5.log 10000

// 结果

avg: 28μs

max: 211μs

min: 22μs
```
* PHP7.0不使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 no_magic php get

// 运行数据统计脚本

sh analysis ./logs/__get_no_magic_php.log 10000

// 结果

avg: 16μs

max: 295μs

min: 10μs
```
* PHP7.0使用魔术方法数据如下，单位微秒μs


```
// PHP7.0中连续调用脚本10000次

sh test 10000 magic php get

// 运行数据统计脚本

sh analysis ./logs/__get_magic_php.log 10000

// 结果

avg: 19μs

max: 525μs

min: 12μs
```
通过上面的数据我们可以看出：

> 在php5.6中使用`__get`的脚本执行的平均时间是要大致等于不使用`__get`的;在php7.0中使用`__get`的脚本执行的平均时间是要慢于不使用，**大概慢3微秒**。

# 结语

这里主要测试了`__construct()`，` __call()`， `__callStatic()`， `__get()`， `__set()`这五个常用的且可有其他实现方式代替的魔法函数。通过上面的测试再回来解答我的疑惑

> 1. 魔术方法真的性能比较差吗？

答：除了使用`__construct`之外，这里使用其他的魔法方法的时间大致慢10微秒以内。

> 1. PHP7里使用魔术方法的性能还是存在问题吗？

答：在PHP7中使用与不使用魔术方法之间的差异和在PHP5.6中近乎一致。

> 1. 我们应该如何合理的使用魔术方法？

答：通过整个测试我们可以看出使不使用魔法方法这之间的执行时间差异大致都是在10微秒以内的，所以如果魔法方法可以很好的节省我们的开发成本和优化我们的代码结构，我们应该可以考虑牺牲掉这不到10微秒。而`__construct`是要快的，所以使用`__construct`应该没什么异议。

# 脚本源码

[https://github.com/TIGERB/easy-tips/tree/master/php/magic-function][1]

[0]: http://tigerb.cn/tags/php/
[1]: https://github.com/TIGERB/easy-tips/tree/master/php/magic-function