<?php

namespace SimpleFactory;

/**
 * VehicleInterface 是车子接口
 */
interface VehicleInterface
{
    /**
     * @param mixed $destination
     *
     * @return mixed
     */
    public function driveTo($destination);
}