## MobaXterm：“十项全能”的远程终端登录软件 【开源硬件佳软介绍 #1】

来源：[https://segmentfault.com/a/1190000000483148](https://segmentfault.com/a/1190000000483148)

提到SSH、Telnet等 **`远程终端登录`** ，我相信很多人想到的都是[PuTTY][9]`[注A]`。


PuTTY足够成熟、小巧、专注核心任务，并且对编码等常见坑的处理并不缺乏，这其实都是优点。但PuTTY在额外功能上就同时缺了一些，例如直接SFTP文件传输、标签页切换等。


所以这里推荐一款豪华、全功能的终端软件MobaXterm。它不仅可以像PuTTY一样通过SSH连接Raspberry Pi等开源硬件，并且还能：


* 直接的便携版
* 内建多标签和多终端分屏
* 内建SFTP文件传输
* 内建X server，可远程运行X窗口程序
* 直接支持VNC/RDP/Xdmcp等远程桌面
* 默认的UTF-8编码
* 更加友好的串口连接设置
* 操作更明确，更少的“神秘技巧”


## 下载与安装


[MobaXterm官方网站][10]提供MobaXterm的[开源免费版“Home Edition”下载][11]`[注B]`。你可以直接下载普通的 **`安装版`** ，或者用`.ini`存储配置的 **`绿色便携版`** 。


对比而言，如果需要PuTTY的便携版，就需要专门去找PuTTY的分支项目“[PuTTY File][12]”。

## 内建多标签和多终端分屏


MobaXterm内置 **`多标签页`** 、 **`横向纵向2分屏`** 和 **`田字形4分屏`** ，用于一个窗口内管理多个连接。管理多台服务器不必开多个窗口。


![][0] 

![][1] 

▲ 标签页浏览与四分屏

## 内建SFTP文件传输


如果用SSH连接远程主机，则左侧就会自动启动SFTP连接，列出服务器上的文件列表，无需任何配置。


可以直接上传下载，更方便的是，还可以让文件列表的当前目录，直接跟随终端当前目录同步切换！


![][2] 

▲ 跟随SSH连接同步启动的SFTP连接（见左边栏）


对比而言，一般需要另行使用FileZilla/WinSCP等第三方SFTP工具。

## 内建X server


MobaXterm内建了一个X server，可以直接执行远程端的X窗口程序。也是随着SSH连接自动发挥作用，无需任何配置。


这一点对于Raspberry Pi等资源贫乏的设备很有意义——这样就无需启动完整的LXDE等桌面环境，也无需准备笨重的VNC等远程桌面服务器。我们可以用最小的资源消耗，达到远程执行图形程序的效果。


![][3] 

▲ 远程连接Raspberry Pi并运行Midori浏览器


对比而言，一般需要另行使用Xming等第三方X server，并在PuTTY中配置X11映射。

## 直接支持VNC/RDP/Xdmcp远程桌面


如果真的需要完整的远程桌面了，也无需多种客户端，一个软件即可对付所有的需求。Windows服务器管理员特别推荐。


![][4] 

▲ MobaXterm所有支持的连接方式

## 默认UTF-8编码


SSH和SFTP都默认采用UTF-8编码，无需设置，多语言均不乱码。Linux爱好者福音。


![][5] 

▲ 左侧的SFTP和右侧终端，无需设置均不乱码


对比而言，PuTTY中需要手动改编码。而SecureCRT等部分其他SSH客户端，甚至不允许更改编码，乱码不可避免。

## 更加友好的串口连接设置


MobaXterm不仅支持串口连接，并且直接提供下拉框选择串口号和波特率，选择串口号时还会自动显示串口设备的名称。这一点对于开源硬件玩家是相当幸福的。


![][6] 

▲ 可以友好选择的串口连接界面


对比而言，PuTTY的设计就极其令人发指：串口号和波特率都只有一个文本框手工输入，往往需要用户自己去费劲检查设备管理器……

## 操作更明确，更少的“神秘技巧”


明确的菜单命令和文字提示，用户友好。复制、粘贴、断线重连等常见行为很容易找到，不需要“教程”或口口相传的“暗示”。


![][7] 

▲ 复制粘贴直接在右键菜单里

![][8] 

▲ 断线重连有直接的命令提示


对比而言，PuTTY中复制和粘贴分别是“选中文字后点左键”和“任意地方点右键”，断线重连等功能必须在标题栏上点右键才能出菜单。所有这些玩法都极其的依赖暗示，不合乎任何规范，也不显而易见。

介绍就是这些，欢迎大家试用MobaXterm去连接和操作自己的服务器或开源硬件设备。`[注C]`
-----

## 注解


[注A] PuTTY通常用于Windows，但实际上可以多平台运行，因此不表达为“Windows下的远程终端登录”。

  [注B] MobaXterm Home Edition的授权方式很奇怪——本身是GPLv3的，但官方发布的版本却像Shareware一样，对可保存的配置数量等多种参数[加入了限制][13]。

  这些限制官方建议购买他们的"Professional"收费版来去除，可是GPL下拆掉这些限制没有难度啊，官方为什么做这个无用功？

  [注C] 我们通过 VirSCAN.org 扫描了MobaXterm 7.1不含病毒，但并未审查MobaXterm的代码。

  如果您特别在意安全问题，请自行查询他人的审查结论，或者自行组织代码审查。

## 《开源硬件佳软介绍》系列文章


《开源硬件佳软介绍》系列文章，介绍调试Raspberry Pi等开源硬件板卡，所用到的各种优秀软件。

  ——当然这些软件的用途也不限于开源硬件（本次介绍的MobaXterm就是如此），所以也欢迎所有开发者阅读！
 **`每周五`** 更新，敬请期待！

  
下一篇：[USB Image Tool：Windows下的直接写盘利器 【开源硬件佳软介绍 #2】][14]

-----

《USB Image Tool：Windows下的直接写盘利器》 [http://segmentfault.com/a/1190000000483148][15]

SegmentFault原创内容。

转载使用请遵守本站相关声明。

本文作者与责任：沙渺

[9]: http://www.chiark.greenend.org.uk/~sgtatham/putty/
[10]: http://mobaxterm.mobatek.net/features.html
[11]: http://mobaxterm.mobatek.net/download-home-edition.html
[12]: http://jakub.kotrla.net/putty/
[13]: http://mobaxterm.mobatek.net/download.html
[14]: http://segmentfault.com/a/1190000000492510
[15]: http://segmentfault.com/a/1190000000483148
[0]: ../img/bVcbPG.png
[1]: ../img/bVcbPH.png
[2]: ../img/bVcbPP.png
[3]: ../img/bVcbPX.png
[4]: ../img/bVcbPZ.png
[5]: ../img/bVcbQa.png
[6]: ../img/bVcbQh.png
[7]: ../img/bVcbQP.png
[8]: ../img/bVcbQR.png