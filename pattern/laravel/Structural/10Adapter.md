# PHP 设计模式系列 —— 适配器模式（Adapter / Wrapper）

 Posted on [2015年12月18日][0] by [学院君][1]

### **1、模式定义**

首先我们来看看什么是适配器。

适配器的存在，就是为了将已存在的东西（接口）转换成适合我们需要、能被我们所利用的东西。在现实生活中，适配器更多的是作为一个中间层来实现这种转换作用。比如电源适配器，它是用于电流变换（整流）的设备。

[适配器模式][2]将一个类的接口转换成客户希望的另外一个接口，使得原本由于接口不兼容而不能一起工作的那些类可以在一起工作。

### **2、UML类图**

![适配器模式UML类图][3]

### **3、示例代码**

#### **PaperBookInterface.php**

```php
<?php

namespace DesignPatterns\Structural\Adapter;

/**
 * PaperBookInterface 是纸质书接口
 */
interface PaperBookInterface
{
    /**
     * 翻页方法
     *
     * @return mixed
     */
    public function turnPage();

    /**
     * 打开书的方法
     *
     * @return mixed
     */
    public function open();
}
```

#### **Book.php**

```php
<?php

namespace DesignPatterns\Structural\Adapter;

/**
 * Book 是纸质书实现类
 */
class Book implements PaperBookInterface
{
    /**
     * {@inheritdoc}
     */
    public function open()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function turnPage()
    {
    }
}
```

#### **EBookAdapter.php**

```php
<?php

namespace DesignPatterns\Structural\Adapter;

/**
 * EBookAdapter 是电子书适配器类
 *
 * 该适配器实现了 PaperBookInterface 接口,
 * 但是你不必修改客户端使用纸质书的代码
 */
class EBookAdapter implements PaperBookInterface
{
    /**
     * @var EBookInterface
     */
    protected $eBook;

    /**
     * 注意该构造函数注入了电子书接口EBookInterface
     *
     * @param EBookInterface $ebook
     */
    public function __construct(EBookInterface $ebook)
    {
        $this->eBook = $ebook;
    }

    /**
     * 电子书将纸质书接口方法转换为电子书对应方法
     */
    public function open()
    {
        $this->eBook->pressStart();
    }

    /**
     * 纸质书翻页转化为电子书翻页
     */
    public function turnPage()
    {
        $this->eBook->pressNext();
    }
}
```

#### **EBookInterface.php**

```php
<?php

namespace DesignPatterns\Structural\Adapter;

/**
 * EBookInterface 是电子书接口
 */
interface EBookInterface
{
    /**
     * 电子书翻页
     *
     * @return mixed
     */
    public function pressNext();

    /**
     * 打开电子书
     *
     * @return mixed
     */
    public function pressStart();
}
```

#### **Kindle.php**

```php
<?php

namespace DesignPatterns\Structural\Adapter;

/**
 * Kindle 是电子书实现类
 */
class Kindle implements EBookInterface
{
    /**
     * {@inheritdoc}
     */
    public function pressNext()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function pressStart()
    {
    }
}
```

### **4、测试代码**

**Tests/AdapterTest.php**

```php
<?php

namespace DesignPatterns\Structural\Adapter\Tests;

use DesignPatterns\Structural\Adapter\EBookAdapter;
use DesignPatterns\Structural\Adapter\Kindle;
use DesignPatterns\Structural\Adapter\PaperBookInterface;
use DesignPatterns\Structural\Adapter\Book;

/**
 * AdapterTest 用于测试适配器模式
 */
class AdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function getBook()
    {
        return array(
            array(new Book()),
            // 我们在适配器中引入了电子书
            array(new EBookAdapter(new Kindle()))
        );
    }

    /**
     * 客户端只知道有纸质书，实际上第二本书是电子书，
     * 但是对客户来说代码一致，不需要做任何改动
     *
     * @param PaperBookInterface $book
     *
     * @dataProvider getBook
     */
    public function testIAmAnOldClient(PaperBookInterface $book)
    {
        $this->assertTrue(method_exists($book, 'open'));
        $this->assertTrue(method_exists($book, 'turnPage'));
    }
}
```

[0]: http://laravelacademy.org/post/2660.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e9%80%82%e9%85%8d%e5%99%a8%e6%a8%a1%e5%bc%8f
[3]: ../img/cf94563f-04af-4f55-9801-c9a62320d469.png
[4]: http://laravelacademy.org/tags/adapter
[5]: http://laravelacademy.org/tags/php