# PHP 设计模式系列 —— 中介者模式（Mediator）

 Posted on [2016年1月4日][0] by [学院君][1]

### **1、模式定义**

[中介者模式][2]（Mediator）就是用一个中介对象来封装一系列的对象交互，中介者使各对象不需要显式地相互引用，从而使其耦合松散，而且可以独立地改变它们之间的交互。

对于中介对象而言，所有相互交互的对象，都视为同事类，中介对象就是用来维护各个同事对象之间的关系，所有的同事类都只和中介对象交互，也就是说，中介对象是需要知道所有的同事对象的。当一个同事对象自身发生变化时，它是不知道会对其他同事对象产生什么影响，它只需要通知中介对象，“我发生变化了”，中介对象会去和其他同事对象进行交互的。这样一来，同事对象之间的依赖就没有了。有了中介者之后，所有的交互都封装在了中介对象里面，各个对象只需要关心自己能做什么就行，不需要再关心做了之后会对其他对象产生什么影响，也就是无需再维护这些关系了。

### **2、UML类图**

![Mediator-Design-Pattern-UML][3]

### **3、示例代码**

#### **MediatorInterface.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Mediator;
    
    /**
     * MediatorInterface是一个中介者契约
     * 该接口不是强制的，但是使用它更加符合里氏替换原则
     */
    interface MediatorInterface
    {
        /**
         * 发送响应
         *
         * @param string $content
         */
        public function sendResponse($content);
    
        /**
         * 发起请求
         */
        public function makeRequest();
    
        /**
         * 查询数据库
         */
        public function queryDb();
    }
```
#### **Mediator.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Mediator;
    
    use DesignPatterns\Behavioral\Mediator\Subsystem;
    
    /**
     * Mediator是中介者模式的具体实现类
     * In this example, I have made a "Hello World" with the Mediator Pattern.
     */
    class Mediator implements MediatorInterface
    {
    
        /**
         * @var Subsystem\Server
         */
        protected $server;
    
        /**
         * @var Subsystem\Database
         */
        protected $database;
    
        /**
         * @var Subsystem\Client
         */
        protected $client;
    
        /**
         * @param Subsystem\Database $db
         * @param Subsystem\Client   $cl
         * @param Subsystem\Server   $srv
         */
        public function setColleague(Subsystem\Database $db, Subsystem\Client $cl, Subsystem\Server $srv)
        {
            $this->database = $db;
            $this->server = $srv;
            $this->client = $cl;
        }
    
        /**
         * 发起请求
         */
        public function makeRequest()
        {
            $this->server->process();
        }
    
        /**
         * 查询数据库
         * @return mixed
         */
        public function queryDb()
        {
            return $this->database->getData();
        }
    
        /**
         * 发送响应
         *
         * @param string $content
         */
        public function sendResponse($content)
        {
            $this->client->output($content);
        }
    }
```
#### **Colleague.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Mediator;
    
    /**
     * Colleague是一个抽象的同事类，但是它只知道中介者Mediator，而不知道其他同事
     */
    abstract class Colleague
    {
        /**
         * this ensures no change in subclasses
         *
         * @var MediatorInterface
         */
        private $mediator;
        
        /**
         * @param MediatorInterface $medium
         */
        public function __construct(MediatorInterface $medium)
        {
            $this->mediator = $medium;
        }
    
        // for subclasses
        protected function getMediator()
        {
            return $this->mediator;
        }
    }
```
#### **Subsystem/Client.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Mediator\Subsystem;
    
    use DesignPatterns\Behavioral\Mediator\Colleague;
    
    /**
     * Client是发起请求&获取响应的客户端
     */
    class Client extends Colleague
    {
        /**
         * request
         */
        public function request()
        {
            $this->getMediator()->makeRequest();
        }
    
        /**
         * output content
         *
         * @param string $content
         */
        public function output($content)
        {
            echo $content;
        }
    }
```
#### **Subsystem/Database.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Mediator\Subsystem;
    
    use DesignPatterns\Behavioral\Mediator\Colleague;
    
    /**
     * Database提供数据库服务
     */
    class Database extends Colleague
    {
        /**
         * @return string
         */
        public function getData()
        {
            return "World";
        }
    }
```
#### **Subsystem/Server.php**

```php
    <?php
    
    namespace DesignPatterns\Behavioral\Mediator\Subsystem;
    
    use DesignPatterns\Behavioral\Mediator\Colleague;
    
    /**
     * Server用于发送响应
     */
    class Server extends Colleague
    {
        /**
         * process on server
         */
        public function process()
        {
            $data = $this->getMediator()->queryDb();
            $this->getMediator()->sendResponse("Hello $data");
        }
    }
```
### **4、测试代码**

#### **Tests/MediatorTest.php**

```php
    <?php
    
    namespace DesignPatterns\Tests\Mediator\Tests;
    
    use DesignPatterns\Behavioral\Mediator\Mediator;
    use DesignPatterns\Behavioral\Mediator\Subsystem\Database;
    use DesignPatterns\Behavioral\Mediator\Subsystem\Client;
    use DesignPatterns\Behavioral\Mediator\Subsystem\Server;
    
    /**
     * MediatorTest tests hello world
     */
    class MediatorTest extends \PHPUnit\Framework\TestCase
    {
    
        protected $client;
    
        protected function setUp()
        {
            $media = new Mediator();
            $this->client = new Client($media);
            $media->setColleague(new Database($media), $this->client, new Server($media));
        }
    
        public function testOutputHelloWorld()
        {
            // 测试是否输出 Hello World :
            $this->expectOutputString('Hello World');
            // 正如你所看到的, Client, Server 和 Database 是完全解耦的
            $this->client->request();
        }
    }
```
### **5、总结**

中介者主要是通过中介对象来封装对象之间的关系，使之各个对象在不需要知道其他对象的具体信息情况下通过中介者对象来与之通信。同时通过引用中介者对象来减少系统对象之间关系，提高了对象的可复用和系统的可扩展性。但是就是因为中介者对象封装了对象之间的关联关系，导致中介者对象变得比较庞大，所承担的责任也比较多。它需要知道每个对象和他们之间的交互细节，如果它出问题，将会导致整个系统都会出问题。

[0]: http://laravelacademy.org/post/2894.html
[1]: http://laravelacademy.org/post/author/nonfu
[2]: http://laravelacademy.org/tags/%e4%b8%ad%e4%bb%8b%e8%80%85%e6%a8%a1%e5%bc%8f
[3]: ../img/Mediator-Design-Pattern-UML.png
[4]: http://laravelacademy.org/tags/php