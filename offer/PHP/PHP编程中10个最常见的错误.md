# PHP编程中10个最常见的错误，你犯过几个？ 

PHP是一种非常流行的开源服务器端脚本语言，你在万维网看到的大多数网站都是使用php开发的。本文将为大家介绍PHP开发中10个最常见的问题，希望能够对大家有所帮助。

## 错误1：foreach循环后留下悬挂指针

在foreach循环中，如果我们需要更改迭代的元素或是为了提高效率，运用引用是一个好办法：

```php
<?php
$arr = array(1, 2, 3, 4);
foreach ($arr as &$value) {
    $value = $value * 2;
}
// $arr is now array(2, 4, 6, 8)
```

这里有个问题很多人会迷糊。循环结束后，`$value`并未销毁，`$value`其实是数组中最后一个元素的引用，这样在后续对`$value`的使用中，如果不知道这一点，会引发一些莫名奇妙的错误:)看看下面这段代码：

```php
<?php
$array = [1, 2, 3];
echo implode(',', $array), "\n";

foreach ($array as &$value) {}    // by reference
echo implode(',', $array), "\n";

foreach ($array as $value) {}     // by value (i.e., copy)
echo implode(',', $array), "\n";
```

上面代码的运行结果如下：

```
    1,2,3
    1,2,3
    1,2,2
```

你猜对了吗？为什么是这个结果呢？

我们来分析下。第一个循环过后，$value是数组中最后一个元素的引用。第二个循环开始：

* 第一步：复制`$arr[0]`到`$value`（注意此时`$value`是`$arr[2]`的引用），这时数组变成[1,2,1]
* 第二步：复制`$arr[1]`到`$value`，这时数组变成[1,2,2]
* 第三步：复制`$arr[2]`到`$value`，这时数组变成[1,2,2]

综上，最终结果就是1,2,2

避免这种错误最好的办法就是在循环后立即用`unset`函数销毁变量：

```php
<?php
$arr = array(1, 2, 3, 4);
foreach ($arr as &$value) {
    $value = $value * 2;
}
unset($value);   // $value no longer references $arr[3]
```

## 错误2：对isset()函数行为的错误理解

对于`isset()`函数，变量不存在时会返回false，变量值为null时也会返回false。这种行为很容易把人弄迷糊。。。看下面的代码：

```
    $data = fetchRecordFromStorage($storage, $identifier);
    if (!isset($data['keyShouldBeSet']) {
        // do something here if 'keyShouldBeSet' is not set
    }
```

写这段代码的人本意可能是如果$data['keyShouldBeSet']未设置，则执行对应逻辑。但问题在于即使$data['keyShouldBeSet']已设置，但设置的值为null，还是会执行对应的逻辑，这就不符合代码的本意了。

下面是另外一个例子：

```php
<?php
if ($_POST['active']) {
    $postData = extractSomething($_POST);
}

// ...

if (!isset($postData)) {
    echo 'post not active';
}
```

上面的代码假设`$_POST['active']`为真，那么`$postData`应该被设置，因此`isset($postData)`会返回true。反之，上 面代码假设`isset($postData)`返回false的唯一途径就是`$_POST['active']`也返回false。

真是这样吗？当然不是！

即使`$_POST['active']`返回true，`$postData`也有可能被设置为null，这时`isset($postData)`就会返回false。这就不符合代码的本意了。

如果上面代码的本意仅是检测`$_POST['active']`是否为真，下面这样实现会更好：

```php
<?php
if ($_POST['active']) {
    $postData = extractSomething($_POST);
}

// ...

if ($_POST['active']) {
    echo 'post not active';
}
```

判断一个变量是否真正被设置（区分未设置和设置值为null），`array_key_exists()`函数或许更好。重构上面的第一个例子，如下：

```php
<?php
$data = fetchRecordFromStorage($storage, $identifier);
if (! array_key_exists('keyShouldBeSet', $data)) {
// do this if 'keyShouldBeSet' isn't set
}
```

另外，结合`get_defined_vars()`函数，我们可以更加可靠的检测变量在当前作用域内是否被设置：

```php
<?php
if (array_key_exists('varShouldBeSet', get_defined_vars())) {
// variable $varShouldBeSet exists in current scope
}
```

