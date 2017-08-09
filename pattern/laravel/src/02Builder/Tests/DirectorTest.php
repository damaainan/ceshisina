<?php

namespace Builder\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Builder\Director;
use Builder\CarBuilder;
use Builder\BikeBuilder;
use Builder\BuilderInterface;

/**
 * DirectorTest 用于测试建造者模式
 */
class DirectorTest extends \PHPUnit\Framework\TestCase
{

    protected $director;

    protected function setUp()
    {
        $this->director = new Director();
    }

    public static function getBuilder()
    {
        return array(
            array(new CarBuilder()),
            array(new BikeBuilder())
        );
    }

   /**
    * 这里我们测试建造过程，客户端并不知道具体的建造者。
    *
    * @dataProvider getBuilder
    */
    public function testBuild(BuilderInterface $builder)
    {
        $newVehicle = $this->director->build($builder);
        $this->assertInstanceOf('Builder\Parts\Vehicle', $newVehicle);
    }
}