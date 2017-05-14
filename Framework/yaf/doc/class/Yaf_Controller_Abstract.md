## The Yaf_Controller_Abstract class

#### 简介 

Yaf_Controller_Abstract是Yaf的MVC体系的核心部分. MVC是指Model-View-Controller, 是一个用于分离应用逻辑和表现逻辑的设计模式.

Yaf_Controller_Abstract体系具有可扩展性, 可以通过继承已有的类, 来实现这个抽象类, 从而添加应用自己的应用逻辑.

对于Controller来说, 真正的执行体是在Controller中定义的一个一个的动作, 当然这些动作也可以定义在Controller外:参看 Yaf_Controller_Abstract::$action

与一般的框架不同, 在Yaf中, 可以定义动作的参数, 这些参数的值来自对Request的路由结果中的同名参数值. 比如对于如下的控制器:

> **Yaf_Controller_Abstract参数动作 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public function indexAction($name, $value) {
        }
     }
     ?>
```
在使用默认路由的情况下, 对于请求http://domain.com/index/index/name/a/value/2我们知道会在Request对象中生成俩个参数name和value, 而注意到动作indexAction的参数, 与此同名, 于是在indexAction中, 可以有如下两种方式来获取这俩个参数:

> **Yaf_Controller_Abstract参数动作 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public function indexAction($name, $value) {
            //直接获取参数;
            echo $name, $value; //a2
            //通过Request对象获取
            echo $this->getRequest()->getParam("name"); //a
        }
     }
     ?>
```


> 注意 需要注意的是, 这些参数是来自用户请求URL, 所以使用前一定要做安全化过滤. 另外, 为了防止PHP抛出参数缺失的警告, 请尽量定义有默认值的参数. 

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Controller_Abstract.

```php
abstract Yaf_Controller_Abstract {
protected array actions ;
protected Yaf_Request_Abstract _request ;
protected Yaf_Response_Abstract _response ;
protected Yaf_View_Interface _view ;
protected string _script_path ;
private void __construct ( void );
public void init ( void );
public string getModuleName ( void );
public Yaf_Request_Abstract getRequest ( void );
public Yaf_Response_Abstract getResponse ( void );
public Yaf_View_Interface getView ( void );
public Yaf_View_Interface initView ( void );
public boolean setViewPath ( string $view_directory );
public string getViewPath ( void );
public Yaf_Response_Abstract render ( string $action_name ,
array $tpl_vars = NULL );
public boolean display ( string $action_name ,
array $tpl_vars = NULL );
public boolean forward ( string $action ,
array $invoke_args = NULL );
public boolean forward ( string $controller ,
string $action ,
array $invoke_args = NULL );
public boolean forward ( string $module ,
string $controller ,
string $action ,
array $invoke_args = NULL );
public boolean redirect ( string $url );
}
```
    

属性说明 

- actions  
有些时候为了拆分比较大的Controller, 使得代码更加清晰和易于管理, Yaf支持将具体的动作分开定义. 每个动作都需要实现 Yaf_Action_Abstract 就可以通过定义Yaf_Controller_Abstract::$actions来指明那些动作对应于具体的那些分离的类. 比如:
            
```php
       <?php
        class IndexController extends Yaf_Controller_Abstract {
              public $actions = array (
                 "index" => "actions/Index.php",
              );
        }
```

这样, 当路由到动作Index的时候, 就会加载APPLICATION_PATH . "/actions/Index.php", 并且在这个脚本文件中寻找IndexAction(可通过`yaf.name_suffix`和`yaf.name_separator`来改变具体命名形式), 继而调用这个类的execute方法.


> 注意 在yaf.st_compatible打开的情况下, 会产生额外的查找逻辑. 

- _request   
当前的请求实例, 属性的值由Yaf_Dispatcher保证, 一般通过Yaf_Controller_Abstract::getRequest来获取此属性.

- _response   
当前的响应对象, 属性的值由Yaf_Dispatcher保证, 一般通过Yaf_Controller_Abstract::getResponse来获取此属性.

- _view   
视图引擎, Yaf才会延时实例化视图引擎来提高性能, 所以这个属性直到显示的调用了Yaf_Controller_Abstract::getView或者Yaf_Controller_Abstract::initView以后才可用

- _script_path   
视图文件的目录, 默认值由Yaf_Dispatcher保证, 可以通过Yaf_Controller_Abstract::setViewPath来改变这个值.



## 名称

Yaf_Controller_Abstract::getModuleName

(Since Yaf 1.0.0.5)

    public string Yaf_Controller_Abstract::getModuleName( void  );
获取当前控制器所属的模块名

> 参数

void  
本方法不需要参数

> 返回值

成功返回模块名,失败返回NULL


> **Yaf_Controller_Abstract::getModuleName 的例子**

```php
     
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           echo $this->getModuleName();
        }
     }
     ?>
```


## 名称

Yaf_Controller_Abstract::getRequest

(Since Yaf 1.0.0.5)

    public Yaf_Request_Abstract Yaf_Controller_Abstract::getRequest( void  );
获取当前的请求实例

> 参数

void  
该方法不需要参数

> 返回值

Yaf_Request_Abstract实例

>  **Yaf_Controller_Abstract::getRequest 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $request = $this->getRequest();
        }
     }
     ?>
```



## 名称

Yaf_Controller_Abstract::getResponse

(Since Yaf 1.0.0.5)

    public Yaf_Response_Abstract Yaf_Controller_Abstract::getResponse( void  );
获取当前的响应实例

> 参数

void  
该方法不需要参数

> 返回值

Yaf_Response_Abstract实例

> **Yaf_Controller_Abstract::getResponse 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $response = $this->getResponse();
        }
     }
     ?>

```


## 名称

Yaf_Controller_Abstract::getView

