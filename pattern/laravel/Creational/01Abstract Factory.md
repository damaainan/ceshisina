# PHP 设计模式系列 —— 抽象工厂模式（Abstract Factory）

 Posted on [2015年12月9日2015年12月9日][0] by [学院君][1]

### **1、模式概述**

[抽象工厂模式][2]为一组相关或相互依赖的对象创建提供接口，而无需指定其具体实现类。抽象工厂的客户端不关心如何创建这些对象，只关心如何将它们组合到一起。

### **2、问题引出**

举个例子，如果某个应用是可移植的，那么它需要封装平台依赖，这些平台可能包括窗口系统、操作系统、数据库等等。这种封装如果未经设计，通常代码会包含多个 if 条件语句以及对应平台的操作。这种硬编码不仅可读性差，而且扩展性也不好。

### **3、解决方案**

提供一个间接的层（即“抽象工厂”）抽象一组相关或依赖对象的创建而不是直接指定具体实现类。该“工厂”对象的职责是为不同平台提供创建服务。客户端不需要直接创建平台对象，而是让工厂去做这件事。

这种机制让替换平台变得简单，因为抽象工厂的具体实现类只有在实例化的时候才出现，如果要替换的话只需要在实例化的时候指定具体实现类即可。

### **4、UML类图**

抽象工厂为每个产品（具体实现）定义了工厂方法，每个工厂方法封装了new操作符和具体类（指定平台的产品类），每个“平台”都是抽象工厂的派生类。

![抽象工厂模式UML类图][3]

### **5、代码实现**

#### **AbstractFactory.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory;

/**
 * 抽象工厂类
 *
 * 该设计模式实现了设计模式的依赖倒置原则，因为最终由具体子类创建具体组件
 *
 * 在本例中，抽象工厂为创建 Web 组件（产品）提供了接口，这里有两个组件：文本和图片，有两种渲染方式：HTML
 * 和 JSON，对应四个具体实现类。
 *
 * 尽管有四个具体类，但是客户端只需要知道这个接口可以用于构建正确的 HTTP 响应即可，无需关心其具体实现。
 */
abstract class AbstractFactory
{
    /**
     * 创建本文组件
     *
     * @param string $content
     *
     * @return Text
     */
    abstract public function createText($content);

    /**
     * 创建图片组件
     *
     * @param string $path
     * @param string $name
     *
     * @return Picture
     */
    abstract public function createPicture($path, $name = '');
}
```
#### **JsonFactory.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory;

/**
 * JsonFactory类
 *
 * JsonFactory 是用于创建 JSON 组件的工厂
 */
class JsonFactory extends AbstractFactory
{

    /**
     * 创建图片组件
     *
     * @param string $path
     * @param string $name
     *
     * @return Json\Picture|Picture
     */
    public function createPicture($path, $name = '')
    {
        return new Json\Picture($path, $name);
    }

    /**
     * 创建文本组件
     *
     * @param string $content
     *
     * @return Json\Text|Text
     */
    public function createText($content)
    {
        return new Json\Text($content);
    }
}
```

#### **HtmlFactory.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory;

/**
 * HtmlFactory类
 *
 * HtmlFactory 是用于创建 HTML 组件的工厂
 */
class HtmlFactory extends AbstractFactory
{
    /**
     * 创建图片组件
     *
     * @param string $path
     * @param string $name
     *
     * @return Html\Picture|Picture
     */
    public function createPicture($path, $name = '')
    {
        return new Html\Picture($path, $name);
    }

    /**
     * 创建文本组件
     *
     * @param string $content
     *
     * @return Html\Text|Text
     */
    public function createText($content)
    {  
        return new Html\Text($content);
    }
}
```

#### **MediaInterface.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory;

/**
 * MediaInterface接口
 *
 * 该接口不是抽象工厂设计模式的一部分, 一般情况下, 每个组件都是不相干的
 */
interface MediaInterface
{

    /**
     * JSON 或 HTML（取决于具体类）输出的未经处理的渲染
     *
     * @return string
     */
    public function render();
}
```

#### **Picture.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory;

/**
 * Picture类
 */
abstract class Picture implements MediaInterface
{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $path
     * @param string $name
     */
    public function __construct($path, $name = '')
    {
        $this->name = (string) $name;
        $this->path = (string) $path;
    }
}
```

#### **Text.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory;

/**
 * Text类
 */
abstract class Text implements MediaInterface
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = (string) $text;
    }
}
```

