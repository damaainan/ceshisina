## [使用 PHPStorm 与 Xdebug 调试 Laravel (一)](https://segmentfault.com/a/1190000005878593)

## 环境

* 系统版本：OSX 10.11.4
* PHP 版本：7.0.5
* Xdebug 版本：2.4.0
* Laravel 版本：5.1.31
* PHPStorm 版本：10.0.4

## Xdebug 配置

本机的 Xdebug 配置文件位于 `/usr/local/etc/php/7.0/conf.d/ext-xdebug.ini`打开文件添加并以下代码：

    [xdebug]
    zend_extension="/usr/local/Cellar/php70-xdebug/2.4.0/xdebug.so"
    xdebug.idekey=PHPSTORM
    xdebug.remote_enable=1
    xdebug.remote_host=localhost
    xdebug.remote_port=10000
    xdebug.profiler_enable=1
    xdebug.profiler_output_dir="/Users/LuisEdware/Downloads/Xdebug"

## PHPStorm 配置

### PHP 版本和 Interpreter

打开 PHPStorm，首先配置 PHP 的使用版本与 Interpreter

`Preferences => Language & Frameworks -> PHP`，选项如下：

* PHP language level ：选择 PHP 的使用版本
* Interpreter : 配置 PHP 可执行文件的位置
    * Name : 命名
    * PHP executable : PHP 可执行文件位置，本机使用 Homebrew 安装的 PHP，位置在`/usr/local/Cellar/php70/7.0.5/bin/php`


![][0] 

![][1]

### Debug 端口

配置 PHP Debug 时的端口，将端口 `9000` 修改成 `10000`

![][2]

### 新增 PHP Web Application

修改 `Run => Edit configurations`，点击弹出窗口左上角加号，新增一个 `PHP Web Application`

* Name : 命名
* Server : 服务器，没有跟着下个步骤创建
* Start URL : 要开始 Debug 的 URL


![][3] 

![][4]

### 新增 Server

跟随着选项新增一个 `Server`

* Name : 命名
* Host : 主机，我在本地将需要 Debug 的项目映射到 `cowcat.app` 上
* Port : 端口
* Debugger : 除了 Xdebug 还有 Zend Debugger，选择 Xdebug

![][5]

## 设置断点

设置断点，运行`Run => Debug 'Cowcat'` 

![][6] 

![][7]

## Debug 控制台

当浏览器运行指定 URL（就是 PHP Web Application 配置时的 Start URL） 时，出现 Xdebug 控制台，根据控制台的信息和操作进行 Debug

![][8]

控制台的功能介绍如下：

* 左侧绿色三角形 ： `Resume Program`，表示將继续执行，直到下一个中断点停止。
* 左侧红色方形 ： `Stop`，表示中断当前程序调试。
* 上方第一个图形示 ： `Step Over`，跳过当前函数。
* 上方第二个图形示 ： `Step Into`，进入当前函数內部的程序（相当于观察程序一步一步执行）。
* 上方第三个图形示 ： `Force Step Into`，強制进入当前函数內部的程序。
* 上方第四个图形示 ： `Step Out`，跳出当前函数內部的程式。
* 上方第五个图形示 ： `Run to Cursor`，定位到当前光标。
* Variables ： 可以观察到所有全局变量、当前局部变量的数值
* Watches ： 可以新增变量，观察变量随着程序执行的变化。

[0]: ./img/1460000006962726.png
[1]: ./img/1460000006962727.png
[2]: ./img/1460000006962728.png
[3]: ./img/1460000006962729.png
[4]: ./img/1460000006962730.png
[5]: ./img/1460000006962731.png
[6]: ./img/1460000006962732.png
[7]: ./img/1460000006962733.png
[8]: ./img/1460000006962734.png

