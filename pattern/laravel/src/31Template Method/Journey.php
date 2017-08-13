<?php
    
    namespace TemplateMethod;
    
    abstract class Journey
    {
        /**
         * 该方法是父类和子类提供的公共服务
         * 注意到方法前加了final，意味着子类不能重写该方法
         */
        final public function takeATrip()
        {
            $this->buyAFlight();
            $this->takePlane();
            $this->enjoyVacation();
            $this->buyGift();
            $this->takePlane();
        }
    
        /**
         * 该方法必须被子类实现, 这是模板方法模式的核心特性
         */
        abstract protected function enjoyVacation();
    
        /**
         * 这个方法也是算法的一部分，但是是可选的，只有在需要的时候才去重写它
         */
        protected function buyGift()
        {
        }
    
        /**
         * 子类不能访问该方法
         */
        private function buyAFlight()
        {
            echo "Buying a flight\n";
        }
    
        /**
         * 这也是个final方法
         */
        final protected function takePlane()
        {
            echo "Taking the plane\n";
        }
    }