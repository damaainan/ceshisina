## 你必须知道的 17 个  Composer 最佳实践（已更新至 22 个）

来源：https://zhuanlan.zhihu.com/p/33486366

Thu Feb 01 2018 10:17:48 GMT+0800 (CST)

转自：[你必须知道的 17 个 Composer 最佳实践（已更新至 22 个）][0]

尽管大多数PHP开发人员都知道如何使用`Composer`，但并不是所有的人都在有效的或以最好的方式来使用它。 所以我决定总结一些在我日常工作流程很重要的东西。

大多数技巧的哲学是 “稳，不冒险”，这意味着如果有更多的方法来处理某些事情，我会使用最有把握不容易出错的方法。

## Tip #1: 阅读文档

我是真心这样认为的. [文档][1] 是一个非常有用的东西并且在长远看来几个小时的阅读将会为您节省更多的时间. 你会惊讶原来`Composer`可以做这么多事情.

## Tip #2: 注意`项目`和`库`之间的区别

无论你是创建`项目`还是`库`, 了解这一点非常重要, 它们每一个都需要单独的一套做法。

一个库是一个可重用的包，你可以添加一个依赖

* 比如:`symfony / symfony`，`doctrine / orm`或者`[elasticsearch/elasticsearch][2]`.

一个项目通常是一个应用程序，依赖于几个库. 它通常是不可重用的（没有其它项目会要求它作为依赖. 典型的例子是电子商务网站，客户支持系统等)。

我将在下面的提示中区分库和项目。

## Tip #3: 使用特定的依赖关系#关于应用程序的版本

如果你正在创建一个应用程序，您应该使用最具体的版本来定义依赖项，假如你需要解析YAML文件，你应该指定这样的依赖版本“symfony / YAML”：“4.0.2”。

即使你遵循了库的依赖版本控制，在次要版本和补丁版本中也有可能中断向后兼容性。例如，如果你使用的是“symfony / Symfony”：“^ 3.1”,会有3.2的版本有可能会破坏你的应用程序测试用列。或者可能新版本有bug没有修正，那么php_codesniffer检测你的代码格式的时候会导致新问题，这在很大程度上可能会破坏一个应用的构建。

依赖关系的更新应该是深思熟虑的，而不是偶然的。其中的一个技巧更详细地讨论了它。

```
  这听起来像一个多余的，但它可以防止你的同事不小心在项目中添加一个新的库文件或更新所有依赖的时候出错，（不然的话，你们有可能会导致浪费大量的时间在审核代码上）。
```

## Tip #4: 对库依赖项使用版本范围

创建库时，应尽可能定义最大的可用版本范围。比如创建了一个库，要使用`symfony/yaml`库进行 YAML 解析，就应这样写：

```
 "symfony/yaml": "^3.0 || ^4.0"
```

这表示该库能从 Symfony 3.x 或 4.x 中任意版本中使用`symfony/yaml`。这相当重要，因为这个版本约束会传递给使用该库的应用程序。

万一有两个库的请求存在冲突，比如一个要`~3.1.0`，另一个需要`~3.2.0`，则安装会失败。

## Tip #5: 开发应用程序要提交`composer.lock`文件到 git 版本库中

创建了 `一个项目`，一定要把`composer.lock`文件提交到 git 中。 这会确保每一个人——你、你的合作伙伴、你的 CI 服务器以及你的产品服务器——所运行的应用程序拥有相同依赖的版本。

乍一看有些画蛇添足，在 Tip #3 中已经提过要使用明确的版本号的约束了啊。这并不多余，要知道你使用的依赖项的依赖项并不受这些约束绑定（如`symfony/console`还依赖`symfony/polyfill-mbstring`）。如果不提交`composer.lock`文件，就不会获取到相同版本的依赖集合。

## Tip #6: 开发库要把`composer.lock`文件添加到`.gitignore`文件中

创建 `一个库` （比如说叫`acme/my-library`）， 这就不应该把`composer.lock`文件提交到 git 库中了。该文件对使用该库的项目 It [不会有任何影响][3] 。

假设`acme/my-library`使用`monolog/monolog`作依赖项。你已经在版本库中提交了`composer.lock`，开发`acme/my-library`的每个人都可能在使用 Monolog 的老旧版本。该库开发完成后，在实际项目中使用该库，就可能存在安装的 Monolog 是一个新版本 ， 而此时就会和该库存在不兼容。可是你在之前根本就不会注意到兼容问题就因为这个`composer.lock`！

因此，最佳处理方式就是把`composer.lock`添加到`.gitignore`文件中，这样就避免了不小心提交它到版本库中引发的问题。

