# PHP-Yaf执行流程-源码分析

作者  [简单方式][0] 关注 2017.02.08 21:09  字数 1439 

<font face=微软雅黑>

### 介绍

Yaf框架是一个c语言编写的PHP框架，是一个以PHP扩展形式提供的PHP开发框架，相比于一般的PHP框架， 它更快，更轻便，内存占用率更低，就是本着对性能的追求，Yaf把框架中不易变的部分抽象出来，类如路由、自动加载、bootstrap、分发等，采用PHP扩展去实现，以此来保证性能。

###### Yaf优点

* 用c语言开发的PHP框架，相比原生的PHP，几乎不会带来额外的性能开销
* 所有的框架类，不需要编译，在PHP启动的时候加载，并常驻内存.
* 更快的执行速度，更少的内存占用.
* 灵巧的自动加载. 支持全局和局部两种加载规则, 方便类库共享.

###### yaf缺点

* 维护成本高，要维护PHP扩展，需要熟练`C开发`和`Zend Api`.
* 目标用户群小，现在国内很多中小型站都是使用虚拟主机，并不能随意的给PHP添加扩展.
* 不像其他框架一样提供各种丰富功能的类库和各种优雅的写法，它只提供一个MVC的基本骨架.

#### Yaf框架执行流程图

![][1]



引用官方流程图

### 流程图说明

在application目录下有个`Bootstrap.php`文件，这个就是图中的第一个环节，如果存在`Bootstrap()`就会先执行该文件，该文件包含了一系列的初始化环节，并返回一个`Yaf_Application`对象，紧接着调用了它的`run方法`，run里面包含了图中所有环节，run **首先** 是`调用路由`，路由的主要目的其实就是找到 **controllers**文件，然后执行里面的`init`和`action`方法，或者找到所有`actions`的地址然后加载，在去执行对应的execute方法，如果设置了`autoRender`在返回的时候会执行`render`方法，就是`view自动渲染`，图中有六个双横线标出的环节，就是六个插件方法，用户可以自定义实现这几个方法，然后Yaf框架会在图中相应的步骤处调用对应的HOOK方法。

#### Yaf框架目录结构

    + public
      |- index.php //入口文件
      |- .htaccess //重写规则    
      |+ css
      |+ img
      |+ js
    + conf
      |- application.ini //配置文件   
    + application 
      |+ actions //可将controller里面的方法单独抽出来做为一个类实现
         |+ index
            |- Main.php
      |+ controllers
         |- Index.php //默认控制器
      |+ views    
         |+ index   //控制器
            |- index.phtml //默认视图
      |+ modules //可以按模块来区分不同业务下的控制器层
         |+ admin 
            |+ controllers 
               |- Index.php 
      |+ library //本地类库
      |+ models  //model目录
          |- baseModel.php
      |+ plugins //插件目录
          |- testPlugin.php
      Bootstrap.php  引导文件

### Yaf配置项说明

* php.ini 配置项

选项名称 | 默认值 | 说明 
-|-|-
yaf.environ | product | 环境名称, 当用INI作为Yaf的配置文件时, 这个指明了Yaf将要在INI配置中读取的节的名字 
yaf.library | NULL | 全局类库的目录路径 
yaf.cache_config | 0 | 是否缓存配置文件(只针对INI配置文件生效), 打开此选项可在复杂配置的情况下提高性能 
yaf.name_suffix | 1 | 在处理Controller, Action, Plugin, Model的时候, 类名中关键信息是否是后缀式, 比如UserModel, 而在前缀模式下则是ModelUser 
yaf.name_separator | "" | 在处理Controller, Action, Plugin, Model的时候, 前缀和名字之间的分隔符, 默认为空, 也就是UserPlugin, 加入设置为"`_`", 则判断的依据就会变成:"`User_Plugin`", 这个主要是为了兼容ST已有的命名规范 
yaf.forward_limit | 5 | forward最大嵌套深度 
yaf.use_namespace | 0 | 开启的情况下, Yaf将会使用命名空间方式注册自己的类, 比如`Yaf_Application`将会变成`Yaf\Application` 
yaf.use_spl_autoload | 0 | 开启的情况下, Yaf在加载不成功的情况下, 会继续让PHP的自动加载函数加载, 从性能考虑, 除非特殊情况, 否则保持这个选项关闭 

    //php.ini
    [Yaf]
    yaf.library = "c:/huan"
    yaf.name_suffix = 0
    yaf.name_separator = "_"
    yaf.environ = "product"

* application.ini 配置项

选项名称 | 默认值 | 说明 
-|-|-
application.ext | php | PHP脚本的扩展名 
application.bootstrap | Bootstrapplication.php  | Bootstrap路径(绝对路径) 
application.library | application.directory+"/library" | 本地(自身)类库的绝对目录地址 
application.baseUri | NULL | 在路由中,需要忽略的路径前缀,一般不需要设置,Yaf会自动判断. 
application.dispatcher.defaultModule | index | 默认的模块 
application.dispatcher.throwException | True | 在出错的时候,是否抛出异常 
application.dispatcher.catchException | False | 是否使用默认的异常捕获Controller,如果开启,在有未捕获的异常的时候, 控制权会交给ErrorController的errorAction方法, 可以通过$request->getException()获得此异常对象 
application.dispatcher.defaultController | index | 默认的控制器 
application.dispatcher.defaultAction | index | 默认的动作 
application.view.ext | phtml | 视图模板扩展名 
application.modules | modules | 声明存在的模块名, 请注意,如果你要定义这个值,一定要定义Index Module 

    //application.ini
    [mysql]
    mysql.master.user_name = word
    mysql.master.pass_word = 1234
    mysql.slave.user_name = word
    mysql.slave.pass_word = 1234
    
    [database : mysql]
    database.master.host = 127.0.0.1
    database.slave.host  = 127.0.0.2,127.0.0.3
    
    [product : database]
    yaf.directory = APP_PATH "/app/" 
    yaf.libray = APP_PATH "/libray/

可以看到在application.ini配置文件里面除了配置框架本身的配置项，还可以添加一些我们自定义的配置项，同时支持继承配置功能，在框架启动的时候会根据yaf.environ设定的节点名字去读取.

> 配置文件解析完毕后读取到内存的状态

    Yaf_Config_Ini Object
    (
        [_config:protected] => Array
            (
                [mysql] => Array
                    (
                        [master] => Array
                            (
                                [user_name] => word
                            )
    
                        [slave] => Array
                            (
                                [user_name] => word
                            )
    
                    )
    
                [database] => Array
                    (
                        [master] => Array
                            (
                                [host] => 127.0.0.1
                            )
    
                        [slave] => Array
                            (
                                [host] => 127.0.0.2,127.0.0.3
                            )
    
                    )
    
                [yaf] => Array
                    (
                        [directory] => C:\huan\apache\htdocs\yaf/app/
                        [libray] => C:\huan\apache\htdocs\yaf/libray/
                    )
    
            )
    
        [_readonly:protected] => 1
    )

### 调用Yaf框架

```php
    //入口文件index.php
    define("APP_PATH",  '/home/test/yaf'); 
    $app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
    $app->bootstrap()->run();
```

> Bootstrap.php 类文件

```php
    class Bootstrap extends Yaf_Bootstrap_Abstract{
    
            public function _initConfig(Yaf_Dispatcher $dispatcher) {
                    //存放全局数据
                    $config = Yaf_Application::app()->getConfig();
                    Yaf_Registry::set("config", $config);
                    //读取配置
                    $dispatcher->getConfig()->get('database')->master->host
                     //关闭自动渲染
                    $dispatcher->autoRender(false);
                    //设置自定的模板引擎类如smarty
                    $dispatcher->setView( Yaf_View_Interface  $request );
            }
    
            public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
                    $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
            }
    
            public function _initPlugin(Yaf_Dispatcher $dispatcher){
                   //注册插件
                   $objPlugin = new  Test_Plugin();
                   $dispatcher->registerPlugin($objPlugin);
            }
    
            public function _initRegistLocalLib(Yaf_Dispatcher $dispatcher){
                  //注册本地类前缀, 是的对于以这些前缀开头的本地类, 都从本地类库路径中加载.
                  Yaf_Loader::getInstance()->registerLocalNamespace(array('Foo','Msn'));
            }
    }
```

> Plugin.php 插件类文件

```php
      class Test_Plugin extends Yaf_Plugin_Abstract {
    
           public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
           }
    
           public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
           }
      }
```

