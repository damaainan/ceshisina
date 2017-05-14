## The Yaf_View_Interface class

### 简介

Yaf_View_Interface是为了提供可扩展的, 可自定的视图引擎而设立的视图引擎接口, 它定义了用在Yaf上的视图引擎需要实现的方法和功能.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\View_Interface

```php
interface Yaf_View_Interface {
public string render( string $view_path ,
array $tpl_vars = NULL );
public boolean display( string $view_path ,
array $tpl_vars = NULL );
public boolean assign( mixed $name ,
mixed $value = NULL );
public boolean setScriptPath( string $view_directory );
public string getScriptPath( void );
}
```


## The Yaf_View_Simple class

### 简介

Yaf_View_Simple是Yaf自带的视图引擎, 它追求性能, 所以并没有提供类似Smarty那样的多样功能, 和复杂的语法.

对于Yaf_View_Simple的视图模板, 就是普通的PHP脚本, 对于通过Yaf_View_Interface::assgin的模板变量, 可在视图模板中直接通过变量名使用.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\View_Simple

```php
Yaf_View_Simple extends Yaf_View_Interface {
protected array _tpl_vars ;
protected string _script_path ;
public string render ( string $view_path ,
array $tpl_vars = NULL );
public boolean display ( string $view_path ,
array $tpl_vars = NULL );
public boolean setScriptPath ( string $view_directory );
public string getScriptPath ( void );
public boolean assign ( string $name ,
mixed $value );
public boolean __set ( string $name ,
mixed $value = NULL );
public mixed __get ( string $name );
}
```


**属性说明**  

- _tpl_vars    
所有通过Yaf_View_Simple::assign分配的变量, 都保存在此属性中

- _script_path  
当前视图引擎的模板文件基目录



## 名称

##### Yaf_View_Simple::assign

(Since Yaf 1.0.0.0)

    public boolean Yaf_View_Simple::assign( mixed  $name ,
                                            mixed  $value = NULL );
为视图引擎分配一个模板变量, 在视图模板中可以直接通过${$name}获取模板变量值

> 参数

$name  
字符串或者关联数组, 如果为字符串, 则$value不能为空, 此字符串代表要分配的变量名. 如果为数组, 则$value须为空, 此参数为变量名和值的关联数组.

$value   
分配的模板变量值

> 注意
如果$name不是合法的PHP变量名, 比如整数,或者是包含"|"的字符串, 那么在视图模板文件中, 将不能直接通过${$name}来访问这个变量. 当然, 你还是可以在视图模板文件中通过$this->_tpl_vars[$name]来访问这个变量.

> 返回值

成功返回Yaf_View_Simple, 失败返回FALSE

> **Yaf_View_Simple::assign 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           $params = array(
               'name' => 'value',
           );
 
           $this->getView()->assign($params)->assign("foo", "bar");
        }
     }
```


## 名称

##### Yaf_View_Simple::render

(Since Yaf 1.0.0.0)

    public string Yaf_View_Simple::render( string  $view_path ,
                                            array  $tpl_vars = NULL );
渲染一个视图模板, 得到结果

> 参数

$view_path  
视图模板的文件, 绝对路径, 一般这个路径由Yaf_Controller_Abstract提供

$tpl_vars  
关联数组, 模板变量

> 返回值

成功返回视图模板执行结果. 失败返回NULL

> **Yaf_View_Simple::render 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
          echo $this->getView()->render($this->_script_path . "/test.phtml");
        }
     }
     ?>
```


## 名称

##### Yaf_View_Simple::display

(Since Yaf 1.0.0.0)

    public string Yaf_View_Simple::display( string  $view_path ,
                                            array  $tpl_vars = NULL );
渲染一个视图模板, 并直接输出给请求端

> 参数

$view_path  
视图模板的文件, 绝对路径, 一般这个路径由Yaf_Controller_Abstract提供

$tpl_vars  
关联数组, 模板变量

> 返回值

成功返回TRUE, 失败返回FALSE

> **Yaf_View_Simple::display 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
          $this->getView()->display($this->_script_path . "/test.phtml");
        }
     }
     ?>
```



## 名称

##### Yaf_View_Simple::setScriptPath

(Since Yaf 1.0.0.13)

    public boolean Yaf_View_Simple::setScriptPath( string  $view_directory );
设置模板的基目录, 默认的Yaf_Dispatcher会设置此目录为APPLICATION_PATH . "/views".

> 参数

$view_diretory  
视图模板的基目录, 绝对地址

> 返回值

成功返回TRUE, 失败返回FALSE

> **Yaf_View_Simple::setScriptPath 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
         $this->getView()->setScriptPath("/tmp/views/");
        }
     }
     ?>
```


## 名称

##### Yaf_View_Simple::getScriptPath

(Since Yaf 1.0.0.13)

    public string Yaf_View_Simple::getScriptPath( void  );
获取当前的模板目录

> 参数

void  
此方法不需要参数

> 返回值

成功返回目前的视图目录, 失败返回NULL

> **Yaf_View_Simple::getScriptPath 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton indexAction() {
         echo  $this->getView()->getScriptPath();
        }
     }
     ?>
```


## 名称

##### Yaf_View_Simple::__set

(Since Yaf 1.0.0.0)

    public boolean Yaf_View_Simple::__set( mixed  $name ,
                                            mixed  $value = NULL );
为视图引擎分配一个模板变量, 在视图模板中可以直接通过${$name}获取模板变量值

> 参数

$name  
字符串或者关联数组, 如果为字符串, 则$value不能为空, 此字符串代表要分配的变量名. 如果为数组, 则$value须为空, 此参数为变量名和值的关联数组.

$value  
分配的模板变量值

> 返回值

成功返回Yaf_View_Simple, 失败返回FALSE

> **Yaf_View_Simple::__set 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public funciton init() {
           $this->getView()->name = "value";
        }
     }
     ?>
```


## 名称

##### Yaf_View_Simple::__get

(Since Yaf 1.0.0.0)

    public string Yaf_View_Simple::__get( string  $name );
获取视图引擎的一个模板变量值

> 参数

$name  
模板变量名

> 返回值

成功返回变量值,失败返回NULL

> **Yaf_View_Simple::__get 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public function init() {
           $this->initView();
        }
        public funciton indexAction() {
           //通过__get直接获取变量值
           echo $this->_view->name;
        }
     }
     ?>
```


## 名称

##### Yaf_View_Simple::get

(Since Yaf 1.0.0.0)

    public string Yaf_View_Simple::get( string  $name );
获取视图引擎的一个模板变量值

> 参数

$name   
模板变量名

> 返回值

成功返回变量值,失败返回NULL

> **Yaf_View_Simple::get 的例子**

```php
     <?php
     class IndexController extends Yaf_Controller_Abstract {
        public function init() {
           $this->initView();
        }
        public funciton indexAction() {
           echo $this->_view->get("name");
        }
     }
     ?>
```


