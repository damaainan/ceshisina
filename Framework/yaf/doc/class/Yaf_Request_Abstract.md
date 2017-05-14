## The Yaf_Request_Abstract class

### 简介
代表了一个实际请求, 一般的不用自己实例化它, Yaf_Application在run以后会自动根据当前请求实例它

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Request_Abstract

```php
abstract Yaf_Request_Abstract {
protected string _method ;
protected string _module ;
protected string _controller ;
protected string _action ;
protected array _params ;
protected string _language ;
protected string _base_uri ;
protected string _request_uri ;
protected boolean _dispatched ;
protected boolean _routed ;
public string getModuleName ( void );
public string getControllerName ( void );
public string getActionName ( void );
public boolean setModuleName ( string $name );
public boolean setControllerName ( string $name );
public boolean setActionName ( string $name );
public Exception getException ( void );
public mixed getParams ( void );
public mixed getParam ( string $name ,
mixed $dafault = NULL );
public mixed setParam ( string $name ,
mixed $value );
public mixed getMethod ( void );
abstract public mixed getLanguage ( void );
abstract public mixed getQuery ( string $name = NULL );
abstract public mixed getPost ( string $name = NULL );
abstract public mixed getEnv ( string $name = NULL );
abstract public mixed getServer ( string $name = NULL );
abstract public mixed getCookie ( string $name = NULL );
abstract public mixed getFiles ( string $name = NULL );
abstract public bool isGet ( void );
abstract public bool isPost ( void );
abstract public bool isHead ( void );
abstract public bool isXmlHttpRequest ( void );
abstract public bool isPut ( void );
abstract public bool isDelete ( void );
abstract public bool isOption ( void );
abstract public bool isCli ( void );
public bool isDispatched ( void );
public bool setDispatched ( void );
public bool isRouted ( void );
public bool setRouted ( void );
}

```


属性说明

- _method  
当前请求的Method, 对于命令行来说, Method为"CLI"

- _language  
当前请求的希望接受的语言, 对于Http请求来说, 这个值来自分析请求头Accept-Language. 对于不能鉴别的情况, 这个值为NULL.

- _module  
在路由完成后, 请求被分配到的模块名

- _controller  
在路由完成后, 请求被分配到的控制器名

- _action  
在路由完成后, 请求被分配到的动作名

- _params  
当前请求的附加参数

- _routed  
表示当前请求是否已经完成路由

- _dispatched  
表示当前请求是否已经完成分发

- _request_uri  
当前请求的Request URI

- _base_uri  
当前请求Request URI要忽略的前缀, 一般不需要手工设置, Yaf会自己分析. 只是当Yaf分析出错的时候, 可以通过application.baseUri来手工设置.

## The Yaf_Request_Http class

### 简介
代表了一个实际的Http请求, 一般的不用自己实例化它, Yaf_Application在run以后会自动根据当前请求实例它

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Request_Http


```php
final Yaf_Request_Http extends Yaf_Request_Abstract {
public void __construct ( string $request_uri = NULL ,
string $base_uri = NULL );
public mixed getLanguage ( void );
public mixed getQuery ( string $name = NULL );
public mixed getPost ( string $name = NULL );
public mixed getEnv ( string $name = NULL );
public mixed getServer ( string $name = NULL );
public mixed getCookie ( string $name = NULL );
public mixed getFiles ( string $name = NULL );
public bool isGet ( void );
public bool isPost ( void );
public bool isHead ( void );
public bool isXmlHttpRequest ( void );
public bool isPut ( void );
public bool isDelete ( void );
public bool isOption ( void );
public bool isCli ( void );
public bool isDispatched ( void );
public bool setDispatched ( void );
public bool isRouted ( void );
public bool setRouted ( void );
public string getBaseUri ( void );
public boolean setBaseUri ( string $base_uri );
public string getRequestUri ( void );
}
```


属性说明


## The Yaf_Request_Simple class

