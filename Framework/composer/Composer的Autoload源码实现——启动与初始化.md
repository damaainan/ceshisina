## Composer的Autoload源码实现——启动与初始化

来源：[https://segmentfault.com/a/1190000009369297](https://segmentfault.com/a/1190000009369297)


## 前言

-----

在开始之前，欢迎关注我自己的博客：[www.leoyang90.cn][0]

[上一篇文章][1]，我们讨论了 PHP 的自动加载原理、PHP 的命名空间、PHP 的 PSR0 与 PSR4 标准，有了这些知识，其实我们就可以按照 PSR4 标准写出可以自动加载的程序了。然而我们为什么要自己写呢？尤其是有 Composer 这神一样的包管理器的情况下？
## Composer 自动加载概论

-----

## 简介

Composer 是 PHP 的一个依赖管理工具。它允许你申明项目所依赖的代码库，它会在你的项目中为你安装他们。详细内容可以查看 [Composer 中文网][2]。
Composer Composer 将这样为你解决问题：

* 你有一个项目依赖于若干个库。

* 其中一些库依赖于其他库。

* 你声明你所依赖的东西。

* Composer 会找出哪个版本的包需要安装，并安装它们（将它们下载到你的项目中）。


例如，你正在创建一个项目，你需要一个库来做日志记录。你决定使用 monolog。为了将它添加到你的项目中，你所需要做的就是创建一个 composer.json 文件，其中描述了项目的依赖关系。

```json
{
  "require": {
    "monolog/monolog": "1.2.*"
  }
}
```

然后我们只要在项目里面直接use MonologLogger即可，神奇吧！
简单的说，Composer 帮助我们下载好了符合 PSR0 或 PSR4 标准的第三方库，并把文件放在相应位置；帮我们写了 _autoload() 函数，注册到了 spl_register() 函数，当我们想用第三方库的时候直接使用命名空间即可。
  
那么当我们想要写自己的命名空间的时候，该怎么办呢？很简单，我们只要按照 PSR4 标准命名我们的命名空间，放置我们的文件，然后在 composer 里面写好顶级域名与具体目录的映射，就可以享用 composer 的便利了。
当然如果有一个非常棒的框架，我们会惊喜地发现，在 composer 里面写顶级域名映射这事我们也不用做了，框架已经帮我们写好了顶级域名映射了，我们只需要在框架里面新建文件，在新建的文件中写好命名空间，就可以在任何地方 use 我们的命名空间了。
下面我们就以 laravel 框架为例，讲一讲 composer 是如何实现 PSR0 和 PSR4 标准的自动加载功能。
## Composer 自动加载文件

首先，我们先大致了解一下 Composer 自动加载所用到的源文件。

* **`autoload_real.php`**：自动加载功能的引导类。任务是 composer 加载类的初始化(顶级命名空间与文件路径映射初始化)和注册( `spl_autoload_register()` )。

* **`ClassLoader.php`**：composer 加载类。composer 自动加载功能的核心类。

* **`autoload_static.php`**：顶级命名空间初始化类，用于给核心类初始化顶级命名空间。

* **`autoload_classmap.php`**：自动加载的最简单形式，有完整的命名空间和文件目录的映射；

* **`autoload_files.php`**：用于加载全局函数的文件，存放各个全局函数所在的文件路径名；

* **`autoload_namespaces.php`**：符合 PSR0 标准的自动加载文件，存放着顶级命名空间与文件的映射；

* **`autoload_psr4.php`**：符合 PSR4 标准的自动加载文件，存放着顶级命名空间与文件的映射；


## 启动

-----

laravel 框架的初始化是需要 composer 自动加载协助的，所以 laravel 的入口文件 index.php 第一句就是利用 composer 来实现自动加载功能。

```php
  require __DIR__.'/../bootstrap/autoload.php';
```

咱们接着去看 bootstrap 目录下的 autoload.php：

```php
  define('LARAVEL_START', microtime(true));

  require __DIR__.'/../vendor/autoload.php';
```

再去vendor目录下的autoload.php：

```php
  require_once __DIR__ . '/composer' . '/autoload_real.php';

  return ComposerAutoloaderInit
  832ea71bfb9a4128da8660baedaac82e::getLoader();
```

为什么框架要在 bootstrap/autoload.php 转一下？个人理解，laravel 这样设计有利于支持或扩展任意有自动加载的第三方库。
好了，我们终于要看到了 Composer 真正要显威的地方了。`autoload_real` 里面就是一个自动加载功能的引导类，这个类不负责具体功能逻辑，只做了两件事：初始化自动加载类、注册自动加载类。
到 `autoload_real` 这个文件里面去看，发现这个引导类的名字叫 ComposerAutoloaderInit832ea71bfb9a4128da8660baedaac82e，为什么要叫这么古怪的名字呢？因为这是防止用户自定义类名跟这个类重复冲突了，所以在类名上加了一个 hash 值。
其实还有一个原因，那就是composer运行加载多个`ComposerAutoloaderInit`类。在实际情况下可能会出现这样的情况：vendor／modelA／vendor／composer。也就是说第三方库中也存在着一个composer，他有着自己所依赖的各种库，也是通过composer来加载。这样的话就会有两个ComposerAutoloaderInit类，那么就会触发redeclare的错误。给ComposerAutoloaderInit加上一个hash，那么就可以实现多个class loader 的加载。
## autoload_real 引导类

-----

在 vendor 目录下的 autoload.php 文件中我们可以看出，程序主要调用了引导类的静态方法 **`getLoader()`**，我们接着看看这个函数。

```php
public static function getLoader()
{
  /***************************经典单例模式********************/
  if (null !== self::$loader) {
      return self::$loader;
  }

  /***********************获得自动加载核心类对象********************/
  spl_autoload_register(array('ComposerAutoloaderInit
  832ea71bfb9a4128da8660baedaac82e', 'loadClassLoader'), true, true);

  self::$loader = $loader = new \Composer\Autoload\ClassLoader();

  spl_autoload_unregister(array('ComposerAutoloaderInit
  832ea71bfb9a4128da8660baedaac82e', 'loadClassLoader'));

  /***********************初始化自动加载核心类对象********************/
  $useStaticLoader = PHP_VERSION_ID >= 50600 &&
  !defined('HHVM_VERSION');

  if ($useStaticLoader) {
      require_once __DIR__ . '/autoload_static.php';

      call_user_func(\Composer\Autoload\ComposerStaticInit
      832ea71bfb9a4128da8660baedaac82e::getInitializer($loader));

  } else {
      $map = require __DIR__ . '/autoload_namespaces.php';
      foreach ($map as $namespace => $path) {
          $loader->set($namespace, $path);
      }

      $map = require __DIR__ . '/autoload_psr4.php';
      foreach ($map as $namespace => $path) {
          $loader->setPsr4($namespace, $path);
      }

      $classMap = require __DIR__ . '/autoload_classmap.php';
      if ($classMap) {
          $loader->addClassMap($classMap);
      }
  }

  /***********************注册自动加载核心类对象********************/
  $loader->register(true);

  /***********************自动加载全局函数********************/
  if ($useStaticLoader) {
      $includeFiles = Composer\Autoload\ComposerStaticInit
                      832ea71bfb9a4128da8660baedaac82e::$files;
  } else {
      $includeFiles = require __DIR__ . '/autoload_files.php';
  }

  foreach ($includeFiles as $fileIdentifier => $file) {
      composerRequire
      832ea71bfb9a4128da8660baedaac82e($fileIdentifier, $file);
  }

  return $loader;
}
```

从上面可以看出，我把自动加载引导类分为5个部分。
## 第一部分——单例

-----

第一部分很简单，就是个最经典的单例模式，自动加载类只能有一个,多次加载影响效率，可能会引起重复require同一个文件。

```php
if (null !== self::$loader) {
  return self::$loader;
}
```
## 第二部分——构造 ClassLoader 核心类

-----

第二部分 new 一个自动加载的核心类对象。

```php
  /***********************获得自动加载核心类对象********************/
  spl_autoload_register(array('ComposerAutoloaderInit
  832ea71bfb9a4128da8660baedaac82e', 'loadClassLoader'), true, true);

  self::$loader = $loader = new \Composer\Autoload\ClassLoader();

  spl_autoload_unregister(array('ComposerAutoloaderInit
               832ea71bfb9a4128da8660baedaac82e', 'loadClassLoader'));
```

loadClassLoader() 函数：

```php
public static function loadClassLoader($class)
{
if ('Composer\Autoload\ClassLoader' === $class) {
    require __DIR__ . '/ClassLoader.php';
}
}
```

从程序里面我们可以看出，composer 先向 PHP 自动加载机制注册了一个函数，这个函数 require 了 ClassLoader 文件。成功 new 出该文件中核心类 `ClassLoader()` 后，又销毁了该函数。
为什么不直接 require，而要这么麻烦？原因和ComposerAutoloaderInit加上hash一样，如果直接require，那么会造成ClassLoader类的重复定义。所以有人建议这样：

```php
if (!class_exists('Composer\Autoload\ClassLoader', false)) {
  require __DIR__ . '/ClassLoader.php';
}
static::\$loader = \$loader = new \\Composer\\Autoload\\ClassLoader();
```

其实这样可以更加直观。但是`class_exists`有个缺点，那就是opcache缓存有个bug，`class_exists`即使为真，程序仍然会进入if条件进行require，这样仍然造成了重复定义的问题。
那为什么不跟引导类一样用个 hash 呢？这样就可以多次定义这个ClassLoader类了。原因就是这个类是可以复用的，框架允许用户使用这个类，如果用hash用户就完全没办法用ClassLoader了。
所以最终的解决方案就是利用`spl_autoload_register`来加载，这样只要ClassLoader只要被声明过，spl_autoload_register就不会调用，也就不会require。
可见这简单的几行代码其实内幕很深的。详细可见
[github 的相关 issue：Unable to run tests with phpunit and composer installed globally #1248][3]
[github 相关解决方案 PR : Allow loading of multiple composer autoloaders concurrently, fixes #1248 #1313][4]
## 第三部分——初始化核心类对象

-----

```php
  /***********************初始化自动加载核心类对象********************/
  $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION');
  if ($useStaticLoader) {
      require_once __DIR__ . '/autoload_static.php';

      call_user_func(\Composer\Autoload\ComposerStaticInit
      832ea71bfb9a4128da8660baedaac82e::getInitializer($loader));
  } else {
      $map = require __DIR__ . '/autoload_namespaces.php';
      foreach ($map as $namespace => $path) {
          $loader->set($namespace, $path);
      }

      $map = require __DIR__ . '/autoload_psr4.php';
      foreach ($map as $namespace => $path) {
      $loader->setPsr4($namespace, $path);
  }

  $classMap = require __DIR__ . '/autoload_classmap.php';
  if ($classMap) {
      $loader->addClassMap($classMap);
  }
}
```

这一部分就是对自动加载类的初始化，主要是给自动加载核心类初始化顶级命名空间映射。初始化的方法有两种：(1)使用 `autoload_static` 进行静态初始化；(2)调用核心类接口初始化。
### autoload_static 静态初始化

-----

静态初始化只支持 PHP5.6 以上版本并且不支持 HHVM 虚拟机。为什么要单独要求 php5.6 版本以上呢？原因就是这种静态加载加速机制是 opcache 缓存针对静态数组优化的，只支持 php5.6 以上的版本。hhvm 是 php 另一个虚拟机，当然没有办法支持 opcache 缓存。
[github相关 PR: Speedup autoloading on PHP 5.6 & 7.0+ using static arrays][5]
我们深入 autoload_static.php 这个文件发现这个文件定义了一个用于静态初始化的类，名字叫 ComposerStaticInit832ea71bfb9a4128da8660baedaac82e，仍然为了避免冲突加了 hash 值和多次复用。这个类很简单：

```php
class ComposerStaticInit832ea71bfb9a4128da8660baedaac82e{
    public static $files = array(...);
    public static $prefixLengthsPsr4 = array(...);
    public static $prefixDirsPsr4 = array(...);
    public static $prefixesPsr0 = array(...);
    public static $classMap = array (...);

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 =   ComposerStaticInit832ea71bfb9a4128da8660baedaac82e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit832ea71bfb9a4128da8660baedaac82e::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit832ea71bfb9a4128da8660baedaac82e::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit832ea71bfb9a4128da8660baedaac82e::$classMap;

        }, null, ClassLoader::class);
    }
}
```

这个静态初始化类的核心就是 `getInitializer()` 函数，它将自己类中的顶级命名空间映射给了 ClassLoader 类。值得注意的是这个函数返回的是一个匿名函数，为什么呢？原因就是 ClassLoader 类中的 prefixLengthsPsr4、prefixDirsPsr4 等等都是 private的。。。普通的函数没办法给类的 private 成员变量赋值。利用匿名函数的绑定功能就可以将把匿名函数转为 ClassLoader 类的成员函数。
关于匿名函数的 [绑定功能][6]。
接下来就是顶级命名空间初始化的关键了。
#### 最简单的classMap:

```php
public static $classMap = array (
    'App\\Console\\Kernel' => __DIR__ . '/../..' . '/app/Console/Kernel.php',
    'App\\Exceptions\\Handler' => __DIR__ . '/../..' . '/app/Exceptions/Handler.php',
    'App\\Http\\Controllers\\Auth\\ForgotPasswordController' => __DIR__ . '/../..' . '/app/Http/Controllers/Auth/ForgotPasswordController.php',
    'App\\Http\\Controllers\\Auth\\LoginController' => __DIR__ . '/../..' . '/app/Http/Controllers/Auth/LoginController.php',
    'App\\Http\\Controllers\\Auth\\RegisterController' => __DIR__ . '/../..' . '/app/Http/Controllers/Auth/RegisterController.php',
...
)
```

简单吧，直接命名空间全名与目录的映射，没有顶级命名空间。。。简单粗暴，也导致这个数组相当的大。
#### PSR0顶级命名空间映射：

```php
public static $prefixesPsr0 = array (
  'P' =>
  array (
    'Prophecy\\' =>
    array (
      0 => __DIR__ . '/..' . '/phpspec/prophecy/src',
    ),
    'Parsedown' =>
    array (
      0 => __DIR__ . '/..' . '/erusev/parsedown',
    ),
  ),
  'M' =>
  array (
    'Mockery' =>
    array (
      0 => __DIR__ . '/..' . '/mockery/mockery/library',
    ),
  ),
  'J' =>
  array (
    'JakubOnderka\\PhpConsoleHighlighter' =>
    array (
      0 => __DIR__ . '/..' . '/jakub-onderka/php-console-highlighter/src',
    ),
    'JakubOnderka\\PhpConsoleColor' =>
    array (
      0 => __DIR__ . '/..' . '/jakub-onderka/php-console-color/src',
    ),
  ),
  'D' =>
  array (
    'Doctrine\\Common\\Inflector\\' =>
    array (
      0 => __DIR__ . '/..' . '/doctrine/inflector/lib',
    ),
  ),
);
```

为了快速找到顶级命名空间，我们这里使用命名空间第一个字母作为前缀索引。这个映射的用法比较明显，假如我们有 Parsedown/example 这样的命名空间，首先通过首字母 P，找到

```php
  'P' =>
  array (
    'Prophecy\\' =>
    array (
      0 => __DIR__ . '/..' . '/phpspec/prophecy/src',
    ),
    'Parsedown' =>
    array (
      0 => __DIR__ . '/..' . '/erusev/parsedown',
    ),
  )
```

这个数组，然后我们就会遍历这个数组来和 Parsedown/example 比较，发现第一个 Prophecy 不符合，第二个 Parsedown 符合，然后得到了映射目录：(映射目录可能不止一个)

```php
array (
  0 => __DIR__ . '/..' . '/erusev/parsedown',
)
```

我们会接着遍历这个数组，尝试 _ DIR_  .'/..' . '/erusev/parsedown/Parsedown/example.php 是否存在，如果不存在接着遍历数组(这个例子数组只有一个元素)，如果数组遍历完都没有，就会加载失败。
#### PSR4标准顶级命名空间映射数组：

```php
public static $prefixLengthsPsr4 = array(
  'p' =>
  array (
    'phpDocumentor\\Reflection\\' => 25,
  ),
  'S' =>
  array (
    'Symfony\\Polyfill\\Mbstring\\' => 26,
    'Symfony\\Component\\Yaml\\' => 23,
    'Symfony\\Component\\VarDumper\\' => 28,
    ...
  ),
  ...
);

public static $prefixDirsPsr4 = array (
  'phpDocumentor\\Reflection\\' =>
  array (
    0 => __DIR__ . '/..' . '/phpdocumentor/reflection-common/src',
    1 => __DIR__ . '/..' . '/phpdocumentor/type-resolver/src',
    2 => __DIR__ . '/..' . '/phpdocumentor/reflection-docblock/src',
  ),
  'Symfony\\Polyfill\\Mbstring\\' =>
  array (
    0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
  ),
  'Symfony\\Component\\Yaml\\' =>
  array (
    0 => __DIR__ . '/..' . '/symfony/yaml',
  ),
  ...
)
```

PSR4 标准顶级命名空间映射用了两个数组，第一个和 PSR0 一样用命名空间第一个字母作为前缀索引，然后是顶级命名空间，但是最终并不是文件路径，而是顶级命名空间的长度。为什么呢？因为前一篇 [文章][7] 我们说过，PSR4 标准的文件目录更加灵活，更加简洁。PSR0 中顶级命名空间目录直接加到命名空间前面就可以得到路径 (Parsedown/example => _ DIR_  .'/..' . '/erusev/parsedown/Parsedown/example.php)，而 PSR4 标准却是用顶级命名空间目录替换顶级命名空间（Parsedown/example => _ DIR_  .'/..' . '/erusev/parsedown/example.php)，所以获得顶级命名空间的长度很重要。
具体的用法：假如我们找 Symfony\Polyfill\Mbstring\example 这个命名空间，和 PSR0 一样通过前缀索引和字符串匹配我们得到了

```php
  'Symfony\\Polyfill\\Mbstring\\' => 26,
```

这条记录，键是顶级命名空间，值是命名空间的长度。拿到顶级命名空间后去 $prefixDirsPsr4 数组获取它的映射目录数组：(注意映射目录可能不止一条)

```php
'Symfony\\Polyfill\\Mbstring\\' =>
array (
  0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
)
```

然后我们就可以将命名空间 Symfony\Polyfill\Mbstring\example 前26个字符替换成目录 _ DIR_  . '/..' . '/symfony/polyfill-mbstring，我们就得到了 _ DIR_  . '/..' . '/symfony/polyfill-mbstring/example.php，先验证磁盘上这个文件是否存在，如果不存在接着遍历。如果遍历后没有找到，则加载失败。
  
自动加载核心类 ClassLoader 的静态初始化完成！！！
### ClassLoader 接口初始化

-----

如果PHP版本低于5.6或者使用HHVM虚拟机环境，那么就要使用核心类的接口进行初始化。

```php
  //PSR0标准
  $map = require __DIR__ . '/autoload_namespaces.php';
  foreach ($map as $namespace => $path) {
      $loader->set($namespace, $path);
  }

  //PSR4标准
  $map = require __DIR__ . '/autoload_psr4.php';
  foreach ($map as $namespace => $path) {
      $loader->setPsr4($namespace, $path);
  }

  $classMap = require __DIR__ . '/autoload_classmap.php';
  if ($classMap) {
      $loader->addClassMap($classMap);
  }
```
#### PSR0 标准

autoload_namespaces：

```php
return array(
  'Prophecy\\' => array($vendorDir . '/phpspec/prophecy/src'),
  'Parsedown' => array($vendorDir . '/erusev/parsedown'),
  'Mockery' => array($vendorDir . '/mockery/mockery/library'),
  'JakubOnderka\\PhpConsoleHighlighter' => array($vendorDir . '/jakub-onderka/php-console-highlighter/src'),
  'JakubOnderka\\PhpConsoleColor' => array($vendorDir . '/jakub-onderka/php-console-color/src'),
  'Doctrine\\Common\\Inflector\\' => array($vendorDir . '/doctrine/inflector/lib'),
);
```

PSR0 标准的初始化接口：

```php
public function set($prefix, $paths)
{
  if (!$prefix) {
    $this->fallbackDirsPsr0 = (array) $paths;
  } else {
    $this->prefixesPsr0[$prefix[0]][$prefix] = (array) $paths;
  }
}
```

很简单，PSR0 标准取出命名空间的第一个字母作为索引，一个索引对应多个顶级命名空间，一个顶级命名空间对应多个目录路径，具体形式可以查看上面我们讲的 `autoload_static` 的 `$prefixesPsr0`。如果没有顶级命名空间，就只存储一个路径名，以便在后面尝试加载。
#### PSR4标准

autoload_psr4

```php
return array(
  'XdgBaseDir\\' => array($vendorDir . '/dnoegel/php-xdg-base-dir/src'),
  'Webmozart\\Assert\\' => array($vendorDir . '/webmozart/assert/src'),
  'TijsVerkoyen\\CssToInlineStyles\\' => array($vendorDir . '/tijsverkoyen/css-to-inline-styles/src'),
  'Tests\\' => array($baseDir . '/tests'),
  'Symfony\\Polyfill\\Mbstring\\' => array($vendorDir . '/symfony/polyfill-mbstring'),
  ...
)
```

PSR4 标准的初始化接口:

```php
public function setPsr4($prefix, $paths)
{
  if (!$prefix) {
    $this->fallbackDirsPsr4 = (array) $paths;
  } else {
    $length = strlen($prefix);
    if ('\\' !== $prefix[$length - 1]) {
      throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
    }
    
    $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
    $this->prefixDirsPsr4[$prefix] = (array) $paths;
  }
}
```

PSR4 初始化接口也很简单。如果没有顶级命名空间，就直接保存目录。如果有命名空间的话，要保证顶级命名空间最后是**`\`**，然后分别保存（前缀=》顶级命名空间，顶级命名空间=》顶级命名空间长度），（顶级命名空间=》目录）这两个映射数组。具体形式可以查看上面我们讲的 `autoload_static`的`prefixLengthsPsr4`、 `$prefixDirsPsr4`。
#### 傻瓜式命名空间映射

autoload_classmap：

```php
public static $classMap = array (
  'App\\Console\\Kernel' => __DIR__ . '/../..' . '/app/Console/Kernel.php',
  'App\\Exceptions\\Handler' => __DIR__ . '/../..' . '/app/Exceptions/Handler.php',
  ...
)
```

addClassMap:

```php
public function addClassMap(array $classMap)
{
  if ($this->classMap) {
    $this->classMap = array_merge($this->classMap, $classMap);
  } else {
    $this->classMap = $classMap;
  }
}
```

这个最简单，就是整个命名空间与目录之间的映射。
## 结语

其实我很想接着写下下去，但是这样会造成篇幅过长，所以我就把自动加载的注册和运行放到下一篇文章了。我们回顾一下，这篇文章主要讲了：（1）框架如何启动 composer 自动加载;（2）composer 自动加载分为5部分；
其实说是5部分，真正重要的就两部分——初始化与注册。初始化负责顶层命名空间的目录映射，注册负责实现顶层以下的命名空间映射规则。

Written with [StackEdit][8].

[0]: http://www.leoyang90.cn
[1]: http://leoyang90.cn/2017/03/11/PHP-Composer-autoload/
[2]: http://docs.phpcomposer.com/00-intro.html
[3]: https://github.com/composer/composer/issues/1248
[4]: https://github.com/composer/composer/pull/1313
[5]: https://github.com/composer/composer/pull/5174
[6]: http://www.cnblogs.com/yjf512/p/4421289.html
[7]: http://leoyang90.cn/2017/03/11/PHP-Composer-autoload/
[8]: https://stackedit.io/