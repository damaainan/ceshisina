<?php
    namespace Specification;
    
    /**
     * 逻辑或规格
     */
    class Either extends AbstractSpecification
    {
    
        protected $left;
        protected $right;
    
        /**
         * 两种规格的组合
         *
         * @param SpecificationInterface $left
         * @param SpecificationInterface $right
         */
        public function __construct(SpecificationInterface $left, SpecificationInterface $right)
        {
            $this->left = $left;
            $this->right = $right;
        }
    
        /**
         * 返回两种规格的逻辑或评估
         *
         * @param Item $item
         *
         * @return bool
         */
        public function isSatisfiedBy(Item $item)
        {
            return $this->left->isSatisfiedBy($item) || $this->right->isSatisfiedBy($item);
        }
    }