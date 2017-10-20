# Laravel源码解读系列第一篇-初始化 

Published on May 31, 2017 in [Laravel][0][PHP][1] with [0 comment][2]

## 前言

作为PHP中最优雅的框架，Laravel的优点不由分说，里面集成了许多PHP的新特性，所以也是冲着学习的目的，花了一些时间取读Laravel的源代码。当然，里面还有很多部分并没有完全理解到，希望后续有时间继续研究。  
这个系列的大致逻辑会从入口文件一直读到服务脚本结束，里面会涉及到服务容器，服务提供者等Laravel的核心板块，后续会根据Laravel的功能来解读予以补充，如有解析不到位的地方还望大神指出。

## 入口文件

当我们用Laravel的脚手架生成一个项目之后(laravel new project)，执行`php artisan serve`，会默认调用8000端口，浏览器输入`localhost:8000`即可正常访问，而入口文件则是`public/index.php`文件。

    <?php
    //引入composer自动加载
    require __DIR__ . '/../bootstrap/autoload.php';
    
    //引入容器
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    $response->send();
    
    $kernel->terminate($request, $response);
    

- - -

### Composer部分

Composer之于PHP就好比npm之于node，使用参照[官网][3]即可。  
通过`require __DIR__ . '/../bootstrap/autoload.php';`我们可以执行到ComposerAutoloaderInit591f4d4910a902ad6f72c35a8198ed6a::getLoader();  
getLoader内容:

    public static function getLoader()
        {
            if (null !== self::$loader) {
                return self::$loader;
            }
    //当我们实例化ClassLoader时，会调用ComposerAutoloaderInit591f4d4910a902ad6f72c35a8198ed6a下的loadClassLoader方法
            spl_autoload_register(array('ComposerAutoloaderInit591f4d4910a902ad6f72c35a8198ed6a', 'loadClassLoader'), true, true);
            self::$loader = $loader = new \Composer\Autoload\ClassLoader();
    //        解除注册函数
            spl_autoload_unregister(array('ComposerAutoloaderInit591f4d4910a902ad6f72c35a8198ed6a', 'loadClassLoader'));
            $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
            if ($useStaticLoader) {
                require_once __DIR__ . '/autoload_static.php';
    //        调用ComposerStaticInit591f4d4910a902ad6f72c35a8198ed6a的方法getInitializer，他会返回一个闭包函数，然后call_user_func直接执行，给$loader添加几个属性
                call_user_func(\Composer\Autoload\ComposerStaticInit591f4d4910a902ad6f72c35a8198ed6a::getInitializer($loader));
            } else {
    //            添加配置
                $map = require __DIR__ . '/autoload_namespaces.php';
                foreach ($map as $namespace => $path) {
                    $loader->set($namespace, $path);
                }
    
                $map = require __DIR__ . '/autoload_psr4.php';
                foreach ($map as $namespace => $path) {
                    $loader->setPsr4($namespace, $path);
                }
    
                $classMap = require __DIR__ . '/autoload_classmap.php';
                if ($classMap) {
                    $loader->addClassMap($classMap);
                }
            }
            $loader->register(true);
            if ($useStaticLoader) {
                $includeFiles = Composer\Autoload\ComposerStaticInit591f4d4910a902ad6f72c35a8198ed6a::$files;
            } else {
                $includeFiles = require __DIR__ . '/autoload_files.php';
            }
    //        在引入这些文件的同时，把这些配置添加到$GLOBALS超全局变量中
            foreach ($includeFiles as $fileIdentifier => $file) {
                composerRequire591f4d4910a902ad6f72c35a8198ed6a($fileIdentifier, $file);
            }
            return $loader;
        }

以上都是一些常规的配置和引入相关文件，不过比较有意思的是，这里用了一个`call_user_func+Closure::bind()`的模式，其实这里我们也可以完全按照常规的做法，引入一个配置文件的方式来实现，`Closure::bind()`返回一个Closure对象，即一个闭包函数，其中一共有三个参数:

