<?php

namespace Registry\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Registry\Registry;

class RegistryTest extends \PHPUnit\Framework\TestCase
{

    public function testSetAndGetLogger()
    {
        Registry::set(Registry::LOGGER, new \StdClass());

        $logger = Registry::get(Registry::LOGGER);
        $this->assertInstanceOf('StdClass', $logger);
    }
}