<?php
    
    namespace Visitor;
    
    class User extends Role
    {
        /**
         * @var string
         */
        protected $name;
    
        /**
         * @param string $name
         */
        public function __construct($name)
        {
            $this->name = (string) $name;
        }
    
        /**
         * @return string
         */
        public function getName()
        {
            return "User " . $this->name;
        }
    }