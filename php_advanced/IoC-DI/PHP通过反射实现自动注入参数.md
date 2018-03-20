## PHP通过反射实现自动注入参数

来源：[https://segmentfault.com/a/1190000011146414](https://segmentfault.com/a/1190000011146414)

现在的框架中都有一个容器， 而容器解决依赖的问题是通过反射来达到的，
<!--- more -->
首先先说明一下项目文件结构:

```
/ ROOT_PATH

├─src
│ ├─Controllers
│ │  └─IndexController.php
| ├─Application.php (核心，获得实例)
│ ├─Http.php
│ └─Request.php
│
├─vendor
│ └─autoload.php
│
├─composer.json
└─index.php
```

而我们要运行`IndexController.php`，而这个控制器的构造函数需要一个`Request`类，而`Request`类构造函数需要一个`Http`类。


-----


* IndexController.php


```php
<?php

namespace Waitmoonman\Reflex\Controllers;

use Waitmoonman\Reflex\Request;

class IndexController
{

    /**
     * 注入一个 Request 类
     * IndexController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        echo '我是 ' . __CLASS__ . '   我依赖' . $request->className;
    }

}
```


* Application.php


```php
<?php

    namespace Waitmoonman\Reflex;

    use Exception;
    use ReflectionClass;

    class Application
    {

        /*
         * @param $class
         * @param array $parameters
         * @return mixed
         * @throws Exception
         */
        public static function make($class, $parameters = [])
        {
            // 通过反射获取反射类
            $rel_class = new ReflectionClass($class);

            // 查看是否可以实例化
            if (! $rel_class->isInstantiable())
            {
                throw new Exception($class . ' 类不可实例化');
            }

            // 查看是否用构造函数
            $rel_method = $rel_class->getConstructor();

            // 没有构造函数的话，就可以直接 new 本类型了
            if (is_null($rel_method))
            {
                return new $class();
            }

            // 有构造函数的话就获取构造函数的参数
            $dependencies = $rel_method->getParameters();

            // 处理，把传入的索引数组变成关联数组， 键为函数参数的名字
            foreach ($parameters as $key => $value)
            {
                if (is_numeric($key))
                {
                    // 删除索引数组， 只留下关联数组
                    unset($parameters[$key]);

                    // 用参数的名字做为键
                    $parameters[$dependencies[$key]->name] = $value;
                }
            }

            // 处理依赖关系
            $actual_parameters = [];

            foreach ($dependencies as $dependenci)
            {
                // 获取对象名字，如果不是对象返回 null
                $class_name = $dependenci->getClass();
                // 获取变量的名字
                $var_name = $dependenci->getName();

                // 如果是对象， 则递归new
                if (array_key_exists($var_name, $parameters))
                {
                    $actual_parameters[] = $parameters[$var_name];
                }
                elseif (is_null($class_name))
                {
                    // null 则不是对象，看有没有默认值， 如果没有就要抛出异常
                    if (! $dependenci->isDefaultValueAvailable())
                    {
                        throw new Exception($var_name . ' 参数没有默认值');
                    }

                    $actual_parameters[] = $dependenci->getDefaultValue();
                }
                else
                {
                    $actual_parameters[] = self::make($class_name->getName());
                }

            }


            // 获得构造函数的数组之后就可以实例化了
            return $rel_class->newInstanceArgs($actual_parameters);
        }

    }
```


* Http.php


```php
<?php
namespace Waitmoonman\Reflex;

class Http
{
    public $className;

    public function __construct()
    {
        $this->className = __CLASS__;
    }
}
```


* Request.php


```php
<?php

namespace Waitmoonman\Reflex;

class Request
{
    public $className;

    public function __construct(Http $http)
    {
        $this->className = __CLASS__;

        $this->className = $this->className . '  ->  ' . $http->className;
    }
}
```


* index.php


```php
<?php

    // 要实现自动载入
    use Waitmoonman\Reflex\Application;

    require 'vendor/autoload.php';


    // new 一个 ReflectionClass 类， 放入需要实例的类名
    $ctl = Application::make(\Waitmoonman\Reflex\Controllers\IndexController::class);

    var_dump($ctl);
```


-----

输出:

```
我是 Waitmoonman\Reflex\Controllers\IndexController 我依赖Waitmoonman\Reflex\Request -> Waitmoonman\Reflex\Http
F:\phpStudy\WWW\reflex\index.php:12:
object(Waitmoonman\Reflex\Controllers\IndexController)[9]
```

这就是一个完整的反射类动态注入参数的实例。
以上代码可以查看我的[git仓库][0]

[0]: https://github.com/WaitMoonMan/design-pattern/tree/master/Reflex