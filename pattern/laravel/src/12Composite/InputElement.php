<?php

namespace Composite;

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