# [php 内核探秘之 PHP_FUNCTION 宏][0]

* [c][1]
* [php][2]

[**daryl**][3] 23 小时前发布 


> 本人也只是个初入门的菜鸟，因对技术有着向往，故在“无趣”的工作之余，尽自己所能提升自己。由于我的 C 语言功底也有限，故本文的深度也有限，如有幸得大牛阅读，还望指导一二，小弟感激不尽。

## PHP 的函数

作为 PHPer，我们几乎每天都在写函数，我们一定会好奇，那些 PHP 内置的函数，是长什么样子的。如果写过 PHP 扩展的话，一定知道这个宏：PHP_FUNCTION。在定义一个函数的时候，这样来使用这个宏。例如 array_change_key_case，它的定义是这样的：PHP_FUNCTION(array_change_key_case)。没错，就是这么简单。但是，在这个简单的背后，却没有这么简单。

## PHP_FUNCTION 追根溯源

### 宏

相信对这篇文章感兴趣的同学，一定多少对 C 语言以及它的宏定义有一定的了解。如果没有，也不要紧，我这里来简单解释一下，什么是宏。

C 语言中的宏，我认为，可以理解为一种简单的封装。通过宏定义，可以对开发者隐去一些细节，让开发者在使用简单的语法来完成重复的复杂的编码。当然，宏定义还有其它的用途，但是，我们在 PHP_FUNCTION 涉及到的就是这个作用。有下面的代码。

    #define TEST(test) void test(int a)
    
    TEST(haha)

宏，就是完全的替换，即使用后面的语句替换前面的。那么对于下面的 TEST(haha) 就相当于下面的样子。

    void haha(int a)

### PHP_FUNCTION 的定义

首先，我们要定义函数，这样使用这个宏。

    PHP_FUNCTION(array_change_key_case)
    {
        // TODO
    }

我们在 php-src/main/php.h 中找到了下面的定义。

    #define PHP_FUNCTION ZEND_FUNCTION

也就是说，这里用 ZEND_FUNCTION 替换了 PHP_FUNCTION 这个宏。所以，我们的定义就相当于变成了这样。

    ZEND_FUNCTION(array_change_key_case)
    {
        // TODO
    }

我们继续往下找，因为，这里还是宏，我们并没有看到我们希望看到的代码。我们可以在 php-src/Zend/zend_API.h 中找到下面的定义。

    #define ZEND_FN(name) zif_##name
    #define ZEND_FUNCTION(name) ZEND_NAMED_FUNCTION(ZEND_FN(name))
    #define ZEND_NAMED_FUNCTION(name) void name(INTERNAL_FUNCTION_PARAMETERS)

我们看到，在宏定义中，使用了另外的宏。不要怕，还是一个词，替换。我们按照这样的步骤来。（## 是一个连接符，它的作用是，是将它前面的与后面的，按照字符串的方式连接起来。

1. 替换 ZEND_FUNCTION

```
    ZEND_NAMED_FUNCTION(ZEND_FN(name))
    {
        // TODO
    }
```
1. 替换 ZEND_FN

```
    ZEND_NAMED_FUNCTION(zif_array_change_key_case)
    {
        // TODO
    }
```
1. 替换 ZEND_NAMED_FUNCTION

```
    void zif_array_change_key_case(INTERNAL_FUNCTION_PARAMETERS)
    {
        // TODO
    }
```
到这里，我们可以看到，这里已经基本和我们熟悉的函数定义差不多了，不过，这还没完，以为，这里还有宏，那就是 INTERNAL_FUNCTION_PARAMETERS。我们找到 php-src/Zend/zend.h，可以找到 INTERNAL_FUNCTION_PARAMETERS 的宏定义。

```
    #define INTERNAL_FUNCTION_PARAMETERS zend_execute_data *execute_data, zval *return_value
```
好了，依然按照替换的原则，我们就可以将函数定义变成这样了。

```
    void zif_array_change_key_case(zend_execute_data *execute_data, zval *return_value)
    {
        // TODO
    }
```
看，整个函数的定义，已经完全没有宏了，这已经是我们在熟悉不过的 C 语言函数的定义了。这就是  
PHP_FUNCTION 的整个定义的过程。

### execute_data 和 return_value

return_value，顾名思义，就是定义的 PHP 函数的返回值。而 execute_data，按照我的理解，就是 Zend 内部的一个调用栈，而在执行这个函数的时候，指向的是这个函数的栈帧。具体的细节，暂时在这里先不考虑，有兴趣的同学可以来这里看一下。[深入理解 PHP 内核][12]

## 后记

我始终认为，对于一个 PHPer 来说，C 语言是一项必不可少的技能。理解 PHP 的内核，对于我们编写出高质量的代码，起到了关键的作用。所以，我现在开始研究 PHP 的源码实现了。我希望我能通过这些文章，记录下我理解源码的瞬间，也希望我的文章能让更多的 PHPer，进入到 PHP 内核的世界。

[0]: /a/1190000010529733
[1]: /t/c/blogs
[2]: /t/php/blogs
[3]: /u/daryl
[12]: http://www.php-internals.com/book/?p=chapt03/03-06-02-var-scope