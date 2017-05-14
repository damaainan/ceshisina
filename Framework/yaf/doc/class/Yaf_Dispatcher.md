## The Yaf_Dispatcher class

### 简介

Yaf_Dispatcher实现了MVC中的C分发, 它由Yaf_Application负责初始化, 然后由Yaf_Application::run启动, 它协调路由来的请求, 并分发和执行发现的动作, 并收集动作产生的响应, 输出响应给请求者, 并在整个过程完成以后返回响应.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Dispatcher.


```php
final Yaf_Dispatcher {
protected static Yaf_Dispatcher _instance ;
protected Yaf_Router_Interface _router ;
protected Yaf_View_Abstract _view ;
protected Yaf_Request_Abstract _request ;
protected array _plugins ;
protected boolean _render ;
protected boolean _return_response = FALSE ;
protected boolean _instantly_flush = FALSE ;
protected string _default_module ;
protected string _default_controller ;
protected string _default_action ;
public static Yaf_Dispatcher getInstance ( void );
public Yaf_Dispatcher disableView ( void );
public Yaf_Dispatcher enableView ( void );
public boolean autoRender ( bool $flag );
public Yaf_Dispatcher returnResponse ( boolean $flag );
public Yaf_Dispatcher flushInstantly ( boolean $flag );
public Yaf_Dispatcher setErrorHandler ( mixed $callback ,
int $error_type = E_ALL | E_STRICT );
public Yaf_Application getApplication ( void );
public Yaf_Request_Abstract getRequest ( void );
public Yaf_Router_Interface getRouter ( void );
public Yaf_Dispatcher registerPlugin ( Yaf_Plugin_Abstract $plugin );
public Boolean setAppDirectory ( string $directory );
public Yaf_Dispatcher setRequest ( Yaf_Request_Abstract $request );
public Yaf_View_Interface initView ( void );
public Yaf_Dispatcher setView ( Yaf_View_Interface $view );
public Yaf_Dispatcher setDefaultModule ( string $default_module_name );
public Yaf_Dispatcher setDefaultController ( string $default_controller_name );
public Yaf_Dispatcher setDefaultAction ( string $default_action_name );
public Yaf_Dispatcher throwException ( boolean $switch = FALSE );
public Yaf_Dispatcher catchException ( boolean $switch = FALSE );
public Yaf_Response_Abstract dispatch ( Yaf_Request_Abstract $request );
}
```


属性说明
- _instance  
Yaf_Dispatcher实现了单利模式, 此属性保存当前实例

- _request  
当前的请求

- _router  
路由器, 在Yaf0.1之前, 路由器是可更改的, 但是Yaf0.2以后, 随着路由器和路由协议的分离, 各种路由都可以通过配置路由协议来实现, 也就取消了自定义路由器的功能

- _view  
当前的视图引擎, 可以通过Yaf_Dispatcher::setView来替换视图引擎为自定义视图引擎(比如Smary/Firekylin等常见引擎)

- _plugins  
已经注册的插件, 插件一经注册, 就不能更改和删除

- _render  
标示着,是否在动作执行完成后, 调用视图引擎的render方法, 产生响应. 可以通过Yaf_Dispatcher::disableView和Yaf_Dispatcher::enableView来切换开关状态

- _return_response  
标示着,是否在产生响应以后, 不自动输出给客户端, 而是返回给调用者. 可以通过Yaf_Dispatcher::returnResponse来切换开关状态

- _instantly_flush  
标示着, 是否在有输出的时候, 直接响应给客户端, 不写入Yaf_Response_Abstract对象.

> 注意
如果此属性为TRUE, 那么将忽略Yaf_Dispatcher::$_return_response

- _default_module  
默认的模块名, 在路由的时候, 如果没有指明模块, 则会使用这个值, 也可以通过配置文件中的ap.dispatcher.defaultModule来指定

- _default_controller  
默认的控制器名, 在路由的时候, 如果没有指明控制器, 则会使用这个值, 也可以通过配置文件中的ap.dispatcher.defaultController来指定

- _default_action  
默认的动作名, 在路由的时候, 如果没有指明动作, 则会使用这个值, 也可以通过配置文件中的ap.dispatcher.defaultAction来指定


## 名称

##### Yaf_Dispatcher::getInstance

(Since Yaf 1.0.0.5)

    public static Yaf_Dispatcher Yaf_Dispatcher::getInstance( void  );
获取当前的Yaf_Dispatcher实例

> 参数

void  
该方法不需要参数

> 返回值

Yaf_Dispatcher

> **Yaf_Dispatcher::getInstance 的例子**

```php
     <?php
     $dispatcher = Yaf_Dispatcher::getInstance();
     ?>
```



## 名称

