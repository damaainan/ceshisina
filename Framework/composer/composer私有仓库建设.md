## composer私有仓库建设

来源：[https://yuerblog.cc/2018/05/07/composer-private-repository/](https://yuerblog.cc/2018/05/07/composer-private-repository/)

时间 2018-05-07 16:06:35


正在公司推动composer私有仓库，整理了一些思路如下。


## 背景

* 新建项目没有规范，随手拷贝已有项目已成”乱象”
* 已有项目对框架侵入严重，每个项目的框架代码都不相同
* 基于复制粘贴复制公共库，代码不一致，无法统一升级更新，效率低下

## 目标

* 统一项目脚手架：命令一键创建新项目
* 统一基础公共库：命令一键安装与更新依赖

## 路线图

* composer satis + gitlab 私有仓库搭建
* composer工具培训
* 脚手架接入composer仓库
* 图片处理、link处理等常见痛点封装为公共库，接入composer仓库

## 架构

基于composer+gitlab搭建整套架构，其中composer类似于java maven、python pip、nodejs npm，是一款可靠的php包管理工具。

``` 
composer cli <--- packagist.baidu.com <--- gitlab-team.baidu.com


```

* 客户端：composer完成依赖配置、安装、更新
* 私有仓库：composer satis搭建，仅维护私有包的索引信息
* 代码托管：gitlab，存储库代码，维护分支与tag

## composer安装与使用


### 安装

``` 
curl -sS https://getcomposer.org/installer | php
 
mv composer.phar /usr/local/bin/composer


```

安装成功后，可以直接使用composer命令。

使用Windows的同学可以通过Phpstorm直接安装与管理composer。


### 创建项目

执行composer init
来初始化一个空白项目，也称为”root项目”。

``` 
composer init

  Welcome to the Composer config generator

This command will guide you through creating your composer.json config.
 
Package name (<vendor>/<name>) [owenliang/myproject]:


```

根据提示填入各种信息即可，最终会生成composer.json配置文件。

生成后的composer.json文件如下：

```json
{
    "name": "owenliang/myproject",
    "authors": [
        {
            "name": "owenliang",
            "email": "120848369@qq.com"
        }
    ],
    "require": {}
}


```


### 安装外部依赖

作为root项目，通常只关心自己依赖哪些包、包的版本是什么。

默认composer只会在    [官方仓库][0]
中寻找你要安装的包，例如安装一个elasticsearch的客户端：

``` 
composer require 'ongr/elasticsearch-dsl:^5.0'


```

包的命名遵循：”组织名/包名”，是全局唯一的，冒号之后是版本控制，具体规则后面会提供学习链接。

安装这样的第三方包不需要做额外的配置，composer工具总是会到官方仓库中寻找你要安装的包。

现在composer.json如下：

```json
{
    "name": "owenliang/myproject",
    "authors": [
        {
            "name": "owenliang",
            "email": "120848369@qq.com"
        }
    ],
    "require": {
        "ongr/elasticsearch-dsl": "^5.0"
    }
}


```


### 安装内部依赖

安装私有仓库的包略有不同，首先需要配置composer私有库的地址，编辑上述composer.json：

```json
{
    "name": "owenliang/myproject",
    "authors": [
        {
            "name": "owenliang",
            "email": "120848369@qq.com"
        }
    ],
    "require": {
        "ongr/elasticsearch-dsl": "^5.0"
    },
    "repositories": [{
        "type": "composer",
        "url": "http://packagist.baidu.com"
    }],
    "config": {
        "secure-http": false
    }
}


```

现在尝试安装一个私有的包：

``` 
composer require 'baidu/common:^1.0'


```

composer工具会到    [私有仓库][1]
查找这个包，如果没找到还会去默认的官方仓库查找。

未来可以移除secure-http选项，因为最终私有仓库域名会改为https协议。


### 常用命令

* 初始化composer.json：

``` 
composer init


```

* 安装依赖

``` 
composer require "组织名/库名:版本控制"


```

* 删除依赖

``` 
composer remove "组织名/库名"


```

* 升级依赖（先修改composer.json中的依赖配置）

``` 
composer update


```

* 清空缓存

``` 
composer clearcache


```


### 引入composer自动加载

composer安装的包文件存储在vendor目录。

为了在自己的项目中使用安装的依赖库，需要加载composer的autoload函数，这样才能访问到包中的类和方法：

```php
<?php
 
require_once __DIR__ . "/vendor/autoload.php";


```


### 调用包中的方法

```php
<?php
 
require_once __DIR__ . "/vendor/autoload.php";

use baidu\common\Author;
 
Author::name();

```

baidu\common\Author类会被自动加载到PHP中，这里调用了它的静态方法name()。


### 参考资料

* [composer入门][2]

* [composer命令行使用][3]

* [composer.json配置][4]

* [composer版本控制][5]

## composer包制作

下面介绍如何制作一个composer包，最终制作好的包既可以发布到公共仓库，也可以发布到私有仓库。

我最终希望发布包到composer私有仓库，所以包的代码也托管在私有的gitlab中才安全。


### 在gitlab创建项目

在gitlab上创建一个叫做php-common的项目。

希望发布一个包叫做”baidu/common”，因此我们依旧通过composer init
来初始化一个composer.json：

```json
{
  "name": "baidu/common",
  "description": "common utility for php team",
  "type": "library",
  "license": "MIT",
  "authors": [
    {
      "name": "owenliang",
      "email": "owenliang@baidu.com"
    }
  ],
  "minimum-stability": "stable",
  "require": {}
}


```

该包当然也可以通过require来依赖其他的包，即二级依赖。

composer包名是name指定的，与gitlab项目名无关。


### 开发库代码

composer包建议把源代码放在src目录下：

``` 
owenliangs-MacBook-Pro:php-common owenliang$ ll src/
total 8
-rw-r--r--  1 owenliang  staff  120  5  7 12:39 Author.php


```

我希望整个包下的所有class文件，均采用baidu\common的命名空间前缀。

以src/Author.php为例：

```php
<?php
 
namespace baidu\common;
 
class Author
{
    public static function name() {
        return 'baidu@2018';
    }
}


```


### 配置类自动加载

为了composer可以找到加载到baidu\common\Author这个类，需要在composer.json中定义”命名空间前缀”与”类文件路径”之间的关系。

```json
{
  "name": "baidu/common",
  "description": "common utility for php team",
  "type": "library",
  "license": "MIT",
  "authors": [
    {
      "name": "owenliang",
      "email": "owenliang@baidu.com"
    }
  ],
  "minimum-stability": "stable",
  "require": {},
  "autoload": {
    "psr-4": {
      "baidu\\common\\": "src"
    }
  }
}


```

我们遵循psr-4类加载规范，以baidu\common命名空间为前缀的class，会在src目录下查找。

当其他项目调用baidu\common\Author类时，composer会在”vendor/baidu(组织名)/common(包名)/src/”下加载Author.php文件。


### 编写测试

composer建议把测试代码放在test目录下：

``` 
owenliangs-MacBook-Pro:php-common owenliang$ ll test/
total 8
-rw-r--r--  1 owenliang  staff  167  5  7 10:25 test_Author.php


```

我编写一个很简单的调用示例：

```php
<?php
 
require_once __DIR__ . "/../vendor/autoload.php";
 
 if (\baidu\common\Author::name() == 'baidu') {
     printf("success\n");
 } else {
     printf("fail\n");
 }


```

就像其他项目一样，我们引入composer的autoload，然后就可以访问对应的类。


## satis安装

satis用于构建一个私有仓库，它通过扫描gitlab上的版本库信息建立仓库索引，并将索引提供给composer客户端获取。

代码最终依旧是客户端直接向gitlab下载的，相关的权限验证依旧由gitlab把控。


### 下载satis

``` 
composer create-project composer/satis:dev-master --keep-vcs


```


### 配置satis

在satis目录下放置一个satis.json配置文件，在其中罗列所有gitlab上希望发布的包信息：

```json
{
  "name": "Private Repository",
  "homepage": "http://packagist.baidu.com",
  "repositories": [
    {
        "type": "git",
        "url": "https://gitlab-team.baidu.com/baidu/php-common",
        "options": {
          "http-basic": {
            "gitlab-team.baidu.com": {
              "username": "owenliang",
              "password": "baidu1234567890"
            }
          }
        }
    }
  ],
  "require": {
      "baidu/common": "*"
  }
}


```

* name: 私有仓库的名字，最终会展现在UI界面上
* homepage: 私有仓库的服务地址，未来会替换为https协议
* repositories：所有要发布的包地址，因为公司的gitlab采用ldap认证，所以需要配置http-basic帐号密码。
* require: 指定索引哪些包的哪些版本，这里我指定索引baidu/common包的所有版本。

千万不要使用require-all选项，而是应该在require中罗列每个包（require-all的意思是将官方仓库的所有包索引到本地，对我们毫无意义）。


### 定时更新仓库索引

satis只是一个命令行工具，我们需要定时让satis扫描gitlab上的版本变化，更新仓库索引信息。

将下面的任务添加到crontab（非root用户）：

``` 
flock -xn /tmp/satis.lock -c '/usr/local/bin/php /data/webroot/phpsrc/satis/bin/satis build /data/webroot/phpsrc/satis/satis.json /data/webroot/phpsrc/satis/repo'


```

该命令可以防止并发执行多个satis，同时会将构建好的静态索引文件存储到repo目录。

实际上仓库就是一个索引文件package.json文件，它描述了完整的gitlab包索引信息：

``` json
{
    "packages": {
        "baidu/common": {
            "1.0.0": {
                "name": "baidu/common",
                "version": "1.0.0",
                "version_normalized": "1.0.0.0",
                "source": {
                    "type": "git",
                    "url": "https://gitlab-team.baidu.com/baidu/php-common",
                    "reference": "91266fd41e3755794f4eb16d4d5f3cde44ca7649"
                },
                "time": "2018-05-07T03:41:48+00:00",
                "type": "library",
                "autoload": {
                    "psr-4": {
                        "baidu\\common\\": "src"
                    }
                },
                "license": [
                    "MIT"
                ],
                "authors": [
                    {
                        "name": "owenliang",
                        "email": "owenliang@baidu.com"
                    }
                ],
                "description": "common utility for php team"
            },
            "1.0.1": {
                "name": "baidu/common",
                "version": "1.0.1",
                "version_normalized": "1.0.1.0",
                "source": {
                    "type": "git",
                    "url": "https://gitlab-team.baidu.com/baidu/php-common",
                    "reference": "ff8fbe09faa384ebcdc57db9d63cc8dc78a1dc39"
                },
                "time": "2018-05-07T04:39:28+00:00",
                "type": "library",
                "autoload": {
                    "psr-4": {
                        "baidu\\common\\": "src"
                    }
                },
                "license": [
                    "MIT"
                ],
                "authors": [
                    {
                        "name": "owenliang",
                        "email": "owenliang@baidu.com"
                    }
                ],
                "description": "common utility for php team"
            },
            "dev-master": {
                "name": "baidu/common",
                "version": "dev-master",
                "version_normalized": "9999999-dev",
                "source": {
                    "type": "git",
                    "url": "https://gitlab-team.baidu.com/baidu/php-common",
                    "reference": "ff8fbe09faa384ebcdc57db9d63cc8dc78a1dc39"
                },
                "time": "2018-05-07T04:39:28+00:00",
                "type": "library",
                "autoload": {
                    "psr-4": {
                        "baidu\\common\\": "src"
                    }
                },
                "license": [
                    "MIT"
                ],
                "authors": [
                    {
                        "name": "owenliang",
                        "email": "owenliang@baidu.com"
                    }
                ],
                "description": "common utility for php team"
            }
        }
    }
}


```

* dev-master：非stable的开发中master分支
* 1.0.0：stable版本
* 1.0.1：stable版本，修复了1.0.0版本中的一些问题

### 配置Web

为了让composer客户端可以访问到私有仓库的索引，以及为了有一个可视化的界面查看索引，我们需要配置nginx指向repo目录：

``` nginx
server {
    listen 80;
    server_name packagist.baidu.com;
 
    root /data/webroot/phpsrc/satis/repo;
    charset utf-8;
    access_log off;
}


```

打开浏览器访问http://packagist.baidu.com即可看到所有被索引的包与版本信息。


### 参考资料

* [satis私有仓库][6]

## 其他


### composer脚手架

我们可以将一份标准模板web框架上传到gitlab项目中，并将项目发布为一个composer包。

我们知道，composer require命令安装的包都会被存储到vendor目录下，并提供autoload自动加载。

除此之外，其实composer还提供了create-project命令，它专门用于创建模板项目。

它的工作原理是：

* git clone直接下载包代码到当前目录
* composer update
下载模板项目的依赖包
* 执行composer.json中配置的一些钩子函数，做一些自定义操作（比如创建目录）

这个过程我们可以手动操作，只是composer更方便：

``` 
composer create-project 'baidu/common' my-common --stability=stable --repository 'http://packagist.baidu.com' --no-secure-http --remove-vcs


```

上述命令将baidu/common项目下载到本地的my-common目录，需要指定仓库地址为我们的私有仓库，并且下载完成后删除.git目录。

主流的PHP开源框架均采用composer来帮助开发者初始化项目，一般思路如下：

* 框架核心模块分别独立发布在多个composer包里
* 框架脚手架发布单独的composer包，预建了controller、model等目录，并配置composer.json定义二级依赖各个独立的框架核心包
* 使用脚手架的用户可以通过composer update
一键更新所有框架核心代码，实现了业务与框架的分离

### 语义化版本控制

satis会自动识别gitlab上的分支与tag。

作为tag发布的版本均被composer视为stable稳定版本，例如1.0.0，1.0.1的tag会被Composer索引为1.0.0与1.0.1。

在普通分支中的代码是非stable（非稳定）版本，composer会在索引时在它们的分支名前增加dev-前缀，例如master分支的composer版本为dev-master。

我们总是应该依赖stable版本，而不是依赖一个开发中的dev-master版本。

另外，简单解释一下语义化版本a.b.c：

* a：主版本，一般与前一个主版本存在不兼容的功能
* b：次版本，发布的功能能够向下兼容
* c：补丁版本：修复先有功能问题

### 参考资料

* [生命期与回调][7]

最后附上一开始做的一个例子：

* 制作一个composer包：https://github.com/owenliang/first-composer-package
* 搭建私有composer仓库：https://github.com/owenliang/satis-composer-repository
* 从私有仓库安装包：https://github.com/owenliang/satis-composer-usage

[0]: https://packagist.org/
[1]: http://packagist.baidu.com
[2]: https://docs.phpcomposer.com/01-basic-usage.html
[3]: https://docs.phpcomposer.com/03-cli.html#install
[4]: https://docs.phpcomposer.com/04-schema.html
[5]: https://overtrue.me/articles/2017/08/about-composer-version-constraint.html
[6]: https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md
[7]: https://docs.phpcomposer.com/articles/scripts.html