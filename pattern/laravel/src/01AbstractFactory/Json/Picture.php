<?php

namespace AbstractFactory\Json;

use AbstractFactory\Picture as BasePicture;

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