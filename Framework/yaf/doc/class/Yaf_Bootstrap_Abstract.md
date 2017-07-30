## The Yaf_Bootstrap class

### 简介

Yaf_Bootstrap_Abstract提供了一个可以定制Yaf_Application的最早的时机, 它相当于一段引导, 入口程序. 它本身没有定义任何方法.但任何继承自Yaf_Bootstrap的类中的以_init开头的方法, 都会在`Yaf_Application::bootstrap`时刻被调用. 调用的顺序和这些方法在类中的定义顺序相同. Yaf保证这种调用顺序.

> 注意 这些方法, 都可以接受一个Yaf_Dispatcher参数. 

例子 

> **Yaf_Bootstrap_Abstract的例子**

```php
<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{
                /**
                 * 注册一个插件
                 * 插件的目录是在application_directory/plugins
                 */
        public function _initPlugin(Yaf_Dispatcher $dispatcher) {
                $user = new UserPlugin();
                $dispatcher->registerPlugin($user);
        }

 /**
  * 添加配置中的路由
  */
        public function _initRoute(Yaf_Dispatcher $dispatcher) {
                $router = Yaf_Dispatcher::getInstance()->getRouter();
                $router->addConfig(Yaf_Registry::get("config")->routes);
                /**
                 * 添加一个路由
                 */
                $route  = new Yaf_Route_Rewrite(
                        "/product/list/:id/",
                        array(
                                "controller" => "product",
                                "action"         => "info",
                        )
                );

                $router->addRoute('dummy', $route);
        }

 /**
  * 自定义视图引擎
  */
        public function _initSmarty(Yaf_Dispatcher $dispatcher) {
                $smarty = new Smarty_Adapter(null, Yaf_Registry::get("config")->get("smarty"));
                Yaf_Dispatcher::getInstance()->setView($smarty);
        }
}
```
    
在入口文件:

```php
<?php
/* 默认的, Yaf_Application将会读取配置文件中在php.ini中设置的ap.environ的配置节 */
$application = new Yaf_Application("conf/sample.ini");

/** 
 * 如果没有关闭自动response(通过Yaf_Dispatcher::getInstance()->autoResponse(FALSE)), 
 * 则$response会被自动输出, 此处也不需要再次输出Response
 */
$response = $application ->bootstrap()/*实例化Bootstrap, 依次调用Bootstrap中所有_init开头的方法*/
        ->run();
?>
```

