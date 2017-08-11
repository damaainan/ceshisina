<?php

namespace Decorator;

/**
 * 装饰器必须实现 RendererInterface 接口, 这是装饰器模式的主要特点，
 * 否则的话就不是装饰器而只是个包裹类
 */

/**
 * Decorator类
 */
abstract class Decorator implements RendererInterface
{
    /**
     * @var RendererInterface
     */
    protected $wrapped;

    /**
     * 必须类型声明装饰组件以便在子类中可以调用renderData()方法
     *
     * @param RendererInterface $wrappable
     */
    public function __construct(RendererInterface $wrappable)
    {
        $this->wrapped = $wrappable;
    }
}