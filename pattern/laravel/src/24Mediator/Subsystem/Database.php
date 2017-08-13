<?php
    
    namespace Mediator\Subsystem;
    
    use Mediator\Colleague;
    
    /**
     * Database提供数据库服务
     */
    class Database extends Colleague
    {
        /**
         * @return string
         */
        public function getData()
        {
            return "World";
        }
    }