##### Yaf_Dispatcher::disableView

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::disableView( void  );
关闭自动Render. 默认是开启的, 在动作执行完成以后, Yaf会自动render以动作名命名的视图模板文件.

> 参数

void  
本方法不需要参数

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::disableView的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
         /**
          * Controller的init方法会被自动首先调用
          */
          public function init() {
             /**
              * 如果是Ajax请求, 则关闭HTML输出
              */
             if ($this->getRequest()->isXmlHttpRequest()) {
                 Yaf_Dispatcher::getInstance()->disableView();
             }
          }
     }
     ?>
```


## 名称

##### Yaf_Dispatcher::enableView

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::enableView( void  );
开启自动Render. 默认是开启的, 在动作执行完成以后, Yaf会自动render以动作名命名的视图模板文件.

> 参数

void  
本方法不需要参数

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::enableView的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
         /**
          * Controller的init方法会被自动首先调用
          */
          public function init() {
             /**
              * 如果不是Ajax请求, 则开启HTML输出
              */
             if (!$this->getRequest()->isXmlHttpRequest()) {
                 Yaf_Dispatcher::getInstance()->enableView();
             }
          }
     }
     ?>
```



## 名称

##### Yaf_Dispatcher::autoRender

(Since Yaf 1.0.0.11)

    public boolean Yaf_Dispatcher::autoRender( boolean  $switch );
开启/关闭自动渲染功能. 在开启的情况下(Yaf默认开启), Action执行完成以后, Yaf会自动调用View引擎去渲染该Action对应的视图模板.

> 参数

$switch  
开启状态

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::autoRender的例子**

```php
     <?php
class IndexController extends Yaf_Controller_Abstract {
     public function init() {
        if ($this->getRequest()->isXmlHttpRequest()) {
            //如果是Ajax请求, 关闭自动渲染, 由我们手工返回Json响应
            Yaf_Dispatcher::getInstance()->autoRender(FALSE);
        }
     }
}
     ?>
```



## 名称

##### Yaf_Dispatcher::returnResponse

(Since Yaf 1.0.0.5)

    public void Yaf_Dispatcher::returnResponse( boolean  $switch );
是否返回Response对象, 如果启用, 则Response对象在分发完成以后不会自动输出给请求端, 而是交给程序员自己控制输出.

> 参数

$switch  
开启状态

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::returnResponse的例子**

```php
     <?php
     $application = new Yaf_Application("config.ini"); 

     /* 关闭自动响应, 交给rd自己输出*/
     $response =
     $application->getDispatcher()->returnResponse(TRUE)->getApplication()->run();

     /** 输出响应*/
     $response->response();
     ?>
```



## 名称

##### Yaf_Dispatcher::flushInstantly

(Since Yaf 1.0.0.5)

    public void Yaf_Dispatcher::flushInstantly( boolean  $switch );
切换自动响应. 在Yaf_Dispatcher::enableView()的情况下, 会使得Yaf_Dispatcher调用Yaf_Controller_Abstract::display方法, 直接输出响应给请求端

> 参数

$switch  
开启状态

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::flushInstantly的例子**

```php
     <?php
     $application = new Yaf_Application("config.ini");

     /* 立即输出响应 */
     Yaf_Dispatcher::getInstance()->flushInstantly(TRUE);

     /* 此时会调用Yaf_Controller_Abstract::display方法 */
     $application->run();

     ?>
```


## 名称

##### Yaf_Dispatcher::setErrorHandler

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::setErrorHandler( mixed  $callback ,
                                                int  $error_code = E_ALL | E_STRICT );
设置错误处理函数, 一般在appcation.throwException关闭的情况下, Yaf会在出错的时候触发错误, 这个时候, 如果设置了错误处理函数, 则会把控制交给错误处理函数处理.

> 参数

$callback  
错误处理函数, 这个函数需要最少接受俩个参数: 错误代码($error_code)和错误信息($error_message), 可选的还可以接受三个参数: 错误文件($err_file), 错误行($err_line)和错误上下文($errcontext)

$error_code
要捕获的错误类型

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::setErrorHandler的例子**

```php
<?php
/** 
 * 一般可放在Bootstrap中定义错误处理函数
 */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
    case YAF_ERR_NOTFOUND_CONTROLLER:
    case YAF_ERR_NOTFOUND_MODULE:
    case YAF_ERR_NOTFOUND_ACTION:
         header("Not Found");
    break;

    default:
        echo "Unknown error type: [$errno] $errstr<br />\n";
        break;
    }

    return true;
}

Yaf_Dispatcher::getInstance()->setErrorHandler("myErrorHandler");
?>
```


## 名称

##### Yaf_Dispatcher::getApplication

(Since Yaf 1.0.0.8)

    public Yaf_Application Yaf_Dispatcher::getApplication( void  );
获取当前的Yaf_Application实例

