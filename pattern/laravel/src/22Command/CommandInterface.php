<?php
    
    namespace Command;
    
    /**
     * CommandInterface
     */
    interface CommandInterface
    {
        /**
         * 在命令模式中这是最重要的方法,
         * Receiver在构造函数中传入.
         */
        public function execute();
    }