> 过程比较简单

#### 1、添加 php 的 xDebug 扩展



#### 2、php.ini 配置中开启 Profiler（性能跟踪器）

Profiler工作方式类似于函数追踪，是在脚本程序运行时自动将性能记录文件保存下来。

[php.ini]

```ini
xdebug.profiler_enable = 1
xdebug.profiler_enable_trigger = 1
```

#### 3、下载 webgrind



所以需要下载 **图形界面的分析工具** ，Linux KDE 可以用 KChaceGrind，windows 下可以用 winChaceGrind ，Mac 上没有发现合适的桌面端软件，所以用 Web 版的 webgrind 是最好的选择。

Github 地址：[https://github.com/jokkedk/webgrind][2]

下载后直接放在本地服务器根目录直接访问就行：

[http://localhost/webgrind][3]

#### 4、使用方法

把需要分析的 url 后面接上 ==?XDEBUG_PROFILE==，例如：

[http://localhost/XXX/index.php/PriceApply/getPrice?XDEBUG_PROFILE][4]

然后刷新 webgrind 网页，新的数据会出现：

![][5]

下面介绍下 webgrind 的一些参数含义：

![][6]

第一个选项：webgrind 把所有被调用函数/方法首先做一个排序，由高到低显示。然后取出前 N 个，使他们耗时比率之和在 90-100% 之间。

> 要注意的是，最好不要选择100%，这样将会显示所有被调用的函数/方法，如果是一个代码复杂的页面，那么webgrind偶尔会被卡死。并且通常我们只要关注耗时前几 > 名的函数即可。

第二个选项：选择 profile 文件。**默认是分析最新一次的 xdebug 记录**。如果之前设置好路径和记录机制那么我们就会发现下拉列表里有很多选项。

第三个选项：显示百分比/毫秒/微秒。

**彩色进度条：蓝代表 php 内置函数，灰代表 require/include，绿代表类方法，橙黄代表过程函数 (用户自定义函数)**

![][7]

invocation count - 表示整个 php 页面从载入到执行完毕呈现，各种函数被调用的总次数  
total self cost - 表示函数自身消耗  
total inclusive cost - 表示此函数从开始到执行完毕所用消耗 ，包括自身消耗和调用其他函数消耗

点击一个父函数名后出现展开：

Calls - 此函数中调用并执行的所有函数/方法名、次数及耗时  
Total Call Cost - 被此父函数调用时，执行的总耗时  
Count - 被此父函数调用时，执行的次数

#### 5、分析数据


[2]: https://github.com/jokkedk/webgrind
[3]: http://localhost/webgrind
[4]: http://localhost/XXX/index.php/PriceApply/getPrice?XDEBUG_PROFILE
[5]: ../img/1604410264.png
[6]: ../img/1029720322.png
[7]: ../img/348726502.png