* `closure`:需要绑定的匿名函数
* `newthis`:需要绑定到匿名函数的对象，或者 NULL 创建未绑定的闭包
* `newscope`:想要绑定给闭包的类作用域，或者 'static' 表示不改变。如果传入一个对象，则使用这个对象的类型名。  
第一个参数好理解，第二个和第三个可以配合起来使用，当我们需要在闭包函数中使用$this的时候，我们需要给这个闭包绑定一个对象，如果闭包中使用了$this，那么newthis需要指定一个对应的对象，如果修改的属性是protected或者private，那么第三个参数不能忽略，指定为这个类。
* 例1:

    class A{
        public $name;
        protected $age;
    }
    $a = new A();
    $new_a = Closure::bind(function(){
        $this->name = 'nine';
    } , null);
    $new_a();
    var_dump($new_a , $a);

此时会报错`Using $this when not in object context in ...`，说明这个上下文并没有指定`$this`。

* 例2:

    class A{
        public $name;
        protected $age;
    }
    $a = new A();
    $new_a = Closure::bind(function(){
        $this->name = 'nine';
        $this->age = 10;
    } , $a);
    $new_a();
    var_dump($new_a , $a);

此时会报错`Cannot access protected property A::$age in...`，属性是受保护的。

* 例3:

    class A{
        public $name;
        protected $age;
    }
    $a = new A();
    $new_a = Closure::bind(function(){
        $this->name = 'nine';
        $this->age = 10;
    } , $a , A::class);
    $new_a();
    var_dump($new_a , $a);

设置成功。

![39526bfd-fcae-4c31-8334-daf0b89f7f2f.png][4]

- - -

### app部分

#### app初始化

引入文件  
`$app = require_once __DIR__ . '/../bootstrap/app.php';`  
传入路径进行实例化:

    $app = new Illuminate\Foundation\Application(
        realpath(__DIR__.'/../')
    );

app初始化准备工作:

    public function __construct($basePath = null)
        {
    //        调用setBasePath设置basePath
            if ($basePath) {
                $this->setBasePath($basePath);
            }
    //        初始化最开始需要绑定的对象
            $this->registerBaseBindings();
    //        初始化最开始的服务提供者,起结果绑定在$this->serverProvider中
            $this->registerBaseServiceProviders();
    //          绑定别名生成在$this->aliases和$this->abstractAliases中
            $this->registerCoreContainerAliases();
        }

App实例化的时候需要完成四件事情:

* 文件路径初始化(setBasePath)  
这部分主要是确定物理路径，以数组的形式存储在`$this->instances`中，通过调用`$this->setBasePath()`我们发现其核心代码主要是`Illuminate\Container\Container::instance`方法:

    public function instance($abstract, $instance)
        {
    //移除$this->abstractAliases中的值
            $this->removeAbstractAlias($abstract);
            unset($this->aliases[$abstract]);
            $this->instances[$abstract] = $instance;
            if ($this->bound($abstract)) {
                $this->rebound($abstract);
            }
        }

需要稍微注意一下的是`$this->aliases`和`$this->abstractAliases`的区别(后面初始化别名的时候也会讲到)，二者的键值对是相反的，`$this->aliases`的键是类名，值是别名，而`$this->abstractAliases`的键是别名，值是类名(数组形式)。  
当我们添加到`$this->instances`中去之后，我们会用`$this->bound`方法检查是否已经存在在了`$this->bindings`或者`$this->instances`或者`$this->aliases`中，如果true则会调用`$this->rebound`方法。

    public function bound($abstract)
        {
            return isset($this->bindings[$abstract]) ||
                   isset($this->instances[$abstract]) ||
                   $this->isAlias($abstract);
        }
    
    protected function rebound($abstract)
        {
    //        调用方法，返回在$this->instances里面设置的结果
            $instance = $this->make($abstract);
    
            foreach ($this->getReboundCallbacks($abstract) as $callback) {
                call_user_func($callback, $this, $instance);
            }
        }

