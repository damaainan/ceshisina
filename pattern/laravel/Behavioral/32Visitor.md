# PHP 设计模式系列 —— 访问者模式（Visitor）

 Posted on [2016年1月13日][0] by [学院君][1]

### **1、模式定义**

我们去银行柜台办业务，一般情况下会开几个个人业务柜台的，你去其中任何一个柜台办理都是可以的。我们的[访问者模式][2]可以很好付诸在这个场景中：对于银行柜台来说，他们是不用变化的，就是说今天和明天提供个人业务的柜台是不需要有变化的。而我们作为访问者，今天来银行可能是取消费流水，明天来银行可能是去办理手机银行业务，这些是我们访问者的操作，一直是在变化的。

访问者模式就是表示一个作用于某对象结构中的各元素的操作。它使你可以在不改变各元素的类的前提下定义作用于这些元素的新操作。

### **2、UML类图**

![Visitor-Design-Pattern-Uml][3]

### **3、示例代码**

#### **RoleVisitorInterface.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Visitor;
    
    /**
     * 访问者接口
     */
    interface RoleVisitorInterface
    {
        /**
         * 访问 User 对象
         *
         * @param \DesignPatterns\Behavioral\Visitor\User $role
         */
        public function visitUser(User $role);
    
        /**
         * 访问 Group 对象
         *
         * @param \DesignPatterns\Behavioral\Visitor\Group $role
         */
        public function visitGroup(Group $role);
    }
```
#### **RolePrintVisitor.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Visitor;
    
    /**
     * Visitor接口的具体实现
     */
    class RolePrintVisitor implements RoleVisitorInterface
    {
        /**
         * {@inheritdoc}
         */
        public function visitGroup(Group $role)
        {
            echo "Role: " . $role->getName();
        }
    
        /**
         * {@inheritdoc}
         */
        public function visitUser(User $role)
        {
            echo "Role: " . $role->getName();
        }
    }
```
#### **Role.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Visitor;
    
    /**
     * Role 类
     */
    abstract class Role
    {
        /**
         * 该方法基于Visitor的类名判断调用Visitor的方法
         *
         * 如果必须调用其它方法，重写本方法即可
         *
         * @param \DesignPatterns\Behavioral\Visitor\RoleVisitorInterface $visitor
         *
         * @throws \InvalidArgumentException
         */
        public function accept(RoleVisitorInterface $visitor)
        {
            $klass = get_called_class();
            preg_match('#([^\\\\]+)$#', $klass, $extract);
            $visitingMethod = 'visit' . $extract[1];
    
            if (!method_exists(__NAMESPACE__ . '\RoleVisitorInterface', $visitingMethod)) {
                throw new \InvalidArgumentException("The visitor you provide cannot visit a $klass instance");
            }
    
            call_user_func(array($visitor, $visitingMethod), $this);
        }
    }
```
#### **User.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Visitor;
    
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
```
#### **Group.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Visitor;
    
    class Group extends Role
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
            return "Group: " . $this->name;
        }
    }
```
### **4、测试代码**

#### **Tests/VisitorTest.php**

```php
    <?php
    
    namespace DesignPatterns\Tests\Visitor\Tests;
    
    use DesignPatterns\Behavioral\Visitor;
    
    /**
     * VisitorTest 用于测试访问者模式
     */
    class VisitorTest extends \PHPUnit\Framework\TestCase
    {
    
        protected $visitor;
    
        protected function setUp()
        {
            $this->visitor = new Visitor\RolePrintVisitor();
        }
    
        public function getRole()
        {
            return array(
                array(new Visitor\User("Dominik"), 'Role: User Dominik'),
                array(new Visitor\Group("Administrators"), 'Role: Group: Administrators')
            );
        }
    
        /**
         * @dataProvider getRole
         */
        public function testVisitSomeRole(Visitor\Role $role, $expect)
        {
            $this->expectOutputString($expect);
            $role->accept($this->visitor);
        }
    
        /**
         * @expectedException \InvalidArgumentException
         * @expectedExceptionMessage Mock
         */
        public function testUnknownObject()
        {
            $mock = $this->getMockForAbstractClass('DesignPatterns\Behavioral\Visitor\Role');
            $mock->accept($this->visitor);
        }
    }
```
### **5、总结**

访问者模式适用于[数据结构][5]相对稳定的系统，它把数据结构和作用于结构之上的操作之间的耦合解脱开，使得操作集合可以相对自由的演化。在本例中，User、Group 是数据结构，而 RolePrintVisitor 是访问者（用于结构之上的操作）。

当实现访问者模式时，要将尽可能多的将对象浏览逻辑放在 Visitor 类中，而不是放在它的子类中，这样的话，ConcreteVisitor 类所访问的对象结构依赖较少，从而使维护较为容易。

[0]: http://laravelacademy.org/post/3024.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e8%ae%bf%e9%97%ae%e8%80%85%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/wp-content/uploads/2016/01/Visitor-Design-Pattern-Uml.png
[4]: http://laravelacademy.org/tags/php
[5]: http://laravelacademy.org/tags/%e6%95%b0%e6%8d%ae%e7%bb%93%e6%9e%84