## PHP程序员如何理解IoC/DI
<font face=黑体>

### 思想

> 思想是解决问题的根本  
> 思想必须转换成习惯  
> 构建一套完整的思想体系是开发能力成熟的标志  
> ——《简单之美》（前言）

.

> “成功的软件项目就是那些提交产物达到或超出客户的预期的项目，而且开发过程符合时间和费用上的要求，结果在面对变化和调整时有弹性。”  
> ——《面向对象分析与设计》（第3版）P.236

### 术语介绍

——引用《Spring 2.0 技术手册》林信良

#### 非侵入性 No intrusive

* 框架的目标之一是非侵入性（No intrusive）
* 组件可以直接拿到另一个应用或框架之中使用
* 增加组件的可重用性（Reusability）

#### 容器（Container）

* 管理对象的生成、资源取得、销毁等生命周期
* 建立对象与对象之间的依赖关系
* 启动容器后，所有对象直接取用，不用编写任何一行代码来产生对象，或是建立对象之间的依赖关系。

#### IoC

* 控制反转 Inversion of Control
* 依赖关系的转移
* 依赖抽象而非实践

#### DI

* 依赖注入 Dependency Injection
* 不必自己在代码中维护对象的依赖
* 容器自动根据配置，将依赖注入指定对象

#### AOP

* Aspect-oriented programming
* 面向方面编程
* 无需修改任何一行程序代码，将功能加入至原先的应用程序中，也可以在不修改任何程序的情况下移除。

#### 分层

> 表现层：提供服务，显示信息。  
> 领域层：逻辑，系统中真正的核心。  
> 数据源层：与数据库、消息系统、事务管理器及其它软件包通信。  
> ——《企业应用架构模式》P.14

### 代码演示IoC

假设应用程序有储存需求，若直接在高层的应用程序中调用低层模块API，导致应用程序对低层模块产生依赖。

```php
    /**
     * 高层
     */
    class Business
    {
        private $writer;
    
        public function __construct()
        {
            $this->writer = new FloppyWriter();
        }
    
        public function save()
        {
            $this->writer->saveToFloppy();
        }
    }
    
    /**
     * 低层，软盘存储
     */
    class FloppyWriter
    {
        public function saveToFloppy()
        {
            echo __METHOD__;
        }
    }
    
    $biz = new Business();
    $biz->save(); // FloppyWriter::saveToFloppy
```

假设程序要移植到另一个平台，而该平台使用USB磁盘作为存储介质，则这个程序无法直接重用，必须加以修改才行。本例由于低层变化导致高层也跟着变化，不好的设计。

正如前方提到的

> 控制反转 Inversion of Control  
> 依赖关系的转移  
> 依赖抽象而非实践

程序不应该依赖于具体的实现，而是要依赖抽像的接口。请看代码演示

```php
    /**
     * 接口
     */
    interface IDeviceWriter
    {
        public function saveToDevice();
    }
    
    /**
     * 高层
     */
    class Business
    {
        /**
         * @var IDeviceWriter
         */
        private $writer;
    
        /**
         * @param IDeviceWriter $writer
         */
        public function setWriter($writer)
        {
            $this->writer = $writer;
        }
    
        public function save()
        {
            $this->writer->saveToDevice();
        }
    }
    
    /**
     * 低层，软盘存储
     */
    class FloppyWriter implements IDeviceWriter
    {
    
        public function saveToDevice()
        {
            echo __METHOD__;
        }
    }
    
    /**
     * 低层，USB盘存储
     */
    class UsbDiskWriter implements IDeviceWriter
    {
    
        public function saveToDevice()
        {
            echo __METHOD__;
        }
    }
    
    $biz = new Business();
    $biz->setWriter(new UsbDiskWriter());
    $biz->save(); // UsbDiskWriter::saveToDevice
    
    $biz->setWriter(new FloppyWriter());
    $biz->save(); // FloppyWriter::saveToDevice

```

控制权从实际的FloppyWriter转移到了抽象的IDeviceWriter接口上，让Business依赖于IDeviceWriter接口，且FloppyWriter、UsbDiskWriter也依赖于IDeviceWriter接口。

这就是IoC，面对变化，高层不用修改一行代码，不再依赖低层，而是依赖注入，这就引出了DI。

比较实用的注入方式有三种：

* Setter injection 使用setter方法
* Constructor injection 使用构造函数
* Property Injection 直接设置属性

事实上不管有多少种方法，都是IoC思想的实现而已，上面的代码演示的是Setter方式的注入。

### 依赖注入容器 Dependency Injection Container

* 管理应用程序中的『全局』对象（包括实例化、处理依赖关系）。
* 可以延时加载对象（仅用到时才创建对象）。
* 促进编写可重用、可测试和松耦合的代码。

理解了IoC和DI之后，就引发了另一个问题，引用Phalcon文档描述如下：

如果这个组件有很多依赖， 我们需要创建多个参数的setter方法​​来传递依赖关系，或者建立一个多个参数的构造函数来传递它们，另外在使用组件前还要每次都创建依赖，这让我们的代码像这样不易维护