make方法会调用Container中的resolve方法，此方法是其中的一个**核心方法**，需要我们格外留意。

    protected function resolve($abstract, $parameters = [])
        {
            $abstract = $this->getAlias($abstract);
            $needsContextualBuild = ! empty($parameters) || ! is_null(
                $this->getContextualConcrete($abstract)
            );
            if (isset($this->instances[$abstract]) && ! $needsContextualBuild) {
                return $this->instances[$abstract];
            }
            ...
        }

省略号的部分是因为此处的逻辑操作不涉及到下面的代码，为了方便阅读，就不贴出来了(下面打省略号的地方同理)。  
当我们调用getAlias之后会返回当前的$abstract，然后直接返回$abstract在$this->instances中所对应的值。  
路径的绑定工作基本完成。  
dd($this->instances):

![2cf062b0-a3fa-4552-85ed-d01cb1af94df.png][5]

* 基础绑定(registerBaseBindings)

    protected function registerBaseBindings()
        {
            static::setInstance($this);
    
            $this->instance('app', $this);
    
            $this->instance(Container::class, $this);
        }

基础绑定部分与前面的路径绑定部分基本一致。  
dd($this->instances):

![9323df17-d436-4428-b68f-4570eef1e489.png][6]

* 基础服务提供者绑定(registerBaseServiceProviders)

    protected function registerBaseServiceProviders()
        {
    //        在注册的同时会给服务提供者注入$app对象
            $this->register(new EventServiceProvider($this));
    
            $this->register(new LogServiceProvider($this));
    
            $this->register(new RoutingServiceProvider($this));
        }

基础服务提供者绑定部分会给app对象绑定三个服务提供者，服务提供者都继承自ServiceProvider.php，调用其构造方法给$this->app注入当前的app对象。

    public function register($provider, $options = [], $force = false)
        {
    //        调用$this->getProvider判断是否已经注册了服务提供者，如果有，就返回这个服务提供者
            if (($registered = $this->getProvider($provider)) && ! $force) {
                return $registered;
            }
    
    //        判断是否是一个字段，如果是，就实例化，并注入$this,即$app
            if (is_string($provider)) {
                $provider = $this->resolveProvider($provider);
            }
    
    //        如果这个服务提供者的register的方法存在，那么就调用他的register方法,其结果就是把服务提供者绑定到$this->bindings这个数组中
    //        以EventServiceProvider这个服务提供者为例
            if (method_exists($provider, 'register')) {
                $provider->register();
            }
    //        绑定到$this->serverProvider中，同时在$this->loadedProvider设置这个类为true，表示已经加载
            $this->markAsRegistered($provider);
            if ($this->booted) {
                $this->bootProvider($provider);
            }
            return $provider;
        }

register方法首先会调用getProvider尝试获取是否已经绑定了当前的服务提供者。

    public function getProvider($provider)
        {
            $name = is_string($provider) ? $provider : get_class($provider);
    
    //        调用first方法，判断是否是第一次设置，如果不是，则执行其中的callback函数，判断是否是这个类的一个实例
            return Arr::first($this->serviceProviders, function ($value) use ($name) {
                return $value instanceof $name;
            });
        }

    public static function first($array, callable $callback = null, $default = null)
        {
            if (is_null($callback)) {
                if (empty($array)) {
                    return value($default);
                }
    
                foreach ($array as $item) {
                    return $item;
                }
            }
    
            foreach ($array as $key => $value) {
                if (call_user_func($callback, $value, $key)) {
                    return $value;
                }
            }
    
            return value($default);
        }

此时当我们第一次绑定EventServiceProvider这个服务提供者的时候显然会执行return value($default)返回一个null。所以此时我们又回到了$app中的register方法，执行EventServiceProvider中的register方法:

    public function register()
        {
    //        这里的$this->app是最开始构造的时候注入的,调用他的singleton方法
            $this->app->singleton('events', function ($app) {
                return (new Dispatcher($app))->setQueueResolver(function () use ($app) {
                    return $app->make(QueueFactoryContract::class);
                });
            });
        }

