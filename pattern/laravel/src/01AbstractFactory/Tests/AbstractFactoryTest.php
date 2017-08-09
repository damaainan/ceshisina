<?php

namespace AbstractFactory\Tests;


//装载自动加载函数
// $autoLoadFilePath = dirname(dirname(dirname($_SERVER['DOCUMENT_ROOT']))).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
$autoLoadFilePath = '../../vendor/autoload.php';
require_once $autoLoadFilePath;

use AbstractFactory\AbstractFactory;
use AbstractFactory\HtmlFactory;
use AbstractFactory\JsonFactory;
use PHPUnit\Framework\TestCase;

/**
 * AbstractFactoryTest 用于测试具体的工厂
 */
class AbstractFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function getFactories()
    {
        return array(
            array(new JsonFactory()),
            array(new HtmlFactory())
        );
    }

    /**
     * 这里是工厂的客户端，我们无需关心传递过来的是什么工厂类，
     * 只需以我们想要的方式渲染任意想要的组件即可。
     *
     * @dataProvider getFactories
     */
    public function testComponentCreation(AbstractFactory $factory)
    {
        $article = array(
            $factory->createText('Laravel学院'),
            $factory->createPicture('/image.jpg', 'laravel-academy'),
            $factory->createText('LaravelAcademy.org')
        );

        $this->assertContainsOnly('AbstractFactory\MediaInterface', $article);
    }
}