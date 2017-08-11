<?php

namespace DependencyInjection\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use DependencyInjection\ArrayConfig;
use DependencyInjection\Connection;

class DependencyInjectionTest extends \PHPUnit\Framework\TestCase
{
    protected $config;
    protected $source;

    public function setUp()
    {
        $this->source = include 'config.php';
        $this->config = new ArrayConfig($this->source);
    }

    public function testDependencyInjection()
    {
        $connection = new Connection($this->config);
        $connection->connect();
        $this->assertEquals($this->source['host'], $connection->getHost());
    }
}