singleton会调用bind的方法，也就是我们比较熟悉的绑定。

    public function bind($abstract, $concrete = null, $shared = false)
        {
    //        同时删除$this->instances和$this->aliases里面的$abstract中对应的值
            $this->dropStaleInstances($abstract);
    
    //        如果没有传入回调函数，那么就是其本身
            if (is_null($concrete)) {
                $concrete = $abstract;
            }
    
    //        如果这个不是一个回调函数的话,就生成一个回调函数,这个回调函数需要注入一个容器作为参数
            if (! $concrete instanceof Closure) {
                $concrete = $this->getClosure($abstract, $concrete);
            }
    
            $this->bindings[$abstract] = compact('concrete', 'shared');
            if ($this->resolved($abstract)) {
                $this->rebound($abstract);
            }
        }

当我们执行`$this->bindings[$abstract] = compact('concrete', 'shared');`的时候，绑定初始化的服务提供者工作也就完成了。  
dd($this->bindings):

![adddcaaa-0573-4f2a-8045-0dc53629ef7b.png][7]

* 别名初始化(registerCoreContainerAliases)  
正如上面所说，别名是分成两个数组填充的，一个是$this->aliases，另一个是$this->abstractAliases，实现方法非常简单，这里就不做阐述，自己看源码即可。

dd($this->aliases):

![df9be0b1-5174-4680-b743-26e7b19583bd.png][8]

  
dd($this->aliases):

![38c62a35-0b50-47b8-b85c-4e2eccd26e79.png][9]

- - -

#### 绑定核心类

app初始化完成之后，会调用singleton来绑定三个接口:

    $app->singleton(
        Illuminate\Contracts\Http\Kernel::class,
        App\Http\Kernel::class
    );
    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );
    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );

其逻辑和上述一样，不过需要注意的是，当我们绑定的$concrete并非一个Closure实例的时候，会根据当前的$concrete来帮助我们生成一个回调函数，这会为我们后面make生成对象埋下伏笔:

    public function bind($abstract, $concrete = null, $shared = false)
        {
           ...
    //        如果这个不是一个回调函数的话,就生成一个回调函数,这个回调函数需要注入一个容器作为参数
            if (! $concrete instanceof Closure) {
                $concrete = $this->getClosure($abstract, $concrete);
            }
            $this->bindings[$abstract] = compact('concrete', 'shared');
            if ($this->resolved($abstract)) {
                $this->rebound($abstract);
            }
        }

    protected function getClosure($abstract, $concrete)
        {
            return function ($container, $parameters = []) use ($abstract, $concrete) {
                $method = ($abstract == $concrete) ? 'build' : 'make';
    
                return $container->$method($concrete, $parameters);
            };
        }

`Illuminate\Contracts\Http\Kernel`的绑定结果如下:

![d7a770f4-6169-4d24-9070-97ee00e97b74.png][10]

  
接下来重点讲如何调用的:

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

当我们调用make的时候，最后会执行到resolve:

    protected function resolve($abstract, $parameters = [])
        {
          ...
    //        这里会给这个with压入一个参数对象，后面会调用$this->getLastParameterOverride()方法来获取最新压入的数组
            $this->with[] = $parameters;
    
    //        获取之前设置的concrete
            $concrete = $this->getConcrete($abstract);
    //        如果绑定的二者一样，或者说concrete是一个回调，执行build
            if ($this->isBuildable($concrete, $abstract)) {
                $object = $this->build($concrete);
            } else {
                $object = $this->make($concrete);
            }
            ...
        }

此时，我们发现，项目一直走到了$concrete = $this->getConcrete($abstract);这里去获取Illuminate\Contracts\Http\Kernel的一个实体，当然，前面的$this->with[] = $parameters也需要我们留意一下，因为后面会用到这里，虽然在此处并没有生成参数。

    protected function getConcrete($abstract)
        {
            if (! is_null($concrete = $this->getContextualConcrete($abstract))) {
                return $concrete;
            }
            if (isset($this->bindings[$abstract])) {
                return $this->bindings[$abstract]['concrete'];
            }
    //如果都没有的话，返回自身，比如我们给一个接口绑定一个实现，那么当我们第二次调用make的时候，之前是没有bindings的，所以就返回本身
            return $abstract;
        }

