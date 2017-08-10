<?php

namespace Multiton;

/**
 * Multiton类
 */
class Multiton
{
    /**
     *
     * 第一个实例
     */
    const INSTANCE_1 = '1';

    /**
     *
     * 第二个实例
     */
    const INSTANCE_2 = '2';

    /**
     * 实例数组
     *
     * @var array
     */
    private static $instances = array();

    /**
     * 构造函数是私有的，不能从外部进行实例化
     *
     */
    private function __construct()
    {
    }

    /**
     * 通过指定名称返回实例（使用到该实例的时候才会实例化）
     *
     * @param string $instanceName
     *
     * @return Multiton
     */
    public static function getInstance($instanceName)
    {
        if (!array_key_exists($instanceName, self::$instances)) {
            self::$instances[$instanceName] = new self();
        }

        return self::$instances[$instanceName];
    }

    /**
     * 防止实例从外部被克隆
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * 防止实例从外部反序列化
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}