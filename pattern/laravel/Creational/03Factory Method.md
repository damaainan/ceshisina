# PHP 设计模式系列 —— 工厂方法模式（Factory Method）

 Posted on [2015年12月11日][0] by [学院君][1]

### **1、模式定义**

定义一个创建对象的接口，但是让子类去实例化具体类。[工厂方法模式][2]让类的实例化延迟到子类中。

### **2、问题引出**

框架需要为多个应用提供标准化的架构模型，同时也要允许独立应用定义自己的域对象并对其进行实例化。

### **3、解决办法**

工厂方法以模板方法的方式创建对象来解决上述问题。父类定义所有标准通用行为，然后将创建细节放到子类中实现并输出给客户端。

人们通常使用工厂模式作为创建对象的标准方式，但是在这些情况下不必使用工厂方法：实例化的类永远不会改变；或者实例化发生在子类可以轻易覆盖的操作中（比如初始化）。

### **4、UML类图**

![Factory-Method-UML][3]

### **5、示例代码**

#### **FactoryMethod.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod;

/**
 * 工厂方法抽象类
 */
abstract class FactoryMethod
{

    const CHEAP = 1;
    const FAST = 2;

    /**
     * 子类必须实现该方法
     *
     * @param string $type a generic type
     *
     * @return VehicleInterface a new vehicle
     */
    abstract protected function createVehicle($type);

    /**
     * 创建新的车辆
     *
     * @param int $type
     *
     * @return VehicleInterface a new vehicle
     */
    public function create($type)
    {
        $obj = $this->createVehicle($type);
        $obj->setColor("#f00");

        return $obj;
    }
}
```
#### **ItalianFactory.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod;

/**
 * ItalianFactory是意大利的造车厂
 */
class ItalianFactory extends FactoryMethod
{
    /**
     * {@inheritdoc}
     */
    protected function createVehicle($type)
    {
        switch ($type) {
            case parent::CHEAP:
                return new Bicycle();
                break;
            case parent::FAST:
                return new Ferrari();
                break;
            default:
                throw new \InvalidArgumentException("$type is not a valid vehicle");
        }
    }
}
```
#### **GermanFactory.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod;

/**
 * GermanFactory是德国的造车厂
 */
class GermanFactory extends FactoryMethod
{
    /**
     * {@inheritdoc}
     */
    protected function createVehicle($type)
    {
        switch ($type) {
            case parent::CHEAP:
                return new Bicycle();
                break;
            case parent::FAST:
                $obj = new Porsche();
                //因为我们已经知道是什么对象所以可以调用具体方法
                $obj->addTuningAMG();

                return $obj;
                break;
            default:
                throw new \InvalidArgumentException("$type is not a valid vehicle");
        }
    }
}
```
#### **VehicleInterface.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod;

/**
 * VehicleInterface是车辆接口
 */
interface VehicleInterface
{
    /**
     * 设置车的颜色
     *
     * @param string $rgb
     */
    public function setColor($rgb);
}
```
#### **Porsche.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod;

/**
 * Porsche（保时捷）
 */
class Porsche implements VehicleInterface
{
    /**
     * @var string
     */
    protected $color;

    /**
     * @param string $rgb
     */
    public function setColor($rgb)
    {
        $this->color = $rgb;
    }

    /**
     * 尽管只有奔驰汽车挂有AMG品牌，这里我们提供一个空方法仅作代码示例
     */
    public function addTuningAMG()
    {
    }
}
```
#### **Bicycle.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod;

/**
 * Bicycle（自行车）
 */
class Bicycle implements VehicleInterface
{
    /**
     * @var string
     */
    protected $color;

    /**
     * 设置自行车的颜色
     *
     * @param string $rgb
     */
    public function setColor($rgb)
    {
        $this->color = $rgb;
    }
}
```
#### **Ferrari.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod;

/**
 * Ferrari（法拉利）
 */
class Ferrari implements VehicleInterface
{
    /**
     * @var string
     */
    protected $color;

    /**
     * @param string $rgb
     */
    public function setColor($rgb)
    {
        $this->color = $rgb;
    }
}
```

### **6、测试代码**

**Tests/FactoryMethodTest.php**

```php
<?php

namespace DesignPatterns\Creational\FactoryMethod\Tests;

use DesignPatterns\Creational\FactoryMethod\FactoryMethod;
use DesignPatterns\Creational\FactoryMethod\GermanFactory;
use DesignPatterns\Creational\FactoryMethod\ItalianFactory;

/**
 * FactoryMethodTest用于测试工厂方法模式
 */
class FactoryMethodTest extends \PHPUnit\Framework\TestCase
{

    protected $type = array(
        FactoryMethod::CHEAP,
        FactoryMethod::FAST
    );

    public function getShop()
    {
        return array(
            array(new GermanFactory()),
            array(new ItalianFactory())
        );
    }

    /**
     * @dataProvider getShop
     */
    public function testCreation(FactoryMethod $shop)
    {
        // 该方法扮演客户端角色，我们不关心什么工厂，我们只知道可以可以用它来造车
        foreach ($this->type as $oneType) {
            $vehicle = $shop->create($oneType);
            $this->assertInstanceOf('DesignPatterns\Creational\FactoryMethod\VehicleInterface', $vehicle);
        }
    }

    /**
     * @dataProvider getShop
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage spaceship is not a valid vehicle
     */
    public function testUnknownType(FactoryMethod $shop)
    {
        $shop->create('spaceship');
    }
}

```

### **7、总结**

工厂方法模式和[抽象工厂模式][5]有点类似，但也有不同。

工厂方法针对每一种产品提供一个工厂类，通过不同的工厂实例来创建不同的产品实例，在同一等级结构中，支持增加任意产品。

抽象工厂是应对产品族概念的，比如说，每个汽车公司可能要同时生产轿车，货车，客车，那么每一个工厂都要有创建轿车，货车和客车的方法。应对产品族概念而生，增加新的产品线很容易，但是无法增加新的产品。

[0]: http://laravelacademy.org/post/2506.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e5%b7%a5%e5%8e%82%e6%96%b9%e6%b3%95%e6%a8%a1%e5%bc%8f
[3]: ../img/Factory-Method-UML.png
[4]: http://laravelacademy.org/tags/php
[5]: http://laravelacademy.org/tags/%e6%8a%bd%e8%b1%a1%e5%b7%a5%e5%8e%82%e6%a8%a1%e5%bc%8f