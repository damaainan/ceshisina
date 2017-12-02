# [让人相见恨晚的vim插件：模糊查找神器LeaderF](http://blog.csdn.net/archofortune/article/details/77906066)

 原创  2017年09月09日 10:06:17

提到vim的模糊查找插件，很多人第一反应是ctrlp.vim，ctrlp知名度很高，但跟其它的同类插件相比，它的唯一优点是用vimL编写（这让它的性能是所有同类插件中最差的）。本文向大家推荐一款模糊查找插件——[LeaderF][6]，无论是从性能还是匹配精度上，都远远超越ctrlp。

## [LeaderF][6]是什么？

LeaderF是一个用Python写的vim插件，可以在成千上万数十万个文件中，通过模糊查找的方式，快速找到目标文件。它还有很多衍生功能：快速打开或定位某个buffer、最近使用的文件（mru）、tags（包括函数、类、变量等）、命令历史、文件中的某一行、vim的help、marks等等。

### 查找文件

文件查找是vimer的常用操作，海量文件中快速定位目标文件是很多vimer迫切需要的功能，所以就有了ctrlp这样的插件。但ctrlp性能不佳，而且有时候把目标文件名都输入进搜索栏还是找不到目标文件，最大的问题是它的模糊匹配算法不佳，经常排在前面的跟所要找的相去甚远。LeaderF采用异步检索和精心设计的模糊匹配算法完美地解决了这些问题，当你在根目录(/)按下搜索命令，再也没有想剁手的冲动了; 查找文件时，用更少的键击次数就可以找到目标文件, 延长你的键盘使用寿命 :)

* 异步检索

![异步检索][7]
* 模糊查找

![模糊查找][8]
* 正则表达式查找

![正则][9]
* 多字节字符查找（中文查找）

![中文][10]

### 快速定位tags（包括函数、类、变量等）

有了它，tagbar可以淘汰掉了：

![tags][11]

### 查找历史命令

![History][12]

### 切换Colorscheme

![Colorscheme][13]

- - -

当然还有其他功能就不一一展示了，感兴趣的小伙伴可以查看[这里][14]。

### 你不知道的细节

* 智能大小写（smartcase）

如果输入的搜索字符都是小写字母，则匹配是大小写不敏感的；如果输入大写字母，则只匹配大写字母，小写字母仍然是大小写不敏感的。例如，输入abcDef，可以匹配如下字符串：

    abcDef
    AbcDef
    abcDEf
    aBcDeF

但不能匹配：

    abcdef
    Abcdef

**注意**：abc和ef仍然是大小写不敏感的   
这样可以通过大写字母，在搜索过程中快速缩小搜索范围。

* 同时打开多个文件

![同时打开多个文件][15]

* 细化搜索结果

![细化搜索结果][16]

### 开箱即用

上面所有的功能都不需要额外的配置，只要装好LeaderF插件就可以使用了，不像有的插件，配置就像一门新的脚本语言。

## 最后

LeaderF还支持写扩展程序，[这里][17]是一个样例。

友情链接：[LeaderF，也許是Vim最好的模糊查詢插件][18]



[6]: https://github.com/Yggdroot/LeaderF
[7]: ./img/27315d03a6243089.gif
[8]: ./img/2a30914948e8f8f3.gif
[9]: ./img/5995778736f4e83e.gif
[10]: ./img/4fd3f4d2fb0c7662.gif
[11]: ./img/cf11e8297876130a.gif
[12]: ./img/7d7ed2c8fd4a6237.gif
[13]: ./img/ca5b0e50f927e02b.gif
[14]: https://github.com/Yggdroot/LeaderF/blob/master/README.md
[15]: ./img/085c8ee957047408.gif
[16]: ./img/8ff92a484012254d.gif
[17]: https://github.com/Yggdroot/LeaderF-marks
[18]: https://0x3f.org/post/leaderf-currently-the-best-fuzzy-finder-of-vim/