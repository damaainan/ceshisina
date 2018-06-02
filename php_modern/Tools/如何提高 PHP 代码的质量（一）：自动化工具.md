## 如何提高 PHP 代码的质量（一）：自动化工具

来源：[https://mp.weixin.qq.com/s/qIBZOIEN8SA6YRdvuYvlkg](https://mp.weixin.qq.com/s/qIBZOIEN8SA6YRdvuYvlkg)

时间 2018-05-30 08:18:01

#### 女主宣言

说实话，在代码质量方面，PHP的压力非常大。通过阅读本系列文章，您将了解如何提高PHP代码的质量  。
 
PS：丰富的一线技术、多元化的表现形式，尽在“HULK一线技术杂谈  ”，点关注哦！

说实话，在代码质量方面，PHP的压力非常大。通过阅读本系列文章，您将了解如何提高PHP代码的质量。
 
我们可以将此归咎于许多原因，但这肯定不仅仅是因为PHP生态系统缺乏适当的测试工具。在本文中，我想向您展示一个简单的设置，用于项目的基本质量测试。 我不会详述任何特定的工具，而是专注于设定测试环境。
 
本文中有一个演示代码可以在GitHub上找到：https://github.com/mkosiedowski/php-testing-demo 如果你对这篇文章中的例子有任何问题，可以参考。

1
 
#### 必备条件

我假设您熟悉PHP 7.1语法，您可以使用Composer和PSR-4来进行自动加载和PSR-1&PSR-2的编码标准。在我的示例中，vendor的二进制文件被安装到 ./bin 目录。

2
 
#### 构建工具

我们将使用一些不同的测试工具，所以最好有一些能用一个脚本来运行它们的东西。 PHING为我们提供了解决此问题的绝佳解决方案。 PHing与Apache Ant相似，可以使用XML配置轻松自动执行任务。 我们可以通过运行以下命令来安装它：
 
``` 
$ php composer.phar require --dev phing/phing
```
 
然后，在项目的根目录中创建一些基本的build.xml文件。
 
``` 
<?xml version="1.0" encoding="UTF-8"?>
</project>
```
 
在接下来的步骤中，我们将添加一些由PHing运行的目标。

3
 
#### 静态代码分析

我为了提高代码质量，您可以做的第一件事就是设置静态代码分析器。他们会在没有真正运行的情况下阅读你的错误代码。这就像在几秒钟内由一个机器人完成了一个代码审查一样。很酷，不是吗?

4
 
#### 代码风格

当使用正确的样式编写时，您的代码更容易维护。每个人都知道（如果你不这样做，你至少应该开始阅读Robert C. Martin的“Clean Code”），但仍然有很多团队在遵守他们达成的标准方面存在问题。我们可以用phpcs - PHP代码嗅探来自动化这个任务，有没有很神奇。
 
我们可以通过运行以下命令来安装：
 
``` 
$ php composer.phar require --dev squizlabs/php_codesniffer
```
 
然后添加一个在build.xml中运行它的目标。你的build.xml现在应该是这样的：
 
``` 
<?xml version="1.0" encoding="UTF-8"?>
<target name="phpcs" description="Check code style with PHP_CodeSniffer">
        <exec executable="bin/phpcs" passthru="true" checkreturn="true">
            <arg line="--standard=PSR1,PSR2 -extensions=php src"/>
        </exec>
    </target>
    <target name="run" depends="phpcs"/>
</project>
```
 
现在您可以运行 ./bin/phing了，phpc将自动检查您是否在PSR-1和PSR-2编码标准上有任何错误。
 
许多框架，比如Symfony，定义了它们自己的代码风格规则，我们也可以自动检查这些规则。比如：如果您使用的是Symfony框架，请检查https://github.com/leaphub/phpcs-symfony2标准，以了解如何使用phpcs检查Symfony的标准。
 
错误格式的文件的示例输出：
 
``` 
MyProject > phpcs:


FILE: /home/maciej/workspace/php-testing/src/Domain/Price.php
-------------------------------------------------------------------------
FOUND 1 ERROR AFFECTING 1 LINE
-------------------------------------------------------------------------
28 | ERROR | Method name "Price::get_value" is not in camel caps format
-------------------------------------------------------------------------
Time: 67ms; Memory: 6Mb
```
 
在代码评审期间，不再浪费时间检查编码标准，从现在开始，它将自动实现！

5
 
#### 复制/粘贴检测器

重复的代码是不好的，每个人都知道。有时我们错误地创建了这样的代码，我们从来没有注意到它。有时我们这样做是因为我们懒惰。最好是配备一个工具，它可以在构建时提示这个问题。PHPCPD - PHP复制/粘贴检测器。
 
通过运行以下命令来安装它：
 
```
$ php composer.phar require --dev sebastian/phpcpd
```
 
然后将目标添加到build.xml：
 
```
<target name="phpcpd" description="Generate pmd-cpd.xml using PHPCPD">
    <exec executable="bin/phpcpd" passthru="true">
        <arg line="src"/>
    </exec>
</target>
...
<target name="run" depends="phpcs,phpcpd"/>
```
 
在vendor目录上运行的重复代码检查的示例输出：
 
```
phpcpd 4.0.0 by Sebastian Bergmann.

Found 74 clones with 2929 duplicated lines in 97 files:

- /home/maciej/workspace/php-testing/vendor/phpspec/phpspec/src/PhpSpec/Matcher/TriggerMatcher.php:81-102   
  /home/maciej/workspace/php-testing/vendor/phpspec/phpspec/src/PhpSpec/Matcher/TriggerMatcher.php:114-135

- /home/maciej/workspace/php-testing/vendor/squizlabs/php_codesniffer/src/Reports/Full.php:81-114 
  /home/maciej/workspace/php-testing/vendor/squizlabs/php_codesniffer/src/Reports/Code.php:162-195

(...)
```

6
 
#### 想要真正深入的代码分析？

如果你从头开始你的项目，你应该看看Phan - 它是一个非常强大的代码分析器，它会让你的代码变得漂亮。在https://github.com/phan/phan上查看。安装非常简单 - 只需安装php-ast扩展（在Ubuntu中，您可以尝试运行sudo apt-get install php-ast）并运行：
 
```
$ php composer.phar require --dev phan/phan
```
 
然后创建一个配置文件 .phan/config.php 内容为：
 
```php
<?php
return [
    'target_php_version' => '7.1',
    'directory_list' => [
        'src',
        'vendor/symfony/console',
    ],
    "exclude_analysis_directory_list" => [
        'vendor/'
    ],
];
```
 
在build.xml文件中也创建phan目标：
 
```xml
<target name="phan" description="Check code with phan">
   <exec executable="bin/phan" passthru="true" checkreturn="true"/>
</target>
...
<target name="run" depends="phpcs,phpcpd,phan"/>
```
 
现在，您可以运行您的代码分析，如果您犯了错误（例如……为类属性声明错误的phpdoc类型），您应该看到这样的消息：
 
``` 
MyProject > phan:

src/Domain/PriceComparator.php:17 PhanTypeMismatchProperty Assigning \Domain\PriceConverter to property but \Domain\PriceComparator::priceConverter is int
src/Domain/PriceComparator.php:35 PhanNonClassMethodCall Call to method convert on non-class type int
```
 
Phan很神奇 - 它读取你的整个代码并对它执行多次检查，包括将phpdoc声明与实际使用变量、方法、类等进行比较，你可以查看https://github.com/phan/phan#features的所有特征列表。

#### 总结
 
现在，您的项目中有三个完全自动化的工具，可以保护您的代码的质量。您所需要做的就是手动运行 ./bin/phing，或者将其附加到您的git-hook或持续集成中。您的代码将被检查编码标准、重复和正式错误。这些检查应该会导致更可靠的运行时，并且花费在代码审查上的时间更少。

