<?php

namespace Composite;

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