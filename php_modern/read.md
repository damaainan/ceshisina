# [现在写 PHP，你应该知道这些][0]

2015-10-21 分类：[WEB开发][1]、[编程开发][2]、[首页精华][3][3人评论][4] 来源：[Scholer's Blog][5]

 分享到： 更多7

首先你应该是在用 PHP 5.3 以上的版本，如果 PHP 版本在这之下，是时候该升级了。我建议如果有条件，最好使用最新的版本。

你应该看过 [PHP The Right Way][6]，这篇文章包含了很多内容，而且还能再扩展开。大部分的名词和概念你都需要了解。

![现在写 PHP，你应该知道这些][7]

## 1. PSR

> The idea behind the group is for project representatives to talk about the commonalities between our projects and find ways we can work together.

在之前的文章中以及跟同事交流的过程中我多次提到过 PSR（PHP Standard Recommendation）。很多人以为 PSR 只是做一些规范代码风格等无关痛痒的事情，但其实远不止此。

PSR 的一系列标准文档由 [php-fig][8] (PHP Framework Interop Group)起草和投票决议，投票成员中有一些主流框架和扩展的作者，包括 Laravel、Symfony、Yii等等。

按照其官网的说法，这个组织的目的并不是告诉你你应该怎么做，只是一些主流的框架之间相互协商和约定。但是我相信这些框架和扩展中总会有你用到的。

PSR 目前通过的共有 6 份文档：

* 0：自动加载（主要是针对 PHP 5.3 以前没有命名空间的版本）
* 1：编码规范
* 2：编码风格推荐
* 3：Log 结果
* 4：自动加载更细（在出现命名空间后有很大的改变）
* 7：HTTP 消息接口

目前在起草（Draft）中的还有 PSR-5(PHPDoc Standard)、PSR-6(Cache)等。5 和 6 没有出现在以上的列表中，是因为还没有投票通过。

我相信随着标准的不断更新，你会发现研究这些约定对你也是很有裨益的，虽然未必什么都要遵守。

> Nobody in the group wants to tell you, as a programmer, how to build your application.

## 2. Composer

> Composer is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.

composer 和 Pear、Pecl 都不同，它不仅仅是用于安装扩展，更重要的是定义了一种现代 PHP 框架的实现和扩展管理的方法。类似 node.js 的 npm、Python 的 pip 但又比以上做的更多。

composer 的核心是实现扩展的标准安装和类的自动加载。通过 [packagist.org][9] 这个平台，无数的扩展组件可以被很方便的引入，目前比较知名的 PHP 扩展都可以通过 composer 安装了。而调用仅仅只需要加载一个 autoload.php 的文件即可。

composer 是通过 spl_autoload_register 方法注册一个自动加载方法实现扩展类和文件的加载的，当然这中间 composer 也做了一个优化。

我们都知道 PHP 引入文件要通过 include 和 require 实现，这其实写起来并不好看。 PHP 5.3 提供了命名空间，这本来和文件引入也不相干。但是 composer 实现了 PSR-4（在老版本的 PHP 上是 PSR-0），使用use 时通过调用 spl_autoload_register 实现的方法在调用时加载所需要的类，在写法上类似 Python 的 import，既美观也起到了按需加载、延迟加载的作用。

## 3. php-cs-fixer

> The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents.

这个工具的作用是按照 PSR-1 和 PSR-2 的规范格式化你的代码，还有一些可选的编码风格是 Symfony 的规范。

这个其实本来并没有那么值得一说，只是最近在几个开源框架中都看到了 .php_cs 的文件，一时好奇，深究下去才发现了这个项目。

项目地址：[https://github.com/FriendsOfPHP/PHP-CS-Fixer][10]

具体的使用和配置方法在其项目主页上都有介绍。这个组织的名字也很有趣：[FriendsOfPHP][11]。主要的成员大概是来自 Symfony 项目中。

可能有人觉得纠结代码风格的问题其实没有特别大的必要。要说好处我也说不上来，如果你觉得编程不仅仅是一份工作，那这就跟你收拾房间一样，邋遢的房间不影响你吃饭睡觉，但干净的看起来更舒服。如果要和别人合作，那这件事情就更重要了。

## 4. PsySH

> A runtime developer console, interactive debugger and REPL for PHP.

[PsySH][12] 类似 Python 的 IDLE 的一个 PHP 的交互运行环境。这个是我在 Laravel 中发现的，Laravel 5 的artisan tinker 的功能是通过它来实现的。Laravel 4 中用的是另外一个项目：[boris][13]。

这个主要是在平时测试一些 php 的简单的函数和特性的时候可以方便使用。遇到一些不确定的事情、比如empty 的使用等，可以用它来做些测试。

## 5. 一些框架和组件

### 框架

我比较喜欢的是 Laravel，目前公司在用的是 Yii2，我关注的有 Symfony 以及 Phalcon （C语言实现）。用什么不用什么，主要是喜好，有时候也由不得自己选择，但研究一下，多一分了解也未尝不可。

提到 Laravel 很多人都会立马想到 Ruby on Rails。我想模仿或者抄袭这都不是主要的目的，主要的目的是提供给开发者一个更好的工具。Laravel 好在它有一个不一样的路由控制（不带 Action 后缀或前缀的），有一个好用的 ORM (Eloquent)，好用的模板引擎 (Blade) 亦或有一个颜值比较高的文档（社区看到的话）等等。

强大有时候也会被人诟病庞大，但这在于你需要了解自己项目的中长期规划，项目现在的大小以及未来的大小及承载。

Larval 的核心实现是一个容器（Container）以及 PHP 的反射类（ReflectionClass）（Yii 2 也是一样）。要理解这些，多看文章和文档的同时，也可以看看源码。

Symfony 2 提供了很多组件。[http-kernel][14] 和 [http-foundation][15] 在 Laravel 中也有被继承过来直接使用。它是值得了解和学习的。

CodeIgniter 是一个小巧而强大的框架。虽然 CI 并没有使用 Composer 组件的方式进行开发，但 3.0 以后的版本也加入了 Composer 的支持（这无非就是多一个 vendor 的目录，引入 autoload.php）的文件。

### ORM

ORM 亦或 Active Record 我觉得还是需要的。也许有人认为 PHP 就是一个模板引擎、就应该手写 SQL 。不要被这些话所困扰。

CodeIgniter 中 Active Record 的实现方式很轻巧，但对于 CI 本身的体量来说，已经是很好用的了。

Laravel 实现的 Eloquent 我是很喜欢的，也可以集成到别的项目中去。Symfony 2 使用的是 [Doctrine][16] ,这个项目也值得关注。Yii 2 也有自己的一套实现方式。

### 模板引擎

模板引擎需要做三件事情：

1. 变量值的输出（echo）,
1. 条件判断和循环（if … else、for、foreach、while）
1. 引入或继承自其他文件

Laravel 实现的 Blade 是一个比较轻量好用的模板引擎。不过目前并不是很好能够引入到其他框架中。十一的时候闲来无事试图将其引入到 Yii 2 中，现在还只是简单的实现，我希望后面能将 Blade 的解析部分单独抽取出来做一个轻量的实现。在 Github 上搜一下发现也有人在做同样的事情。

Yii 2 似乎更推荐就用原生的 PHP 去写，不过也提供了支持 Smarty 和 Twig 的扩展。Symfony 2 则采用了 Twig。[Twig][17] 和 Symfony 以及上文提到的 php-cs-fixer 都是 SensioLabs 的作品。

Smarty 是一个古老而顽强的模板引擎。说实话我并不是太喜欢，其语法过于复杂，变量赋值这些事情都有自己的一套做法。现在的版本中更是使用 Lexer 的方式来解析文件，感觉像是用 PHP 实现了另外一种语言。项目里面还有一些太长的正则表达式、太复杂的实现，我觉得这是一件很危险很容易出错的事情。

[0]: http://www.codeceo.com/article/php-you-must-know.html
[1]: http://www.codeceo.com/article/category/develop/web
[2]: http://www.codeceo.com/article/category/develop
[3]: http://www.codeceo.com/article/category/pick
[4]: http://www.codeceo.com/article/php-you-must-know.html#comments
[5]: http://0x1.im/blog/php/php-now-you-shoud-know.html
[6]: http://wulijun.github.io/php-the-right-way/
[7]: http://static.codeceo.com/images/2015/10/165059k9osgg7whqswwo97.png
[8]: http://www.php-fig.org/
[9]: https://packagist.org/
[10]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
[11]: https://github.com/FriendsOfPHP
[12]: http://psysh.org/
[13]: https://github.com/borisrepl/boris
[14]: https://github.com/symfony/http-kernel
[15]: https://github.com/symfony/http-foundation
[16]: http://www.doctrine-project.org/
[17]: http://twig.sensiolabs.org/