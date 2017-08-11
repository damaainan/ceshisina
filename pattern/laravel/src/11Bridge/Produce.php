<?php

namespace Bridge;

/**
 * 具体实现：Produce
 */
class Produce implements Workshop
{

    public function work()
    {
        print 'Produced ';
    }
}