```php
    //创建依赖实例或从注册表中查找
    $connection = new Connection();
    $session = new Session();
    $fileSystem = new FileSystem();
    $filter = new Filter();
    $selector = new Selector();
    
    //把实例作为参数传递给构造函数
    $some = new SomeComponent($connection, $session, $fileSystem, $filter, $selector);
    
    // ... 或者使用setter
    
    $some->setConnection($connection);
    $some->setSession($session);
    $some->setFileSystem($fileSystem);
    $some->setFilter($filter);
    $some->setSelector($selector);
```

假设我们必须在应用的不同地方使用和创建这些对象。如果当你永远不需要任何依赖实例时，你需要去删掉构造函数的参数，或者去删掉注入的setter。为了解决这样的问题，我们再次回到全局注册表创建组件。不管怎么样，在创建对象之前，它增加了一个新的抽象层：

```php
    class SomeComponent
    {
    
        // ...
    
        /**
         * Define a factory method to create SomeComponent instances injecting its dependencies
         */
        public static function factory()
        {
    
            $connection = new Connection();
            $session = new Session();
            $fileSystem = new FileSystem();
            $filter = new Filter();
            $selector = new Selector();
    
            return new self($connection, $session, $fileSystem, $filter, $selector);
        }
    
    }
```

瞬间，我们又回到刚刚开始的问题了，我们再次创建依赖实例在组件内部！我们可以继续前进，找出一个每次能奏效的方法去解决这个问题。但似乎一次又一次，我们又回到了不实用的例子中。

一个实用和优雅的解决方法，是为依赖实例提供一个容器。这个容器担任全局的注册表，就像我们刚才看到的那样。使用依赖实例的容器作为一个桥梁来获取依赖实例，使我们能够降低我们的组件的复杂性：

```php
    class SomeComponent
    {
    
        protected $_di;
    
        public function __construct($di)
        {
            $this->_di = $di;
        }
    
        public function someDbTask()
        {
    
            // 获得数据库连接实例
            // 总是返回一个新的连接
            $connection = $this->_di->get('db');
    
        }
    
        public function someOtherDbTask()
        {
    
            // 获得共享连接实例
            // 每次请求都返回相同的连接实例
            $connection = $this->_di->getShared('db');
    
            // 这个方法也需要一个输入过滤的依赖服务
            $filter = $this->_di->get('filter');
    
        }
    
    }
    
    $di = new Phalcon\DI();
    
    //在容器中注册一个db服务
    $di->set('db', function() {
        return new Connection(array(
            "host" => "localhost",
            "username" => "root",
            "password" => "secret",
            "dbname" => "invo"
        ));
    });
    
    //在容器中注册一个filter服务
    $di->set('filter', function() {
        return new Filter();
    });
    
    //在容器中注册一个session服务
    $di->set('session', function() {
        return new Session();
    });
    
    //把传递服务的容器作为唯一参数传递给组件
    $some = new SomeComponent($di);
    
    $some->someTask();
```

这个组件现在可以很简单的获取到它所需要的服务，服务采用延迟加载的方式，只有在需要使用的时候才初始化，这也节省了服务器资源。这个组件现在是高度解耦。例如，我们可以替换掉创建连接的方式，它们的行为或它们的任何其他方面，也不会影响该组件。

### 参考文章

* [PHP程序员如何理解依赖注入容器(dependency injection container)][0]
* [http://docs.phalconphp.com/zh/latest/reference/di.html][1]
* [What is Dependency Injection? Fabien Potencier][2]
* [Inversion of Control Containers and the Dependency Injection pattern][3] by Martin Fowler

### 补充

很多代码背后，都是某种哲学思想的体现。

以下引用《面向模式的软件架构》卷1模式系统第六章模式与软件架构

#### 软件架构支持技术（开发软件时要遵循的基本原则）

1. 抽象
1. 封装
1. 信息隐藏
1. 分离关注点
1. 耦合与内聚
1. 充分、完整、简单
1. 策略与实现分离
    * 策略组件负责上下文相关决策，解读信息的语义和含义，将众多不同结果合并或选择参数值
    * 实现组件负责执行定义完整的算法，不需要作出与上下文相关的决策。上下文和解释是外部的，通常由传递给组件的参数提供。

1. 接口与实现分离
    * 接口部分定义了组件提供的功能以及如何使用该组件。组件的客户端可以访问该接口。
    * 实现部分包含实现组件提供的功能的实际代码，还可能包含仅供组件内部使用的函数和数据结构。组件的客户端不能访问其实现部分。

1. 单个引用点
    * 软件系统中的任何元素都应只声明和定义一次，避免不一致性问题。  

1. 分而治之

#### 软件架构的非功能特性

1. 可修改性
    * 可维护性
    * 可扩展性
    * 重组
    * 可移植性

1. 互操作性
    * 与其它系统或环境交互

1. 效率
1. 可靠性
    * 容错：发生错误时确保行为正确并自行修复
    * 健壮性：对应用程序进行保护，抵御错误的使用方式和无效输入，确保发生意外错误时处于指定状态。

1. 可测试性
1. 可重用性
    * 通过重用开发软件
    * 开发软件时考虑重用
</font>

[0]: /a/1190000002424023
[1]: http://docs.phalconphp.com/zh/latest/reference/di.html
[2]: http://fabien.potencier.org/article/11/what-is-dependency-injection
[3]: http://martinfowler.com/articles/injection.html