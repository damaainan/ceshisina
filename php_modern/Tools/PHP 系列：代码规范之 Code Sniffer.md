# PHP 系列：代码规范之 Code Sniffer 

3个月前 ⋅ 1364 ⋅ 32 ⋅ 11 

![php code sniffer][0]

有些同学可能还没听过这东西，大概介绍一下：

`PHP_CodeSniffer` 是一个代码风格检测工具。它包含两类脚本，`phpcs` 和 `phpcbf`（[GitHub地址][1]）。

`phpcs` 脚本对 PHP、JavaScript、CSS 文件定义了一系列的代码规范（通常使用官方的代码规范标准，比如 PHP 的 PSR2），能够检测出不符合代码规范的代码并发出警告或报错（可设置报错等级）。

`phpcbf` 脚本能自动修正代码格式上不符合规范的部分。比如 PSR2 规范中对每一个 PHP 文件的结尾都需要有一行空行，那么运行这个脚本后就能自动在结尾处加上一行空行。

## 安装[#][2]

推荐使用 composer 来安装：

    composer global require "squizlabs/php_codesniffer=*"

安装完后就会在全局的 Vendor 目录下的 bin 中生成两个软链接：

    phpcbf -> ../squizlabs/php_codesniffer/bin/phpcbf
    phpcs -> ../squizlabs/php_codesniffer/bin/phpcs

如果你不知道全局 Vendor 目录在哪，用下这个命令吧：

    composer global config bin-dir --absolute

## 命令行使用[#][3]

到这一步，其实你就可以愉快的使用这两个命令了：

哦，不过如果要全局使用这两个命令那还是做个软链接放在 `/usr/local/bin` 下吧~

    $ phpcs test.php
    
    FILE: /Users/stephen/Develop/Code/test.php
    --------------------------------------------------------------------------------------------
    FOUND 2 ERRORS AFFECTING 2 LINES
    --------------------------------------------------------------------------------------------
     2 | ERROR | [ ] Missing file doc comment
     3 | ERROR | [x] TRUE, FALSE and NULL must be lowercase; expected "false" but found "FALSE"
    --------------------------------------------------------------------------------------------
    PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
    --------------------------------------------------------------------------------------------
    
    Time: 45ms; Memory: 4Mb
    
    $ phpcbf test.php
    
    PHPCBF RESULT SUMMARY
    ----------------------------------------------------------------------
    FILE                                                  FIXED  REMAINING
    ----------------------------------------------------------------------
    /Users/stephen/Develop/Code/test.php                  1      1
    ----------------------------------------------------------------------
    A TOTAL OF 1 ERROR WERE FIXED IN 1 FILE
    ----------------------------------------------------------------------
    
    Time: 49ms; Memory: 4Mb

## Sublime Text 中集成[#][4]

### 安装插件[#][5]

前提 `Package Control` 要安装好，这个你肯定没问题的（莫名的自信，逃）

安装 phpcs 这个插件，然后打开这个插件的 `Settings - User` 和 `Settings - Default`，将后者的内容全部复制到前者。

### 配置插件[#][6]

将以下这几个配置项配置一下：

当前环境中 php 的执行路径

    "phpcs_php_prefix_path": "/usr/bin/php",

当前环境中 `phpcs` 的执行路径

    "phpcs_executable_path": "~/.composer/vendor/bin/phpcs",

执行脚本时额外添加的参数，一般以 PSR2 代码规范作为标准，你也可以选择 PSR1、PEAR 等

    "phpcs_additional_args": {
        "--standard": "PSR2",
        "-n": ""
    },

当前环境中 `phpcbf` 的执行路径

    "phpcbf_executable_path": "/Users/stephen/.composer/vendor/bin/phpcbf",

开启保存就执行 `cbf` 脚本功能

    "phpcbf_on_save": true,

现在就可以愉快的玩耍了，编辑完 .php 后缀的文件保存后就会自动回复修正代码不规范的地方。

这个插件还有其他代码规范可以设置的地方，比如 PHP Mess Detector settings、PHP Linter settings、PHP Scheck settings下次在研究其他几个，目前是够我用了，逃)

## PhpStrom 中集成[#][7]

### 配置 Code Sniffer[#][8]

在 Settings -> Languages & Frameworks -> PHP -> Code Sniffer 中对 phpcs 进行配置

点击 Configuration: Local 旁边的 ...，将当前环境的 phpcs 执行脚本所在路径配置进去，旁边有一个 Validate 按钮可以进行验证，其他两个参数默认就好，这里也稍微说一下吧。

Maxumum number of messages per file[1...100]：每个文件最多显示不符合代码规范的条数，一般出现50个了那你有很多工作可以做了，其实10个就够了，尤其对我这种代码洁癖和强迫症晚期的人来说一个足矣 。。。

Tool process timeout, sec[1...30]：脚本执行的超时时间

### 开启验证[#][9]

在 Settings -> Editor -> Inspections 中进行开启

找到 PHP -> PHP Code Sniffer validation 选项，对其打钩，在右侧进行详细配置

我是将 Options -> Show warnings as: Error 开启了，当然你也可以选择 Warnning，提示级别高一点能够强迫自己，没啥不好的吧~

Coding standard 依旧是选择 PSR2，如果找不到这个选项，记得点一下紧挨着的刷新按钮。

### 自动修复[#][10]

我找了半天没发现 PhpStrom 有支持 phpcbf 的可用选项，所以只能通过 External Tools 来实现了。

在 Settings -> Tools -> External Tools 中进行添加，下面是我的一个示例，基本可以照搬 

![:blush:][11]

Parameter | Value 
-|-
Name | phpcbf 
Description | Automatically correct coding standard violations. 
Program | phpcbf 
Parameters | --standard=PSR2 "$FileDir$/$FileName$" 

现在就可以在菜单栏上的 Tools -> External Tools 中找到它并愉快的使用了。

亮哥，这样很不方便诶。。。

那就加一个快捷键把。。。

在 Settings -> Keymap -> External Tools -> phpcbf 中进行添加快捷键操作，我设置的是 Option + F，或者 Windows 上的 Alt + F。

## 蜜汁延伸[#][12]

之前没用 phpcbf，代码自动修复使用的是 php-cs-fixer，但后来发现两者功能差不多，所以秉着能少一个包就少一个包的原则，放弃了 php-cs-fixer。

有兴趣的自行拓展了解~

[0]: https://cdn.stephencode.com/article/php-code-sniffer/php-code-sniffer.jpg
[1]: https://github.com/squizlabs/PHP_CodeSniffer
[2]: #安装
[3]: #命令行使用
[4]: #Sublime-Text-中集成
[5]: #安装插件
[6]: #配置插件
[7]: #PhpStrom-中集成
[8]: #配置-Code-Sniffer
[9]: #开启验证
[10]: #自动修复
[11]: https://dn-phphub.qbox.me/assets/images/emoji/blush.png
[12]: #蜜汁延伸