# PHP 设计模式系列 —— 简单工厂模式（Simple Factory）

 Posted on [2015年12月17日][0] by [学院君][1]

### **1、模式定义**

简单工厂的作用是实例化对象，而不需要客户了解这个对象属于哪个具体的子类。简单工厂实例化的类具有相同的接口或者基类，在子类比较固定并不需要扩展时，可以使用简单工厂。

### **2、UML类图**

![简单工厂模式类图][2]

### **3、实例代码**

#### **ConcreteFactory.php**

```php
<?php

namespace DesignPatterns\Creational\SimpleFactory;

/**
 * ConcreteFactory类
 */
class ConcreteFactory
{
    /**
     * @var array
     */
    protected $typeList;

    /**
     * 你可以在这里注入自己的车子类型
     */
    public function __construct()
    {
        $this->typeList = array(
            'bicycle' => __NAMESPACE__ . '\Bicycle',
            'other' => __NAMESPACE__ . '\Scooter'
        );
    }

    /**
     * 创建车子
     *
     * @param string $type a known type key
     *
     * @return VehicleInterface a new instance of VehicleInterface
     * @throws \InvalidArgumentException
     */
    public function createVehicle($type)
    {
        if (!array_key_exists($type, $this->typeList)) {
            throw new \InvalidArgumentException("$type is not valid vehicle");
        }
        $className = $this->typeList[$type];

        return new $className();
    }
}
```

#### **VehicleInterface.php**

```php
<?php

namespace DesignPatterns\Creational\SimpleFactory;

/**
 * VehicleInterface 是车子接口
 */
interface VehicleInterface
{
    /**
     * @param mixed $destination
     *
     * @return mixed
     */
    public function driveTo($destination);
}
```

#### **Bicycle.php**

```php
<?php

namespace DesignPatterns\Creational\SimpleFactory;

/**
 * 自行车类
 */
class Bicycle implements VehicleInterface
{
    /**
     * @param mixed $destination
     *
     * @return mixed|void
     */
    public function driveTo($destination)
    {
    }
}
```

#### **Scooter.php**

```php
<?php

namespace DesignPatterns\Creational\SimpleFactory;

/**
 * 摩托车类
 */
class Scooter implements VehicleInterface
{
    /**
     * @param mixed $destination
     */
    public function driveTo($destination)
    {
    }
}
```

### **4、测试代码**

#### **Tests/SimpleFactoryTest.php**

```php
<?php

namespace DesignPatterns\Creational\SimpleFactory\Tests;

use DesignPatterns\Creational\SimpleFactory\ConcreteFactory;

/**
 * SimpleFactoryTest 用于测试[简单工厂模式][3]
 */
class SimpleFactoryTest extends \PHPUnit\Framework\TestCase
{

    protected $factory;

    protected function setUp()
    {
        $this->factory = new ConcreteFactory();
    }

    public function getType()
    {
        return array(
            array('bicycle'),
            array('other')
        );
    }

    /**
     * @dataProvider getType
     */
    public function testCreation($type)
    {
        $obj = $this->factory->createVehicle($type);
        $this->assertInstanceOf('DesignPatterns\Creational\SimpleFactory\VehicleInterface', $obj);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadType()
    {
        $this->factory->createVehicle('car');
    }
}
```
### **5、总结**

采用简单工厂的优点是可以使用户根据参数获得对应的类实例，避免了直接实例化类，降低了耦合性；缺点是可实例化的类型在编译期间已经被确定，如果增加新类型，则需要修改工厂，不符合OCP（开闭原则）的原则。简单工厂需要知道所有要生成的类型，当子类过多或者子类层次过多时不适合使用。

[0]: http://laravelacademy.org/post/2643.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: ../img/Simple-Factory.png
[3]: http://laravelacademy.org/tags/%e7%ae%80%e5%8d%95%e5%b7%a5%e5%8e%82%e6%a8%a1%e5%bc%8f
[4]: http://laravelacademy.org/tags/php