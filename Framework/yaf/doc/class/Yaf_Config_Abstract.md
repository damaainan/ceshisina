## The Yaf_Config_Abstract class

### 简介

Yaf_Config_Abstract被设计在应用程序中简化访问和使用配置数据。它为在应用程序代码中访问这样的配置数据提 供了一个基于用户接口的嵌入式对象属性。配置数据可能来自于各种支持等级结构数据存储的媒体。 Yaf_Config_Abstract实现了Countable, ArrayAccess 和 Iterator 接口。 这样，可以基于Yaf_Config_Abstract对象使用count()函数和PHP语句如foreach, 也可以通过数组方式访问Yaf_Config_Abstract的元素.

Yaf_Config_INI为存储在Ini文件的配置数据提供了适配器。 Yaf_Config_Simple为存储在PHP的数组中的配置数据提供了适配器。

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Config_Abstract

```php
Abstract Yaf_Config_Abstract implements Iterator , ArrayAccess , Countable {
protected array _config ;
protected array _readonly ;
public mixed get ( string $name = NULL );
public mixed __get ( string $name );
public mixed __isset ( string $name );
public mixed __set ( string|int $name ,
mixed $value );
public mixed set ( string|int $name ,
mixed $value );
public mixed count ( void );
public mixed offsetGet ( string|int $name );
public mixed offsetSet ( string|int $name ,
mixed $value );
public mixed offsetExists ( string|int $name );
public mixed offsetUnset ( string|int $name );
public void rewind ( void );
public mixed key ( void );
public mixed next ( void );
public mixed current ( void );
public boolean valid ( void );
public array toArray ( void );
public boolean readOnly ( void );
}
```


属性说明

- _config  
配置实际的保存容器

- _readonly   
表示配置是否容许修改, 对于Yaf_Config_Ini来说, 永远都是TRUE

## The Yaf_Config_Ini class

### 简介

Yaf_Config_INI为存储在Ini文件的配置数据提供了适配器。

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Config_Ini

> 重要
当使用INI文件作为Yaf_Application的配置的时候, 可以打开ap.cache_config来提升性能

**说明**

Yaf_Config_Ini允许开发者通过嵌套的对象属性语法在应用程序中用熟悉的 INI 格式存储和读取配置数据。INI格式在提供拥有配置数据键的等级结构和配置数据节之间的继承能力方面具有专长。 配置数据等级结构通过用点或者句号(.)分离键值。一个节可以扩展或者通过在节的名称之后带一个冒号(:)和被继承的配置数据的节的名称来从另一个节继承。

例  INI文件

```
[base]
database.master.host = localhost
[production : base]
;Yaf的配置
application.directory    = /usr/local/www/production
;应用的配置
webhost                  = www.example.com
database.adapter         = pdo_mysql
database.params.host     = db.example.com
database.params.username = dbuser
database.params.password = secret
database.params.dbname   = dbname
; 开发站点配置数据从生产站点配置数据集成并如果需要可以重写
[dev : production]
application.directory    = /usr/dev/htdocs
database.params.host     = dev.example.com
database.params.username = devuser
database.params.password = devsecret
```

   

dev节, 将得到production节的所有配置, 并间接获得base节的配置 并且覆盖application.directory的配置为"/usr/dev/htdocs"

Yaf_Config_Abstract实现了__get方法, 所以获取配置将会变得很容易

> **获取配置**

```php
$config = new Yaf_Config_Ini('/path/to/config.ini', 'staging');
echo $config->database->get("params")->host;   // 输出 "dev.example.com"
echo $config->get("database")->params->dbname; // 输出 "dbname"
```
   


## The Yaf_Config_Simple class

### 简介

Yaf_Config_Simple为存储在数组中的配置数据提供了适配器。

在PHP5.3之后, 打开yaf.use_namespace的情况下, 也可以使用 Yaf\Config_Simple