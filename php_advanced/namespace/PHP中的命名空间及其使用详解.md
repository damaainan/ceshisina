# [PHP中的命名空间(namespace)及其使用详解][0]

(2014-01-02)  

php自5.3.0开始，引入了一个namespace关键字以及`__NAMESPACE__`魔术常量（当然`use`关键字或`use as`嵌套语句也同时引入）；那么什么是命名空间呢？php官网已很明确的进行了定义并形象化解释，这里直接从php官网copy一段文字[[来源][3]]。 

“什么是命名空间？从广义上来说，命名空间是一种封装事物的方法。在很多地方都可以见到这种抽象概念。例如，在操作系统中目录用来将相关文件分组，对于目录中的文件来说，它就扮演了命名空间的角色。具体举个例子，文件 foo.txt 可以同时在目录/home/greg 和 /home/other 中存在，但在同一个目录中不能存在两个 foo.txt 文件。另外，在目录 /home/greg 外访问 foo.txt 文件时，我们必须将目录名以及目录分隔符放在文件名之前得到 /home/greg/foo.txt。这个原理应用到程序设计领域就是命名空间的概念。” 

目前php5.5系列早已推出，php的面向对象编程思想也在逐渐的完善，而本文要学习的namespace关键字的引入就是为了解决php面向对象编程过程中已出现的各种“麻烦”；具体麻烦如下： 

1. 用户编写的代码与PHP内部的类/函数/常量或第三方类/函数/常量之间的名字冲突。
1. 为了缓解麻烦1，通常编写各种class时会使用较长的类名或为实现不同功能的class添加名称前缀（或后缀）。
1. 不使用魔法函数`__autoload`的情况下，而每个class又独占一个php文件时，为了调用不同的class，会在使用这些class的另外的php文件的开头位置书写较多的include（或require或require_once）语句。

### 命名空间的使用概要： 

Tips：以下示例中成为了两个文件，一个Demo.php，一个index.php，两个文件处于同级目录下；Demo.php文件中书写命名空间以及Demo类，index.php调用Demo.php中的Demo类；如下示例中的“输出结果”即表示浏览器访问index.php。 

## 一、简单的示例 

Demo.php文件代码 

```php
<?php
namespace DemoNameSpace;
 
class Demo {
    private $mysqlHandle;
 
    public function __construct() {
        echo 'This is namespace of PHP demo ,The Demo magic constant "__NAMESPACE__" is '.__NAMESPACE__;
    }
}
```

index.php文件代码 

```php
<?php
include 'Demo.php';
use DemoNameSpace\Demo;
$DemoObj = new Demo();
```

输出结果1：“This is namespace of PHP demo ,The Demo magic constant "**`__NAMESPACE__`**" is DemoNameSpace” 

以上示例的说明：Demo.php中有一个`__NAMESPACE__`魔法常量；“它包含当前命名空间名称的字符串。在全局的，不包括在任何命名空间中的代码，它包含一个空的字符串。” 

接着做示例： 

Demo.php不做变动，改动index.php文件，如下： 

```php
<?php
include 'Demo.php';
$Demo = new Demo();
```

输出结果2：“Fatal error: Class 'Demo' not found in F:\JJserver\demo\index.php on line 4” 

这个是常见的“致命错误”信息了。按照常规的php编程思路，这里的输出应该是跟“输出结果1”一致的，但这里它偏要来个致命错误，这下要抓狂了吧？~ 

行，先把抓狂的麻烦解决，去掉（或注释掉）Demo.php文件中的：“namespace DemoNameSpace；”这个语句，就正常了。这是咱们平常写class以及调用class最常见的书写方法，就不再解释这种不使用namespace的情况了。 

对比使用namespace与不使用namespace的两种输出情况，并加入namespace的定义理解后，上述出现致命错误的情况就很好理解了。在Demo.php中定义了一个namespace，也就是命名空间后，接着定义了Demo类，然后这个Demo类就被归并到了DemoNameSpace命名空间中去了，那么要调用这个Demo类时，自然要先调用这个DemoNameSpace命名空间了，也就是在index.php文件中使用“useDemoNameSpace\Demo”语句了。 

## 二、复杂一点的示例 

Demo.php文件代码 

```php
<?php
namespace DemoNameSpace;
 
class Demo {
    private $mysqlHandle;
 
    public function __construct() {
        echo 'This is namespace of PHP demo ,The Demo magic constant "__NAMESPACE__" is '.__NAMESPACE__;
    }
}
 
namespace DemoNameSpace1;
 
const constDefine = 'JJonline1';
 
class Demo {
    private $mysql;
    const constDefine = 'JJonline2';
 
    public function __construct() {
        echo 'The const constant outside class is: '.constDefine;
        echo '===cut-off rule of god!!!!===';
        echo 'The const constant inside class is: '.self::constDefine;
    }
}
```

