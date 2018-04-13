## PHP扩展开发教程2 - 编写第一个扩展 hello world

来源：[https://segmentfault.com/a/1190000014193269](https://segmentfault.com/a/1190000014193269)

PHP扩展是高级PHP程序员必须了解的技能之一，对于一个初入门的PHP扩展开发者，怎么才能开发一个成熟的扩展，进入PHP开发的高级领域呢？本系列开发教程将手把手带您从入门进入高级阶段。
本教程系列在linux下面开发（推荐使用centos），php版本用的是5.6，并假设您有一定的linux、git操作经验和c/c++基础。
有问题需要沟通的朋友请加QQ技术交流群32550793和我沟通。
我们使用容易上手的PHP-CPP框架来开发PHP扩展，如果您有一定的linux操作经验和c++基础，按照下面的步骤，相信用不了10分钟就能做出属于你自己的第一个扩展出来。
以下示范的操作都是在linux centos系统上完成的，并且已经事先安装了php5.6系列。
## 一、下载并安装 PHP-CPP

要想使用PHP-CPP编译属于您自己的php扩展，需要先下载PHP-CPP的源码并编译安装。
PHP-CPP有两个框架源码，分别叫 PHP-CPP(新版) 和 PHP-CPP-LEGACY。
PHP-CPP(新版)适合开发PHP-7的扩展，PHP-CPP-LEGACY则适合开发5.X系列的扩展，两套框架的接口一样，学会了其中一个就很容易做出兼容的PHP各版本的扩展出来。
下面我们的操作都以PHP-CPP-LEGACY为例。

如果你会git命令，可以直接在终端命令行敲入以下git命令即可。

```
# git clone https://github.com/CopernicaMarketingSoftware/PHP-CPP-LEGACY.git
```

如果不会git也没关系，可以直接用浏览器打开该源码的github仓库网址，下载源码压缩包并解压即可，仓库网址是 
[https://github.com/CopernicaM...][0]。

下载完成后，进入PHP-CPP-LEGACY的源码目录，敲入make命令编译源码，编译完成后会生成开发扩展所需要的相关类库。

```
# make
```

接着运行make install命令，把生成的类库和相关开发的头文件安装到linux系统里面去，一会儿编译扩展的时候就可以不用配置头文件和类库目录也能自动连接上了。

```
# sudo make install
```
## 二、下载第一个扩展 helloworld

第一个扩展 helloworld 的源码已经在github上准备好了，直接用git命令克隆，或者手工下载都可以。

```
# git clone https://github.com/elvisszhang/phpcpp_helloworld.git
```

进入helloworld源码目录，打开main.cpp，可以看到如下代码结构，已经都给加了中文注释。
其中最重要的就是 get_module 函数，它是扩展的入口函数。

```c
#include#include <iostream>

//这是PHP里面可以调用的接口函数
void say_hello()
{
    //输出一段欢迎
    Php::out << "hello world from my first extension" << std::endl;
}

/**
 *  告诉编译器get_module是个纯C函数
 */
extern "C" {
    
    /**
     *  本函数在PHP进程一打开就会被访问，并返回一个描述扩展信息的PHP结构指针
     */
    PHPCPP_EXPORT void *get_module() 
    {
        // 必须是static类型，因为扩展对象需要在PHP进程内常驻内存
        static Php::Extension extension("helloworld", "1.0.0");
        
        //这里可以添加你要暴露给PHP调用的函数
        extension.add<say_hello>("say_hello");
        
        // 返回扩展对象指针
        return extension;
    }
}

```

test.php则是扩展测试用的一段php代码。

```php
<?php
say_hello();

```
## 三、编译第一个扩展 helloworld

编译这个扩展很简单，在终端命令行下输入make命令即可。

```
# make
g++ -Wall -c -O2 -std=c++11 -fpic -o main.o main.cpp
g++ -shared -o helloworld.so main.o -lphpcpp
```

不出意料的话，就会在源码目录下看到 helloworld.so 这个扩展文件了，可以发现这个文件很小，才14K而已。
不过现在如果你敲一下命令 php -m ，发现php的模块中并没有 helloworld 这个扩展，因为我们还没有把它安装到php的运行环境里。
## 四、安装第一个扩展 helloworld

我们这里暂时介绍手工安装扩展的方式。

* 第一步: 先用php-config命令确定一下扩展存放的位置

```
# php-config --extension-dir
/usr/local/php56/lib/php/extensions/no-debug-non-zts-20131226
```

上面显示的是我服务器上扩展安装的位置，各人的服务器可能配置不一样。

* 第二步：然后把 helloworld.so 拷贝到扩展存放目录下。

```
# cp helloworld.so /usr/local/php56/lib/php/extensions/no-debug-non-zts-20131226/
```

* 第三步：修改 php.ini 文件，启用 helloworld 扩展

打开 php.ini文件，加上以下配置项，在php.ini的任意地方新加一行即可。

```
extension = helloworld.so
```

* 第四步：确认 helloworld扩展已经安装成功

使用php -m命令可以查看php目前已经安装的所有扩展。

```
# php -m | grep helloworld
helloworld
```

从上面命令行的响应看，helloworld扩展已经安装成功了。

* 第五步：运行 test.php 确认注册函数能使用

还是在扩展的源码目录，运行以下命令

```
# php test.php
hello world from my first extension
```

从上面命令行的响应看，我们通过扩展向php注册的say_hello函数已经成功运行了，是不是感觉很简单，但现在的扩展只会打个招呼，还干不了什么正儿八经的事，我们后面给他完善一下，让他能做更多的事情。
## 参考文献

[PHP-CPP安装以及hello world][1]
[PHP-CPP官网教程][2]

[0]: https://github.com/CopernicaMarketingSoftware/PHP-CPP-LEGACY.git
[1]: https://segmentfault.com/a/1190000008046774?_ea=1532821
[2]: http://www.php-cpp.com/documentation/install