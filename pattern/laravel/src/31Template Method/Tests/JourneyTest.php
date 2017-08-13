<?php
    
    namespace TemplateMethod\Tests;
    
    $autoLoadFilePath = '../../vendor/autoload.php';
    require_once $autoLoadFilePath;


    
    use TemplateMethod;
    
    /**
     * JourneyTest测试所有的度假
     */
    class JourneyTest extends \PHPUnit\Framework\TestCase
    {
    
        public function testBeach()
        {
            $journey = new TemplateMethod\BeachJourney();
            $this->expectOutputRegex('#sun-bathing#');
            $journey->takeATrip();
        }
    
        public function testCity()
        {
            $journey = new TemplateMethod\CityJourney();
            $this->expectOutputRegex('#drink#');
            $journey->takeATrip();
        }
    
        /**
         * 在PHPUnit中如何测试抽象模板方法
         */
        public function testLasVegas()
        {
            $journey = $this->getMockForAbstractClass('TemplateMethod\Journey');
            $journey->expects($this->once())
                ->method('enjoyVacation')
                ->will($this->returnCallback(array($this, 'mockUpVacation')));
            $this->expectOutputRegex('#Las Vegas#');
            $journey->takeATrip();
        }
    
        public function mockUpVacation()
        {
            echo "Fear and loathing in Las Vegas\n";
        }
    }