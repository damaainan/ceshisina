# PHP 设计模式系列 —— 静态工厂模式（Static Factory）

 Posted on [2015年12月17日][0] by [学院君][1]

### **1、模式定义**

与简单工厂类似，该模式用于创建一组相关或依赖的对象，不同之处在于[静态工厂模式][2]使用一个静态方法来创建所有类型的对象，该静态方法通常是 factory 或 build。

### **2、UML类图**

![静态工厂模式类图][3]

### **3、示例代码**

#### **StaticFactory.php**

```php
<?php

namespace DesignPatterns\Creational\StaticFactory;

class StaticFactory
{
    /**
     * 通过传入参数创建相应对象实例
     *
     * @param string $type
     *
     * @static
     *
     * @throws \InvalidArgumentException
     * @return FormatterInterface
     */
    public static function factory($type)
    {
        $className = __NAMESPACE__ . '\Format' . ucfirst($type);

        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Missing format class.');
        }

        return new $className();
    }
}
```

#### **FormatterInterface.php**

```php
<?php

namespace DesignPatterns\Creational\StaticFactory;

/**
 * FormatterInterface接口
 */
interface FormatterInterface
{
}
```

#### **FormatString.php**

```php
<?php

namespace DesignPatterns\Creational\StaticFactory;

/**
 * FormatNumber类
 */
class FormatNumber implements FormatterInterface
{
}
```

### **4、测试代码**

#### **Tests/StaticFactoryTest.php**

```php
<?php

namespace DesignPatterns\Creational\StaticFactory\Tests;

use DesignPatterns\Creational\StaticFactory\StaticFactory;

/**
 * 测试静态工厂模式
 *
 */
class StaticFactoryTest extends \PHPUnit\Framework\TestCase
{

    public function getTypeList()
    {
        return array(
            array('string'),
            array('number')
        );
    }

    /**
     * @dataProvider getTypeList
     */
    public function testCreation($type)
    {
        $obj = StaticFactory::factory($type);
        $this->assertInstanceOf('DesignPatterns\Creational\StaticFactory\FormatterInterface', $obj);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testException()
    {
        StaticFactory::factory("");
    }
}
```

[0]: http://laravelacademy.org/post/2647.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e9%9d%99%e6%80%81%e5%b7%a5%e5%8e%82%e6%a8%a1%e5%bc%8f
[3]: ../img/Static-Factory.png
[4]: http://laravelacademy.org/tags/php