如果还想确保该库与它的依赖项的不同版本保持兼容性，那继续阅读下一个 Tip ！

## Tip #7: Travis CI 构建依赖项的不同版本

当前 Tip 仅适合库（对于应用程序要指明具体的版本号）。
如果你在构建开源的库，很有可能你会使用 Travis CI 来跑构建过程。

默认情况下，在`composer.json`文件约束允许的条件下，composer 安装会安装依赖的最新可能版本。这就意味着对于`^3.0 || ^4.0`这样的依赖约束，构建安装总是使用最新的 v4 版本发行包。 而 3.0 版本根本不会测试，所构建的库就可能与该版本不兼容，你的用户要哭了。

幸好，composer 为安装低版本依赖项提供了一个开关`--prefer-lowest`（应使用`--prefer-stable`，可阻止不稳定版本的安装）。

已上传的`.travis.yml`配置类似下面的格式：

```yaml
language: php

php:
  - 7.1
  - 7.2

env:
  matrix:
    - PREFER_LOWEST="--prefer-lowest --prefer-stable"
    - PREFER_LOWEST=""

before_script:
  - composer update $PREFER_LOWEST

script:
  - composer ci
```

代码详见 [my mhujer/fio-api-php library][4] 及 [the build matrix on Travis CI][5]

虽然这解决了多数的不兼容问题，不过仍然要记得，依赖项的最低和最高版本间有太多的组合。他们仍旧可能存在不兼容的情况。

## Tip #8: 按名称对 require 和 require-dev 中的包排序

按名称对`require`及`require-dev`中的包排序是非常好的实践。这在衍合一个分支时可以避免不必要的合并冲突。假如你把一个包添加到两个分支文件中的列表末尾，那每次合并都可能遇到冲突。

手动进行包排序的话会很乏味，所以最好办法就是在`composer.json`中 [配置一下][6] 即可：

```json
{
...
    "config": {
        "sort-packages": true
    },
...
}
```

以后再要`require`一个新的包，它会自动添加到一个正确位置（不会跑到尾部）。

## Tip #9: 进行版本衍合或合并时不要合并`composer.lock`如果你在`composer.json`（和`composer.lock`）中添加了一个新依赖项，并且在该分支被合并前主分支中添加另一个依赖项，此时就需要对你的分支进行衍合处理。那么`composer.lock`文件就会得到一个合并冲突。

千万别试图手动解决冲突，这是因为`composer.lock`文件包含了定义`composer.json`中依赖项的哈希值。所以即使你解决了冲突，这个最终合并结果的lock文件仍是错误的。

最佳方案应该这样做，用下面一行代码在项目根目录创建一个`.gitattributes`文件，它会告诉 git 不要试图对`composer.lock`文件进行合并操作：

```
/composer.lock -merge
```

推荐 [Trunk Based Development][7] 方式（常用佳品，不会有错），使用临时的特性分支纠正这种问题。当你有个临时分支需要即时合并时，因此导致的`composer.lock`文件合并冲突的风险极小。你甚至可以仅仅为添加一个依赖项而创建分支，然后马上进行合并。

假如在衍合过程中`composer.lock`遇到合并冲突又当如何呢？ 使用主分支版本解决，这样仅仅修改`composer.json`文件即可（新增一个包）。然后运行`composer update --lock`，就会把`composer.json`文件的修改更新到`composer.lock`文件中。现在把已经更新的`composer.lock`文件提交到版本暂存区，然后继续衍合操作。

## Tip #10:了解`require`和`require-dev`之间的区别

能够意识到`require`和`require-dev`模块之间的区别是非常重要的。

需要运行在应用中或者库中的包都应该被定义在`require`(例如： Symfony, Doctrine, Twig, Guzzle, ...)中。如果你正在创建一个库， 注意将什么内容定义为`require`。因为这个部分的 每个依赖项同时也是使用了该库的应用的依赖。

开发应用程序(或库)所需的包应该定义在`require-dev`(例如：PHPUnit, PHP_CodeSniffer, PHPStan)中。

## Tip #11: 安全地升级依赖项

我想大家对如下事实存有共识：应该定期对依赖项升级。 此处我想讨论的是依赖项的升级应该放在明处且慎之又慎，而不能是因其他活计的需要才顺手为之。如果在重构应用的同时又升级了库，那么就很难区分应用崩溃的原因是重构还是升级带来的。

可用`composer outdated`命令查看哪些依赖项需要升级。追加一个`--direct`（或`-D`）参数开关是个聪明之举，这只会查看`composer.json`指定的依赖项。还有一个`-m`参数开关，只查看次版本号的升级列表。

对每一个老版本的依赖项进行升级都要尊循如下步骤：