#### **Json/Picture.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory\Json;

use DesignPatterns\Creational\AbstractFactory\Picture as BasePicture;

/**
 * Picture类
 *
 * 该类是以 JSON 格式输出的具体图片组件类
 */
class Picture extends BasePicture
{
    /**
     * JSON 格式输出
     *
     * @return string
     */
    public function render()
    {
        return json_encode(array('title' => $this->name, 'path' => $this->path));
    }
}
```

#### **Json/Text.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory\Json;

use DesignPatterns\Creational\AbstractFactory\Text as BaseText;

/**
 * Class Text
 *
 * 该类是以 JSON 格式输出的具体文本组件类
 */
class Text extends BaseText
{
    /**
     * 以 JSON 格式输出的渲染
     *
     * @return string
     */
    public function render()
    {
        return json_encode(array('content' => $this->text));
    }
}
```

#### **Html/Picture.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory\Html;

use DesignPatterns\Creational\AbstractFactory\Picture as BasePicture;

/**
 * Picture 类
 *
 * 该类是以 HTML 格式渲染的具体图片类
 */
class Picture extends BasePicture
{
    /**
     * HTML 格式输出的图片
     *
     * @return string
     */
    public function render()
    {
        return sprintf('<img src="%s" title="%s"/>', $this->path, $this->name);
    }
}
```

#### **Html/Text.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory\Html;

use DesignPatterns\Creational\AbstractFactory\Text as BaseText;

/**
 * Text 类
 *
 * 该类是以 HTML 渲染的具体文本组件类
 */
class Text extends BaseText
{
    /**
     * HTML 格式输出的文本
     *
     * @return string
     */
    public function render()
    {
        return '<div>' . htmlspecialchars($this->text) . '</div>';
    }
}
```

### **6、测试代码**

**Tests/AbstractFactoryTest.php**

```php
<?php

namespace DesignPatterns\Creational\AbstractFactory\Tests;

use DesignPatterns\Creational\AbstractFactory\AbstractFactory;
use DesignPatterns\Creational\AbstractFactory\HtmlFactory;
use DesignPatterns\Creational\AbstractFactory\JsonFactory;

/**
 * AbstractFactoryTest 用于测试具体的工厂
 */
class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function getFactories()
    {
        return array(
            array(new JsonFactory()),
            array(new HtmlFactory())
        );
    }

    /**
     * 这里是工厂的客户端，我们无需关心传递过来的是什么工厂类，
     * 只需以我们想要的方式渲染任意想要的组件即可。
     *
     * @dataProvider getFactories
     */
    public function testComponentCreation(AbstractFactory $factory)
    {
        $article = array(
            $factory->createText('Laravel学院'),
            $factory->createPicture('/image.jpg', 'laravel-academy'),
            $factory->createText('LaravelAcademy.org')
        );

        $this->assertContainsOnly('DesignPatterns\Creational\AbstractFactory\MediaInterface', $article);
    }
}
```

执行测试：

    phpunit /path/to/AbstractFactoryTest.php

### **7、总结**

最后我们以工厂生产产品为例，所谓抽象工厂模式就是我们的抽象工厂约定了可以生产的产品，这些产品都包含多种规格，然后我们可以从抽象工厂为每一种规格派生出具体工厂类，然后让这些具体工厂类生产具体的产品。以上示例中`AbstractFactory`是抽象工厂，`JsonFactory`和`HtmlFactory`是具体工厂，`Html\Picture`、`Html\Text`、`Json\Picture`和`Json\Text`都是具体产品，客户端需要`HTML`格式的`Text`，调用`HtmlFactory`的`createText`方法即可，而不必关心其实现逻辑。

[0]: http://laravelacademy.org/post/2471.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e6%8a%bd%e8%b1%a1%e5%b7%a5%e5%8e%82%e6%a8%a1%e5%bc%8f
[3]: ../img/78bd6487-a8c4-443c-a6c7-7f8aa13ff4c3.png
[4]: http://laravelacademy.org/tags/%e8%ae%be%e8%ae%a1%e6%a8%a1%e5%bc%8f
[5]: http://laravelacademy.org/tags/%e4%be%9d%e8%b5%96%e5%80%92%e7%bd%ae    
[6]: http://laravelacademy.org/tags/php