> Controller.php 类文件

```php
    class Index_Controller extends Yaf_Controller_Abstract {
    
       //第二种action实现方式
       public $actions = array(
            'one' => 'actions/index/One.php',
            'two' => 'actions/index/Two.php',
       );
    
       //初始化方法
       public function ini(){
    
       }
    
       //第一种action实现方式
       public function index_Action() { 
           $this->getView()->assign("content", "Hello World");
           $this->getView()->display();
       }
    }
```

> Action.php 类文件

      Class One_Action extends Yaf_Action_Abstract{
             public function execute(){
    
             }
      }

> Model 类文件

```php
        class Base_Model {
              public function getDataList(){
              }
        }
```

注意： Yaf并没有实现Model层，需要自己实现或者调用现成的Model库.

### 扩展-核心功能模块实现

> 扩展 Yaf_Application 类注册

```c
    YAF_STARTUP_FUNCTION(application) {  //宏替换一下 ZEND_MINIT_FUNCTION(yaf_##module)
        //PHP内核中对PHP类的实现是通过zend_class_entry结构体实现的
        zend_class_entry ce;
        //相当于对ce初始化，指定一个类名称 Ap_Application
        //指定类的成员方法列表 ap_application_methods 结构体数组指针
        YAF_INIT_CLASS_ENTRY(ce, "Yaf_Application", "Yaf\\Application", yaf_application_methods);
        //向PHP注册类，PHP中由class_table维护全局的类数组
        //可以简单理解为把类添加到这个数组中，这样就可以在
        //PHP中找到这个类了，内核中有一组类似的注册函数
        //用来注册接口、类、子类、接口实现、抽象类等
        yaf_application_ce = zend_register_internal_class_ex(&ce, NULL, NULL TSRMLS_CC);
        //指定类的属性
        yaf_application_ce->ce_flags |= ZEND_ACC_FINAL_CLASS;
        //设置类内部的一些变量和属性
        zend_declare_property_null(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_CONFIG),     ZEND_ACC_PROTECTED TSRMLS_CC);
        zend_declare_property_null(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_DISPATCHER),     ZEND_ACC_PROTECTED TSRMLS_CC);
        zend_declare_property_null(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_APP),          ZEND_ACC_STATIC | ZEND_ACC_PROTECTED TSRMLS_CC);
        zend_declare_property_null(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_MODULES),     ZEND_ACC_PROTECTED TSRMLS_CC);
    
        zend_declare_property_bool(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_RUN),     0,         ZEND_ACC_PROTECTED TSRMLS_CC);
        zend_declare_property_string(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_ENV),     YAF_G(environ), ZEND_ACC_PROTECTED TSRMLS_CC);
    
        zend_declare_property_long(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_ERRNO),         0,    ZEND_ACC_PROTECTED TSRMLS_CC);
        zend_declare_property_string(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_ERRMSG),     "",     ZEND_ACC_PROTECTED TSRMLS_CC);
        //内核中有一组这样的属性标记来指定类和变量的性质
        /* method flags (types) 
           #define ZEND_ACC_STATIC            0x01
           #define ZEND_ACC_ABSTRACT        0x02
           #define ZEND_ACC_FINAL            0x04
           #define ZEND_ACC_IMPLEMENTED_ABSTRACT        0x08
           class flags (types) 
           #define ZEND_ACC_IMPLICIT_ABSTRACT_CLASS    0x10
           #define ZEND_ACC_EXPLICIT_ABSTRACT_CLASS    0x20
           #define ZEND_ACC_FINAL_CLASS                0x40 
           #define ZEND_ACC_INTERFACE                    0x80 */
        return SUCCESS;
    }
    
    //类成员函数的声明
    zend_function_entry yaf_application_methods[] = {
        PHP_ME(yaf_application, __construct,         yaf_application_construct_arginfo,     ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
        PHP_ME(yaf_application, run,               yaf_application_run_arginfo,         ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, execute,          yaf_application_execute_arginfo,     ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, app,               yaf_application_app_arginfo,         ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
        YAF_ME(yaf_application_environ, "environ",     yaf_application_environ_arginfo,     ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, bootstrap,           yaf_application_bootstrap_arginfo,      ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, getConfig,           yaf_application_getconfig_arginfo,     ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, getModules,           yaf_application_getmodule_arginfo,      ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, getDispatcher,         yaf_application_getdispatch_arginfo,    ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, setAppDirectory,    yaf_application_setappdir_arginfo,      ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, getAppDirectory,    yaf_application_void_arginfo,         ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, getLastErrorNo,     yaf_application_void_arginfo,         ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, getLastErrorMsg,    yaf_application_void_arginfo,         ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, clearLastError,     yaf_application_void_arginfo,         ZEND_ACC_PUBLIC)
        PHP_ME(yaf_application, __destruct,        NULL,                     ZEND_ACC_PUBLIC | ZEND_ACC_DTOR)
        PHP_ME(yaf_application, __clone,        NULL,                     ZEND_ACC_PRIVATE | ZEND_ACC_CLONE)
        PHP_ME(yaf_application, __sleep,        NULL,                     ZEND_ACC_PRIVATE)
        PHP_ME(yaf_application, __wakeup,        NULL,                     ZEND_ACC_PRIVATE)
        {NULL, NULL, NULL}
    };
```
上面这个就是在扩展里面注册一个类的实现，Yaf其他的类文件在注册类的时候也几乎和上面方式一致，具体看下源码就可以了，下面将从实例化一个Yaf_Application类开始分析。

> 扩展 Yaf_Application 类构造函数 __construct 实现

```c
    PHP_METHOD(yaf_application, __construct) {
        yaf_config_t          *zconfig;
        yaf_request_t          *request;
        yaf_dispatcher_t    *zdispatcher;
        yaf_application_t    *app, *self;
        yaf_loader_t        *loader;
        zval             *config;
        zval             *section = NULL;
        //获取yaf_application::_app变量值,默认为0
        app     = zend_read_static_property(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_APP), 1 TSRMLS_CC);
    
    #if PHP_YAF_DEBUG
        php_error_docref(NULL TSRMLS_CC, E_STRICT, "Yaf is running in debug mode");
    #endif
        //如果app有值则报错，也就是当前类只能被实例化一次
        if (!ZVAL_IS_NULL(app)) {
            yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "Only one application can be initialized");
            RETURN_FALSE;
        }
        //获取当前的对象，也就是php里面的$this
        self = getThis();
        //获取参数，把配置文件路径或者配置数组传递进来
        //section配置文件中的节点名字，默认product
        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "z|z", &config, §ion) == FAILURE) {
            YAF_UNINITIALIZED_OBJECT(getThis());
            return;
        }
    
        if (!section || Z_TYPE_P(section) != IS_STRING || !Z_STRLEN_P(section)) {
            MAKE_STD_ZVAL(section);
            ZVAL_STRING(section, YAF_G(environ), 0);
            //获取config对象
            zconfig = yaf_config_instance(NULL, config, section TSRMLS_CC);
            efree(section);
        } else {
            //获取config对象
            zconfig = yaf_config_instance(NULL, config, section TSRMLS_CC);
        }
        //解析配置文件，并将配置信息保存在zconfig对象中
        if  (zconfig == NULL
                || Z_TYPE_P(zconfig) != IS_OBJECT
                || !instanceof_function(Z_OBJCE_P(zconfig), yaf_config_ce TSRMLS_CC)
                || yaf_application_parse_option(zend_read_property(yaf_config_ce,
                           zconfig, ZEND_STRL(YAF_CONFIG_PROPERT_NAME), 1 TSRMLS_CC) TSRMLS_CC) == FAILURE) {
            YAF_UNINITIALIZED_OBJECT(getThis());
            yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "Initialization of application config failed");
            RETURN_FALSE;
        }
        //获取 request 对象，请求的信息保存在里面
        request = yaf_request_instance(NULL, YAF_G(base_uri) TSRMLS_CC);
        if (YAF_G(base_uri)) {
            efree(YAF_G(base_uri));
            YAF_G(base_uri) = NULL;
        }
    
        if (!request) {
            YAF_UNINITIALIZED_OBJECT(getThis());
            yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "Initialization of request failed");
            RETURN_FALSE;
        }
        //获取dispatcher对象，主要在里面调用插件、路由、分发等
        zdispatcher = yaf_dispatcher_instance(NULL TSRMLS_CC);
        if (NULL == zdispatcher
                || Z_TYPE_P(zdispatcher) != IS_OBJECT
                || !instanceof_function(Z_OBJCE_P(zdispatcher), yaf_dispatcher_ce TSRMLS_CC)) {
            YAF_UNINITIALIZED_OBJECT(getThis());
            yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "Instantiation of application dispatcher failed");
            RETURN_FALSE;
        }
        //把request对象保存在zdispatcher对像属性中
        yaf_dispatcher_set_request(zdispatcher, request TSRMLS_CC);
        //保存zconfig对象到当前yaf_application对象属性中
        zend_update_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_CONFIG), zconfig TSRMLS_CC);
        //保存zdispatcher对象到当前yaf_application对象属性中
        zend_update_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_DISPATCHER), zdispatcher TSRMLS_CC);
    
        zval_ptr_dtor(&request);
        zval_ptr_dtor(&zdispatcher);
        zval_ptr_dtor(&zconfig);
        //是否指定本地类库地址
        if (YAF_G(local_library)) {
            //获取一个load加载类，并同时向内核注册一个自动加载器__autoload
            //之后在代码中new class()都会调用扩展中注册的自动加载器
            loader = yaf_loader_instance(NULL, YAF_G(local_library),
                    strlen(YAF_G(global_library))? YAF_G(global_library) : NULL TSRMLS_CC);
            efree(YAF_G(local_library));
            YAF_G(local_library) = NULL;
        } else {
            char *local_library;
            //获取一个默认的本地类库地址
            spprintf(&local_library, 0, "%s%c%s", YAF_G(directory), DEFAULT_SLASH, YAF_LIBRARY_DIRECTORY_NAME);
            //同上面解释一致
            loader = yaf_loader_instance(NULL, local_library,
                    strlen(YAF_G(global_library))? YAF_G(global_library) : NULL TSRMLS_CC);
            efree(local_library);
        }
    
        if (!loader) {
            YAF_UNINITIALIZED_OBJECT(getThis());
            yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "Initialization of application auto loader failed");
            RETURN_FALSE;
        }
    
        //赋值对象属性
        zend_update_property_bool(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_RUN), 0 TSRMLS_CC);
        zend_update_property_string(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_ENV), YAF_G(environ) TSRMLS_CC);
    
        //这个modules一定会存在，默认是Index
        if (YAF_G(modules))  {
            zend_update_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_MODULES), YAF_G(modules) TSRMLS_CC);
            Z_DELREF_P(YAF_G(modules));
            YAF_G(modules) = NULL;
        } else {
            zend_update_property_null(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_MODULES) TSRMLS_CC);
        }
        //yaf_application::_app 赋值成当前self对象
        zend_update_static_property(yaf_application_ce, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_APP), self TSRMLS_CC);
    }
```

