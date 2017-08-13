<?php
    
    namespace Command;
    
    /**
     * Invoker类
     */
    class Invoker
    {
        /**
         * @var CommandInterface
         */
        protected $command;
    
        /**
         * 在调用者中我们通常可以找到这种订阅命令的方法
         *
         * @param CommandInterface $cmd
         */
        public function setCommand(CommandInterface $cmd)
        {
            $this->command = $cmd;
        }
    
        /**
         * 执行命令
         */
        public function run()
        {
            $this->command->execute();
        }
    }