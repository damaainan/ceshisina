# PHP 设计模式系列 —— 命令模式（Command）

 Posted on [2016年1月1日][0] by [学院君][1]

### **1、模式定义**

[命令模式][2]（Command）将请求封装成对象，从而使你可用不同的请求对客户进行参数化；对请求排队或记录请求日志，以及支持可撤消的操作。这么说很抽象，我们举个例子：

假设我们有一个调用者类 Invoker 和一个接收调用请求的类 Receiver，在两者之间我们使用命令类 Command 的 execute 方法来托管请求调用方法，这样，调用者 Invoker 只知道调用命令类的 execute 方法来处理客户端请求，从而实现接收者 Receiver 与调用者 Invoker 的解耦。

Laravel 中的 [Artisan][3] 命令就使用了命令模式。

### **2、UML类图**

[![Command-Design-Pattern-UML](http://laravelacademy.org/wp-content/uploads/2016/01/Command-Design-Pattern-UML.png)](http://laravelacademy.org/wp-content/uploads/2016/01/Command-Design-Pattern-UML.png)

### **3、示例代码**

#### **CommandInterface.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Command;
    
    /**
     * CommandInterface
     */
    interface CommandInterface
    {
        /**
         * 在命令模式中这是最重要的方法,
         * Receiver在构造函数中传入.
         */
        public function execute();
    }
```
#### **HelloCommand.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Command;
    
    /**
     * 这是一个调用Receiver的print方法的命令实现类，
     * 但是对于调用者而言，只知道调用命令类的execute方法
     */
    class HelloCommand implements CommandInterface
    {
        /**
         * @var Receiver
         */
        protected $output;
    
        /**
         * 每一个具体的命令基于不同的Receiver
         * 它们可以是一个、多个，甚至完全没有Receiver
         *
         * @param Receiver $console
         */
        public function __construct(Receiver $console)
        {
            $this->output = $console;
        }
    
        /**
         * 执行并输出 "Hello World"
         */
        public function execute()
        {
            // 没有Receiver的时候完全通过命令类来实现功能
            $this->output->write('Hello World');
        }
    }
```
#### **Receiver.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Command;
    
    /**
     * Receiver类
     */
    class Receiver
    {
        /**
         * @param string $str
         */
        public function write($str)
        {
            echo $str;
        }
    }
```
#### **Invoker.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Command;
    
    /**
     * Invoker类
     */
    class Invoker
    {
        /**
         * @var CommandInterface
         */
        protected $command;
    
        /**
         * 在调用者中我们通常可以找到这种订阅命令的方法
         *
         * @param CommandInterface $cmd
         */
        public function setCommand(CommandInterface $cmd)
        {
            $this->command = $cmd;
        }
    
        /**
         * 执行命令
         */
        public function run()
        {
            $this->command->execute();
        }
    }
```
### **4、测试代码**

#### **Tests/CommandTest.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Command\Tests;
    
    use DesignPatterns\Behavioral\Command\Invoker;
    use DesignPatterns\Behavioral\Command\Receiver;
    use DesignPatterns\Behavioral\Command\HelloCommand;
    
    /**
     * CommandTest在命令模式中扮演客户端角色
     */
    class CommandTest extends \PHPUnit\Framework\TestCase
    {
    
        /**
         * @var Invoker
         */
        protected $invoker;
    
        /**
         * @var Receiver
         */
        protected $receiver;
    
        protected function setUp()
        {
            $this->invoker = new Invoker();
            $this->receiver = new Receiver();
        }
    
        public function testInvocation()
        {
            $this->invoker->setCommand(new HelloCommand($this->receiver));
            $this->expectOutputString('Hello World');
            $this->invoker->run();
        }
    }
```
### **5、总结**

命令模式就是将一组对象的相似行为，进行了抽象，将调用者与被调用者之间进行解耦，提高了应用的灵活性。命令模式将调用的目标对象的一些异构性给封装起来，通过统一的方式来为调用者提供服务。

[0]: http://laravelacademy.org/post/2871.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e5%91%bd%e4%bb%a4%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/artisan
[4]: http://laravelacademy.org/tags/php