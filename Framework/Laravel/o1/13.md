## [Laravel 5.4 入门系列 13. 终篇： 小白也能看懂的 Laravel 核心概念讲解](https://segmentfault.com/a/1190000009171779)


## 自动依赖注入

什么是依赖注入，用大白话将通过类型提示的方式向函数传递参数。

### 实例 1

首先，定义一个类:

    /routes/web.php
    class Bar {}

假如我们在其他地方要使用到 Bar 提供的功能（服务），怎么办，直接传入参数即可：

    /routes/web.php
    Route::get('bar', function(Bar $bar) {
        dd($bar);
    });

访问 /bar，显示 $bar 的实例:

    Bar {#272}

也就是说，我们不需要先对其进行实例！如果学过 PHP 的面向对象，都知道，正常做法是这样:

    class Bar {}
    $bar = new Bar();
    dd($bar);

### 实例 2

可以看一个稍微复杂的例子：

    class Baz {}
    class Bar 
    {
        public $baz;
    
        public function __construct(Baz $baz)
        {
            $this->baz = $baz;
        }
        
    }
    $baz = new Baz();
    $bar = new Bar($baz);
    dd($bar);

为了在 Bar 中能够使用 Baz 的功能，我们需要实例化一个 Baz，然后在实例化 Bar 的时候传入 Baz 实例。

在 Laravel 中，不仅仅可以自动注入 Bar，也可以自动注入 Baz:

    /routes/web.php
    class Baz {}
    class Bar 
    {
        public $baz;
    
        public function __construct(Baz $baz)
        {
            $this->baz = $baz;
        }
        
    }
    
    Route::get('bar', function(Bar $bar) {
           dd($bar->baz);
    });

显示结果:

    Baz {#276}

### 小结

通过上述两个例子，可以看出，在 Laravel 中，我们要在类或者函数中使用其他类体用的服务，只需要通过类型提示的方式传递参数，而 Laravel 会自动帮我们去寻找响对应的依赖。

那么，Laravel 是如何完成这项工作的呢？答案就是通过服务容器。

## 服务容器

### 什么是服务容器

服务容器，很好理解，就是装着各种服务实例的特殊类。可以通过「去餐馆吃饭」来进行类比：

* 吃饭 - 使用服务，即调用该服务的地方
* 饭 - 服务
* 盘子 - 装饭的容器，即服务容器
* 服务员 - 服务提供者，负责装饭、上饭

这个过程在 Laravel 中如何实现呢？

* 饭

定义 Rice 类：

    /app/Rice.php
    <?php
    
    namespace App;
    
    class Rice
    {
        public function food()
        {
            return '香喷喷的白米饭';
        }
    }

* 把饭装盘子
在容器中定义了名为 rice 的变量（你也可以起其他名字，比如 rice_container），绑定了 Food 的实例：

```
    app()->bind('rice', function (){
        return new \App\Rice();
    });
```

也可以写成:

    app()->bind('rice',\App\Rice::class);

现在，吃饭了，通过 make 方法提供吃饭的服务：

    Route::get('eat', function() {
           
           return app()->make('rice')->food(); 
           // 或者 return resolve('rice')->food()；
    
    });

make 方法传入我们刚才定义的变量名即可调用该服务。

访问 /eat，返回 香喷喷的白米饭。

为了方便起见，我们在路由文件中直接实现了该过程，相当于自给自足。但是服务通常由服务提供者来管理的。

因此，我们可以让 AppServiceProvider 这个服务员来管理该服务：

    /app/Providers/AppServiceProvider.php
    namespace App\Providers;
    
    public function register()
    {
        $this->app->bind('food_container',Rice::class);
    }

更为常见的是，我们自己创建一个服务员：

    $ php artisan make:provider RiceServiceProvider

注册:

    /app/Providers/RiceServiceProvider.php
    <?php
    
    use App\Rice;
    public function register()
    {
        $this->app->bind('rice',Rice::class);
    }

这里定义了 register() 方法，但是还需要调用该方法才能真正绑定服务到容器，因此，需要将其添加到 providers 数组中：

    /config/app.php
    'providers' => [
       App\Providers\RiceServiceProvider::class,
    ],

这一步有何作用呢？Laravel 在启动的时候会访问该文件，然后调用里面的所有服务提供者的 register() 方法，这样我们的服务就被绑定到容器中了。

### 小结

通过上述的例子，基本上可以理解服务容器和服务提供者的使用。当然了，我们更为常见的还是使用类型提示来传递参数：

    use App\Rice;
    
    Route::get('eat', function(Rice $rice) {
           return $rice->food();
    
    });

在本例中，使用自动依赖注入即可。不需要在用 bind 来手动绑定以及 make 来调用服务。那么，为什么还需要 bind 和 make 呢？ make 比较好理解，我们有一些场合 Laravel 不能提供自动解析，那么这时候手动使用 make 解析就可以了，而 bind 的学问就稍微大了点，后面将会详细说明。

## 门面

门面是什么，我们回到刚才的「吃饭」的例子:

    Route::get('eat', function(Rice $rice) {
           return $rice->food();
    
    });

在 Laravel，通常还可以这么写:

    Route::get('eat', function() {
           return Rice::food();
    });

或者

    Route::get('eat', function() {
           return rice()->food();
    });

那么，Laravel 是如何实现的呢？答案是通过门面。

### 门面方法实现

先来实现 Rice::food()，只需要一步：

    /app/RiceFacade.php
    <?php 
    
    namespace App;
    use Illuminate\Support\Facades\Facade;
    
    class RiceFacade extends Facade
    {
       
        protected static function getFacadeAccessor()
        {
            return 'rice';
        }
    }

现在，RiceFacade 就代理了 Rice 类了，这就是门面的本质了。我们就可以直接使用：

    Route::get('eat', function() {
    
        dd(\App\RiceFacade::food());
    
    });

因为 \App\RiceFacade 比较冗长，我们可以用 php 提供的 class_alias 方法起个别名吧：

    /app/Providers/RiceServiceProvider.php
    public function register()
    {  
       $this->app->bind('rice',\App\Rice::class);
       class_alias(\App\RiceFacade::class, 'Rice');
    }

这样做的话，就实现了一开始的用法：

    Route::get('eat', function() {
           return Rice::food();
    });

看上去就好像直接调用了 Rice 类，实际上，调用的是 RiceFacade 类来代理，因此，个人觉得Facade 翻译成假象比较合适。

最后，为了便于给代理类命名，Laravel 提供了统一命名别名的地方：

    /config/app.php
    
    'aliases' => [
    
        'Rice' => \App\RiceFacade::class,
    
    ],

### 门面实现过程分析

首先：

    Rice::food();

因为 Rice 是别名，所以实际上执行的是:

    \App\RiceFacade::food()

但是我们的 RiceFacade 类里面并没有定义静态方法 food 啊？怎么办呢？直接抛出异常吗？不是，在 PHP 里，如果访问了不可访问的静态方法，会先调用 __callstatic,所以执行的是:

    \App\RiceFacade::__callStatic()

虽然我们在 RiceFacade 中没有定义，但是它的父类 Facade 已经定义好了：

    /vendor/laravel/framework/src/Illuminate/Support/Facades/Facade.php
    public static function __callStatic($method, $args)
    {   
         
         // 实例化  Rice {#270}
        $instance = static::getFacadeRoot();
        
         // 实例化失败，抛出异常
        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }
        
         // 调用该实例的方法
        return $instance->$method(...$args);
    }

主要工作就是第一步实例化:

    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
        // 本例中：static::resolveFacadeInstance('rice')
    }

