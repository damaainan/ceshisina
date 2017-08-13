    <?php
    
    namespace Memento;
    
    class Originator
    {
        /* @var mixed */
        private $state;
    
        // 这个类还可以包含不属于备忘录状态的额外数据
    
        /**
         * @param mixed $state
         */
        public function setState($state)
        {
            // 必须检查该类子类内部的状态类型或者使用依赖注入
            $this->state = $state;
        }
    
        /**
         * @return Memento
         */
        public function getStateAsMemento()
        {
            // 在Memento中必须保存一份隔离的备份
            $state = is_object($this->state) ? clone $this->state : $this->state;
    
            return new Memento($state);
        }
    
        public function restoreFromMemento(Memento $memento)
        {
            $this->state = $memento->getState();
        }
    }