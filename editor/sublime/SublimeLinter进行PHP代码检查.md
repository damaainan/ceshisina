# [Sublime Text3下配置SublimeLinter进行PHP代码检查][0]

* 作者: [Don][1]
* 时间: June 27, 2016
* 分类: [代码分析][2]

> SublimeLinter 是前端编码利器——Sublime Text 的一款插件，用于高亮提示用户编写的代码中存在的不规范和错误的写法，支持 JavaScript、CSS、HTML、Java、PHP、Python、Ruby 等十多种开发语言。

1. 安装SublimeLinter

在Sublime Text3下同时按住ctrl+shift+p，然后输入install，选择Install Package。

![Install Package][3]

然后输入`sublimelinter`，选择`SublimeLinter`进行安装。

![SublimeLinter][4]

安装完成后，会看到如下的说明。

![SublimeLinter安装][5]
1. 安装SublimeLinter-php

> SublimeLinter-php使用的是php -l 进行的检查。  
> This linter plugin for SublimeLinter provides an interface to php -l. It will be used with files that have the “PHP”, “HTML”, or “HTML 5” syntax.

在Sublime Text3下同时按住ctrl+shift+p，然后输入install，选择Install Package。

![Install Package][3]

然后输入`sublimelinter-php`，选择`SublimeLinter-php`进行安装。

![SublimeLinter-php][6]
1. 配置SublimeLinter

打开`SublimeLinter`的配置文件，依次点击Preferences->Package Settings->SublimeLinter->Settings - User。

在打开的配置文件里，搜索paths，找到下面的windows，配置php的绝对路径。

![配置SublimeLinter][7]

其中的`lint_mode`，表示运行模式，可选的值有`background`, `load/save`, `save only`, 和 `manual`，这里我设置为了`save only`，只有才保存时才进行检查。  
其中的`mark_style`，表示出错的显示样式，可选的值有"`fill`", "`outline`", "`solid underline`", "`squiggly underline`", "`stippled underline`", 和 "`none`"，默认值为`outline`，出错的情况显示如下。

![出错显示][8]



[0]: https://www.liudon.org/1335.html
[1]: https://www.liudon.org/author/1/
[2]: https://www.liudon.org/category/code/
[3]: http://ww2.sinaimg.cn/large/63c9befagw1f59rzbqew9j20ch03it91.jpg
[4]: http://ww1.sinaimg.cn/large/63c9befagw1f59rze8e55j20c60b9413.jpg
[5]: http://ww1.sinaimg.cn/large/63c9befagw1f59s24y47mj20up0if437.jpg
[6]: http://ww3.sinaimg.cn/large/63c9befagw1f59s3slow5j20c90bg0vc.jpg
[7]: http://ww3.sinaimg.cn/large/63c9befagw1f59s8l1u1kj20y80ljaf0.jpg
[8]: http://ww3.sinaimg.cn/large/63c9befagw1f59sgj1hywj207m043q2v.jpg