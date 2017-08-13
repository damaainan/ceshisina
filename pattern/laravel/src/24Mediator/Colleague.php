<?php
    
    namespace Mediator;
    
    /**
     * Colleague是一个抽象的同事类，但是它只知道中介者Mediator，而不知道其他同事
     */
    abstract class Colleague
    {
        /**
         * this ensures no change in subclasses
         *
         * @var MediatorInterface
         */
        private $mediator;
        
        /**
         * @param MediatorInterface $medium
         */
        public function __construct(MediatorInterface $medium)
        {
            $this->mediator = $medium;
        }
    
        // for subclasses
        protected function getMediator()
        {
            return $this->mediator;
        }
    }