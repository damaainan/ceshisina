## [PHP容器--Pimple运行流程浅析](https://segmentfault.com/a/1190000010018086)

## 需要具备的知识点

### 闭包

闭包和匿名函数在PHP5.3.0中引入的。

闭包是指：创建时封装周围状态的函数。即使闭包所处的环境不存在了，闭包中封装的状态依然存在。

> 理论上，闭包和匿名函数是不同的概念。但是PHP将其视作相同概念。  
> 实际上，闭包和匿名函数是伪装成函数的对象。他们是Closure类的实例。

闭包和字符串、整数一样，是一等值类型。

创建闭包：

    <?php
    $closure = function ($name) {
        return 'Hello ' . $name;
    };
    echo $closure('nesfo');//Hello nesfo
    var_dump(method_exists($closure, '__invoke'));//true

> 我们之所以能调用> $closure> 变量，是因为这个变量的值是一个闭包，而且闭包对象实现了> __invoke()> 魔术方法。只要变量名后有> ()> ,PHP就会查找并调用> __invoke()> 方法。

通常会把PHP闭包当作函数的回调使用。

array_map(), preg_replace_callback()方法都会用到回调函数，这是使用闭包的最佳时机！

举个例子：

    <?php
    $numbersPlusOne = array_map(function ($number) {
        return $number + 1;
    }, [1, 2, 3]);
    print_r($numbersPlusOne);

得到结果：

    [2, 3, 4]

在闭包出现之前，只能单独创建具名函数，然后使用名称引用那个函数。这么做，代码执行会稍微慢点，而且把回调的实现和使用场景隔离了。

    <?php
    function incrementNum ($number) {
        return $number + 1;
    }
    
    $numbersPlusOne = array_map('incrementNum', [1, 2, 3]);
    print_r($numbersPlusOne);

### SPL

#### ArrayAccess

实现ArrayAccess接口，可以使得object像array那样操作。ArrayAccess接口包含四个必须实现的方法：

```php
    interface ArrayAccess {
        //检查一个偏移位置是否存在 
        public mixed offsetExists ( mixed $offset  );
        
        //获取一个偏移位置的值 
        public mixed offsetGet( mixed $offset  );
        
        //设置一个偏移位置的值 
        public mixed offsetSet ( mixed $offset  );
        
        //复位一个偏移位置的值 
        public mixed offsetUnset  ( mixed $offset  );
    }
```
#### SplObjectStorage

SplObjectStorage类实现了以对象为键的映射（map）或对象的集合（如果忽略作为键的对象所对应的数据）这种数据结构。这个类的实例很像一个数组，但是它所存放的对象都是唯一。该类的另一个特点是，可以直接从中删除指定的对象，而不需要遍历或搜索整个集合。

### ::class语法

因为 ::class 表示是字符串。用 ::class 的好处在于 IDE 里面可以直接改名一个 class，然后 IDE 自动处理相关引用。  
同时，PHP 执行相关代码时，是不会先加载相关 class 的。

同理，代码自动化检查 inspect 也可以正确识别 class。

## Pimple容器流程浅析

