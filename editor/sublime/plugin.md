# sublime 插件列表及其配置、使用方法


### 主题类
包含大量配色主题的插件包 首先介绍一个包含大量配色包的网站, [Colorsublime](http://colorsublime.com/), 里面各种各样的配色让人眼花缭乱 [Colorsublime Plugin](https://github.com/Colorsublime/Colorsublime-Plugin).  

iTg 主题  
ayu  
[Soda](http://buymeasoda.github.io/soda-theme/)  
[Spacegray](http://kkga.github.io/spacegray/)  
[Flatland](https://github.com/thinkpixellab/flatland)  
[Tomorrow](https://github.com/chriskempson/tomorrow-theme)  
[Base 16](https://github.com/chriskempson/base16)  
[Solarized](http://ethanschoonover.com/solarized)  
[Predawn](https://github.com/jamiewilson/predawn)  
[itg.flat](https://sublime.wbond.net/packages/Theme%20-%20itg.flat)  
适用于所有其他偏好 [Color Schemes](https://github.com/daylerees/colour-schemes) 和[Сolorsublime](http://colorsublime.com/).

#### Sublime 3 如何设置Seti_UI主题

用 Package Control 安装：Seti_UI
安装之后启用 Seti UI 主题，在 Preferences -> Settings – User 添加一行：

    {
        "theme": "Seti.sublime-theme"
    }

###### 设置Seti_UI主题
通过Browse Packages可以进到 Sublime3 包安装目录下能看到一系列配置文件，主要看下面两个：

    Seti_orig.sublime-theme
    README.md
README.md 文件里面给出了一些 Seti_UI 支持的配置项，使用 CMD+, 组合键打开 Sublime 3的配置文件，在里面添加上README.md里面你喜欢的配置项，比如我添加了：

    "Seti_SB_blue": true,
    "Seti_sb_tree_miny": true,
然后到Seti_orig.sublime-theme 文件中搜索相关配置的定义，比如我搜索选项Seti_sb_tree_miny的定义如下：

    {
        "class": "sidebar_tree",
        "settings": ["Seti_sb_tree_miny"],
        "indent": 10,
        "row_padding": 3,
        "indent_offset": 15,
    },
你就可以通过修改这部分配置来重新定制你喜欢的Sublime 3.




---


### 工具
all Autocomplete sublime只对当前文件进行本文件中的查找不全, all Autocomplete是对全部打开的文件进行查找不全, 选择更多更全面；  

> **converttoUTF8** 编辑的所有文件都使用UTF-8编码；  

**docblockr** 强大的文档注释功能, 只要在文档中输入/*然后按一下tab, 就会根据代码自动生成注释；  
**emmet** 前段神器, 减少大量的工作量, 使用方法可以参考Emmet：HTML/CSS代码快速编写神器或者官方文档；  
markdownediting或者markdownPerview 这个是写Markdown必备的。可以在包管理器中安装。装完之后，写作Markdown时（右下角显示语法为Markdown），可以按ctrl+b，直接就会生成HTML，并在浏览器中显示；  
**OmniMarkupPreviewer** Ctrl+Alt+O 浏览器预览  
**jsformat** JavaScript代码格式化；  
**sidebarenhancement** 这是用来增强左边的侧边栏。左侧边栏可以在View -> Side Bar -> Show Side Bar中打开，可以用Project -> Add Folder to Project...往侧边栏加入常用的文件夹。装完这个插件，侧边栏的右键菜单会多一些功能，挺实用的；
Bracket Highlighter 这是用来做括号匹配高亮的，可以在包管理器中安装。Sublime Text 2自带的括号匹配只有小小的一横线，太不显眼了，这个可以让高亮变成大大的一坨，不过我觉得它大得会盖住光标了；  


#### 代码校验、提示、优化

**SublimeCodeIntel**是sublime text下的一款代码提示插件(特别好用)  
**SublimeLinter**用于高亮提示用户编写的代码中存在的不规范和错误的写法    
**Alignment**  美化对其”=”、”:”这些符号。
**BracketHighlight**代码块括号高亮工具，可以自定义括号颜色。
[![brack.png](http://yalishizhude.github.io/2015/10/20/sublime/brack.png)](http://yalishizhude.github.io/2015/10/20/sublime/brack.png)

**DocBlockr**在函数上一行输入/**然后回车，神奇的事情发生了，jsdoc就生成了。
**HTML-CSS-JS Prettify**html、css、js文件一键优化，但貌似只会优化缩进

##### AngularJS
编写Angular时给出智能提示
##### JQuery
JQueryAPI的智能提示
##### SublimeLinter
##### SublimeLinter-jshint
配合使用，支持js语法规则校验，每个js编写者必备。
# 文件保存预览
##### Local History
非常推荐，智能缓存编辑过的文件，有点像本地版本管理工具。
##### Minifier
手动版js文件压缩工具。


#### Linux 管理：
[Generic Config](https://github.com/skozlovf/Sublime-GenericConfig)： Linux Config 文档的语法高亮。

---
### PHP 必备插件

2. 使用Package Control 搜索SublimeLinter并安装
3. 使用Package Control 搜索SublimeLinter-php并安装
4. 打开Preferences->Package Settings->SublimeLinter->Settings - User，寻找到"paths"并更改代码，确认PHP的Linting引擎，这里的目录是PHP的根目录， 同时在配置文件中路径中的\都要替换为\\ 
```
    "paths": {
        "linux": [],
        "osx": [],
        "windows": [
            "F:\\php\\php-7.0.5-Win32-VC14-x64\\php.exe"
        ]
    },
```
5. 使用Package Control 搜索`phpfmt`并安装，phpfmt的默认键位是`Ctrl+F11`，不喜欢的可以更改键位，通过打开Preferences->Package Settings->phpfmt->Key Budildings - User，再文件中输入以下代码就可以将快捷键轻松设为`Ctrl+Alt+F`，妈妈再也不用担心我的手小了
```
    [    
        { 
            "keys": ["ctrl+alt+f"], "command": "fmt_now" 
        }   
    ] 
```
小结：SublimeLinter是一个代码纠错的插件，它可以自定义纠错的风格，只需要添加php引擎就可以帮助纠错php代码，再也不用担心少个“；”毁灭世界的情况了
phpfmt是一个php的重新排版插件，它可以帮助你在写的乱七八糟以后将代码恢复整齐，强迫症专用。
Sublime 再加上这两个插件，可以让你的php编程变得更加流畅方便

phpfmt配置：
Preferences > Package Settings > phpfmt > Settings - User
我将我的配置贴出来，供大家参考：

    {
        "enable_auto_align":true,//自动调整对齐
        "indent_with_space": true,//自动空格
        "psr1": true,
        "psr2": true,
        "version": 4,
        "php_bin":"D:/wamp/bin/php/php5.6.16/php.exe",//php路径
        "format_on_save":true,//保存的时候自动格式化
        "option": "value"
    }




##### Submine Text 中使用 php-cs-fixer 软件

这个插件是一个综合的插件，安装并配置后，可以很方便的格式化代码。

    {
        "php_cs_fixer_on_save": false,
        "php_cs_fixer_show_quick_panel": true,
        "php_cs_fixer_executable_path": "C:\\vendor\\bin\\php-cs-fixer.bat",
    }


----
