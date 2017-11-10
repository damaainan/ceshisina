# PHP 系列：代码规范之 Mess Detector 

2个月前 ⋅ 587 ⋅ 9 ⋅ 9 

之前写了一篇 [《PHP 系列：代码规范之 Code Sniffer》][0]，介绍了 phpcs 和 phpcbf 这两个检测脚本，今天让我们欢迎新成员 phpmd ~ 

![:clap:][1]

![:clap:][1]

> PHPMD is a spin-off project of PHP Depend and aims to be a PHP equivalent of the well known Java tool PMD. PHPMD can be seen as an user friendly and easy to configure frontend for the raw metrics measured by PHP Depend.

![phpmd][2]

它的作用主要是分析我们写的代码，然后指出其中潜在的问题。举个栗子，大家都知道 IDE 中，如果一个变量声明了但未使用过，IDE 会报出警告。

其实使用的也是这类静态检测脚本，对于编写良好的代码，这些工具能较好的帮到你。自从弃了 PHPStorm 后，一直在 Sublime Text 上敲，既然默认不集成这些，那就自己手动集成咯~

## 安装[#][3]

还是使用 composer，使用全局安装方式：

    composer global require "phpmd/phpmd"

图方便加个软连接：

    sudo ln -s /Users/stephen/.composer/vendor/phpmd/phpmd/src/bin/phpmd /usr/bin/phpmd

## 命令行使用[#][4]

    $ phpmd /path/to/ text unusedcode
    
    /path/to/test.php:25    Avoid unused local variables such as '$a'.
    /path/to/test.php:26    Avoid unused local variables such as '$b'.

这里使用了 text 格式的输出，默认是 xml，像这样：

    <?xml version="1.0" encoding="UTF-8" ?>
    <pmd version="@project.version@" timestamp="2017-09-22T01:33:16+00:00">
      <file name="/path/to/test.php">
        <violation beginline="25" endline="25" rule="UnusedLocalVariable" ruleset="Unused Code Rules" externalInfoUrl="http://phpmd.org/rules/unusedcode.html#unusedlocalvariable" priority="3">
          Avoid unused local variables such as '$a'.
        </violation>
        <violation beginline="26" endline="26" rule="UnusedLocalVariable" ruleset="Unused Code Rules" externalInfoUrl="http://phpmd.org/rules/unusedcode.html#unusedlocalvariable" priority="3">
          Avoid unused local variables such as '$b'.
        </violation>
      </file>
    </pmd>

然后你会发现使用 text 格式会简明扼要很多 

![:see_no_evil:][5]

。。。

上面这个例子只使用了一个规则 unusedcode，官方提供了六个规则，能满足绝大部分的需求： cleancode, codesize, controversial, design, naming, unusedcode具体这些规则检测的是哪些方面，去看下官方文档是最好的，[传送门][6]

## 在 Sublime 中集成[#][7]

### 安装插件[#][8]

前提 Package Control 要安装好，这个你肯定没问题的（莫名的自信，逃 

![:runner:][9]

）

安装 phpcs 这个插件，然后打开这个插件的 Settings - User 和 Settings - Default，将后者的内容全部复制到前者。

### 配置[#][10]

将以下配置项替换掉：

    {
        "phpmd_run": true,
    
        "phpmd_command_on_save": true,
    
        "phpmd_executable_path": "phpmd",
    
        "phpmd_additional_args": {
            "codesize,controversial,design,naming,unusedcode": ""
        },
    }

这边有几个注意点：

1. "codesize,controversial,design,naming,unusedcode" 这里的逗号后面不能加空格
1. cleancode 没有加上是因为一旦你使用了类静态方法调用，这个规则就会提醒你不要用。。。比如你在用 Laravel，然后你就炸了，虽然多用静态方法会增加不少运行时内存，但 Laravel 的静态方法是伪静态，都会实例化，所以先暂时舍弃这个规则了，之后自己写一个规则把其他好用的检测方法包含进来就好。

> 在配置的过程中建议全程开着 Sublime 的控制台（> Ctrl + ~>  唤起）。

比如我将 "codesize,controversial" 改成 "codesize, controversial"，加了个逗号，然后再使用的时候，控制台里会报出错误：

    [Phpcs] Cannot find specified rule-set " controversial".

- - -

本文参考链接：

* [Mess Detector 官方网站][11]

[0]: https://blog.stephencode.com/p/php_code_sniffer.html
[1]: https://dn-phphub.qbox.me/assets/images/emoji/clap.png
[2]: https://cdn.stephencode.com/article/php/phpmd.png
[3]: #安装
[4]: #命令行使用
[5]: https://dn-phphub.qbox.me/assets/images/emoji/see_no_evil.png
[6]: https://phpmd.org/rules/index.html
[7]: #在-Sublime-中集成
[8]: #安装插件
[9]: https://dn-phphub.qbox.me/assets/images/emoji/runner.png
[10]: #配置
[11]: https://phpmd.org