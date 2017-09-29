# 你真的了解现在的PHP吗？（1）

 时间 2017-01-26 15:15:52 

原文[http://www.jianshu.com/p/5a1b9e8cda7f][1]


前段时间，公司的项目从PHP5.3升级到PHP7，现在项目里开始使用PHP7的一些新语法和特性。反观PHP的5.4、5.5、5.6版本，有点认知缺失的感觉。所以，决定看《Modern PHP》补一补里面的一些概念。

![][3]

在看这本书

## 一、特性

## 1. 命名空间

命名空间用的比较多，不详细写了，记录几个值得注意的实践和细节。

多重导入 

别这么做，这样写容易让人困惑。

```php
    <?php
    use Symfony\Component\HttpFoundation\Request,
        Symfony\Component\HttpFoundation\Response,
        Symfony\Component\HttpFoundation\Cookie;
```

建议一行写一个use语句：

```php
    <?php
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Cookie;
```

一个文件中使用多个命名空间 

你可以这么做，但这违背了 **“一个文件定义一个类”** 的良好实践。 

```php
    <?php
    namespace Foo {
      //code
    }
    
    namespace Bar {
      //code 
    }
```

全局命名空间 

想要使用PHP原生的Exception类，需要在类名前加 `\` 符号。

```php
    <?php
    namespace My\App;
    
    class Foo
    {
      public function doSomething()
      {
        $exception = new \Exception();
      }
    }
```

如果Exception前不加 `\` 符号，会在My\App命名空间下寻找Exception类。

## 2. 使用接口

使用接口编写的代码更灵活，能委托其他人实现细节。使用的人只需要关心有什么接口，而不需要关心实现。能够很好地解耦代码，方便扩展，比较常用就不说啦。

## 3. 性状

在学习laravel框架之前都没弄清楚性状（trait）。这是PHP5.4.0引入的新概念，既像类又像接口。但它两个都不是。

性状是类的部分实现，可以混入一个或多个现有PHP类中。类似Ruby的组合模块活混入（mixin）。

为什么使用性状 

举个具体的例子，比如有两个类，Car 和 Phone，他们都需要GPS功能。为了解决这个问题，第一反应创建一个父类，然后让Car和Phone继承它。但因为很明显，这个祖先不属于各自的继承层次结构。

第二反应创建一个GPS的接口，定义好GPS的功能接口，然后让Car和Phone两个类都实现这个接口。这样做能实现功能，同时也能保持自然的继承层级结构。不过，这就使得在两个都要实现重复的GPS功能，这不符合DRY（dont repeat yourself）原则。

第三反应创建实现GPS功能的性状（trait），然后在Car和Phone类中混入这个性状。能实现功能，不影响继承结构，不重复实现，完美。

创建与使用性状 

创建trait

```php
    <?php
    trait MyTrait{
      //实现
    }
```

使用trait

```php
    <?php
    class MyClass
    {
      use MyTrait;
      // 类的实现
    }
```

## 4. 生成器

PHP生成器（generator）是PHP5.5.0引入的新功能，很多PHP开发者生成器不了解。生成器是个简单的迭代器，但生成器不要求实现Iterator接口。生成器会根据需要计算并产生要迭代的值。如果不查询，生成器永远不知道下一个要迭代的值是什么，在生成器中无法后退或快进。具体看如下两个例子：

#### 简单的生成器

```php
    <?php
    function makeRange($length) {
      for ($i = 0; $i < $length; $i++) {
        yield $i;
      }
    }
    
    foreach (makeRange(1000000) as $i) {
      echo $i, PHP_EOL;
    }
```

#### 具体场景：使用生成器处理CSV文件

```php
    <?php
    function getRows($file) {
      $handle = fopen($file, 'rb');
      if ($handle === false) {
        throw new Exception();
      }
      while (feof($handle) === false) {
        yield fgetcsv($handle);
      }
    }
    
    foreach (getRows('data.csv') as $row) {
      print_r($row);
    }
```

处理这种场景，习惯的处理方法是先读取文件的所有内容放到数组中，然后再做处理等等。这种的处理存在的问题是： 当文件特别大，一次读取就占用很多内存资源。而生成器最适合这种场景，因为这样占用的系统内存量极少 。 

## 5. 闭包

理论上，闭包和匿名函数是不同的概念。不过，PHP将其视作相同的概念。

简单闭包 

```php
    <?php
    $closure = function ($name) {
      return sprintf('Hello %s', $name);
    }
    
    echo $closure("Beck");
    // 输出 --> “Hello Beck”
```

注意：我们之所以能调用`$closure`变量，是因为这个变量的值是个闭包，而且闭包对象实现了`__invoke()`魔术方法。只要变量名后有`()`，PHP就会查找并调用`__invoke()`方法。

附加状态 

使用use关键字可以把多个参数传入闭包，此时要像PHP函数或方法的参数一样，使用逗号分隔多个参数。

```php
    <?php
    function enclosePerson($name) {
      return function ($doCommand) use ($name) {
        return sprintf('%s, %s', $name, $doCommand);
      };
    }
    
    // 把字符串“Clay”封装在闭包中
    $clay = enclosePerson('Clay');
    
    // 传入参数，调用闭包
    echo $clay('get me sweet tea!');
    // 输出 --> "Clay, get me sweet tea!"
```

使用`bindTo()`方法附加闭包的状态 

PHP框架经常使用`bindTo()`方法把路由URL映射到匿名回调函数上，框架会把匿名函数绑定到应用对象上，这么做可以在这个匿名函数中使用`$this`关键字引用重要的应用对象。例子如下：

```php
    <?php
    class App
    {
      protected $routes = array();
      protected $responseStatus = '200 OK';
      protected $responseContentType = 'text/html';
      protected $responseBody = 'Hello world';
    
      public function addRoute($routePath, $routeCallback)
      {
        $this->routes[$routePath] = $routeCallback->bindTo($this, __CLASS__);//重点
      }
    
      public function dispatch($currentPath)
      {
        foreach ($this->routes as $routePath => $callback) {
          if ($routePath === $currentPath) {
            $callback();
          }
        }
    
        header('HTTP/1.1' . $this->responseStatus);
        header('Content-type:' . $this->responseContentType);
        header('Content-length' . mb_strlen($this->responseBody));
        echo $this->responseBody;
      }
    }
```

第11行是重点所在，把路由回调绑定到了当前的App实例上。这么做能在回调函数中处理App实例的状态：

```php
    <?php
    $app = new App();
    $app->addRoute('/users/josh', function () {
      $this->responseContentType = 'application/json;charset=utf8';
      $this->responseBody = '{"name": "Josh"}';
    });
    $app->dispatch('/users/josh');
```

## 6. Zend OPcache

字节码缓存不是PHP的新特性，很多独立的扩展可以实现缓存。从PHP5.5.0开始，PHP内置了字节码缓存功能，名为Zend OPcache。

字节码缓存是什么 

PHP是解释性语言，PHP解释器执行PHP脚本时会解析PHP脚本代码，把PHP代码编译成一系列Zend操作码，然后执行字节码。每次请求PHP文件都是这样，会消耗很多资源。字节码缓存能存储预先编译好的PHP字节码。这意味着，请求PHP脚本时，PHP解释器不用每次都读取、解析和编译PHP代码。这样能极大地提升应用的性能。

## 7. 内置的HTTP服务器

从PHP5.4.0起，PHP内置了Web服务器，这对众多使用Apache或nginx的php开发者来说，可能是个隐藏功能。不过，这个内置的服务器功能并不完善，不应该在生产环境中使用，但对本地开发来说是个便利的工具，可以用于快速预览一些框架和应用。

#### 启动服务器

    php -S localhost:4000

#### 配置服务器

    php -S localhost:8000 -c app/config/php.ini

路由器脚本 

与Apache和nginx不同，它不支持`.htaccess`文件。因此，这个服务器很难使用多数流行的PHP框架中常见的前端控制器。PHP内置的服务器使用路由器脚本弥补了这个遗漏的功能。处理每个HTTP请求前，会先经过这个路由器脚本，如果结果为false，返回当前HTTP请求中引用的静态资源URI。

    php -S localhost:8000 route.php

#### 是否为内置的服务器

```php
    <?php
    if (php_sapi_name() === 'cli-server') {
      // php 内置的web服务器
    }
```


[1]: http://www.jianshu.com/p/5a1b9e8cda7f

[3]: http://img1.tuicool.com/3uiUFzi.png