进一步查看 resolveFacadeInstance() 方法：

     protected static function resolveFacadeInstance($name)
        {   
              // rice 是字符串，因此跳过该步骤
            if (is_object($name)) {
                return $name;
            }
             
             // 是否设置了 `rice` 实例
            if (isset(static::$resolvedInstance[$name])) {
                return static::$resolvedInstance[$name];
            }
             
            return static::$resolvedInstance[$name] = static::$app[$name];
        }

第一步比较好理解，如果我们之前在 RiceFacade 这样写：

    protected static function getFacadeAccessor()
    {
    
        return new \App\Rice;
    
    }

那么就直接返回 Rice 实例了，这也是一种实现方式。

主要难点在于最后这行：

    return static::$resolvedInstance[$name] = static::$app[$name];

看上去像是在访问 $app数组，实际上是使用 数组方式来访问对象，PHP 提供了这种访问方式接口，而 Laravel 实现了该接口。

也就是说，$app 属性其实就是对 Laravel 容器的引用，因此这里实际上就是访问容器上名为 rice 的对象。而我们之前学习容器的时候，已经将 rice 绑定了 Rice 类：

    public function register()
    {  
       $this->app->bind('rice',\App\Rice::class);
       // class_alias(\App\RiceFacade::class, 'Rice');
    }

