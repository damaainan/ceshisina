## 内建的类

##  The Yaf_Application class

### 简介

Yaf_Application代表一个产品/项目, 是Yaf运行的主导者, 真正执行的主题. 它负责接收请求, 协调路由, 分发, 执行, 输出.

在PHP5.3之后, 打开`yaf.use_namespace`的情况下, 也可以使用 **Yaf\Application** 

```php
final Yaf_Application {
protected Yaf_Config _config ;
protected Yaf_Dispatcher _dispatcher ;
protected static Yaf_Application _app ;
protected boolean _run = FALSE ;
protected string _environ ;
protected string _modules ;
public void __construct ( mixed $config ,
string $section = ap.environ );
public Yaf_Application bootstrap ( void );
public Yaf_Response_Abstract run ( void );
public Yaf_Dispatcher getDispatcher ( void );
public Yaf_Config_Abstract getConfig ( void );
public string environ ( void );
public string geModules ( void );
public static Yaf_Application app ( void );
public mixed execute ( callback $funcion ,
mixed $parameter = NULL ,
mixed $... = NULL );
}
```

### 属性说明

- _app  
Yaf_Application通过特殊的方式实现了单利模式, 此属性保存当前实例

- _config   
全局配置实例

- _dispatcher   
Yaf_Dispatcher实例

- _modules  
存在的模块名, 从配置文件中app.modules读取

- _environ  
当前的环境名, 也就是Yaf_Application在读取配置的时候, 获取的配置节名字

- _run   
布尔值, 指明当前的Yaf_Application是否已经运行

---
## 名称

**Yaf_Application::__construct**

(Since Yaf 1.0.0.0)

    public void Yaf_Application::__construct ( mixed $config , string  $section  = ap.environ );


初始化一个Yaf_Application, 如果$config是一个INI文件, 那么$section指明要读取的配置节.

> 参数 

- _config_

关联 数组 的配置, 或者一个指向ini格式的配置文件的路径的字符串, 或者是一个Yaf_Config_Abstract实例

> 返回值 

void

例子 

**例 11.1. Yaf_Application::__construct的例子**

```php     
<?php
$config = array(
 "ap" => array(
   "directory" => "/usr/local/www/ap",
  ),
);
$app = new Yaf_Application($config);
?>
```
    
输出
```php
object(Yaf_Application)(6) {
...
}
     
```



## 名称

Yaf_Application::bootstrap

(Since Yaf 1.0.0.0)

    Yaf_Application Yaf_Application::bootstrap ( void );

 指示Yaf_Application去寻找Bootstrap(默认在ap.directory/Bootstrap.php), 并执行所有在Bootstrap类中定义的, 以_init开头的方法. 一般用作在处理请求之前, 做一些个性化定制.

Bootstrap并不会调用run, 所以还需要在bootstrap以后调用Application::run来运行Yaf_Application实例

> 参数 

- void  
本方法不需要参数

> 返回值 

Yaf_Application

例子 

**例 11.2. Yaf_Application::bootstrap 的例子**

```php 
<?php
$config = array(
        "ap" => array(
                "directory" => "/usr/local/www/ap",
        ),
);
$app = new Yaf_Application($config);
$app->bootstrap()->run();
?>
```


## 名称

Yaf_Application::app

(Since Yaf 1.0.0.0)

    static Yaf_Application Yaf_Application::app ( void );

获取当前的Yaf_Application实例

> 参数 

- void  
本方法不需要参数

> 返回值 

Yaf_Application

例子 

**例 11.3. Yaf_Application::app 的例子**

```php
<?php
$config = array(
        "ap" => array(
                "directory" => "/usr/local/www/ap",
        ),
);
$app = new Yaf_Application($config);
assert($app === Yaf_Application::app());
?>
```


## 名称

Yaf_Application::environ

(Since Yaf 1.0.0.5)

    string Yaf_Application::environ ( void );

获取当前Yaf_Application的环境名

> 参数 

- void
本方法不需要参数

> 返回值 

当前的环境名, 也就是ini_get("yaf.environ")

例子 

**例 11.4. Yaf_Application::environ 的例子**

