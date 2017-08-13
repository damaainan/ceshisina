<?php
    
    namespace Command;
    
    /**
     * Receiver类
     */
    class Receiver
    {
        /**
         * @param string $str
         */
        public function write($str)
        {
            echo $str;
        }
    }