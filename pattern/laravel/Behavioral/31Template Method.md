# PHP 设计模式系列 —— 模板方法模式（Template Method）

 Posted on [2016年1月12日][0] by [学院君][1]

### **1、模式定义**

[模板方法模式][2]又叫模板模式，该模式在一个方法中定义一个算法的骨架，而将一些步骤延迟到子类中。模板方法使得子类可以在不改变算法结构的情况下，重新定义算法中的某些步骤。

模板方法模式将主要的方法定义为 final，防止子类修改算法骨架，将子类必须实现的方法定义为 abstract。而普通的方法（无 final 或 abstract 修饰）则称之为钩子（hook）。

### **2、UML类图**

![Template-Method-Design-Pattern-Uml][3]

### **3、示例代码**

#### **Journey.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\TemplateMethod;
    
    abstract class Journey
    {
        /**
         * 该方法是父类和子类提供的公共服务
         * 注意到方法前加了final，意味着子类不能重写该方法
         */
        final public function takeATrip()
        {
            $this->buyAFlight();
            $this->takePlane();
            $this->enjoyVacation();
            $this->buyGift();
            $this->takePlane();
        }
    
        /**
         * 该方法必须被子类实现, 这是模板方法模式的核心特性
         */
        abstract protected function enjoyVacation();
    
        /**
         * 这个方法也是算法的一部分，但是是可选的，只有在需要的时候才去重写它
         */
        protected function buyGift()
        {
        }
    
        /**
         * 子类不能访问该方法
         */
        private function buyAFlight()
        {
            echo "Buying a flight\n";
        }
    
        /**
         * 这也是个final方法
         */
        final protected function takePlane()
        {
            echo "Taking the plane\n";
        }
    }
```
#### **BeachJourney.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\TemplateMethod;
    
    /**
     * BeachJourney类（在海滩度假）
     */
    class BeachJourney extends Journey
    {
        protected function enjoyVacation()
        {
            echo "Swimming and sun-bathing\n";
        }
    }
```
#### **CityJourney.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\TemplateMethod;
    
    /**
     * CityJourney类（在城市中度假）
     */
    class CityJourney extends Journey
    {
        protected function enjoyVacation()
        {
            echo "Eat, drink, take photos and sleep\n";
        }
    }
```
### **4、测试代码**

#### **Tests/JourneyTest.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\TemplateMethod\Tests;
    
    use DesignPatterns\Behavioral\TemplateMethod;
    
    /**
     * JourneyTest测试所有的度假
     */
    class JourneyTest extends \PHPUnit\Framework\TestCase
    {
    
        public function testBeach()
        {
            $journey = new TemplateMethod\BeachJourney();
            $this->expectOutputRegex('#sun-bathing#');
            $journey->takeATrip();
        }
    
        public function testCity()
        {
            $journey = new TemplateMethod\CityJourney();
            $this->expectOutputRegex('#drink#');
            $journey->takeATrip();
        }
    
        /**
         * 在PHPUnit中如何测试抽象模板方法
         */
        public function testLasVegas()
        {
            $journey = $this->getMockForAbstractClass('DesignPatterns\Behavioral\TemplateMethod\Journey');
            $journey->expects($this->once())
                ->method('enjoyVacation')
                ->will($this->returnCallback(array($this, 'mockUpVacation')));
            $this->expectOutputRegex('#Las Vegas#');
            $journey->takeATrip();
        }
    
        public function mockUpVacation()
        {
            echo "Fear and loathing in Las Vegas\n";
        }
    }
```
### **5、总结**

模板方法模式是基于[继承][5]的代码复用技术，模板方法模式的结构和用法也是[面向对象][6]设计的核心之一。在模板方法模式中，可以将相同的代码放在父类中，而将不同的方法实现放在不同的子类中。

在模板方法模式中，我们需要准备一个抽象类，将部分逻辑以具体方法以及具体构造函数的形式实现，然后声明一些抽象方法来让子类实现剩余的逻辑。不同的子类可以以不同的方式实现这些抽象方法，从而对剩余的逻辑有不同的实现，这就是模板方法模式的用意。模板方法模式体现了面向对象的诸多重要思想，是一种使用频率较高的模式。

[0]: http://laravelacademy.org/post/3006.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e6%a8%a1%e6%9d%bf%e6%96%b9%e6%b3%95%e6%a8%a1%e5%bc%8f
[3]: ../img/Template-Method-Design-Pattern-Uml.png
[4]: http://laravelacademy.org/tags/php
[5]: http://laravelacademy.org/tags/%e7%bb%a7%e6%89%bf
[6]: http://laravelacademy.org/tags/%e9%9d%a2%e5%90%91%e5%af%b9%e8%b1%a1