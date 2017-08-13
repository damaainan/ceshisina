<?php
    
    namespace State;
    
    /**
     * OrderFactory类
     */
    class OrderFactory
    {
        private function __construct()
        {
            throw new \Exception('Can not instance the OrderFactory class!');
        }
    
        /**
         * @param int $id
         *
         * @return CreateOrder|ShippingOrder
         * @throws \Exception
         */
        public static function getOrder($id)
        {
            //从数据库获取订单伪代码
            $order = 'Get Order From Database';
    
            switch ($order['status']) {
                case 'created':
                    return new CreateOrder($order);
                case 'shipping':
                    return new ShippingOrder($order);
                default:
                    throw new \Exception('Order status error!');
                    break;
            }
        }
    }