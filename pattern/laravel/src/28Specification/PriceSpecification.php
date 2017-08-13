<?php
    namespace Specification;
    
    /**
     * 判断给定Item的价格是否介于最小值和最大值之间的规格
     */
    class PriceSpecification extends AbstractSpecification
    {
        protected $maxPrice;
        protected $minPrice;
    
        /**
         * 设置最大值
         *
         * @param int $maxPrice
         */
        public function setMaxPrice($maxPrice)
        {
            $this->maxPrice = $maxPrice;
        }
    
        /**
         * 设置最小值
         *
         * @param int $minPrice
         */
        public function setMinPrice($minPrice)
        {
            $this->minPrice = $minPrice;
        }
    
        /**
         * 判断给定Item的定价是否在最小值和最大值之间
         *
         * @param Item $item
         *
         * @return bool
         */
        public function isSatisfiedBy(Item $item)
        {
            if (!empty($this->maxPrice) && $item->getPrice() > $this->maxPrice) {
                return false;
            }
            if (!empty($this->minPrice) && $item->getPrice() < $this->minPrice) {
                return false;
            }
    
            return true;
        }
    }