## The Yaf_Loader class

### 简介

Yaf_Loader类为Yaf提供了自动加载功能, 它根据类名中包含的路径信息实现类的定位和自动加载.

Yaf_Loader也提供了对传统的require_once的替代方案, 相比传统的require_once, 因为舍弃对require的支持, 所以性能能有一丁点小优势.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Loader.

```php
final Yaf_Loader {
protected static Yaf_Loader _instance ;
protected string _library_directory ;
protected string _global_library_directory ;
protected string _local_ns ;
public static Yaf_Loader getInstance ( string $local_library_directory = NULL ,
string $global_library_directory = NULL );
public Yaf_Loader registerLocalNamespace ( mixed $namespace );
public boolean getLocalNamespace ( void );
public boolean clearLocalNamespace ( void );
public boolean isLocalName ( string $class_name );
public static boolean import ( string $file_name );
public boolean autoload ( string $class_name );
}
```

##### 属性说明
- _instance  
Yaf_Loader实现了单利模式, 一般的它由Yaf_Application负责初始化. 此属性保存当前实例

- _library_directory  
本地(自身)类加载路径, 一般的, 属性的值来自配置文件中的app.library

- _global_library_directory  
全局类加载路径, 一般的, 属性的值来自php.ini中的app.library

- _local_ns  
本地类的类名前缀, 此属性通过Yaf_Loader::registerLocalNamespace来添加新的值



## 名称

Yaf_Loader::getInstance

(Since Yaf 1.0.0.5)

    public static Yaf_Loader Yaf_Loader::getInstance( string  $local_library_directory = NULL ,
                                                    string  $global_library_directory = NULL );
获取当前的Yaf_Loader实例

> 参数

$local_library_directory  
本地(自身)类库目录, 如果留空, 则返回已经实例化过的Yaf_Loader实例


> Yaf_Loader是单例模式, 所以即使第二次以不同的参数实例化一个Yaf_Loader, 得到的仍然是已经实例化的第一个实例.

$global_library_directory  
全局类库目录, 如果留空则会认为和$local_library_directory相同.

> 返回值   
Yaf_Loader

> **Yaf_Loader::getInstance 的例子**

```php
<?php
$loader = Yaf_Loader::getInstance();
?>
```


## 名称

Yaf_Loader::import

(Since Yaf 1.0.0.5)

    public static boolean Yaf_Loader::import ( string  $file_name );

导入一个PHP文件, 因为Yaf_Loader::import只是专注于一次包含, 所以要比传统的require_once性能好一些

> 参数 

- _$file_name_  
要载入的文件路径, 可以为绝对路径和相对路径. 如果为相对路径, 则会以应用的本地类目录(ap.library)为基目录.

>返回值 

成功返回TRUE, 失败返回FALSE.

> **Yaf_Loader::import 的例子**

```php
<?php
//绝对路径
Yaf_Loader::import("/usr/local/foo.php");

//相对路径, 会在APPLICATION_PATH."/library"下加载
Yaf_loader::import("plugins/User.php");
?>
```



## 名称

Yaf_Loader::autoload

(Since Yaf 1.0.0.5)

    public static boolean Yaf_Loader::autoload( string  $class_name );
载入一个类, 这个方法被Yaf用作自动加载类的方法, 当然也可以手动调用.

> 参数

$class_name   
要载入的类名, 类名必须包含路径信息, 也就是下划线分隔的路径信息和类名. 载入的过程中, 首先会判断这个类名是否是本地类, 如果是本地类, 则使用本地类类库目录, 否则使用全局类目录. 然后判断yaf.lowcase_path是否开启, 如果开启, 则会把类名中的路径部分全部小写. 然后加载, 执行.

        
        /** yaf.lowcase_path=0 */
        Foo_Bar_Dummy表示这个类存在于类库目录下的Foo/Bar/Dummy.php

        /** yaf.lowcase_path=1 */
        Foo_Bar_Dummy表示这个类存在于类库目录下的foo/bar/Dummy.php
        
       

> 注意

在php.ini中的yaf.lowcase_path开启的情况下, 路径信息中的目录部分都会被转换成小写.

>返回值

成功返回TRUE

> 注意

在php.ini中的yaf.use_spl_autoload关闭的情况下, 即使类没有找到, Yaf_Loader::autoload也会返回TRUE, 剥夺其后面的自动加载函数的执行权利.

> **Yaf_Loader::autoload 的例子**

```php
<?php
Yaf_Loader::autoload("Baidu_ST_Dummy_Bar");
?>
```


## 名称

Yaf_Loader::registerLocalNamespace

(Since Yaf 1.0.0.5)

    public Yaf_Loader Yaf_Loader::registerLocalNamespace( mixed  $local_name_prefix );
注册本地类前缀, 是的对于以这些前缀开头的本地类, 都从本地类库路径中加载.

>参数

$local_name_prefix   
字符串或者是数组格式的类名前缀, 不包含前缀后面的下划线.

> 返回值

Yaf_Loader

> **Yaf_Loader::registerLocalNamespace 的例子**

```php
<?php
Yaf_Loader::getInstance()->registerLocalNamespace("Foo");
Yaf_Loader::getInstance()->registerLocalNamespace(array("Foo", "Bar"));
?>
```


## 名称

Yaf_Loader::isLocalName

(Since Yaf 1.0.0.5)

    public boolean Yaf_Loader::isLocalName( string  $class_name );
判断一个类, 是否是本地类.

> 参数

$class_name  
字符串的类名, 本方法会根据下划线分隔截取出类名的第一部分, 然后在Yaf_Loader的_local_ns中判断是否存在, 从而确定结果.

> 返回值

boolean

> **Yaf_Loader::isLocalName 的例子**

```php
<?php
Yaf_Loader::getInstance()->registrLocalNamespace("Foo");

Yaf_Loader::getInstance()->isLocalName("Foo_Bar");//TRUE
Yaf_Loader::getInstance()->isLocalName("FooBar");//FALSE
?>
```




## 名称

Yaf_Loader::getLocalNamespace

(Since Yaf 1.0.0.5)

    public array Yaf_Loader::getLocalNamespace( void  );
获取当前已经注册的本地类前缀

> 参数

void  
本方法不需要参数

> 返回值

成功返回字符串

> **Yaf_Loader::getLocalNamespace 的例子**

```php
<?php
Yaf_Loader::getInstance()->registerLocalNamespace(array("Foo", "Bar"));
print(Yaf_Loader::getInstance()->getLocalNamespace());
?>
```
    
输出:

     :Foo:Bar:


## 名称

Yaf_Loader::clearLocalNamespace

(Since Yaf 1.0.0.5)

    public boolean Yaf_Loader::clearLocalNamespace( void  );
清除已注册的本地类前缀

> 参数

void  
本方法不需要参数

> 返回值

成功返回TRUE, 失败返回FALSE

> **Yaf_Loader::clearLocalNamespace 的例子**

```php
<?php
Yaf_Loader::getInstance()->clearLocalNamespace();
?>
```