<?php

namespace Adapter;

/**
 * PaperBookInterface 是纸质书接口
 */
interface PaperBookInterface
{
    /**
     * 翻页方法
     *
     * @return mixed
     */
    public function turnPage();

    /**
     * 打开书的方法
     *
     * @return mixed
     */
    public function open();
}