所以，其实就是返回该类的实例了。懂得了服务容器和服务提供者，理解门面也就不难了。

### 辅助方法实现

辅助方法的实现，更简单了。不就是把 app->make('rice') 封装起来嘛：

    /vendor/laravel/framework/src/Illuminate/Foundation/helpers.php
    if (! function_exists('rice')) {
      
        function rice()
        {   
            return app()->make('rice');
            // 等价于 return app('rice');
            // 等价于 return app()['rice'];
        }
    } 

然后我们就可以使用了:

    Route::get('eat', function() {
    
        dd(rice()->food());
    });

### 小结

Laravel 提供的三种访问类的方式：

* 依赖注入：通过类型提示的方式实现自动依赖注入
* 门面：通过代理来访问类
* 辅助方法：通过方法的方式来访问类

本质上，这三种方式都是借助于服务容器和服务提供者来实现。那么，服务容器本身有什么好处呢？我们接下来着重介绍下。

## IOC

### 不好的实现

我们来看另外一个例子（为了方便测试，该例子都写在路由文件中），假设有三种类型的插座：USB、双孔、三孔插座，分别提供插入充电的服务：

    class UsbsocketService
    {
        public function insert($deviceName){
            return $deviceName." 正在插入 USB 充电";
        }
    }
    
    class DoubleSocketService
    {
        public function insert($deviceName){
            return $deviceName." 正在插入双孔插座充电";
        }
    }
    
    class ThreeSocketService
    {
        public function insert($deviceName){
            return $deviceName." 正在插入三孔插座充电";
        }
    }

设备要使用插座的服务来充电：

    class Device {
    
        protected $socketType; // 插座类型
        public function __construct()
        {
            $this->socketType = new UsbSocketService();
        }
    
        public function power($deviceName)
        {    
            return $this->socketType->insert($deviceName);
        }
    }

现在有一台手机要进行充电:

    Route::get('/charge',function(){
           
       $device = new Device();
       return $device->power("手机");
        
    });

因为 Laravel 提供了自动依赖注入功能，因此可以写成：

    Route::get('/charge/{device}',function(Device $device){
           
       return $device->power("手机");
        
    });

访问 /charge/phone，页面显示 phone 正在插入 USB 充电。

假如，现在有一台电脑要充电，用的是三孔插座，那么我们就需要去修改 Device 类:

    $this->socketType = new ThreeSocketService();

这真是糟糕的设计，设备类对插座服务类产生了依赖。更换设备类型时，经常就要去修改类的内部结构。

### 好的实现

为了解决上面的问题，可以参考「IOC」思路：即将依赖转移到外部。来看看具体怎么做。

首先定义插座类型接口：

    interface SocketType {
        public function insert($deviceName);
    }

让每一种插座都实现该接口：

    class UsbsocketService implements SocketType
    {
        public function insert($deviceName){
            return $deviceName." 正在插入 USB 充电";
        }
    }
    
    class DoubleSocketService implements SocketType
    {
        public function insert($deviceName){
            return $deviceName." 正在插入双孔插座充电";
        }
    }
    
    class ThreeSocketService implements SocketType
    {
        public function insert($deviceName){
            return $deviceName." 正在插入三孔插座充电";
        }
    }

