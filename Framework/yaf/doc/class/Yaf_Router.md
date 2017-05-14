## The Yaf_Router class

##### 简介

Yaf的路由器, 负责分析请求中的request uri, 得出目标模板, 控制器, 动作.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Router

```php
final Yaf_Router {
protected array _routes ;
protected string _current_route ;
public Yaf_Router addRoute ( string $name ,
Yaf_Route_Interface $route );
public boolean addConfig ( Yaf_Config_Abstract $routes_config );
public array getRoutes ( void );
public array getRoute ( string $name );
public string getCurrentRoute ( void );
public boolean route ( Yaf_Request_Abstract $request );
public boolean isModuleName ( string $name );
}
```


属性说明
- _routes  
路由器已有的路由协议栈, 默认的栈底总是名为"default"的Yaf_Route_Static路由协议的实例.

- _current_route   
在路由成功后, 路由生效的路由协议名




## 名称

##### Yaf_Router::addRoute

(Since Yaf 1.0.0.5)

    public boolean Yaf_Router::addRoute( string  $name ,
                                        Yaf_Route_Interface  $route );
给路由器增加一个名为$name的路由协议


> 参数

$name   
要增加的路由协议的名字

$route  
要增加的路由协议, Yaf_Route_Interface的一个实例

> 返回值

成功返回Yaf_Router, 失败返回FALSE, 并抛出异常(或者触发错误)

> **Yaf_Router::addRoute 的例子**



```
<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{
        public function _initRoute(Yaf_Dispatcher $dispatcher) {
                /*
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

?>
```




## 名称

##### Yaf_Config::addConfig

(Since Yaf 1.0.0.5)

    public boolean Yaf_Config::addConfig( Yaf_Config_Abstract  $routes_config );
给路由器通过配置增加一簇路由协议


> 参数

$routers_config   
一个Yaf_Config_Abstract的实例, 它包含了一簇路由协议的定义, 一个例子是:

> **INI路由协议簇的例子**


```
;ini配置文件
[product]
routes.regex.type="regex"
routes.regex.route="#^list/([^/]*)/([^/]*)#"
routes.regex.default.controller=Index
routes.regex.default.action=action
routes.regex.map.1=name
routes.regex.map.2=value

routes.simple.type="simple"
routes.simple.controller=c
routes.simple.module=m
routes.simple.action=a

routes.supervar.type="supervar"
routes.supervar.varname=r

routes.rewrite.type="rewrite"
routes.rewrite.route="/product/:name/:value"
routes.rewrite.default.controller=product
routes.rewrite.default.action=info

```
    


> 返回值

成功返回Yaf_Config, 失败返回FALSE, 并抛出异常(或者触发错误)

> **Yaf_Config::addConfig 的例子**

```
<?php

class Bootstrap extends Yaf_Bootstrap_Abstract{
        public function _initRoute(Yaf_Dispatcher $dispatcher) {
                $router = Yaf_Dispatcher::getInstance()->getRouter();
                $router->addConfig(Yaf_Registry::get("config")->routes);
?>
```



## 名称

##### Yaf_Router::getRoutes

(Since Yaf 1.0.0.5)

    public array Yaf_Router::getRoutes( void  );
获取当前路由器中的所有路由协议


> 参数

void   
本方法不需要参数

> 返回值

成功返回当前路由器的路由协议栈内容, 失败返回FALSE

> **Yaf_Router::getRoutes 的例子**

```php
<?php
     $routes = Yaf_Dispatcher::getInstance()->getRouter()->getRoutes();
?>
```


## 名称

##### Yaf_Router::getRoute

(Since Yaf 1.0.0.5)

    public Yaf_Route_Interface Yaf_Router::getRoute( string  $name );
获取当前路由器的路由协议栈中名为$name的协议


> 参数

$name   
要获取的协议名

> 返回值

成功返回目的路由协议, 失败返回NULL

> **Yaf_Router::getRoute 的例子**

```php
<?php
/** 路由器中永远都存在一个名为default的Yaf_Route_Static路由协议实例 */
$routes = Yaf_Dispatcher::getInstance()->getRouter()->getRoute('default');
?>
```


## 名称

##### Yaf_Router::getCurrentRoute

(Since Yaf 1.0.0.5)

    public string Yaf_Router::getCurrentRoute( void  );
在路由结束以后, 获取路由匹配成功, 路由生效的路由协议名


> 参数

void   
本方法不需要参数

> 返回值

成功返回生效的路由协议名, 失败返回NULL

> **Yaf_Router::getCurrentRoute 的例子**

```php
<?php
class UserPlugin extends Yaf_Plugin_Abstract {

        public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
                echo "生效的路由协议是:" . Yaf_Dispatcher::getInstance()->getRouter()->getCurrentRoute();
        }
}
?>
```


## 名称

##### Yaf_Router::isModuleName

(Since Yaf 1.0.0.5)

    public boolean Yaf_Router::isModuleName( string  $name );
判断一个Module名, 是否是申明存在的Module

[注意]    注意
通过ap.modules在配置文件中申明加载的模块名列表

> 参数

$name   
Module名

> 返回值

如果是返回TRUE, 不是返回FALSE

> **Yaf_Router::isModuleName 的例子**

```php
<?php
$routes = Yaf_Dispatcher::getInstance()->isModuleNamer("Index")
?>
```




## 名称

##### Yaf_Router::route

(Since Yaf 1.0.0.5)

    public boolean Yaf_Router::route( Yaf_Request_Abstract  $request );
路由一个请求, 本方法不需要主动调用, Yaf_Dispatcher::dispatch会自动调用本方法


> 参数

$request   
一个Yaf_Request_Abstract实例

> 返回值

成功返回TRUE, 失败返回FALSE

> **Yaf_Router::route 的例子**

```php
<?php
?>
```






























