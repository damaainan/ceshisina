<?php
    namespace Specification;
    
    /**
     * 逻辑非规格
     */
    class Not extends AbstractSpecification
    {
    
        protected $spec;
    
        /**
         * 在构造函数中传入指定规格
         *
         * @param SpecificationInterface $spec
         */
        public function __construct(SpecificationInterface $spec)
        {
            $this->spec = $spec;
        }
    
        /**
         * 返回规格的相反结果
         *
         * @param Item $item
         *
         * @return bool
         */
        public function isSatisfiedBy(Item $item)
        {
            return !$this->spec->isSatisfiedBy($item);
        }
    }