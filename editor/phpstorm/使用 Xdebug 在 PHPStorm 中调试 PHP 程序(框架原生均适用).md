## [使用 Xdebug 在 PHPStorm 中调试 PHP 程序(框架/原生均适用)](https://segmentfault.com/a/1190000011387666)

## 序言

Xdebug 作为 PHP 调试工具，提供了丰富的调试函数和配置，可以直观的看到 PHP 源代码的步进和性能数据，以便优化PHP代码。

使用 phpstorm + xdebug 来调试 php 程序是借助强大的IDE监听功能, 更方便的调试程序. 提高我们的编码效率, 固然 `var_dump`, `print_r` 等函数也能提供相应的功能, 但是自动化的工具更能够事半功倍. 下面我根据自己的使用介绍下如何进行调试和配置. 

一种方式是用外部设置的 session, 另外一种是在 phpstorm 中配置页面入口然后使用内置的监听来访问, 原理相同, 下面我们从原理开始讲解

## 调试原理

![][0]

## 配置调试环境

### 1) 配置 xdebug

这里使用了最小化配置, 对于 profile 等功能没有配置

    [xdebug]
    zend_extension="/usr/local/opt/php70-xdebug/xdebug.so"
    xdebug.remote_enable=1          # 启用远程调试
    xdebug.remote_connect_back=1    # 忽略 remote_host 配置, 不关注主机配置, 开发者使用最舒服
    xdebug.remote_port=9050         # 监听端口

**注意** 这里监听端口默认是 9000 , 和 php 默认监听重复, 注意尽量不用使用 9000, 以免出现不生效的情况.

### 2) 设置 phpstorm 配置并开启监听

这里是让 phpstorm 通过监听端口的方式获取到xdebug 断点传送过来的数据

#### 2.1) 配置端口

我们这里监听的是 9500 端口, 和 xdebug 配置监听数据端口一致

![][1]

#### 2.2) 开启phpstorm 数据监听

切换 “开始监听PHP调试连接” 按钮。

![][2]

![][3]

### 3) 在 phpstorm 中设置断点

点击行号右侧空白, 设置断点

![][4]

### 4) 设置 debug session

debug session 的工具的目的是设置一个cookie, 让每次发送数据的时候都会携带这个 cookie, 从而识别监听.

#### 4.1) 安装工具

安装 chrome 扩展 [Xdebug helper][5]

![][6]

#### 4.2) 点击 图标设置session

![][7]

已经设置了cookie, Key 是 `XDEBUG_SESSION`, 值是 `PHPSTORM`, 我认为这里的值无关紧要, 对于 phpstorm 来说, 是能够监控到的. 

![][8]

### 5) 运行页面

这里我们在断点位置可以看到输出的内容项目

![][9]

## 另一种方式: 内部调用

这里的另外一种方式的服务器配置方式和流程完全一致, 就是第四步和第五步有所不同, 实现的原理是在phpstorm中设置运行的服务器, 然后通过 debug 模式自动设置 `XDEBUG_SESSION`, 并且自动开启监听.

### 内部调用: 4) 设置 debug session

#### 4.1) 设置 web 访问的服务器

例如我这里的本地域名是 `l.dailian.sour-lemon.com`, 我们需要配置一个本地服务器来打开这个页面, 我们首先配置一台服务器. 

![][10]

**注意** 这里的配置的域名是你本地已经配置好开发环境的域名, 端口号是 本地开发所使用的端口, 我这里是 `l.dailian.sour-lemon.com` 和 80

#### 4.2) 配置调试页面

我们这里创建的调试页面的类型是 `PHP Web Application`, 服务器选择的是刚才已经建立好的服务器

![][11]

### 内部调用: 5) 运行测试页面

这样运行的情况下上面的 `2.2) 开启phpstorm 数据监听` 步骤可以忽略掉, 这里不需要开启这个监听.

#### 5.1) 开始 debug

点击 debug 按钮, 这里会自动打开一个页面并且传递一个唯一的ID(可能是进程 ID)作为 debug 值

![][12]

打开的url地址是: `http://l.dailian.sour-lemon.com/?XDEBUG_SESSION_START=13608`, 这里的数值是会变动的.

#### 5.2) 查看 debug 面板

打开 debug 面板, 会看到相对应的监听 idekey, 这里和上一步设置的key是一致的, 同样也和 cookie 中的设置的 `XDEBUG_SESSION` 值一致

![][13]

![][14]

## 其他帮助

### 1. 查看兼容性

第一次运行的时候可以通过 phpstorm 自带的工具来检查配置的兼容性. 

`Run > Web Server Debug Validation `

![][15]

### 2. debug 帮助面板说明

![][16]

**左侧**  
绿色三角形 ： `Resume Program`，表示將继续执行，直到下一个中断点停止。  
红色方形 ： `Stop`，表示中断当前程序调试。

**上方**  
第一个图形示 ： `Step Over`，跳过当前函数。  
第二个图形示 ： `Step Into`，进入当前函数內部的程序（相当于观察程序一步一步执行）。  
第三个图形示 ： `Force Step Into`，強制进入当前函数內部的程序。  
第四个图形示 ： `Step Out`，跳出当前函数內部的程式。  
第五个图形示 ： `Run to Cursor`，定位到当前光标。

**框架说明**  
Frames : 加载的文件列表  
Variables ： 可以观察到所有全局变量、当前局部变量的数值  
Watches ： 可以新增变量，观察变量随着程序执行的变化。

## 参考文章

* [Zero-configuration Web Application Debugging with Xdebug and PhpStorm][17]
* [使用 PHPStorm 与 Xdebug 调试 Laravel (一)][18]

[0]: ./img/1460000011387669.png
[1]: ./img/1460000011387670.png
[2]: ./img/1460000011387671.png
[3]: ./img/1460000011387672.png
[4]: ./img/1460000011387673.png
[5]: https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc
[6]: ./img/1460000011387674.png
[7]: ./img/1460000011387675.png
[8]: ./img/1460000011387676.png
[9]: ./img/1460000011387677.png
[10]: ./img/1460000011387678.png
[11]: ./img/1460000011387679.png
[12]: ./img/1460000011387680.png
[13]: ./img/1460000011387681.png
[14]: ./img/1460000011387682.png
[15]: ./img/1460000011387683.png
[16]: ./img/1460000011387684.png
[17]: https://confluence.jetbrains.com/display/PhpStorm/Zero-configuration+Web+Application+Debugging+with+Xdebug+and+PhpStorm#Zero-configurationWebApplicationDebuggingwithXdebugandPhpStorm-2.PreparePhpStorm
[18]: https://segmentfault.com/a/1190000005878593