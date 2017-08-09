<?php

namespace AbstractFactory;

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