<?php

namespace Adapter;

/**
 * EBookInterface 是电子书接口
 */
interface EBookInterface
{
    /**
     * 电子书翻页
     *
     * @return mixed
     */
    public function pressNext();

    /**
     * 打开电子书
     *
     * @return mixed
     */
    public function pressStart();
}