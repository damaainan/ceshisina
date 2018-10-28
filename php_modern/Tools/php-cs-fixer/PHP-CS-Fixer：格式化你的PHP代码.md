## PHP-CS-Fixer：格式化你的PHP代码

时间：2016-04-22 17:41:26

来源：[https://yq.aliyun.com/articles/33522](https://yq.aliyun.com/articles/33522)

摘要：
            
简介
良好的代码规范可以提高代码可读性，团队沟通维护成本。最推荐大家遵守的是 php-fig（PHP Framework Interop Group） 组织定义的 PSR-1 、 PSR-2 两个。不了解的同学可以先通过链接点击过去阅读下。
## 简介

良好的代码规范可以提高代码可读性，团队沟通维护成本。最推荐大家遵守的是 [php-fig][0]（PHP Framework Interop Group） 组织定义的 [PSR-1][1] 、 [PSR-2][2] 两个。不了解的同学可以先通过链接点击过去阅读下。

这个工具的作用就是按照`PSR-1`和`PSR-2`的规范格式化你的代码。
## 安装

PHP需求：PHP最小版本5.3.6。
 **`本地安装`** 

安装很简单，下载php-cs-fixer.phar文件就行了。官方地址是：
[http://get.sensiolabs.org/php-cs-fixer.phar][3]

国内的朋友如果下载很慢，可以使用百度云：

链接: [http://pan.baidu.com/s/1qWUTd5y][4] 密码: yith
 **`Composer方式安装`** 

如果你还不了解[Composer][5]，请点击链接查看。

新建composer.json

```json
{
    "require" :{
        "fabpot/php-cs-fixer":"*"
    },"config": {
        "secure-http": false
    }
}
```

运行：

```
composer update
```

稍等片刻，下载完成：目录生成了vendor文件夹。

设置全局：

```
export PATH="$PATH:$HOME/.composer/vendor/bin"
```

注意，composer安装的与本地方式安装后调用的执行文件是不一样的。本地安装执行的是`php-cs-fixer.phar`；composer安装的执行的是`php vendor\fabpot\php-cs-fixer\php-cs-fixer`。
 **`homebrew安装`** 

```
$ brew install homebrew/php/php-cs-fixer
```
## 如何使用

命令行运行：

```
php-cs-fixer
```

会给出很多帮助提示。

使用 fix 指令修复文件夹或文件的代码风格

```
php php-cs-fixer.phar fix /path/to/dir
php php-cs-fixer.phar fix /path/to/file
```

选项：

```
--format 输出文件格式，支持txt、xml
--verbose 
--level 应用哪种PSR类型。支持psr0、psr1、psr2。默认是psr2
--dry-run 显示需要修复但是没有修复的代码
```

```
php php-cs-fixer.phar fix /path/to/project --level=psr0
php php-cs-fixer.phar fix /path/to/project --level=psr1
php php-cs-fixer.phar fix /path/to/project --level=psr2
php php-cs-fixer.phar fix /path/to/project --level=symfony
```

示例：

```
$ php php-cs-fixer.phar fix test.php

   1) test.php
Fixed all files in 0.290 seconds, 4.250 MB memory used
```

有一些要注意的地方是，php-cs-fixer 因为是在不破坏相容性的前提下修正的，所以有些`方法命名`的规则就无法修。不过比起手动修正，可以省下不少时间。

更多使用方式参见 [Usage][6]。
## 使用.php_cs文件

在一些开源框架中都看到了`.php_cs`文件。这个文件便是php-cs-fixer的格式化配置。

官方是这么描述的：

```
Instead of using command line options to customize the fixer, you can save the configuration in a .php_cs file in the root directory of your project. 
```

如何使用`.php_cs`？

```
$ php php-cs-fixer fix --config-file .php_cs  test.php

Loaded config from ".php_cs"
   1) test.php
Fixed all files in 0.242 seconds, 4.250 MB memory used
```

使用`--config-file`加载`.php_cs`文件。文件内容详情见文末。
## 升级

```
php php-cs-fixer.phar self-update
```

或者

```
$ sudo php-cs-fixer self-update
```

composer方式：

```
$ ./composer.phar global update fabpot/php-cs-fixer
```

brew方式：

```
$ brew upgrade php-cs-fixer
```

## StyleCI介绍

当我们使用 PHP-CS-Fixer 让我们现有的代码规范化之后，我们怎么确保以后开发的代码，以及别人 pr 的代码都能正确的符合代码风格规范呢？

StyleCI 是一个 Laravel5 项目，功能实现也是由 PHP-CS-Fixer 驱动。

它可以自己分析你项目的 pull request，并且在你 merge 前显示出分析的结果。

该工具没有具体使用过，下面是它的官网，感兴趣的同学可以看看。

官方网站：[https://styleci.io/][7]

## 相关资源

PHP Coding Standards Fixer--FriendsOfPHP
[https://github.com/FriendsOfPHP/PHP-CS-Fixer][8]

使用 PHP-CS-Fixer 自动规范化你的 PHP 代码_PHPHub - PHP & Laravel的中文社区
[https://phphub.org/topics/547][9]

现在写 PHP，你应该知道这些 - Scholer 的 PHP 之路 - SegmentFault
[https://segmentfault.com/a/1190000003844380][10]

Basic php-cs-fixer rules
[https://github.com/XiaoLer/php-develop-standards/blob/master/php-cs-fixer-rules.md][11]

## .php_cs内容参考

```php
<?php

$header = <<<EOF
This file is part of the PHP CS utility.

(c) Fabien Potencier <fabien@symfony.com>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    // use default SYMFONY_LEVEL and extra fixers:
    ->fixers(array(
        'header_comment',
        'long_array_syntax',
        'ordered_use',
        'php_unit_construct',
        'php_unit_strict',
        'strict',
        'strict_param',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('Symfony/CS/Tests/Fixtures')
            ->in(__DIR__)
    )
;

```


[0]: http://www.php-fig.org/
[1]: https://github.com/PizzaLiu/PHP-FIG/blob/master/PSR-1-basic-coding-standard-cn.md
[2]: https://github.com/PizzaLiu/PHP-FIG/blob/master/PSR-2-coding-style-guide-cn.md
[3]: http://get.sensiolabs.org/php-cs-fixer.phar
[4]: http://pan.baidu.com/s/1qWUTd5y
[5]: http://www.cnblogs.com/52fhy/p/5246013.html
[6]: https://github.com/FriendsOfPHP/PHP-CS-Fixer#usage
[7]: https://styleci.io/
[8]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
[9]: https://phphub.org/topics/547
[10]: https://segmentfault.com/a/1190000003844380
[11]: https://github.com/XiaoLer/php-develop-standards/blob/master/php-cs-fixer-rules.md