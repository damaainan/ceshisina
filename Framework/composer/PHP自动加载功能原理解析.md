## PHP自动加载功能原理解析

来源：[https://segmentfault.com/a/1190000009368742](https://segmentfault.com/a/1190000009368742)


## 前言

-----

在开始之前，欢迎关注我自己的博客：[www.leoyang90.cn][0]

这篇文章是对PHP自动加载功能的一个总结，内容涉及PHP的自动加载功能、PHP的命名空间、PHP的PSR0与PSR4标准等内容。
## 一、PHP自动加载功能

-----

## PHP自动加载功能的由来

在PHP开发过程中，如果希望从外部引入一个 class，通常会使用 include 和 require 方法，去把定义这个 class 的文件包含进来。这个在小规模开发的时候，没什么大问题。但在大型的开发项目中，使用这种方式会带来一些隐含的问题：如果一个 PHP 文件需要使用很多其它类，那么就需要很多的 require/include 语句，这样有可能会造成遗漏或者包含进不必要的类文件。如果大量的文件都需要使用其它的类，那么要保证每个文件都包含正确的类文件肯定是一个噩梦， 况且 require_once 的代价很大。

PHP5 为这个问题提供了一个解决方案，这就是类的自动装载 (autoload) 机制。autoload 机制可以使得 PHP 程序有可能在使用类时才自动包含类文件，而不是一开始就将所有的类文件 include 进来，这种机制也称为 lazy loading。

总结起来，自动加载功能带来了几处优点：

* 使用类之前无需 include 或者 require

* 使用类的时候才会 require/include 文件，实现了 lazy loading，避免了 require/include 多余文件。

* 无需考虑引入类的实际磁盘地址，实现了逻辑和实体文件的分离。


  
如果想具体详细的了解关于自动加载的功能，可以查看资料：
[PHP的类自动加载机制][1]
[PHP的autoload机制的实现解析][2]
## PHP自动加载函数__autoload()

通常 PHP5 在使用一个类时，如果发现这个类没有加载，就会自动运行 `__autoload()` 函数，这个函数是我们在程序中自定义的，在这个函数中我们可以加载需要使用的类。下面是个简单的示例：

```php
function __autoload($classname) {
    require_once ($classname . "class.php"); 
}
```

在我们这个简单的例子中，我们直接将类名加上扩展名 ”.class.php” 构成了类文件名，然后使用 require_once 将其加载。从这个例子中，我们可以看出 autoload 至少要做三件事情：

* 根据类名确定类文件名；

* 确定类文件所在的磁盘路径(在我们的例子是最简单的情况，类与调用它们的PHP程序文件在同一个文件夹下)；

* 将类从磁盘文件中加载到系统中。


 
第三步最简单，只需要使用 include/require 即可。要实现第一步，第二步的功能，必须在开发时约定类名与磁盘文件的映射方法，只有这样我们才能根据类名找到它对应的磁盘文件。

当有大量的类文件要包含的时候，我们只要确定相应的规则，然后在 __autoload() 函数中，将类名与实际的磁盘文件对应起来，就可以实现 lazy loading 的效果。从这里我们也可以看出 _autoload() 函数的实现中最重要的是类名与实际的磁盘文件映射规则的实现。
## __autoload() 函数存在的问题

如果在一个系统的实现中，如果需要使用很多其它的类库，这些类库可能是由不同的开发人员编写的，其类名与实际的磁盘文件的映射规则不尽相同。这时如果要实现类库文件的自动加载，就必须在 __autoload() 函数中将所有的映射规则全部实现，这样的话 autoload() 函数有可能会非常复杂，甚至无法实现。最后可能会导致 autoload() 函数十分臃肿，这时即便能够实现，也会给将来的维护和系统效率带来很大的负面影响。

那么问题出现在哪里呢？问题出现在 _autoload() 是全局函数只能定义一次，不够灵活，所以所有的类名与文件名对应的逻辑规则都要在一个函数里面实现，造成这个函数的臃肿。那么如何来解决这个问题呢？答案就是使用一个 _autoload 调用堆栈，不同的映射关系写到不同的 _autoload 函数中去，然后统一注册统一管理，这个就是 PHP5 引入的 SPL Autoload。
## SPL Autoload

SPL是 Standard PHP Library (标准PHP库)的缩写。它是 PHP5 引入的一个扩展库，其主要功能包括 autoload 机制的实现及包括各种 Iterator 接口或类。SPL Autoload 具体有几个函数：

* spl_autoload_register：注册__autoload()函数

* spl_autoload_unregister：注销已注册的函数

* spl_autoload_functions：返回所有已注册的函数

* spl_autoload_call：尝试所有已注册的函数来加载类

* spl_autoload ：__autoload() 的默认实现

* spl_autoload_extionsions： 注册并返回 spl_autoload 函数使用的默认文件扩展名。


  
这几个函数具体详细用法可见 [php中spl_autoload详解][3]

简单来说，spl_autoload 就是 SPL 自己的定义 __autoload() 函数，功能很简单，就是去注册的目录(由 set_include_path 设置)找与 $classname 同名的 .php/.inc 文件。当然，你也可以指定特定类型的文件，方法是注册扩展名 (spl_autoload_extionsions)。

而 spl_autoload_register() 就是我们上面所说的 autoload 调用堆栈，我们可以向这个函数注册多个我们自己的 `_autoload()` 函数，当 PHP 找不到类名时，PHP 就会调用这个堆栈，一个一个去调用自定义的 `_autoload()` 函数，实现自动加载功能。如果我们不向这个函数输入任何参数，那么就会注册 `spl_autoload()` 函数。

好啦，PHP 自动加载的底层就是这些，注册机制已经非常灵活，但是还缺什么呢？我们上面说过，自动加载关键就是类名和文件的映射，这种映射关系不同框架有不同方法，非常灵活，但是过于灵活就会显得杂乱，PHP 有专门对这种映射关系的规范，那就是 PSR 标准中 PSR0 与 PSR4。

不过在谈 PSR0 与 PSR4 之前，我们还需要了解 PHP 的命名空间的问题，因为这两个标准其实针对的都不是类名与目录文件的映射，而是命名空间与文件的映射。为什么会这样呢？在我的理解中，规范的面向对象 PHP 思想，命名空间在一定程度上算是类名的别名，那么为什么要推出命名空间，命名空间的优点是什么呢
## 二、Namespace命名空间

-----

要了解命名空间，首先先看看 [官方文档][4] 中对命名空间的介绍：

什么是命名空间？从广义上来说，命名空间是一种封装事物的方法。在很多地方都可以见到这种抽象概念。例如，在操作系统中目录用来将相关文件分组，对于目录中的文件来说，它就扮演了命名空间的角色。具体举个例子，文件 foo.txt 可以同时在目录 /home/greg 和 /home/other 中存在，但在同一个目录中不能存在两个 foo.txt 文件。另外，在目录 /home/greg 外访问 foo.txt 文件时，我们必须将目录名以及目录分隔符放在文件名之前得到 /home/greg/foo.txt。这个原理应用到程序设计领域就是命名空间的概念。
在PHP中，命名空间用来解决在编写类库或应用程序时创建可重用的代码如类或函数时碰到的两类问题：
1 用户编写的代码与PHP内部的类/函数/常量或第三方类/函数/常量之间的名字冲突
2 为很长的标识符名称(通常是为了缓解第一类问题而定义的)创建一个或简短）的名称，提高源代码的可读性。
PHP 命名空间提供了一种将相关的类、函数和常量组合到一起的途径。

  
简单来说就是 PHP 是不允许程序中存在两个名字一样一样的类或者函数或者变量名的，那么有人就很疑惑了，那就不起一样名字不就可以了？事实上很多大程序依赖很多第三方库，名字冲突什么的不要太常见，这个就是官网中的第一个问题。那么如何解决这个问题呢？在没有命名空间的时候，可怜的程序员只能给类名起 a_b_c_d_e_f 这样的，其中 a/b/c/d/e/f 一般有其特定意义，这样一般就不会发生冲突了，但是这样长的类名编写起来累，读起来更是难受。因此 PHP5 就推出了命名空间，类名是类名，命名空间是命名空间，程序写 / 看的时候直接用类名，运行起来机器看的是命名空间，这样就解决了问题。

另外，命名空间提供了一种将相关的类、函数和常量组合到一起的途径。这也是面向对象语言命名空间的很大用途，把特定用途所需要的类、变量、函数写到一个命名空间中，进行封装。

解决了类名的问题，我们终于可以回到 PSR 标准来了，那么 PSR0 与 PSR4 是怎么规范文件与命名空间的映射关系的呢？答案就是：对命名空间的命名（额，有点绕）、类文件目录的位置和两者映射关系做出了限制，这个就是标准的核心了。更完整的描述可见 [现代 PHP 新特性系列（一） —— 命名空间][5]
## 三、PSR标准

-----

在说 PSR0 与 PSR4 之前先介绍一下 PSR 标准。PSR 标准的发明者和规范者是：PHP-FIG，它的网站是：[www.php-fig.org][6]。就是这个联盟组织发明和创造了 PSR 规范。FIG 是 Framework Interoperability Group（框架可互用性小组）的缩写，由几位开源框架的开发者成立于 2009 年，从那开始也选取了很多其他成员进来，虽然不是 “官方” 组织，但也代表了社区中不小的一块。组织的目的在于：以最低程度的限制，来统一各个项目的编码规范，避免各家自行发展的风格阻碍了程序设计师开发的困扰，于是大伙发明和总结了 PSR，PSR 是 Proposing a Standards Recommendation（提出标准建议）的缩写。

具体详细的规范标准可以查看 
[PHP中PSR规范][7]
## PSR0 标准

PRS-0规范是他们出的第1套规范，主要是制定了一些自动加载标准（Autoloading Standard）PSR-0 强制性要求几点：

1、  一个完全合格的 namespace 和 class 必须符合这样的结构：“< Vendor Name>(< Namespace>)*< Class Name>”
2、每个 namespace 必须有一个顶层的 namespace（"Vendor Name" 提供者名字）
3、每个 namespace 可以有多个子 namespace
4、当从文件系统中加载时，每个 namespace 的分隔符(/)要转换成  DIRECTORY_SEPARATOR (操作系统路径分隔符)
5、在类名中，每个下划线(_)符号要转换成 DIRECTORY_SEPARATOR (操作系统路径分隔符)。在 namespace 中，下划线_符号是没有（特殊）意义的。
6、当从文件系统中载入时，合格的 namespace 和 class 一定是以 .php 结尾的
7、verdor name,namespaces,class 名可以由大小写字母组合而成（大小写敏感的）

  
具体规则可能有些让人晕，我们从头讲一下。
  
我们先来看PSR0标准大致内容，第1、2、3、7条对命名空间的名字做出了限制，第4、5条对命名空间和文件目录的映射关系做出了限制，第6条是文件后缀名。
  
前面我们说过，PSR标准是如何规范命名空间和所在文件目录之间的映射关系？是通过限制命名空间的名字、所在文件目录的位置和两者映射关系。
  
那么我们可能就要问了，哪里限制了文件所在目录的位置了呢？其实答案就是：
  

限制命名空间名字 + 限制命名空间名字与文件目录映射 = 限制文件目录

  
好了，我们先想一想，对于一个具体程序来说，如果它想要支持PSR0标准,它需要做什么调整呢？

* 首先，程序必须定义一个符合PSR0标准第4、5条的映射函数，然后把这个函数注册到 spl_register() 中；

* 其次，定义一个新的命名空间时，命名空间的名字和所在文件的目录位置必须符合第1、2、3、7条。


   
一般为了代码维护方便，我们会在一个文件只定义一个命名空间。
   
好了，我们有了符合PSR0的命名空间的名字，通过符合PSR0标准的映射关系就可以得到符合PSR0标准的文件目录地址，如果我们按照PSR0标准正确存放文件，就可以顺利require该文件了，我们就可以使用该命名空间啦，是不是很神奇呢？
   
接下来，我们详细地来看看PSR0标准到底规范了什么呢？
   
我们以 laravel 中第三方库Symfony其中一个命名空间 /Symfony/Core/Request 为例，讲一讲上面 PSR0 标准。
  
* 一个完全合格的 namespace 和 class 必须符合这样的结构：“< Vendor Name>(< Namespace>)*< Class Name>”


上面所展示的 /Symfony 就是 Vendor Name，也就是第三方库的名字，/Core 是 Namespace 名字，一般是我们命名空间的一些属性信息(例如 request 是 Symfony 的核心功能)；最后 Request 就是我们命名空间的名字，这个标准规范就是让人看到命名空间的来源、功能非常明朗，有利于代码的维护。
  

2 . 每个 namespace 必须有一个顶层的 namespace（"Vendor Name" 提供者名字）

也就是说每个命名空间都要有一个类似于 /Symfony 的顶级命名空间，为什么要有这种规则呢？因为 PSR0 标准只负责顶级命名空间之后的映射关系，也就是 /Symfony/Core/Request 这一部分，关于 /Symfony 应该关联到哪个目录，那就是用户或者框架自己定义的了。所谓的顶层的 namespace，就是自定义了映射关系的命名空间，一般就是提供者名字（第三方库的名字）。换句话说顶级命名空间是自动加载的基础。为什么标准要这么设置呢？原因很简单，如果有个命名空间是 /Symfony/Core/Transport/Request，还有个命名空间是 /Symfony/Core/Transport/Request1,如果没有顶级命名空间，我们就得写两个路径和这两个命名空间相对应，如果再有 Request2、Request3 呢。有了顶层命名空间 /Symfony，那我们就仅仅需要一个目录对应即可，剩下的就利用 PSR 标准去解析就行了。
  

3.每个namespace可以有多个子namespace

这个很简单，Request 可以定义成 /Symfony/Core/Request，也可以定义成 /Symfony/Core/Transport/Request，/Core 这个命名空间下面可以有很多子命名空间，放多少层命名空间都是自己定义。

  

4.当从文件系统中加载时，每个 namespace 的分隔符(/)要转换成  DIRECTORY_SEPARATOR (操作系统路径分隔符)

现在我们终于来到了映射规范了。命名空间的/符号要转为路径分隔符，也就是说要把 /Symfony/Core/Request 这个命名空间转为 SymfonyCoreRequest 这样的目录结构。
  

5.在类名中，每个下划线 _ 符号要转换成 DIRECTORYSEPARATOR (操作系统路径分隔符)。在 namespace 中，下划线符号是没有（特殊）意义的。

这句话的意思就是说，如果我们的命名空间是 /Symfony/Core/Request_a，那么我们就应该把它映射到 SymfonyCoreRequesta 这样的目录。为什么会有这种规定呢？这是因为 PHP5 之前并没有命名空间，程序员只能把名字起成 Symfony_Core_Request_a 这样， PSR0 的这条规定就是为了兼容这种情况。
剩下两个很简单就不说了。
  
有这样的命名空间命名规则和映射标准，我们就可以推理出我们应该把命名空间所在的文件该放在哪里了。依旧以 Symfony/Core/Request 为例， 它的目录是 /path/to/project/vendor/Symfony/Core/Request.php，其中 /path/to/project是你项目在磁盘的位置，/path/to/project/vendor 是项目用的所有第三方库所在目录。/path/to/project/vendor/Symfony 就是与顶级命名空间 /Symfony 存在对应关系的目录，再往下的文件目录就是按照PSR0标准建立的：

/Symfony/Core/Request  =>  /Symfony/Core/Request.php

一切很完满了是吗？不，还有一些瑕疵：

* 我们是否应该还兼容没有命名空间的情况呢？

* 按照 PSR0 标准，命名空间 /A/B/C/D/E/F 必然对应一个目录结构 /A/B/C/D/E/F，这种目录结构层次是不是太深了？


## PSR4标准

2013年底，新出了第5个规范——PSR-4。

PSR-4规范了如何指定文件路径从而自动加载类定义，同时规范了自动加载文件的位置。这个乍一看和 PSR-0 重复了，实际上，在功能上确实有所重复。区别在于 PSR-4 的规范比较干净，去除了兼容 PHP 5.3 以前版本的内容，有一点 PSR-0 升级版的感觉。当然，PSR-4 也不是要完全替代 PSR-0，而是在必要的时候补充 PSR-0 ——当然，如果你愿意，PSR-4 也可以替代 PSR-0。PSR-4 可以和包括 PSR-0 在内的其他自动加载机制共同使用。
  
PSR4标准与PSR0标准的区别：

* 在类名中使用下划线没有任何特殊含义。

* 命名空间与文件目录的映射方法有所调整。


对第二项我们详细解释一下 ( [Composer自动加载的原理][8])：
假如我们有一个命名空间：Foo/class，Foo 是顶级命名空间，其存在着用户定义的与目录的映射关系：

```php
"Foo/" => "src/"

```

按照PSR0标准，映射后的文件目录是: src/Foo/class.php，但是按照 PSR4 标准，映射后的文件目录就会是:src/class.php，为什么要这么更改呢？原因就是怕命名空间太长导致目录层次太深，使得命名空间和文件目录的映射关系更加灵活。

再举一个例子,来源 [PSR-4——新鲜出炉的PHP规范][9]：
PSR-0风格

```
    vendor/
    vendor_name/
        package_name/
            src/
                Vendor_Name/
                    Package_Name/
                        ClassName.php       # Vendor_Name\Package_Name\ClassName
            tests/
                Vendor_Name/
                    Package_Name/
                        ClassNameTest.php   # Vendor_Name\Package_Name\ClassName
```

  PSR-4风格

```
    vendor/
    vendor_name/
        package_name/
            src/
                ClassName.php       # Vendor_Name\Package_Name\ClassName
            tests/
                ClassNameTest.php   # Vendor_Name\Package_Name\ClassNameTest
```

对比以上两种结构，明显可以看出PSR-4带来更简洁的文件结构。

Written with [StackEdit][10].

[0]: http://www.leoyang90.cn
[1]: http://blog.csdn.net/hguisu/article/details/7463333
[2]: http://www.jb51.net/article/31279.htm
[3]: http://www.jb51.net/article/56370.htm
[4]: http://php.net/manual/zh/language.namespaces.rationale.php
[5]: http://laravelacademy.org/post/4221.html
[6]: https://www.php-fig.org
[7]: https://www.zybuluo.com/phper/note/65033
[8]: http://hanfeng.name/blog/2015/08/17/composer-autoload/
[9]: https://segmentfault.com/a/1190000000380008
[10]: https://stackedit.io/