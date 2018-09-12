摘要：  
有几个疑问：

1. 自动加载的概念
1. 自动加载的演进

## 自动加载的概念

当在 php 代码的某一行使用一个类的时候，但该类处于一种“不存在”的状态的时候（类没有被加载或者已经被注销了），就会调用某个函数，在该函数中可以去加载那个类，以实现类的自动加载。

换个说法：在某个php执行上下文中，在new某个类，或者静态调用时如果某个类没有找到，php默认会首先触发`__autoload`回调，由回调尝试去加载类代码文件。这个回调由用户自己实现，通过用户规定的类名到代码文件的映射规则得到代码文件路径，并使用require/include函数去加载代码文件。

> 类的自动加载机制也通常被叫做autoload机制。autoload机制可以使得PHP程序有可能在使用类时才自动包含类文件，而不是一开始就将所有的类文件include进来，这种机制也称为lazy loading（延迟加载机制）。

## 自动加载的演进

* 没有使用自动加载的时候，单独使用include/require来解决。
* 最开始的时候，使用常规自动加载函数 `__autoload()`实现自动加载的（后来出了 `spl_autoload`后被取代）
    * `__autoload`只允许注册一个回调（一次只允许一条规则去注册类或者说只可以定义一次），不够灵活，例如，因为我们在使用一些第三方类库的时候，经常需要维护各自的autoload调用规则。
* 到了php5后，spl_autoload取代了 `__autoload()`。

### 1、没有使用自动加载的时候

不断的 require 或者 include 需要的 class 文件，在小型项目中是无所谓的，但是在大中型项目里，就会很繁琐，也容易出错，因为太多这种 require 或者 include了。
    
```php
<?php
require_once('lib/mylib/myclass.php');

require_once('lib/mylib/myclass2.php');

require_once('lib/anotherlib/someclass.php');

$myclass = new MyClass;

$myclass2 = new MyClass2;

$someclass = new SomeClass;
```

### 2、使用__autoload()实现自动加载的时候
    
```php
<?php
function __autoload($class_name) {
    // __autoload只是在这里操作第三步
　　require_once ($class_name . "class.php");
}

$memo= new Demo();
```
* 第一步：是根据类名确定类文件名。
* 第二步：是确定类文件所在的磁盘路径。（在我们的例子是最简单的情况，类与调用它们的PHP程序文件在同一个文件夹下，所以直接使用当前目录路径）
* 第三步：是将类从磁盘文件中加载到系统中。（使用include/require）

> 要实现第一步，第二步的功能，必须在开发时约定类名与磁盘文件的映射方法，只有这样我们才能实现第三步，根据类名找到它对应的磁盘文件。(最重要的部分就是确定他们的路径和类名对应)

假如在一个系统的实现中，假如需要使用很多其它的类库，这些类库可能是由不同的开发工程师开发，其类名与实际的磁盘文件的映射规则不尽相同。这时假如要实现类库文件的自动加载，就必须在`__autoload()`函数中将所有的映射规则全部实现，因此`__autoload()`函数有可能会非常复杂，甚至无法实现。最后可能会导致`__autoload()`函数十分臃肿，这时即便能够实现，也会给将来的维护和系统效率带来很大的负面影响。

例子1：
    
```php
<?php
function __autoload($class_name) {

    $path = str_replace("_", "/", $class_name);

    require_once $path.".php";

}

$memo= new Demo();
```
应该注意到，这里的路径其实是有一定局限性的，例如不能太复杂，太多不同层次的目录路径，因为最终只有一个 require 加载。

例子2：
    
```php
<?php
function __autoload($className) {

    $extensions = array(".php", ".class.php", ".inc");

    $paths = explode(PATH_SEPARATOR, get_include_path());

    $className = str_replace("_" , DIRECTORY_SEPARATOR, $className);

    foreach ($paths as $path) {

        $filename = $path . DIRECTORY_SEPARATOR . $className;

        foreach ($extensions as $ext) {

            if (is_readable($filename . $ext)) {

                require_once $filename . $ext;

                break;

           }

       }

    }

}
```
这里就是比较复杂的使用不同的类型的文件后缀进行加载判断。（同理可以类比各种库的不同目录架构）

### 3、使用spl_autoload实现自动加载的时候

简单来说，spl_autoload的执行过程是：

