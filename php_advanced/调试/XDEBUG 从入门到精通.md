## XDEBUG 从入门到精通

来源：[https://segmentfault.com/a/1190000016325041](https://segmentfault.com/a/1190000016325041)

![][0]
## 前言

Xdebug是PHP的扩展，用于协助调试和开发。


* 它包含一个用于IDE的调试器
* 它升级了PHP的`var_dump()`函数
* 它为通知，警告，错误和异常添加了堆栈跟踪
* 它具有记录每个函数调用和磁盘变量赋值的功能
* 它包含一个分析器
* 它提供了与PHPUnit一起使用的代码覆盖功能。


猿生必备的工具。
但不推荐在生产环境中使用xdebug，因为他太重了。
## 安装
## PECL 安装

```
pecl install xdebug
zend_extension="/usr/local/php/modules/xdebug.so"
```
## 源码包安装

[https://xdebug.org/download.php][24] 寻找对应的包

```
wget xxx.gz
./configure
make && make install
zend_extension="/usr/local/php/modules/xdebug.so"
```
## Docker安装

以下为php dockerfile内部分内容

```
RUN pecl install xdebug
RUN echo 'zend_extension=xdebug.so' >> /usr/local/etc/php/conf.d/xdebug.ini
```
## 工作原理

IDE（如PHPStorm）已经集成了一个遵循BGDP的XDebug插件，当开启它的时候， 会在本地开一个XDebug调试服务，监听在调试器中所设置的端口上，默认是9000，这个服务就会监听所有到9000端口的链接。

![][1]

当浏览器发送一个带 XDEBUG_SESSION_START 的参数的请求到服务器时，服务器接受后将其转到后端的php处理，如果php开启了XDebug模块，则会将debug信息转发到客户端IP的IDE的调试端口上。

![][2]

当参数或者cookie信息中不带 XDEBUG_SESSION_START ，则不会启动调试。这就为后续在浏览器中添加开关提供了可能。
## 基本配置

一般情况下，你都只需了解,无需修改。

| 名称 | 类型 | 默认值 | 注解 |
| - | - | - | - |
| xdebug.default_enable | boolean | 1 | 堆栈跟踪,默认开启,是xdebug基础功能之一 |
| xdebug.force_display_errors | integer | 0 | 默认关闭,如果这个设置设置为1,那么无论PHP的display_errors设置为什么,都将始终显示错误。 |
| xdebug.force_error_reporting | integer | 0 | 默认关闭,允许强制显示某些错误 |
| xdebug.halt_level | integer | 0 | 默认关闭,设置接收某些指定错误 |
| xdebug.max_nesting_level | integer | 256 | 控制无限递归(死循环)的保护机制,默认是256 |
| xdebug.max_stack_frames | integer | -1 | 控制有多少堆栈帧显示在堆栈跟踪中，在PHP错误堆栈跟踪的命令行中，以及在浏览器中显示HTML跟踪。 |
| xdebug.scream | boolean | 0 | 默认关闭,如果该设置为1，那么Xdebug将禁用@(关闭)操作符，以便不再隐藏通知、警告和错误。 |


具体内容请移步官网 [https://xdebug.org/docs/basic][25]
## 打印配置

Xdebug将替换PHP的var_dump()函数来显示变量。Xdebug版本包含了不同类型的不同颜色，并对数组元素/对象属性的数量、最大深度和字符串长度进行了限制。还有一些其他函数也处理变量显示。

| 名称 | 类型 | 默认值 | 注解 |
| - | - | - | - |
| xdebug.cli_color | integer | 0 | cli模式下输入结果是否设置颜色 |
| xdebug.overload_var_dump | boolean | 2 | 是否允许xdebug重载var_dump函数 |
| xdebug.var_display_max_children | integer | 128 | var_dump 对数组对象子级的显示层数限制 |
| xdebug.var_display_max_data | integer | 512 | var_dump 对结果长度的限制 |
| xdebug.var_display_max_depth | integer | 3 | var_dump 对默认显示嵌套的层数的限制 |


具体内容请移步官网 [https://xdebug.org/docs/display][26]
## 堆栈跟踪配置

当Xdebug被激活时，当PHP决定显示一个通知、警告、错误等时，它将显示一个堆栈跟踪。堆栈跟踪显示的信息以及它们的显示方式可以配置为适合您的需要。

| 名称 | 类型 | 默认值 | 注解 |
| - | - | - | - |
| xdebug.cli_color | integer | 0 | cli模式下输入结果是否设置颜色 |
| xdebug.collect_includes | boolean | 1 | 控制Xdebug是否应该将include()、include_once()、require()或require_once()中的文件名写入跟踪文件 |
| xdebug.collect_params | integer | 0 | 该设置默认为0，控制当函数跟踪或堆栈跟踪记录函数调用时，Xdebug是否应该收集传递给函数的参数 |
| xdebug.collect_vars | boolean | 0 | 这个设置告诉Xdebug在特定范围内使用哪些变量。由于Xdebug必须逆向工程PHP的操作码数组，这种分析可能相当缓慢。对于使用xdebug.collect_params，此设置不会记录不同变量的值。只有当您希望使用xdebug_get_declared_vars()时，才需要启用此设置。 |
| xdebug.dump.* | string | empty | * 可以是任何COOKIE、文件、GET、POST、REQUEST、SERVER、SESSION。这七个设置控制在发生错误时显示来自超全局变量的哪些数据。 |
| xdebug.dump_globals | boolean | 1 | 当该设置设置为true时，Xdebug将添加通过Xdebug.dump配置的超级全局变量的值*到屏幕上的堆栈跟踪和错误日志。 |
| xdebug.dump_once | boolean | 1 | 控制是否应该在所有错误情况(设置为0)上转储超全局变量的值，或只在第一个错误情况下转储超全局变量的值(设置为1) |
| xdebug.dump_undefined | boolean | 0 | 如果您想从超全局变量中转储未定义的值，您应该将该设置设置为1，否则将其设置为0。 |
| xdebug.file_link_format | string |  | 文件链接格式 |


具体内容请移步官网 [https://xdebug.org/docs/stack...][27]
## 函数调试配置

Xdebug允许记录所有函数调用，包括参数和以不同格式返回的值。

| 名称 | 类型 | 默认值 | 注解 |
| - | - | - | - |
| xdebug.auto_trace | boolean | 0 | 当将此设置设置为ture时，将在脚本运行之前启用函数调用的跟踪 |
| xdebug.collect_assignments | boolean | 0 | 该设置默认为0，控制Xdebug是否应该向函数跟踪添加变量赋值。 |
| xdebug.collect_includes | boolean | 1 | 该设置默认为1，控制Xdebug是否应该将include()、include_once()、require()或require_once()中的文件名写入跟踪文件。 |
| xdebug.collect_params | integer | 0 | 该设置默认为0，控制当函数跟踪或堆栈跟踪记录函数调用时，Xdebug是否应该收集传递给函数的参数。 |
| xdebug.collect_return | boolean | 0 | 该设置默认为0，控制Xdebug是否应该将函数调用的返回值写入跟踪文件。 |
| xdebug.show_mem_delta | integer | 0 | Xdebug生成的跟踪文件将显示函数调用之间内存使用的差异 |
| xdebug.trace_format | integer | 0 | 跟踪文件的格式 |
| xdebug.trace_options | integer | 0 | 当设置为“1”时，跟踪文件将被附加到后面的请求中，而不是被覆盖。 |
| xdebug.trace_output_dir | string | /tmp | 写入跟踪文件的目录，确保PHP运行的用户具有该目录的写权限。 |


具体内容请移步官网 [https://xdebug.org/docs/execu...][28]
## 垃圾收集统计信息

Xdebug的内置垃圾收集统计信息分析器允许您查明PHP内部垃圾收集器何时触发、它能够清理多少变量、它花费了多长时间以及实际释放了多少内存。

| 名称 | 类型 | 默认值 | 注解 |
| - | - | - | - |
| xdebug.gc_stats_enable | bool | false | 如果启用此设置，则垃圾收集运行的统计信息将自动收集到使用xdebug.gc_stats_output_dir设置的给定目录中， 并使用由xdebug.gc_stats_output_name配置的自动生成的名称。 |
| xdebug.gc_stats_output_dir | string | /tmp | 将写入垃圾收集统计信息输出的目录，确保将运行PHP的用户具有对该目录的写入权限。无法使用ini_set() 在脚本中设置此设置。 |
| xdebug.gc_stats_output_name | string | gcstats.%p | 此设置确定用于将垃圾回收统计信息转储到的文件的名称。该设置使用格式说明符指定格式，与sprintf() 和strftime() 非常相似。有几种格式说明符可用于格式化文件名。 |


具体内容请移步官网 [https://xdebug.org/docs/garba...][29]
## 远程调试配置

Xdebug为与运行PHP脚本交互的调试器客户机提供了一个接口。

| 名称 | 类型 | 默认值 | 注解 |
| - | - | - | - |
| xdebug.extended_info | integer | 1 | 控制Xdebug是否应该为PHP解析器强制执行'extended_info'模式; 这允许Xdebug使用远程调试器执行文件/行断点。在跟踪或分析脚本时，您通常希望关闭此选项，因为PHP生成的oparray将增加大约三分之一的大小，从而减慢脚本速度。无法使用ini_set（）在脚本中设置此设置，但只能在php.ini中设置。 |
| xdebug.idekey | string | complex | 控制哪些IDE Key Xdebug应传递给DBGp调试器处理程序。则与客户端通信的key |
| xdebug.remote_addr_header | string | "" | 该值将用作$SERVER超全局数组中的键，以确定用于查找用于“连接回”的IP地址或主机名的标头 |
| xdebug.remote_autostart | boolean | 0 | 您需要使用特定的HTTP GET / POST变量来启动远程调试 |
| xdebug.remote_connect_back | boolean | 0 | 如果你不设置ip地址，就只能靠xdebug自己找了，Xdebug将尝试连接到发出HTTP请求的客户端。它检查$_SERVER['HTTP_X_FORWARDED_FOR']和$_SERVER['REMOTE_ADDR']变量以找出要使用的IP地址 |
| xdebug.remote_cookie_expire_time | integer | 3600 | 设置cookie的生命周期 |
| xdebug.remote_enable | boolean | 0 | 是否开启远程调试 |
| xdebug.remote_handler | string | dbgp | 调试的通信协议 |
| xdebug.remote_host | string | localhost | 调试的网络地址，开启了remote_connect_back后，当前配置就无效了 |
| xdebug.remote_log | string |  | 调试的日志 |
| xdebug.remote_port | integer | 9000 | 调试的端口 |
| xdebug.remote_timeout | integer | 200 | 调试的通信链接的等待时间 |


具体内容请移步官网 [https://xdebug.org/docs/remote][30]
## 官方链接

全部配置请移步官网 [https://xdebug.org/docs/all_s...][31]
全部函数请移步官网 [https://xdebug.org/docs/all_f...][32]
## PHPSTORM DEBUG

网络上的配置DEBUG文章太杂太乱,在PHPSTROM跳来跳去的,实在让人心烦意乱,本章重新整理描述。
## 配置

其实在PHPSTROM中已经出了很详细的教程，是吧？只不过大部分人都会直接百度/Google，查询是个好习惯，但要视情况而定。

Languages & Frameworks > PHP > Debug![][3]
### 第一步

下载XDEBUG扩展，本章开头已经讲过了，这里不再阐述。
在安装完成后，PHPSTROM提供了验证脚本。脚本地址为 [https://gist.github.com/litzi...][33]
正常情况下检测成功的样子是

![][4]

看到这个你就可以放心的去debug了。
### 第二步

装个浏览器插件，用户请求监听。这个东西唯一的作用就是方便了debug,正常如果不安装的话你需要通过GET传参`  XDEBUG_SESSION_START=ID_KEY `才可以，安装插件后就不需要了。

![][5]

你可以自行选择是否开启debug，以下为浏览器插件列表

| 浏览器 | 下载 |
| - | - |
| Chrome | [Xdebug Helper][34] |
| Firefox | [The easiest Xdebug][35] or [Xdebug Helper][36] |
| Safari | [Xdebug Toggler][37] |
| Opera | [Xdebug launcher][38] |
| Internet Explorer | [PhpStorm bookmarklets generator][39] |


### 第三步

开启监听即可。

![][6]

你可以直接点击图片上的监听按钮。

到现在为止，就配置好了IDE。
### 第四步

创建DEBUG配置文件，根据每个项目都要配置。这步你跑不了。

![][7]

你看，PHPSTORM非常人性化，在每一步都会给你出个简单的教程来描述如何配置DEBUG。

添加一个Server和IDE key(xdebug.idekey) 就可以进行调试了。
### 总结

总结以下IDE配置DEBUG的步骤，实际很简单。


* 下载安装debug
* 验证是否通信正常
* 添加浏览器插件 (可省略)
* 配置运行文件
* 愉快的debug


这里还要说明下，如果你是不需要访问本机调试一些类或者代码块，你只需要在本机上安装好XDEBUG，上述配置全部省略，直接点小虫子图标即可调试。DEBUG端口默认是9000，如果配置文件内修改了，当然在IDE内也需要修改下。## 调试

介绍下实际DEBUG过程中PHPSTORM面板的按钮都是做什么用的。当你启动监听后，你将会看到如下图

![][8]

根据上图图标位置，以x,y坐标的方式描述每个图标的功能。

| 图标 | 位置 (x,y) | 功能 |
| - | - | - |
| ![][9] | 0,0 | 重新执行DEBUG (Rerun test.php) |
| ![][10] | 0,1 | 跳转到原调试文件 (Show execution point) |
| ![][11] | 0,2 | 跳到下一行 (Step over) |
| ![][12] | 0,3 | 执行下一步 (Step info) |
| ![][13] | 0,4 | 跳入库、构造函数等方法或类中 (Force Step Info) |
| ![][14] | 0,5 | 执行函数体或类方法,如果在最外层则结束本次调试 (Step out) |
| ![][15] | 0,6 | 跳到当前节点的下个断点 (Run to Cursor) |
| ![][16] | 0,7 | 执行修改变量或返回的结果用于二次调试(Evaluate arbitrary expression) |
| ![][17] | 0,8 | 显示值的地址 (Show Values Addresses) |
| ![][18] | 0,9 | 是否显示空的值 , 默认是不显示 |
| ![][19] | 0,10 | 跳到下个断点 (Add method to skip list) |
| ![][20] | 1,0 | 重新执行DEBUG (Resume Program) |
| ![][21] | 2,0 | 停止DEBUG(Step process) |
| ![][22] | 3,0 | 查看和管理所有的断点 ( View and manage all breakpoints) |
| ![][23] | 4,0 | 忽略所有断点 (Mute breakpoints) |


## 其他
## Docker PHP XDEBUG

部分人在本机装了Docker,容器中运行这PHP，这个时候该如何进行DEBUG呢？如果认真看本篇文章，你会发现十分的简单。本身XDEBUG就是远程调试。首先你要保证


* 容器做了端口映射到本机80
* 容器内项目目录做了磁盘挂载


将容器PHP XDEBUG 中

```ini
xdebug.remote_host=local_ip
```

即可，也不用担心hosts文件的问题

```
本机IP = 127.0.0.1 = localhost
```

这样配置完成后，就可以当容器不存在，与本机调试一样。
## 致谢

感谢你看到这里，希望本章内容可以帮到你，谢谢。

[24]: https://xdebug.org/download.php
[25]: https://xdebug.org/docs/basic
[26]: https://xdebug.org/docs/display
[27]: https://xdebug.org/docs/stack_trace
[28]: https://xdebug.org/docs/execution_trace
[29]: https://xdebug.org/docs/garbage_collection
[30]: https://xdebug.org/docs/remote
[31]: https://xdebug.org/docs/all_settings
[32]: https://xdebug.org/docs/all_functions
[33]: https://gist.github.com/litzinger/8fd06ab6e5033b7ff8d8
[34]: https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc
[35]: https://addons.mozilla.org/en-US/firefox/addon/the-easiest-xdebug/
[36]: https://addons.mozilla.org/en-US/firefox/addon/xdebug-helper-for-firefox/
[37]: https://github.com/benmatselby/xdebug-toggler
[38]: https://addons.opera.com/zh-cn/extensions/details/xdebug-launcher/
[39]: https://www.jetbrains.com/phpstorm/marklets/
[0]:./img/bVbgEC5.png
[1]:./img/bVbgEC2.png
[2]:./img/bVbgEC3.png
[3]:./img/bVbgBAE.png
[4]:./img/bVbgBJn.png
[5]:./img/bVbgBC3.png
[6]:./img/bVbgBKf.png
[7]:./img/bVbgBLh.png
[8]:./img/bVbgByN.png
[9]:./img/bVbgCPH.png
[10]:./img/bVbgCPO.png
[11]:./img/bVbgCPQ.png
[12]:./img/bVbgCPV.png
[13]:./img/bVbgCPZ.png
[14]:./img/bVbgCP1.png
[15]:./img/bVbgCQd.png
[16]:./img/bVbgCQe.png
[17]:./img/bVbgCQf.png
[18]:./img/bVbgCQi.png
[19]:./img/bVbgCQk.png
[20]:./img/bVbgDq5.png
[21]:./img/bVbgDra.png
[22]:./img/bVbgDrc.png
[23]:./img/bVbgDre.png