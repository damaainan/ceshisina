<?php
    
    namespace State;
    
    /**
     * CreateOrder类
     */
    class CreateOrder implements OrderInterface
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
         * @return mixed
         */
        public function shipOrder()
        {
            $this->order['status'] = 'shipping';
            $this->order['updatedTime'] = time();
    
            // 将订单状态保存到数据库
            return $this->updateOrder($this->order);
        }
    
        /**
         * @return mixed|void
         * @throws \Exception
         */
        public function completeOrder()
        {
            // 还未发货的订单不能设置为完成状态
            throw new \Exception('Can not complete the order which status is created!');
        }
    }