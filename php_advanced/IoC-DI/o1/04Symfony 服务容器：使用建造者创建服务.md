## Symfony 服务容器：使用建造者创建服务

来源：[http://blog.phpzendo.com/?p=334](http://blog.phpzendo.com/?p=334)

时间 2018-05-08 22:16:20


本文是依赖注入（Depeendency Injection）系列教程的第 4 篇文章，本系列教程主要讲解如何使用 PHP 实现一个轻量级服务容器，教程包括：


* [第 1 篇：什么是依赖注入？][0]
    
* [第 2 篇：是否需要使用依赖注入容器？][1]
    
* [第 3 篇：Symfony 服务容器入门][2]
    
* [第 4 篇：Symfony 服务容器：使用建造者创建服务][3]
    
* [@TODO 第 5 篇：Symfony 服务容器：使用 XML 或 YAML 文件描述服务][4]
    
* [@TODO 第 6 篇：性能优化][5]
    
  


## 术语


* **`Depeendency Injection`** 译作 **`依赖注入`**     
* **`Depeendency Injection Container`** 译作 **`依赖注入容器`**     
* **`Container`** 译作 **`容器`**     
* **`Service Container`** 译作 **`服务容器`**     
* **`Session`** 译作 **`会话`**     
* **`Object-Oriented`** 译作 **`面向对象`**     
* **`mock`** 译作 **`模拟`**     
* **`anti-patterns`** 译作 **`反模式`**     
* **`hardcoded`** 译作 **`硬编码`**     
  

在Symfony 服务容器入门 一文中，我们学习了如何通过继承 **`sfServiceContainer`** 类实现一个更加强大的自定义服务容器。这篇文件将会进一步讲解，如何通过使用 PHP 代码来设置服务配置选项供 **`spServiceContainerBuilder`** 建造者类创建服务。

SVN 版本库有更新。如果您已经检出版本，可以执行更新操作。如果还没有检出，可从    [http://svn.symfony-project.com/components/dependency_injection/trunk/][6]
检出版本（译注：不用试了，这个文章写作年代比较久远已经不再维护 SVN 版本了）。

spServiceContainerBuilder同样继承自 **`sfServiceContainer`** 基类，它提供了设置服务配置选项接口供开发者使用。


## 服务容器接口

所有「服务容器」类都继承自 **`sfServiceContainerInterface`** 接口:

```php
<?php
interface sfServiceContainerInterface
{
    public function setParameters(array $parameters);
    public function addParameters(array $parameters);
    public function getParameters();
    public function getParameter($name);
    public function setParameter($name, $value);
    public function hasParameter($name);
    public function setService($id, $service);
    public function getService($id);
    public function hasService($name);
}
```

服务的描述通过注册服务定义来完成。每个服务定义都描述了一个服务：从要使用的类、到传递给构造函数的参数、以及一堆其他配置属性（请参见下文 sfServiceDefinition）。

接下来删除掉 **`Zend_Mail`** 中所有硬编码代码，并使用构建器动态重写：

```php
<?php

require_once 'PATH/TO/sf/lib/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();

$sc = new sfServiceContainerBuilder();

$sc->
  register('mail.transport', 'Zend_Mail_Transport_Smtp')->
  addArgument('smtp.gmail.com')->
  addArgument(array(
    'auth'     => 'login',
    'username' => '%mailer.username%',
    'password' => '%mailer.password%',
    'ssl'      => 'ssl',
    'port'     => 465,
  ))->
  setShared(false)
;

$sc->
  register('mailer', '%mailer.class%')->
  addMethodCall('setDefaultTransport', array(new sfServiceReference('mail.transport')))
;
```

通过调用 **`register()`** 方法创建一个 **`sfServiceDefinition`** 实例，方法接收服务名和类名两个参数。

服务定义在内部将实例化为 sfServiceDefinition 实例。也可以手动创建一个实例，并使用服务容器 setServiceDefinition() 方法直接注册它。

定义对象支持链式操作，同时提供配置服务所需的方法。在上面的例子中，我们使用了以下几个方法：


* addArgument()：向待创建服务的构造函数添加参数；

    
* setShared()：是否启用单例模式创建服务（默认为：true）。

    
* addMethodCall()：创建服务后的回调方法。第二个参数是传递给该方法的参数数组。
  

这样一个 sfServiceReference 实例被添加到我们所需使用的服务里中。当这个特殊的类被调用时将被动态的替换成所需要的服务实例。

在注册阶段实际上没有创建服务实例，只是定义了服务的描述。这些服务只有在您真正想要使用它们时才会创建。这意味着您可以以任何顺序注册服务，而无需考虑它们之间的依赖关系。这也意味着您可以通过重新注册具有相同名称的服务来覆盖现有的服务定义。同时，这种定义服务的方法也更易于测试。


## sfServiceDefinition 类

这个类有诸多可用于修改创建和配置的方法：


* setConstructor()：设置创建服务的静态方法，而不是通过 new 关键字实例对象（结合工厂方法效果更佳）；
* setClass()：设置服务类名；
* setArguments()：设置传递给构造函数的参数（注意数组中元素的顺序）；
* addArgument()：为构造函数添加一个参数；
* setMethodCalls()：设置服务创建后回调方法组。调用顺序同设置顺序相同；
* addMethodCall()：添加服务创建后的回调方法。如果需要，您可以多次向同一个方法添加个调用；
* setFile()：设置待创建服务里的引入文件名（如果未启用自动加载机制，需要使用该方法引入文件）；
* setShared()：是否启用单例模式创建服务（默认为：true）；
* setConfigurator()：设置服务配置完成后的回调函数。
  

由于 sfServiceContainerBuilder 类实现了 sfServiceContainerInterface 接口，所以可以像之前一样使用容器中的方法：

```php
<?php
$sc->addParameters(array(
  'mailer.username' => 'foo',
  'mailer.password' => 'bar',
  'mailer.class'    => 'Zend_Mail',
));

$mailer = $sc->mailer;
```

sfServiceContainerBuilder 类能够描述任何对象是如何实例化和配置的。我们已经在 **`Zend_Mail`** 实例中使用了容器，下面是另一个相关示例：

```php
$sc = new sfServiceContainerBuilder(array(
  'storage.class'        => 'sfMySQLSessionStorage',
  'storage.options'      => array('database' => 'session', 'db_table' => 'session'),
  'user.class'           => 'sfUser',
  'user.default_culture' => 'en',
));

$sc->register('dispatcher', 'sfEventDispatcher');

$sc->
  register('storage', '%storage.class%')->
  addArgument('%storage.options%')
;

$sc->
  register('user', '%user.class%')->
  addArgument(new sfServiceReference('dispatcher'))->
  addArgument(new sfServiceReference('storage'))->
  addArgument(array('default_culture' => '%user.default_culture%'))->
;

$user = $sc->user;
```

在这个 Symfony 示例中，即使存储对象将一个选项数组作为参数，我们也会传递一个字符串占位符（addArgument('%storage.options%')）。容器功能强大到支持传递一个置为占位符的数组。

以上就是今天的全部内容。使用 PHP 代码来描述服务非常简单而且功能强大。它为您提供了一个创建容器的工具，无需复制过多的代码，并抽象了对象实例化过程和配置。在下一篇文章中，我们将看到如何使用 XML 或 YAML 文件来描述服务。敬请期待吧！

原文：    [http://fabien.potencier.org/symfony-service-container-using-a-builder-to-create-services.html][7]


[0]: http://blog.phpzendo.com/?p=313
[1]: http://blog.phpzendo.com/?p=318
[2]: http://blog.phpzendo.com/?p=321
[3]: http://blog.phpzendo.com/?p=334
[4]: http://fabien.potencier.org/symfony-service-container-using-xml-or-yaml-to-describe-services.html
[5]: http://fabien.potencier.org/symfony-service-container-the-need-for-speed.html
[6]: http://svn.symfony-project.com/components/dependency_injection/trunk/
[7]: http://fabien.potencier.org/symfony-service-container-using-a-builder-to-create-services.html