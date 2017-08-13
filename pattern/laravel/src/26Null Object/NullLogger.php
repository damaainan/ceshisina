<?php
    
    namespace NullObject;
    
    /**
     * 核心特性 : 必须实现LoggerInterface接口
     */
    class NullLogger implements LoggerInterface
    {
        /**
         * {@inheritdoc}
         */
        public function log($str)
        {
            // do nothing
        }
    }