通过调用getConcrete，返回给了我们绑定在$bindings中最开始因为不是Closure的对象而帮他生成的一个回调函数。此时回到resolve方法中，执行`$this->isBuildable($concrete, $abstract)`予以判断:

    protected function isBuildable($concrete, $abstract)
        {
            return $concrete === $abstract || $concrete instanceof Closure;
        }

显然，需要我们去执行$this->build方法:

    public function build($concrete)
        {
            if ($concrete instanceof Closure) {
    //            这里会执行之前绑定的闭包函数，如果之前绑定的是两个class类，会帮他生成一个闭包，这个闭包会重新return 一个$this->make 或者 build方法，此时的第一个参数是我们之前bind的第二个参数
                return $concrete($this, $this->getLastParameterOverride());
            }
        }
        ...

此时因为当前的这个$concrete是一个闭包，所以直接执行了当前函数，不知道之前那个生成的闭包函数是否还有印象，此时需要调用他了，并传递两个参数，其中，`$this->getLastParameterOverride()`会返回我们之前用$this->with压入的最后一个参数数组，当然，因为$this->with为空，所以返回了一个空的数组，如下是我们需要执行的函数:

    protected function getClosure($abstract, $concrete)
        {
            return function ($container, $parameters = []) use ($abstract, $concrete) {
                $method = ($abstract == $concrete) ? 'build' : 'make';
                return $container->$method($concrete, $parameters);
            };
        }

当时我们绑定的时候的$concrete是`App\Http\Kernel`，$abstract是`Illuminate\Contracts\Http\Kernel`，因为二者不相等，所以我们此时又会调用$container，即我们刚传入的$this，也就是$app对象的make方法，而此时传入的$abstract是`App\Http\Kernel`，同样的，他也会调用getConcrete，但是此时返回的是自己本身，即App\Http\Kernel:

    protected function getConcrete($abstract)
        {
            if (! is_null($concrete = $this->getContextualConcrete($abstract))) {
                return $concrete;
            }
            if (isset($this->bindings[$abstract])) {
                return $this->bindings[$abstract]['concrete'];
            }
    //如果都没有的话，返回自身，比如我们给一个接口绑定一个实现，那么当我们第二次调用make的时候，之前是没有bindings的，所以就返回本身
            return $abstract;
        }

此时，又会继续调用isBuildable来判断，因为二者相等，又执行了build方法，不过这里需要注意的是，因为此时并不是Closure的一个对象，所以执行了下面的反射类:

    public function build($concrete)
        {
            if ($concrete instanceof Closure) {
    //            这里会执行之前绑定的闭包函数，如果之前绑定的是两个class类，会帮他生成一个闭包，这个闭包会重新return 一个$this->make 或者 build方法，此时的第一个参数是我们之前bind的第二个参数
                return $concrete($this, $this->getLastParameterOverride());
            }
    //        对我们接口的实现做一个反射生成class
            $reflector = new ReflectionClass($concrete);
    //        判断这个反射类是否可以实例化
            if (! $reflector->isInstantiable()) {
                return $this->notInstantiable($concrete);
            }
            $this->buildStack[] = $concrete;
    //        获取构造函数
            $constructor = $reflector->getConstructor();
            if (is_null($constructor)) {
                array_pop($this->buildStack);
                return new $concrete;
            }
    //        判断构造函数的参数
            $dependencies = $constructor->getParameters();
            $instances = $this->resolveDependencies(
                $dependencies
            );
            array_pop($this->buildStack);
    
            return $reflector->newInstanceArgs($instances);
        }

