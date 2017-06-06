# [PHP内核的存储机制（分离/改变）][0]

 2016-04-04 21:14  8795人阅读  

 分类：

版权声明：本文为博主原创文章，转载请说明出处。

 目录

1. [实例一][6]
1. [实例二][7]
1. [分离指的是分离两个变量存储的zval的位置让分开不指向同一个空间 那如何判定是否要分离呢依据是什么见下边 改变指的是有引用赋值时要把新开辟的zval 的 is_ref 赋值为1][8]
1. [实例三内存是如何泄漏的][9]
  1. [数组变量与普通变量生成的zval非常类似但也有很大不同][10]
  1. [本文参考文献][11]

 前言：

大部分程序员看博客可能不是太喜欢看汉字比较多的文章哈，但本文确实介绍以汉字为主描述，耐心看完，对大部分人来说肯定会有收获！

 或许你知道，或许你不知道，PHP是一个弱类型，动态的脚本语言。所谓弱类型，就是说PHP并不严格验证变量类型(严格来讲，PHP是一个中强类型语言)，在申明一个变量的时候，并不需要显示指明它保存的数据的类型。比如： $a = 1; (整形)  $a ="1";(字符串)

一直使用[PHP][12]，但它究竟什么，底层是怎么实现才成就了PHP这样方便快捷的弱类型语言。

最近也查阅了很多书籍，还有相关博客资料，了解到了许多关于PHP内核的一些机制。  
  
 php简单的理解就是一个[C语言][13]的类库，你去php[.NET][14] 下面下载一下它的源代码就会发现，首先php的内核是zend engine ，它是一个用c语言写的函数库，用于处理底层的函数管理，内存管理，类管理，和变量管理。在内核上面，他们写了很多扩展，这些扩展大多数都是独立的。用[操作系统][15]来比喻的话，zend engine 就是一个操作系统，然后官方提供了很多“应用程序”，只是这个“应用程序” 不是media play 而是 [MySQL][16]， libxml，dom。当然，你也可以根据zend engine 的api 开发自己的扩展。

- - -

 **下边开始介绍下PHP变量在内核中的存储机制。**

  
PHP是若类型语言，也就是说一个PHP变量可以保存任何的数据类型。但PHP是使用C语言编写的，而C语言是强类型语言是吧，每个变量都会有固定的类型，(一颗通过强类型转变，不过有可能出现问题)，那在Zend引擎中如何做到一个变量保存任何数据类型？下边请看它存储结构体。

打开Zend/zend.h头文件，会发现下列结构体 Zval ：

1.zval结构


     typedef struct _zval_struct zval;



     typedef union _zvalue_value {
        long lval;      /* long value */
        double dval;    /* double value */
        struct {
        char *val; //4字节
        int len;   //4字节
        } str;
        HashTable *ht;    /* hash table value */
        zend_object_value obj;
     } zvalue_value;





     struct _zval_struct {
        /* Variable information */
        zvalue_value value;  /* 变量值保存在这里 12字节*/
        zend_uint refcount;//4字节，变量引用计数器
        zend_uchar type;   /* active type变量类型 1字节*/
        zend_uchar is_ref;//是否变量被&引用，0表示非引用，1表示引用，1字节
        };

 2. zend_uchar type   
 PHP中的变量包括 四种标量类型 （bool,int,float,string）， 两种复合类型 （array, object）和 两种特殊的类型 （resource 和NULL）。在zend内部，这些类型对应于下面的宏（代码位置 phpsrc/Zend/zend.h） Zend根据type值来决定访问value的哪个成员，可用值如下：

 

![][17]   
 3 . zend_uint refcount__gc

该值实际上是一个计数器，用来保存有多少变量（或者符号， symbols ,所有的符号都存在符号表（symble table）中, 不同的作用域使用不同的符号表，关于这一点，我们之后会论述）指向该zval。在变量生成时，其refcount=1，典型的赋值操作如$a = $b会令zval的refcount加1，而unset操作会相应的减1。在PHP5.3之前，使用引用计数的机制来实现GC，如果一个zval的refcount较少到0，那么Zend引擎会认为没有任何变量指向该zval，因此会释放该zval所占的内存空间。但，事情有时并不会那么简单。后面我们会看到， 单纯的引用计数机制无法GC掉循环引用的zval(详见后举例3) ，即使指向该zval的变量已经被unset，从而导致了内存泄露（ Memory Leak ）。

  
 4.is_ref__gc

. 

这个字段用于标记变量是否是引用变量。对于普通的变量，该值为0，而对于引用型的变量，该值为1。这个变量会影响zval的共享、分离等。关于这点，我们之后会有论述。

正如名字所示，ref_count__gc和 is_ref__gc 是PHP的GC机制所需的很重要的两个字段，这两个字段的值，可以通过xdebug等调试工具查看。

下面我们围绕zval，展开叙述，PHP变量到底是怎么个存储机制。

Xdebug的安装我在前边PHPstorm Xdebug调试也介绍过，这里不赘述，请看： [phpstorm+Xdebug断点调试PHP][18]

安装成功后， 你的脚本中，可以通过 xdebug_debug_zval 打印Zval的信息，用法：



     $var = 1;
     debug_zval_dump($var);
     $var_dup = $var;
     debug_zval_dump($var);

## **实例** **一：**



        $a = 1;
        $b = $a;
        $c = $b;
        $d = &$c; // 在一堆非引用赋值中，插入一个引用
    

  
  整个过程图示如下：   
![][19]

  
---------------------------------------------------------

