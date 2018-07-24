## PHP Composer 以及PSR规范

来源：[https://juejin.im/post/5b2c590de51d4558be41b458](https://juejin.im/post/5b2c590de51d4558be41b458)

时间 2018-06-22 10:33:00


随着项目代码量的不断增加，以及一些库的依赖，我们不得不引入包的管理，来解决不易管理、阅读、模块化等问题。
三方库优秀，而且很多人在维护，功能对接也方便，我们没有必要在这个一个常用的功能上花费时间来封装或者造轮子，
很多人在自己的项目中都实践过，出现了问题，反应的issue也很快会被解决掉，功能也不断在完善。
一直强调，站在巨人的肩膀上，我们会走的更远，也许我们自己撸出来的代码难等大雅之堂，无法比拟，
把重心放在自己的核心产品和功能时间上，学会借鉴学习和使用，降低自己的开发成本。
也许有人会觉得我使用三方库可能会造成性能上的影响，有些功能我根本用不到。项目中代码有很多无用的代码，导致文件加载过慢，
其实不用担心，`opcache`可以将php脚本预编译到共享内存中来提升php的性能。


## php Composer psr-4 autoload

Composer 是php用来管理依赖关系的工具，可以在项目中声明外部依赖的工具库，Composer会帮你安装这些依赖的库文件
psr-4 是一种代码规范，能够实现package的自动加载，规范了如何从文件路径自动加载类，同时也规范了自动加载文件的位置


## 自动加载

我们在支持Composer的项目中，只需引入这个文件，加上下面这段php的代码，就可以得到自动加载的支持了

```php
<?php

require_once __DIR __ . '/vendor/autoload.php';
```


## composer.json


```json
{
    "autoload": {
        "psr-4": {
            "Work\\": "src/"
        },
        "psr-0": {
            "Vendor_Namespace_": "src/"
        }
    }
}
```

Composer 将注册一个 PSR-4 autoloader 到 Work 命名空间, PSR-0 则支持`_`，自动转化为目录结构


### classmap

不遵循PSR-0/4规范的类库，


### files

明确的指定文件加载


## 额外的


### repositories 自定义资源包库


#### type

```
composer.json


```


## scripts

Composer 允许你在安装过程中的各个阶段挂接脚本。


### [详细流程][0]
  


### 典型的命令，composer安装时

```
composer install
composer update


```


### 自定义脚本demo

```json
{
    "scripts": {
        "post-update-cmd": "MyVendor\\MyClass::postUpdate",
        "post-package-install": [
            "MyVendor\\MyClass::postPackageInstall"
        ],
        "post-install-cmd": [
            "MyVendor\\MyClass::warmCache",
            "phpunit -c app/",
            "find vendor -type d -name .git -exec rm -rf '{}' \\;"
        ]
    }
}
```

```php
<?php

namespace MyVendor;

use Composer\Script\Event;

class MyClass
{
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        // do stuff
    }

    public static function postPackageInstall(Event $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
        // do stuff
    }

    public static function warmCache(Event $event)
    {
        // make cache toasty
    }
}
```

不然看出执行的脚本可以是一个类中的静态方法，当然也可以是一个函数，还可以是一条`shell`命令

我们也可以手动执行一些命令

```
composer run-script [--dev] [--no-dev] script
```


## 扩展

顺便了解一下其他的几个代码规范


### PSR


#### psr-0 自动加载

psr-1基本代码规范



* 文件内只出现`<?php`和`<?=`标签 （必须）    
* 只是用`utf-8`没有BOM头的php代码 （必须）    
* 声明新的类型符，不产生副作用
* 命名空间遵循`autoload`自动加载`psr-0/4`规范（必须）    
* 类名驼峰（必须）
* 类中的常量下划线`_`分隔（必须）    
* 方法驼峰（必须）
  



[0]: https://link.juejin.im?target=http%3A%2F%2Fdocs.phpcomposer.com%2Farticles%2Fscripts.html