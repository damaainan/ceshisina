<?php

namespace Composite\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Composite;

/**
 * FormTest用于测试表单的组合模式
 */
class CompositeTest extends \PHPUnit\Framework\TestCase
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
        $className = 'Composite\Form';
        $abstractName = 'Composite\FormElement';
        $this->assertTrue(is_subclass_of($className, $abstractName));
    }
}