## **实例二：**



       $a = 1;
        $b = &$a;
        $c = &$b;
        $d = $c; // 在一堆引用赋值中，插入一个非引用

  整个过程图示如下：   
 

![][20]

  
  
 通过实例一、二，展现了，这就是PHP的 **copy on write写时分离机制 、** change on write写时改变机制

**过程：**

PHP在修改一个变量以前，会首先查看这个变量的refcount，如果refcount大于1，PHP就会执行一个分离的例程，

对于上面的实例一代码，当执行到第四行的时候，PHP发现$c指向的zval的 refcount大于1 ，那么PHP就会复制一个新的zval出来，将原zval的refcount减1，并修改symbol_table，使得$a,$b和$c分离(Separation)。这个机制就是所谓的copy on write(写时复制/写时分离)。把$d指向的新zval的is_ref的值 == 1 ，这个机制叫做change on write(写时改变)

**结论：**

## 分离 指的是：分离两个变量存储的zval的位置，让分开不指向同一个空间！ (那如何判定是否要分离呢，依据是什么？见下边)

改变 指的是，有&引用赋值时，要把新开辟的zval 的 is_ref 赋值为1

##   
判定是否分离的条件：如果is_ref =1 或recount == 1,则不分离 



    if((*val)->is_ref || (*val)->refcount<2){
              //不执行Separation
            ... ;//process
      }

  
  
  
  
 ---------------------------------------------------------------------------------------------------

## 实例三：(内存是如何泄漏的) 

###  数组变量与普通变量生成的zval非常类似，但也有很大不同 

举例：



    $a = $array('one');  
    $a[] = &$a;  
    xdebug_debug_zval('a'); 

 debug_zval_dump打印出zval的结构是：



    a: (refcount=2, is_ref=1)=array (
        0 => (refcount=1, is_ref=0)='one', 
        1 => (refcount=2, is_ref=1)=...
    )

 上述输出中，…表示指向原始数组，因而这是一个循环的引用。如下图所示：

![][21]

  
  
  
 现在，我们对$a执行unset操作，这会在symbol table中删除相应的symbol,同时，zval的refcount减1（之前为2），也就是说，现在的zval应该是这样的结构：



    unset($a);
    (refcount=1, is_ref=1)=array (
        0 => (refcount=1, is_ref=0)='one', 
        1 => (refcount=1, is_ref=1)=...
    )

  
![][22]

（应该ref_count=1）

（unset,其实就是打断$a在 符号表（symble table） 与zval 的一个指针映射关系。）

这时，不幸的事情发生了！

Unset之后，虽然没有变量指向该zval，但是该zval却不能被GC（指PHP5.3之前的单纯引用计数机制的GC）清理掉， **$a 被释放，但是$a里的$a[1]也指向了该zval，它没有被释放，导致zval的refcount均大于0。** 这样，这些zval实际上会一直存在内存中，直到请求结束（参考SAPI的生命周期）。在此之前，这些zval占据的内存不能被使用，便白白浪费了，换句话说， **无法释放的内存导致了内存泄露** 。

如果这种内存泄露仅仅发生了一次或者少数几次，倒也还好，但如果是成千上万次的内存泄露，便是很大的问题了。尤其在长时间运行的脚本中（例如守护程序，一直在后台执行不会中断），由于无法回收内存，最终会导致系统“再无内存可用”，所以说，一定要避免这种操作。

  
垃圾回收机制：

1.php原来是通过引用计数器来实现内存回收，也就是是多个php变量可能会引用同一份内存，这种情况unset掉其中一个是不会释放内存的；   
 例如： $a = 1; $b = $a; unset($a);//$a开辟的内存不会回收  
 2.离开了变量的作用域后变量所占用的内存就会被 自动清理 （不包含静态变量，静态变量在脚本加载时创建，在脚本结束时释放），

如函数或方法内的局部变量，对这些局部变量进行unset在函数外来看内存也是没有减少的。

3.引用计数有个缺陷，就是当循环引用出现时，计数器没法清0， 内存占用会持续到页面访问结束 。

对于这个问题PHP5.3中增加了垃圾回收机制。具体可以查阅文档：[http://php.net/manual/zh/features.gc.php][23]

 垃圾回收机制就是最早在Lisp中被提出，关于更多垃圾回收的信息. 参见维基百科： https://en.wikipedia.org/wiki/Garbage_collection_(computer_science)

  
 ###  本文参考文献： 

1. 鸟哥的深入变量引用/分离 [http://www.laruence.com/2008/09/19/520.html][24]

本文链接地址： [http://blog.csdn.net/ty_hf/article/details/51057954][25]   
 啊~终于写完了，清明三天假期也就这么过去了，今天4月4号，明天开始准备上班啦！~

[0]: http://blog.csdn.net/ty_hf/article/details/51057954
[6]: #t0
[7]: #t1
[8]: #t2
[9]: #t4
[10]: #t5
[11]: #t6
[12]: http://lib.csdn.net/base/php
[13]: http://lib.csdn.net/base/c
[14]: http://lib.csdn.net/base/dotnet
[15]: http://lib.csdn.net/base/operatingsystem
[16]: http://lib.csdn.net/base/mysql
[17]: ../img/20160404194942266.png
[18]: http://blog.csdn.net/ty_hf/article/details/50768702
[19]: ../img/20160404201622636.png
[20]: ../img/20160404202921641.png
[21]: ../img/20160404210358201.png
[22]: ../img/20160404210602608.png
[23]: http://php.net/manual/zh/features.gc.php
[24]: http://www.laruence.com/2008/09/19/520.html
[25]: http://blog.csdn.net/ty_hf/article/details/51057954