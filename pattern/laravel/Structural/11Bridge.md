# PHP 设计模式系列 —— 桥梁模式（Bridge）

 Posted on [2015年12月20日2015年12月23日][0] by [学院君][1]

### **1、模式定义**

系统设计中，总是充满了各种变数，这是防不慎防的。比如客户代表可能要求修改某个需求，增加某种功能等等。面对这样那样的变动，你只能去不停的修改设计和代码，并且要开始新的一轮测试……

那采取什么样的方式可以较好的解决变化带给系统的影响？你可以分析变化的种类，将不变的框架使用抽象类定义出来，然后再将变化的内容使用具体的子类来分别实现。这样面向客户的只是一个抽象类，这种方式可以较好的避免为抽象类中现有接口添加新的实现所带来的影响，缩小了变化带来的影响。但是这可能会造成子类数量的爆炸，并且在某些时候不是很灵活。

但是当你各个子类的行为经常发生变化，或者有一定的重复和组合关系时，我们不妨将这些行为提取出来，也采用接口的方式提供出来，然后以组合的方式将服务提供给原来的子类。这样就达到了前端和被使用的后端独立的变化，而且还达到了后端的重用。

其实这就是[桥梁模式][2]的诞生。

桥梁模式（Bridge）也叫做桥接模式，用于将抽象和实现解耦，使得两者可以独立地变化。

桥梁模式完全是为了解决[继承][3]的缺点而提出的[设计模式][4]。在该模式下，实现可以不受抽象的约束，不用再绑定在一个固定的抽象层次上。

### **2、UML类图**

我们以汽车制造厂生产汽车为例，Vehicle 是`抽象生产类`，Motorcycle 和 Car 是`具体实现子类`，制造汽车分为生产和组装两部分完成，这意味着我们要在制造方法 manufacture 中实现生产和组装工作，这里我们将这一`实现过程分离出去成为一个新的接口` Workshop，由该`接口的实现类` Produce 和 Assemble 负责具体生产及组装，从而实现抽象（Vehicle）与实现（Workshop）的分离，让两者可以独立变化而不相互影响：

![bridge-design-pattern][5]

### **3、示例代码**

#### **Workshop.php**

```php
<?php

namespace DesignPatterns\Structural\Bridge;

/**
 * 实现
 */
interface Workshop
{

    public function work();
}
```
#### **Assemble.php**

```php
<?php

namespace DesignPatterns\Structural\Bridge;

/**
 * 具体实现：Assemble
 */
class Assemble implements Workshop
{

    public function work()
    {
        print 'Assembled';
    }
}
```
#### **Produce.php**

```php
<?php

namespace DesignPatterns\Structural\Bridge;

/**
 * 具体实现：Produce
 */
class Produce implements Workshop
{

    public function work()
    {
        print 'Produced ';
    }
}
```

#### **Vehicle.php**

```php
<?php

namespace DesignPatterns\Structural\Bridge;

/**
 * 抽象
 */
abstract class Vehicle
{

    protected $workShop1;
    protected $workShop2;

    protected function __construct(Workshop $workShop1, Workshop $workShop2)
    {
        $this->workShop1 = $workShop1;
        $this->workShop2 = $workShop2;
    }

    public function manufacture()
    {
    }
}
```

#### **Motorcycle.php**

```php
<?php

namespace DesignPatterns\Structural\Bridge;

/**
 * 经过改良的抽象实现：Motorcycle
 */
class Motorcycle extends Vehicle
{

    public function __construct(Workshop $workShop1, Workshop $workShop2)
    {
        parent::__construct($workShop1, $workShop2);
    }

    public function manufacture()
    {
        print 'Motorcycle ';
        $this->workShop1->work();
        $this->workShop2->work();
    }
}
```
#### **Car.php**

```php
<?php

namespace DesignPatterns\Structural\Bridge;

/**
 * 经过改良的抽象实现：Car
 */
class Car extends Vehicle
{

    public function __construct(Workshop $workShop1, Workshop $workShop2)
    {
        parent::__construct($workShop1, $workShop2);
    }

    public function manufacture()
    {
        print 'Car ';
        $this->workShop1->work();
        $this->workShop2->work();
    }
}
```

### **4、测试代码**

#### **Tests/BridgeTest.php**

```php
<?php

namespace DesignPatterns\Structural\Bridge\Tests;

use DesignPatterns\Structural\Bridge\Assemble;
use DesignPatterns\Structural\Bridge\Car;
use DesignPatterns\Structural\Bridge\Motorcycle;
use DesignPatterns\Structural\Bridge\Produce;

class BridgeTest extends \PHPUnit_Framework_TestCase
{

    public function testCar()
    {
        $vehicle = new Car(new Produce(), new Assemble());
        $this->expectOutputString('Car Produced Assembled');
        $vehicle->manufacture();
    }

    public function testMotorcycle()
    {
        $vehicle = new Motorcycle(new Produce(), new Assemble());
        $this->expectOutputString('Motorcycle Produced Assembled');
        $vehicle->manufacture();
    }
}
```

### **5、总结**

系统设计时，发现类的继承有 N 层时，可以考虑使用桥梁模式。使用桥梁模式时主要考虑如何拆分抽象和实现，并不是一涉及继承就要考虑使用该模式。桥梁模式的意图还是对变化的封装，尽量把可能变化的因素封装到最细、最小的逻辑单元中，避免风险扩散。

[0]: http://laravelacademy.org/post/2680.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e6%a1%a5%e6%a2%81%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/%e7%bb%a7%e6%89%bf
[4]: http://laravelacademy.org/tags/%e8%ae%be%e8%ae%a1%e6%a8%a1%e5%bc%8f
[5]: ../img/bridge-design-pattern.png
[6]: http://laravelacademy.org/tags/php