> 参数

void  
该方法不需要参数

> 返回值

Yaf_Application实例

> **Yaf_Dispatcher::getApplication 的例子**

```php
     <?php
     $application = Yaf_Dispatcher::getInstance()->getApplication();
     //不过, 还是推荐大家使用
     $application = Application::app();
     ?>
```


## 名称

##### Yaf_Dispatcher::getRouter

(Since Yaf 1.0.0.5)

    public Yaf_Router Yaf_Dispatcher::getRouter( void  );
获取路由器

> 参数

void  
该方法不需要参数

> 返回值

Yaf_Router实例

> **Yaf_Dispatcher::getRouter 的例子**

```php
     <?php
     $router = Yaf_Dispatcher::getInstance()->getRouter();
     ?>
```


## 名称

##### Yaf_Dispatcher::getRequest

(Since Yaf 1.0.0.5)

    public Yaf_Request_Abstract Yaf_Dispatcher::getRequest( void  );
获取当前的请求实例

> 参数

void  
该方法不需要参数

> 返回值

Yaf_Request_Abstract实例

> **Yaf_Dispatcher::getRequest 的例子**

```php
     <?php
     $request = Yaf_Dispatcher::getInstance()->getRequest();
     ?>
```



## 名称

##### Yaf_Dispatcher::registerPlugin

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::registerPlugin( Yaf_Plugin_Abstract  $plugin );
注册一个插件.

> 参数

$plugin  
一个Yaf_Plugin_Abstract派生类的实例.

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::registerPlugin的例子**

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
}


/** 
 * 插件类定义
 * UserPlugin.php
 */
class UserPlugin extends Yaf_Plugin_Abstract {
    
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin routerStartup called <br/>\n";
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin routerShutdown called <br/>\n";
    }

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin DispatchLoopStartup called <br/>\n";
    }

    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin PreDispatch called <br/>\n";
    }

    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin postDispatch called <br/>\n";
    }
  
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin DispatchLoopShutdown called <br/>\n";
    }
}
```



## 名称

##### Yaf_Dispatcher::setAppDirectory

(Since Yaf 1.0.0.0)

boolean Yaf_Dispatcher::setAppDirectory(string $directory);
改变APPLICATION_PATH, 在这之后, 将从新的APPLICATION_PATH下加载控制器/视图, 但注意, 不会改变自动加载的路径.

> 参数

$directory  
绝度路径的APPLICATION_PATH

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::setAppDirectory 的例子**

```php
<?php
$config = array(
        "ap" => array(
                "directory" => "/usr/local/www/ap",
        ),
);
$app = new Yaf_Application($config);
$app->getDispatcher()->setAppDirectory("/usr/local/new/application")->getApplication()->run();
?>
```

## 名称

##### Yaf_Dispatcher::setRequest

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::setRequest( Yaf_Request_Abstract  $request );
设置请求对象

> 参数

$request  
一个Yaf_Request_Abstract实例

$error_code
要捕获的错误类型

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::setRequest的例子**

```php
<?php
$request = new Yaf_Request_Simple("Index", "Index", "index");
Yaf_Dispatcher::getInstance()->setRequest($request);
```


## 名称

##### Yaf_Dispatcher::initView

(Since Yaf 1.0.0.9)

    public Yaf_View_Interface Yaf_Dispatcher::initView( string  $tpl_dir );
初始化视图引擎, 因为Yaf采用延迟实例化视图引擎的策略, 所以只有在使用前调用此方法, 视图引擎才会被实例化

[注意]    注意
如果你需要自定义视图引擎, 那么需要在调用Yaf_Dispatcher::setView自定义视图引擎之后, 才可以调用此方法, 否则将得不到正确的视图引擎, 因为默认的此方法会实例化一个Yaf_View_Simple视图引擎
> 参数

$tpl_dir  
视图的模板目录的绝对路径.

> 返回值

Yaf_View_Interface实例

> **Yaf_Dispatcher::initView 的例子**

```php
     <?php
     class Bootstrap extends Yaf_Bootstrap_Abstract {
        public function _initViewParameters(Yaf_Dispatcher $dispatcher) {
         $dispatcher->initView(APPLICATION_PATH . "/views/")->assign("webroot", WEBROOT);
        }
     }
     ?>
```


## 名称

##### Yaf_Dispatcher::setView

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::setView( Yaf_View_Interface  $request );
设置视图引擎

> 参数

$view  
一个实现了Yaf_View_Interface的视图引擎实例

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::setView的例子**

```php
<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{
 /**
  * 自定义视图引擎
  */
        public function _initSmarty(Yaf_Dispatcher $dispatcher) {
                $smarty = new Smarty_Adapter(null, Yaf_Registry::get("config")->get("smarty"));
                Yaf_Dispatcher::getInstance()->setView($smarty);
  }
}


