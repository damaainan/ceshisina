## [git warning: LF will be replaced by CRLF in 解决办法][0]

在使用git的时候，每次执行

    #git add "目录"
    
    git add  .

都会提示这样一个警告消息：

    warning: LF will be replaced by CRLF in XXXXXXXXXXXXXX.

虽然说没有什么影响吧。

不过就是觉得太碍眼了，

按照这样设置就没有问题了:

    git config core .autocrlf  false

这样设置git的配置后在执行add操作就没有问题了。

[0]: http://www.cnblogs.com/kpengfang/p/5962233.html


---


## [GitHub 下载文件夹][0] 



#### 工具

**TortoiseSVN**

#### 步骤

1、打开要下载的项目，选中要下载的文件夹，**右键** 选择 **复制链接地址**

[![image](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100837274-601913344.png "image")](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100836274-343884587.png)

2、把链接中的 `tree/master` 改成 `trunk` ，(trunk是master分支，可以使用`svn ls` 查看可用的分支和标记)

https://github.com/googlevr/gvr-unity-sdk/ tree/master /GoogleVR   
https://github.com/googlevr/gvr-unity-sdk/ trunk /GoogleVR

3、在需要的目录下，点 **右键** 选择 **SVN Checkout(检出)**

[![image](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100840117-771550666.png "image")](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100839102-715142979.png)

4、在弹出的检出窗口中，粘贴 修改后的地址到 **版本库URL**，按 **确定**

[![image](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100847336-1076918112.png "image")](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100844602-1747575850.png)

5、等待检出完成

[![image](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100856055-2111053878.png "image")](http://images2015.cnblogs.com/blog/363476/201606/363476-20160604100849180-735910398.png)

#### 附

**命令行操作:**

    svn checkout https://github.com/googlevr/gvr-unity-sdk/trunk/GoogleVR

如果遇到冲突信息：(R)eject, accept (t)emporarily or accept (p)ermanently? 输 p

[0]: http://www.cnblogs.com/zhaoqingqing/p/5558253.html

----


## [Github 下载单个文件][0] 


#### 前言

通常我们对Github上的项目都是完整的clone下来，但对于某些大型项目，或者某些时候只需要其中一两个文件，那该怎么办呢？

本文就是教你如何在github上下载单个文件。

#### 方法

1、找到需要下载的文件，点击进入

[![image](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153644788-773016211.png "image")](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153644241-1050764004.png)

2、在打开的页面中，找到 **Raw** 按钮，**右键** 选择 **目标另存为**

[![image](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153646053-5905205.png "image")](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153645491-1966508574.png)

#### Octo Mate

如果你是Chrome用户，并且安装了octo mate扩展，那么很高兴，你的页面上直接有个Download按钮

[![image](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153646944-1241883250.png "image")](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153646475-875334002.png)

#### Octo Mate小功能

[![image](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153648100-1374703772.png "image")](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153647709-386153808.png)

#### 附

Chrome用户有福啦，在Chrome Store中有个扩展：Octo Mate 。感谢作者：**Cam Song**

链接：[https://chrome.google.com/webstore/detail/github-mate/baggcehellihkglakjnmnhpnjmkbmpkf][7]

源码：[https://github.com/camsong/chrome-github-mate][8]

**我使用的基于Chrome内核的浏览器，也可以安装此扩展。**

[![image](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153649475-364288625.png "image")](http://images2015.cnblogs.com/blog/363476/201605/363476-20160527153648600-357834997.png)

[0]: http://www.cnblogs.com/zhaoqingqing/p/5534827.html
[7]: https://chrome.google.com/webstore/detail/github-mate/baggcehellihkglakjnmnhpnjmkbmpkf
[8]: https://github.com/camsong/chrome-github-mate