# Laravel 核心——IoC 服务容器 

5个月前 ⋅ 1755 ⋅ 42 ⋅ 3 

## **服务容器**[#][0]

- - -

在说 Ioc 容器之前，我们需要了解什么是 Ioc 容器。

> Laravel 服务容器是一个用于管理类依赖和执行依赖注入的强大工具。

在理解这句话之前，我们需要先了解一下服务容器的来龙去脉： [laravel神奇的服务容器][1]。这篇博客告诉我们，服务容器就是工厂模式的升级版，对于传统的工厂模式来说，虽然解耦了对象和外部资源之间的关系，但是工厂和外部资源之间却存在了耦和。而服务容器在为对象创建了外部资源的同时，又与外部资源没有任何关系，这个就是 Ioc 容器。  
  
所谓的依赖注入和控制反转: [依赖注入和控制反转][2]，就是

> 只要不是由内部生产（比如初始化、构造函数 __construct 中通过工厂方法、自行手动 new 的），而是由外部以参数或其他形式注入的，都属于依赖注入（DI）

也就是说：

> 依赖注入是从应用程序的角度在描述，可以把依赖注入描述完整点：应用程序依赖容器创建并注入它所需要的外部资源；

> 控制反转是从容器的角度在描述，描述完整点：容器控制应用程序，由容器反向的向应用程序注入应用程序所需要的外部资源。

## **Laravel中的服务容器**[#][3]

- - -

Laravel服务容器主要承担两个作用：绑定与解析，服务容器的结构如下：  


![Markdown][4]

### **绑定**[#][5]

- - -

所谓的绑定就是将接口与实现建立对应关系。几乎所有的服务容器绑定都是在服务提供者中完成，也就是在服务提供者中绑定。

> 如果一个类没有基于任何接口那么就没有必要将其绑定到容器。容器并不需要被告知如何构建对象，因为它会使用 PHP 的反射服务自动解析出具体的对象。

也就是说，如果需要依赖注入的外部资源如果没有接口，那么就不需要绑定，直接利用服务容器进行解析就可以了，服务容器会根据类名利用反射对其进行自动构造。

#### **bind绑定**[#][6]

绑定有多种方法，首先最常用的是bind函数的绑定：

* 绑定自身
```
    $this->app->bind('App\Services\RedisEventPusher', null);
```
* 绑定闭包
```
    $this->app->bind('HelpSpot\API', function ($app) {
    return new HelpSpot\API();
    });//闭包直接提供实现方式
    
    $this->app->bind('HelpSpot\API', function ($app) {
    return new HelpSpot\API($app->make('HttpClient'));
    });//需要依赖注入
```
* 绑定接口
```
    $this->app->bind(
    'App\Contracts\EventPusher',
    'App\Services\RedisEventPusher'
    );
```
这三种绑定方式中，第一种绑定自身一般用于绑定单例。

#### **singleton绑定**[#][7]

singleton 方法绑定一个只需要解析一次的类或接口到容器，然后接下来对容器的调用将会返回同一个实例：

    $this->app->singleton('HelpSpot\API', function ($app) {
        return new HelpSpot\API($app->make('HttpClient'));
    });

#### **instance绑定**[#][8]

我们还可以使用 instance 方法绑定一个已存在的对象实例到容器，随后调用容器将总是返回给定的实例：

      $api = new HelpSpot\API(new HttpClient);
      $this->app->instance('HelpSpot\Api', $api);

#### **Context绑定**[#][9]

有时侯我们可能有两个类使用同一个接口，但我们希望在每个类中注入不同实现，例如，两个控制器依赖 Illuminate\Contracts\Filesystem\Filesystem 契约的不同实现。Laravel 为此定义了简单、平滑的接口：

    use Illuminate\Support\Facades\Storage;
    use App\Http\Controllers\VideoController;
    use App\Http\Controllers\PhotoControllers;
    use Illuminate\Contracts\Filesystem\Filesystem;
    
    $this->app->when(StorageController::class)
              ->needs(Filesystem::class)
              ->give(function () {
                Storage::class
              });//提供类名
    
    $this->app->when(PhotoController::class)
              ->needs(Filesystem::class)
              ->give(function () {
                 return new Storage();
              });//提供实现方式
    
    $this->app->when(VideoController::class)
              ->needs(Filesystem::class)
              ->give(function () {
                return new Storage($app->make(Disk::class));
              });//需要依赖注入

#### **原始值绑定**[#][10]

我们可能有一个接收注入类的类，同时需要注入一个原生的数值比如整型，可以结合上下文轻松注入这个类需要的任何值：

    $this->app->when('App\Http\Controllers\UserController')
              ->needs('$variableName')
              ->give($value);

#### **数组绑定**[#][11]

    app()['service'] = function(){
        return new Service();
    };

