<?php
    namespace Specification;
    
    /**
     * 规格抽象类
     */
    abstract class AbstractSpecification implements SpecificationInterface
    {
        /**
         * 检查给定Item是否满足所有规则
         *
         * @param Item $item
         *
         * @return bool
         */
        abstract public function isSatisfiedBy(Item $item);
    
        /**
         * 创建一个新的逻辑与规格（AND）
         *
         * @param SpecificationInterface $spec
         *
         * @return SpecificationInterface
         */
        public function plus(SpecificationInterface $spec)
        {
            return new Plus($this, $spec);
        }
    
        /**
         * 创建一个新的逻辑或组合规格（OR）
         *
         * @param SpecificationInterface $spec
         *
         * @return SpecificationInterface
         */
        public function either(SpecificationInterface $spec)
        {
            return new Either($this, $spec);
        }
    
        /**
         * 创建一个新的逻辑非规格（NOT）
         *
         * @return SpecificationInterface
         */
        public function not()
        {
            return new Not($this);
        }
    }