最后，设备中传入接口类型而非具体的类：

    class Device {
    
        protected $socketType; // 插座类型
        public function __construct(SocketType $socketType) // 传入接口
        {
            $this->socketType = $socketType;
        }
    
        public function power($deviceName)
        {    
            return $this->socketType->insert($deviceName);
        }
    }

实例化的时候再决定使用哪种插座类型，这样依赖就转移到了外部：

    Route::get('/charge',function(){
       
       $socketType = new ThreeSocketService();
       $device = new Device($socketType);
       echo $device->power("电脑");    
    });

我们现在可以再不修改类结构的情况下，方便的更换插座来满足不同设备的充电需求:

    Route::get('/charge',function(){
       
       $socketType = new DoubleSocketService();
       $device = new Device($socketType);
       echo $device->power("台灯");    
    });

### 自动依赖注入的失效

上面举的例子，我们通过 Laravel 的自动依赖注入可以进一步简化：

    Route::get('/charge',function(Device $device){ 
           echo $device->power("电脑");
    });

这里的类型提示有两个，一个是 Device $device，一个是 Device 类内部构造函数传入的 SocketType $sockType。第一个没有问题，之前也试过。但是第二个 SocketType 是接口，而 Laravel 会将其当成类试图去匹配 SocketType 的类并将其实例化，因此访问 /charge 时候就会报错:

> Target [SocketType] is not instantiable while building [Device].

错误原因很明显，Laravel 没法自动绑定接口。因此，我们就需要之前的 bind 方法来手动绑定接口啦：

    app()->bind('SocketType',ThreeSocketService::class);
    Route::get('/charge',function(Device $device){
           
           echo $device->power("电脑");
        
    });

现在，如果要更换设备，我们只需要改变绑定的值就可以了:

    app()->bind('SocketType',DoubleSocketService::class);
    Route::get('/charge',function(Device $device){
           
           echo $device->power("台灯");
        
    });

也就是说，我们将依赖转移到了外部之后，进一步由第三方容器来管理，这就是 IOC。

## 契约

契约，不是什么新奇的概念。其实就是上一个例子中，我们定义的接口:

    interface SocketType {
        public function insert($deviceName);
    }

通过契约，我们就可以保持松耦合了:

    public function __construct(SocketType $socketType) // 传入接口而非具体的插座类型
    {
        $this->socketType = $socketType;
    }

然后服务容器再根据需要去绑定哪种服务即可:

    app()->bind('SocketType',UsbSocketService::class);
    app()->bind('SocketType',DoubleSocketService::class);
    app()->bind('SocketType',ThreeSocketService::class);

Laravel 5.4 入门系列告一段落，接下来准备学习 Vue :)

- - -

参考资料：

* [Patrick Stephan :: A Simple Look At The Laravel Service Container][0]
* [Unraveling Laravel Facades – Alan Storm][1]
* [2.7. 外观模式 — DesignPatternsPHP 1.0 文档][2]
* [简单理解laravel框架中的服务容器，服务提供者以及怎样调用服务 - xiake2014的博客 - 博客频道 - CSDN.NET][3]
* [laravel 学习笔记 —— 神奇的服务容器 - 灵感 - 来自生活的馈赠][4]
* [Laravel 的请求生命周期 | Laravel 5.4 中文文档][5]
* [PHP: 重载 - Manual][6]
* [PHP: 数组式访问 - Manual][7]

[0]: https://www.patrickstephan.me/post/a-simple-look-at-the-laravel-service-container.html
[1]: http://alanstorm.com/laravel_facades/
[2]: http://designpatternsphp.readthedocs.io/zh_CN/latest/Structural/Facade/README.html
[3]: http://blog.csdn.net/xiake2014/article/details/52935364
[4]: https://www.insp.top/learn-laravel-container
[5]: http://d.laravel-china.org/docs/5.4/lifecycle#focus-on-service-providers
[6]: http://php.net/manual/zh/language.oop5.overloading.php
[7]: http://php.net/manual/zh/class.arrayaccess.php