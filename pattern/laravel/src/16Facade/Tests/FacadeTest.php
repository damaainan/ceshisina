<?php

namespace Facade\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use Facade\Facade as Computer;
use Facade\OsInterface;

/**
 * FacadeTest用于测试门面模式
 */
class FacadeTest extends \PHPUnit\Framework\TestCase
{

    public function getComputer()
    {
        $bios = $this->getMockBuilder('Facade\BiosInterface')
                ->setMethods(array('launch', 'execute', 'waitForKeyPress'))
                ->disableAutoload()
                ->getMock();
        $os = $this->getMockBuilder('Facade\OsInterface')
                ->setMethods(array('getName'))
                ->disableAutoload()
                ->getMock();
        $bios->expects($this->once())
                ->method('launch')
                ->with($os);
        $os->expects($this->once())
                ->method('getName')
                ->will($this->returnValue('Linux'));

        $facade = new Computer($bios, $os);
        return array(array($facade, $os));
    }

    /**
     * @dataProvider getComputer
     */
    public function testComputerOn(Computer $facade, OsInterface $os)
    {
        // interface is simpler :
        $facade->turnOn();
        // but I can access to lower component
        $this->assertEquals('Linux', $os->getName());
    }
}