> PHP_METHOD(Yaf_application, bootstrap) 扩展实现 

```c
    PHP_METHOD(yaf_application, bootstrap) {
        char            *bootstrap_path;
        uint             len, retval = 1;
        zend_class_entry    **ce;
        yaf_application_t    *self = getThis();
        //判断 Bootstrap 类是否存在，默认第一次肯定不存在
        //因为还没有加载注册进来
        if (zend_hash_find(EG(class_table), YAF_DEFAULT_BOOTSTRAP_LOWER, YAF_DEFAULT_BOOTSTRAP_LEN, (void **) &ce) != SUCCESS) {
            //判断是否指定了bootstrap文件路径
            if (YAF_G(bootstrap)) {
                bootstrap_path  = estrdup(YAF_G(bootstrap));
                len = strlen(YAF_G(bootstrap));
            } else {
                //没有指定则使用默认路径
                len = spprintf(&bootstrap_path, 0, "%s%c%s.%s", YAF_G(directory), DEFAULT_SLASH, YAF_DEFAULT_BOOTSTRAP, YAF_G(ext));
            }
            //导入bootstrap文件，内核如果发现是类，则会自动注册上
            if (!yaf_loader_import(bootstrap_path, len + 1, 0 TSRMLS_CC)) {
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "Couldn't find bootstrap file %s", bootstrap_path);
                retval = 0;
            //获取 bootstrap 类
            } else if (zend_hash_find(EG(class_table), YAF_DEFAULT_BOOTSTRAP_LOWER, YAF_DEFAULT_BOOTSTRAP_LEN, (void **) &ce) != SUCCESS)  {
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "Couldn't find class %s in %s", YAF_DEFAULT_BOOTSTRAP, bootstrap_path);
                retval = 0;
            //判断该类是否继承Yaf_Bootstrap_Abstract
            } else if (!instanceof_function(*ce, yaf_bootstrap_ce TSRMLS_CC)) {
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "Expect a %s instance, %s give", yaf_bootstrap_ce->name, (*ce)->name);
                retval = 0;
            }
    
            efree(bootstrap_path);
        }
    
        if (!retval) {
            RETURN_FALSE;
        } else {
            zval             *bootstrap;
            HashTable         *methods;
            yaf_dispatcher_t     *dispatcher;
            //实例化一个bootstrap对象
            MAKE_STD_ZVAL(bootstrap);
            object_init_ex(bootstrap, *ce);
            //获取dispatcher 对象
            dispatcher = zend_read_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_DISPATCHER), 1 TSRMLS_CC);
            //获取bootstrap类里面所有的方法，每个类都用一个函数表function_table来记录
            //方法名和对应结构体指针
            methods    = &((*ce)->function_table);
            //循环bootstrap类里面所有的方法
            //内核中对array的实现是通过hash_table实现的，内核中会
            //看到到处使用hash_table的地方，可以简单理解为数组的操作
            for(zend_hash_internal_pointer_reset(methods);
                    zend_hash_has_more_elements(methods) == SUCCESS;
                    zend_hash_move_forward(methods)) {
                char *func;
                uint len;
                ulong idx;
                //获取第一个方法名字
                zend_hash_get_current_key_ex(methods, &func, &len, &idx, 0, NULL);
                /* cann't use ZEND_STRL in strncasecmp, it cause a compile failed in VS2009 */
                #define YAF_BOOTSTRAP_INITFUNC_PREFIX      "_init"
                //Bootstrap类实现了一系列的_init开头的方法
                //这里是比较函数func是否以_init开头
                if (strncasecmp(func, YAF_BOOTSTRAP_INITFUNC_PREFIX, sizeof(YAF_BOOTSTRAP_INITFUNC_PREFIX)-1)) {
                    continue;
                }
                //调用所有以_init开头的函数，入参统一为dispatcher对象
                zend_call_method(&bootstrap, *ce, NULL, func, len - 1, NULL, 1, dispatcher, NULL TSRMLS_CC);
                /** an uncaught exception threw in function call */
                if (EG(exception)) {
                    zval_ptr_dtor(&bootstrap);
                    RETURN_FALSE;
                }
            }
    
            zval_ptr_dtor(&bootstrap);
        }
        //最后会返回application对象自身
        RETVAL_ZVAL(self, 1, 0);
    }
```

> PHP_METHOD(yaf_application, run) 扩展实现 

```c
    PHP_METHOD(yaf_application, run) {
        zval *running;
        yaf_dispatcher_t  *dispatcher;
        yaf_response_t      *response;
        yaf_application_t *self = getThis();
    
        //获取属性值，默认第一次为0
        running = zend_read_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_RUN), 1 TSRMLS_CC);
        if (IS_BOOL == Z_TYPE_P(running)
                && Z_BVAL_P(running)) {
            yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "An application instance already run");
            RETURN_TRUE;
        }
        //赋值为1，可以看出来当前application对象中的run函数只能被调用一次
        ZVAL_BOOL(running, 1);
        zend_update_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_RUN), running TSRMLS_CC);
        //获取dispatcher对象
        dispatcher = zend_read_property(yaf_application_ce, self, ZEND_STRL(YAF_APPLICATION_PROPERTY_NAME_DISPATCHER), 1 TSRMLS_CC);
        //进行路由分发等工作
        if ((response = yaf_dispatcher_dispatch(dispatcher TSRMLS_CC))) {
            RETURN_ZVAL(response, 1, 1);
        }
    
        RETURN_FALSE;
    }

```

yaf_dispatcher_dispatch() 方法里面主要分两部分，路由+分发，其中夹杂着一些插件调用，就是上图中双横线标出的环节，先看下官方对插件的说明。