### 简介
代表了一个实际的请求, 一般的不用自己实例化它, Yaf_Application在run以后会自动根据当前请求实例它

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Request_Simple

```php
final Yaf_Request_Simple extends Yaf_Request_Abstract {
public void __construct ( string $module ,
string $controller ,
string $action ,
string $method ,
array $params = NULL );
public mixed getLanguage ( void );
public mixed getQuery ( string $name = NULL );
public mixed getPost ( string $name = NULL );
public mixed getEnv ( string $name = NULL );
public mixed getServer ( string $name = NULL );
public mixed getCookie ( string $name = NULL );
public mixed getFiles ( string $name = NULL );
public bool isGet ( void );
public bool isPost ( void );
public bool isHead ( void );
public bool isXmlHttpRequest ( void );
public bool isPut ( void );
public bool isDelete ( void );
public bool isOption ( void );
public bool isSimple ( void );
public bool isDispatched ( void );
public bool setDispatched ( void );
public bool isRouted ( void );
public bool setRouted ( void );
}
```
属性说明


## 名称

##### Yaf_Request_Abstract::getException

(Since Yaf 1.0.0.12)

    public Exception Yaf_Request_Abstract::getException( void  );
本方法主要用于在异常捕获模式下, 在异常发生的情况时流程进入Error控制器的error动作时, 获取当前发生的异常对象

> 参数

void  
该方法不需要参数

>  返回值

在有异常的情况下, 返回当前异常对象. 没有异常的情况下, 返回NULL

> **Yaf_Request_Abstract::getException 的例子**

```php
     <?php
     class ErrorController extends Yaf_Controller_Abstract {
        public funciton errorAction() {
            $exception = $this->getRequest()->getException();
        }
     }
     ?>
```

## 名称

##### Yaf_Request_Abstract::getModuleName

(Since Yaf 1.0.0.5)

    public string Yaf_Request_Abstract::getModuleName( void  );
获取当前请求被路由到的模块名.

> 参数

void  
该方法不需要参数

> 返回值

路由成功以后, 返回当前被分发处理此次请求的模块名. 路由之前, 返回NULL

> **Yaf_Request_Abstract::getModuleName 的例子**

```php
     <?php
     class ErrorController extends Yaf_Controller_Abstract {
        public funciton errorAction() {
            echo "current Module:" . $this->getRequest()->getModuleName();
        }
     }
     ?>
```


## 名称

##### Yaf_Request_Abstract::getControllerName

(Since Yaf 1.0.0.5)

    public string Yaf_Request_Abstract::getControllerName( void  );
获取当前请求被路由到的控制器名.

> 参数

void  
该方法不需要参数

> 返回值

路由成功以后, 返回当前被分发处理此次请求的控制器名. 路由之前, 返回NULL

> **Yaf_Request_Abstract::getControllerName 的例子**

```php
     <?php
     class ErrorController extends Yaf_Controller_Abstract {
        public funciton errorAction() {
            echo "current Controller:" . $this->getRequest()->getControllerName();
        }
     }
     ?>
```

## 名称

##### Yaf_Request_Abstract::getActionName

(Since Yaf 1.0.0.5)

    public string Yaf_Request_Abstract::getActionName( void  );
获取当前请求被路由到的动作(Action)名.

> 参数

void  
该方法不需要参数

> 返回值

路由成功以后, 返回当前被分发处理此次请求的动作名. 路由之前, 返回NULL

> **Yaf_Request_Abstract::getActionName 的例子**

```php
     <?php
     class ErrorController extends Yaf_Controller_Abstract {
        public funciton errorAction() {
            echo "current Action:" . $this->getRequest()->getActionName();
        }
     }
     ?>
```



## 名称

##### Yaf_Request_Abstract::getParams

(Since Yaf 1.0.0.5)

    public array Yaf_Request_Abstract::getParams( void  );
