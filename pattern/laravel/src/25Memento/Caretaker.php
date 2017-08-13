<?php
    
    namespace Memento;
    
    class Caretaker
    {
        protected $history = array();
    
        /**
         * @return Memento
         */
        public function getFromHistory($id)
        {
            return $this->history[$id];
        }
    
        /**
         * @param Memento $state
         */
        public function saveToHistory(Memento $state)
        {
            $this->history[] = $state;
        }
    
        public function runCustomLogic()
        {
            $originator = new Originator();
    
            //设置状态为State1
            $originator->setState("State1");
            //设置状态为State2
            $originator->setState("State2");
            //将State2保存到Memento
            $this->saveToHistory($originator->getStateAsMemento());
            //设置状态为State3
            $originator->setState("State3");
    
            //我们可以请求多个备忘录, 然后选择其中一个进行[回滚][4]
            
            //保存State3到Memento
            $this->saveToHistory($originator->getStateAsMemento());
            //设置状态为State4
            $originator->setState("State4");
    
            $originator->restoreFromMemento($this->getFromHistory(1));
            //从备忘录恢复后的状态: State3
    
            return $originator->getStateAsMemento()->getState();
        }
    }