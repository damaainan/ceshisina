## 将 Sublime 里的AutoFileName改造一下，支持图片预览

来源：[http://wanyaxing.com/blog/20180901154709.html](http://wanyaxing.com/blog/20180901154709.html)

时间 2018-09-01 15:47:09

 
## 前言
 
[AutoFileName][2] 是`Sublime Text`中非常有用的一款插件，其主要功能是帮助你在代码中快速录入文件路径，如HTML和CSS里经常会输入各类图片地址。
 
## 不够满意的地方
 
这款插件的上次更新已经是五年前，然而在插件下载榜里仍排在 Top25 里，可见其受欢迎程度。
 
然而我还是有点小不满，就是在默认的插件功能里，只能通过语法提示的方式，列出文件名，最多显示个图片大小，也就没了。
 
![][0]
 
而我想要的更多，我希望能直接看到图标！
 
## 自己动手改造一下
 
因为懒，这事儿憋心里很久没有动手。
 
因为懒，看不到图写代码时总是在一堆文件名里翻来找去又让我有点烦躁。
 
忍无可忍，今天我终于把这事儿给做了，当~当~当~当~~~
 
![][1]
 
顺便也将 Vue.js 里的`@`符号给支持了，简直酷毙了。
 
## 安装方法
 
因为这款插件的上次更新已经是五年前，也不知道作者还是否会合并代码，所以目前只能手动覆盖插件文件来实现更新。

 
* 下载最新压缩包：[https://github.com/wanyaxing/AutoFileName/archive/st3.zip][3]  
* 解压并放置到插件目录：`Sublime Text 3/Packages/` 
 
 
## 后语
 
已提交 PR 给原作者，不知原作还在不在线-。-，目前只能先开个分支出来：[https://github.com/wanyaxing/AutoFileName][4] ，不要客气，请用力点赞。
 
反正能用了，想想以后写代码可以刷刷刷地再次效率+1，真是美滋滋。


[2]: https://packagecontrol.io/packages/AutoFileName
[3]: https://github.com/wanyaxing/AutoFileName/archive/st3.zip
[4]: https://github.com/wanyaxing/AutoFileName
[0]: ../img/aYreIjv.png 
[1]: ../img/feMfuui.png 