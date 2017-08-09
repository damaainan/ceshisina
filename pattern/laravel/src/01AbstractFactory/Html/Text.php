<?php

namespace AbstractFactory\Html;

use AbstractFactory\Text as BaseText;

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