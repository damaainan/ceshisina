## 教你在不使用框架的情况下也能写出现代化 PHP 代码

来源：[http://www.cnblogs.com/summerblue/p/8716407.html](http://www.cnblogs.com/summerblue/p/8716407.html)

时间 2018-04-04 11:44:00

我为你们准备了一个富有挑战性的事情。接下来你们将以 无  框架的方式开启一个项目之旅。
 
首先声明， 这篇并非又臭又长的反框架裹脚布文章。也不是推销 [非原创][1] 思想 。毕竟， 我们还将在接下来的开发之旅中使用其他框架开发者编写的辅助包。我对这个领域的创新也是持无可非议的态度。
 
这无关他人，而是关乎己身。作为一名开发者，它将有机会让你成长。
 
也许无框架开发令你受益匪浅的地方就是，可以从底层运作的层面中汲取丰富的知识。抛却依赖神奇的，帮你处理无法调试和无法真正理解的东西的框架，你将清楚的看到这一切是如何发生的。
 
很有可能下一份工作中，你并不能随心所以地选择框架开拓新项目。现实就是，在很多高价值，关键业务的 PHP 工作中均使用现有应用。 并且该应用程序是否构建在当前令人舒爽的 Laravel 或 Symfony 等流行框架中，亦或是陈旧过时的 CodeIgniter 或者 FuelPHP 中，更有甚者它可能广泛出现在令人沮丧的 [“面向包含体系结构” 的传统的 PHP 应用][2] 之中，所以 无  框架开发会在将来你所面临的 任何  PHP 项目中助你一臂之力。
 
上古时代， 因为 某些系统  不得不解释分发 HTTP 请求，发送 HTTP 响应，管理依赖关系，无框架开发就是痛苦的鏖战。缺乏行业标准必然意味着，框架中的这些组件高度耦合 。如果你从无框架开始，你终将难逃自建框架的命运。
 
时至今日，幸亏有 [PHP-FIG][3] 完成所有的自动加载和交互工作，无框架开发并非让你白手起家。各色供应商都有这么多优秀的可交互的软件包。把他们组合起来容易得超乎你的想象！
 
## PHP 是如何工作的？
 
在做其他事之前，搞清楚 PHP 如何与外界沟通是非常重要的。
 
PHP 以请求 / 响应为周期运行服务端应用程序。与你的应用程序的每一次交互——无论是来自浏览器，命令行还是 REST API ——都是作为请求进入应用程序的。 当接收到请求以后：
 

* 程序开始启动； 
* 开始处理请求； 
* 产生响应； 
* 接着，响应返回给产生请求的相应客户端； 
* 最后程序关闭。 
 

每一个请求都在重复以上的交互。
 
## 前端控制器
 
用这些知识把自己武装起来以后，就可以先从我们的前端控制器开始编写程序了。前端控制器是一个 PHP 文件，它处理程序的每一个请求。控制器是请求进入程序后遇到的第一个 PHP 文件，并且（本质上）也是响应走出你应用程序所经过的最后一个文件。
 
我们使用经典的 Hello, world!  作为例子来确保所有东西都正确连接上，这个例子由  [PHP 的内置服务器][4] 驱动。在你开始这样做之前，请确保你已经安装了 PHP7.1 或者更高版本。
 
创建一个含有`public`目录的项目，然后在该目录里面创建一个`index.php`文件，文件里面写入如下代码：
 
```php
<?php
declare(strict_types=1);

echo 'Hello, world!';
```
 
注意，这里我们声明了使用严格模式 —— 作为最佳实践，你应该在应用程序的 [每个 PHP 文件的开头][5] 都这样做。因为对从你后面来的开发者来说类型提示对 [调试和清晰的交流意图很重要][6] 。
 
使用命令行（比如 macOS 的终端）切换到你的项目目录并启动 PHP 的内置服务器。
 
```
php -S localhost:8080 -t public/
```
 
现在，在浏览器中打开 [http://localhost:8080/][7] 。是不是成功地看到了 "Hello, world!" 输出？
 
很好。接下来我们可以开始进入正题了！
 
## 自动加载与第三方包
 
当你第一次使用 PHP 时，你可能会在你的程序中使用`includes`或 `requires`语句来从其他 PHP 文件导入功能和配置。 通常，我们会避免这么干，因为这会使得其他人更难以遵循你的代码路径和理解依赖在哪里。这让调试成为了一个  **`真正的`**  噩梦。
 
解决办法是使用自动加载（autoloading）。 自动加载的意思是：当你的程序需要使用一个类， PHP 在调用该类的时候知道去哪里找到并加载它。虽然从 PHP 5 开始就可以使用这个特性了， 但是得益于 [PSR-0][8] （ 自动加载标准，后来被 [PSR-4][9] 取代），其使用率才开始有真正的提升。
 
我们可以编写自己的自动加载器来完成任务，但是由于我们将要使用的管理第三方依赖的 [Composer][10] 已经包含了一个完美的可用的自动加载器，那我们用它就行了。
 
确保你已经在你的系统上 [安装][11] 了 Composer。然后为此项目初始化 Composer：
 
```
composer init
```
 
这条命令通过交互式引导你创建`composer.json`配置文件。 一旦文件创建好了，我们就可以在编辑器中打开它然后向里面写入 `autoload`字段，使他看起来像这个样子（这确保了自动加载器知道从哪里找到我们项目中的类）：
 
```json
{
    "name": "kevinsmith/no-framework",
    "description": "An example of a modern PHP application bootstrapped without a framework.",
    "type": "project",
    "require": {},
    "autoload": {
        "psr-4": {
            "ExampleApp\\": "src/"
        }
    }
}
```
 
现在为此项目安装 composer，它引入了依赖（如果有的话），并为我们创建好了自动加载器：
 
```
composer install
```
 
更新`public/index.php`文件来引入自动加载器。在理想情况下，这将是你在程序当中使用的少数『包含』语句之一。
 
```php
<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

echo 'Hello, world!';
```
 
此时如果你刷新浏览器，你将不会看到任何变化。因为自动加载器没有修改或者输出任何数据，所以我们看到的是同样的内容。让我们把 Hello, world!  这个例子移动到一个已经自动加载的类里面看看它是如何运作的。
 
在项目根目录创建一个名为`src`的目录，然后在里面添加一个叫 `HelloWorld.php`的文件，写入如下代码：
 
```php
<?php
declare(strict_types=1);

namespace ExampleApp;

class HelloWorld
{
    public function announce(): void
    {
        echo 'Hello, autoloaded world!';
    }
}
```
 
现在到`public/index.php`里面用  `HelloWorld`类的 `announce`方法替换掉`echo`语句。
 
```php
// ...

require_once dirname(__DIR__) . '/vendor/autoload.php';

$helloWorld = new \ExampleApp\HelloWorld();
$helloWorld->announce();
```
 
刷新浏览器查看新的信息！
 
## 什么是依赖注入？
 
依赖注入是一种编程技术，每个依赖项都供给它需要的对象，而不是在对象外获得所需的信息或功能。
 
举个例子，假设应用中的类方法需要从数据库中读取。为此，你需要一个数据库连接。常用的技术就是创建一个全局可见的新连接。
 
```php
class AwesomeClass
{
    public function doSomethingAwesome()
    {
        $dbConnection = return new \PDO(
            "{$_ENV['type']}:host={$_ENV['host']};dbname={$_ENV['name']}",
            $_ENV['user'],
            $_ENV['pass']
        );

        // Make magic happen with $dbConnection
    }
}
```
 
但是这样做显得很乱，它把一个并非属于这里的职责置于此地---创建一个* 数据库连接对象 ，  检查凭证 ，   还有  处理一些连接失败的问题---它会导致应用中出现  大量*  重复代码。如果你尝试对这个类进行单元测试，会发现根本不可行。这个类和应用环境以及数据库高度耦合。
 
相反，为何不一开始就搞清楚你的类需要什么？我们只需要首先将 “PDO” 对象注入该类即可。
 
```php
class AwesomeClass
{
    private $dbConnection;

    public function __construct(\PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function doSomethingAwesome()
    {
        // Make magic happen with $this->dbConnection
    }
}
```
 
这样更简洁清晰易懂，且更不易产生 Bug。通过类型提示和依赖注入，该方法可以清楚准确地声明它要做的事情，而无需依赖外部调用去获取。在做单元测试的时候，我们可以很好地模拟数据库连接，并将其传入使用。
 
依赖注入 容器  是一个工具，你可以围绕整个应用程序来处理创建和注入这些依赖关系。容器并不需要能够使用依赖注入技术，但随着应用程序的增长并变得更加复杂，它将大有裨益。
 
我们将使用 PHP 中最受欢迎的 DI 容器之一：名副其实的 [PHP-DI][12] 。  （值得推荐的是它文档中的 [依赖注入另解][13]  可能会对读者有所帮助） 
 
## 依赖注入容器
 
现在我们已经安装了 Composer ，那么安装 PHP-DI 就轻而易举了，我们继续回到命令行来搞定它。
 
```
composer require php-di/php-di
```
 
修改`public/index.php`用来配置和构建容器。
 
```php
// ...

require_once dirname(__DIR__) . '/vendor/autoload.php';

$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions([
    \ExampleApp\HelloWorld::class => \DI\create(\ExampleApp\HelloWorld::class)
]);

$container = $containerBuilder->build();

$helloWorld = $container->get(\ExampleApp\HelloWorld::class);
$helloWorld->announce();
```
 
没啥大不了的。它仍是一个单文件的简单示例，你很容易能看清它是怎么运行的。
 
迄今为止, 我们只是在 [配置容器][14] ，所以我们必须  [显式地声明依赖关系][15] （而不是使用  [自动装配][16] 或  [注解][17] ），并且从容器中检索`HelloWorld`对象。
 
小贴士：自动装配在你开始构建应用程序的时候是一个很不错的特性，但是它隐藏了依赖关系，难以维护。 很有可能在接下里的岁月里， 另一个开发者在不知情的状况下引入了一个新库，然后就造就了多个库实现一个单接口的局面，这将会破坏自动装配，导致一系列让接手者很容易忽视的的不可见的问题。
 
尽量 [引入命名空间][18] ，可以增加代码的可读性。
 
```php
<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use ExampleApp\HelloWorld;
use function DI\create;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(false);
$containerBuilder->useAnnotations(false);
$containerBuilder->addDefinitions([
    HelloWorld::class => create(HelloWorld::class)
]);

$container = $containerBuilder->build();

$helloWorld = $container->get(HelloWorld::class);
$helloWorld->announce();
```
 
现在看来，我们好像是把以前已经做过的事情再拿出来小题大做。
 
毋需烦心，当我们添加其他工具来帮助我们引导请求时，容器就有用武之地了。它会在适当的时机下按需加载正确的类。
 
## 中间件
 
如果把你的应用想象成一个洋葱，请求从外部进入，到达洋葱中心，最后变成响应返回出去。那么中间件就是洋葱的每一层。它接收请求并且可以处理请求。要么把请求传递到更里层，要么向更外层返回一个响应（如果中间件正在检查请求不满足的特定条件，比如请求一个不存在的路由，则可能发生这种情况）。
 
如果请求通过了所有的层，那么程序就会开始处理它并把它转换为响应，中间件接收到响应的顺序与接收到请求的顺序相反，并且也能对响应做修改，然后再把它传递给下一个中间件。
 
下面是一些中间件用例的闪光点：
 

* 在开发环境中调试问题 
* 在生产环境中优雅的处理异常 
* 对传入的请求进行频率限制 
* 对请求传入的不支持资源类型做出响应 
* 处理跨域资源共享（CORS） 
* 将请求路由到正确的处理类 
 

那么中间件是实现这些功能的唯一方式吗？当然不是。但是中间件的实现使得你对请求 / 响应这个生命周期的理解更清晰。这也意味着你调试起来更简单，开发起来更快速。
 
我们将从上面列出的最后一条用例，也就是路由，当中获益。
 
## 路由
 
路由依靠传入的请求信息来确定应当由哪个类来处理它。(例如 URI`/products/purple-dress/medium`应该被  `ProductDetails::class`类接收处理，同时`purple-dress`和 `medium`作为参数传入)
 
在范例应用中，我们将使用流行的 [FastRoute][19] 路由，基于  [PSR-15兼容的中间件实现][20] 。
 
## 中间件调度器
 
为了让我们的应用可以和 FastRoute 中间件---以及我们安装的其他中间件协同工作---我们需要一个中间件调度器。
 
[PSR-15][21] 是为中间件和调度器定义接口的中间件标准（在规范中又称“请求处理器”），它允许各式各样的中间件和调度器互相交互。我们只需选择兼容 PSR-15 的调度器，这样就可以确保它能和任何兼容 PSR-15 的中间件协同工作。
 
我们先安装一个 [Relay][22] 作为调度器。
 
```
composer require relay/relay:2.x@dev
```
 
而且根据 PSR-15 的中间件标准要求实现可传递 [兼容 PSR-7 的 HTTP 消息][23] , 我们使用  [Zend Diactoros][24] 作为 PSR-7 的实现。
 
```
composer require zendframework/zend-diactoros
```
 
我们用 Relay 去接收中间件。
 
```php
// ...

use DI\ContainerBuilder;
use ExampleApp\HelloWorld;
use Relay\Relay;
use Zend\Diactoros\ServerRequestFactory;
use function DI\create;

// ...

$container = $containerBuilder->build();

$middlewareQueue = [];

$requestHandler = new Relay($middlewareQueue);
$requestHandler->handle(ServerRequestFactory::fromGlobals());
```
 
我们在第 16 行使用`ServerRequestFactory::fromGlobals()`把  [创建新请求的必要信息合并起来][25] 然后把它传给 Relay。 这正是`Request`进入我们中间件堆栈的起点。
 
现在我们继续添加 FastRoute 和请求处理器中间件。 ( FastRoute 确定请求是否合法，究竟能否被应用程序处理，然后请求处理器发送`Request`到路由配置表中已注册过的相应处理程序中)
 
```
composer require middlewares/fast-route middlewares/request-handler
```
 
然后我们给 Hello, world!  处理类定义一个路由。我们在此使用 `/hello`路由来展示基本 URI 之外的路由。
 
```php
// ...

use DI\ContainerBuilder;
use ExampleApp\HelloWorld;
use FastRoute\RouteCollector;
use Middlewares\FastRoute;
use Middlewares\RequestHandler;
use Relay\Relay;
use Zend\Diactoros\ServerRequestFactory;
use function DI\create;
use function FastRoute\simpleDispatcher;

// ...

$container = $containerBuilder->build();

$routes = simpleDispatcher(function (RouteCollector $r) {
    $r->get('/hello', HelloWorld::class);
});

$middlewareQueue[] = new FastRoute($routes);
$middlewareQueue[] = new RequestHandler();

$requestHandler = new Relay($middlewareQueue);
$requestHandler->handle(ServerRequestFactory::fromGlobals());
```
 
为了能运行，你还需要修改`HelloWorld`使其成为一个可调用的类， 也就是说  [这里类可以像函数一样被随意调用][26] .
 
```php
// ...

class HelloWorld
{
    public function __invoke(): void
    {
        echo 'Hello, autoloaded world!';
        exit;
    }
}
```
 
(注意在魔术方法`__invoke()`中加入`exit;`。 我们只需1秒钟就能搞定--只是不想让你遗漏这个事)
 
现在打开 [http://localhost:8080/hello][27] ，开香槟吧！
 
## 万能胶水
 
睿智的读者可能很快看出，虽然我们仍旧囿于配置和构建 DI 容器的藩篱之中，容器现在实际上对我们毫无用处。调度器和中间件在没有它的情况下也一样运作。
 
那它何时才能发挥威力？
 
嗯，如果---在实际应用程序中总是如此---`HelloWorld`类具有依赖关系呢?
 
我们来讲解一个简单的依赖关系，看看究竟发生了什么。
 
```php
// ...

class HelloWorld
{
    private $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public function __invoke(): void
    {
        echo "Hello, {$this->foo} world!";
        exit;
    }
}
```
 
刷新浏览器..
 
WOW！
 
看下这个`ArgumentCountError`.
 
发生这种情况是因为`HelloWorld`类在构造的时候需要注入一个字符串才能运行，在此之前它只能等着。  这  正是容器要帮你解决的痛点。
 
我们在容器中定义该依赖关系，然后将容器传给 `RequestHandler`去  [解决这个问题][28] .
 
```php
// ...

use Zend\Diactoros\ServerRequestFactory;
use function DI\create;
use function DI\get;
use function FastRoute\simpleDispatcher;

// ...

$containerBuilder->addDefinitions([
    HelloWorld::class => create(HelloWorld::class)
        ->constructor(get('Foo')),
    'Foo' => 'bar'
]);

$container = $containerBuilder->build();

// ...

$middlewareQueue[] = new FastRoute($routes);
$middlewareQueue[] = new RequestHandler($container);

$requestHandler = new Relay($middlewareQueue);
$requestHandler->handle(ServerRequestFactory::fromGlobals());
```
 
嗟夫！当刷新浏览器的时候， "Hello, bar world!"将映入你的眼帘！
 
## 正确地发送响应
 
是否还记得我之前提到过的位于`HelloWorld`类中的`exit`语句?
 
当我们构建代码时，它可以让我们简单粗暴的获得响应，但是它绝非输出到浏览器的最佳选择。这种粗暴的做法给`HelloWorld`附加了额外的响应工作---其实应该由其他类负责的---它会过于复杂的发送正确的头部信息和 [状态码][29] ，然后立刻退出了应用，使得 `HelloWorld` 之后  的中间件也无机会运行了。
 
记住，每个中间件都有机会在`Request`进入我们应用时修改它，然后 (以相反的顺序) 在响应输出时修改响应。 除了`Request`的通用接口， PSR-7 同样也定义了另外一种 HTTP 消息结构，以辅助我们在应用运行周期的后半部分之用：`Response`。（如果你想真正了解这些细节，请阅读 [HTTP 消息以及什么让 PSR-7 请求和响应标准如此之好][30] 。）
 
修改`HelloWorld`返回一个`Response`。
 
```php
// ...

namespace ExampleApp;

use Psr\Http\Message\ResponseInterface;

class HelloWorld
{
    private $foo;

    private $response;

    public function __construct(
        string $foo,
        ResponseInterface $response
    ) {
        $this->foo = $foo;
        $this->response = $response;
    }

    public function __invoke(): ResponseInterface
    {
        $response = $this->response->withHeader('Content-Type', 'text/html');
        $response->getBody()
            ->write("<html><head></head><body>Hello, {$this->foo} world!</body></html>");

        return $response;
    }
}
```
 
然后修改容器给`HelloWorld`提供一个新的`Response`对象。
 
```php
// ...

use Middlewares\RequestHandler;
use Relay\Relay;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use function DI\create;

// ...

$containerBuilder->addDefinitions([
    HelloWorld::class => create(HelloWorld::class)
        ->constructor(get('Foo'), get('Response')),
    'Foo' => 'bar',
    'Response' => function() {
        return new Response();
    },
]);

$container = $containerBuilder->build();

// ...
```
 
如果你现在刷新页面，会发现一片空白。我们的应用正在从中间件调度器返回正确的`Response`对象，但是... 肿么回事？
 
它啥都没干，就这样。
 
我们还需要一件东西来包装下：发射器。发射器位于应用程序和 Web 服务器（Apache，nginx等）之间，将响应发送给发起请求的客户端。它实际上拿到了`Response`对象并将其转化为 [服务端 API][31] 可理解的信息。
 
好消息！ 我们已经用来封装请求的 Zend Diactoros 包同样也内置了发送 PSR-7 响应的发射器。
 
值得注意的是，为了举例，我们只是对发射器的使用小试牛刀。虽然它们可能会更复杂点，真正的应用应该配置成自动化的流式发射器用来应对大量下载的情况， [Zend 博客展示了如何实现它][32] 。
 
修改`public/index.php`，用来从调度器那里接收 `Response`，然后传给发射器。
 
```php
// ...

use Relay\Relay;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;
use function DI\create;

// ...

$requestHandler = new Relay($middlewareQueue);
$response = $requestHandler->handle(ServerRequestFactory::fromGlobals());

$emitter = new SapiEmitter();
return $emitter->emit($response);
```
 
刷新浏览器，业务恢复了！这次我们用了一种更健壮的方式来处理响应。
 
以上代码的第 15 行是我们应用中请求/响应周期结束的地方，同时也是 web 服务器接管的地方。
 
## 总结
 
现在你已经获得了现代化的 PHP 代码。 仅仅 44 行代码，在几个被广泛使用，经过全面测试和拥有可靠互操作性的组件的帮助下，我们就完成了一个现代化 PHP 程序的引导。它兼容 [PSR-4][33] ，  [PSR-7][34] ， [PSR-11][35] 以及  [PSR-15][36] ，这意味着你可以使用自己选择的其他任一供应商对这些标准的实现，来构建自己的 HTTP 消息， DI 容器，中间件，还有中间件调度器。
 
我们深入理解了我们决策背后使用的技术和原理，但我更希望你能明白，在没有框架的情况下，引导一个新的程序是多么简单的一件事。或许更重要的是，我希望在有必要的时候你能更好的把这些技术运用到已有的项目中去。
 
你可以在 [这个例子的 GitHub 仓库][37] 上免费 fork 和下载它。
 
如果你正在寻找更高质量的解耦软件包资源，我衷心推荐你看看 [Aura][38] ，  [了不起的软件包联盟][39] ，  [Symfony 组件][40] ，  [Zend Framework 组件][41] ， [Paragon 计划的聚焦安全的库][42] ， 还有这个  [关于 PSR-15 中间件的清单][43] .
 
如果你想把这个例子的代码用到生产环境中， 你可能需要把路由和 [容器定义][44] 分离到它们各自的文件里面，以便将来项目复杂度提升的时候更好维护。我也建议  [实现 EmitterStack][45] 来更好的处理文件下载以及其他的大量响应。
 
如果有任何问题，疑惑或者建议，请 [给我留言][46] 。
 
更多现代化 PHP 知识，请前往 [Laravel / PHP 知识社区][47]
 


[1]: https://en.wikipedia.org/wiki/Not_invented_here
[2]: https://leanpub.com/mlaphp
[3]: https://www.php-fig.org/
[4]: http://php.net/manual/en/features.commandline.webserver.php
[5]: http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.strict
[6]: http://paul-m-jones.com/archives/6774
[7]: http://localhost:8080/
[8]: https://www.php-fig.org/psr/psr-0/
[9]: https://www.php-fig.org/psr/psr-4/
[10]: https://getcomposer.org/
[11]: https://getcomposer.org/doc/00-intro.md
[12]: http://php-di.org/
[13]: http://php-di.org/doc/understanding-di.html
[14]: http://php-di.org/doc/container-configuration.html#lightweight-container
[15]: http://php-di.org/doc/php-definitions.html
[16]: http://php-di.org/doc/autowiring.html
[17]: http://php-di.org/doc/annotations.html
[18]: http://php.net/manual/en/phpuage.namespaces.importing.php
[19]: https://github.com/nikic/FastRoute
[20]: https://github.com/middlewares/fast-route
[21]: https://www.php-fig.org/psr/psr-15/
[22]: https://github.com/relayphp/Relay.Relay
[23]: http://www.php-fig.org/psr/psr-7/
[24]: https://zendframework.github.io/zend-diactoros/
[25]: https://zendframework.github.io/zend-diactoros/usage/#marshaling-an-incoming-request
[26]: https://lornajane.net/posts/2012/phps-magic-__invoke-method-and-the-callable-typehint
[27]: http://localhost:8080/hello
[28]: https://github.com/middlewares/request-handler#options
[29]: https://httpstatuses.com/
[30]: https://mwop.net/blog/2015-01-26-psr-7-by-example.html
[31]: https://stackoverflow.com/a/9948058/1217620
[32]: https://framework.zend.com/blog/2017-09-14-diactoros-emitters.html
[33]: https://www.php-fig.org/psr/psr-4
[34]: https://www.php-fig.org/psr/psr-7
[35]: https://www.php-fig.org/psr/psr-11
[36]: https://www.php-fig.org/psr/psr-15
[37]: https://github.com/kevinsmith/no-framework
[38]: http://auraphp.com/
[39]: https://thephpleague.com/
[40]: https://symfony.com/components
[41]: https://zendframework.github.io/
[42]: https://paragonie.com/software
[43]: https://github.com/middlewares/awesome-psr15-middlewares
[44]: http://php-di.org/doc/php-definitions.html
[45]: https://framework.zend.com/blog/2017-09-14-diactoros-emitters.html
[46]: https://twitter.com/_KevinSmith
[47]: https://laravel-china.org/topics/9365
