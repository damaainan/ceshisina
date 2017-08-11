<?php

namespace Bridge\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Bridge\Assemble;
use Bridge\Car;
use Bridge\Motorcycle;
use Bridge\Produce;

class BridgeTest extends \PHPUnit\Framework\TestCase
{

    public function testCar()
    {
        $vehicle = new Car(new Produce(), new Assemble());
        $this->expectOutputString('Car Produced Assembled');
        $vehicle->manufacture();
    }

    public function testMotorcycle()
    {
        $vehicle = new Motorcycle(new Produce(), new Assemble());
        $this->expectOutputString('Motorcycle Produced Assembled');
        $vehicle->manufacture();
    }
}