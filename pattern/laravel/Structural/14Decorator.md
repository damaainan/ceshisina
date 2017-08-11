# PHP 设计模式系列 —— 装饰器模式（Decorator）

 Posted on [2015年12月23日2015年12月23日][0] by [学院君][1]

### **1、模式定义**

[装饰器模式][2]能够从一个对象的外部动态地给对象添加功能。

通常给对象添加功能，要么直接修改对象添加相应的功能，要么派生对应的子类来扩展，抑或是使用对象组合的方式。显然，直接修改对应的类这种方式并不可取。在面向对象的设计中，我们也应该尽量使用对象组合，而不是对象继承来扩展和复用功能。装饰器模式就是基于对象组合的方式，可以很灵活的给对象添加所需要的功能。装饰器模式的本质就是动态组合。动态是手段，组合才是目的。

常见的使用示例：Web服务层 —— 为 REST 服务提供 JSON 和 XML 装饰器。

### **2、UML类图**

![decorator-design-pattern][3]

### **3、示例代码**

#### **RendererInterface.php**

```php
<?php

namespace DesignPatterns\Structural\Decorator;

/**
 * RendererInterface接口
 */
interface RendererInterface
{
    /**
     * render data
     *
     * @return mixed
     */
    public function renderData();
}
```

#### **Webservice.php**

```php
<?php

namespace DesignPatterns\Structural\Decorator;

/**
 * Webservice类
 */
class Webservice implements RendererInterface
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function renderData()
    {
        return $this->data;
    }
}
```

#### **Decorator.php**

```php
<?php

namespace DesignPatterns\Structural\Decorator;

/**
 * 装饰器必须实现 RendererInterface 接口, 这是装饰器模式的主要特点，
 * 否则的话就不是装饰器而只是个包裹类
 */

/**
 * Decorator类
 */
abstract class Decorator implements RendererInterface
{
    /**
     * @var RendererInterface
     */
    protected $wrapped;

    /**
     * 必须类型声明装饰组件以便在子类中可以调用renderData()方法
     *
     * @param RendererInterface $wrappable
     */
    public function __construct(RendererInterface $wrappable)
    {
        $this->wrapped = $wrappable;
    }
}
```

#### **RenderInXml.php**

```php
<?php

namespace DesignPatterns\Structural\Decorator;

/**
 * RenderInXml类
 */
class RenderInXml extends Decorator
{
    /**
     * render data as XML
     *
     * @return mixed|string
     */
    public function renderData()
    {
        $output = $this->wrapped->renderData();

        // do some fancy conversion to xml from array ...

        $doc = new \DOMDocument();

        foreach ($output as $key => $val) {
            $doc->appendChild($doc->createElement($key, $val));
        }

        return $doc->saveXML();
    }
}
```

#### **RenderInJson.php**

```php
<?php

namespace DesignPatterns\Structural\Decorator;

/**
 * RenderInJson类
 */
class RenderInJson extends Decorator
{
    /**
     * render data as JSON
     *
     * @return mixed|string
     */
    public function renderData()
    {
        $output = $this->wrapped->renderData();

        return json_encode($output);
    }
}
```

### **4、测试代码**

#### **Tests/DecoratorTest.php**

```php
<?php

namespace DesignPatterns\Structural\Decorator\Tests;

use DesignPatterns\Structural\Decorator;

/**
 * DecoratorTest 用于测试装饰器模式
 */
class DecoratorTest extends \PHPUnit_Framework_TestCase
{

    protected $service;

    protected function setUp()
    {
        $this->service = new Decorator\Webservice(array('foo' => 'bar'));
    }

    public function testJsonDecorator()
    {
        // Wrap service with a JSON decorator for renderers
        $service = new Decorator\RenderInJson($this->service);
        // Our Renderer will now output JSON instead of an array
        $this->assertEquals('{"foo":"bar"}', $service->renderData());
    }

    public function testXmlDecorator()
    {
        // Wrap service with a XML decorator for renderers
        $service = new Decorator\RenderInXml($this->service);
        // Our Renderer will now output XML instead of an array
        $xml = '<?xml version="1.0"?><foo>bar</foo>';
        $this->assertXmlStringEqualsXmlString($xml, $service->renderData());
    }

    /**
     * The first key-point of this pattern :
     */
    public function testDecoratorMustImplementsRenderer()
    {
        $className = 'DesignPatterns\Structural\Decorator\Decorator';
        $interfaceName = 'DesignPatterns\Structural\Decorator\RendererInterface';
        $this->assertTrue(is_subclass_of($className, $interfaceName));
    }

    /**
     * Second key-point of this pattern : the decorator is type-hinted
     *
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testDecoratorTypeHinted()
    {
        if (version_compare(PHP_VERSION, '7', '>=')) {
            throw new \PHPUnit_Framework_Error('Skip test for PHP 7', 0, __FILE__, __LINE__);
        }

        $this->getMockForAbstractClass('DesignPatterns\Structural\Decorator\Decorator', array(new \stdClass()));
    }

    /**
     * Second key-point of this pattern : the decorator is type-hinted
     *
     * @requires PHP 7
     * @expectedException TypeError
     */
    public function testDecoratorTypeHintedForPhp7()
    {
        $this->getMockForAbstractClass('DesignPatterns\Structural\Decorator\Decorator', array(new \stdClass()));
    }

    /**
     * The decorator implements and wraps the same interface
     */
    public function testDecoratorOnlyAcceptRenderer()
    {
        $mock = $this->getMock('DesignPatterns\Structural\Decorator\RendererInterface');
        $dec = $this->getMockForAbstractClass('DesignPatterns\Structural\Decorator\Decorator', array($mock));
        $this->assertNotNull($dec);
    }
}
```

[0]: http://laravelacademy.org/post/2760.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e8%a3%85%e9%a5%b0%e5%99%a8%e6%a8%a1%e5%bc%8f
[3]: ../img/decorator-design-pattern.png
[4]: http://laravelacademy.org/tags/php