#### **标签绑定**[#][12]

少数情况下，我们需要解析特定分类下的所有绑定，例如，你正在构建一个接收多个不同 Report 接口实现的报告聚合器，在注册完 Report 实现之后，可以通过 tag 方法给它们分配一个标签：

    $this->app->bind('SpeedReport', function () {
      //
    });
    
    $this->app->bind('MemoryReport', function () {
      //
    });
    
    $this->app->tag(['SpeedReport', 'MemoryReport'], 'reports');

这些服务被打上标签后，可以通过 tagged 方法来轻松解析它们：

    $this->app->bind('ReportAggregator', function ($app) {
        return new ReportAggregator($app->tagged('reports'));
    });

#### **extend扩展**[#][13]

extend是在当原来的类被注册或者实例化出来后，可以对其进行扩展：

    public function testExtendInstancesArePreserved()
    {
        $container = new Container;
        $container->bind('foo', function () {
            $obj = new StdClass;
            $obj->foo = 'bar';
    
            return $obj;
        });
    
        $obj = new StdClass;
        $obj->foo = 'foo';
        $container->instance('foo', $obj);
    
        $container->extend('foo', function ($obj, $container) {
            $obj->bar = 'baz';
            return $obj;
        });
    
        $container->extend('foo', function ($obj, $container) {
            $obj->baz = 'foo';
            return $obj;
        });
    
        $this->assertEquals('foo', $container->make('foo')->foo);
        $this->assertEquals('baz', $container->make('foo')->bar);
        $this->assertEquals('foo', $container->make('foo')->baz);
    }

#### **Rebounds与Rebinding**[#][14]

绑定是针对接口的，是为接口提供实现方式的方法。我们可以对接口在不同的时间段里提供不同的实现方法，一般来说，对同一个接口提供新的实现方法后，不会对已经实例化的对象产生任何影响。但是在一些场景下，在提供新的接口实现后，我们希望对已经实例化的对象重新做一些改变，这个就是 rebinding 函数的用途。  
下面就是一个例子：

    abstract class Car
    {
        public function __construct(Fuel $fuel)
        {
            $this->fuel = $fuel;
        }
    
        public function refuel($litres)
        {
            return $litres * $this->fuel->getPrice();
        }
    
        public function setFuel(Fuel $fuel)
        {
            $this->fuel = $fuel;
        }
    
    }
    
    class JeepWrangler extends Car
    {
      //
    }
    
    interface Fuel
    {
        public function getPrice();
    }
    
    class Petrol implements Fuel
    {
        public function getPrice()
        {
            return 130.7;
        }
    }

我们在服务容器中是这样对car接口和fuel接口绑定的：

    $this->app->bind('fuel', function ($app) {
        return new Petrol;
    });
    
    $this->app->bind('car', function ($app) {
        return new JeepWrangler($app['fuel']);
    });
    
    $this->app->make('car');

如果car被服务容器解析实例化成对象之后，有人修改了 fuel 接口的实现，从 Petrol 改为 PremiumPetrol：

    $this->app->bind('fuel', function ($app) {
        return new PremiumPetrol;
    });

由于 car 已经被实例化，那么这个接口实现的改变并不会影响到 car 的实现，假若我们想要 car 的成员变量 fuel 随着 fuel 接口的变化而变化，我们就需要一个回调函数，每当对 fuel 接口实现进行改变的时候，都要对 car 的 fuel 变量进行更新，这就是 rebinding 的用途：

    $this->app->bindShared('car', function ($app) {
        return new JeepWrangler($app->rebinding('fuel', function ($app, $fuel) {
            $app['car']->setFuel($fuel);
        }));
    });

### **服务别名**[#][15]

- - -

#### **什么是服务别名**[#][16]

在说服务容器的解析之前，需要先说说服务的别名。什么是服务别名呢？不同于上一个博客中提到的 Facade 门面的别名(在 config/app 中定义)，这里的别名服务绑定名称的别名。通过服务绑定的别名，在解析服务的时候，跟不使用别名的效果一致。别名的作用也是为了同时支持全类型的服务绑定名称以及简短的服务绑定名称考虑的。  
  
通俗的讲，假如我们想要创建 auth 服务，我们既可以这样写：

    $this->app->make('auth')

又可以写成：

    $this->app->make('\Illuminate\Auth\AuthManager::class')

还可以写成

    $this->app->make('\Illuminate\Contracts\Auth\Factory::class')

后面两个服务的名字都是 auth 的别名，使用别名和使用 auth 的效果是相同的。

#### **服务别名的递归**[#][17]

需要注意的是别名是可以递归的：

    app()->alias('service', 'alias_a');
    app()->alias('alias_a', 'alias_b');
    app()-alias('alias_b', 'alias_c');

