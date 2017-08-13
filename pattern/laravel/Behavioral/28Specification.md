# PHP 设计模式系列 —— 规格模式（Specification）

 Posted on [2016年1月9日2016年1月9日][0] by [学院君][1]

### **1、模式定义**

[规格模式][2]（Specification）可以认为是[组合模式][3]的一种扩展。有时项目中某些条件决定了业务逻辑，这些条件就可以抽离出来以某种关系（与、或、非）进行组合，从而灵活地对业务逻辑进行定制。另外，在查询、过滤等应用场合中，通过预定义多个条件，然后使用这些条件的组合来处理查询或过滤，而不是使用逻辑判断语句来处理，可以简化整个实现逻辑。

这里的每个条件就是一个规格，多个规格/条件通过串联的方式以某种逻辑关系形成一个组合式的规格。

### **2、UML类图**

![Specification-Design-Pattern-Uml][4]

### **3、示例代码**

#### **Item.php**

```php
    <?php
    namespace DesignPatterns\Behavioral\Specification;
    
    class Item
    {
        protected $price;
    
        /**
         * An item must have a price
         *
         * @param int $price
         */
        public function __construct($price)
        {
            $this->price = $price;
        }
    
        /**
         * Get the items price
         *
         * @return int
         */
        public function getPrice()
        {
            return $this->price;
        }
    }
```
#### **SpecificationInterface.php**

```php
    <?php
    namespace DesignPatterns\Behavioral\Specification;
    
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
```
#### **AbstractSpecification.php**

```php
    <?php
    namespace DesignPatterns\Behavioral\Specification;
    
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
```
#### **Plus.php**

```php
    <?php
    namespace DesignPatterns\Behavioral\Specification;
    
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
```
#### **Either.php**

```php
    <?php
    namespace DesignPatterns\Behavioral\Specification;
    
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
```
#### **Not.php**

```php
    <?php
    namespace DesignPatterns\Behavioral\Specification;
    
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
```
#### **PriceSpecification.php**

```php
    <?php
    namespace DesignPatterns\Behavioral\Specification;
    
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
```
### **4、测试代码**

#### **Tests/SpecificationTest.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Specification\Tests;
    
    use DesignPatterns\Behavioral\Specification\PriceSpecification;
    use DesignPatterns\Behavioral\Specification\Item;
    
    /**
     * SpecificationTest 用于测试规格模式
     */
    class SpecificationTest extends \PHPUnit\Framework\TestCase
    {
        public function testSimpleSpecification()
        {
            $item = new Item(100);
            $spec = new PriceSpecification();
    
            $this->assertTrue($spec->isSatisfiedBy($item));
    
            $spec->setMaxPrice(50);
            $this->assertFalse($spec->isSatisfiedBy($item));
    
            $spec->setMaxPrice(150);
            $this->assertTrue($spec->isSatisfiedBy($item));
    
            $spec->setMinPrice(101);
            $this->assertFalse($spec->isSatisfiedBy($item));
    
            $spec->setMinPrice(100);
            $this->assertTrue($spec->isSatisfiedBy($item));
        }
    
        public function testNotSpecification()
        {
            $item = new Item(100);
            $spec = new PriceSpecification();
            $not = $spec->not();
    
            $this->assertFalse($not->isSatisfiedBy($item));
    
            $spec->setMaxPrice(50);
            $this->assertTrue($not->isSatisfiedBy($item));
    
            $spec->setMaxPrice(150);
            $this->assertFalse($not->isSatisfiedBy($item));
    
            $spec->setMinPrice(101);
            $this->assertTrue($not->isSatisfiedBy($item));
    
            $spec->setMinPrice(100);
            $this->assertFalse($not->isSatisfiedBy($item));
        }
    
        public function testPlusSpecification()
        {
            $spec1 = new PriceSpecification();
            $spec2 = new PriceSpecification();
            $plus = $spec1->plus($spec2);
    
            $item = new Item(100);
    
            $this->assertTrue($plus->isSatisfiedBy($item));
    
            $spec1->setMaxPrice(150);
            $spec2->setMinPrice(50);
            $this->assertTrue($plus->isSatisfiedBy($item));
    
            $spec1->setMaxPrice(150);
            $spec2->setMinPrice(101);
            $this->assertFalse($plus->isSatisfiedBy($item));
    
            $spec1->setMaxPrice(99);
            $spec2->setMinPrice(50);
            $this->assertFalse($plus->isSatisfiedBy($item));
        }
    
        public function testEitherSpecification()
        {
            $spec1 = new PriceSpecification();
            $spec2 = new PriceSpecification();
            $either = $spec1->either($spec2);
    
            $item = new Item(100);
    
            $this->assertTrue($either->isSatisfiedBy($item));
    
            $spec1->setMaxPrice(150);
            $spec2->setMaxPrice(150);
            $this->assertTrue($either->isSatisfiedBy($item));
    
            $spec1->setMaxPrice(150);
            $spec2->setMaxPrice(0);
            $this->assertTrue($either->isSatisfiedBy($item));
    
            $spec1->setMaxPrice(0);
            $spec2->setMaxPrice(150);
            $this->assertTrue($either->isSatisfiedBy($item));
    
            $spec1->setMaxPrice(99);
            $spec2->setMaxPrice(99);
            $this->assertFalse($either->isSatisfiedBy($item));
        }
    }
```
[0]: http://laravelacademy.org/post/2960.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e8%a7%84%e6%a0%bc%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/%e7%bb%84%e5%90%88%e6%a8%a1%e5%bc%8f
[4]: ../img/Specification-Design-Pattern-Uml.png
[5]: http://laravelacademy.org/tags/php