触发顺序 名称 触发时机 1 routerStartup 在路由之前触发 2 routerShutdown 路由结束之后触发 3 dispatchLoopStartup 分发循环开始之前被触发 4 preDispatch 分发之前触发 5 postDispatch 分发结束之后触发 6 dispatchLoopShutdown 分发循环结束之后触发 

> 扩展中插件方法名用宏表示

```c
    #define YAF_PLUGIN_HOOK_ROUTESTARTUP                "routerstartup"
    #define YAF_PLUGIN_HOOK_ROUTESHUTDOWN             "routershutdown"
    #define YAF_PLUGIN_HOOK_LOOPSTARTUP                "dispatchloopstartup"
    #define YAF_PLUGIN_HOOK_PREDISPATCH                 "predispatch"
    #define YAF_PLUGIN_HOOK_POSTDISPATCH                "postdispatch"
    #define YAF_PLUGIN_HOOK_LOOPSHUTDOWN                "dispatchloopshutdown"
    #define YAF_PLUGIN_HOOK_PRERESPONSE                "preresponse"
```

> yaf_dispatcher_dispatch(yaf_dispatcher_t *dispatcher TSRMLS_DC)

```c
    yaf_response_t * yaf_dispatcher_dispatch(yaf_dispatcher_t *dispatcher TSRMLS_DC) {
        zval *return_response, *plugins, *view;
        yaf_response_t *response;
        yaf_request_t *request;
        uint nesting = YAF_G(forward_limit);
        //获取response对象
        response = yaf_response_instance(NULL, sapi_module.name TSRMLS_CC);
        //获取request对象
        request     = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_REQUEST), 1 TSRMLS_CC);
        //获取插件数组对象，这是一个数组，里面每个值都是一个插件对象
        //但前提是我们创建插件类，实例化之后，注册进dispatcher对象里
        plugins     = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_PLUGINS), 1 TSRMLS_CC);
        //如果不是对象则报错
        if (IS_OBJECT != Z_TYPE_P(request)) {
            yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "Expect a %s instance", yaf_request_ce->name);
            zval_ptr_dtor(&response);
            return NULL;
        }
    
        /* route request */
        //是否已经路由过
        if (!yaf_request_is_routed(request TSRMLS_CC)) {
            //调用插件routerstartup方法，如果注册的情况
            YAF_PLUGIN_HANDLE(plugins, YAF_PLUGIN_HOOK_ROUTESTARTUP, request, response);
            //捕获异常 Error_Controller，开启的情况下，对应配置项为 catchException
            YAF_EXCEPTION_HANDLE(dispatcher, request, response);
            //进行路由，就是找到 controller、action、model
            if (!yaf_dispatcher_route(dispatcher, request TSRMLS_CC)) {
               //抛出异常
                yaf_trigger_error(YAF_ERR_ROUTE_FAILED TSRMLS_CC, "Routing request failed");
                //捕获异常 Error_Controller
                YAF_EXCEPTION_HANDLE_NORET(dispatcher, request, response);
                zval_ptr_dtor(&response);
                return NULL;
            }
            //设置Controller、action、model
            yaf_dispatcher_fix_default(dispatcher, request TSRMLS_CC);
            //调用插件routerShutdown方法
            YAF_PLUGIN_HANDLE(plugins, YAF_PLUGIN_HOOK_ROUTESHUTDOWN, request, response);
            //捕获异常
            YAF_EXCEPTION_HANDLE(dispatcher, request, response);
            //已经路由完毕标识
            (void)yaf_request_set_routed(request, 1 TSRMLS_CC);
        } else {
            //设置Controller、action、model
            yaf_dispatcher_fix_default(dispatcher, request TSRMLS_CC);
        }
        //调用插件dispatchLoopStartup方法
        YAF_PLUGIN_HANDLE(plugins, YAF_PLUGIN_HOOK_LOOPSTARTUP, request, response);
        //捕获异常
        YAF_EXCEPTION_HANDLE(dispatcher, request, response);
        //获取view对象
        view = yaf_dispatcher_init_view(dispatcher, NULL, NULL TSRMLS_CC);
        if (!view) {
            return NULL;
        }
    
        do {
            //调用插件preDispatch方法
            YAF_PLUGIN_HANDLE(plugins, YAF_PLUGIN_HOOK_PREDISPATCH, request, response);
            //捕获异常
            YAF_EXCEPTION_HANDLE(dispatcher, request, response);
            //开始分发
            if (!yaf_dispatcher_handle(dispatcher, request, response, view TSRMLS_CC)) {
                YAF_EXCEPTION_HANDLE(dispatcher, request, response);
                zval_ptr_dtor(&response);
                return NULL;
            }
            yaf_dispatcher_fix_default(dispatcher, request TSRMLS_CC);
            //调用插件postDispatch方法
            YAF_PLUGIN_HANDLE(plugins, YAF_PLUGIN_HOOK_POSTDISPATCH, request, response);
            //捕获异常
            YAF_EXCEPTION_HANDLE(dispatcher, request, response);
            //这个就是控制分发次数，配置项中的forward_limit
        } while (--nesting > 0 && !yaf_request_is_dispatched(request TSRMLS_CC));
        //调用插件dispatchLoopShutdown方法
        YAF_PLUGIN_HANDLE(plugins, YAF_PLUGIN_HOOK_LOOPSHUTDOWN, request, response);
        //捕获异常
        YAF_EXCEPTION_HANDLE(dispatcher, request, response);
        //如果分发次数全部耗尽，且还有异常的话
        if (0 == nesting && !yaf_request_is_dispatched(request TSRMLS_CC)) {
            yaf_trigger_error(YAF_ERR_DISPATCH_FAILED TSRMLS_CC, "The max dispatch nesting %ld was reached", YAF_G(forward_limit));
            YAF_EXCEPTION_HANDLE_NORET(dispatcher, request, response);
            zval_ptr_dtor(&response);
            return NULL;
        }
        //最后返回一个 response 对象
        return_response = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_RETURN), 1 TSRMLS_CC);
    
        if (!Z_BVAL_P(return_response)) {
            (void)yaf_response_send(response TSRMLS_CC);
            yaf_response_clear_body(response, NULL, 0 TSRMLS_CC);
        }
    
        return response;
    }
```

> yaf_dispatcher_route() 路由入口

```c
    int yaf_dispatcher_route(yaf_dispatcher_t *dispatcher, yaf_request_t *request TSRMLS_DC) {
        zend_class_entry *router_ce;
        //获取路由器对象，dispatcher初始化时会创建内置路由器
        yaf_router_t *router = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_ROUTER), 1 TSRMLS_CC);
        if (IS_OBJECT == Z_TYPE_P(router)) {
            //是否内置的路由器
            if ((router_ce = Z_OBJCE_P(router)) == yaf_router_ce) {
                //执行路由器里面的路由协议
                yaf_router_route(router, request TSRMLS_CC);
            } else {
                //自定义路由器
                /* user custom router */
                zval *ret = zend_call_method_with_1_params(&router, router_ce, NULL, "route", &ret, request);
                if (Z_TYPE_P(ret) == IS_BOOL && Z_BVAL_P(ret) == 0) {
                    yaf_trigger_error(YAF_ERR_ROUTE_FAILED TSRMLS_CC, "Routing request faild");
                    return 0;
                }
            }
            return 1;
        }
        return 0;
    }
```

这段代码是路由环节的入口，dispatcher初始化时会创建内置路由器，这里只涉及路由器概念，上面的自定义并不是自定义路由协议，而是你可以重新写一个路由器，我们通常在项目中自定义路由协议就可以了，没有必要自己实现一个路由器。而且框架中其实也是写死了内置路由器，没有给你set自定义路由器的接口。

> yaf_router_route() 执行路由