其中的重点在于`$this->resolveDependencies`，当我们对这个生成的反射类进行实例化的时候，我们发现，这个对象，即`App\Http\Kernel`继承自`Illuminate\Foundation\Http\Kernel`，他的父级构造需要注入两个对象:Application和Router，所以，需要我们调用`resolveDependencies`获取到这两个对象的实例:

    protected function resolveDependencies(array $dependencies)
        {
            $results = [];
            foreach ($dependencies as $dependency) {
    //            判断$this->with是否有这个name的key
                if ($this->hasParameterOverride($dependency)) {
                    $results[] = $this->getParameterOverride($dependency);
                    continue;
                }
                $results[] = is_null($class = $dependency->getClass())
                                ? $this->resolvePrimitive($dependency)
                                : $this->resolveClass($dependency);
            }
    
            return $results;
        }

dd($dependencies):

![b7b1fb59-0665-4bdd-81c9-af1062f5bfbd.png][11]

  
其实前面我们也提到了前面的`$this->with`是以压入的形式传入参数的，所以显然通过调用`hasParameterOverride`是找不到的(当然，后面的$this->with可能有其他的方式注入)，因此，我们最终要遍历的执行resolveClass方法:

    protected function resolveClass(ReflectionParameter $parameter)
        {
            try {
                return $this->make($parameter->getClass()->name);
            }
            catch (BindingResolutionException $e) {
                if ($parameter->isOptional()) {
                    return $parameter->getDefaultValue();
                }
    
                throw $e;
            }
        }

这个时候我们发现，我们熟悉的老朋友make又回来了，我们拿第一个为例，此时给make传入的是app:

    protected function resolve($abstract, $parameters = [])
        {
    //        返回abstract，如果已经有对$this->aliases设置，会返回他对应的值
            $abstract = $this->getAlias($abstract);
    
    //        在参数不为空的时候，判断是否在$this->contextual以及在$this->abstractAlias有所设置
            $needsContextualBuild = ! empty($parameters) || ! is_null(
                $this->getContextualConcrete($abstract)
            );
    //        这里主要是为了做初始化操作，如果已经设置了，返回设置的结果即可
            if (isset($this->instances[$abstract]) && ! $needsContextualBuild) {
                return $this->instances[$abstract];
            }
            ...

还记得我们最开始初始化时通过`registerBaseBindings`方法绑定的app吗？此时，就通过`return $this->instances[$abstract];`返回了他的一个实例。  
而router则相对而言复杂一些，还记得我们在`registerBaseServiceProviders`中其实有执行`$this->register(new RoutingServiceProvider($this));`的步骤，其实和之前的`EventServiceProvider`例子一样，最终会给router绑定一个singleton:

    protected function registerRouter()
        {
            $this->app->singleton('router', function ($app) {
                return new Router($app['events'], $app);
            });
        }

所以当我们在resolve方法中调用`$object = $this->build($concrete)`时，会返回Router的一个实例。当然，执行到这里还没立即返回，这也是弥补上面提到注册基础服务提供者部分没有讲到的，同时也是`singleton`和直接bind的一个区别:

    if ($this->isShared($abstract) && ! $needsContextualBuild) {
                $this->instances[$abstract] = $object;
            }

当我们是调用`singleton`时，其中的share是true，所以会把这个实例放在`$this->instances`中，因此当我们下次再需要make这个实例的时候，会直接拿`$this->instances`中的结果了。

至此，Laravel的初始化工作正式完成。

本文由 [nine][12] 创作，采用 [知识共享署名4.0][13] 国际许可协议进行许可  
本站文章除注明转载/出处外，均为本站原创或翻译，转载前请务必署名  
最后编辑时间为: Jun 10, 2017 at 11:05 am

[0]: http://www.hellonine.top/index.php/category/laravel/
[1]: http://www.hellonine.top/index.php/category/PHP/
[2]: #comments
[3]: https://getcomposer.org/
[4]: ../img/211119917.png
[5]: ../img/2258779425.png
[6]: ../img/1664614924.png
[7]: ../img/172912044.png
[8]: ../img/2066187438.png
[9]: ../img/3922776503.png
[10]: ../img/3045177057.png
[11]: ../img/3486746872.png
[12]: http://www.hellonine.top/index.php/author/1/
[13]: https://creativecommons.org/licenses/by/4.0/