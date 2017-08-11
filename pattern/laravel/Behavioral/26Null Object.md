# PHP 设计模式系列 —— 空对象模式（Null Object）

 Posted on [2016年1月6日][0] by [学院君][1]

### **1、模式定义**

[空对象模式][2]并不是 GoF 那本《[设计模式][3]》中提到的 23 种经典设计模式之一，但却是一个经常出现以致我们不能忽略的模式。该模式有以下优点：

* 简化客户端代码
* 减少空指针异常风险
* 更少的条件控制语句以减少测试用例

在[空对象][4]模式中，以前返回对象或 null 的方法现在返回对象或空对象 [Null][5]Object，这样会减少代码中的条件判断，比如之前调用返回对象方法要这么写：

    if (!is_null($obj)) { 
        $obj->callSomething(); 
    }

现在因为即使对象为空也会返回空对象，所以可以直接这样调用返回对象上的方法：

    $obj->callSomething();

从而消除客户端的检查代码。

当然，你可能已经意识到了，要实现这种调用的前提是返回对象和空对象需要实现同一个接口，具备一致的代码结构。

### **2、UML类图**

![Null-Object-Design-Pattern-Uml][6]

### **3、示例代码**

#### **Service.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\NullObject;
    
    /**
     * Service 是使用 logger 的模拟服务
     */
    class Service
    {
        /**
         * @var LoggerInterface
         */
        protected $logger;
    
        /**
         * 我们在构造函数中注入logger
         *
         * @param LoggerInterface $log
         */
        public function __construct(LoggerInterface $log)
        {
            $this->logger = $log;
        }
    
        /**
         * do something ...
         */
        public function doSomething()
        {
            // 在空对象模式中不再需要这样判断 "if (!is_null($this->logger))..."
            $this->logger->log('We are in ' . __METHOD__);
            // something to do...
        }
    }
```
#### **LoggerInterface.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\NullObject;
    
    /**
     * LoggerInterface 是 logger 接口
     *
     * 核心特性: NullLogger必须和其它Logger一样实现这个接口
     */
    interface LoggerInterface
    {
        /**
         * @param string $str
         *
         * @return mixed
         */
        public function log($str);
    }
```
#### **PrintLogger.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\NullObject;
    
    /**
     * PrintLogger是用于打印Logger实体到标准输出的Logger
     */
    class PrintLogger implements LoggerInterface
    {
        /**
         * @param string $str
         */
        public function log($str)
        {
            echo $str;
        }
    }
```
#### **NullLogger.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\NullObject;
    
    /**
     * 核心特性 : 必须实现LoggerInterface接口
     */
    class NullLogger implements LoggerInterface
    {
        /**
         * {@inheritdoc}
         */
        public function log($str)
        {
            // do nothing
        }
    }
```
### **4、测试代码**

#### **Tests/LoggerTest.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\NullObject\Tests;
    
    use DesignPatterns\Behavioral\NullObject\NullLogger;
    use DesignPatterns\Behavioral\NullObject\Service;
    use DesignPatterns\Behavioral\NullObject\PrintLogger;
    
    /**
     * LoggerTest 用于测试不同的Logger
     */
    class LoggerTest extends \PHPUnit\Framework\TestCase
    {
    
        public function testNullObject()
        {
            $service = new Service(new NullLogger());
            $this->expectOutputString(null);  // 没有输出
            $service->doSomething();
        }
    
        public function testStandardLogger()
        {
            $service = new Service(new PrintLogger());
            $this->expectOutputString('We are in DesignPatterns\Behavioral\NullObject\Service::doSomething');
            $service->doSomething();
        }
    }
```
[0]: http://laravelacademy.org/post/2912.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e7%a9%ba%e5%af%b9%e8%b1%a1%e6%a8%a1%e5%bc%8f
[3]: http://laravelacademy.org/tags/%e8%ae%be%e8%ae%a1%e6%a8%a1%e5%bc%8f
[4]: http://laravelacademy.org/tags/%e7%a9%ba%e5%af%b9%e8%b1%a1
[5]: http://laravelacademy.org/tags/null
[6]: http://laravelacademy.org/wp-content/uploads/2016/01/Null-Object-Design-Pattern-Uml.png
[7]: http://laravelacademy.org/tags/php