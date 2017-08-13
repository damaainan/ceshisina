<?php
    
namespace Strategy;

/**
 * ComparatorInterface类
 */
interface ComparatorInterface
{
    /**
     * @param mixed $a
     * @param mixed $b
     *
     * @return bool
     */
    public function compare($a, $b);
}