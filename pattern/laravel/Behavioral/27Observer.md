# PHP 设计模式系列 —— 观察者模式（Observer）

 Posted on [2016年1月7日][0] by [学院君][1]

### **1、模式定义**

[观察者模式][2]有时也被称作发布/订阅模式，该模式用于为对象实现发布/订阅功能：一旦主体对象状态发生改变，与之关联的观察者对象会收到通知，并进行相应操作。

将一个系统分割成一个一些类相互协作的类有一个不好的副作用，那就是需要维护相关对象间的一致性。我们不希望为了维持一致性而使各类紧密耦合，这样会给维护、扩展和重用都带来不便。观察者就是解决这类的耦合关系的。

消息队列系统、事件都使用了观察者模式。

[PHP][3] 为观察者模式定义了两个接口：[SplSubject][4] 和 [SplObserver][5]。SplSubject 可以看做主体对象的抽象，SplObserver 可以看做观察者对象的抽象，要实现观察者模式，只需让主体对象实现 SplSubject ，观察者对象实现 SplObserver，并实现相应方法即可。

### **2、UML类图**

![Observer-Design-Pattern-Uml][6]

### **3、示例代码**

#### **User.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Observer;
    
    /**
     * 观察者模式 : 被观察对象 (主体对象)
     *
     * 主体对象维护观察者列表并发送通知
     *
     */
    class User implements \SplSubject
    {
        /**
         * user data
         *
         * @var array
         */
        protected $data = array();
    
        /**
         * observers
         *
         * @var \SplObjectStorage
         */
        protected $observers;
        
        public function __construct()
        {
            $this->observers = new \SplObjectStorage();
        }
    
        /**
         * 附加观察者
         *
         * @param \SplObserver $observer
         *
         * @return void
         */
        public function attach(\SplObserver $observer)
        {
            $this->observers->attach($observer);
        }
    
        /**
         * 取消观察者
         *
         * @param \SplObserver $observer
         *
         * @return void
         */
        public function detach(\SplObserver $observer)
        {
            $this->observers->detach($observer);
        }
    
        /**
         * 通知观察者方法
         *
         * @return void
         */
        public function notify()
        {
            /** @var \SplObserver $observer */
            foreach ($this->observers as $observer) {
                $observer->update($this);
            }
        }
    
        /**
         *
         * @param string $name
         * @param mixed  $value
         *
         * @return void
         */
        public function __set($name, $value)
        {
            $this->data[$name] = $value;
    
            // 通知观察者用户被改变
            $this->notify();
        }
    }
```
#### **UserObserver.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Observer;
    
    /**
     * UserObserver 类（观察者对象）
     */
    class UserObserver implements \SplObserver
    {
        /**
         * 观察者要实现的唯一方法
         * 也是被 Subject 调用的方法
         *
         * @param \SplSubject $subject
         */
        public function update(\SplSubject $subject)
        {
            echo get_class($subject) . ' has been updated';
        }
    }
```
### **4、测试代码**

#### **Tests/ObserverTest.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Observer\Tests;
    
    use DesignPatterns\Behavioral\Observer\UserObserver;
    use DesignPatterns\Behavioral\Observer\User;
    
    /**
     * ObserverTest 测试观察者模式
     */
    class ObserverTest extends \PHPUnit\Framework\TestCase
    {
    
        protected $observer;
    
        protected function setUp()
        {
            $this->observer = new UserObserver();
        }
    
        /**
         * 测试通知
         */
        public function testNotify()
        {
            $this->expectOutputString('DesignPatterns\Behavioral\Observer\User has been updated');
            $subject = new User();
    
            $subject->attach($this->observer);
            $subject->property = 123;
        }
    
        /**
         * 测试订阅
         */
        public function testAttachDetach()
        {
            $subject = new User();
            $reflection = new \ReflectionProperty($subject, 'observers');
    
            $reflection->setAccessible(true);
            /** @var \SplObjectStorage $observers */
            $observers = $reflection->getValue($subject);
    
            $this->assertInstanceOf('SplObjectStorage', $observers);
            $this->assertFalse($observers->contains($this->observer));
    
            $subject->attach($this->observer);
            $this->assertTrue($observers->contains($this->observer));
    
            $subject->detach($this->observer);
            $this->assertFalse($observers->contains($this->observer));
        }
    
        /**
         * 测试 update() 调用
         */
        public function testUpdateCalling()
        {
            $subject = new User();
            $observer = $this->getMock('SplObserver');
            $subject->attach($observer);
    
            $observer->expects($this->once())
                ->method('update')
                ->with($subject);
    
            $subject->notify();
        }
    }
```
### **5、总结**

观察者模式解除了主体和具体观察者的耦合，让耦合的双方都依赖于抽象，而不是依赖具体。从而使得各自的变化都不会影响另一边的变化。

[0]: http://laravelacademy.org/post/2935.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e8%a7%82%e5%af%9f%e8%80%85%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/php
[4]: http://laravelacademy.org/tags/splsubject
[5]: http://laravelacademy.org/tags/splobserver
[6]: ../img/Observer-Design-Pattern-Uml.png