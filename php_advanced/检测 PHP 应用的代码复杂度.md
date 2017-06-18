# [检测 PHP 应用的代码复杂度][0]

* [php][1]
* [code][2]
* [代码复杂度][3]

[**JellyBool**][4] 6月4日发布 


> 原文来自：[https://www.laravist.com/blog...][13]

如果说你想知道一个 PHP 项目的代码复杂度是什么样子的，我推荐你可以使用 [phploc][14] 和 [PhpMetrics][15] 来检测一下。

## 1.使用 phploc

这是一个标准的 composer package，不过我推荐大家可以直接使用 composer 全局安装：

    composer global require 'phploc/phploc=*'

然后安装完毕，你就可以使用 phploc 命令来检测你的代码复杂度了：

    phploc ./app

比如上面这行代码就是检测你的项目中 app/ 目录的代码复杂度；如果是一个 Laravel 的项目的话，大概会是这个样子的结果输出：

    phploc 3.0.1 by Sebastian Bergmann.
    
    Directories                                         14
    Files                                               72
    
    Size
      Lines of Code (LOC)                             3748
      Comment Lines of Code (CLOC)                     790 (21.08%)
      Non-Comment Lines of Code (NCLOC)               2958 (78.92%)
      Logical Lines of Code (LLOC)                     950 (25.35%)
        Classes                                        656 (69.05%)
          Average Class Length                           9
            Minimum Class Length                         0
            Maximum Class Length                        84
          Average Method Length                          2
            Minimum Method Length                        0
            Maximum Method Length                       21
        Functions                                        0 (0.00%)
          Average Function Length                        0
        Not in classes or functions                    294 (30.95%)
    
    Cyclomatic Complexity
      Average Complexity per LLOC                     0.10
      Average Complexity per Class                    2.33
        Minimum Class Complexity                      1.00
        Maximum Class Complexity                     15.00
      Average Complexity per Method                   1.41
        Minimum Method Complexity                     1.00
        Maximum Method Complexity                     6.00
    
    Dependencies
      Global Accesses                                    0
        Global Constants                                 0 (0.00%)
        Global Variables                                 0 (0.00%)
        Super-Global Variables                           0 (0.00%)
      Attribute Accesses                               436
        Non-Static                                     436 (100.00%)
        Static                                           0 (0.00%)
      Method Calls                                     570
        Non-Static                                     412 (72.28%)
        Static                                         158 (27.72%)
    
    Structure
      Namespaces                                        15
      Interfaces                                         0
      Traits                                             0
      Classes                                           72
        Abstract Classes                                 0 (0.00%)
        Concrete Classes                                72 (100.00%)
      Methods                                          233
        Scope
          Non-Static Methods                           226 (97.00%)
          Static Methods                                 7 (3.00%)
        Visibility
          Public Methods                               194 (83.26%)
          Non-Public Methods                            39 (16.74%)
      Functions                                         24
        Named Functions                                  0 (0.00%)
        Anonymous Functions                             24 (100.00%)
      Constants                                          0
        Global Constants                                 0 (0.00%)
        Class Constants                                  0 (0.00%)
        

不过你可能也感觉到，这个 phploc 的一大不便之处就是，目前来说，他还不能把相关的测试结果可视化或者说自定义检测的最高复杂度。所以，PhpMetrics 就应运而生了。

## 使用 PhpMetrics

首先需要说明的是，[PhpMetrics][15] 可以更深入到你的代码中，并且会生成一个 html 文件作为分析的结果，这样我们查看检测结果就会非常的直观。

安装 [PhpMetrics][15] 也是可以直接 composer 全局安装：

    composer global require 'phpmetrics/phpmetrics'

安装完毕之后，可以这样来运行命令分析代码复杂度：

    phpmetrics --report-html=report.html ./app

等待 phpmetrics 运行结束，用 Chrome 打开 report.html 就可以查看相对应的结果，大概是这个样子：

![][16]

[0]: https://segmentfault.com/a/1190000009654074
[1]: https://segmentfault.com/t/php/blogs
[2]: https://segmentfault.com/t/code/blogs
[3]: https://segmentfault.com/t/%E4%BB%A3%E7%A0%81%E5%A4%8D%E6%9D%82%E5%BA%A6/blogs
[4]: https://segmentfault.com/u/jellybool
[13]: https://www.laravist.com/blog/post/code-complexity-tools-for-php-apps
[14]: https://github.com/sebastianbergmann/phploc
[15]: http://www.phpmetrics.org/
[16]: https://segmentfault.com/img/bVOFB1?w=694&h=724