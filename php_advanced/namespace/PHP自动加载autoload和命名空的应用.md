# PHP自动加载autoload和命名空的应用

 时间 2017-11-30 20:27:03 

原文[https://www.helloweba.com/view-blog-459.html][1]


## PHP自动加载autoload和命名空的应用

 helloweba.com  作者：月光光 时间： 2017年11月30日 20:27  标签：php

PHP的自动加载就是我们加载实例化类的时候，不需要手动去写require来导入这个class.php文件，程序自动帮我们加载导入进来。配合命名空间规范，我们可以在复杂系统中很轻松的处理不同类的加载和调用问题。

#### 1. 自动加载的原理以及__autoload的使用

自动加载的原理，就是在我们实例化一个 class 的时候，PHP如果找不到这个类，就会去自动调用本文件中的 __autoload($class_name) 方法，我们new的这个class_name 就成为这个方法的参数。所以我们就可以在这个方法中根据我们需要new class_name的各种判断和划分就去require对应的路径类文件，从而实现自动加载。 

我们先来看下 `__autoload()` 的自动调用，举个栗子： 

index.php

    <?php 
    $db = new Db();

如果我们不手动导入Db类，程序可能会报错，说找不到这个类：

    Fatal error: Uncaught Error: Class 'DB' not found in D:\web\helloweba\demo\2017\autoload\index.php:2 Stack trace: #0 {main} thrown in D:\web\helloweba\demo\2017\autoload\index.php on line 2

那么，我们现在加入 `__autoload()` 这个方法再看看： 

    $db = new DB();
    function __autoload($className) {
        echo $className;
        exit();
    }

根据上面自动加载机制的描述，会输出：Db， 也就是我们需要new 的类的类名。所以，这个时候我们就可以在 __autoload() 方法里，根据需要去加载类库文件了。 

#### 2. spl_autoload_register自动加载

如果是小项目，用 `__autoload()` 就能实现基本的自动加载了。但是如果一个项目很大，或者需要不同的自动加载来加载不同路径的文件，这个时候`__autoload`就杯具了，因为一个项目中只允许有一个 `__autoload()` 函数，因为 PHP 不允许函数重名了，也就是说你不能声明2个 `__autoload()` 函数文件，否则会报致命错误。那怎么办呢？放心，你想到的，PHP大神早已经想到。 所以 `spl_autoload_register()` 这样又一个牛逼函数诞生了，并且取而代之它。它执行效率更高，更灵活。 

先看下它如何使用，在index.php中加入以下代码。

    <?php 
    spl_autoload_register(function($className){
        if (is_file('./Lib/' . $className . '.php')) {
            require './Lib/' . $className . '.php';
        }
    });
    
    $db = new Db();
    $db::test();

在`Lib\Db.php`文件中加入以下代码：

    <?php 
    class Db
    {
        public static function test()
        {
            echo 'Test';
        }
    }

运行index.php后，当调用 `new Db()` 时， `spl_autoload_register` 会自动去lib/目录下查找对应的Db.php文件，成功后并且能够执行 $db::test(); 。同样如果在Lib\目录下有多个php类文件，都可以在index.php中直接调用，而不需要使用 require 多个文件。 

也就是说， `spl_autoload_register` 是可以多次重复使用的，这一点正是解决了 `__autoload` 的短板，那么如果一个页面有多个 `spl_autoload_register` ，执行顺序是按照注册的顺序，一个一个往下找，如果找到了就停止。 

#### 3. spl_autoload_register自动加载和namespace命名空间

对于非常复杂的系统，其目录结构也会非常复杂，规范的命名空间解决了复杂路径下大量文件、函数、类重名的问题。而自动加载现在是PHP现代框架的基石，基本都是 `spl_autoload_register` 来实现自动加载。所以**`spl_autoload_register + namespace`** 就成为了一个主流。 

根据PSR系列规范，namespace命名已经非常规范化，所以根据namespace就能找到详细的路径，从而找到类文件。

我们用最简单的例子来说明复杂系统如何自动加载类文件。

首先，我们准备系统目录结构：

    ----/Lib            // 类目录
        --Db.php
        --Say.php
    ----autoload.php    // 自动加载函数
    ----index.php       // 首页

以上是一个基本的系统目录，我们要实现的是，使用命名空间和自动加载，直接在首页index.php调用Lib目录下的多个类。

我们准备两个列文件：

Db.php

    <?php 
    namespace Lib;
    
    class Db
    {
        public function __construct()
        {
            //echo 'Hello Db';
        }
    
        public static function test()
        {
            echo 'Test';
        }
    }

Say.php

    <?php
    namespace Lib;
    
    class Say 
    {
        public function __construct()
        {
            //echo 'Hello';
        }
    
        public function hello()
        {
            echo 'say hello';
        }
    }

以上两个普通的类文件，添加了命名空间： namespace Lib; 表示该类文件属于Lib\目录名称下的，当然你可以随便取个不一样的名字来表示你的项目名称。 

现在我们来看autoload.php：

    <?php 
    spl_autoload_register(function ($class) {
    
        $prefix = 'Lib\\';
    
        $base_dir = __DIR__ . '/Lib/';
    
        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            // no, move to the next registered autoloader
            return;
        }
    
        $relative_class = substr($class, $len);
    
        // 兼容Linux文件找。Windows 下（/ 和 \）是通用的
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
        if (file_exists($file)) {
            require $file;
        }
    });

以上代码使用函数 `spl_autoload_register()` 首先判断是否使用了命名空间，然后验证要调用的类文件是否存在，如果存在就 require 类文件。 

好了，现在我们在首页index.php这样调用:

    <?php 
    
    use Lib\Db;
    use Lib\Say;
    
    require './autoload.php';
    
    $db = new Db();
    $db::test();
    
    $say = new Say;
    $say->hello();

我们只需使用一个require将autoload.php加载进来，使用 `use` 关键字将类文件路径变成绝对路径了，当然你也可以在调用类的时候把路径都写上，如： new Lib\Db(); ，但是涉及到多个类互相调用的时候就会很棘手，所以我们还是在文件开头就使用 `use` 把路径处理好。 

接下来就直接调用Lib/目录下的各种类文件了，你可以在Lib/目录下放置多个类文件尝试下。

运行index.php看看是不是如您所愿。

#### 结束语

该文简单介绍了自动加载以及命名空间的使用，实际开发中，我们很少去关注autoload自动加载的问题，因为大多数现代PHP框架都已经处理好了文件自动加载的问题。开发者只需关注业务代码，使用规范的命名空间就可以了。当然，如果你想自己开发个项目不依赖大型框架亦或者自己开发php框架，那你就得熟悉下autoload自动加载这个好东西了，毕竟它可以让我们“偷懒”，省事多了。

现代php里，我们经常使用 Composer 方式安装的组件，都可以通过autoload实现自动加载，所以还是一个“懒”字给我们带来了极好的开发效率。 

#### 参考文献：

[PHP PSR-4 Autoloader自动加载][3]

[类的自动加载：http://php.net/manual/zh/language.oop5.autoload.php][4]

 声明： 本文为原创文章，helloweba.com和作者拥有版权，如需转载，请注明来源于helloweba.com并保留原文链接：https://www.helloweba.com/view-blog-459.html

[1]: https://www.helloweba.com/view-blog-459.html
[3]: https://www.helloweba.com/view-blog-402.html
[4]: http://php.net/manual/zh/language.oop5.autoload.php