```c
    int yaf_router_route(yaf_router_t *router, yaf_request_t *request TSRMLS_DC) {
        zval         *routers, *ret;
        yaf_route_t    **route;
        HashTable     *ht;
        //获取路由协议，可以添加多层路由协议，类似于多重插件
        //通过addRoute方法向路由器注册自己的路由协议，默认的
        //路由协议是Yaf_Route_Static
        routers = zend_read_property(yaf_router_ce, router, ZEND_STRL(YAF_ROUTER_PROPERTY_NAME_ROUTES), 1 TSRMLS_CC);
    
        ht = Z_ARRVAL_P(routers);
        //for循环依次调用路由协议的route方法，成功则记下当前
        //生效的这个路由协议的索引位置，并设置request为已路由
        //不成功则继续调用下一个路由协议。
        for(zend_hash_internal_pointer_end(ht);
                zend_hash_has_more_elements(ht) == SUCCESS;
                zend_hash_move_backwards(ht)) {
    
            if (zend_hash_get_current_data(ht, (void**)&route) == FAILURE) {
                continue;
            }
            //调用路由协议对象的route方法
            zend_call_method_with_1_params(route, Z_OBJCE_PP(route), NULL, "route", &ret, request);
    
            if (IS_BOOL != Z_TYPE_P(ret) || !Z_BVAL_P(ret)) {
                zval_ptr_dtor(&ret);
                continue;
            } else {
                char *key;
                uint len = 0;
                ulong idx = 0;
                //记录当前路由协议的索引
                switch(zend_hash_get_current_key_ex(ht, &key, &len, &idx, 0, NULL)) {
                    case HASH_KEY_IS_LONG:
                        zend_update_property_long(yaf_router_ce, router, ZEND_STRL(YAF_ROUTER_PROPERTY_NAME_CURRENT_ROUTE), idx TSRMLS_CC);
                        break;
                    case HASH_KEY_IS_STRING:
                        if (len) {
                            zend_update_property_string(yaf_router_ce, router, ZEND_STRL(YAF_ROUTER_PROPERTY_NAME_CURRENT_ROUTE), key TSRMLS_CC);
                        }
                        break;
                }
                //设置为已路由
                yaf_request_set_routed(request, 1 TSRMLS_CC);
                zval_ptr_dtor(&ret);
                break;
            }
        }
        return 1;
    }
```

> 内置的路由协议

* [Yaf_Route_Static][2]
* [Yaf_Route_Simple][3]
* [Yaf_Route_Supervar][4]
* [Yaf_Route_Rewrite][5]
* [Yaf_Route_Regex][6]
* [Yaf_Route_Map][7]


> 上面几种路由协议源代码列表

```c
    yaf_route_static.c
    yaf_route_simple.c
    yaf_route_supervar.c
    yaf_route_rewrite.c
    yaf_route_regex.c
    yaf_route_map.c
```

无路是哪个路由协议最后功能都是为了设置module，controller，action的名称

> yaf_dispatcher_handle() 开始分发

```c
    //只看下主逻辑 
    int yaf_dispatcher_handle(yaf_dispatcher_t *dispatcher, yaf_request_t *request,  yaf_response_t *response, yaf_view_t *view TSRMLS_DC) {
    
        zend_class_entry *request_ce;
        //代码根目录
        char *app_dir = YAF_G(directory);
    
        request_ce = Z_OBJCE_P(request);
        //代表request开始，如果出现异常，则会在把当前状态改成0
        //在分发循环while会判断这个值，如果为0则代表之前分发中
        //存在异常所以保证在分发forward_limit次数内再继续分发
        //如果等于1则代表没有异常情况，分发完毕.
        yaf_request_set_dispatched(request, 1 TSRMLS_CC);
        if (!app_dir) {
            yaf_trigger_error(YAF_ERR_STARTUP_FAILED TSRMLS_CC, "%s requires %s(which set the application.directory) to be initialized first",
                    yaf_dispatcher_ce->name, yaf_application_ce->name);
            return 0;
        } else {
            int    is_def_module = 0;
            /* int is_def_ctr = 0; */
            zval *module, *controller, *dmodule, /* *dcontroller,*/ *instantly_flush;
            zend_class_entry *ce;
            yaf_controller_t *executor;
            zend_function    *fptr;
            //获取module
            module        = zend_read_property(request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_MODULE), 1 TSRMLS_CC);
            //获取controller
            controller    = zend_read_property(request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_CONTROLLER), 1 TSRMLS_CC);
            //获取默认module默认为Index
            dmodule        = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_MODULE), 1 TSRMLS_CC);
    
            if (Z_TYPE_P(module) != IS_STRING
                    || !Z_STRLEN_P(module)) {
                yaf_trigger_error(YAF_ERR_DISPATCH_FAILED TSRMLS_CC, "Unexcepted a empty module name");
                return 0;
            //判断当前请求的model是否在modules里面，默认情况modules里面也是Index
            //如果自定义其他的model，就需要添加在modules这个选项里面，添加的无论是
            //什么model都需要存在Index这个model，类似于:Index,Admin,Main这样，不添
            //加的话会提示错误.
            } else if (!yaf_application_is_module_name(Z_STRVAL_P(module), Z_STRLEN_P(module) TSRMLS_CC)) {
                yaf_trigger_error(YAF_ERR_NOTFOUND_MODULE TSRMLS_CC, "There is no module %s", Z_STRVAL_P(module));
                return 0;
            }
    
            if (Z_TYPE_P(controller) != IS_STRING
                    || !Z_STRLEN_P(controller)) {
                yaf_trigger_error(YAF_ERR_DISPATCH_FAILED TSRMLS_CC, "Unexcepted a empty controller name");
                return 0;
            }
            //判断当前的model是否等于默认的model
            //如果等于就去默认的Controllers目录下找
            //控制器，如果不等于这个值就为0则就去
            //modules/model/controllers/下面找控制器
            //后面会有体现
            if (strncasecmp(Z_STRVAL_P(dmodule), Z_STRVAL_P(module), Z_STRLEN_P(module)) == 0) {
                is_def_module = 1;
            }
            //找到对应的controller类
            ce = yaf_dispatcher_get_controller(app_dir, Z_STRVAL_P(module), Z_STRVAL_P(controller), Z_STRLEN_P(controller), is_def_module TSRMLS_CC);
            if (!ce) {
                return 0;
            } else {
                zval  *action, *render, *ret = NULL;
                char  *action_lower, *func_name, *view_dir;
                uint  func_name_len;
    
                yaf_controller_t *icontroller;
                //实例化controoller对象
                MAKE_STD_ZVAL(icontroller);
                object_init_ex(icontroller, ce);
    
                yaf_controller_construct(ce, icontroller, request, response, view, NULL TSRMLS_CC);
                if (EG(exception)) {
                    zval_ptr_dtor(&icontroller);
                    return 0;
                }
    
                //获取view路径，如果这个is_def_module为1
                //则取默认的views目录，如果为0，则取 modules/model/views 这个目录
                if (is_def_module) {
                    spprintf(&view_dir, 0, "%s%c%s", app_dir, DEFAULT_SLASH, "views");
                } else {
                    spprintf(&view_dir, 0, "%s%c%s%c%s%c%s", app_dir, DEFAULT_SLASH, "modules", DEFAULT_SLASH, Z_STRVAL_P(module), DEFAULT_SLASH, "views");
                }
    
                if (YAF_G(view_directory)) {
                    efree(YAF_G(view_directory));
                }
                YAF_G(view_directory) = view_dir;
    
                zend_update_property(ce, icontroller, ZEND_STRL(YAF_CONTROLLER_PROPERTY_NAME_NAME),    controller TSRMLS_CC);
    
                //获取action
                action         = zend_read_property(request_ce, request, ZEND_STRL(YAF_REQUEST_PROPERTY_NAME_ACTION), 1 TSRMLS_CC);
                action_lower = zend_str_tolower_dup(Z_STRVAL_P(action), Z_STRLEN_P(action));
    
                /* because the action might call the forward to override the old action */
                Z_ADDREF_P(action);
                //拼接一个action函数名
                func_name_len = spprintf(&func_name,  0, "%s%s", action_lower, "action");
                efree(action_lower);
                //判断在controller对象里面是否有这个action函数，如 indexaction
                if (zend_hash_find(&((ce)->function_table), func_name, func_name_len + 1, (void **)&fptr) == SUCCESS) {
                    //省略......
    
                    executor = icontroller;
    
                    //如果存在这个action函数则调用
                    zend_call_method(&icontroller, ce, NULL, func_name, func_name_len, &ret, 0, NULL, NULL TSRMLS_CC);
    
                    efree(func_name);
    
                    //省略......
    
                //如果不存在这个action函数则按第二种action方式去获
                //取看看在这个controller里面是否存在actions变量，因
                //为有可能在这个actions数组变量里面记录所有的action
                //类方法地址
                } else if ((ce = yaf_dispatcher_get_action(app_dir, icontroller,
                                Z_STRVAL_P(module), is_def_module, Z_STRVAL_P(action), Z_STRLEN_P(action) TSRMLS_CC))
                        && (zend_hash_find(&(ce)->function_table, YAF_ACTION_EXECUTOR_NAME,
                                sizeof(YAF_ACTION_EXECUTOR_NAME), (void **)&fptr) == SUCCESS)) {
                    //省略......
    
                    //实例化这个Action类
                    MAKE_STD_ZVAL(iaction);
                    object_init_ex(iaction, ce);
                    executor = iaction;
                    //省略......
    
                    //调用这个Action类里面的execute方法，可以看到这个方法是固定的
                    zend_call_method_with_0_params(&iaction, ce, NULL, "execute", &ret);
    
                    //省略......
                } else {
                    efree(func_name);
                    zval_ptr_dtor(&icontroller);
                    return 0;
                }
                //下面这部分就是视图view自动渲染部分，不在详细分析了
                if (executor) {
                    int auto_render = 1;
                    render = zend_read_property(ce, executor, ZEND_STRL(YAF_CONTROLLER_PROPERTY_NAME_RENDER), 1 TSRMLS_CC);
                    instantly_flush    = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_FLUSH), 1 TSRMLS_CC);
                    if (render == EG(uninitialized_zval_ptr)) {
                        render = zend_read_property(yaf_dispatcher_ce, dispatcher, ZEND_STRL(YAF_DISPATCHER_PROPERTY_NAME_RENDER), 1 TSRMLS_CC);
                        auto_render = Z_BVAL_P(render);
                    } else if (Z_TYPE_P(render) <= IS_BOOL && !Z_BVAL_P(render)) {
                        auto_render = 0;
                    }
                    //是否自动渲染view
                    if (auto_render) {
                        ret = NULL;
                        if (!Z_BVAL_P(instantly_flush)) {
                            zend_call_method_with_1_params(&executor, ce, NULL, "render", &ret, action);
                            zval_ptr_dtor(&executor);
    
                            if (!ret) {
                                zval_ptr_dtor(&action);
                                return 0;
                            } else if (IS_BOOL == Z_TYPE_P(ret) && !Z_BVAL_P(ret)) {
                                zval_ptr_dtor(&ret);
                                zval_ptr_dtor(&action);
                                return 0;
                            }
    
                            if (Z_TYPE_P(ret) == IS_STRING && Z_STRLEN_P(ret)) {
                                yaf_response_alter_body(response, NULL, 0, Z_STRVAL_P(ret), Z_STRLEN_P(ret), YAF_RESPONSE_APPEND  TSRMLS_CC);
                            } 
    
                            zval_ptr_dtor(&ret);
                        } else {
                            zend_call_method_with_1_params(&executor, ce, NULL, "display", &ret, action);
                            zval_ptr_dtor(&executor);
    
                            if (!ret) {
                                zval_ptr_dtor(&action);
                                return 0;
                            }
    
                            if ((Z_TYPE_P(ret) == IS_BOOL && !Z_BVAL_P(ret))) {
                                zval_ptr_dtor(&ret);
                                zval_ptr_dtor(&action);
                                return 0;
                            }
                            zval_ptr_dtor(&ret);
                        }
                    } else {
                        zval_ptr_dtor(&executor);
                    }
                }
                zval_ptr_dtor(&action);
            }
            return 1;
        }
    return 0;
    }
```

