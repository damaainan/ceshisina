# PHP 设计模式系列 —— 组合模式（Composite）

 Posted on [2015年12月21日2015年12月21日][0] by [学院君][1]

### **1、模式定义**

[组合模式][2]（Composite Pattern）有时候又叫做部分-整体模式，用于将对象组合成树形结构以表示“部分-整体”的层次关系。组合模式使得用户对单个对象和组合对象的使用具有一致性。

常见使用场景：如树形菜单、文件夹菜单、部门组织架构图等。

### **2、UML类图**

![composite-design-pattern][3]

### **3、示例代码**

#### **FormElement.php**

```php
<?php

namespace DesignPatterns\Structural\Composite;

/**
 * FormElement类
 */
abstract class FormElement
{
    /**
     * renders the elements' code
     *
     * @param int $indent
     *
     * @return mixed
     */
    abstract public function render($indent = 0);
}
```

#### **Form.php**

```php
<?php

namespace DesignPatterns\Structural\Composite;

/**
 * 组合节点必须实现组件接口，这对构建组件树而言是强制的
 */
class Form extends FormElement
{
    /**
     * @var array|FormElement[]
     */
    protected $elements;

    /**
     * 遍历所有元素并调用它们的render()方法, 然后返回返回完整的表单显示
     *
     * 但是从外部来看, 并没有看见组合过程, 就像是单个表单实例一样
     *
     * @param int $indent
     *
     * @return string
     */
    public function render($indent = 0)
    {
        $formCode = '';

        foreach ($this->elements as $element) {
            $formCode .= $element->render($indent + 1) . [PHP][4]_EOL;
        }

        return $formCode;
    }

    /**
     * @param FormElement $element
     */
    public function addElement(FormElement $element)
    {
        $this->elements[] = $element;
    }
}
```

#### **InputElement.php**

```php
<?php

namespace DesignPatterns\Structural\Composite;

/**
 * InputElement类
 */
class InputElement extends FormElement
{
    /**
     * 渲染input元素HTML
     *
     * @param int $indent
     *
     * @return mixed|string
     */
    public function render($indent = 0)
    {
        return str_repeat('  ', $indent) . '<input type="text" />';
    }
}
```

#### **TextElement.php**

```php
<?php

namespace DesignPatterns\Structural\Composite;

/**
 * TextElement类
 */
class TextElement extends FormElement
{
    /**
     * 渲染文本元素
     *
     * @param int $indent
     *
     * @return mixed|string
     */
    public function render($indent = 0)
    {
        return str_repeat('  ', $indent) . 'this is a text element';
    }
}
```

### **4、测试代码**

#### **Tests/CompositeTest.php**

```php
<?php

namespace DesignPatterns\Structural\Composite\Tests;

use DesignPatterns\Structural\Composite;

/**
 * FormTest用于测试表单的组合模式
 */
class CompositeTest extends \PHPUnit_Framework_TestCase
{

    public function testRender()
    {
        $form = new Composite\Form();
        $form->addElement(new Composite\TextElement());
        $form->addElement(new Composite\InputElement());
        $embed = new Composite\Form();
        $embed->addElement(new Composite\TextElement());
        $embed->addElement(new Composite\InputElement());
        $form->addElement($embed);  // 这里我们添加一个嵌套树到表单

        $this->assertRegExp('#^\s{4}#m', $form->render());
    }

    /**
     * 组合模式最关键之处在于如果你想要构建组件树每个组件必须实现组件接口
     */
    public function testFormImplementsFormEelement()
    {
        $className = 'DesignPatterns\Structural\Composite\Form';
        $abstractName = 'DesignPatterns\Structural\Composite\FormElement';
        $this->assertTrue(is_subclass_of($className, $abstractName));
    }
}
```

[0]: http://laravelacademy.org/post/2699.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e7%bb%84%e5%90%88%e6%a8%a1%e5%bc%8f
[3]: ../img/composite-design-pattern.png
[4]: http://laravelacademy.org/tags/php