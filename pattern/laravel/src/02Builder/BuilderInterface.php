<?php

namespace Builder;

/**
 * 建造者接口
 */
interface BuilderInterface
{
    /**
     * @return mixed
     */
    public function createVehicle();

    /**
     * @return mixed
     */
    public function addWheel();

    /**
     * @return mixed
     */
    public function addEngine();

    /**
     * @return mixed
     */
    public function addDoors();

    /**
    * @return mixed
    */
    public function getVehicle();
}