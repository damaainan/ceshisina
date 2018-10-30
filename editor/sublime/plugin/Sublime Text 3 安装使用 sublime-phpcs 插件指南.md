# MacOS Sublime Text 3 安装使用 sublime-phpcs 插件指南

* 2016-08-03 [Mac OS][0] , [前端][1] 12.91k 阅读 [赞 ( 6 )][2]


一直在找比较好用的php代码语法错误提示的sublime插件，之前使用的sublime-linter能够提示一些fatal error和syntax error，但是比如变量未定义的错误不会提示（我经常会犯的错误，比如手滑前面定义了一个变量，后面使用的时候拼写错误）。

使用IDE倒是能解决错误提示和代码格式化的问题，但是不管是Netbeans还是PhpStorm在Mac上使用都不够流畅，滚动不够流畅，而且各种装饰线，文字底色提示都比较晃眼，不够干净，所以一直还是使用sublime做为主力开发（Sublime Text 3也已支持代码追踪但是的确不如IDE好用）。

最近在搜索时发现了sublime-phpcs，安装下来发现可通过绑定PHP Mess Detector来检测包括未定义变量等的错误，可以绑定PHP-CS-Fixer来格式化代码，很惊喜。但是安装插件的过程中遇到不少坑，所以在这里写一个详细的安装指南，方便自己以后再次安装，也给打算安装该款插件的朋友一个帮助。

### 一、[sublime-phpcs][4] 插件安装

该插件无法单独使用，需要配合各种phar包，官方文档：[sublime-phpcs][5]

插件本身的安装步骤不在赘述，使用sublime的Package安装即可：

> Preferences -> Package Control -> Install Package -> 搜索Phpcs

插件安装好后（在Preferences -> Package Setting 菜单中可看到PHP Code Sniffer）要配置要用到的功能的phar包的执行文件路径，这时候如果你直接去打开Preferences -> Package Setting -> PHP Code Sniffer -> Setting – Default 并且保存，可能会有文件不存在的错误提示，这时候需要手动创建一个Phpcs包的配置文件夹（参见[Making PHP Code Sniffer plugin work in Sublime Text 3][6]）：

    cmkdir ~/Library/Application\ Support/Sublime\ Text\ 3/Packages/Phpcs

然后再保存该文件即可。但是在今后修改该插件的配置时，还是需要在Setting – User 中进行修改配置。

插件安装好后，可根据需要来安装相应功能的phar包。

### 二、插件各个功能依赖的phar包的安装

sublime-phpcs 插件中各种功能（错误提示，代码格式化）需要依赖相应的PHP phar包来实现。每个功能的phar包本身是为了实现各自功能而开发的，每个功能包都有自己的一套cli下使用的方法。而sublime-phpcs插件提供了把这些功能包在sublime中使用的界面。

#### 1. [PHP_CodeSniffer][4]

下载页面：[PHP_CodeSniffer Download][7]  
该包的作用是用指定的代码规范（默认使用PEAR规范，可指定使用PSR1，PSR2或自己制定的规范）来检查代码是否符合规范。详细介绍和使用方法参见：[PHP_CodeSniffer Manuel][8]。该包使用pear来安装，OS X在10.4之前已默认安装pear，10.4之后需要自行安装，官方安装方法：[Getting and installing the PEAR package manager][9]。

    pear install PHP_CodeSniffer-2.6.0


pear 命令的安装我会另外写一篇博客。  
如果你没有把 pear/bin 目录加入到 PATH 中，安装好phpcs后会不能直接用phpcs 来执行。

#### 2. PHP Mess Detector (phpmd)

主页：[PHP Mess Detector][10]  
该包可以检查PHP代码存在的问题，包括：

1. > 潜在的BUG
1. > 有待改进的代码（比如过短变量名长度等）
1. > 过于复杂的表达式
1. > 定义但未使用的变量、方法、属性）
1. > 使用未定义的变量

OS X可以通过homebrew 安装

    brew install phpmd


或手动下载安装

```
wget -c http://static.phpmd.org/php/latest/phpmd.phar
chmod a+x phpmd.phar
mv phpmd.phar /usr/local/bin/phpmd
```

#### 3. PHP Coding Standards Fixer（php-cs-fixer）

主页：[PHP Coding Standards Fixer][11]  
该包可以修复PHP代码中的规范问题。装不装都行，因为phpcs自带了PHP Code Beautifier（phpcbf）也可以用来修复不规范的代码（在安装时phpcs时，phpcbf也被安装到了pear/bin/中）。