> yaf_dispatcher_get_controller() 获取 controller 类

```c
    zend_class_entry * yaf_dispatcher_get_controller(char* app_dir, char *module, char *controller, int len, int def_module TSRMLS_DC) {
        char      *directory     = NULL;
        int     directory_len     = 0;
    
        //这块之前说过，如果def_module等于1走默认的路径
        //如果等于0则走modules下的路径
        if (def_module) {
            // directory = app_dir/controllers
            directory_len = spprintf(&directory, 0, "%s%c%s", app_dir, DEFAULT_SLASH, YAF_CONTROLLER_DIRECTORY_NAME);
        } else {
            // directory = app_dir/modules/model/controllers
            directory_len = spprintf(&directory, 0, "%s%c%s%c%s%c%s", app_dir, DEFAULT_SLASH,
                    YAF_MODULE_DIRECTORY_NAME, DEFAULT_SLASH, module, DEFAULT_SLASH, YAF_CONTROLLER_DIRECTORY_NAME);
        }
    
        if (directory_len) {
            char *class         = NULL;
            char *class_lowercase     = NULL;
            int class_len        = 0;
            zend_class_entry **ce     = NULL;
            // 这里根据配置区分前缀模式还是后缀模式 
            // Controller_Index 或者 Index_Controller 
            // ControllerIndex 或者 IndexController 
            if (YAF_G(name_suffix)) {
                class_len = spprintf(&class, 0, "%s%s%s", controller, YAF_G(name_separator), "Controller");
            } else {
                class_len = spprintf(&class, 0, "%s%s%s", "Controller", YAF_G(name_separator), controller);
            }
            //转小写
            class_lowercase = zend_str_tolower_dup(class, class_len);
    
            //是否存在这个Controller类
            if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void **)&ce) != SUCCESS) {
                //加载这个Controller类
                if (!yaf_internal_autoload(controller, len, &directory TSRMLS_CC)) {
                    yaf_trigger_error(YAF_ERR_NOTFOUND_CONTROLLER TSRMLS_CC, "Failed opening controller script %s: %s", directory, strerror(errno));
                    efree(class);
                    efree(class_lowercase);
                    efree(directory);
                    return NULL;
                //获取这个Controller类指针
                } else if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void **) &ce) != SUCCESS)  {
                    yaf_trigger_error(YAF_ERR_AUTOLOAD_FAILED TSRMLS_CC, "Could not find class %s in controller script %s", class, directory);
                    efree(class);
                    efree(class_lowercase);
                    efree(directory);
                    return 0;
                //判断是否继承 Yaf_Controller_Abstract
                } else if (!instanceof_function(*ce, yaf_controller_ce TSRMLS_CC)) {
                    yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "Controller must be an instance of %s", yaf_controller_ce->name);
                    efree(class);
                    efree(class_lowercase);
                    efree(directory);
                    return 0;
                }
            }
    
            efree(class);
            efree(class_lowercase);
            efree(directory);
    
            return *ce;
        }
    
        return NULL;
    }
```

> yaf_dispatcher_get_action() 获取 action 类

```c
    zend_class_entry * yaf_dispatcher_get_action(char *app_dir, yaf_controller_t *controller, char *module, int def_module, char *action, int len TSRMLS_DC) {
        zval **ppaction, *actions_map;
    
        //获取actions数组
        actions_map = zend_read_property(Z_OBJCE_P(controller), controller, ZEND_STRL(YAF_CONTROLLER_PROPERTY_NAME_ACTIONS), 1 TSRMLS_CC);
    
        if (IS_ARRAY == Z_TYPE_P(actions_map)) {
            zend_class_entry **ce;
            uint  class_len;
            char *class_name, *class_lowercase;
            char *action_upper = estrndup(action, len);
            //将action名字首字母转为大写
            *(action_upper) = toupper(*action_upper);
    
            //前后缀模式
            //Index_Action 或 Action_Index
            //IndexAction 或 ActionIndex
            if (YAF_G(name_suffix)) {
                class_len = spprintf(&class_name, 0, "%s%s%s", action_upper, YAF_G(name_separator), "Action");
            } else {
                class_len = spprintf(&class_name, 0, "%s%s%s", "Action", YAF_G(name_separator), action_upper);
            }
    
            //类名转换为小写
            class_lowercase = zend_str_tolower_dup(class_name, class_len);
            //是否存在这个Action类
            if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void **) &ce) == SUCCESS) {
                efree(action_upper);
                efree(class_lowercase);
                //是否继承Yaf_Action_Abstract
                if (instanceof_function(*ce, yaf_action_ce TSRMLS_CC)) {
                    efree(class_name);
                    return *ce;
                } else {
                    yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "Action %s must extends from %s", class_name, yaf_action_ce->name);
                    efree(class_name);
                    return NULL;
                }
            }
            //在数组中找到对应的key（action名称）
            if (zend_hash_find(Z_ARRVAL_P(actions_map), action, len + 1, (void **)&ppaction) == SUCCESS) {
                char *action_path;
                uint action_path_len;
                //把值（后面的文件路径）赋给action_path
                //也就是得到了action类所在的文件了
                action_path_len = spprintf(&action_path, 0, "%s%c%s", app_dir, DEFAULT_SLASH, Z_STRVAL_PP(ppaction));
                //导入这个类文件
                if (yaf_loader_import(action_path, action_path_len, 0 TSRMLS_CC)) {
                    //action类是否存在
                    if (zend_hash_find(EG(class_table), class_lowercase, class_len + 1, (void **) &ce) == SUCCESS) {
                        efree(action_path);
                        efree(action_upper);
                        efree(class_lowercase);
                        //是否继承Yaf_Action_Abstract
                        if (instanceof_function(*ce, yaf_action_ce TSRMLS_CC)) {
                            efree(class_name);
                            //返回Action类指针
                            return *ce;
                        } else {
                            yaf_trigger_error(YAF_ERR_TYPE_ERROR TSRMLS_CC, "Action %s must extends from %s", class_name, yaf_action_ce->name);
                            efree(class_name);
                        }
    
                    } else {
                        yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "Could not find action %s in %s", class_name, action_path);
                    }
    
                    efree(action_path);
                    efree(action_upper);
                    efree(class_name);
                    efree(class_lowercase);
    
                } else {
                    yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "Failed opening action script %s: %s", action_path, strerror(errno));
                    efree(action_path);
                }
            } else {
                yaf_trigger_error(YAF_ERR_NOTFOUND_ACTION TSRMLS_CC, "There is no method %s%s in %s::$%s",
                        action, "Action", Z_OBJCE_P(controller)->name, YAF_CONTROLLER_PROPERTY_NAME_ACTIONS);
            } 
         } else if (YAF_G(st_compatible)) {
                //省略.....
    
                //这部分不说了，大概就是在 actions 里面没有找到这个action的路径
                //那么就尝试自己拼接路径去加载
    
                if (def_module) {
                    spprintf(&directory, 0, "%s%c%s", app_dir, DEFAULT_SLASH, "actions");
                } else {
                    spprintf(&directory, 0, "%s%c%s%c%s%c%s", app_dir, DEFAULT_SLASH,
                        "modules", DEFAULT_SLASH, module, DEFAULT_SLASH, "actions");
                }
    
               //省略.....
         }
    }
```

