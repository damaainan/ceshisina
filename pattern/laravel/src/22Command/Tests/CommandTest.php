<?php
    
    namespace Command\Tests;
    
    $autoLoadFilePath = '../../vendor/autoload.php';
    require_once $autoLoadFilePath;

    use Command\Invoker;
    use Command\Receiver;
    use Command\HelloCommand;
    
    /**
     * CommandTest在命令模式中扮演客户端角色
     */
    class CommandTest extends \PHPUnit\Framework\TestCase
    {
    
        /**
         * @var Invoker
         */
        protected $invoker;
    
        /**
         * @var Receiver
         */
        protected $receiver;
    
        protected function setUp()
        {
            $this->invoker = new Invoker();
            $this->receiver = new Receiver();
        }
    
        public function testInvocation()
        {
            $this->invoker->setCommand(new HelloCommand($this->receiver));
            $this->expectOutputString('Hello World');
            $this->invoker->run();
        }
    }