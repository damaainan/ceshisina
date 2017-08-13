<?php
    
    namespace State;
    
    /**
     * OrderInterface接口
     */
    interface OrderInterface
    {
        /**
         * @return mixed
         */
        public function shipOrder();
    
        /**
         * @return mixed
         */
        public function completeOrder();
    }