## 错误3：混淆返回值和返回引用

考虑下面的代码：

```php
<?php
class Config
{
    private $values = [];
    
    public function getValues() {
        return $this->values;
    }
}

$config = new Config();

$config->getValues()['test'] = 'test';
echo $config->getValues()['test'];
```

运行上面的代码，将会输出下面的内容：

```
    PHP Notice:  Undefined index: test in /path/to/my/script.php on line 21
```

问题出在哪呢？问题就在于上面的代码混淆了返回值和返回引用。在PHP中，除非你显示的指定返回引用，否则对于数组PHP是值返回，也就是数组的拷贝。因此上面代码对返回数组赋值，实际是对拷贝数组进行赋值，非原数组赋值。

```php
<?php
// getValues() returns a COPY of the $values array, so this adds a 'test' element
// to a COPY of the $values array, but not to the $values array itself.
$config->getValues()['test'] = 'test';

// getValues() again returns ANOTHER COPY of the $values array, and THIS copy doesn't
// contain a 'test' element (which is why we get the "undefined index" message).
echo $config->getValues()['test'];
```

下面是一种可能的解决办法，输出拷贝的数组，而不是原数组：

```php
<?php
$vals = $config->getValues();
$vals['test'] = 'test';
echo $vals['test'];
```

如果你就是想要改变原数组，也就是要反回数组引用，那应该如何处理呢？办法就是显示指定返回引用即可：

```php
<?php
class Config
{
    private $values = [];
    
    // return a REFERENCE to the actual $values array
    public function &getValues() {
        return $this->values;
    }
}

$config = new Config();

$config->getValues()['test'] = 'test';
echo $config->getValues()['test'];
```

经过改造后，上面代码将会像你期望那样会输出test。

我们再来看一个例子会让你更迷糊的例子：

```php
<?php
class Config
{
    private $values;
    
    // using ArrayObject rather than array
    public function __construct() {
        $this->values = new ArrayObject();
    }
    
    public function getValues() {
        return $this->values;
    }
}

$config = new Config();

$config->getValues()['test'] = 'test';
echo $config->getValues()['test'];
```

如果你想的是会和上面一样输出“ Undefined index”错误，那你就错了。代码会正常输出“test”。原因在于PHP对于对象默认就是按引用返回的，而不是按值返回。

综上所述，我们在使用函数返回值时，要弄清楚是值返回还是引用返回。PHP中对于对象，默认是引用返回，数组和内置基本类型默认均按值返回。这个要与其它语言区别开来（很多语言对于数组是引用传递）。

像其它语言，比如java或C#，利用getter或setter来访问或设置类属性是一种更好的方案，当然PHP默认不支持，需要自己实现：

```php
<?php
class Config
{
    private $values = [];
    
    public function setValue($key, $value) {
        $this->values[$key] = $value;
    }
    
    public function getValue($key) {
        return $this->values[$key];
    }
}

$config = new Config();

$config->setValue('testKey', 'testValue');
echo $config->getValue('testKey');    // echos 'testValue'
```

上面的代码给调用者可以访问或设置数组中的任意值而不用给与数组public访问权限。感觉怎么样:)

## 错误4：在循环中执行sql查询

在PHP编程中发现类似下面的代码并不少见：

```php
<?php
$models = [];

foreach ($inputValues as $inputValue) {
    $models[] = $valueRepository->findByValue($inputValue);
}
```

当然上面的代码是没有什么错误的。问题在于我们在迭代过程中`$valueRepository->findByValue()`可能每次都执行了sql查询：

```php
<?php
$result = $connection->query("SELECT `x`,`y` FROM `values` WHERE `value`=" . $inputValue);
```

如果迭代了10000次，那么你就分别执行了10000次sql查询。如果这样的脚本在多线程程序中被调用，那很可能你的系统就挂了。。。

在编写代码过程中，你应该要清楚什么时候应该执行sql查询，尽可能一次sql查询取出所有数据。

有一种业务场景，你很可能会犯上述错误。假设一个表单提交了一系列值（假设为IDs），然后为了取出所有ID对应的数据，代码将遍历IDs，分别对每个ID执行sql查询，代码如下所示：

```php
<?php
$data = [];
foreach ($ids as $id) {
    $result = $connection->query("SELECT `x`, `y` FROM `values` WHERE `id` = " . $id);
    $data[] = $result->fetch_row();
}
```

