# PHP 设计模式系列 —— 依赖注入模式（Dependency Injection）

 Posted on [2015年12月24日2015年12月24日][0] by [学院君][1]

### **1、模式定义**

[依赖注入][2]（Dependency Injection）是[控制反转][3]（Inversion of Control）的一种实现方式。

我们先来看看什么是控制反转。

当调用者需要被调用者的协助时，在传统的程序设计过程中，通常由调用者来创建被调用者的实例，但在这里，创建被调用者的工作不再由调用者来完成，而是将被调用者的创建移到调用者的外部，从而反转被调用者的创建，消除了调用者对被调用者创建的控制，因此称为控制反转。

要实现控制反转，通常的解决方案是将创建被调用者实例的工作交由 IoC 容器来完成，然后在调用者中注入被调用者（通过构造器/方法注入实现），这样我们就实现了调用者与被调用者的解耦，该过程被称为依赖注入。

依赖注入不是目的，它是一系列工具和手段，最终的目的是帮助我们开发出松散耦合（loose coupled）、可维护、可测试的代码和程序。这条原则的做法是大家熟知的[面向接口][4]，或者说是面向抽象编程。

### **2、UML 类图**

![Dependency-Injection-UML][5]

### **3、示例代码**

在本例中，我们在 Connection 类（调用者）的构造方法中依赖注入 Parameters 接口的实现类（被调用者），如果不使用依赖注入的话，则必须在 Connection 中创建该接口的实现类实例，这就形成紧耦合代码，如果我们要切换成该接口的其它实现类则必须要修改代码，这对到测试和扩展而言都是极为不利的。

#### **AbstractConfig.php**

```php
<?php

namespace DesignPatterns\Structural\DependencyInjection;

/**
 * AbstractConfig类
 */
abstract class AbstractConfig
{
    /**
     * @var Storage of data
     */
    protected $storage;

    public function __construct($storage)
    {
        $this->storage = $storage;
    }
}
```

#### **Parameters.php**

```php
<?php

namespace DesignPatterns\Structural\DependencyInjection;

/**
 * Parameters接口
 */
interface Parameters
{
    /**
     * 获取参数
     *
     * @param string|int $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * 设置参数
     *
     * @param string|int $key
     * @param mixed      $value
     */
    public function set($key, $value);
}
```

#### **ArrayConfig.php**

```php
<?php

namespace DesignPatterns\Structural\DependencyInjection;

/**
 * ArrayConfig类
 *
 * 使用数组作为数据源
 */
class ArrayConfig extends AbstractConfig implements Parameters
{
    /**
     * 获取参数
     *
     * @param string|int $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->storage[$key])) {
            return $this->storage[$key];
        }
        return $default;
    }

    /**
     * 设置参数
     *
     * @param string|int $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->storage[$key] = $value;
    }
}
```

#### **Connection.php**

```php
<?php

namespace DesignPatterns\Structural\DependencyInjection;

/**
 * Connection类
 */
class Connection
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Currently connected host
     */
    protected $host;

    /**
     * @param Parameters $config
     */
    public function __construct(Parameters $config)
    {
        $this->configuration = $config;
    }

    /**
     * connection using the injected config
     */
    public function connect()
    {
        $host = $this->configuration->get('host');
        // connection to host, authentication etc...

        //if connected
        $this->host = $host;
    }

    /*
     * 获取当前连接的主机
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
}
```

### **4、测试代码**

#### **
**

```php
<?php

namespace DesignPatterns\Structural\DependencyInjection\Tests;

use DesignPatterns\Structural\DependencyInjection\ArrayConfig;
use DesignPatterns\Structural\DependencyInjection\Connection;

class DependencyInjectionTest extends \PHPUnit_Framework_TestCase
{
    protected $config;
    protected $source;

    public function setUp()
    {
        $this->source = include 'config.php';
        $this->config = new ArrayConfig($this->source);
    }

    public function testDependencyInjection()
    {
        $connection = new Connection($this->config);
        $connection->connect();
        $this->assertEquals($this->source['host'], $connection->getHost());
    }
}
```

#### **Tests/config.php**

```php
    <?php
    
    return array('host' => 'github.com');
```

### **5、总结**

依赖注入模式需要在调用者外部完成容器创建以及容器中接口与实现类的运行时绑定工作，在 Laravel 中该容器就是服务容器，而接口与实现类的运行时绑定则在服务提供者中完成。此外，除了在调用者的构造函数中进行依赖注入外，还可以通过在调用者的方法中进行依赖注入。

[0]: http://laravelacademy.org/post/2792.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e4%be%9d%e8%b5%96%e6%b3%a8%e5%85%a5
[3]: http://laravelacademy.org/tags/%e6%8e%a7%e5%88%b6%e5%8f%8d%e8%bd%ac
[4]: http://laravelacademy.org/tags/%e9%9d%a2%e5%90%91%e6%8e%a5%e5%8f%a3
[5]: ../img/Dependency-Injection-UML.png
[6]: http://laravelacademy.org/tags/php