index.php文件代码 

```php
<?php
    include 'Demo.php';
    use DemoNameSpace1\Demo as Test;
    $Demo = new Test();
    echo '||||'.DemoNameSpace1\constDefine;
?>
```

输出结果3：“The const constant outside class is: JJonline1===cut-off rule of god!!!!===The const constant inside class is: JJonline2||||JJonline1” 

这个结果在没有命名空间的时候，就直接报诸如“Fatal error: Cannot redeclare class Demo”的致命错误了。但运行没有报错，这也就是php5.3以后引入的命名空间的好处了，就诸如本文开头引用的官方解释中以不同目录下的相同文件名的文件可以存在一样是一个道理了。Demo.php文件中，定义的第一个名称叫做Demo的class类被归并到了DemoNameSpace的命名空间，而定义的第二个名称叫做Demo的class被归并到了DemoNameSpace1的命名空间，故而并不会出现不能重复定义某一个类的致命错误。以上的书写方法是要尽量避免的，因为类外部const常量名与类内部const常量名是一样的，很容易混淆，这里这样书写的目的就是看看不同位置申明的const常量，在调用时的情况；输出结果3已经很明显了，就不再多墨迹解释了。 

Demo.php中DemoNameSpace1命名空间下还将const常量constDefine提出，拿到了定义class之外，这又要抓狂了，因为之前的知识是define定义全局常量，const定义class内部常量；这儿却将const拿出来玩了...具体就不再讲解了，Demo.php文件代码以及运行后的结果已经很明确的表明了相关知识。class内部定义的const只能在class的内部调用，采用self::constName形式，而class内部调用命名空间下、class外的const常量，则可以直接使用诸如define定义的常量一样使用。当需要使用该命名空间下、class外定义的const常量时，就使用类似路径形式的方式调用（index.php文件中的输出）。 

该例子还有一点说明，就是在index.php中使用了use as语句，看index.php的代码，意义一目了然，new的一个class名称叫Test，但Test这个类并没有在Demo.php中定义，却没有出错，这就在于了use as语句了，具体意义不再解释。 

通过上述的了解，namespace关键字可以将实现各种功能的class通过指定不同的命名空间分门别类存放，而且不同命名空间下的class可以同名；另外const常量定义也可以提出到class外部，当然也会有作用范围这么一个“内涵”~ 

### 总结下namespace的相关知识： 

1、当前脚本文件的第一个命名空间前面不能有任何代码，例如如下代码就是会报致命错误的： 

```php
<?php
define("GREETING","Hello world!");
 
namespace DemoNameSpace;
 
class Demo {
    private $mysqlHandle;
 
    public function __construct() {
        echo 'This is namespace of PHP demo ,The Demo magic constant "__NAMESPACE__" is '.__NAMESPACE__;
    }
}
$Demo = new Demo();
```

运行上述代码，会出现致命错误：“Fatal error: Namespace declaration statement has to be the very first statement in xxxx” 

2、命名空间下直接new该命名空间中的class名称，可以省略掉use语法，这是php按脚本书写顺序执行导致的。例如如下代码是可以运行的 

    
```php
<?php
namespace DemoTest;
class Demo {
    public function __construct() {
        echo 'this is a test script';
    }
}
namespace DemoNameSpace;
 
class Demo {
    private $mysqlHandle;
 
    public function __construct() {
        echo 'This is namespace of PHP demo ,The Demo magic constant "__NAMESPACE__" is '.__NAMESPACE__;
    }
}
$Demo = new Demo();
```

运行结果4：“This is namespace of PHP demo ,The Demo magic constant "`__NAMESPACE__`" is DemoNameSpace” 

这个结果表明，同一脚本下new一个没有指定use哪个命名空间时，会顺着该脚本，使用最靠近new语句之前的一个命名空间中的class 

3、公共空间：可以简单的理解，没有定义命名空间的方法（函数）、类库（class）、属性（变量）都默认归属于公共空间。这样就解释了为php5.3.0以前版本书写的代码大部分为何在php5.3.0及其以上版本还能正常运行的原因。另外：公共空间中的代码段被引入到某个命名空间下后，该公共空间中的代码段不属于任何命名空间！ 

公共空间这块，还是各位自己写写代码比较好。 

4、就如目录结构一样，命名空间也有子命名空间的概念，具体就不再举例说明了。 

命名空间的引入，让php面向对象编程更加的贴切，合理利用命名空间，也可以让项目文件规划，各个类库规划更加的合理、易读。更多关于php5.3.0及其以上引入的命名空间的问题就不再介绍了。

[0]: http://blog.jjonline.cn/phptech/154.html
[1]: http://blog.jjonline.cn/author/1
[2]: http://blog.jjonline.cn/sort/phptech
[3]: http://www.php.net/manual/zh/language.namespaces.rationale.php