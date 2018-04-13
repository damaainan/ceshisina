## PHP扩展开发教程1 - 相关开发技术对比及介绍

来源：[https://segmentfault.com/a/1190000014169130](https://segmentfault.com/a/1190000014169130)

PHP扩展是高级PHP程序员必须了解的技能之一，对于一个初入门的PHP扩展开发者，怎么才能开发一个成熟的扩展，进入PHP开发的高级领域呢？本系列开发教程将手把手带您从入门进入高级阶段。
本教程系列在linux下面开发（推荐使用centos），php版本用的是5.6，并假设您有一定的linux操作经验和c/c++基础。
有问题需要沟通的朋友请加QQ技术交流群32550793和我沟通。
开发php扩展有好几种技术方法和框架，对于初学者来说，最好能够选择一个最容易下手，最快出效果的框架，这样才能提升学习的兴趣。下面逐一对比一下各个技术框架，让大家能够找到最适合自己的。
## 一、使用ext-skel C语言开发

ext-skel是PHP官方源码里提供的生成php扩展的工具，可以生成一个c语言框架的php扩展的骨架。

PHP 官方对扩展开发者非常不友好，源代码中提供的Zend API极其难用，API复杂而且凌乱，充斥着各种宏的写法。Zend API坑非常多，普通开发者很容易踩到坑里。出现各种莫名其妙的core dump问题。Zend API几乎没有任何文档，开发者如果要真正掌握这项技能需要付出大量的学习时间。
以上是swoole插件开发者的肺腑之言，可见用这个方法来开发插件，对我们初学者来说将是对自信心极严重的打击。幸好有大神们为我们准备了其他开发php扩展的方法，不用学习zend api，不用精通c语言，也照样能开发php扩展,而且生成的扩展运行速度不会比c语言开发的相差太多。
## 二、使用zephir 类php语言开发

Zephir提供了一种类似php的高级语言语法的方式，来自动生成扩展的c语言代码，使编写php扩展变得非常的简单。不过这种开发方式带来了一个问题，就是由于他用的不是c/c++语言开发，那就没办法直接利用现有的各种c/c++开发库来实现强大的功能。所以感觉上有点鸡肋。
## 三、使用PHP-X C++语言开发

php-x是知名的swoole扩展开发者根据多年的开发经验，提炼出来的一套基于c++的扩展开发框架。从文档来看，这是一个比较容易上手的开发框架，数据类型很齐全，和php cpp的开发风格非常相似，但本人还没有去体验使用。
按照php-x官方的文档，开发出来的扩展只支持PHP7以上，这是一个遗憾。
## 四、使用phpcpp C++语言开发

PHP CPP是我重点推荐的php扩展开发框架，简明易懂，功能强大，开发效率高，代码易维护，执行速度快。

PHP CPP是一款免费的php开发扩展库，主要针对C++语言，可以进行类集合的扩展和构建，采用简单的计算机语言，让扩展变得更有趣更有用，方便开发者进行维护和编写，易于理解、维护轻松并且代码优美。用C ++编写的算法看起来与用PHP编写的算法几乎完全相同。如果你知道如何在PHP中编程，你可以很容易地学习如何在C ++中做同样的。

* 优点一：不需要Zend引擎知识。

Zend引擎的内部太复杂，Zend引擎的代码是一团糟，并且大多是无证的。但是PHP-CPP库已经在非常容易使用的C ++类和对象中封装了所有这些复杂的结构。你可以使用C ++写出惊人的快速算法，而不必直接调用Zend引擎，甚至无需查看Zend引擎源代码。使用PHP-CPP，您可以编写本地代码，而无需处理PHP的内部。

* 优点二：支持所有重要的PHP功能

使用PHP-CPP，您可以像使用普通PHP脚本一样轻松地处理变量，数组，函数，对象，类，接口，异常和命名空间。除此之外，你可以使用C ++的所有功能，包括线程，lambda和异步编程。

* 优点三：支持PHP 5.X，PHP7的扩展开发

PHP-CPP有两套扩展开发框架，分别支持PHP 5.X，PHP7，虽然框架代码有两个，但是接口却是一样的。所以如果你要开发兼容多个版本的php扩展，不会花费你额外太多时间做兼容。
## 五、各开发框架的 hello world 扩展源码大比拼

下面列出各个框架的hello world扩展源码，从源码长度和复杂度，就能有个直观感受。
ext-skel生成的c扩展源码明显可读性极差，也极难理解。
zephir的扩展源码最类似php语法，最容易入手，但难以加入成熟的c/c++库代码。
PHP-X和PHP CPP的源码风格很相似，都是标准的c++语言，都很容易看懂。不难想象，这两种方式开发扩展必然是最合适的，因为我们既能利用c++的封装简化开发，又能直接调用市面上各个成熟c++库为我们服务。
 **`ext-skel的hello world源码`** 

```c
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_helloworld.h"

static int le_helloworld;

PHP_FUNCTION(confirm_helloworld_compiled)
{
    char *arg = NULL;
    int arg_len, len;
    char *strg;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &arg, &arg_len) == FAILURE) {
        return;
    }

    len = spprintf(&strg, 0, "Congratulations! You have successfully modified ext/%.78s/config.m4. Module %.78s is now compiled into PHP.", "helloworld", arg);
    RETURN_STRINGL(strg, len, 0);
}

PHP_MINIT_FUNCTION(helloworld)
{
    return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(helloworld)
{

    return SUCCESS;
}

PHP_RINIT_FUNCTION(helloworld)
{
    return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(helloworld)
{
    return SUCCESS;
}

PHP_MINFO_FUNCTION(helloworld)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "helloworld support", "enabled");
    php_info_print_table_end();

}

const zend_function_entry helloworld_functions[] = {
    PHP_FE(confirm_helloworld_compiled,    NULL)        /* For testing, remove later. */
    PHP_FE_END    /* Must be the last line in helloworld_functions[] */
};

zend_module_entry helloworld_module_entry = {
    STANDARD_MODULE_HEADER,
    "helloworld",
    helloworld_functions,
    PHP_MINIT(helloworld),
    PHP_MSHUTDOWN(helloworld),
    PHP_RINIT(helloworld),        /* Replace with NULL if there's nothing to do at request start */
    PHP_RSHUTDOWN(helloworld),    /* Replace with NULL if there's nothing to do at request end */
    PHP_MINFO(helloworld),
    PHP_HELLOWORLD_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_HELLOWORLD
ZEND_GET_MODULE(helloworld)
#endif
```
 **`zephir的hello world源码`** 

```
namespace Test;
class Hello
{
    public function say()
    {
        echo "Hello World!";
    }
}
```
 **`PHP-X的hello world源码`** 

```
#includeusing namespace std;
using namespace php;

//声明函数
PHPX_FUNCTION(say_hello);

//导出模块
PHPX_EXTENSION()
{
    Extension *ext = new Extension("hello-world", "0.0.1");
    ext->registerFunction(PHPX_FN(say_hello));
    return ext;
}

//实现函数
PHPX_FUNCTION(say_hello)
{
    echo("hello world");
}
```
 **`PHP CPP的hello world源码`** 

```cpp
#includevoid say_hello(Php::Parameters &params)
{
    Php::out << "hello world" << std::endl;
}
extern "C" {
    PHPCPP_EXPORT void *get_module() 
    {
        static Php::Extension extension("helloworld", "1.0");
        extension.add("say_hello", say_hello);
        return extension;
    }
}
```
## 参考文献

[如何基于 PHP-X 快速开发一个 PHP 扩展][0]
[PHP-X中文帮助][1]
[5分钟PHP扩展开发快速入门][2]
[zephir中文网][3]
[zephir英文官网][4]
[zephir安装和演示开发][5]
[phpcpp英文官网][6]
[phpcpp英文帮助][7]
[phpcpp中文帮助][8]

[0]: https://segmentfault.com/a/1190000011111074
[1]: https://wiki.swoole.com/wiki/page/721.html
[2]: https://segmentfault.com/a/1190000008114150
[3]: https://zephir.org.cn/
[4]: http://zephir-lang.com/
[5]: https://blog.csdn.net/manwea/article/details/78835644
[6]: http://www.php-cpp.com/
[7]: http://www.php-cpp.com/documentation/install
[8]: http://blog.ihuxu.com/the-documentation-in-chinese-of-the-php-cpp/