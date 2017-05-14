## Yaf_Registry

### 简介
Yaf_Registry, 对象注册表(或称对象仓库)是一个用于在整个应用空间(application space)内存储对象和值的容器. 通过把对象存储在其中,我们可以在整个项目的任何地方使用同一个对象.这种机制相当于一种全局存储. 我们可以通过Yaf_Registry类的静态方法来使用对象注册表. 另外,由于该类是一个数组对象,你可以使用数组形式来访问其中的类方法.

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Registry

```php
Yaf_Registry {
public static Yaf_Registry has ( string $name );
public static Yaf_Registry get ( string $name );
public static Yaf_Registry set ( string $name ,
mixed $value );
public static Yaf_Registry del ( string $name );
}
```


## 名称

Yaf_Registry::set

(Since Yaf 1.0.0.5)

    public static Yaf_Registry Yaf_Registry::set( string  $name ,
                                                  mixed  $value );
往全局注册表添加一个新的项

> 参数

$name  
要注册的项的名字

$value  
要注册的项的值

> 返回值

Yaf_Registry

例子
**例 11.38. Yaf_Registry::set 的例子**

```php
     <?php
     /** 存入 */
     Yaf_Registry::set('config', Yaf_Application::app()->getConfig());

     /* 之后可以在任何地方获取到 */
     $config->Yaf_Registry::get("config");
     ?>
```




## 名称

Yaf_Registry::get

(Since Yaf 1.0.0.5)

    public static Yaf_Registry Yaf_Registry::get( string  $name );
获取注册表中寄存的项

> 参数
   
$name  
要获取的项的名字

> 返回值

成功返回要获取的注册项的值, 失败返回FALSE

例子
**例 11.39. Yaf_Registry::get 的例子**

```php
     <?php
     /** 存入 */
     Yaf_Registry::set('config', Yaf_Application::app()->getConfig());

     /* 之后可以在任何地方获取到 */
     $config->Yaf_Registry::get("config");
     ?>
```


## 名称

Yaf_Registry::has

(Since Yaf 1.0.0.5)

    public static Yaf_Registry Yaf_Registry::has( string  $name );
查询某一项目是否存在于注册表中

> 参数

$name  
要查询的项的名字

> 返回值

存在返回TRUE, 不存在返回FALSE

例子
**例 11.40. Yaf_Registry::has 的例子**

```php
     
     <?php
     /** 存入 */
     Yaf_Registry::set('config', Yaf_Application::app()->hasConfig());

     assert(Yaf_Registry::has("config"));
     ?>
```


## 名称

Yaf_Registry::del

(Since Yaf 1.0.0.5)

    public static Yaf_Registry Yaf_Registry::del( string  $name );
删除存在于注册表中的名为$name的项目

> 参数

$name  
要删除的项的名字

> 返回值

成功返回TRUE, 失败返回FALSE

例子
**例 11.41. Yaf_Registry::del 的例子**

```php
     
     <?php
     /** 存入 */
     Yaf_Registry::set('config', Yaf_Application::app()->delConfig());

     Yaf_Registry::del("config");
     ?>

```




