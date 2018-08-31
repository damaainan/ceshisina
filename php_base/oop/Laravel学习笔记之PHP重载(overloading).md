## Laravel学习笔记之PHP重载(overloading)

来源：[https://segmentfault.com/a/1190000005998930](https://segmentfault.com/a/1190000005998930)

说明：本文主要讲述PHP中重载概念，由于Laravel框架中经常使用这块知识点，并且PHP的重载概念又与其他OOP语言如JAVA中重载概念不一样，故复习并记录相关知识点。同时，作者会将开发过程中的一些截图和代码黏上去，提高阅读效率。
## 重载(overloading)

在Laravel中就大量应用了重载相关知识，如在`IlluminateSupportFacadesFacade`中就用到了方法重载知识：使用魔术方法__callStatic()来动态创建类中未定义或不可见的静态方法。PHP中重载概念与其他的OOP语言如JAVA语言中重载概念还不一样，PHP中重载概念主要是：`动态的创建类属性和方法`，而不是一般的类中方法名一样而参数不一样。PHP中通过引入魔术方法来实现`动态的创建类属性和方法`，包括属性重载的魔术方法和方法重载的魔术方法。当然，重载是在类的外部发生的，所以所有魔术方法必须声明public，而且参数不能引用传递。

PHP中是可以动态创建一个类中未定义属性或方法的，这也是PHP这个语言的一个比较灵活的特性，如：

```php
class Person {

}

$person = new Person();
$person->name = 'PHP';
echo $person->name.PHP_EOL;
$person->age('18');
```

Person类中没有属性$name和方法age()，但PHP可以动态创建，echo出的$name值是'PHP'，访问未定义的age()方法并不报错。
### 属性重载

PHP中引入了4个魔术方法来实现属性重载：


* __set(string $name, array $value)

* __get(string $name)

* __isset(string $name)

* __unset(string $name)


1、当在类中定义魔术方法`__set()`时，给未定义或不可见属性赋值时会先触发`__set()`，可以使用`__set()`魔术方法来禁止动态创建属性：

```php
class Person {
    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            return $this->$name = $value;
        } else {
            return null;
        }
    }
}

$person = new Person();
$person->name = 'PHP';
echo $person->name.PHP_EOL;
```

这时想要动态创建$name属性就不可以了，返回null。

2、当在类中定义魔术方法`__get()`时，当读取未定义或不可见属性时就触发`__get()`方法：

```php
class Person {
    private $sex;
    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            return $this->$name = $value;
        } else {
            return null;
        }
    }

    public function __get($name)
    {
        return $name;
    }
}

$person = new Person();
$person->name = 'PHP';
echo $person->name.PHP_EOL;
echo $person->sex.PHP_EOL;
```

如果不写魔术方法`__get()`，当读取不可见属性$sex就报错，而这里返回的是`name`和`sex`字符串。

3、当在类中定义魔术方法`__isset()`时，当对未定义或不可见属性调用isset()或empty()方法时，就会先触发`__isset()`魔术方法：

```php
class Person {
    private $sex;
    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            return $this->$name = $value;
        } else {
            return null;
        }
    }

    public function __get($name)
    {
        return $name;
    }

    public function __isset($name)
    {
        echo $name;
    }
}

$person = new Person();
$person->name = 'PHP';
echo $person->name.PHP_EOL;
echo $person->sex.PHP_EOL;
echo isset($person->address).PHP_EOL;
```

如果没有魔术方法`__isset()`最后一行返回空，否则就触发该魔术方法。

4、同样的，魔术方法`__unset()`当使用unset()方法时触发：

```php
class Person {
    private $sex;
    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            return $this->$name = $value;
        } else {
            return null;
        }
    }

    public function __get($name)
    {
        return $name;
    }

    public function __isset($name)
    {
        echo $name;
    }

    public function __unset($name)
    {
        echo $name.PHP_EOL;
    }
}

$person = new Person();
$person->name = 'PHP';
echo $person->name.PHP_EOL;
echo $person->sex.PHP_EOL;
echo isset($person->address).PHP_EOL;
unset($person->name);
```
### 方法重载

上面是类属性重载，当类方法重载时，PHP提供了两个魔术方法：`__call()`和`__callStatic()`，`__call()`是动态创建对象方法触发，`__callStatic()`是动态创建类方法触发:

```php
class Person {
    private $sex;
    public function __set($name, $value)
    {
        if (isset($this->$name)) {
            return $this->$name = $value;
        } else {
            return null;
        }
    }

    public function __get($name)
    {
        return $name;
    }

    public function __isset($name)
    {
        echo $name;
    }

    public function __unset($name)
    {
        echo $name.PHP_EOL;
    }

    public function __call(string $method, array $args)
    {
        echo $method.'/'.implode(',', $args).PHP_EOL;
    }

    public function __callStatic(string $method, array $args)
    {
        echo $method.'/'.implode(',', $args).PHP_EOL;
    }
}

$person = new Person();

$person->name = 'PHP';
echo $person->name.PHP_EOL;
echo $person->sex.PHP_EOL;
echo isset($person->address).PHP_EOL;
unset($person->name);

$person->age('18');
Person::education('Master');
```

当调用对象方法age()时触发`__call()`魔术方法，且$args是一个数组，是要传递给$method方法的参数。方法返回字符串：`age/18`和`education/Master`。
## Laravel中方法重载使用

在使用Laravel的Facade这种模式时，是通过Facade帮我们代理从容器Container中取出所需要的服务Service，就不需要通过$app['config']这种方式取服务了，如：

```php
        $callback = Config::get('github.callback');
```

但是查看源码`IlluminateSupportFacadesConfig`，发现并没有`get()`这个静态方法：

```php
<?php

namespace Illuminate\Support\Facades;

/**
 * @see \Illuminate\Config\Repository
 */
class Config extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}

```

利用上面知识，当调用一个类中未定义或不可见的静态方法时，必然是调用了`__callStatic()`方法，发现`IlluminateSupportFacadesFacade`这个抽象类中定义了魔术方法`__callStatic()`：

```php
public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
```

其中，

```php
    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());//这里调用Config::getFacadeAccessor()，返回'config'，static是静态延迟绑定
    }
    
    /**
     * Resolve the facade root instance from the container.
     *
     * @param  string|object  $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }
        //这里是使用$app['config']从容器中解析，也就是实际上Facade貌似是帮我们从容器中解析Service，其实也是通过$app['config']这种方式去解析。
        //当然，有了Facade后，从容器中解析服务就不用受限于$app这个容器变量了。
        return static::$resolvedInstance[$name] = static::$app[$name];
    }
```

看到这里，我们知道当使用Config::get()方法时，会从容器中解析出名称为'config'这个Service，也就是这个Service中有我们需要的get()方法，那哪一个Service名字叫做'config'。实际上，观察Laravel源码包的目录结构也知道在哪了：`IlluminateConfigRepository`，这个服务就是我们需要的，里面get()方法源码：

```php
    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }
```

既然这个服务Service叫做config，那么容器类Application刚启动时就已经把所有需要的服务注册进来了，并且取了名字。实际上，'config'服务是在`IlluminateFoundationBootstrapLoadConfiguration`注册的，看bootstrap()方法源码：

```php
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $items = [];

        // First we will see if we have a cache configuration file. If we do, we'll load
        // the configuration items from that file so that it is very quick. Otherwise
        // we will need to spin through every configuration file and load them all.
        if (file_exists($cached = $app->getCachedConfigPath())) {
            $items = require $cached;

            $loadedFromCache = true;
        }

        $app->instance('config', $config = new Repository($items)); //在这里注册名叫config的服务，服务实体是Repository类

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the repository. This will make all of the
        // options available to the developer for use in various parts of this app.
        if (! isset($loadedFromCache)) {
            $this->loadConfigurationFiles($app, $config);
        }

        $app->detectEnvironment(function () use ($config) {
            return $config->get('app.env', 'production');
        });

        date_default_timezone_set($config['app.timezone']);

        mb_internal_encoding('UTF-8');
    }
```

这个启动方法做了一些环境监测、时间设置和编码设置。使用其他的Facade获取其他Service也是这样的过程。
`总结：基本学习了PHP的重载知识后，对使用Laravel的Facade这个方式来获取服务时有了更深入的了解。总之，多多使用Laravel来做一些东西和多多学习Laravel源码并模仿之，也是一件有趣的事情。`欢迎关注[Laravel-China][0]。

[RightCapital][1]招聘[Laravel DevOps][2]

[0]: https://laravel-china.org/
[1]: https://www.rightcapital.com
[2]: https://join.rightcapital.com