Pimpl是php社区中比较流行的容器。代码不是很多，详见[https://github.com/silexphp/P...][0] 。

我们的应用可以基于Pimple开发：

```php
    namespace EasyWeChat\Foundation;
    
    use Pimple\Container;
    
    class Application extends Container
    {
        /**
         * Service Providers.
         *
         * @var array
         */
        protected $providers = [
            ServiceProviders\ServerServiceProvider::class,
            ServiceProviders\UserServiceProvider::class
        ];
    
        /**
         * Application constructor.
         *
         * @param array $config
         */
        public function __construct($config)
        {
            parent::__construct();
    
            $this['config'] = function () use ($config) {
                return new Config($config);
            };
    
            if ($this['config']['debug']) {
                error_reporting(E_ALL);
            }
    
            $this->registerProviders();
        }
    
        /**
         * Add a provider.
         *
         * @param string $provider
         *
         * @return Application
         */
        public function addProvider($provider)
        {
            array_push($this->providers, $provider);
    
            return $this;
        }
    
        /**
         * Set providers.
         *
         * @param array $providers
         */
        public function setProviders(array $providers)
        {
            $this->providers = [];
    
            foreach ($providers as $provider) {
                $this->addProvider($provider);
            }
        }
    
        /**
         * Return all providers.
         *
         * @return array
         */
        public function getProviders()
        {
            return $this->providers;
        }
    
        /**
         * Magic get access.
         *
         * @param string $id
         *
         * @return mixed
         */
        public function __get($id)
        {
            return $this->offsetGet($id);
        }
    
        /**
         * Magic set access.
         *
         * @param string $id
         * @param mixed  $value
         */
        public function __set($id, $value)
        {
            $this->offsetSet($id, $value);
        }
    }
```
如何使用我们的应用：

    $app = new Application([]);
    $user = $app->user;

之后我们就可以使用$user对象的方法了。我们发现其实并没有$this->user这个属性，但是可以直接使用。主要是这两个方法起的作用：

    public function offsetSet($id, $value){}
    public function offsetGet($id){}

下面我们将解释在执行这两句代码，Pimple做了什么。但在解释这个之前，我们先看看容器的一些核心概念。

### 服务提供者

服务提供者是连接容器与具体功能实现类的桥梁。服务提供者需要实现接口ServiceProviderInterface:

```php
    namespace Pimple;
    
    /**
     * Pimple service provider interface.
     *
     * @author  Fabien Potencier
     * @author  Dominik Zogg
     */
    interface ServiceProviderInterface
    {
        /**
         * Registers services on the given container.
         *
         * This method should only be used to configure services and parameters.
         * It should not get services.
         *
         * @param Container $pimple A container instance
         */
        public function register(Container $pimple);
    }
```
所有服务提供者必须实现接口register方法。

我们的应用里默认有2个服务提供者：

    protected $providers = [
        ServiceProviders\ServerServiceProvider::class,
        ServiceProviders\UserServiceProvider::class
    ];

以UserServiceProvider为例，我们看其代码实现：

```php
    namespace EasyWeChat\Foundation\ServiceProviders;
    
    use EasyWeChat\User\User;
    use Pimple\Container;
    use Pimple\ServiceProviderInterface;
    
    /**
     * Class UserServiceProvider.
     */
    class UserServiceProvider implements ServiceProviderInterface
    {
        /**
         * Registers services on the given container.
         *
         * This method should only be used to configure services and parameters.
         * It should not get services.
         *
         * @param Container $pimple A container instance
         */
        public function register(Container $pimple)
        {
            $pimple['user'] = function ($pimple) {
                return new User($pimple['access_token']);
            };
        }
    }
    
```
我们看到，该服务提供者的注册方法会给容器增加属性user，但是返回的不是对象，而是一个闭包。这个后面我再做讲解。

### 服务注册

我们在Application里构造函数里使用$this->registerProviders();对所有服务提供者进行了注册：

    private function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

仔细看，我们发现这里实例化了服务提供者，并调用了容器Pimple的register方法：

```php
    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $provider->register($this);
    
        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
    
        return $this;
    }
```
而这里调用了服务提供者的register方法，也就是我们在上一节中提到的：注册方法给容器增加了属性user，但返回的不是对象，而是一个闭包。

当我们给容器Pimple添加属性user的同时，会调用offsetSet($id, $value)方法：给容器Pimple的属性values、keys分别赋值：

    $this->values[$id] = $value;
    $this->keys[$id] = true;

到这里，我们还没有实例化真正提供实际功能的类EasyWeChat\User\Usr。但已经完成了服务提供者的注册工作。

当我们运行到这里：

    $user = $app->user;

会调用offsetGet($id)并进行实例化真正的类：

```php
    $raw = $this->values[$id];
    $val = $this->values[$id] = $raw($this);
    $this->raw[$id] = $raw;
    
    $this->frozen[$id] = true;
    
    return $val;
```
$raw获取的是闭包：

    $pimple['user'] = function ($pimple) {
        return new User($pimple['access_token']);
    };

$raw($this)返回的是实例化的对象User。也就是说只有实际调用才会去实例化具体的类。后面我们就可以通过$this['user']或者$this->user调用User类里的方法了。

当然，Pimple里还有很多特性值得我们去深入研究，这里不做过多讲解。

## 参考

1、PHP: 数组式访问 - Manual   
[http://php.net/manual/zh/clas...][1]  
2、利用 SPL 快速实现 Observer 设计模式  
[https://www.ibm.com/developer...][2]  
3、Pimple - A simple PHP Dependency Injection Container  
[https://pimple.sensiolabs.org/][3]  
4、Laravel源码里面为什么要用::class语法？ - 知乎  
[https://www.zhihu.com/questio...][4]  
5、Laravel 学习笔记 —— 神奇的服务容器 | Laravel China 社区 - 高品质的 Laravel 和 PHP 开发者社区 - Powered by PHPHub  
[https://laravel-china.org/top...][5]  
6、Pimple/README_zh.rst at master · 52fhy/Pimple  
[https://github.com/52fhy/Pimp...][6]

> 原文发布于博客园：[> http://www.cnblogs.com/52fhy/...][7]

[0]: https://github.com/silexphp/Pimple/blob/master/src/Pimple/Container.php
[1]: http://php.net/manual/zh/class.arrayaccess.php
[2]: https://www.ibm.com/developerworks/cn/opensource/os-cn-observerspl/
[3]: https://pimple.sensiolabs.org/
[4]: https://www.zhihu.com/question/52656676?from=profile_question_card
[5]: https://laravel-china.org/topics/789/laravel-learning-notes-the-magic-of-the-service-container
[6]: https://github.com/52fhy/Pimple/blob/master/README_zh.rst
[7]: http://www.cnblogs.com/52fhy/p/7102083.html