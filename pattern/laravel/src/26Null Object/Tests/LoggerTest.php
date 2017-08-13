<?php
    
    namespace NullObject\Tests;
    
    $autoLoadFilePath = '../../vendor/autoload.php';
    require_once $autoLoadFilePath;


    
    use NullObject\NullLogger;
    use NullObject\Service;
    use NullObject\PrintLogger;
    
    /**
     * LoggerTest 用于测试不同的Logger
     */
    class LoggerTest extends \PHPUnit\Framework\TestCase
    {
    
        public function testNullObject()
        {
            $service = new Service(new NullLogger());
            $this->expectOutputString(null);  // 没有输出
            $service->doSomething();
        }
    
        public function testStandardLogger()
        {
            $service = new Service(new PrintLogger());
            $this->expectOutputString('We are in NullObject\Service::doSomething');
            $service->doSomething();
        }
    }