<?php

namespace DataStructure;

/**
 * 点
 */
class Vertex
{
    private $number;

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Vertex constructor.
     * @param $number
     */
    public function __construct($number)
    {
        if (false === is_int($number) || 0 >= $number) {
            throw new \Exception('点必须是>1的正整数');
        }
        $this->number = $number;
    }

}