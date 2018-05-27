## 白话composer的简单使用

来源：[https://juejin.im/post/5af3bf6851882567147d64c5](https://juejin.im/post/5af3bf6851882567147d64c5)

时间 2018-05-10 11:46:49

 
composer是php的依赖管理工具，是非常有用的，phper的必备技能。 但是可能因为出现的比较晚，使用的人还是是太少，网上居然找不到像样的入门文章，要么复制黏贴，要么直接列一堆命令，官方文档比较详细了，但是页面和内容组织都比较一般，所以我来写一个。
 
#### 1.简介
 
#### 什么是composer？
 
在我们的项目中，可能用到别人的包，以前我们回去下载下来放到我们的项目中，在代码中再require，其实这是比较low的，比较麻烦 composer就是自动管理依赖的工具，你只要在你的项目中声明依赖哪些包，composer就会自动去下载，就这样。 另外，composer还有一个自动生成autoload文件的便利功能。
 
#### 安装
 
win的话下载一个安装文件，安装，就在命令行用了，全局的。 其他平台看官方文档，文章最后有官网链接。
 
安装的过程中我遇到了错误 报错 Failed to decode zlib stream 解决办法是在php.ini配置文件中将 zlib.output_compression=Off 改成 On
 
#### 学习使用composer主要有这么三件事：
 
 
* 1.三个概念，包是什么？comnposer是什么？packgist是什么？ 
* 2.两个文件，肯定要有文件记录依赖信息吧，然后大家要统一一个格式，每个字段代表什么要看一下 
* 3.几个操作，就是命令，依赖的增删安装等 浏览一遍这几个点我觉得composer就掌握了，本来也不是复杂的东西
 
| 命令 | 备注 | 
|-|-|
| composer init | 初始化项目 引导生成composer.json | 
| composer search | 搜索包 | 
| composer require | 安装新的依赖包 | 
| composer update [package name] | 更新依赖 | 
 
 
#### 2.依赖管理怎么用
 
#### 2.1 三个概念
 
包 包就是一个文件夹，对项目的意义就是一个可以引用的组件，比如monolog
 
composer composer其实就是一个phar文件，当做一个工具来使用，全局安装了的话就composer install这样用，没有全局安装就 php /path/to/composer.phar install这样用
 
packgist 我们需要一个存储包的中央仓库，这样只要告诉composer一个包的名字，composer就会从这个中央仓库去下载代码，https://packagist.org是 Composer 的主官方资源库。 可以访问 packagist website (https://packagist.org/) (packagist.org)浏览和搜索资源包。
 
#### 2.2 两个文件
 
如果你在你的项目中使用composer，会增加两个文件
 
 
* composer.json 
* composer.lock .json记录的最重要的信息是项目依赖的包 但是，假设，你实际安装的某个依赖变化了，比如升级了，这就导致json文件与实际不符。 .lock记录的是实际安装的依赖的信息，主要是版本。 每次升级项目的依赖，lock文件会同步更新。 
 
 
所以提交你的项目的时候 这两个文件都要提交
 
当别人下载你的项目，composer会先查看有没有lock文件，如果有，就按照lock文件下载指定的依赖，这样别人跟你的项目的依赖的所有版本都会一致
 
#### 2.3 几个操作
 
围绕着依赖管理，自然会有这么几个操作
 
 
* 创建你的依赖记录文件 也就是composer.json 
* 安装新依赖，并更新记录文件 
* 删除依赖 或者升级依赖 ，更新依赖文件 
* 发布你的包（依赖是被ignore的） 
* 下载包 并安装依赖（根据依赖记录 安装依赖） 下面挨个说 
 
 
#### 创建composer.json
 
我们可以手动创建json文件，也可以自动创建，有自动当然首选自动 在我们的项目根目录运行命令行`composer init`就自动创建了
 
``` 
PS D:\code\test3> composer init


  Welcome to the Composer config generator



This command will guide you through creating your composer.json config.

Package name (<vendor>/<name>) [kelle/test3]: my/ctest
Description []:
Author [, n to skip]: dragonfly429 <dragonfly429@foxmail.com>
Minimum Stability []:
Package Type (e.g. library, project, metapackage, composer-plugin) []:
License []:

Define your dependencies.

Would you like to define your dependencies (require) interactively [yes]?
Search for a package:
Would you like to define your dev dependencies (require-dev) interactively [yes]?
Search for a package:

{
    "name": "my/ctest",
    "authors": [
        {
            "name": "dragonfly429",
            "email": "dragonfly429@foxmail.com"
        }
    ],
    "require": {}
}

Do you confirm generation [yes]?
```
 
其中这个json文件的字段和格式需要了解一下
 
``` 
--name: 包的名称。
--description: 包的描述。
--author: 包的作者。
--homepage: 包的主页。
--require: 需要依赖的其它包，必须要有一个版本约束。并且应该遵循  foo/bar:1.0.0  这样的格式。
--require-dev: 开发版的依赖包，内容格式与 --require 相同。
--stability (-s):  minimum-stability  字段的值。
```
 
name author require 是必填的 -dev代表开发时才用到的 这个跟npm的语法都一样的(save 不用写)
 
#### 安装新依赖
 
插一个搜索命令`composer search`

``` 
PS D:\code\test3> composer search monolog
monolog/monolog Sends your logs to files, sockets, inboxes, databases and various web services
symfony/monolog-bundle Symfony MonologBundle
symfony/monolog-bridge Symfony Monolog Bridge
easycorp/easy-log-handler A handler for Monolog that optimizes log messages to be processed by humans instead of software. Improve your productivity with logs that are easy to understand.
wazaari/monolog-mysql A handler for Monolog that sends messages to MySQL
theorchard/monolog-cascade Monolog extension to configure multiple loggers in the blink of an eye and access them from anywhere
logentries/logentries-monolog-handler A handler for Monolog that sends messages to Logentries.com.
flynsarmy/slim-monolog Monolog logging support Slim Framework
bramus/monolog-colored-line-formatter Colored Line Formatter for Monolog
tylercd100/lern LERN (Laravel Exception Recorder and Notifier) is a Laravel 5 package that will record exceptions into a database and will notify you via Email, Pushover or Slack.
maxbanton/cwh AWS CloudWatch Handler for Monolog library
rahimi/monolog-telegram A handler for Monolog that sends messages to Telegram Channels
markhilton/monolog-mysql Laravel 5 MySQL driver for Monolog
lexik/monolog-browser-bundle This Symfony2 bundle provides a Doctrine DBAL handler for Monolog and a web UI to display log entries
kdyby/monolog Integration of Monolog into Nette Framework
```
 
命令`composer require`

``` 
PS D:\code\test3> composer require monolog/monolog
Using version ^1.23 for monolog/monolog
./composer.json has been updated
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 2 installs, 0 updates, 0 removals
  - Installing psr/log (1.0.2): Downloading (100%)
  - Installing monolog/monolog (1.23.0): Downloading (100%)
monolog/monolog suggests installing aws/aws-sdk-php (Allow sending log messages to AWS services like DynamoDB)
monolog/monolog suggests installing doctrine/couchdb (Allow sending log messages to a CouchDB server)
monolog/monolog suggests installing ext-amqp (Allow sending log messages to an AMQP server (1.0+ required))
monolog/monolog suggests installing ext-mongo (Allow sending log messages to a MongoDB server)
monolog/monolog suggests installing graylog2/gelf-php (Allow sending log messages to a GrayLog2 server)
monolog/monolog suggests installing mongodb/mongodb (Allow sending log messages to a MongoDB server via PHP Driver)
monolog/monolog suggests installing php-amqplib/php-amqplib (Allow sending log messages to an AMQP server using php-amqplib)
monolog/monolog suggests installing php-console/php-console (Allow sending log messages to Google Chrome)
monolog/monolog suggests installing rollbar/rollbar (Allow sending log messages to Rollbar)
monolog/monolog suggests installing ruflin/elastica (Allow sending log messages to an Elastic Search server)
monolog/monolog suggests installing sentry/sentry (Allow sending log messages to a Sentry server)
Writing lock file
Generating autoload files
```
 
 ![][0]
 
完成后会多一个vendor文件夹，包含有monolog和autoload
 
额 然后 喜闻乐见的下载很慢 添加国内镜像是基本操作
 
``` 
引用知乎回答
Composer 下载扩展包时候需要跟这两个网站通信：Packagist 官网  —— 获取扩展包信息，下载代码包；GitHub ——
下载代码包。下载慢的原因是这两个网站都为国外的，一般情况下访问速度很慢，有时候甚至无法访问。解决方案是将构建一台能高速访问的服务器，并将所有的扩展包使用国内的 CDN 进行加速。我们社区维护了一个加速镜像，请见：Laravel China 社区维护的国内全量镜像 ——
https://laravel-china.org/composer使用方法：
选项一、全局配置（推荐）

`$ composer config -g repo.packagist composer https://packagist.laravel-china.org`

选项二、单独使用如果仅限当前工程使用镜像，去掉 -g 即可，如下：

`$ composer config repo.packagist composer https://packagist.laravel-china.org`

取消镜像

`composer config -g --unset repos.packagist`

作者：Summer
链接：https://www.zhihu.com/question/24997679/answer/30703365
来源：知乎
著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。
```
 
#### 更新依赖
 `remove和 update`略
 
#### 发布包
 
略
 
#### 下载包 并安装依赖
 
·`install`就是下载一个包的时候 这个命令是让composer读取composer.json，安装里面列出的依赖
 
#### 3.autoload怎么用
 
只需要将下面这行代码添加到你项目的引导文件中：
 
`require 'vendor/autoload.php';`
 
``` php
require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler('./test.log', Logger::WARNING));

// add records to the log
$log->addWarning('Foo');
$log->addError('Bar');
```
 
运行 多了一个文件 成功
 
 ![][1]
 


[0]: ../img/MVJ7F3v.png 
[1]: ../img/vEbm6vF.png 