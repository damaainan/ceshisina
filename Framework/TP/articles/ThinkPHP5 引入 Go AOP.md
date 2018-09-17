## [ThinkPHP5 引入 Go AOP](https://segmentfault.com/a/1190000012656671)

## 项目背景

目前开发的WEB软件里有这一个功能，PHP访问API操作数据仓库，刚开始数据仓库小，没发现问题，随着数据越来越多，调用API时常超时(60s)。于是决定采用异步请求，改为60s能返回数据则返回，不能则返回一个异步ID，然后轮询是否完成统计任务。由于项目紧，人手不足，必须以最小的代价解决当前问题。

## 方案选择

1. 从新分析需求，并改进代码
1. 采用AOP方式改动程序

从新做需求分析，以及详细设计，并改动代码，需要产品，架构，前端，后端的支持。会惊动的人过多，在资源紧张的情况下是不推荐的。  
采用AOP方式，不改动原有代码逻辑，只需要后端就能完成大部分任务了。后端用AOP切入请求API的方法，通过监听API返回的结果来控制是否让其继续运行原有的逻辑（API在60s返回了数据），或者是进入离线任务功能（API报告统计任务不能在60s内完成）。

## 实际环境

Debian，php-fpm-7.0，ThinkPHP-5.10。

## 引入AOP

作为一门zui好的语言，PHP是不自带AOP的。那就得安装[AOP-PHP][0]拓展，当我打开[pecl][0]要下载时，傻眼了，全是bate版，没有显示说明支持php7。但我还是抱着侥幸心理，找到了git，发现4-5年没更新了，要不要等一波更新，哦，作者在issue里说了有时间就开始兼容php7。  
好吧，狠话不多说，下一个方案：[Go!AOP][1].看了下git，作者是个穿白体恤，喜欢山峰的大帅哥，基本每个issue都会很热心回复。

    composer require goaop/framework

ThinkPHP5 对composer兼容挺不错的哦，（到后面，我真想揍ThinkPHP5作者）这就装好了，怎么用啊，git上的提示了简单用法。我也就照着写了个去切入controller。
```php
<?PHP
namespace app\tests\controller;

use think\Controller;

class Test1 extends Controller
{
    public function test1()
    {
        echo $this->aspectAction();
    }
    
    public function aspectAction()
    {
        return 'hello';
    }
}
```
定义aspect

```php
<?php
namespace app\tests\aspect;

use Go\Aop\Aspect;
use Go\Aop\Intercept\FieldAccess;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Pointcut;

use app\tests\controller\Test1;

class MonitorAspect implements Aspect
{

    /**
     * Method that will be called before real method
     *
     * @param MethodInvocation $invocation Invocation
     * @Before("execution(public|protected app\tests\controller\Test1->aspectAction(*))")
     */
    public function beforeMethodExecution(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        echo 'Calling Before Interceptor for method: ',
             is_object($obj) ? get_class($obj) : $obj,
             $invocation->getMethod()->isStatic() ? '::' : '->',
             $invocation->getMethod()->getName(),
             '()',
             ' with arguments: ',
             json_encode($invocation->getArguments()),
             "<br>\n";
    }
}
```
启用aspect

```php
<?php
// file: ./application/tests/service/ApplicationAspectKernel.php

namespace app\tests\service;

use Go\Core\AspectKernel;
use Go\Core\AspectContainer;

use app\tests\aspect\MonitorAspect;

/**
 * Application Aspect Kernel
 *
 * Class ApplicationAspectKernel
 * @package app\tests\service
 */
class ApplicationAspectKernel extends AspectKernel
{

    /**
     * Configure an AspectContainer with advisors, aspects and pointcuts
     *
     * @param AspectContainer $container
     *
     * @return void
     */
    protected function configureAop(AspectContainer $container)
    {
        $container->registerAspect(new MonitorAspect());
    }
}
```

go-aop 核心服务配置

