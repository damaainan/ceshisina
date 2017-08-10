<?php

namespace StaticFactory\Tests;

$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use StaticFactory\StaticFactory;

/**
 * 测试静态工厂模式
 *
 */
class StaticFactoryTest extends \PHPUnit\Framework\TestCase
{

    public function getTypeList()
    {
        return array(
            array('string'),
            array('number')  //没有类 单元测试报错 注释掉 即可通过
        );
    }

    /**
     * @dataProvider getTypeList
     */
    public function testCreation($type)
    {
        $obj = StaticFactory::factory($type);
        $this->assertInstanceOf('StaticFactory\FormatterInterface', $obj);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testException()
    {
        StaticFactory::factory("");
    }
}