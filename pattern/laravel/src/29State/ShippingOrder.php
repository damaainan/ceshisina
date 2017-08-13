<?php
    
    namespace State;
    
    /**
     * ShippingOrder类
     */
    class ShippingOrder implements OrderInterface
    {
        /**
         * @var array
         */
        private $order;
    
        /**
         * @param array $order
         *
         * @throws \Exception
         */
        public function __construct(array $order)
        {
            if (empty($order)) {
                throw new \Exception('Order can not be empty!');
            }
            $this->order = $order;
        }
    
        /**
         * @return mixed|void
         * @throws \Exception
         */
        public function shipOrder()
        {
            //当订单发货过程中不能对该订单进行发货处理
            throw new \Exception('Can not ship the order which status is shipping!');
        }
    
        /**
         * @return mixed
         */
        public function completeOrder()
        {
            $this->order['status'] = 'completed';
            $this->order['updatedTime'] = time();
    
            // 将订单状态保存到数据库
            return $this->updateOrder($this->order);
        }
    }