## Bootstrap

### 简介

Bootstrap, 也叫做引导程序. 它是Yaf提供的一个全局配置的入口, 在Bootstrap中, 你可以做很多全局自定义的工作.



## 使用Bootstrap

在一个Yaf_Application被实例化之后, 运行(Yaf_Application::run)之前, 可选的我们可以运行Yaf_Application::bootstrap

> **调用Bootstrap**

```php
<?php
$app = new Yaf_Application("conf.ini");
$app
 ->bootstrap() //可选的调用
 ->run();
}
```
   

当bootstrap被调用的时刻, Yaf_Application就会默认的在APPLICATION_PATH下, 寻找Bootstrap.php, 而这个文件中, 必须定义一个Bootstrap类, 而这个类也必须继承自Yaf_Bootstrap_Abstract.

实例化成功之后, 所有在Bootstrap类中定义的, 以_init开头的方法, 都会被依次调用, 而这些方法都可以接受一个Yaf_Dispatcher实例作为参数.

> 注意
也可以通过在配置文件中修改application.bootstrap来变更Bootstrap类的位置.

一个Bootstrap的例子:

> **Bootstrap**

```php
<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{

        public function _initConfig() {
                $config = Yaf_Application::app()->getConfig();
                Yaf_Registry::set("config", $config);
        }

        public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
                $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
        }
}
    
```


> 注意
方法在Bootstrap类中的定义出现顺序, 决定了它们的被调用顺序. 比如对于上面的例子, _initConfig会第一个被调用.


