<?php
    namespace Specification;
    
    /**
     * 规格接口
     */
    interface SpecificationInterface
    {
        /**
         * 判断对象是否满足规格
         *
         * @param Item $item
         *
         * @return bool
         */
        public function isSatisfiedBy(Item $item);
    
        /**
         * 创建一个逻辑与规格（AND）
         *
         * @param SpecificationInterface $spec
         */
        public function plus(SpecificationInterface $spec);
    
        /**
         * 创建一个逻辑或规格（OR）
         *
         * @param SpecificationInterface $spec
         */
        public function either(SpecificationInterface $spec);
    
        /**
         * 创建一个逻辑非规格（NOT）
         */
        public function not();
    }