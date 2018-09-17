## 关于composer一些学习和理解_v1.0

来源：[https://segmentfault.com/a/1190000008620138](https://segmentfault.com/a/1190000008620138)


## 关于composer一些学习和理解

Composer 不是一个包管理器。是的，它涉及 "packages" 和 "libraries"，但它在每个项目的基础上进行管理，在你项目的某个目录中（例如 vendor）进行安装。默认情况下它不会在全局安装任何东西。因此，这仅仅是一个依赖管理。

这种想法并不新鲜，Composer 受到了 node's npm 和 ruby's bundler 的强烈启发。而当时 PHP 下并没有类似的工具。

Composer 将这样为你解决问题：


* 你有一个项目依赖于若干个库。

* 其中一些库依赖于其他库。

* 你声明你所依赖的东西。

* Composer 会找出哪个版本的包需要安装，并安装它们（将它们下载到你的项目中）。


因为laravel是使用composer管理的，所以一切以laravel为基础。

## 下载安装composer

这里补充的是：

* 因为某些原因，访问国外的composer资源网站很慢，导致`composer install`或者`update`的时候经常连接超时而出错，所以改为中国镜像

```
    全局：
    composer config -g repo.packagist composer https://packagist.phpcomposer.com
    
    局部项目（需要在项目当前目录下执行）：
    composer config repo.packagist composer https://packagist.phpcomposer.com
```

执行完命令后会在`composer.json`文件里面增加这段，这样就代表添加中国镜像成功了，以后执`行composer install`或者`update`之类的命令的时候就会优先使用这个镜像

```json
    "repositories": {
      "packagist": {
        "type": "composer",
        "url": "https://packagist.phpcomposer.com"
      }
    }
```

用`composer selfupdate`来保持composer工具本身的版本更新

## 关于composer.json文件

```json
{
    "name": "laravel/laravel",
    "description": "The Laravel Framework.",
    "keywords": ["framework", "laravel"],
    "license": "MIT",
    "type": "project",
    "require": {  //这里是告诉composer必须要安装的项目，相当于生产环境
        "php": ">=5.5.9",
        "laravel/framework": "5.2.*",  //require 需要一个 包名称，这个就是包名称
        "laravelcollective/html": "5.2.*",
        "yuanchao/laravel-5-markdown-editor": "dev-master"
    },
    "require-dev": {  //这个是开发需要安装的项目，相当于开发环境，可以通过-no-dev来取消安装这个项目里面的包
        "fzaninotto/faker": "~1.4",
        "mockery/mockery": "0.9.*",
        "phpunit/phpunit": "~4.0",
        "symfony/css-selector": "2.8.*|3.0.*",
        "symfony/dom-crawler": "2.8.*|3.0.*"
    },
    "autoload": {
        "classmap": [
            "database"
        ],
        "psr-4": {
            "App\\": "app/"
        }
    },
    "autoload-dev": {
        "classmap": [
            "tests/TestCase.php"
        ]
    },
    "scripts": {
        "post-root-package-install": [
            "php -r \"copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "php artisan key:generate"
        ],
        "post-install-cmd": [
            "Illuminate\\Foundation\\ComposerScripts::postInstall",
            "php artisan optimize"
        ],
        "post-update-cmd": [
            "Illuminate\\Foundation\\ComposerScripts::postUpdate",
            "php artisan optimize"
        ]
    },
    "config": {
        "preferred-install": "dist"
    }
}

```

包名称的版本

```
确切的版本号--------1.0.2---------你可以指定包的确切版本。

范围-------->=1.0 >=1.0,<2.0 >=1.0,<1.1|>=1.2--------通过使用比较操作符可以指定有效的版本范围。 有效的运算符：>、>=、<、<=、!=。你可以定义多个范围，用逗号隔开，这将被视为一个逻辑AND处理。一个管道符号|将作为逻辑OR处理。 AND 的优先级高于 OR。

通配符--------1.0.*--------你可以使用通配符*来指定一种模式。1.0.*与>=1.0,<1.1是等效的。

赋值运算符--------~1.2--------这对于遵循语义化版本号的项目非常有用。~1.2相当于>=1.2,<2.0。
```

我们需要重点关注通配符和波浪符，通配符很好理解，波浪符有点拗口，`~ 最好用例子来解释： ~1.2 相当于 >=1.2,<2.0（标记你所依赖的最低版本），而 ~1.2.3 相当于 >=1.2.3,<1.3。（指定最低版本，但允许版本号的最后一位数字上升。）`语义化很难懂，但是直接看例子是可以知道怎么用的

## 基本用法

composer是通过读取composer.json和composer.lock文件来进行安装包的

在安装依赖后，Composer 将把安装时确切的版本号列表写入 composer.lock 文件。这将锁定改项目的特定版本。`因为 install 命令将会检查锁文件是否存在，如果存在，它将下载指定的版本（忽略 composer.json 文件中的定义）。如果不存在 composer.lock 文件，Composer 将读取 composer.json 并创建锁文件。`一般的使用用法有：


* composer install  (install 命令从当前目录读取 composer.json 文件，处理了依赖关系，并把其安装到 vendor 目录下。)

* composer install XXXX  (这是单独安装某些包的时候使用)

* composer update (为了获取依赖的最新版本，并且升级 composer.lock 文件，)

* composer update XXX （类似）

```
--prefer-source: 下载包的方式有两种： source 和 dist。对于稳定版本 composer 将默认使用 dist 方式。而 source 表示版本控制源 。如果 --prefer-source 是被启用的，composer 将从 source 安装（如果有的话）。如果想要使用一个 bugfix 到你的项目，这是非常有用的。并且可以直接从本地的版本库直接获取依赖关系。
--prefer-dist: 与 --prefer-source 相反，composer 将尽可能的从 dist 获取，这将大幅度的加快在 build servers 上的安装。这也是一个回避 git 问题的途径，如果你不清楚如何正确的设置。
--dry-run: 如果你只是想演示而并非实际安装一个包，你可以运行 --dry-run 命令，它将模拟安装并显示将会发生什么。
--dev: 安装 require-dev 字段中列出的包（这是一个默认值）。
--no-dev: 跳过 require-dev 字段中列出的包。
--no-scripts: 跳过 composer.json 文件中定义的脚本。
--no-plugins: 关闭 plugins。
--no-progress: 移除进度信息，这可以避免一些不处理换行的终端或脚本出现混乱的显示。
--optimize-autoloader (-o): 转换 PSR-0/4 autoloading 到 classmap 可以获得更快的加载支持。特别是在生产环境下建议这么做，但由于运行需要一些时间，因此并没有作为默认值。

```


* composer require（require 命令增加新的依赖包到当前目录的 composer.json 文件中。但并不即可更新）

* composer dump-autoload（某些情况下你需要更新 autoloader，例如在你的包中加入了一个新的类。）


## 自动加载

composer的自动加载会生产这个文件`vendor/autoload.php`，然后调用这个文件就能够获得文件里面的类的自动加载

自动加载只支持 PSR-4和 PSR-0两种命名方式

```json
Under the psr-4 key you define a mapping from namespaces to paths, relative to the package root. 

{
    "autoload": {
        "psr-4": {
            "Monolog\\": "src/",  //这里写法其实差不多，但是展现的意义并不相同，psr4会设定一个命名空间作为包的根目录，举例这行的意思是src/目录映射成为Monolog\\根目录，那么调用这个包的时候写Monolog\Bar\Baz,其实自动加载就会去这里src/Bar/Baz.php找类文件，然后加载
            "Vendor\\Namespace\\": ""
        }
    }
}

在 psr-0 key 下你定义了一个命名空间到实际路径的映射（相对于包的根目录）
{
    "autoload": {
        "psr-0": {
            "Monolog\\": "src/",  //这里的意思是src/目录映射为Monolog\\，如果要调用Monolog\Bar\Baz，那么自动加载就会去src/Monolog/Bar/Baz.php，然后加载
            "Vendor\\Namespace\\": "src/",
            "Vendor_Namespace_": "src/"
        }
    }
}
```

laravel的自动加载会多了一些东西

```php
// vendor/autoload.php

<?php

// autoload.php @generated by Composer

require_once __DIR__ . '/composer' . '/autoload_real.php';  //会再次加载autoload_real.php这个文件，然后获取getLoader，不过总的过程是一样的。

return ComposerAutoloaderInitf1f9a2cafe15aa5cd52ec13394a5f5fb::getLoader();

```

引用参考：


* [http://docs.phpcomposer.com/00-intro.html][0]

* [https://getcomposer.org/doc/00-intro.md][1]


[0]: http://docs.phpcomposer.com/00-intro.html
[1]: https://getcomposer.org/doc/00-intro.md