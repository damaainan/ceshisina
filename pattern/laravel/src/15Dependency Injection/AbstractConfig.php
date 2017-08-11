<?php

namespace DependencyInjection;

/**
 * AbstractConfig类
 */
abstract class AbstractConfig
{
    /**
     * @var Storage of data
     */
    protected $storage;

    public function __construct($storage)
    {
        $this->storage = $storage;
    }
}