获取当前请求中的所有路由参数, 路由参数不是指$_GET或者$_POST, 而是在路由过程中, 路由协议根据Request Uri分析出的请求参数.

比如, 对于默认的路由协议Yaf_Route_Static, 路由如下请求URL: http://www.domain.com/module/controller/action/name1/value1/name2/value2/ 路由结束后将会得到俩个路由参数, name1和name2, 值分别是value1, value2.

> 注意
路由参数和$_GET,$_POST一样, 是来自用户的输入, 不是可信的. 使用前需要做安全过滤.

> 参数

void  
本方法不需要参数

> 返回值

当前所有的路由参数

> **Yaf_Request_Abstract::getParams的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
            $this->getRequest()->getParams();
        }
     }
     ?>
```



## 名称

##### Yaf_Request_Abstract::getParam

(Since Yaf 1.0.0.5)

    public string Yaf_Request_Abstract::getParam( string  $name ,
                                                    mixed  $default_value = NULL );
获取当前请求中的路由参数, 路由参数不是指$_GET或者$_POST, 而是在路由过程中, 路由协议根据Request Uri分析出的请求参数.

比如, 对于默认的路由协议Yaf_Route_Static, 路由如下请求URL: http://www.domain.com/module/controller/action/name1/value1/name2/value2/ 路由结束后将会得到俩个路由参数, name1和name2, 值分别是value1, value2.

> 注意
路由参数和$_GET,$_POST一样, 是来自用户的输入, 不是可信的. 使用前需要做安全过滤.

>参数

$name  
要获取的路由参数名

$default_value   
如果设定此参数, 如果没有找到$name路由参数, 则返回此参数值.

> 返回值

找到返回对应的路由参数值, 如果没有找到, 而又设置了$default_value, 则返回default_value, 否则返回NULL.

> **Yaf_Request_Abstract::getParam 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
            echo "user id:" . $this->getRequest()->getParam("userid", 0);
        }
     }
     ?>
```


## 名称

##### Yaf_Request_Abstract::setParam

(Since Yaf 1.0.0.5)

    public boolean Yaf_Request_Abstract::setParam( string  $name ,
                                                    mixed  $value );
为当前的请求,设置路由参数.

> 参数

$name  
路由参数名

$value  
值

> 返回值

成功返回Yaf_Request_Abstract实例自身, 失败返回FALSE

> **Yaf_Request_Abstract::setParam 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
            $this->getRequest()->setParam("userid", 0);
        }
     }
     ?>
```


## 名称

##### Yaf_Request_Abstract::getMethod

(Since Yaf 1.0.0.5)

    public string Yaf_Request_Abstract::getMethod( void  );
获取当前请求的类型, 可能的返回值为GET,POST,HEAD,PUT,CLI等.

> 参数

void  
本方法不需要参数

> 返回值  

当前请求的类型.

> **Yaf_Request_Abstract::getMethod的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
          if ($this->getRequest()->getMethod() == "CLI") {
             echo "running in cli mode";
          }
        }
     }
     ?>
```




## 名称

##### Yaf_Request_Abstract::isCli

(Since Yaf 1.0.0.5)

    public string Yaf_Request_Abstract::isCli( void  );
获取当前请求是否为CLI请求

> 参数

void  
本方法不需要参数

> 返回值

是CLI请求返回TRUE, 不是返回FALSE

> **Yaf_Request_Abstract::isCli的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
          if ($this->getRequest()->isCli()) {
             echo "running in Cli mode";
          }
        }
     }
     ?>
```



## 名称

##### Yaf_Request_Abstract::isGet

(Since Yaf 1.0.0.5)

    public string Yaf_Request_Abstract::isGet( void  );
获取当前请求是否为GET请求

> 参数

void  
本方法不需要参数

> 返回值

是GET请求返回TRUE, 不是返回FALSE

> **Yaf_Request_Abstract::isGet的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
          if ($this->getRequest()->isGet()) {
             echo "running in Get mode";
          }
        }
     }
     ?>
```