/**
 * 视图引擎定义
 * Smarty/Adapter.php
 */
class Smarty_Adapter implements Yaf_View_Interface
{
    /**
     * Smarty object
     * @var Smarty
     */
    public $_smarty;
 
    /**
     * Constructor
     *
     * @param string $tmplPath
     * @param array $extraParams
     * @return void
     */
    public function __construct($tmplPath = null, $extraParams = array()) {

        require "Smarty.class.php";
        $this->_smarty = new Smarty;
 
        if (null !== $tmplPath) {
            $this->setScriptPath($tmplPath);
        }
 
        foreach ($extraParams as $key => $value) {
            $this->_smarty->$key = $value;
        }
    }
 
    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing
     * an array of key => value pairs to set en masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or
     * array of key => value pairs)
     * @param mixed $value (Optional) If assigning a named variable,
     * use this as the value.
     * @return void
     */
    public function assign($spec, $value = null) {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
            return;
        }
 
        $this->_smarty->assign($spec, $value);
    }
 
    /**
     * Processes a template and returns the output.
     *
     * @param string $name The template to process.
     * @return string The output.
     */
    public function render($name) {
        return $this->_smarty->fetch($name);
    }
}
?>

```


## 名称

##### Yaf_Dispatcher::setDefaultController

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::setDefaultController( string  $default_controller_name );
设置路由的默认控制器, 如果在路由结果中不包含控制器信息, 则会使用此默认控制器作为路由控制器结果

> 参数

$default_controller_name  
默认控制器名, 请注意需要首字母大写

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::setDefaultController的例子**

```php
<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

        public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
                /**
                 * 这个只是举例, 本身Yaf默认的就是"Index"
                 */
                $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
        }
}

```


## 名称

##### Yaf_Dispatcher::setDefaultModule

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::setDefaultModule( string  $default_module_name );
设置路由的默认模块, 如果在路由结果中不包含模块信息, 则会使用此默认模块作为路由模块结果

> 参数

$default_module_name  
默认模块名, 请注意需要首字母大写

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::setDefaultModule的例子**

```php
<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

        public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
                /**
                 * 这个只是举例, 本身Yaf默认的就是"Index"
                 */
                $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
        }
}
```

## 名称

##### Yaf_Dispatcher::setDefaultAction

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::setDefaultAction( string  $default_module_name );
设置路由的默认动作, 如果在路由结果中不包含动作信息, 则会使用此默认动作作为路由动作结果

> 参数

$default_module_name  
默认动作名, 请注意需要全部小写

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::setDefaultAction的例子**

```php
<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

        public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
                /**
                 * 这个只是举例, 本身Yaf默认的就是"Index"
                 */
                $dispatcher->setDefaultController("Index")->setDefaultAction("index");
        }
}

```


## 名称

##### Yaf_Dispatcher::throwException

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::throwException( boolean  $switch );
切换在Yaf出错的时候抛出异常, 还是触发错误.

当然,也可以在配置文件中使用ap.dispatcher.thorwException=$switch达到同样的效果, 默认的是开启状态.

> 参数

$switch  
如果为TRUE,则Yaf在出错的时候采用抛出异常的方式. 如果为FALSE, 则Yaf在出错的时候采用触发错误的方式.

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::throwException的例子**

```php
     <?php
     $app = new Yaf_Application("conf.ini");
     /**
      * 关闭抛出异常
      */
     Yaf_Dispatcher::getInstance()->throwException(FALSE);
     ?>
     
```


## 名称

##### Yaf_Dispatcher::catchException

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::catchException( boolean  $switch );
在ap.dispatcher.throwException开启的状态下, 是否启用默认捕获异常机制

当然,也可以在配置文件中使用ap.dispatcher.catchException=$switch达到同样的效果, 默认的是开启状态.

> 参数

$switch  
如果为TRUE, 则在有未捕获异常的时候, Yaf会交给Error Controller的Error Action处理.

> 返回值

成功返回Yaf_Dispatcher, 失败返回FALSE

> **Yaf_Dispatcher::catchException的例子**

```php
     <?php
     $app = new Yaf_Application("conf.ini");
     /**
      * 开启捕获异常
      */
     Yaf_Dispatcher::getInstance()->catchException(TRUE);
     ?>
```


## 名称

##### Yaf_Dispatcher::dispatch

(Since Yaf 1.0.0.5)

    public boolean Yaf_Dispatcher::dispatch( Yaf_Request_Abstract  $request );
开始处理流程, 一般的不需要用户调用此方法, Yaf_Application::run 会自动调用此方法.

> 参数

$request  
一个Yaf_Request_Abstart实例

> 返回值

成功返回一个Yaf_Response_Abstract实例, 错误会抛出异常.













