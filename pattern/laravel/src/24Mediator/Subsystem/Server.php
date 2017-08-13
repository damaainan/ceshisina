<?php
    
    namespace Mediator\Subsystem;
    
    use Mediator\Colleague;
    
    /**
     * Server用于发送响应
     */
    class Server extends Colleague
    {
        /**
         * process on server
         */
        public function process()
        {
            $data = $this->getMediator()->queryDb();
            $this->getMediator()->sendResponse("Hello $data");
        }
    }