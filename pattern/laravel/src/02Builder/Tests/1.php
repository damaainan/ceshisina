<?php

namespace Builder\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Builder\Director;
use Builder\CarBuilder;
use Builder\BikeBuilder;
use Builder\BuilderInterface;

$car = new CarBuilder();

var_dump($car);