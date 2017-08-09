<?php

namespace AbstractFactory\Json;

use AbstractFactory\Text as BaseText;

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