上面也看到action的路径是按照你所填写的映射中地址加载，但是类名却是action的名称拼接的，所以虽然类文件不需要按照Yaf的标准路径设定，但是类名必须和action一致，在这个环节可能会因为action的特殊性出现找不到类的问题.

#### Yaf 自动加载

在实例化 application 类的时候，内部会自动实例化一个 Yaf_Loader 对象，同时往内核注册了一个自动加载器 autoload 这里注册自动加载器也是用内核提供的 spl_autoload_register

> yaf_loader_register() 注册自动加载器

```c
    int yaf_loader_register(yaf_loader_t *loader TSRMLS_DC) {
        zval *autoload, *method, *function, *ret = NULL;
        zval **params[1] = {&autoload};
    
        //设置autoload为一个数组
        MAKE_STD_ZVAL(autoload);
        array_init(autoload);
    
    #define YAF_AUTOLOAD_FUNC_NAME  "autoload"
    #define YAF_SPL_AUTOLOAD_REGISTER_NAME "spl_autoload_register"
    
        //设置method = autoload
        MAKE_STD_ZVAL(method);
        ZVAL_STRING(method, YAF_AUTOLOAD_FUNC_NAME, 1);
    
        //把loader对象添加到autoload数组里面
        zend_hash_next_index_insert(Z_ARRVAL_P(autoload), &loader, sizeof(yaf_loader_t *), NULL);
        //把method添加到autoload数组里面
        zend_hash_next_index_insert(Z_ARRVAL_P(autoload), &method, sizeof(zval *), NULL);
    
        //设置function =  spl_autoload_register
        MAKE_STD_ZVAL(function);
        ZVAL_STRING(function, YAF_SPL_AUTOLOAD_REGISTER_NAME, 0);
    
        //这里注册自动加载器跟php中调用spl_autoload_register形式几乎差不多
        //spl_autoload_register(array(loader,autoload))
    
        do {
            zend_fcall_info fci = {
                sizeof(fci),
                EG(function_table),
                function,
                NULL,
                &ret,
                1,
                (zval ***)params,
                NULL,
                1
            };
            // 调用 spl_autoload_register 注册
            if (zend_call_function(&fci, NULL TSRMLS_CC) == FAILURE) {
                if (ret) {
                    zval_ptr_dtor(&ret);
                }
                efree(function);
                zval_ptr_dtor(&autoload);
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "Unable to register autoload function %s", YAF_AUTOLOAD_FUNC_NAME);
                return 0;
            }
            if (ret) {
                zval_ptr_dtor(&ret);
            }
            efree(function);
            zval_ptr_dtor(&autoload);
        } while (0);
        return 1;
    }
```

> PHP_METHOD(yaf_loader, autoload) 自动加载器

```c
    PHP_METHOD(yaf_loader, autoload) {
        char *class_name, *origin_classname, *app_directory, *directory = NULL, *file_name = NULL;
    #ifdef YAF_HAVE_NAMESPACE
        char *origin_lcname = NULL;
    #endif
        uint separator_len, class_name_len, file_name_len = 0;
    
        //获取类名
        if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &class_name, &class_name_len) == FAILURE) {
            return;
        }
        //分隔符长度
        separator_len = YAF_G(name_separator_len);
        //根目录
        app_directory = YAF_G(directory);
        origin_classname = class_name;
    
        do {
            if (!class_name_len) {
                break;
            }
    //命名空间处理方式
    #ifdef YAF_HAVE_NAMESPACE
            {
                int pos = 0;
                origin_lcname = estrndup(class_name, class_name_len);
                class_name       = origin_lcname;
                while (pos < class_name_len) {
                    if (*(class_name + pos) == '\\') {
                        *(class_name + pos) = '_';
                    }
                    pos += 1;
                }
            }
    #endif
    #define YAF_LOADER_RESERVERD                "Yaf_"
            //不允许类名Yaf打头
            if (strncmp(class_name, YAF_LOADER_RESERVERD, YAF_LOADER_LEN_RESERVERD) == 0) {
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "You should not use '%s' as class name prefix", YAF_LOADER_RESERVERD);
            }
    #define YAF_LOADER_MODEL                    "Model"
            //是否属于Model类
            if (yaf_loader_is_category(class_name, class_name_len, YAF_LOADER_MODEL, YAF_LOADER_LEN_MODEL TSRMLS_CC)) {
    #define YAF_MODEL_DIRECTORY_NAME              "models"
                //获取类文件的路径
                //app_directory/models
                spprintf(&directory, 0, "%s/%s", app_directory, YAF_MODEL_DIRECTORY_NAME);
                //获取文件名字的长度，减去分隔符和后缀Index_Model
                file_name_len = class_name_len - separator_len - YAF_LOADER_LEN_MODEL;
               //是否配置后缀式
                if (YAF_G(name_suffix)) {
                    //获取文件名字
                    file_name = estrndup(class_name, file_name_len);
                } else {
                    //获取文件名字
                    file_name = estrdup(class_name + YAF_LOADER_LEN_MODEL + separator_len);
                }
    
                break;
            }
    #define YAF_LOADER_PLUGIN                    "Plugin"
            //是否属于plugin类，流程跟上面一样
            if (yaf_loader_is_category(class_name, class_name_len, YAF_LOADER_PLUGIN, YAF_LOADER_LEN_PLUGIN TSRMLS_CC)) {
                //获取类文件的路径
                //app_directory/plugins
                spprintf(&directory, 0, "%s/%s", app_directory, YAF_PLUGIN_DIRECTORY_NAME);
                file_name_len = class_name_len - separator_len - YAF_LOADER_LEN_PLUGIN;
    
                if (YAF_G(name_suffix)) {
                    file_name = estrndup(class_name, file_name_len);
                } else {
                    file_name = estrdup(class_name + YAF_LOADER_LEN_PLUGIN + separator_len);
                }
    
                break;
            }
    #define YAF_LOADER_CONTROLLER                "Controller"
            //是否属于Controller类，流程跟上面一样
            if (yaf_loader_is_category(class_name, class_name_len, YAF_LOADER_CONTROLLER, YAF_LOADER_LEN_CONTROLLER TSRMLS_CC)) {
                //获取类文件的路径
                //app_directory/controllers
                //可以看到这里只能获取Controllers目录下的控制器
                //不能获取models/model/controllers/这种形式
                spprintf(&directory, 0, "%s/%s", app_directory, YAF_CONTROLLER_DIRECTORY_NAME);
                file_name_len = class_name_len - separator_len - YAF_LOADER_LEN_CONTROLLER;
    
                if (YAF_G(name_suffix)) {
                    file_name = estrndup(class_name, file_name_len);
                } else {
                    file_name = estrdup(class_name + YAF_LOADER_LEN_CONTROLLER + separator_len);
                }
    
                break;
            }
    
    
    /* {{{ This only effects internally */
            if (YAF_G(st_compatible) && (strncmp(class_name, YAF_LOADER_DAO, YAF_LOADER_LEN_DAO) == 0
                        || strncmp(class_name, YAF_LOADER_SERVICE, YAF_LOADER_LEN_SERVICE) == 0)) {
                /* this is a model class */
                spprintf(&directory, 0, "%s/%s", app_directory, YAF_MODEL_DIRECTORY_NAME);
            }
    /* }}} */
    
            file_name_len = class_name_len;
            file_name     = class_name;
    
        } while(0);
    
        if (!app_directory && directory) {
            efree(directory);
    #ifdef YAF_HAVE_NAMESPACE
            if (origin_lcname) {
                efree(origin_lcname);
            }
    #endif
            if (file_name != class_name) {
                efree(file_name);
            }
    
            php_error_docref(NULL TSRMLS_CC, E_WARNING,
                    "Couldn't load a framework MVC class without an %s initializing", yaf_application_ce->name);
            RETURN_FALSE;
        }
    
        if (!YAF_G(use_spl_autoload)) {
            //加载这个类
            if (yaf_internal_autoload(file_name, file_name_len, &directory TSRMLS_CC)) {
                //把类名转成小写
                char *lc_classname = zend_str_tolower_dup(origin_classname, class_name_len);
                //是否存在这个类，如果存在则代表加载成功
                if (zend_hash_exists(EG(class_table), lc_classname, class_name_len + 1)) {
    #ifdef YAF_HAVE_NAMESPACE
                    if (origin_lcname) {
                        efree(origin_lcname);
                    }
    #endif
                    if (directory) {
                        efree(directory);
                    }
    
                    if (file_name != class_name) {
                        efree(file_name);
                    }
    
                    efree(lc_classname);
                    //返回成功
                    RETURN_TRUE;
                } else {
                    efree(lc_classname);
                    php_error_docref(NULL TSRMLS_CC, E_STRICT, "Could not find class %s in %s", class_name, directory);
                }
            }  else {
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "Failed opening script %s: %s", directory, strerror(errno));
            }
    
    #ifdef YAF_HAVE_NAMESPACE
            if (origin_lcname) {
                efree(origin_lcname);
            }
    #endif
            if (directory) {
                efree(directory);
            }
            if (file_name != class_name) {
                efree(file_name);
            }
            RETURN_TRUE;
        } else {
            //跟上面流程差不多
            char *lower_case_name = zend_str_tolower_dup(origin_classname, class_name_len);
            if (yaf_internal_autoload(file_name, file_name_len, &directory TSRMLS_CC) &&
                    zend_hash_exists(EG(class_table), lower_case_name, class_name_len + 1)) {
    #ifdef YAF_HAVE_NAMESPACE
                if (origin_lcname) {
                    efree(origin_lcname);
                }
    #endif
                if (directory) {
                    efree(directory);
                }
                if (file_name != class_name) {
                    efree(file_name);
                }
    
                efree(lower_case_name);
                RETURN_TRUE;
            }
    #ifdef YAF_HAVE_NAMESPACE
            if (origin_lcname) {
                efree(origin_lcname);
            }
    #endif
            if (directory) {
                efree(directory);
            }
            if (file_name != class_name) {
                efree(file_name);
            }
            efree(lower_case_name);
            RETURN_FALSE;
        }
    }
```