1. 调用一个新的函数（类），如果该函数没被定义的话。
1. 使用`spl_autoload_register`将需要被注册的函数放到SPL的`__autoload`函数栈里面
1. `spl_autoload_call`会自动加载SPL的`__autoload`函数栈的函数

这样，根据每个类库不同的命名机制实现各自的自动加载函数，然后使用`spl_autoload_register`分别将其注册到SPL自动加载函数队列中就可了。这样我们就不用维护一个非常复杂的`__autoload`函数了。

> 一般是只有上面三步过程，也可以在其中穿插其他SPL Autoload函数

例子1：
    
```php
<?php
function autoloadModel($className) {

      // 不同的目录不同的处理

    $filename = "models/" . $className . ".php";

    if (is_readable($filename)) {

        require $filename;

    }

}

function autoloadController($className) {

    $filename = "controllers/" . $className . ".php";

    if (is_readable($filename)) {

        require $filename;

    }

}

// 可以写多个加载函数

spl_autoload_register("autoloadModel");

spl_autoload_register("autoloadController");
```
SPL Autoload具体有几个函数：

* `spl_autoload_register`：此函数的功能就是把函数注册至SPL的`__autoload`函数栈中，并移除系统默认的`__autoload()`函数。
    * 第一个参数`autoload_function`： 欲注册的自动装载函数。如果没有提供任何参数，则自动注册 autoload 的默认实现函数`spl_autoload()`。
    * 第二个参数`throw`：此参数设置了 autoload_function 无法成功注册时， `spl_autoload_register()`是否抛出异常。
    * 第三个参数`prepend`：如果是 true，`spl_autoload_register()`会添加函数到队列之首，而不是队列尾部。
* `spl_autoload_unregister`：注销已注册至SPL的`__autoload`函数栈的函数。
* `spl_autoload_functions`：返回所有已注册至SPL的`__autoload`函数栈的函数。
* `spl_autoload_call`：尝试调用所有已注册至SPL的`__autoload`函数栈的函数来加载请求类。
* `spl_autoload `：就是`__autoload()`的默认实现。
* `spl_autoload_extionsions`： 注册并返回至SPL的`__autoload`函数栈的函数使用的默认文件扩展名。

## autoload效率问题及对策

使用autoload机制时，很多人的第一反应就是使用autoload会降低系统效率，甚至有人干脆提议为了效率不要使用autoload。在我们了解了autoload实现的原理后，我们知道autoload机制本身并不是影响系统效率的原因，甚至它还有可能提高系统效率，因为它不会将不需要的类加载到系统中。

那么为什么很多人都有一个使用autoload会降低系统效率的印象呢？**实际上，影响autoload机制效率本身恰恰是用户设计的自动加载函数。如果它不能高效的将类名与实际的磁盘文件(注意，这里指实际的磁盘文件，而不仅仅是文件名)对应起来，系统将不得不做大量的文件是否存在(需要在每个include path中包含的路径中去寻找)的判断，而判断文件是否存在需要做磁盘I/O操作，众所周知磁盘I/O操作的效率很低，因此这才是使得autoload机制效率降低的罪魁祸首!**

> 例如一个支持标准 PSR4规范的 php 代码规范就是一个能够很好提高 autoload 效率的方式！

因此，我们在系统设计时，需要定义一套清晰的将类名与实际磁盘文件映射的机制。这个规则越简单越明确，autoload机制的效率就越高。autoload机制并不是天然的效率低下，只有滥用autoload，设计不好的自动装载函数才会导致其效率的降低。

参考文档：

* [PHP自动加载功能原理解析 | LEOYANG’S BLOG][0]
* [PHP的类自动加载机制 - guisu，程序人生。 逆水行舟，不进则退。 - CSDN博客][1]
* [https://www.sitepoint.com/autoloading-and-the-psr-0-standard/][2]
* [PHP的autoload机制的实现解析_php技巧_脚本之家][3]

* **本文作者：** 茅有知
* **本文链接：**[https://www.godblessyuan.com/backend/php_autoload.html][4]

[0]: http://leoyang90.cn/2017/03/11/PHP-Composer-autoload/
[1]: http://blog.csdn.net/hguisu/article/details/7463333
[2]: https://www.sitepoint.com/autoloading-and-the-psr-0-standard/
[3]: http://www.jb51.net/article/31279.htm
[4]: https://www.godblessyuan.com/backend/php_autoload.html