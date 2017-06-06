# [PHP内核--生命周期][0]

 2016-10-21 00:04  1494人阅读  

 分类：

版权声明：本文为博主原创文章，转载请说明出处。

**了解PHP生命周期之前，先了解一下apache是怎么和php关联起来的吧~**

****

**1.Apache运行机制剖析**

![][5]

-----------------------------

![][6]

![][7]

![][8]

**总体示意图如下：**

**![][9]**

 Apache Hook机制 Apache的Hook机制是指：Apache 允许模块(包括内部模块和外部模块， 例如mod_php5.so,mod_perl.so等 )将自定义的函数注入到请求处理循环中。换句话说，模块可以在Apache的任何一个处理阶段中挂接(Hook)上自己的处理函数，从而参与Apache的请求处理过程。

 **mod_php5.so/ php5apache2.dll就是将所包含的自定义函数，通过Hook机制注入到Apache中，在Apache处理流程的各个阶段负责处理php请求。**

 **知道了apache是怎么hook到php的，那么下边看看apache转接到PHP后的一个流程逻辑。**

**2.PHP运行流程图解**

PHP开始和结束阶段

PHP开始执行以后会经过两个主要的阶段：处理请求之前的开始阶段和请求之后的结束阶段。

![][10]

2.1 SAPI运行PHP都经过的几个阶段

1. 模块初始化阶段(Module init)
即调用每个拓展源码中的的PHP_MINIT_FUNCTION中的方法初始化模块,进行一些模块所需变量的申请,内存分配等。

1. 请求初始化阶段(Request init)
即接受到客户端的请求后调用每个拓展的PHP_RINIT_FUNCTION中的方法,初始化PHP脚本的执行环境。

1. 执行PHP脚本(这一步，应该是大多数PHP程序员所熟悉的部分，自己写的代码就是在这里执行的)
1. 请求结束(Request Shutdown)
这时候调用每个拓展的PHP_RSHUTDOWN_FUNCTION方法清理请求现场,并且ZE开始回收变量和内存

1. 关闭模块(Module shutdown)
Web服务器退出或者命令行脚本执行完毕退出会调用拓展源码中的PHP_MSHUTDOWN_FUNCTION 方法

**经过如下几个环节： 开始 - 请求开始 - 请求关闭 - 结束 SAPI接口实现就完成了其生命周期**

![][11]

**2.2开始阶段**

2.2.1模块初始化阶段MINIT

在整个SAPI生命周期内(例如Apache启动以后的整个生命周期内或者命令行程序整个执行过程中)， 该过程只进行一次。

启动Apache后，PHP解释程序也随之启动； 

PHP调用各个扩展（模块）的MINIT方法，从而使这些扩展切换到可用状态。

//这也是为什么引入了新dll模块，得重启apache的原因。php.ini



    PHP_MINIT_FUNCTION(myphpextension)
    {
        // 注册常量或者类等初始化操作
        return SUCCESS; 
    }

![][13]


2.2.2模块激活阶段RINIT

该过程发生在请求阶段， 例如通过url请求某个页面，则在每次请求之前都会进行模块激活（RINIT请求开始）。

请求到达之后，SAPI层将控制权交给PHP层，PHP初始化本次请求执行脚本所需的环境变量

例如是Session模块的RINIT，如果在php.ini中启用了Session 模块，那在调用该模块的RINIT时就会初始化$_SESSION变量，并将相关内容读入； 然后PHP会调用所有模块RINIT函数,即“请求初始化”。 

在这个阶段各个模块也可以执行一些相关的操作, 模块的RINIT函数和MINIT函数类似 ， RINIT方法可以看作是一个准备过程，在程序执行之前就会自动启动。

****



    PHP_RINIT_FUNCTION(extension_name) {
          /* Initialize session variables, pre-populate variables, redefine global variables etc */
    }
    

![][15]


**2.3结束阶段**

 请求处理完后就进入了结束阶段, 一般脚本执行到末尾或者通过调用exit()或者die()函数,PHP都将进入结束阶段. 和开始阶段对应,结束阶段也分为两个环节,一个在请求结束后(RSHUWDOWN),一个在SAPI生命周期结束时(MSHUTDOWN).、

2.3.1请求结束后(RSHUWDOWN)

请求处理完后就进入了结束阶段，PHP就会启动清理程序。 

它会按顺序调用各个模块的RSHUTDOWN方法。 

RSHUTDOWN用以清除程序运行时产生的符号表， 也就是对每个变量调用unset函数。



    PHP_RSHUTDOWN_FUNCTION(extension_name) {
    /* Do memory management, unset all variables used in the last PHP call etc */
    }
    

![][17]


2.3.2 SAPI生命周期结束时(MSHUTDOWN)

最后，所有的请求都已处理完毕 

SAPI也准备关闭了 

PHP调用每个扩展的MSHUTDOWN方法 

这时各个模块最后一次释放内存的机会。 

（这个是对于CGI和CLI等SAPI，没有“下一个请求”，所以SAPI立刻开始关闭。）



    PHP_MSHUTDOWN_FUNCTION(extension_name) {
        /* Free handlers and persistent memory etc */
    }
    


![][20]

整个PHP生命周期就结束了。要注意的是，只有在服务器没有请求的情况下才会执行“启动第一步”和“关闭第二步”。

原文地址：http://blog.csdn.net/ty_hf/article/details/52877759

[0]: http://blog.csdn.net/ty_hf/article/details/52877759
[5]: ../img/20161020222255245.png
[6]: ../img/20161020222314652.png
[7]: ../img/20161020222354028.png
[8]: ../img/20161020222542082.png
[9]: ../img/20161020222846005.png
[10]: ../img/20161020222959082.png
[11]: ../img/20161020223112928.png
[12]: #
[13]: ../img/20161020223210053.png
[15]: ../img/20161020223259367.png
[17]: ../img/20161020223311273.png
[20]: ../img/20161020223321977.png