但同样的目的可以在一个sql中更加高效的完成，代码如下：

```php
<?php
$data = [];
if (count($ids)) {
    $result = $connection->query("SELECT `x`, `y` FROM `values` WHERE `id` IN (" . implode(',', $ids));
    while ($row = $result->fetch_row()) {
        $data[] = $row;
    }
}
```

## 错误5：内存使用低效和错觉

一次sql查询获取多条记录比每次查询获取一条记录效率肯定要高，但如果你使用的是php中的mysql扩展，那么一次获取多条记录就很可能会导致内存溢出。

我们可以写代码来实验下（测试环境： 512MB RAM、MySQL、php-cli）：

```php
<?php
// connect to mysql
$connection = new mysqli('localhost', 'username', 'password', 'database');

// create table of 400 columns
$query = 'CREATE TABLE `test`(`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT';
for ($col = 0; $col < 400; $col++) {
    $query .= ", `col$col` CHAR(10) NOT NULL";
}
$query .= ');';
$connection->query($query);

// write 2 million rows
for ($row = 0; $row < 2000000; $row++) {
    $query = "INSERT INTO `test` VALUES ($row";
    for ($col = 0; $col < 400; $col++) {
        $query .= ', ' . mt_rand(1000000000, 9999999999);
    }
    $query .= ')';
    $connection->query($query);
}
```

现在来看看资源消耗：

```php
<?php
// connect to mysql
$connection = new mysqli('localhost', 'username', 'password', 'database');
echo "Before: " . memory_get_peak_usage() . "\n";

$res = $connection->query('SELECT `x`,`y` FROM `test` LIMIT 1');
echo "Limit 1: " . memory_get_peak_usage() . "\n";

$res = $connection->query('SELECT `x`,`y` FROM `test` LIMIT 10000');
echo "Limit 10000: " . memory_get_peak_usage() . "\n";
```

输出结果如下：

```
    Before: 224704
    Limit 1: 224704
    Limit 10000: 224704
```

根据内存使用量来看，貌似一切正常。为了更加确定，试着一次获取100000条记录，结果程序得到如下输出：

```
    PHP Warning:  mysqli::query(): (HY000/2013): Lost connection to MySQL server during query in /root/test.php on line 11
```

这是怎么回事呢？

问题出在php的mysql模块的工作方式，mysql模块实际上就是libmysqlclient的一个代理。在查询获取多条记录的同时，这些记录会直接 保存在内存中。由于这块内存不属于php的内存模块所管理，所以我们调用memory_get_peak_usage()函数所获得的值并非真实使用内存 值，于是便出现了上面的问题。

我们可以使用mysqlnd来代替mysql，mysqlnd编译为php自身扩展，其内存使用由php内存管理模块所控制。如果我们用mysqlnd来实现上面的代码，则会更加真实的反应内存使用情况：

```
    Before: 232048
    Limit 1: 324952
    Limit 10000: 32572912
```

更加糟糕的是，根据php的官方文档，mysql扩展存储查询数据使用的内存是mysqlnd的两倍，因此原来的代码使用的内存是上面显示的两倍左右。

为了避免此类问题，可以考虑分几次完成查询，减小单次查询数据量：

```php
<?php
$totalNumberToFetch = 10000;
$portionSize = 100;