```php
<?php
// file: ./application/tests/behavior/Bootstrap.php
namespace app\tests\behavior;

use think\Exception;
use Composer\Autoload\ClassLoader;
use Go\Instrument\Transformer\FilterInjectorTransformer;
use Go\Instrument\ClassLoading\AopComposerLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

use app\tests\service\ApplicationAspectKernel;
use app\tests\ThinkPhpLoaderWrapper;

class Bootstrap
{
    public function moduleInit(&$params)
    {
        $applicationAspectKernel = ApplicationAspectKernel::getInstance();
        $applicationAspectKernel->init([
            'debug' =>  true,
            'appDir'    =>  __DIR__ . './../../../',
                'cacheDir'  =>  __DIR__ . './../../../runtime/aop_cache',
                'includePaths'  =>  [
                    __DIR__ . './../../tests/controller',
                    __DIR__ . './../../../thinkphp/library/think/model'
                ],
                'excludePaths'  =>  [
                    __DIR__ . './../../aspect',
                ]
            ]);
        return $params;
    }
}
```

配置模块init钩子，让其启动 go-aop

```php
<?php
// file: ./application/tests/tags.php
// 由于是thinkphp5.10 没有容器，所有需要在module下的tags.php文件里配置调用他

return [
    // 应用初始化
    'app_init'     => [],
    // 应用开始
    'app_begin'    => [],
    // 模块初始化
    'module_init'  => [
        'app\\tests\\behavior\\Bootstrap'
    ],
    // 操作开始执行
    'action_begin' => [],
    // 视图内容过滤
    'view_filter'  => [],
    // 日志写入
    'log_write'    => [],
    // 应用结束
    'app_end'      => [],
];
```

## 兼容测试