> yaf_internal_autoload() 加载类文件

```c
    int yaf_internal_autoload(char *file_name, uint name_len, char **directory TSRMLS_DC) {
        zval *library_dir, *global_dir;
        char *q, *p, *seg;
        uint seg_len, directory_len, status;
        char *ext = YAF_G(ext);
        smart_str buf = {0};
        //判断传递的路径是否为空
        //如果为空则代表要加载的类文件不属于yaf框架规定的目录
        //有可能是公共库文件目录
        if (NULL == *directory) {
            char *library_path;
            uint  library_path_len;
            yaf_loader_t *loader;
    
            loader = yaf_loader_instance(NULL, NULL, NULL TSRMLS_CC);
    
            if (!loader) {
                /* since only call from userspace can cause loader is NULL, exception throw will works well */
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "%s need to be initialize first", yaf_loader_ce->name);
                return 0;
            } else {
                //获取本地类库地址
                library_dir = zend_read_property(yaf_loader_ce, loader, ZEND_STRL(YAF_LOADER_PROPERTY_NAME_LIBRARY), 1 TSRMLS_CC);
                //获取全局类库地址
                global_dir    = zend_read_property(yaf_loader_ce, loader, ZEND_STRL(YAF_LOADER_PROPERTY_NAME_GLOBAL_LIB), 1 TSRMLS_CC);
                //判断类名前缀是否已经注册过，如果已经注册过则在本地类库去找
                //就是调用 Yaf_Loader::registerLocalNamespace() 注册
                //如果不注册的话全部去公共类库下寻找
                if (yaf_loader_is_local_namespace(loader, file_name, name_len TSRMLS_CC)) {
                    library_path = Z_STRVAL_P(library_dir);
                    library_path_len = Z_STRLEN_P(library_dir);
                } else {
                    library_path = Z_STRVAL_P(global_dir);
                    library_path_len = Z_STRLEN_P(global_dir);
                }
            }
    
            if (NULL == library_path) {
                php_error_docref(NULL TSRMLS_CC, E_WARNING, "%s requires %s(which set the library_directory) to be initialized first", yaf_loader_ce->name, yaf_application_ce->name);
                return 0;
            }
    
            smart_str_appendl(&buf, library_path, library_path_len);
        } else {
            smart_str_appendl(&buf, *directory, strlen(*directory));
            efree(*directory);
        }
    
        directory_len = buf.len;
    
        /* aussume all the path is not end in slash */
        smart_str_appendc(&buf, DEFAULT_SLASH);
    
        //如果这个文件名或者类名是这种形式的Service_Http_Post
        //下面这段代码就会把这个类名切分成路径
        //directory/Service/Http/Post.php 这样
    
        p = file_name;
        q = p;
    
        while (1) {
            while(++q && *q != '_' && *q != '\0');
    
            if (*q != '\0') {
                seg_len    = q - p;
                seg         = estrndup(p, seg_len);
                smart_str_appendl(&buf, seg, seg_len);
                efree(seg);
                smart_str_appendc(&buf, DEFAULT_SLASH);
                p         = q + 1;
            } else {
                break;
            }
        }
    
        if (YAF_G(lowcase_path)) {
            /* all path of library is lowercase */
            zend_str_tolower(buf.c + directory_len, buf.len - directory_len);
        }
    
        smart_str_appendl(&buf, p, strlen(p));
        smart_str_appendc(&buf, '.');
        smart_str_appendl(&buf, ext, strlen(ext));
    
        smart_str_0(&buf);
    
        if (directory) {
            *(directory) = estrndup(buf.c, buf.len);
        }
        //这里最后把类文件导入并调用内核接口进行编译
        status = yaf_loader_import(buf.c, buf.len, 0 TSRMLS_CC);
        smart_str_free(&buf);
    
        if (!status)
               return 0;
    
        return 1;
    }
```

### 结束

上面介绍的大致就是 Yaf 框架一个运行流程，并且把框架的主要代码都分析了一遍，可以以此作为引导，在阅读分析源码的时候可以边看源码边对照 [Yaf 框架官方文档][8] 或者在用Yaf框架搭建一个环境，运行下，在对照源码分析即可。

[0]: http://www.jianshu.com/u/9642a0c8db39
[1]: ../img/2416964-dfe73ae94e387775.png
[2]: http://www.laruence.com/manual/yaf.routes.static.html
[3]: http://www.laruence.com/manual/yaf.routes.static.html#yaf.routes.simple
[4]: http://www.laruence.com/manual/yaf.routes.static.html#yaf.routes.supervar
[5]: http://www.laruence.com/manual/yaf.routes.static.html#yaf.routes.rewrite
[6]: http://www.laruence.com/manual/yaf.routes.static.html#yaf.routes.regex
[7]: http://www.laruence.com/manual/yaf.routes.static.html#yaf.routes.map
[8]: http://www.laruence.com/manual/