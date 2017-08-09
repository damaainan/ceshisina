<?php

namespace Builder\Parts;

/**
 * VehicleInterface是车辆接口
 */
abstract class Vehicle
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setPart($key, $value)
    {  
        $this->data[$key] = $value;
    }
}