* 创建新分支
* 在`composer.json`文件中更新该依赖项版本到最新版本号
* 运行`composer update phpunit/phpunit --with-dependencies`（使用升级过的库替换`phpunit/phpunit`）
* 检查 Github 上库的版本库中 CHANGELOG 文件，检查是否存在重大变化。 如果存在就升级应用程序
* 本地测试应用程序（使用 Symfony 的话还能在调试栏看到弃用警告）
* 提交修改（包括`composer.json`、`composer.lock`及其他新版本正常运行所做的必要修改）
* 等 CI 构建结束
* 合并然后部署

有时需要一次升级多个依赖项，比如升级 Doctrine 或 Symfony。这种情况下，就要在升级命令中把他们全部罗列出来：

```json
composer update symfony/symfony symfony/monolog-bundle --with-dependencies
```

或者使用通配符升级所有指定命名空间的依赖：

```json
composer update symfony/* --with-dependencies
```

这全都是很乏味的工作，但相对于不小心升级依赖项而言，这提供了额外保障。

一个可接受的简捷方式就是一次升级所有`require-dev`中的依赖项（如果程序代码没有修改的话，否则还是建议创建独立分支以便代码审查）。

## Tip #12: 在`composer.json`中定义其他类型的依赖

除了定义库作为依赖项外，也以在这儿定义其他东西。

可以定义应用程序和库所支持的 PHP 版本：

```json
"require": {
    "php": "7.1.* || 7.2.*",
},
```

也能定义应用程序和库所需要的扩展。在尝试 docker 化自己的应用时，或是你的同伴头一次设置应用环境时，这招超级实用。

```json
"require": {
    "ext-mbstring": "*",
    "ext-pdo_mysql": "*",
},
```

（当 [扩展版本不一致][8] 时，版本号要用`*`）。

## Tip #13: 在CI构建期间验证`composer.json``composer.json`和`composer.lock`应当一直保持同步. 因此, 一直为他们保持自动核对是一个好主意. 将此添加成为你的构建脚本的一部分将会确保`composer.lock`与`composer.json`保持同步:

```json
composer validate --no-check-all --strict
```

## Tip #14: 在 PHPStorm 中使用 Composer 插件

这里有一个 [composer.json plugin for PHPStorm][9]. 当手动修改`composer.json`时，插件会自动完成及执行一些验证.

如果你在使用其他 IDE (或者只是一个编辑器), 你可以使用 [its JSON schema][10] 设置验证.

## Tip #15: 在`composer.json`中指明生产环境的PHP版本号

如果你和我一样，有时还 [在本地环境跑PHP最新预释版本][11]， 那么就会处于升级依赖项的版本不能运行于生产环境的风险。现在我就在使用 PHP 7.2.0 ，也就意味着我安装的库可能在 7.1 版本中运行不了。如果生产环境跑的是 7.1 版本，安装就会失败。

不过不用担心，有个非常简单的解决办法，在`composer.json`文件的`config`部分指明生产环境的 PHP 版本号即可：

```json
"config": {
    "platform": {
        "php": "7.1"
    }
}
```

别把它和`require`部分的设置搞混了，它的作用不同。你的应用就可以运行 7.1 或 7.2 版本下，而且同时指定了平台版本为 7.1 （这意味着依赖项的升级版本要和 平台版本 7.1 保持兼容）：

```json
 "require": {
    "php": "7.1.* || 7.2.*"
},
"config": {
    "platform": {
        "php": "7.1"
    }
},
```

## Tip #16: 使用自有托管 Gitlab 上的私有包

推荐使用`vcs`作为版本库类型，并且 Composer 决定获取包的合适的方法。比如，从Github上添加一个 fork，使用它的 API 下载整个版本库的 .zip 文件，而不用克隆。

不过对一个私有的 Gitlab 安装来讲会更复杂。如果用`vcs`作版本库类型，Composer 会检测到它是个 Gitlab 类型的安装，会尝试使用 API 下载包（这要求有 API key。我不想设置，所以我只用 SSH 克隆安装了) ：

首先指明版本库类型是`git`：

```json
 "repositories": [
    {
        "type": "git",
        "url": "git@gitlab.mycompany.cz:package-namespace/package-name.git"
    }
]
```

然后指明常用的包：

```json
 "require": {
    "package-namespace/package-name": "1.0.0"
}
```

## Tip #17: 临时使用 fork 下 bug 修复分支的方法

如果在某个公共的库中找到一个 bug，并且在Github上自己的 fork 中修复了它， 这就需要从自己的版本库里安装这个库，而不是官方版本库（要到修复合并且修复的版本释出才行）。

