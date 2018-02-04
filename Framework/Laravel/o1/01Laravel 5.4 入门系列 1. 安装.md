## Laravel 5.4 入门系列 1. 安装

## Composer 的安装与使用

### Composer 是什么

Composer 是 PHP 的一个依赖管理工具。它**以项目为单位**进行管理，你只需要声明项目所依赖的代码库，Composer 会自动帮你安装这些代码库。

### 安装 Composer

Mac 下的安装只需要在命令行中输入：（[其他平台安装][0]）：

    $ curl -sS https://getcomposer.org/installer | php

### 使用 Composer 安装组件

安装后，使用 require 命令增加新的依赖包，我们以 phpspec 为例：

    $ mkdir learncomposer
    $ cd learncomposer
    $ php composer.phar require phpspec/phpspec

为了便于使用，可以把 composer.phar 添加到 PATH 目录中：

    $ mv composer.phar /usr/local/bin/composer

刚才的命令就可以简化为:

    $ composer require phpspec/phpspec

### Composer 完成了哪些工作

命令执行完，Composer 都干了啥呢？首先创建了 composer.json，将依赖添加进来，composer.json，包括了项目依赖以及其他元数据：

    {
        "require": {
            "phpspec/phpspec": "^3.1"
        }
    }

其次，Composer 会搜索可用的 phpspec/phpspec 包将其安装到 vendor 目录下，而使用 phpspect 所需要的其他库也会自动被安装。装好之后，也可以在终端执行：

    $ vendor/bin/phpspec desc Markdown
    Specification for Markdown created in /Users/zen/composer/spec/MarkdownSpec.php.

## Laravel 的安装与使用

### 安装 Laravel

Laravel 可以通过 Composer 安装，create-project 命令可以从现有的包中创建一个新的项目：

    $ composer create-project laravel/laravel blog

默认会去安装最新的稳定版本，如果要指定版本，比如使用 5.1 版本，可以这样：

    $ composer create-project laravel/laravel=5.1.* blog

为了方便使用，我们可以全局执行 Laravel：

    composer global require "laravel/installer"

查看是否安装成功:

    $ laravel
    Laravel Installer version 1.3.5

现在，我们就可以直接使用下面的命令创建网站了:

    $ laravel new blog
    $ cd blog
    $ php artisan -V
    Laravel Framework 5.4.17

如果要使用最新的「开发」版本，可以使用:

    $ laravel new blog --dev

### 运行 Laravel 项目

安装成功之后，只需要指定项目的 public 为根目录即可运行网站：

    $ cd blog
    $ php -S localhost:8000 -t public/

这里使用的是 PHP 提供的内置服务器，也可以用 Laravel 提供的更为简单的命令行：

    $ cd blog
    $ php artisan serve

打开浏览器，输入 localhost:8000，即可看到网站首页。

[0]: http://docs.phpcomposer.com/00-intro.html