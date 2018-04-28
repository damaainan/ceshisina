## Pimple - 一个简单的 PHP 依赖注入容器

来源：[https://segmentfault.com/a/1190000014471794](https://segmentfault.com/a/1190000014471794)

链接  
[官网 WebSite][0]   
[GitHub - Pimple][1]

这是 Pimple 3.x 的文档。如果你正在使用 Pimple 1.x ，请查看 [Pimple 1.x 文档][2]。
阅读 Pimple 1.x 代码也是学习更多关于如何创建简单的依赖注入容器的好方法，新版本的 Pimple 更加关注性能。
Pimple - 一个简单的 PHP 依赖注入容器
## 安装

在你的项目中使用 Pimple 之前，将其添加到你的 composer.json 文件中：
`$ ./composer.phar require pimple/pimple ~3.0`另外，Pimple 也可作为 PHP C 扩展使用：

```
$ git clone https://github.com/silexphp/Pimple  
$ cd Pimple/ext/pimple  
$ phpize  
$ ./configure  
$ make  
$ make install  
```
## 使用

创建一个容器实例

```php
use Pimple\Container;

$container = new Container();
```

与许多其他依赖注入容器一样，Pimple 管理两种不同类型的数据：`服务`和`参数`### 定义服务

服务是一个对象，它可以作为一个庞大系统的一部分，一些服务的例子：数据库连接，模板引擎，邮件服务。几乎所有的全局对象都可以成为一项服务。

服务通过匿名函数定义，返回一个对象的实例

```php
// 定义一些服务
$container['session_storage'] = function ($c) {
    return new SessionStorage('SESSION_ID');
};

$container['session'] = function ($c) {
    return new Session($c['session_storage']);
};
```

请注意，匿名函数可以访问当前容器实例，从而允许引用其他服务或参数。
由于只有在获取对象时才创建对象，因此定义的顺序无关紧要。

使用定义的服务也非常简单：

```php
// 获取 session 对象
$session = $container['session'];

// 上述调用大致等同于以下代码：
// $storage = new SessionStorage('SESSION_ID');
// $session = new Session($storage);
```
### 定义工厂服务

默认情况下，每次获得服务时，Pimple 都会返回相同的实例。如果要为所有调用返回不同的实例，请使用`factory()`方法包装你的匿名函数。

```php
$container['session'] = $container->factory(function ($c) {
    return new Session($c['session_storage']);
});
```

现在，每次调用 $container['session'] 会返回一个新的 session 实例。
### 定义参数

定义参数允许从外部简化容器的配置并存储全局值

```php
// 定义一些参数
$container['cookie_name'] = 'SESSION_ID';
$container['session_storage_class'] = 'SessionStorage';
```

你现在可以很轻松的通过重写 session_storage_class 参数而不是重新定义服务定义来更改 cookie 名称。
### 保护参数

由于 Pimple 将匿名函数看作服务定义，因此需要使用`protect()`方法将匿名函数包装为参数：

```php
$container['random_func'] = $container->protect(function () {
    return rand();
});
```
### 修改已经定义的服务

在某些情况下，你可能需要在定义服务定义后修改它。在你的服务被创建后,你可以使用`extend()`方法添加额外的代码:

```php
$container['session_storage'] = function ($c) {
    return new $c['session_storage_class']($c['cookie_name']);
};

$container->extend('session_storage', function ($storage, $c) {
    $storage->...();

    return $storage;
});
```

第一个参数是要扩展的服务的名称，第二个参数是访问对象实例和容器的函数。
### 扩展容器

如果你反复使用相同的库，可能希望将一个项目中的某些服务重用到下一个项目，通过实现`Pimple\ServiceProviderInterface`接口，打包你的服务到 Provider 程序中

```php
use Pimple\Container;

class FooProvider implements Pimple\ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        // register some services and parameters
        // on $pimple
    }
}
```

然后，在容器上注册 Provider

```php
$pimple->register(new FooProvider());
```
### 获取服务创建方法

当你访问一个对象时，Pimple 自动调用你定义的匿名函数，为你创建服务对象。如果你想获得这个函数的原始访问权限，你可以使用`raw()`方法：

```php
$container['session'] = function ($c) {
    return new Session($c['session_storage']);
};

$sessionFunction = $container->raw('session');
```
## PSR-11 兼容性

由于历史原因，Container 类没有实现 PSR-11 ContainerInterface。然而，Pimple 提供了一个辅助类，它可以让你从 Pimple 容器类中解耦你的代码
### PSR-11 容器类
`Pimple\Psr11\Container`类允许你使用`Psr\Container\ContainerInterface`方法访问 Pimple 容器的内容：

```php
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

$container = new Container();
$container['service'] = function ($c) {
    return new Service();
};
$psr11 = new PsrContainer($container);

$controller = function (PsrContainer $container) {
    $service = $container->get('service');
};
$controller($psr11);

```
### 使用 PSR-11 服务定位

有时候，服务需要访问其他几个服务，而不必确定所有这些服务都将被实际使用。在这些情况下，你可能希望懒加载这些服务。

传统的解决方案是注入整个服务容器来获得真正需要的服务。但是，这不被推荐，因为它使服务对应用程序的其他部分的访问过于宽泛，并且隐藏了它们的实际依赖关系。
`ServiceLocator`旨在通过访问一组预定义的服务来解决此问题，同时仅在实际需要时才实例化它们。
它还允许你以不同于用于注册的名称提供服务。例如，你可能希望使用一个对象，该对象期望 EventDispatcherInterface 实例在名称 event_dispatcher 下可用，而你的事件分派器已在名称 dispatcher 下注册

```php
use Monolog\Logger;
use Pimple\Psr11\ServiceLocator;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class MyService
{
    /**
     * "logger" must be an instance of Psr\Log\LoggerInterface
     * "event_dispatcher" must be an instance of Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $services;

    public function __construct(ContainerInterface $services)
    {
        $this->services = $services;
    }
}

$container['logger'] = function ($c) {
    return new Monolog\Logger();
};
$container['dispatcher'] = function () {
    return new EventDispatcher();
};

$container['service'] = function ($c) {
    $locator = new ServiceLocator($c, array('logger', 'event_dispatcher' => 'dispatcher'));

    return new MyService($locator);
};
```
### 懒懒的引用一系列服务

在数组中传递一组服务实例可能会导致效率低下，因为如果使用集合的类只需要在稍后调用它的方法时对其进行迭代即可。如果集合中存储的其中一个服务与使用该服务的类之间存在循环依赖关系，则也会导致问题。
`ServiceIterator`类可以帮助你解决这些问题。它在实例化过程中接收服务名称列表，并在迭代时检索服务

```php
use Pimple\Container;
use Pimple\ServiceIterator;

class AuthorizationService
{
    private $voters;

    public function __construct($voters)
    {
        $this->voters = $voters;
    }

    public function canAccess($resource)
    {
        foreach ($this->voters as $voter) {
            if (true === $voter->canAccess($resource)) {
                return true;
            }
        }

        return false;
    }
}

$container = new Container();

$container['voter1'] = function ($c) {
    return new SomeVoter();
}
$container['voter2'] = function ($c) {
    return new SomeOtherVoter($c['auth']);
}
$container['auth'] = function ($c) {
    return new AuthorizationService(new ServiceIterator($c, array('voter1', 'voter2')));
}

```
## 谁在支持 Pimple ?

Pimple 是由 Symfony 框架的创建者 Fabien Potencier 写的 ，Pimple 是在 MIT 协议下发布的。

原创文章，欢迎转载。转载请注明出处，谢谢。
原文链接地址：[http://dryyun.com/2018/04/17/...][3]
作者: [dryyun][4]  
发表日期: 2018-04-17 14:30:29

[0]: https://pimple.symfony.com/
[1]: https://github.com/silexphp/Pimple
[2]: https://github.com/silexphp/Pimple/tree/1.1
[3]: http://dryyun.com/2018/04/17/php-pimple
[4]: https://dryyun.com/