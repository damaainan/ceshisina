## PHP IOC/DI 容器 - 依赖自动注入/依赖单例注入/依赖契约注入/参数关联传值

来源：[https://segmentfault.com/a/1190000010143847](https://segmentfault.com/a/1190000010143847)

借助 PHP 反射机制实现的一套 依赖自动解析注入 的 IOC/DI 容器，可以作为 Web MVC 框架 的应用容器

1、依赖的自动注入：你只需要在需要的位置注入你需要的依赖即可，运行时容器会自动解析依赖（存在子依赖也可以自动解析）将对应的实例注入到你需要的位置。

2、依赖的单例注入：某些情况下我们需要保持依赖的全局单例特性，比如 Web 框架中的 Request 依赖，我们需要将整个请求响应周期中的所有注入 Request 依赖的位置同步为在路由阶段解析完请求体的 Request 实例，这样我们在任何位置都可以访问全局的请求体对象。

3、依赖的契约注入：比如我们依赖某 Storage，目前使用 FileStorage 来实现，后期发现性能瓶颈，要改用 RedisStorage 来实现，如果代码中大量使用 FileStorage 作为依赖注入，这时候就需要花费精力去改代码了。我们可以使用接口 Storage 作为契约，将具体的实现类 FileStorage / RedisStorage 通过容器的绑定机制关联到 Storage 上，依赖注入 Storage，后期切换存储引擎只需要修改绑定即可。

4、标量参数关联传值：依赖是自动解析注入的，剩余的标量参数则可以通过关联传值，这样比较灵活，没必要把默认值的参数放在函数参数最尾部。这点我还是蛮喜欢 python 的函数传值风格的。

```php
function foo($name, $age = 27, $sex)
{
    // php 没办法 foo($name = 'big cat', $sex = 'male') 这样传值
    // 只能 foo('big cat', 27, 'male') 传值...
    // python 可以 foo(name = 'big cat', sex = 'male') 很舒服
}
```

但这也使得我的容器不支持位序传值，必须保证运行参数的键名与运行方法的参数名准确的关联映（有默认值的参数可以省略），我想着并没有什么不方便的地方吧，我不喜欢给 $bar 参数传递个 $foo 变量。
## 容器源码

```php
<?php
/*----------------------------------------------------------------------------------------------------
 | @author big cat
 |----------------------------------------------------------------------------------------------------
 | IOC 容器
 | 1、自动解析依赖     自动的对依赖进行解析，实例化，注入
 |                     /------------------------------------------------------------------------------
 |                     | 比如你用 Redis 或 File 做引擎存储 Session，可以定义一个顶层契约接口 Storage
 | 2、契约注入---------| 将具体的实现类 RedisStorage or FileStorage 的实例绑定到此契约
 |                     | 依赖此契约进行注入 后期可以灵活的更换或者扩展新的存储引擎
 |                     \-------------------------------------------------------------------------------
 | 3、单例注入         可以将依赖绑定为单例，实现此依赖的同步
 | 4、关联参数传值     标量参数采用关联传值，可设定默认值
 | 备注：关联传参才舒服, ($foo = 'foo', $bar), 跳过 foo 直接给 bar 传值多舒服
 |-----------------------------------------------------------------------------------------------------
 | public static methods:
 |   singleton // 单例服务绑定
 |   bind      // 服务绑定
 |   run       // 运行容器
 | private static methods:
 |   getParam    // 获取依赖参数
 |   getInstance // 获取依赖实例
 |-----------------------------------------------------------------------------------------------------
 */

class IOCContainer
{
    /**
     * 注册到容器内的依赖--服务
     * 可以通过 singleton($alias, $instance) 绑定全局单例依赖
     * 可以通过 bind($alias, $class_name) 绑定顶层契约依赖
     * 容器解析依赖时会优先检查是否为注册的内部依赖 如不是则加载外部依赖类实例化后注入
     * @var array
     */
    public static $dependencyServices = array();

    /**
     * 单例模式服务注册
     * 将具体的实例绑定到服务 整个生命周期中此服务的各处依赖注入都用此实例
     * @param  [type] $service  绑定的服务别名
     * @param  [type] $provider 服务提供者：具体的实例或可实例的类
     * @return [type]                   [description]
     */
    public static function singleton($service, $provider)
    {
        static::bind($service, $provider, true);
    }

    /**
     * 服务注册
     * 注册依赖服务到容器内 容器将优先使用此类服务 可以实现契约注入
     * 契约注入：A Interface 可以作为 B Class 和 C Class 的代理人（契约者）注入 B Class 或 C Class 的实例
     * 具体看你绑定的谁 可以灵活切换底层具体的实现代码
     * @param  [type]  $service    [description]
     * @param  [type]  $provider   [description]
     * @param  boolean $singleton     [description]
     * @return [type]                 [description]
     */
    public static function bind($service, $provider, $singleton = false)
    {
        if ( ! is_object($provider) && ! class_exists($provider)) {
            throw new Exception("service provider invalid!", 4043);
        }

        // 单例场景下需要将具体的实例与服务名相绑定注入容器中
        if ($singleton) {
            static::$dependencyServices[$service] = [
                'provider'  => $provider,
                'singleton' => $singleton
            ];
        } else { // 同时可以将具体的实现类绑定到等层契约类 这样可以依赖等层契约类注入 切换具体的实现类很方便
            static::$dependencyServices[$service] = [
                'provider'  => $provider,
                'singleton' => $singleton
            ];
        }
    }

    /**
     * 获取类实例
     * 通过反射获取构造参数
     * 返回对应的类实例
     * @param  [type] $class_name [description]
     * @return [type]             [description]
     */
    private static function getInstance($class_name)
    {
        //方法参数分为 params 和 default_values
        //如果一个开放构造类作为依赖注入传入它类，我们应该将此类注册为全局单例服务
        $params = static::getParams($class_name);
        return (new ReflectionClass($class_name))->newInstanceArgs($params['params']);
    }

    /**
     * 反射方法参数类型
     * 对象参数：构造对应的实例 同时检查是否为单例模式的实例
     * 标量参数：返回参数名 索引路由参数取值
     * 默认值参数：检查路由参数中是否存在本参数 无则取默认值
     * @param  [type] $class_name [description]
     * @param  string $method     [description]
     * @return [type]             [description]
     */
    private static function getParams($class_name, $method = '__construct')
    {
        $params_set['params'] = array();
        $params_set['default_values'] = array();

        //反射检测类是否显示声明或继承父类的构造方法
        //若无则说明构造参数为空
        if ( $method == '__construct' ) {
            $classRf = new ReflectionClass($class_name);
            if ( ! $classRf->hasMethod('__construct') ) {
                return $params_set;
            }
        }

        //反射方法 获取参数
        $methodRf = new ReflectionMethod($class_name, $method);
        $params = $methodRf->getParameters();

        if ( ! empty($params) ) {
            foreach ( $params as $key => $param ) {
                if ( $paramClass = $param->getClass() ) {// 对象参数 获取对象实例
                    $param_class_name = $paramClass->getName();
                    if ( array_key_exists($param_class_name, static::$dependencyServices) ) {// 是否为注册的服务
                        if (static::$dependencyServices[$param_class_name]['singleton']) {// 单例模式直接返回已注册的实例
                            $params_set['params'][] = static::$dependencyServices[$param_class_name]['provider'];
                        } else {// 非单例则返回提供者的新的实例
                            $params_set['params'][] = static::getInstance(static::$dependencyServices[$param_class_name]['provider']);
                        }
                    } else {// 没有做绑定注册的类
                        $params_set['params'][] = static::getInstance($param_class_name);
                    }
                } else {// 标量参数 获取变量名作为路由映射 包含默认值的记录默认值
                    $param_name = $param->getName();

                    if ( $param->isDefaultValueAvailable() ) {// 是否包含默认值
                        $param_default_value = $param->getDefaultValue();
                        $params_set['default_values'][$param_name] = $param_default_value;
                    }

                    $params_set['params'][] = $param_name;
                }
            }
        }

        return $params_set;
    }

    /**
     * 容器的运行入口 主要负责加载类方法，并将运行所需的标量参数做映射和默认值处理
     * @param  [type] $class_name 运行类
     * @param  [type] $method     运行方法
     * @param  array  $params     运行参数
     * @return [type]             输出
     */
    public static function run($class_name, $method, array $params = array())
    {
        if ( ! class_exists($class_name) ) {
            throw new Exception($class_name . "not found!", 4040);
        }

        if ( ! method_exists($class_name, $method) ) {
            throw new Exception($class_name . "::" . $method . " not found!", 4041);
        }

        // 获取要运行的类
        $classInstance = static::getInstance($class_name);
        // 获取要运行的方法的参数
        $method_params = static::getParams($class_name, $method);
        
        // 关联传入的运行参数
        $method_params = array_map(function ($param) use ($params, $method_params) {
            if ( is_object($param) ) {// 对象参数 以完成依赖解析的具体实例
                return $param;
            }

            // 以下为关联传值 可通过参数名映射的方式关联传值 可省略含有默认值的参数
            if ( array_key_exists($param, $params) ) {// 映射传递路由参数
                return $params[$param];
            }

            if ( array_key_exists($param, $method_params['default_values']) ) {// 默认值
                return $method_params['default_values'][$param];
            }

            throw new Exception($param . ' is necessary parameters', 4042); // 路由中没有的则包含默认值
        }, $method_params['params']);

        // 运行
        return call_user_func_array([$classInstance, $method], $method_params);
    }
}
```
## 演示所需的依赖类

```php
// 它将被以单例模式注入 全局的所有注入点都使用的同一实例
class Foo
{
    public $msg = "foo nothing to say!";

    public function index()
    {
        $this->msg = "foo hello, modified by index method!";
    }
}

// 它将以普通依赖模式注入 各注入点会分别获取一个实例
class Bar
{
    public $msg = "bar nothing to say!";

    public function index()
    {
        $this->msg = "bar hello, modified by index method!";
    }
}

// 契约注入
interface StorageEngine
{
    public function info();
}

// 契约实现
class FileStorageEngine implements StorageEngine
{
    public $msg = "file storage engine!" . PHP_EOL;

    public function info()
    {
        $this->msg =  "file storage engine!" . PHP_EOL;
    }
}

// 契约实现
class RedisStorageEngine implements StorageEngine
{
    public $msg = "redis storage engine!" . PHP_EOL;

    public function info()
    {
        $this->msg =  "redis storage engine!" . PHP_EOL;
    }
}
```
## 演示所需的运行类

```php
// 具体的运行类
class BigCatController
{
    public $foo;
    public $bar;

    // 这里自动注入一次 Foo 和 Bar 的实例
    public function __construct(Foo $foo, Bar $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    // 这里的参数你完全可以乱序的定义（我故意写的很乱序），你只需保证 route 参数中存在对应的必要参数即可
    // 默认值参数可以直接省略
    public function index($name = "big cat", Foo $foo, $sex = 'male', $age, Bar $bar, StorageEngine $se)
    {
        // Foo 为单例模式注入 $this->foo $foo 是同一实例
        $this->foo->index();
        echo $this->foo->msg . PHP_EOL;
        echo $foo->msg . PHP_EOL;
        echo "------------------------------" . PHP_EOL;

        // Bar 为普通模式注入 $this->bar $bar 为两个不同的 Bar 的实例
        $this->bar->index();
        echo $this->bar->msg . PHP_EOL;
        echo $bar->msg . PHP_EOL;
        echo "------------------------------" . PHP_EOL;

        // 契约注入 具体看你为契约者绑定了哪个具体的实现类
        // 我们绑定的 RedisStorageEngine 所以这里注入的是 RedisStorageEngine 的实例
        $se->info();
        echo $se->msg;
        echo "------------------------------" . PHP_EOL;

        // 返回个值
        return "name " . $name . ', age ' . $age . ', sex ' . $sex . PHP_EOL;
    }
}
```
## 运行

```php
// 路由信息很 MVC 吧
$route = [
    'controller' => BigCatController::class, // 运行的类
    'action'     => 'index', // 运行的方法
    'params'     => [ // 运行的参数
        'name' => 'big cat',
        'age'  => 27 // sex 有默认值 不传
    ]
];

try {
    // 依赖的单例注册
    IOCContainer::singleton(Foo::class, new Foo());

    // 依赖的契约注册 StorageEngine 相当于契约者 注册关联具体的实现类
    // IOCContainer::bind(StorageEngine::class, FileStorageEngine::class);
    IOCContainer::bind(StorageEngine::class, RedisStorageEngine::class);
    
    // 运行
    $result = IOCContainer::run($route['controller'], $route['action'], $route['params']);
    
    echo $result;
} catch (Exception $e) {
    echo $e->getMessage();
}
```
## 运行结果

```
foo hello, modified by index method!
foo hello, modified by index method!
------------------------------
bar hello, modified by index method!
bar nothing to say!
------------------------------
redis storage engine!
------------------------------
name big cat, age 27, sex male
```

简单的实现了像 laraval 的 IOC 容器的特性，但比它多一项（可能也比较鸡肋）标量参数的关联传值，不过我这功能也限定死了你传入的参数必须与函数定义的参数名相关联，可我还是觉得能充分的填补默认参数不放在参数尾就无法跳过的强迫症问题.....