可通过wget下载安装：

```
wget http://get.sensiolabs.org/php-cs-fixer.phar -O php-cs-fixer
sudo chmod a+x php-cs-fixer
sudo mv php-cs-fixer /usr/local/bin/php-cs-fixer
```

### 三、配置插件

各个功能的phar包都装好后，可以开始配置sublime-phpcs插件了。  
可用which命令先查看各个命令的路径：

```
which phpcs
which phpmd
which php-cs-fixer
which phpcbf
```

然后编辑Preferences -> Package Setting -> PHP Code Sniffer -> Setting – User对插件进行配置：

```
{
    "phpcs_php_path": "/usr/local/bin/php",
    "phpcs_executable_path": "/usr/local/bin/phpcs",
    "phpmd_executable_path": "/usr/local/bin/phpmd",
    "phpcbf_executable_path": "/usr/local/bin/phpcbf",
    "php_cs_fixer_executable_path": "/usr/local/bin/php-cs-fixer",
    // 开启phpmd
    "phpmd_run": true
}
```

查看phpcs的默认配置，可发现phpcs检测代码规范使用的是PSR-2，phpcbf也使用的是PSR-2来格式化代码：

```
// Additional arguments you can specify into the application
//
// Example:
// {
//     "--standard": "PEAR",
//     "-n"
// }
"phpcs_additional_args": {
    "--standard": "PSR2",
    "-n": ""
},
/* ... */
"phpcbf_additional_args": {
    "--standard": "PSR2",
    "-n": ""
},
```

同时这个配置参数还可以指定其他的cli执行参数，具体可参考各个功能包的官方文档。

而php_cs_fixer_additional_args参数为空，查了readme可知：

> By default, all PSR-2 fixers and some additional ones are run.

php-cs-fixer默认使用PSR-2规范格式化代码。  
如果要使用其他规范格式化代码，按照各个功能包文档修改这几组参数即可。

其他配置可参见sublime-phpcs官方文档。

### 四、使用

#### 1. 使用sublime-phpcs

配置完成后，就可以使用sublime-phpcs对代码进行检查和格式化了。sublime-phpcs默认在保存时执行检查。每次保存文件时就会检查代码是否正常，如果代码有不规范的地方或者错误，会在sublime上部的命令行提示，比如：

![phpcs][12]

phpcs会把出现问题的行数和描述都展示出来，可根据提示自行修改，或点击右键，按下图选项对格式问题进行自动修复（phpmd检测出的代码问题等需要手动修复）

![phpcs_setting][13]

另外，可以设置在保存时就格式化代码：

    "phpcbf_on_save": true


#### 2. 批量检测/格式化代码

有时候会对拿到的整个项目的规范进行检测和格式化，可以直接使用功能包命令：
```
// 检查规范
phpcs --standard=PSR2 /path/to/code/directory

// 检查问题： phpmd 代码路径 报告格式 规则列表
phpmd /path/to/code/directory text codesize,unusedcode,naming

// 格式化代码
phpcbf --standard=PSR2 /path/to/code/directory
```

- - -

原文连接：[MacOS Sublime Text 3 安装使用 sublime-phpcs 插件指南][14] ，转载请注明出自[体验盒子 | 关注网络安全][15]。

[0]: https://www.uedbox.com/entertainment/macos/
[1]: https://www.uedbox.com/design/htmlcss/
[2]: javascript:;
[4]: http://www.uedbox.com/macos-install-sublime-phpcs/
[5]: http://benmatselby.github.io/sublime-phpcs
[6]: http://theaveragedev.com/making-php-code-sniffer-plugin-work-in-sublime-text-3/
[7]: http://pear.php.net/package/PHP_CodeSniffer/download
[8]: http://pear.php.net/manual/en/package.php.php-codesniffer.php
[9]: https://pear.php.net/manual/en/installation.getting.php
[10]: https://phpmd.org/documentation/index.html
[11]: https://github.com/FriendsOfPHP/PHP-CS-Fixer
[12]: http://www.uedbox.com/wp-content/uploads/2016/08/phpcs-1024x464.jpg
[13]: http://www.uedbox.com/wp-content/uploads/2016/08/phpcs_setting-1024x420.jpg
[14]: https://www.uedbox.com/macos-install-sublime-phpcs/
[15]: https://www.uedbox.com