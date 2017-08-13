<?php
    
    namespace Observer;
    
    /**
     * 观察者模式 : 被观察对象 (主体对象)
     *
     * 主体对象维护观察者列表并发送通知
     *
     */
    class User implements \SplSubject
    {
        /**
         * user data
         *
         * @var array
         */
        protected $data = array();
    
        /**
         * observers
         *
         * @var \SplObjectStorage
         */
        protected $observers;
        
        public function __construct()
        {
            $this->observers = new \SplObjectStorage();
        }
    
        /**
         * 附加观察者
         *
         * @param \SplObserver $observer
         *
         * @return void
         */
        public function attach(\SplObserver $observer)
        {
            $this->observers->attach($observer);
        }
    
        /**
         * 取消观察者
         *
         * @param \SplObserver $observer
         *
         * @return void
         */
        public function detach(\SplObserver $observer)
        {
            $this->observers->detach($observer);
        }
    
        /**
         * 通知观察者方法
         *
         * @return void
         */
        public function notify()
        {
            /** @var \SplObserver $observer */
            foreach ($this->observers as $observer) {
                $observer->update($this);
            }
        }
    
        /**
         *
         * @param string $name
         * @param mixed  $value
         *
         * @return void
         */
        public function __set($name, $value)
        {
            $this->data[$name] = $value;
    
            // 通知观察者用户被改变
            $this->notify();
        }
    }