for ($i = 0; $i <= ceil($totalNumberToFetch / $portionSize); $i++) {
    $limitFrom = $portionSize * $i;
    $res = $connection->query("SELECT `x`,`y` FROM `test` LIMIT $limitFrom, $portionSize");
}
```

联系上面提到的错误4可以看出，在实际的编码过程中，要做到一种平衡，才能既满足功能要求，又能保证性能。

## 错误6：忽略Unicode/UTF-8问题

php编程中，在处理非ascii字符时，会遇到一些问题，要很小心的去对待，要不然就会错误遍地。举个简单的例子，strlen($name)，如果$name包含非ascii字符，那结果就有些出乎意料。在此给出一些建议，尽量避免此类问题：

* 如果你对unicode和utf-8不是很了解，那么你至少应该了解一些基础。推荐阅读[这篇文章][2]。
* 最好使用`mb_*`函数来处理字符串，避免使用老的字符串处理函数。这里要确保PHP的“multibyte”扩展已开启。
* 数据库和表最好使用unicode编码。
* 知道jason_code()函数会转换非ascii字符，但serialize()函数不会。
* php代码源文件最好使用不含bom的utf-8格式。

在此推荐一篇文章，更详细的介绍了此类问题： [UTF-8 Primer for PHP and MySQL][3]

## 错误7：假定`$_POST`总是包含POST数据

PHP中的`$_POST`并非总是包含表单POST提交过来的数据。假设我们通过 jQuery.ajax()方法向服务器发送了POST请求：

```php
<?php
// js
$.ajax({
    url: 'http://my.site/some/path',
    method: 'post',
    data: JSON.stringify({a: 'a', b: 'b'}),
    contentType: 'application/json'
});
```

注意代码中的 contentType: ‘application/json’ ，我们是以json数据格式来发送的数据。在服务端，我们仅输出`$_POST`数组：

```php
<?php
// php
var_dump($_POST);
```

你会很惊奇的发现，结果是下面所示：

```php
<?php
array(0) { }
```

为什么是这样的结果呢？我们的json数据 {a: ‘a’, b: ‘b’} 哪去了呢？

答案就是PHP仅仅解析Content-Type为 application/x-www-form-urlencoded 或 multipart/form-data的Http请求。之所以这样是因为历史原因，PHP最初实现`$_POST`时，最流行的就是上面两种类型。因此虽说现在有些类型（比如application/json）很流行，但PHP中还是没有去实现自动处理。

因为`$_POST`是全局变量，所以更改`$_POST`会全局有效。因此对于Content-Type为 application/json 的请求，我们需要手工去解析json数据，然后修改`$_POST`变量。

```php
<?php
// php
$_POST = json_decode(file_get_contents('php://input'), true);
```

此时，我们再去输出`$_POST`变量，则会得到我们期望的输出：

```php
<?php
array(2) { ["a"]=> string(1) "a" ["b"]=> string(1) "b" }
```

## 错误8：认为PHP支持字符数据类型

看看下面的代码，猜测下会输出什么：

```php
<?php
for ($c = 'a'; $c <= 'z'; $c++) {
    echo $c . "\n";
}
```

如果你的回答是输出’a’到’z’，那么你会惊奇的发现你的回答是错误的。

不错，上面的代码的确会输出’a’到’z’，但除此之外，还会输出’aa’到’yz’。我们来分析下为什么会是这样的结果。

在PHP中不存在char数据类型，只有string类型。明白这点，那么对’z’进行递增操作，结果则为’aa’。对于字符串比较大小，学过C的应该都知道，’aa’是小于’z’的。这也就解释了为何会有上面的输出结果。

如果我们想输出’a’到’z’，下面的实现是一种不错的办法：

```php
<?php
for ($i = ord('a'); $i <= ord('z'); $i++) {
    echo chr($i) . "\n";
}
```

或者这样也是OK的：

```php
<?php
$letters = range('a', 'z');

