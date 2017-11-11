### Linux Command 系列：Tree 

 2017/10/17 

> 不好意思，连开两个系列，我都怕自己收不住了，嘻嘻。说正事，Linux 命令可以用浩瀚如烟来形容，那怎么办呢，我也很绝望啊，平时工作接触到的也就那么几个命令，那就平时自己多攒点，关键时候用上了就是物超所值 :)

最近想学的东西一大堆，今天晚上没啥事，有时间可以静下来学习一波，然而当双手放在键盘上准备大干一场的时候，突然脑子却一片空白，不知道该先学啥。沉默了一会，越想越乱，难道今天又不在学习状态？？？

:tired_face:
:tired_face:

心没法平静，还是学点短小精悍的实用知识好了，Linux 的命令多而杂，学会单独一个命令不需要花多大的精力，就能使用它的基本用法了。

那么今天就先学一个实用的 Linux 命令：**tree**。

撒花 ~~ 

:cherry_blossom:
:cherry_blossom:
:cherry_blossom:

> tree 会将一个目录的所有内容以树状图的格式列出来。这是一个非常整齐简洁且实用的程序，你可以再命令行中使用它来查看你的文件系统的结构。

    $ tree -d -L 2
    .
    ├── app
    │   ├── Console
    │   ├── Exceptions
    │   ├── Http
    │   └── Providers
    ├── bootstrap
    │   └── cache
    ├── config
    8 directories
## 描述

tree 是一个递归列举目录内容的程序，它以缩进长短不同的方式展现不同层级的文件（如果设置了 **LS_COLORS** 环境变量的话，展现的内容会带有颜色，更加的好看美观）。

最简单的用法就是直接使用 tree，不带任何参数，默认会将当前目录的内容全部列举出来（如果这个目录的文件很多还是不要建议这么干，因为你会看到很长很长很长的一条树状结构，然后你发现也看不出啥来）。

tree 打印出来的内容最后一行总会显示出它列举出来的文件或目录的数量，所以也可以变相的将其用作统计某一个目录下有多少个文件的用途，就想下面这样~

    $ tree
    .
    ├── ClassLoader.php
    ├── LICENSE
    ├── autoload_classmap.php
    ├── autoload_files.php
    ├── autoload_namespaces.php
    ├── autoload_psr4.php
    ├── autoload_real.php
    ├── autoload_static.php
    └── installed.json
    0 directories, 9 files
默认情况下，如果列举的内容中存在软链接的话，tree 会将其指向的实际路径也打印出来，就像这样：


    ├── php -> ../Cellar/php71/7.1.7_19/bin/php
## 语法

学命令最痛苦的就是它的参数，通常的做法就是把常用的几个参数记住，想不起来了就 man 一下（这里推荐一款与 man 很像的工具（[tldr][2]），但比 man 更方便和简洁，实用性更强~）    

    tree [-adfgilnopqrstuxACDFNS] [-L level [-R]] [-H baseHREF] [-T title]
    [-o filename] [--nolinks] [-P pattern] [-I pattern] [--inodes]
    [--device] [--noreport] [--dirsfirst] [--version] [--help]
    [--filelimit #] [--si] [--prune] [--du] [--timefmt format]
    [directory ...]
它的可选项也很多，下面只列举一些常用的哦。

参数 | 描述 
-|-
—help | 列举使用说明 
—version | 输出版本信息 
-a | 默认不会输出隐藏文件，比如那些以.开头的文件，带上这个参数就能把所有文件都打印出来 
-d | 只会输出目录，而不会输出文件 
-L level | 最大展示的目录层级 
-I pattern | 不显示那些匹配给定通配符的文件 
-P pattern | 只显示那些匹配给定通配符的文件 
-p | 额外显示目录和文件的读写权限 

## 例子

* 以树状图的格式显示当前目录的内容，包括子目录。
    
```
    $ tree
    .
    ├── assets
    │   ├── data
    │   │   ├── data1.bin
    │   │   ├── data2.sql
    │   │   └── data3.inf
    │   └── images
    │       ├── background.jpg
    │       ├── icon.gif
    │       └── logi.jpg
    ├── config.dat
    ├── program.exe
    └── readme.txt
    3 directories, 9 files
```
* 只显示目录，且层级不超过2层。
    
```
    $ tree -d -L 2
    .
    └── assets
    ├── data
    └── images
    3 directories
```
* 过滤掉以 data 开头的文件，或者以 con 开头，紧跟三个任意字符，以 .dat 结尾的文件。
    
```
    $ tree -I 'data*|con???.dat'
    .
    ├── assets
    │   └── images
    │       ├── background.jpg
    │       ├── icon.gif
    │       └── logi.jpg
    ├── program.exe
    └── readme.txt
    2 directories, 5 files
```
* 与 -I 参数刚好相反，只显示匹配通配符的文件，在这里就是只显示以 t 开头的文件。
    
```
    $ tree -P 't*'
    .
    └── assets
    ├── data
    └── images
    3 directories, 0 files
```
* 额外显示目录和文件的读写权限。
    
```
    $ tree -P 't*' -p
    .
    └── [drwxr-xr-x]  assets
    ├── [drwxr-xr-x]  data
    └── [drwxr-xr-x]  images
    3 directories, 0 files
```
今天是不是 Get 了一个新知识点了呢


[2]: https://github.com/tldr-pages/tldr