<?php
    namespace Specification;
    
    /**
     * 逻辑与规格（AND）
     */
    class Plus extends AbstractSpecification
    {
    
        protected $left;
        protected $right;
    
        /**
         * 在构造函数中传入两种规格
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
         * 返回两种规格的逻辑与评估
         *
         * @param Item $item
         *
         * @return bool
         */
        public function isSatisfiedBy(Item $item)
        {
            return $this->left->isSatisfiedBy($item) && $this->right->isSatisfiedBy($item);
        }
    }