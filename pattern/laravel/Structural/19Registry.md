# PHP 设计模式系列 —— 注册模式（Registry）

 Posted on [2015年12月30日][0] by [学院君][1]

### **1、模式定义**

[注册模式][2]（[Registry][3]）也叫做注册树模式，注册器模式。注册模式为应用中经常使用的对象创建一个中央存储器来存放这些对象 —— 通常通过一个只包含静态方法的抽象类来实现（或者通过单例模式）。

### **2、UML类图**

![Registry-Design-Pattern-UML][4]

### **3、示例代码**

#### **Registry.php**

```php
<?php

namespace DesignPatterns\Structural\Registry;

/**
 * class Registry
 */
abstract class Registry
{
    const LOGGER = 'logger';

    /**
     * @var array
     */
    protected static $storedValues = array();

    /**
     * sets a value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @static
     * @return void
     */
    public static function set($key, $value)
    {
        self::$storedValues[$key] = $value;
    }

    /**
     * gets a value from the registry
     *
     * @param string $key
     *
     * @static
     * @return mixed
     */
    public static function get($key)
    {
        return self::$storedValues[$key];
    }

    // typically there would be methods to check if a key has already been registered and so on ...
}
```


### **4、测试代码**

#### **Tests/RegistryTest.php**

```php
<?php

namespace DesignPatterns\Structural\Registry\Tests;

use DesignPatterns\Structural\Registry\Registry;

class RegistryTest extends \PHPUnit_Framework_TestCase
{

    public function testSetAndGetLogger()
    {
        Registry::set(Registry::LOGGER, new \StdClass());

        $logger = Registry::get(Registry::LOGGER);
        $this->assertInstanceOf('StdClass', $logger);
    }
}
```

[0]: http://laravelacademy.org/post/2850.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e6%b3%a8%e5%86%8c%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/registry
[4]: ../img/Registry-Design-Pattern-UML.png
[5]: http://laravelacademy.org/tags/php