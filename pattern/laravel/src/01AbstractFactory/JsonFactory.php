<?php

namespace AbstractFactory;

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