会得到：

    'alias_a' => 'service'
    'alias_b' => 'alias_a'
    'alias_c' => 'alias_b'

#### **服务别名的实现**[#][18]

那么这些别名是如何加载到服务容器里面的呢？实际上，服务容器里面有个 aliases 数组：

    $aliases = [
      'app' => [\Illuminate\Foundation\Application::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class],
      'auth' => [\Illuminate\Auth\AuthManager::class, \Illuminate\Contracts\Auth\Factory::class],
      'auth.driver' => [\Illuminate\Contracts\Auth\Guard::class],
      'blade.compiler' => [\Illuminate\View\Compilers\BladeCompiler::class],
      'cache' => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
    ...
    ]

而服务容器的初始化的过程中，会运行一个函数：

    public function registerCoreContainerAliases()
    {
      foreach ($aliases as $key => $aliases) {
        foreach ($aliases as $alias) {
          $this->alias($key, $alias);
        }
      }
    }
    
    public function alias($abstract, $alias)
    {
      $this->aliases[$alias] = $abstract;
    
      $this->abstractAliases[$abstract][] = $alias;
    }
    

加载后，服务容器的aliases和abstractAliases数组：

    $aliases = [
      'Illuminate\Foundation\Application' = "app"
      'Illuminate\Contracts\Container\Container' = "app"
      'Illuminate\Contracts\Foundation\Application' = "app"
      'Illuminate\Auth\AuthManager' = "auth"
      'Illuminate\Contracts\Auth\Factory' = "auth"
      'Illuminate\Contracts\Auth\Guard' = "auth.driver"
      'Illuminate\View\Compilers\BladeCompiler' = "blade.compiler"
      'Illuminate\Cache\CacheManager' = "cache"
      'Illuminate\Contracts\Cache\Factory' = "cache"
      ...
    ］
    $abstractAliases = [
      app = {array} [3]
      0 = "Illuminate\Foundation\Application"
      1 = "Illuminate\Contracts\Container\Container"
      2 = "Illuminate\Contracts\Foundation\Application"
      auth = {array} [2]
      0 = "Illuminate\Auth\AuthManager"
      1 = "Illuminate\Contracts\Auth\Factory"
      auth.driver = {array} [1]
      0 = "Illuminate\Contracts\Auth\Guard"
      blade.compiler = {array} [1]
      0 = "Illuminate\View\Compilers\BladeCompiler"
      cache = {array} [2]
      0 = "Illuminate\Cache\CacheManager"
      1 = "Illuminate\Contracts\Cache\Factory"
      ...
    ]

### **服务解析**[#][19]

- - -

#### **make 解析**[#][20]

有很多方式可以从容器中解析对象，首先，你可以使用 make 方法，该方法接收你想要解析的类名或接口名作为参数：

      $fooBar = $this->app->make('HelpSpot\API');

如果你所在的代码位置访问不了 $app 变量，可以使用辅助函数resolve：

    $api = resolve('HelpSpot\API');

#### **自动注入**[#][21]

    namespace App\Http\Controllers;
    
    use App\Users\Repository as UserRepository;
    
    class UserController extends Controller{
      /**
      * 用户仓库实例
      */
      protected $users;
    
      /**
      * 创建一个控制器实例
      *
      * @param UserRepository $users 自动注入
      * @return void
      */
      public function __construct(UserRepository $users)
      {
        $this->users = $users;
      }
    }

#### **call 方法注入**[#][22]

make 解析是服务容器进行解析构建类对象时所用的方法，在实际应用中，还有另外一个需求，那就是当前已经获取了一个类对象，我们想要调用它的一个方法函数，这时发现这个方法中参数众多，如果一个个的 make 会比较繁琐，这个时候就要用到 call 解析了。我们可以看这个例子：

    class TaskRepository{
    
        public function testContainerCall(User $user,Task $task){
            $task->where('user_id','!=',0)
                ->orderBy('created_at')
                ->get();
    
            $user->orderBy('created_at')
                ->get();
        }
    
        public static function testContainerCallStatic(User $user,Task $task){
            $task->where('user_id','!=',0)
                ->orderBy('created_at')
                ->get();
    
            $user->orderBy('created_at')
                ->get();
        }
    
        public function testCallback(){
            echo 'call callback successfully!';
        }
    
        public function testDefaultMethod(){
            echo 'default Method successfully!';
        }
    }

##### **静态方法注入**[#][23]

服务容器的 call 解析主要依靠 call_user_func_array() 函数，关于这个函数可以查看 [Laravel学习笔记之Callback Type - 来生做个漫画家][24]，这个函数对类中的静态函数和非静态函数有一些区别，对于静态函数来说：

    class ContainerCallTest
    {
        public function testContainerCallStatic(){
            App::call(TaskRepository::class.'@testContainerCallStatic');
            App::call(TaskRepository::class.'::testContainerCallStatic');
            App::call([TaskRepository::class,'testContainerCallStatic']);
        }
    }

服务容器调用类的静态方法有三种，注意第三种使用数组的形式，数组中可以直接传类名 TaskRepository::class；

##### **非静态方法注入**[#][25]

对于类的非静态方法：

    class ContainerCallTest
    {
        public function testContainerCall(){
            $taskRepo = new TaskRepository();
            App::call(TaskRepository::class.'@testContainerCall');
            App::call([$taskRepo,'testContainerCall']);
        }
    }

我们可以看到非静态方法只有两种调用方式，而且第二种数组传递的参数是类对象，原因就是 call_user_func_array函数的限制，对于非静态方法只能传递对象。

##### **bindmethod 方法绑定**[#][26]

服务容器还有一个 bindmethod 的方法，可以绑定类的一个方法到自定义的函数：

    public function testContainCallMethodBind(){
    
        App::bindMethod(TaskRepository::class.'@testContainerCallStatic',function () {
             $taskRepo = new TaskRepository();
             $taskRepo->testCallback();
        });
    
        App::call(TaskRepository::class.'@testContainerCallStatic');
        App::call(TaskRepository::class.'::testContainerCallStatic');
        App::call([TaskRepository::class,'testContainerCallStatic']);
    
        App::bindMethod(TaskRepository::class.'@testContainerCall',function (TaskRepository $taskRepo) { $taskRepo->testCallback(); });
    
        $taskRepo = new TaskRepository();
        App::call(TaskRepository::class.'@testContainerCall');
        App::call([$taskRepo,'testContainerCall']);
    }

从结果上看，bindmethod 不会对静态的第二种解析方法（ :: 解析方式）起作用，对于其他方式都会调用绑定的函数。

##### **默认函数注入**[#][27]

    public function testContainCallDefultMethod(){
    
        App::call(TaskRepository::class,[],'testContainerCall');
    
        App::call(TaskRepository::class,[],'testContainerCallStatic');
    
        App::bindMethod(TaskRepository::class.'@testContainerCallStatic',function () {
            $taskRepo = new TaskRepository();
            $taskRepo->testCallback();
        });
    
        App::bindMethod(TaskRepository::class.'@testContainerCall',function (TaskRepository $taskRepo) {  $taskRepo->testCallback(); });
    
        App::call(TaskRepository::class,[],'testContainerCall');
    
        App::call(TaskRepository::class,[],'testContainerCallStatic');
    
    }

值得注意的是，这种默认函数注入的方法使得非静态的方法也可以利用类名去调用，并不需要对象。默认函数注入也回受到 bindmethod 函数的影响。

#### **数组解析**[#][28]

      app()['service'];

#### **app($service)的形式**[#][29]

      app('service');

### **服务容器事件**[#][30]

- - -

每当服务容器解析一个对象时就会触发一个事件。你可以使用 resolving 方法监听这个事件：

    $this->app->resolving(function ($object, $app) {
      // 解析任何类型的对象时都会调用该方法...
    });
    $this->app->resolving(HelpSpot\API::class, function ($api, $app) {
      // 解析「HelpSpot\API」类型的对象时调用...
    });
    $this->app->afterResolving(function ($object, $app) {
      // 解析任何类型的对象后都会调用该方法...
    });
    $this->app->afterResolving(HelpSpot\API::class, function ($api, $app) {
      // 解析「HelpSpot\API」类型的对象后调用...
    });

服务容器每次解析对象的时候，都会调用这些通过 resolving 和 afterResolving 函数传入的闭包函数，也就是触发这些事件。  
注意：如果是单例，则只在解析时会触发一次

[0]: #服务容器
[1]: https://www.insp.top/learn-laravel-container
[2]: http://blog.csdn.net/doris_crazy/article/details/18353197
[3]: #Laravel中的服务容器
[4]: http://i1.piimg.com/593560/2f28f1762b1b09f9.png
[5]: #绑定
[6]: #bind绑定
[7]: #singleton绑定
[8]: #instance绑定
[9]: #Context绑定
[10]: #原始值绑定
[11]: #数组绑定
[12]: #标签绑定
[13]: #extend扩展
[14]: #Rebounds与Rebinding
[15]: #服务别名
[16]: #什么是服务别名
[17]: #服务别名的递归
[18]: #服务别名的实现
[19]: #服务解析
[20]: #make-解析
[21]: #自动注入
[22]: #call-方法注入
[23]: #静态方法注入
[24]: https://segmentfault.com/a/1190000006981167
[25]: #非静态方法注入
[26]: #bindmethod-方法绑定
[27]: #默认函数注入
[28]: #数组解析
[29]: #appservice的形式
[30]: #服务容器事件