```php
<?php
$config = array(
        "application" => array(
                "directory" => "/usr/local/www/yaf",
        ),
);
$app = new Yaf_Application($config);
print($app->environ());
?>

product

```


## 名称

Yaf_Application::run

(Since Yaf 1.0.0.0)

    boolean Yaf_Application::run ( void );
运行一个Yaf_Application, 开始接受并处理请求. 这个方法只能调用一次, 多次调用并不会有特殊效果.

> 参数 

- void  
本方法不需要参数

> 返回值  
boolean

例子 

**例 11.5. Yaf_Application::run 的例子**

```php
<?php
$config = array(
        "application" => array(
                "directory" => "/usr/local/www/yaf",
        ),
);
$app = new Yaf_Application($config);
$app->run();
?>

```



## 名称

Yaf_Application::execute

(Since Yaf 1.0.0.17)

    mixed Yaf_Application::execute ( callback  $function ,  
                                    mixed  $parameter  = NULL ,  
                                    $parameter  $...  = NULL );

 在Yaf_Application的环境下, 运行一个用户自定义函数过程. 主要用在使用Yaf做简单的命令行脚本的时候, 应用Yaf的外围环境, 比如:自动加载, 配置, 视图引擎等.



> 注意 如果需要使用Yaf的路由分发, 也就是说, 如果是需要在CLI下全功能运行Yaf, 请参看[在命令行下使用Yaf][1]

> 参数

_$function_  
要运行的函数或者方法, 方法可以通过array($obj, "method_name")来定义.

_$parameter_   
零个或者多个要传递给函数的参数.

> 返回值 

被调用函数或者方法的返回值

例子 

**例 11.6. Yaf_Application::execute 的例子**

```php
<?php
$config = array(
        "ap" => array(
                "directory" => "/usr/local/www/ap",
        ),
);
$app = new Yaf_Application($config);
$app->execute("main");

function main() {
}
?>
```


## 名称

Yaf_Application::getDispatcher

(Since Yaf 1.0.0.6)

    Yaf_Config_Abstract Yaf_Application::getDispatcher(void );
获取当前的分发器

> 参数

_void_  

本方法不需要参数

> 返回值
Yaf_Dispatcher实例

例子

**例 11.7. Yaf_Application::getDispatcher 的例子**

```php 
<?php
define ("APPLICATION_PATH", dirname(__FILE__));

$app = new Yaf_Application("conf/application_simple.ini");

//bootstrap
$app->getDispatcher()->setAppDirectory(APPLICATION_PATH . "/action/")->getApplication()->bootstrap()->run();

//当然也可以使用
$dispatcher = Yaf_Dispatcher::getInstance()->setAppDirectory(APPLICATION_PATH ."/action/")->getApplication()->bootstrap()->run();
?>

```



## 名称

Yaf_Application::getConfig

(Since Yaf 1.0.0.0)

    Yaf_Config_Abstract Yaf_Application::getConfig(void );
获取Yaf_Application读取的配置项.

> 参数  

_void_  
本方法不需要参数

> 返回值   
Yaf_Config_Abstract

例子
**例 11.8. Yaf_Application::getConfig 的例子**

```php
     <?php
     $config = array(
        "ap" => array(
        "directory" => "/usr/local/www/ap",
       ),
     );
     $app = new Yaf_Application($config);
     print_r($app->getConfig('application'));
     ?>
```
    
输出
```php  
     Yaf_Config Object
     (
       [_config:private] => Array
       ( 
         [ap] => Array
         (
           [directory] => /usr/local/www/ap
         )

       )

     )

```


## 名称

Yaf_Application::getModules

(Since Yaf 1.0.0.5)

    string Yaf_Application::getModules(void );
获取在配置文件中申明的模块.

> 参数  

_void_   
本方法不需要参数

> 返回值  

string

例子
**例 11.9. Yaf_Application::getModules 的例子**

```php
     <?php
     $config = array(
        "ap" => array(
        "directory" => "/usr/local/www/ap",
        "modules"   => "Index",
       ),
     );
     $app = new Yaf_Application($config);
     print_r($app->getModules());
     ?>
```
    
输出
     
       Array
       ( 
        [0] => Index
       )