好了，访问 [http://127.0.0.1/tests/test1/...][2] 显示：

    hello

这不是预期的效果，在aspect定义了，访问该方法前，会输出方法的更多信息信息。  
像如下内容才是预期

    Calling Before Interceptor for method: app\tests\controller\Test1->aspectAction() with arguments: []

上他[官方Doc][3]看看，是一些更高级的用法。没有讲go-aop的运行机制。  
上git上也没看到类似issue，额，发现作者经常在issue里回复：试一试demo。也许我该试试demo。  
通过额外配置，demo运行起来了。

![][4]   
尝试了下，运行成功

![][5]   
通过以上的输出，可以得出demo里是对方法运行前成功捕获。为什么在thinkphp的controller里运行就不成功呢。我决定采用断点进行调试。

通过断点我发现了这个文件

```php
<?php
// file: ./vendor/lisachenko/go-aop-php/src/Instrument/ClassLoading/AopComposerLoader.php

public function loadClass($class)
{
    if ($file = $this->original->findFile($class)) {
        $isInternal = false;
        foreach ($this->internalNamespaces as $ns) {
            if (strpos($class, $ns) === 0) {
                $isInternal = true;
                break;
            }
        }

        include ($isInternal ? $file : FilterInjectorTransformer::rewrite($file));
    }
}
```

这是一个autoload，每个类的载入都会经过它，并且会对其判断是否为内部类，不是的都会进入后续的操作。通过断点进入 FilterInjectorTransformer，发现会对load的文件进行语法解析，并根据注册的annotation对相关的类生成proxy类。说道这，大家就明白了go-aop是如何做到切入你的程序了吧，生成的proxy类，可以在你配置的cache-dir（我配置的是./runtime/aop_cache/）里看到。

同时./runtime/aop_cache/ 文件夹下也生成了很多东西，通过查看aop_cache文件内产生了与Test1文件名相同的文件，打开文件，发现它代理了原有的Test1控制器。这一系列信息，可以得出，Go!AOP 通过"劫持" composer autoload 让每个类都进过它，根据aspect的定义来决定是否为其创建一个代理类，并植入advice。  
额，ThinkPHP5是把composer autoload里的东西copy出来，放到自己autoload里，然后就没composer啥事了。然后go-aop一直等不到composer autoload下发的命令，自然就不能起作用了，so，下一步

## 改进ThinkPHP5

在ThinkPHP5里，默认有且只会注册一个TP5内部的 [Loader][6]，并不会把include请求下发给composer的autoload。所以，为其让go-aop起作用，那么必须让让include class的请求经过 [AopComposerLoad][7].  
我们看看这个文件

```php
<?php
// ./vendor/lisachenko/go-aop-php/src/Instrument/ClassLoading/AopComposerLoader.php:57

public static function init()
{
    $loaders = spl_autoload_functions();

    foreach ($loaders as &$loader) {
        $loaderToUnregister = $loader;
        if (is_array($loader) && ($loader[0] instanceof ClassLoader)) {
            $originalLoader = $loader[0];

            // Configure library loader for doctrine annotation loader
            AnnotationRegistry::registerLoader(function ($class) use ($originalLoader) {
                $originalLoader->loadClass($class);

                return class_exists($class, false);
            });
            $loader[0] = new AopComposerLoader($loader[0]);
        }
        spl_autoload_unregister($loaderToUnregister);
    }
    unset($loader);

    foreach ($loaders as $loader) {
        spl_autoload_register($loader);
    }
}
```

这个文件里有个类型检测，检测autoload callback是否为Classloader类型，然而ThinkPHP5不是，通过断点你会发现ThinkPHP5是一个字符串数组，so，这里也就无法把go-aop注册到class loader的callback当中了。

这里就要提一下PHP autoload机制了，这是现代PHP非常重要的一个功能，它让我们在用到一个类时，通过名字能自动加载文件。我们通过定义一定的类名规则与文件结构目录，再加上能实现以上规则的函数就能实现自动加载了。在通过 [spl_autoload_register][8] 函数的第三个参数 prepend 设置为true，就能让其排在在TP5的loader前面，先一步被调用。

依照如上原理，就可以做如下改进  
这个是为go-aop包装的新autoload，本质上是在原来的ThinkPHP5的loader上加了一个壳而已。

```php
<?php
// file: ./application/tests 

namespace app\tests;

require_once __DIR__ . './../../vendor/composer/ClassLoader.php';

use think\Loader;
use \Composer\Autoload\ClassLoader;
use Go\Instrument\Transformer\FilterInjectorTransformer;
use Go\Instrument\ClassLoading\AopComposerLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;


class ThinkPhpLoaderWrapper extends ClassLoader
{
    static protected $thinkLoader = Loader::class;

    /**
     * Autoload a class by it's name
     */
    public function loadClass($class)
    {
        return Loader::autoload($class);
    }

    /**
     * {@inheritDoc}
     */
    public function findFile($class)
    {
        $allowedNamespace = [
            'app\tests\controller'
        ];
        $isAllowed = false;
        foreach ($allowedNamespace as $ns) {
            if (strpos($class, $ns) === 0) {
                $isAllowed = true;
                break;
            }
        }
        // 不允许被AOP的类，则不进入AopComposer
        if(!$isAllowed)
            return false;
        
        $obj = new Loader;
        $observer = new \ReflectionClass(Loader::class);

        $method = $observer->getMethod('findFile');
        $method->setAccessible(true);
        $file = $method->invoke($obj, $class);
        return $file;
    }
}

<?PHP
// file: ./application/tests/behavior/Bootstrap.php 在刚刚我们新添加的文件当中
// 这个方法 \app\tests\behavior\Bootstrap::moduleInit 的后面追加如下内容

// 组成AOPComposerAutoLoader
$originalLoader = $thinkLoader = new ThinkPhpLoaderWrapper();
AnnotationRegistry::registerLoader(function ($class) use ($originalLoader) {
    $originalLoader->loadClass($class);

    return class_exists($class, false);
});
$aopLoader = new AopComposerLoader($thinkLoader);
spl_autoload_register([$aopLoader, 'loadClass'], false, true);

return $params;
```
    

在这里我们做了一个autload 并直接把它插入到了最前面（如果项目内还有其他autloader，请注意他们的先后顺序）。

## 最后

现在我们再访问一下 [http://127.0.0.1/tests/test1/...][2] 你就能看到来自 aspect 输出的信息了。  
最后我们做个总结：

1. PHP7目前没有拓展实现的 AOP。
1. ThinkPHP5 有着自己的 Autoloader。
1. Go!AOP 的AOP实现依赖Class Autoload的 callback，通过替换原文件指向Proxy类实现。
1. ThinkPHP5 整合 Go!AOP 需要调整 autoload。

[0]: https://pecl.php.net/package/AOP
[1]: https://github.com/goaop/framework
[2]: http://127.0.0.1/tests/test1/test1
[3]: http://go.aopphp.com/
[4]: https://segmentfault.com/img/bV1gJa
[5]: https://segmentfault.com/img/bV1gJd
[6]: https://github.com/top-think/framework/blob/v5.0.10/library/think/Loader.php
[7]: https://github.com/goaop/framework/blob/0.6.1/src/Instrument/ClassLoading/AopComposerLoader.php
[8]: http://php.net/manual/zh/function.spl-autoload-register.php