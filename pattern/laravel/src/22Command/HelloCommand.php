<?php
    
    namespace Command;
    
    /**
     * 这是一个调用Receiver的print方法的命令实现类，
     * 但是对于调用者而言，只知道调用命令类的execute方法
     */
    class HelloCommand implements CommandInterface
    {
        /**
         * @var Receiver
         */
        protected $output;
    
        /**
         * 每一个具体的命令基于不同的Receiver
         * 它们可以是一个、多个，甚至完全没有Receiver
         *
         * @param Receiver $console
         */
        public function __construct(Receiver $console)
        {
            $this->output = $console;
        }
    
        /**
         * 执行并输出 "Hello World"
         */
        public function execute()
        {
            // 没有Receiver的时候完全通过命令类来实现功能
            $this->output->write('Hello World');
        }
    }