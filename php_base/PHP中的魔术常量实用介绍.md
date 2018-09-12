## PHP中的魔术常量实用介绍
<font face=微软雅黑>

这一节来分析一下PHP中的魔术常量。

![timg (1).jpg][0]

PHP 向它运行的任何脚本提供了大量的预定义常量。不过很多常量都是由不同的扩展库定义的，只有在加载了这些扩展库时才会出现，或者动态加载后，或者在编译时已经包括进去了。

有八个魔术常量它们的值随着它们在代码中的位置改变而改变。例如 `__LINE__` 的值就依赖于它在脚本中所处的行来决定。这些特殊的常量不区分大小写，如下：

#### `__LINE__` 
文件中的当前行号。

#### `__FILE__` 
文件的完整路径和文件名。如果用在被包含文件中，则返回被包含的文件名。自 PHP 4.0.2 起，`__FILE__` 总是包含一个绝对路径（如果是符号连接，则是解析后的绝对路径），而在此之前的版本有时会包含一个相对路径。

#### `__DIR__` 
文件所在的目录。如果用在被包括文件中，则返回被包括的文件所在的目录。它等价于 `dirname(__FILE__)`。除非是根目录，否则目录中名不包括末尾的斜杠。（PHP 5.3.0中新增

#### `__FUNCTION__` 
函数名称（PHP 4.3.0 新加）。自 PHP 5 起本常量返回该函数被定义时的名字（区分大小写）。在 PHP 4 中该值总是小写字母的。

#### `__CLASS__` 
类的名称（PHP 4.3.0 新加）。自 PHP 5 起本常量返回该类被定义时的名字（区分大小写）。在 PHP 4 中该值总是小写字母的。类名包括其被声明的作用区域（例如 `Foo\Bar`）。注意自 PHP 5.4 起 `__CLASS__` 对 `trait` 也起作用。当用在 trait 方法中时，`__CLASS__` 是调用 `trait` 方法的类的名字。

#### `__TRAIT__` 
Trait 的名字（PHP 5.4.0 新加）。自 PHP 5.4 起此常量返回 `trait` 被定义时的名字（区分大小写）。`Trait` 名包括其被声明的作用区域（例如 `Foo\Bar`）。

#### `__METHOD__` 
类的方法名（PHP 5.0.0 新加）。返回该方法被定义时的名字（区分大小写）。

#### `__NAMESPACE__` 
当前命名空间的名称（区分大小写）。此常量是在编译时定义的（PHP 5.3.0 新增）。

## 范例

下面让我们以实例的形式向大家讲解下这几个魔术常量是如何使用的。

#### 1、`__FILE__` 和 `__LINE__` 魔方常量

说明：`__FILE__` 返回当前文件名和所在地址; `__LINE__` 返回当前代码所在文件的行数

作用：这两个常量经常在记录日志的时候会用到，比如：错误日志中要记录哪个文件在什么位置出现了什么问题。

```php
<?php
class Log
{
    private $logFile = null;

    public function __construct( $filePath = '' )
    {
        $this->logFile = $filePath;
    }

    public function writeLog( $message )
    {
        // 把记录写入日志文件
        echo $message;
    }

    public function error()
    {
        $error = "在文件（" . __FILE__ . "） 中的第  " . __LINE__ . " 行出错了";
        $this->writeLog( $error );
    }
}

$log = new Log('./log.txt');
$log->error(); //在文件（E:\www.demo.com\index.php） 中的第 19 行出错了
```

#### 2、`__DIR__` 魔方常量

说明：当前文件所在目录 **（不包最后的 "/" ）**

作用：这个在框架中会经常用到。比如：从入口文件中记录当前项目的路径，然后经这个路径引用其它文件

```php
<?php
define('FILE_PATH', __DIR__);

function __autoload( $className = '' )
{
    // 自动加载文件
    // include FILE_PATH . "/xxx/" . $className . ".class.php";
}
```

`__autoload`在《[PHP中的十六个魔术方法详解][1]》可以查看它的用法。

#### 3、`__FUNCTION__` 和 `__METHOD__` 魔术常量

说明：返回当前函数（方法）的名称

作用：这个在记录日志的时候，也会经常用到，比如：错误日志中记录这个错误所所发生的位置，返回值会包含命名空间名

```php
<?php
namespace Think;

function logWriteToFilea()
{
    echo __FUNCTION__ , "<br><br>";
}

function logWriteToFileb()
{
    echo __METHOD__ , "<br><br>";
}

class Log
{
    private $logFile = null;

    public function error1()
    {
        echo __METHOD__ , "<br><br>";
    }

    public function error2()
    {
        echo __FUNCTION__ , "<br><br>";
    }
}

logWriteToFilea(); // Think\logWriteToFilea
logWriteToFileb(); // Think\logWriteToFileb

$log = new Log();
$log->error1(); // Think\Log::error1
$log->error2(); // error2
```

特别说明一点：如果将 `__METHOD__` 放到函数里时 和 `__FUNCTION__` 返回的内容一样。但是将 `__FUNCTION__` 放到类的方法里时，返回的值 和 `__METHOD__` 返回的是有区别的。看上面的实例返回内容

#### 4、`__CLASS__` 魔术常量

说明：返回当前的类名（包含命名空间）

```php
<?php
namespace Think;

class Log
{
    public function getClassName()
    {
        echo __CLASS__ , "<br><Br>";
    }
}

$log = new Log();
$log->getClassName(); // Think\Log
```

#### 5、`__NAMESPACE__` 魔术常量

说明：返回当前的命名空间名称

```php
    <?php
    namespace Think\Log\File;
    echo __NAMESPACE__; // Think\Log\File
```

#### 6、`__TRAIT__` 魔术常量

说明：返回`Trait` 名，包含命令空间

```php
<?php
namespace Think;

trait ezcReflectionReturnInfo 
{
    public function demo()
    {
        echo __TRAIT__;
    }
}

class ezcReflectionMethod
{
    use ezcReflectionReturnInfo;
}

$demo = new ezcReflectionMethod();
$demo->demo(); // Think\ezcReflectionReturnInfo
```

特别说明一点：`Trait` 是为类似 PHP 的单继承语言而准备的一种代码复用机制。`Trait` 为了减少单继承语言的限制，使开发人员能够自由地在不同层次结构内独立的类中复用 method。`Trait` 和 `Class` 组合的语义定义了一种减少复杂性的方式，避免传统多继承和 Mixin 类相关典型问题。

`Trait` 和 `Class` 相似，但仅仅旨在用细粒度和一致的方式来组合功能。 无法通过 `trait` 自身来实例化。它为传统继承增加了水平特性的组合；也就是说，应用的几个 `Class` 之间不需要继承。

</font>

[0]: ./img/1482114441227665.jpg
[1]: http://www.yduba.com/Index/shows/arid/52.html