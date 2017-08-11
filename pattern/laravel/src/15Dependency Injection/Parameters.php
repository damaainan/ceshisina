<?php

namespace DependencyInjection;

/**
 * Parameters接口
 */
interface Parameters
{
    /**
     * 获取参数
     *
     * @param string|int $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * 设置参数
     *
     * @param string|int $key
     * @param mixed      $value
     */
    public function set($key, $value);
}