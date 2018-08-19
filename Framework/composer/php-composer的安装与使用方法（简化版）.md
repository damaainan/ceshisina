## php-composer的安装与使用方法（简化版）

来源：[https://segmentfault.com/a/1190000012020479](https://segmentfault.com/a/1190000012020479)


## 1.简介

Composer 是 PHP 的一个依赖管理工具。它允许你申明项目所依赖的代码库，它会在你的项目中为你安装他们。
[《Composer 中文网》][0]
## 2.系统要求

运行 Composer 需要 PHP 5.3.2+ 以上版本。
Composer 是多平台的，它可以同时在 Windows 、 Linux 以及 OSX 平台上运行。
## 3.安装(ubuntu)

```
curl -sS https://getcomposer.org/installer | php mv composer.phar
mv composer.phar /usr/local/bin/composer

```

注：如果没有安装curl，可以通过以下命令安装
```
apt-get update
apt-get install curl

```

当你的 Composer 安装完毕之后，你可以实用下列命令查看是否安装成功

```
composer -v

```

注 如果上述方法由于某些原因失败了，你还可以通过 php 下载安装器：
```
php -r "readfile('https://getcomposer.org/installer');" | php

```

这将检查一些 PHP 的设置，然后下载 c`omposer.phar`到你的工作目录中。这是 Composer 的二进制文件。这是一个 PHAR 包（PHP 的归档），这是 PHP 的归档格式可以帮助用户在命令行中执行一些操作。

你可以通过`--install-dir`选项指定 Composer 的安装目录（它可以是一个绝对或相对路径）
## 4.使用

要开始在你的项目中使用 Composer，你只需要一个`composer.json`文件。该文件包含了项目的依赖和其它的一些元数据。

首先创建一个`composer.json`文件，写入相应的包名和版本号，如

```json
{    
    "require": {
        "monolog/monolog": "1.13.*"
    }
}

```

这是后就写入了一个依赖包，之后安装依赖包。获取定义的依赖到你的本地项目，之后在你的项目目录中（即`composer.json`所在目录）使用 Composer 运行`install`命令。

```
composer install

```

当然，如果是在`Windows`系统中，也可以通过调用`composer.phar`包来进行依赖包的安装。

```
php composer.phar install

```

执行`composer install`，就进入自动安装，安装完成后会生成一个`composer.lock`文件，里面是特定的版本号名，需要这个文件和`composer.json`一起提交到版本管理里去。

最后，在需要更新依赖包的时候，可以使用以下命令

```
composer update

```

如果只想更新部分依赖

```
composer update monolog/monolog

```
## 5.自动加载

对于库的自动加载信息，Composer 生成了一个`vendor/autoload.php`文件。你可以在你项目的入口文件中引入它

```php
<?php
require __DIR__ . '/vendor/autoload.php';
?>
```

这使得你可以很容易的使用第三方代码。例如：如果你的项目依赖 monolog，你就可以像这样开始使用这个类库，并且他们将被自动加载。

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$log = new Monolog\Logger('name');
$log->pushHandler(new Monolog\Handler\StreamHandler('app.log', Monolog\Logger::WARNING));

$log->addWarning('Foo');
?>

```
## 6.Packagist / Composer 中国全量镜像

由于墙的问题，所以会导致 Composer 的国外镜像经常无法正常的`install`，所以推荐使用国内的镜像，使用方式如下

有两种方式启用本镜像服务：


* 系统全局配置： 即将配置信息添加到 Composer 的全局配置文件 config.json 中。详见”方法一“
* 将配置信息添加到某个项目的 composer.json 文件中。详见”方法二“


方法一： 修改 composer 的全局配置文件
打开命令行窗口（windows用户）或控制台（Linux、Mac 用户）并执行如下命令：

```
composer config -g repo.packagist composer https://packagist.phpcomposer.com

```

方法二： 修改当前项目的`composer.json`配置文件：

打开命令行窗口（windows用户）或控制台（Linux、Mac 用户），进入你的项目的根目录（也就是`composer.json`文件所在目录），执行如下命令：

```
composer config repo.packagist composer https://packagist.phpcomposer.com

```

上述命令将会在当前项目中的`composer.json`文件的末尾自动添加镜像的配置信息（你也可以自己手工添加）：

```json
"repositories": {
    "packagist": {
        "type": "composer",
        "url": "https://packagist.phpcomposer.com"
    }
}

```
## 7.使用 Composer 中的 autoload 实现自动加载命名空间

Composer 除了可以帮你安装所需要的依赖包以外，还可以实现自动加载命名空间的功能，当我们自己编写的函数库与类库需要自动加载时，我们就可以通过`composer.json`来实现。它类似于 php 中的`spl_autoload_register()`， 其实如果你去查看 Composer 中的源代码，你会看到它的自动加载功能也是用了`spl_autoload_register()`这个函数。[《具体可看此文章详细介绍》][1]

我们在`composer.json`里添加如下代码：

```json
{
    "autoload": {
        "psr-4": {
            "Test\\": "test/",
            "Testtwo\\": "testtwo/"
        }
    }
}

```

这个配置文件中有一个 autoload 段,其中有个 [《PSR-4》][2]，psr-4 是一个基于 psr-4 规则的类库自动加载对应关系，只要在其后的对象中，以 ”命名空间“: “路径” 的方式写入自己的类库信息修改完成后，之后，在执行下列命令，即可完成自动加载。

```
composer dumpautoload

```

注：`"psr-4": {"Test\\": "test/"}`中的 "test/" 路径为相对于`composer.json`的路径
这个时候，你就可以调用你自己编写的函数库或者类库了

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$testClass = new \Test\Testclass();
?>

```

注：本文内容参考了[《Composer 中文网》][0]，后续还会更新 Composer 其它的实用功能

[0]: http://www.phpcomposer.com/
[1]: http://blog.csdn.net/u012580566/article/details/53515938
[2]: https://segmentfault.com/a/1190000010040678
[3]: http://www.phpcomposer.com/