for ($i = 0; $i < count($letters); $i++) {
    echo $letters[$i] . "\n";
}
```

## 错误9：忽略编码标准

虽说忽略编码标准不会导致错误或是bug，但遵循一定的编码标准还是很重要的。

没有统一的编码标准会使你的项目出现很多问题。最明显的就是你的项目代码不具有一致性。更坏的地方在于，你的代码将更加难以调试、扩展和维护。这也就意味着你的团队效率会降低，包括做一些很多无意义的劳动。

对于PHP开发者来说，是比较幸运的。因为有PHP编码标准推荐（PSR），由下面5个部分组成：

* PSR-0：自动加载标准
* PSR-1：基本编码标准
* PSR-2：编码风格指南
* PSR-3：日志接口标准
* PSR-4：自动加载

PSR最初由PHP社区的几个大的团体所创建并遵循。Zend, Drupal, Symfony, Joomla及其它的平台都为此标准做过贡献并遵循这个标准。即使是PEAR，早些年也想让自己成为一个标准，但现在也加入了PSR阵营。

在某些情况下，使用什么编码标准是无关紧要的，只要你使用一种编码风格并一直坚持使用即可。但是遵循PSR标准不失为一个好办法，除非你有什么特殊的原因要 自己弄一套。现在越来越多的项目都开始使用PSR，大部分的PHP开发者也在使用PSR，因此使用PSR会让新加入你团队的成员更快的熟悉项目，写代码时 也会更加舒适。

## 错误10：错误使用empty()函数

一些PHP开发人员喜欢用`empty()`函数去对变量或表达式做布尔判断，但在某些情况下会让人很困惑。

首先我们来看看PHP中的数组Array和数组对象ArrayObject。看上去好像没什么区别，都是一样的。真的这样吗？

```php
<?php
// PHP 5.0 or later:
$array = [];
var_dump(empty($array));        // outputs bool(true)
$array = new ArrayObject();
var_dump(empty($array));        // outputs bool(false)
// why don't these both produce the same output?
```

让事情变得更复杂些，看看下面的代码：

```php
<?php
// Prior to PHP 5.0:
$array = [];
var_dump(empty($array));        // outputs bool(false)
$array = new ArrayObject();
var_dump(empty($array));        // outputs bool(false)
```

很不幸的是，上面这种方法很受欢迎。例如，在Zend Framework 2中，Zend\Db\TableGateway 在 TableGateway::select() 结果集上调用 `current()` 方法返回数据集时就是这么干的。开发人员很容易就会踩到这个坑。

为了避免这些问题，检查一个数组是否为空最后的办法是用 `count()` 函数：

```php
<?php
// Note that this work in ALL versions of PHP (both pre and post 5.0):
$array = [];
var_dump(count($array));        // outputs int(0)
$array = new ArrayObject();
var_dump(count($array));        // outputs int(0)
```

在这顺便提一下，因为PHP中会将数值0认为是布尔值false，因此 `count()` 函数可以直接用在 if 条件语句的条件判断中来判断数组是否为空。另外，`count()` 函数对于数组来说复杂度为O(1)，因此用 count() 函数是一个明智的选择。

再来看一个用 `empty()` 函数很危险的例子。当在魔术方法 `__get()` 中结合使用 `empty()` 函数时，也是很危险的。我们来定义两个类，每个类都有一个 test 属性。

首先我们定义 Regular 类，有一个 test 属性：

```php
<?php
class Regular
{
    public $test = 'value';
}
```

然后我们定义 Magic 类，并用 `__get()` 魔术方法来访问它的 test 属性：

```php
<?php
class Magic
{
    private $values = ['test' => 'value'];
    
    public function __get($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
    }
}
```

好了。我们现在来看看访问各个类的 test 属性会发生什么：

```php
<?php
$regular = new Regular();
var_dump($regular->test);    // outputs string(4) "value"
$magic = new Magic();
var_dump($magic->test);      // outputs string(4) "value"
```

到目前为止，都还是正常的，没有让我们感到迷糊。

但在 test 属性上使用 `empty()` 函数会怎么样呢？

```php
<?php
var_dump(empty($regular->test));    // outputs bool(false)
var_dump(empty($magic->test));      // outputs bool(true)
```

结果是不是很意外？

很不幸的是，如果一个类使用魔法 `__get()` 函数来访问类属性的值，没有简单的方法来检查属性值是否为空或是不存在。在类作用域外，你只能检查是否返回 null 值，但这并不一定意味着没有设置相应的键，因为键值可以被设置为 null 。

相比之下，如果我们访问 Regular 类的一个不存在的属性，则会得到一个类似下面的Notice消息：

```
    Notice: Undefined property: Regular::$nonExistantTest in /path/to/test.php on line 10
    
    Call Stack: 0.0012     234704   1.{main}() /path/to/test.php:0
```

因此，对于 `empty()` 函数，我们要小心的使用，要不然的话就会结果出乎意料，甚至潜在的误导你。

英文原文：[toptal.com][4]，译文：[phpxs.com][5]

[0]: http://9iphp.com/./web/php
[1]: http://9iphp.com/web/php/10-most-common-mistakes-php-programmers-make.html#comments
[2]: http://www.joelonsoftware.com/articles/Unicode.html
[3]: http://www.toptal.com/php/a-utf-8-primer-for-php-and-mysql
[4]: http://www.toptal.com/php/10-most-common-mistakes-php-programmers-make
[5]: http://www.phpxs.com/post/2335