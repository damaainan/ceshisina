## Symfony 服务容器：使用 XML 或 YAML 文件描述服务

来源：[http://blog.phpzendo.com/?p=338](http://blog.phpzendo.com/?p=338)

时间 2018-05-13 22:28:01



## Symfony 服务容器：使用 XML 或 YAML 文件描述服务

本文是依赖注入（Depeendency Injection）系列教程的第 5 篇文章，本系列教程主要讲解如何使用 PHP 实现一个轻量级服务容器，教程包括：


* [第 1 篇：什么是依赖注入？][0]
    
* [第 2 篇：是否需要使用依赖注入容器？][1]
    
* [第 3 篇：Symfony 服务容器入门][2]
    
* [第 4 篇：Symfony 服务容器：使用建造者创建服务][3]
    
* [第 5 篇：Symfony 服务容器：使用 XML 或 YAML 文件描述服务][4]
    
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
* **`dumper`** 译作 **`转存器`**     
* **`loader`** 译作 **`加载器`**     
  

上一篇文章 [Symfony 服务容器：使用建造者创建服务]() 带领大家学习了使用 **`spServiceContainerBuilder`** 类描述待创建的服务功能。今天，我们将学习如何使用 loader 和 dumper 结合 XML 或 YAML 文件描述待创建服务。

SVN 版本库有更新，如果您之前有检出版本库，请更新。如果还没有检出可到    [http://svn.symfony-project.com/components/dependency_injection/trunk/][6]
检出（译注：该版本库已停止维护）。

Symfony 依赖注入组件提供加载服务的辅助类。默认组件包含两种加载器： **`sfServiceContainerLoaderFileXml`** 用于加载 XML 文件； **`sfServiceContainerLoaderFileYaml`** 用于加载 YAML 文件。

在讲解 XML 和 YAML 配置文件使用之前，先来看下 Symfony 提供的另外一个依赖注入组件： **`dumper objects`** 。服务转存器接收一个容器对象并将该对象转换成其它格式。当然，这个组件也可以用于 XML 和 YAML 文件的打包处理。

为了讲解 XML 配置文件使用方法，我们将之前使用 PHP 代码描述服务的定义过程，通过使用 **`sfServiceContainerDumperXml 转存器`** 从 **`container.xml`** 配置进行定义。

下面是之前定义 **`Zend_Mail`** 服务的实现：

```php
<?php
require_once '/PATH/TO/sfServiceContainerAutoloader.php';
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

使用下面的代码将这个服务容器转存为 XML 文件：

```php
$dumper = new sfServiceContainerDumperXml($sc);

file_put_contents('/somewhere/container.xml', $dumper->dump());
```

「转存器」类构造函数第一个参数接受一个服务容器，方法 **`dump()`** 可以将这个服务容器转成其它格式。运行正常的话将会生成类似下方数据的 **`container.xml`** 文件：

```xml
<container xmlns="http://symfony-project.org/2.0/container">
foo</parameter>
bar</parameter>
Zend_Mail</parameter>
  </parameters>
  <services>
    <service id="mail.transport" class="Zend_Mail_Transport_Smtp" shared="false">
      <argument>smtp.gmail.com</argument>
      <argument type="collection">
        <argument key="auth">login</argument>
        <argument key="username">%mailer.username%</argument>
        <argument key="password">%mailer.password%</argument>
        <argument key="ssl">ssl</argument>
        <argument key="port">465</argument>
      </argument>
    </service>
    <service id="mailer" class="%mailer.class%">
      <call method="setDefaultTransport">
        <argument type="service" id="mail.transport">
      </argument></call>
    </service>
  </services>
</container>
```

XML格式支持匿名服务。匿名服务无需定义服务名称，可直接在使用的上下文环境中定义。当某个服务仅在某个作用域范围内使用时，使用匿名服务会非常方便：

```xml
<service id="mailer" class="%mailer.class%">
   <call method="setDefaultTransport">
     <argument type="service">
       <service class="Zend_Mail_Transport_Smtp">
         <argument>smtp.gmail.com</argument>
         <argument type="collection">
           <argument key="auth">login</argument>
           <argument key="username">%mailer .username%</argument>
           <argument key="password">%mailer .password%</argument>
           <argument key="ssl">ssl</argument>
           <argument key="port">465 </argument>
         </argument>
       </service>
     </argument>
   </call>
 </service>
```

使用这个 XML 配置文件也非常简单，仅需一个 XML 服务加载类即可完成：

```php
require_once '/PATH/TO/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();

$sc = new sfServiceContainerBuilder();

$loader = new sfServiceContainerLoaderFileXml($sc);
$loader->load('/somewhere/container.xml');
```

类似于转存器，「加载器」的构造函数的第一个参数同为一个服务容器，「加载器」的 **`load()`** 方法能够从文件中读取配置并完成将服务向「服务容器」的注册功能。如此便可以正常使用服务容器了。

如果将 XML 转存器替换为 **`sfServiceContainerDumperYaml`** 类，则会以 YAML 文件生成配置文件：

```php
require_once '/PATH/TO/sfYaml.php';

$dumper = new sfServiceContainerDumperYaml($sc);

file_put_contents('/somewhere/container.yml', $dumper->dump());
```

上面的代码仅在首次加载 **`sfYAML`** 组件（    [http://svn.symfony-project.com/components/yaml/trunk/）时才能正常处理，因为它是服务容器加载器和转存器必要的依赖][7]
。

生成的 YAML 文件内容如下：

```yaml
parameters:
  mailer.username: foo
  mailer.password: bar
  mailer.class:    Zend_Mail

services:
  mail.transport:
    class:     Zend_Mail_Transport_Smtp
    arguments: [smtp.gmail.com, { auth: login, username: %mailer.username%, password: %mailer.password%, ssl: ssl, port: 465 }]
    shared:    false
  mailer:
    class: %mailer.class%
    calls:
      - [setDefaultTransport, [@mail.transport]]
```

但使用 XML 配置比 YAML 配置有更多优势：


* 当 XML 文件被载入时，会使用内置的 **`services.xsd`** 文件进行校验；    
* IDE 可自动补全 XML 文件；
* XML 文件相比 YAML 文件效率更高；
* XML 格式无其它扩展依赖（YAML 格式依赖于 sfYAML 组件）。
  

当然，你也可以一起使用这些加载器和转存器，将某种格式文件转存为另外一种：

```php
// Convert an XML container service definitions file to a YAML one
$sc = new sfServiceContainerBuilder();

$loader = new sfServiceContainerLoaderFileXml($sc);
$loader->load('/somewhere/container.xml');

$dumper = new sfServiceContainerDumperYaml($sc);
file_put_contents('/somewhere/container.yml', $dumper->dump());
```

简短截说，这里不会列出所有 YAML 和 XML 格式的所有可能性。当然，你可以很容易学会如何使用这些转存器和加载器。

使用 YAML 或 XML 配置文件，可以让我们能够使用 GUI 工具创建服务。同时，也给我们带来更多乐趣。

其一、也是最重要的一个功能就是提供引入资源的能力。一个资源可以是任何一种配置文件：

```xml
<container xmlns="http://symfony-project.org/2.0/container">
  <imports>
    <import resource="default.xml">
  </import></imports>
</parameters>
  <services>

  </services>
</container>
```

imports节点所配置的文件需要在其它配置节点之前引入。默认，会从当前文件目录查找这个文件并引入，你也可以通过「加载器」的第二个参数设置文件查找目录：

```php
$loader = new sfServiceContainerLoaderFileXml($sc, array('/another/path'));
$loader->load('/somewhere/container.xml');
```

甚至，可以在 XML 配置中，定义 YAML 加载器及 YAML 配置文件名：

```xml
<container xmlns="http://symfony-project.org/2.0/container">
  <imports>
    <import resource="default.yml" class="sfServiceContainerLoaderFileYaml">
  </import></imports>
</container>
```

反过来也一样：

```yaml
imports:
  - { resource: default.xml, class: sfServiceContainerLoaderFileXml }
```

imports提供一种灵活的方式管理服务定义文件。此外它提供了复用的可能。继续我们之前说到的「会话」功能。当在测试环境下，会话存储可能是一个模拟对象；相反，当使用负载均衡需要才多台 Web 服务器里存储会话数据，可能会使用类似 MySQL 数据库进行存储。此时，就需要一种基于配置的解决方案，并依据不同开发环境导入所需配置：

```xml
<container xmlns="http://symfony-project.org/2.0/container">
sfSessionStorage</parameter>
  </parameters>  
</container>

<container xmlns="http://symfony-project.org/2.0/container">
  <imports>
    <import resource="session.xml">
  </import></imports>

sfSessionTestStorage</parameter>
  </parameters>
</container>

<container xmlns="http://symfony-project.org/2.0/container">
  <imports>
    <import resource="session.xml">
  </import></imports>

sfMySQLSessionStorage</parameter>
  </parameters>
</container>
```

使用时也异常简单：

```php
$loader = new sfServiceContainerLoaderFileXml($sc, array(
  '/framework/config/default/',
  '/project/config/',
));
$loader->load('/somewhere/session_'.$environment.'.xml');
```

也许有的朋友在面对 XML 配置文件时会留下伤心的泪水，因为 XML 文件也许是世上最难以阅读的数据格式。有 Symfony 开发经验的朋友或许已经能够轻松编写 YAML 格式配置文件。更高级一些，我们还可以将服务定义从一个文件中分离出来。我们可以将服务定义在 **`services.xml`** 文件中，并将它所需的参数定义到 **`parameters.xml`** 文件内。或者，在 **`parameters.yml`** 文件中定义所需的参数配置。此外，我们还提供一个内置的 INI 文件加载器，它能够从标准 INI 文件读取配置参数：

```xml
<container xmlns="http://symfony-project.org/2.0/container">
  <imports>
    <import resource="config.ini" class="sfServiceContainerLoaderFileIni">
  </import></imports>
</container>
```

以上示例仅涉及「加载器」和「转存器」基本使用，但我希望您已经了解到 XML 和 YAML 配置文件的强大。对于哪些对服务容器及需要加载太多配置文件的性能持怀疑态度的开发者，下一篇文章或许会让他们改变自己的观点。由于下一篇文章是系列文章的终章，我还将讨论服务依赖可视化相关内容。

原文：    [http://fabien.potencier.org/symfony-service-container-using-xml-or-yaml-to-describe-services.html][8]


[0]: http://blog.phpzendo.com/?p=313
[1]: http://blog.phpzendo.com/?p=318
[2]: http://blog.phpzendo.com/?p=321
[3]: http://blog.phpzendo.com/?p=334
[4]: http://blog.phpzendo.com/?p=338
[5]: http://fabien.potencier.org/symfony-service-container-the-need-for-speed.html
[6]: http://svn.symfony-project.com/components/dependency_injection/trunk/
[7]: http://svn.symfony-project.com/components/yaml/trunk/%EF%BC%89%E6%97%B6%E6%89%8D%E8%83%BD%E6%AD%A3%E5%B8%B8%E5%A4%84%E7%90%86%EF%BC%8C%E5%9B%A0%E4%B8%BA%E5%AE%83%E6%98%AF%E6%9C%8D%E5%8A%A1%E5%AE%B9%E5%99%A8%E5%8A%A0%E8%BD%BD%E5%99%A8%E5%92%8C%E8%BD%AC%E5%AD%98%E5%99%A8%E5%BF%85%E8%A6%81%E7%9A%84%E4%BE%9D%E8%B5%96
[8]: http://fabien.potencier.org/symfony-service-container-using-xml-or-yaml-to-describe-services.html