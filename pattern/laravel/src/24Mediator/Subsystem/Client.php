<?php
    
    namespace Mediator\Subsystem;
    
    use Mediator\Colleague;
    
    /**
     * Client是发起请求&获取响应的客户端
     */
    class Client extends Colleague
    {
        /**
         * request
         */
        public function request()
        {
            $this->getMediator()->makeRequest();
        }
    
        /**
         * output content
         *
         * @param string $content
         */
        public function output($content)
        {
            echo $content;
        }
    }