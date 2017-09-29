### 学习PHP精粹，编写高效PHP代码之质量保证 


摘要: 我们要完成一个高质量、高标准的项目，不仅要重视编码和测试，还包括管理协作和项目完善。 本文将介绍一些确保项目达到高标准的工具，其中包括phploc、phpcpd、phpmd、phpdocumentor、phing等。 

## 一、使用静态分析工具测量质量

我们用静态分析测量代码而不运行它。实际上，我们将这些工具用于评估代码、读取文件、衡量它所写的要素。使用这些工具，可以帮助我们对代码库有一个完整的层次化的认识，甚至在代码库变得更大、更复杂的时候也能掌握。

静态分析工具是项目过程中的一个关键组成部分，但是，只有定期使用它们，并以理想的方式进行每一次提交，静态分析工具才真正显示出价值。这些工具涵盖了代码的所有方面，从计数类和计算行数，到识别哪里有提示使用复制和粘贴的类似代码段。然后我们来看看静态分析工具在代码质量中两个特别关键的问题上如何帮助我们：编码标准和文档。

#### 1、phploc

phploc：[https://github.com/sebastianbergmann/phploc][5]

PHP代码行（phploc）可能并不是一个非常有趣的静态分析工具，但它确实给了我们一些有趣的信息，特别是随着时间的推移当我们反复运行它的时候。phploc提供项目拓扑结构以及尺寸的相关信息。

例如测试一个标准的WordPress版本，我们只需使用如下命令：

    $ phploc wordpress

#### 2、phpcpd

phpcpd：[https://github.com/sebastianbergmann/phpcpd][6]

PHP复制粘贴器（phpcpd）看起来是一个在代码中寻找类似模式的工具，我们使用它是为了在代码库中识别代码在何处被复制或粘贴。这是常规构建过程中的一个非常有用的工具，但是从输出中获得正确的编号会让项目与项目有所不同。

同样，如果我们测试WordPress，可以使用下面的命令：

    $ phpcpd wordpress

#### 3、phpmd

phpmd：[http://phpmd.org/][7]

PHP项目消息探测器（phpmd）是一个试图量化所谓开发老手所说的“代码发出的气味”的工具。它使用一系列指标寻找似乎失衡的项目元素。该工具生成大量的输出，其中大部分都是好的建议，下面是一个要求phpmd在WordPress中检查命名混乱的命令：

    $ phpmd wordpress/  text naming

## 二、编码标准

编码标准是一个在很多开发团队中引起激烈争论的话题，既然缩进和使用空格并未影响代码的运行，那为什么我们要创建格式化的规则并且严格遵守呢？事实上，当我们已经习惯于某个编码风格，而且代码以我们期望的方式排列时，它会变得更加容易阅读。但是，在实际开发过程中，很容易忘记规则，所以需要工具区检查所有的代码。

#### 1、使用PHP代码探测器检查编码标准

PHP代码探测器：[http://pear.php.net/package/PHP_CodeSniffer][8]

首先，你需要在服务器上安装这个工具。无论它在开发机器还是开发服务器上，这完全取决于你所拥有的可用资源。

安装后，就可以使用下面的命令测试代码了：

    phpcs --standard=PEAR robot.php

#### 2、查看违反编码标准的地方

PHP代码探测器有几个非常重要的报表样式，你可以用它们看着所用代码库的“重点”、我们将这些以详细报表的同样方式输出到屏幕上，它们也可以生成其他格式。

要生成一个汇总报表，只需这样做：

    phpcs --standard=PEAR --report=summary *

#### 3、查看PHP代码探测器标准

有几个编码标准是PHP代码探测器默认运行的，你可以生成或设置任何自己的标准。若想看到有哪些可用的标准，你可以运行具有-i开关的phpcs。

    $ phpcs -i

## 三、文档和代码

使用phpDocumentor将注释转换为文档。

phpDocumentor：[http://www.phpdoc.org/][9]

例如：

    phpdoc -t docs -o HTML:Smarty:PHP -d .

## 四、源代码管理

常用源代码管理工具：

Subversion：[http://subversion.apache.org/][10]

Git：[http://git-scm.com/][11]

## 五、自动部署

Phing：[http://www.phing.info/][12]

Phing 是一个基于Apache ANT 的项目构建系统。Phing使用基于XML的配置，默认保存在一个名为build.xml的文件中。

我们给这个项目命令，并定义一系列属于这个项目的任务，还可以指定哪些任务被默认运行，都可以通过Phing进行配置。


[1]: https://my.oschina.net/refine/home

[3]: https://www.oschina.net/event/2214645
[4]: https://my.oschina.net/img/hot3.png
[5]: https://github.com/sebastianbergmann/phploc
[6]: https://github.com/sebastianbergmann/phpcpd
[7]: http://phpmd.org/
[8]: http://pear.php.net/package/PHP_CodeSniffer
[9]: http://www.phpdoc.org/
[10]: http://subversion.apache.org/
[11]: http://git-scm.com/
[12]: http://www.phing.info/