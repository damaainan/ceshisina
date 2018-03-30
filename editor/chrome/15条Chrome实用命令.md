## 这 15 条实用命令，帮你打开 Chrome 浏览器的隐藏功能

来源：[http://sspai.com/post/43853](http://sspai.com/post/43853)

时间 2018-03-28 16:00:25

**`chrome://version`**   
**`chrome://flags`**   
**`chrome://settings`**   
**`chrome://extensions`**   
**`chrome://net`**   
**`chrome://components`**   
**`chrome://translate`**   
**`chrome://quit`**   
**`chrome://restart`**   
**`chrome://about`**   
**`chrome://downloads`**   
**`chrome://history`**   
**`chrome://apps`**   
**`chrome://bookmarks`**   
**`chrome://dns`**   
**`chrome://devices`**   
 
作为主力浏览器，支持相当丰富的第三方扩展，其实浏览器本身也内置了大量实用的命令。通过下面整理的 Chrome 命令，将会让用户实现快速查询信息的目的，比如：查询浏览器的用户配置文件存储位置、实验阶段的功能选项，甚至是集中显示浏览器支持的所有的命令的详细列表。下面，我们甄选了几个实用的 Chrome 命令：
 
### 显示当前版本 
 
浏览器地址栏输入并打开`chrome://version`，页面显示了当前浏览器版本的详细信息（比如我安装的 v66.0.3359.45 Dev 版本），本地操作系统类型，JavaScript、Flash 软件的具体版本和文件存放位置。
 
![][0]
这一页面中有两处实用的信息：
 
![][1]

命令行。以我的显示信息为例子：`"C:\Users\XX\AppData\Local\Google\Chrome\Application\chrome.exe" --flag-switches-begin --disable-accelerated-video-decode --enable-features=MaterialDesignBookmarks,OverlayScrollbar --flag-switches-end`，引号部分为 Chrome 本地安装的目标位置，引号后面的内容则显示用户在功能特性界面自定义修改过的内容，比如我开启了 Material Design 风格的书签页面、自定义修改了浏览器本身的滚动条样式表现。
 
个人资料路径，这个文件路径就是大家经常说的用户配置文件。下面截图显示的路径为 `C:\Users\XX\AppData\Local\Google\Chrome\User Data\Default`，Default 文件夹就是默认的 Chrome 配置文件夹，如果用户创建了多个 Chrome 用户，会有以 Profile N （N 代表从 1 开始的数字）为方式命名的文件夹。在重装电脑之前将个人资料路径的文件拷贝一份，下次重装电脑间接实现快速备份 Chrome 浏览器的个人配置信息。
 
### 实验项目 
 
输入`chrome://flags`，这个命令将打开 Chrome 浏览器的功能特性界面，我们可用来启用或者关闭某些 Chrome 的实验功能。flags 页面按照 Available、Unavailable 两种标签页显示项目，如果用户明确知道需要查找的实验项目，可以使用顶部的搜索栏，或者在地址栏直接输入 `chrome://flags/#(项目名称)`，比如我要查找 overlay-scrollbars 项，那么只需要输入 `chrome://flags/#overlay-scrollbars`，即可直接定位到目标选项。
 
![][2]
经过粗略统计，flags 实验项目拥有近 200 多项，里面包括了对滚动条、Omnibar 地址栏、书签管理器样式、导入/导出保存的密码信息等内容，大部分选项提供了 Disable 禁止、Enable 开启或者 Default 默认三种状态。
 
![][3]
目前我对其中的三个选项手动进行了设置，分别是打开了 Overlay Scrollbars 和 Enable Material Design bookmarks（实现改变滚动条和书签的表现样式），关闭了 Hardware-accelerated video decode（为了解决与某个 Chrome 扩展有冲突的问题）。
 
### 设置页面 
 
输入`chrome://settings`将快速打开 Chrome 浏览器的设置页面，页面的内容分类划分为基础和高级设置选项，基础项细分为其他人、外观、搜索引擎、默认浏览器、启动时，高级项有隐私设置和安全性、密码和表单、语言、下载内容、打印、无障碍、系统、重置并清理，最后一个就是「关于 Chrome」的选项。
 
![][4]
每个细分选项通过类似`chrome://settings/xx`命令来快速定位，下面是对应命令：
 
![][5]

### 扩展程序页面 
 
输入`chrome://extensions`，这个命令方便取代以往进入两三级菜单才能打开浏览器已安装的扩展程序页面（需打开「菜单 - 更多工具 - 扩展程序」），扩展页面的常规功能包括了打开/关闭开发者模式、手动加载/更新扩展、搜索安装的扩展程序、手动关闭/打开/删除某个扩展。另外，页面侧边栏还隐藏了键盘快捷键的页面，用户同样可在地址栏输入 `chrome://extensions/shortcuts`快速打开，集中管理用户为每个扩展设置的键盘快捷键组合，以及设置是否在全局或者只在 Chrome 本身激活快捷键。
 
![][6]

### 显示网络事件信息 
 
输入`chrome://net-internals`后打开一个显示网络相关信息的页面，这个命令主要用来捕获浏览器生成的网络事件，默认会显示当前连接的网络服务事件，可导出数据、查看 DNS 主机解析缓存。
 
![][7]

### 查看组件信息 
 
输入`chrome://components`，这个命令显示 Chrome 浏览器所有用到的组件，在这里可以查看常用的 Flash 组件的版本，并检查是否有更新。
 
![][8]

### 查看哪些网页被禁止翻译 
 
输入`chrome://translate-internals`，打开浏览器内置翻译功能的页面，显示了页面是什么语言的情况下不提示翻译、哪些页面不再提示翻译、以及哪些语言组合是提示翻译，用户还可以手动关闭哪些以前设置过的翻译选项。
 
![][9]

### 退出和重启浏览器 
 
注意，首先这个不要着急输入这两条命令`chrome://quit`、`chrome://restart`，它们分别可以实现退出和重启浏览器，其中重启命令间接实现了一键重启浏览器的目的。
 
### 查看所有的命令列表 
 
用户如果还对如何找到这些浏览器命令感到困惑的话，输入`chrome://about`命令，将集中列出 Chrome 浏览器支持的所有的命令，分为了 Chrome URLs 以及 Debug 用途的命令。
 
![][10]

#### 其他常用的 Chrome 命令还包括了：

 `chrome://downloads`：直接访问 Chrome 浏览器网页下载的文件。
 `chrome://history`：直接访问 Chrome 浏览器访问的历史记录。
 `chrome://apps`：访问 Chrome 浏览器中安装的应用的界面，可以对应用进行删除管理。
 `chrome://bookmarks`：直接访问 Chrome 浏览器中我们收藏的标签。
 `chrome://dns`：显示浏览器预抓取的主机名列表，让用户随时了解 DNS 状态。
 `chrome://devices`：查看连接电脑的设备，比如传统打印机中，可设置添加打印机到 Google 云打印的入口。
 
### 另类的命令扩展：Steward 
 
[Steward][12] 号称是 Chrome 浏览器里类  [Alfred][13] 启动器，用户可以使用 `off |all`（禁用所有扩展）、`on 扩展名称`（启用某个扩展）、`bk 网址名称`（屏蔽某个网站）、`todo 内容`（建立代办事项）等快捷命令来快速实现某些功能，甚至利用扩展内置的 workflow 设置来创建属于自己的工作流。在这里简要介绍 Steward 内置的 chrome 命令，在调用扩展的搜索栏输入 `chrome`，Steward 将会自动显示目前支持的命令，用户无需记住完整的浏览器命令。
 
![][11]
扩展阅读： [在 Chrome 里用「Alfred」是什么体验？Steward 把效率启动器带进了浏览器][14]
 
### 用好 Chrome 命令来提升效率 
 
Chrome 内置的命令支持快速打开某些页面，无需繁琐地打开多级菜单（快捷键也能提升打开效率）才能进入诸如扩展、书签管理器等页面。同时，实用的命令可以实现一些隐藏的功能，比如查询个人配置文件的路径、打开仍处于实验阶段的新功能、记录页面的网络信息等。用户掌握好这些常用的浏览器命令，将有助于提升配置 Chrome 功能的效率。
 


[12]: http://oksteward.com/
[13]: https://www.alfredapp.com/
[14]: https://sspai.com/post/42048
[0]: https://img1.tuicool.com/aUJjium.jpg
[1]: https://img2.tuicool.com/fYfu2mq.jpg
[2]: https://img0.tuicool.com/6BJBfei.jpg
[3]: https://img0.tuicool.com/2imUJ3z.jpg
[4]: https://img2.tuicool.com/quMJbii.jpg
[5]: https://img1.tuicool.com/YnaeQbZ.jpg
[6]: https://img1.tuicool.com/YnAvI3I.jpg
[7]: https://img1.tuicool.com/Nfa63uM.jpg
[8]: https://img1.tuicool.com/VVfUzyu.jpg
[9]: https://img1.tuicool.com/vEzUFf7.jpg
[10]: https://img0.tuicool.com/B3Mz22b.jpg
[11]: https://img0.tuicool.com/jIR3yiJ.gif