使用 [内嵌别名][12] 可轻松搞定：

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/you/monolog"
        }
    ],
    "require": {
        "symfony/monolog-bundle": "2.0",
        "monolog/monolog": "dev-bugfix as 1.0.x-dev"
    }
}
```

可以通过 href="[https://getcomposer.org/doc/05-repositories.md#path][13]">设置 path 作为版本库类型 在本地测试这次修复，然后再 push 更新版本库。

## 更新于 2018-01-08:

文章发布后，我收到了一些建议，提供了更多的使用技巧。它们分别是：

## Tip #18：使用 prestissimo 加速你的包安装

Composer 有个 [hirak/prestissimo][14] 插件，通过该插件能够以并行的方式进行下载，从而提高依赖包的安装速度。

那么，这么好的东西，你现在该如何做？你仅仅需要马上全局安装这个插件，然后就可以自动地在所有项目中使用。

```
composer global require hirak/prestissimo
```

## Tip #19: 当你不确定时，测试你的版本约束

即使在阅读 [the documentation][15] 之后，书写正确的版本约束在一些时候也是很棘手的.

幸运的是, 这里有 [Packagist Semver Checker][16] 可以用来检查哪个本部匹配特定的约束. 他不是仅仅的分析版本约束, 他从`Packagist`下载数据以来展示实际的发布版本.

查看 [the result for symfony/symfony:^3.1][17].

## Tip #20: 在生产环境中使用使用权威类映射文件

应该在生产环境中 [生成权威类映射文件][18] 。这会让类映射文件中包含的所有类快速加载，而不必到磁盘文件系统进行任何检查。

可以在生产环境构建时运行以下命令：

```
 composer dump-autoload --classmap-authoritative
```

## Tip #21: 为测试配置`autoload-dev`你也不想在生产环境中加载测试文件（考虑到测试文件的大小和内存使用）。这可以通过配置`autoload-dev`解决（与`autoload`相似）：

```json
"autoload": {
    "psr-4": {
        "Acme\\": "src/"
    }
},
"autoload-dev": {
    "psr-4": {
        "Acme\\": "tests/"
    }
},
```

## Tip #22: 尝试 Composer 脚本

`Composer`脚本是一个创建构建脚本的轻量级工具。关于这个，我有[另文述及][19]。

## 总结

如果你不同意某些观点且阐述出你为什么不同意的意见（不要忘记标注`tip`的编号）我将很高兴。

讨论请前往 [你必须知道的 17 个 Composer 最佳实践（已更新至 22 个）][0]

[0]: https://link.zhihu.com/?target=https%3A//link.jianshu.com/%3Ft%3Dhttps%253A%252F%252Flaravel-china.org%252Ftopics%252F7609%252Fyou-have-to-know-17-composer-best-practices-updated-to-22
[1]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/
[2]: https://link.zhihu.com/?target=https%3A//github.com/elastic/elasticsearch-php
[3]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/02-libraries.md%23lock-file
[4]: https://link.zhihu.com/?target=https%3A//github.com/mhujer/fio-api-php/blob/master/.travis.yml
[5]: https://link.zhihu.com/?target=https%3A//travis-ci.org/mhujer/fio-api-php
[6]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/06-config.md%23sort-packages
[7]: https://link.zhihu.com/?target=https%3A//trunkbaseddevelopment.com/
[8]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/01-basic-usage.md%23platform-packages
[9]: https://link.zhihu.com/?target=https%3A//plugins.jetbrains.com/plugin/7631-php-composer-json-support
[10]: https://link.zhihu.com/?target=https%3A//getcomposer.org/schema.json
[11]: https://link.zhihu.com/?target=https%3A//blog.martinhujer.cz/php-7-2-is-due-in-november-whats-new/
[12]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/articles/aliases.md%23require-inline-alias
[13]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/05-repositories.md%23path
[14]: https://link.zhihu.com/?target=https%3A//github.com/hirak/prestissimo
[15]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/articles/versions.md%23writing-version-constraints
[16]: https://link.zhihu.com/?target=https%3A//semver.mwl.be/
[17]: https:`//semver.mwl.be/#?pa`ckage=symfony%2Fsymfony&version=%5E3.1&minimum-stability=stable
[18]: https://link.zhihu.com/?target=https%3A//getcomposer.org/doc/articles/autoloader-optimization.md%23optimization-level-2-a-authoritative-class-maps
[19]: https://link.zhihu.com/?target=https%3A//blog.martinhujer.cz/have-you-tried-composer-scripts/
[20]: https://link.zhihu.com/?target=https%3A//link.jianshu.com/%3Ft%3Dhttps%253A%252F%252Flaravel-china.org%252Ftopics%252F7609%252Fyou-have-to-know-17-composer-best-practices-updated-to-22