(Since Yaf 1.0.0.5)

    public Yaf_View_Interface Yaf_Controller_Abstract::getView( void  );
获取当前的视图引擎

> 参数

void  
该方法不需要参数

> 返回值

Yaf_View_Interface实例

> **Yaf_Controller_Abstract::getView 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $view = $this->getView();
        }
     }
     ?>
```



## 名称

Yaf_Controller_Abstract::initView

(Since Yaf 1.0.0.5)

    public Yaf_View_Interface Yaf_Controller_Abstract::initView( void  );
初始化视图引擎, 因为Yaf采用延迟实例化视图引擎的策略, 所以只有在使用前调用此方法, 视图引擎才会被实例化

> 参数

void  
该方法不需要参数

> 返回值

Yaf_View_Interface实例

> **Yaf_Controller_Abstract::initView 的例子**

```php     
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
            $view = $this->initView();
            /* 此后就可以直接通过获取Yaf_Controller_Abstract::$_view
            来访问当前视图引擎 */

            $this->_view->assign("webroot", "http://domain.com/");
        }
     }
     ?>
```


## 名称

Yaf_Controller_Abstract::setViewPath

(Since Yaf 1.0.0.5)

    public boolean Yaf_Controller_Abstract::setViewPath( string  $view_directory );
更改视图模板目录, 之后Yaf_Controller_Abstract::render就会在整个目录下寻找模板文件

> 参数

$view_directory  
视图模板目录, 绝对目录.

> 返回值

成功返回Yaf_Controller_Abstract, 失败返回FALSE

> **Yaf_Controller_Abstract::setViewPath 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           $this->setViewPath("/usr/local/www/tpl/");
        }
     }
     ?>
```


## 名称

Yaf_Controller_Abstract::getViewPath

(Since Yaf 1.0.0.5)

    public string Yaf_Controller_Abstract::getViewPath( void  );
获取当前的模板目录

> 参数

void  
本方法不需要参数

> 返回值

成功返回模板目录,失败返回NULL

> **Yaf_Controller_Abstract::getViewPath 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           echo $this->getViewPath();
        }
     }
     ?>
```



## 名称

Yaf_Controller_Abstract::render

(Since Yaf 1.0.0.5)

    public Yaf_Response_Abstract Yaf_Controller_Abstract::render( string  $action ,
                                                                array  $tpl_vars = NULL );
渲染视图模板, 得到渲染结果

> 注意

此方法是对Yaf_View_Interface::render的包装

>参数

$action  
要渲染的动作名

$tpl_vars  
传递给视图引擎的渲染参数, 当然也可以使用Yaf_View_Interface::assign来替代

> 返回值

Yaf_Response_Abstract实例

> **Yaf_Controller_Abstract::render 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           /* 首先关闭自动渲染 */
           Yaf_Dispatcher::getInstance()->disableView();
        }

        public function indexAction() {
            $this->initView();

            /* 自己输出响应 */
            echo $this->render("test.phtml");
        }
     }
     ?>
```


## 名称

Yaf_Controller_Abstract::display

(Since Yaf 1.0.0.5)

    public boolean Yaf_Controller_Abstract::display( string  $action ,
                                                    array  $tpl_vars = NULL );
渲染视图模板, 并直接输出渲染结果

> 注意   
> 此方法是对Yaf_View_Interface::display的包装

> 参数

$action  
要渲染的动作名

$tpl_vars  
传递给视图引擎的渲染参数, 当然也可以使用Yaf_View_Interface::assign来替代

> 返回值

成功返回TRUE,失败返回FALSE

> **Yaf_Controller_Abstract::display 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           /* 首先关闭自动渲染 */
           Yaf_Dispatcher::getInstance()->disableView();
        }

        public function indexAction() {
            $this->initView();

            /* 自己输出响应 */
            $this->display("test.phtml", array("name" => "value"));
        }
     }
     ?>
```



## 名称

Yaf_Controller_Abstract::forward

(Since Yaf 1.0.0.5)

    public boolean Yaf_Controller_Abstract::forward( string  $action ,
                                                 array  $params = NULL );

    public boolean Yaf_Controller_Abstract::forward( string  $controller ,
                                                 string  $action ,
                                                 array  $params = NULL );

    public boolean Yaf_Controller_Abstract::forward( string  $module ,
                                                 string  $controller ,
                                                 string  $action ,
                                                 array  $params = NULL );
将当前请求转给另外一个动作处理

> 注意
Yaf_Controller_Abstract::forward只是登记下要forward的目的地, 并不会立即跳转. 而是会等到当前的Action执行完成以后, 才会进行新的一轮dispatch.

> 参数

$module  
要转给动作的模块, 注意要首字母大写, 如果为空, 则转给当前模块

$controller  
要转给动作的控制器, 注意要首字母大写, 如果为空, 则转给当前控制器

$action   
要转给的动作, 注意要全部小写

$params  
关联数组, 附加的参数, 可通过Yaf_Request_Abstract::getParam获取

> 返回值

成功返回Yaf_Controller_Abstract, 失败返回FALSE

> **Yaf_Controller_Abstract::forward 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           /**
            * 如果用户没登陆, 则转给登陆动作
            */
           if($user_not_login) {
              $this->forward("login");
           }
        }
     }
     ?>
```


## 名称

Yaf_Controller_Abstract::redirect

(Since Yaf 1.0.0.5)

    public boolean Yaf_Controller_Abstract::redirect( string  $url );
重定向请求到新的路径

> 参数

$url  
要定向的路径

> 返回值

成功返回Yaf_Controller_Abstract, 失败返回FALSE

> **Yaf_Controller_Abstract::redirect 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           if($user_not_login)
              $this->redirect("/login/");
        }
     }
     ?>
```








