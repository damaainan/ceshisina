## [Just for fun——PHP7扩展编写中的宏](https://segmentfault.com/a/1190000012994203)


# PHP内核架构

![][0]

* **SAPI**是PHP的最上层，它是PHP的应用接口层，对于源码目录为sapi
* **main**是PHP的主要代码，主要是输入/输出，Web通信，以及PHP框架的初始化操作，对于源码目录为main
* **ZendVM**是PHP解释器的主要实现，即ZendVM，对于源码目录为Zend

截一张[php-src][1]的图，目录都有对应

![][2]

# PHP的生命周期

![][3]   
PHP根据不同SAPI的实现，各阶段的执行情况有些差异。譬如cli模式的话，完整地经历了这些阶段，而Fastcgi模式下则在启动时执行一次模块初始化，然后各个请求只经历请求初始化，执行请求脚本，请求关闭这几个阶段。

# PHP扩展

开发者可以通过C/C++实现自定义的功能，通过扩展嵌入到PHP中。  
编写扩展的步骤：

1. 通过ext目录下 **ext_skel**脚本生成扩展的基本框架./ext_skel --extname=module （module is the name of your extension）
1. 修改config.m4配置：设置编译配置参数、设置扩展源文件
1. 编写扩展源代码
1. 生成configure：写完后先phpize（在php的bin目录下）运行一下
1. 编译&安装： ./configure、 make、make install，然后改一下php.ini文件，添加一下`.so`文件

# 举例

操作系统：CentOS Linux release 7.3.1611  
PHP版本：PHP 7.1.11

### 生成骨架

    ./ext_skel --extname=my_test --no-help

--no-help是略去注释代码（干净点）  
生成目录my_test：

![][4]

### 查看C文件

##### my_test.c

```c
    /* $Salamander$ */
    
    #ifdef HAVE_CONFIG_H
    #include "config.h"
    #endif
    
    #include "php.h"
    #include "php_ini.h"
    #include "ext/standard/info.h"
    #include "php_my_test.h"
    
    
    
    static int le_my_test;
    
    
    
    
    PHP_MINIT_FUNCTION(my_test)
    {
        return SUCCESS;
    }
    
    
    PHP_MSHUTDOWN_FUNCTION(my_test)
    {
        return SUCCESS;
    }
    
    
    
    PHP_RINIT_FUNCTION(my_test)
    {
    #if defined(COMPILE_DL_MY_TEST) && defined(ZTS)
        ZEND_TSRMLS_CACHE_UPDATE();
    #endif
        return SUCCESS;
    }
    
    
    
    PHP_RSHUTDOWN_FUNCTION(my_test)
    {
        return SUCCESS;
    }
    
    
    PHP_MINFO_FUNCTION(my_test)
    {
        php_info_print_table_start();
        php_info_print_table_header(2, "my_test support", "enabled");
        php_info_print_table_end();
    
    }
    
    
    const zend_function_entry my_test_functions[] = {
        PHP_FE_END
    };
    
    
    zend_module_entry my_test_module_entry = {
        STANDARD_MODULE_HEADER,
        "my_test",
        my_test_functions,
        PHP_MINIT(my_test),
        PHP_MSHUTDOWN(my_test),
        PHP_RINIT(my_test),    
        PHP_RSHUTDOWN(my_test),
        PHP_MINFO(my_test),
        PHP_MY_TEST_VERSION,
        STANDARD_MODULE_PROPERTIES
    };
    
    
    #ifdef COMPILE_DL_MY_TEST
    #ifdef ZTS
    ZEND_TSRMLS_CACHE_DEFINE()
    #endif
    ZEND_GET_MODULE(my_test)
    #endif
    #endif
    
```

可以注意到这里有一些**宏**

* **PHP_MINIT_FUNCTION**
* **PHP_MSHUTDOWN_FUNCTION**
* **PHP_RINIT_FUNCTION**
* **PHP_RSHUTDOWN_FUNCTION**
* **PHP_MINFO_FUNCTION**

这些是PHP提供的钩子函数，PHP执行到不同的阶段时**回调**各个扩展定义的钩子函数，定义完成后，最后设置一下zend_module_entry对应的函数指针即可。  
回顾之前的PHP的生命周期，也就是说（=>指对应某个阶段）：  
**PHP_MINIT_FUNCTION** => 模块初始化阶段（M就是module的含义，init就是initial）  
**PHP_MSHUTDOWN_FUNCTION** => 模块关闭阶段（M就是module的含义，后面就是shutdown）  
**PHP_RINIT_FUNCTION** => 请求初始化（R就是request的含义，init就是initial）  
**PHP_RSHUTDOWN_FUNCTION** => 请求关闭阶段（R就是request的含义，后面就是shutdown）  
**PHP_MINFO_FUNCTION** 指获取模块信息  
最后，设置**zend_module_entry**这个结构体

```c
    zend_module_entry my_test_module_entry = {
        STANDARD_MODULE_HEADER,
        "my_test",
        my_test_functions,
        PHP_MINIT(my_test),
        PHP_MSHUTDOWN(my_test),
        PHP_RINIT(my_test),    
        PHP_RSHUTDOWN(my_test),
        PHP_MINFO(my_test),
        PHP_MY_TEST_VERSION,
        STANDARD_MODULE_PROPERTIES
    };
```

获取各个钩子函数的指针，有对对应的宏PHP_MINIT，PHP_MSHUTDOWN，PHP_RINIT，PHP_RSHUTDOWN，PHP_MINFO

### 注册函数

分为两步：

1. 定义函数，可以通过PHP_FUNCTION()或ZEND_FUNCTION()宏来完成函数声明
1. 注册函数，PHP提供了**zend_function_entry**，扩展只需为每个内部函数生成这样一个结构，然后将所有函数的结构数组提供给zend_module_entry->functions即可

For Example：

    PHP_FUNCTION(my_func)
    {
        // 具体实现
    }
    

展开后

    void zif_my_func(zend_execute_data *execute_data, zval *return_value)
    {
        // ...
    }

**zend_function_entry**可以通过宏PHP_FE或ZEND_FE生成（FE即function entry）。

```c
    const zend_function_entry my_test_functions[] = {
        PHP_FE(my_func, NULL)
        PHP_FE_END
    };
```
`my_test_functions`就是这个扩展注册的函数数组。  
最后，它设置在了`zend_module_entry`（第三个参数）

```c
    zend_module_entry my_test_module_entry = {
        STANDARD_MODULE_HEADER,
        "my_test",
        my_test_functions,
        PHP_MINIT(my_test),
        PHP_MSHUTDOWN(my_test),
        PHP_RINIT(my_test),    
        PHP_RSHUTDOWN(my_test),
        PHP_MINFO(my_test),
        PHP_MY_TEST_VERSION,
        STANDARD_MODULE_PROPERTIES
    };
```
### 函数参数解析

PHP提供了一个方法将`zend_execute_data`上的参数解析到指定变量上。

```c
    //file: Zend/zend_API.h
    ZEND_API int zend_parse_parameters(int num_args, const char *type_spec, ...)
```
* `num_args`：参数数量，用`ZEND_NUM_ARGS()`可以获取
* `type_spec` 为参数解析规则，是一个字符串
* 最后一个是可变参数，指定要解析到的变量地址

举例：

```c
    PHP_FUNCTION(my_func)
    {
        zval *arr;
        if(zend_parse_parameters(ZEND_NUM_ARGS(), "a", &a) == FAILURE) {
            RETURN_FALSE;
        }
        ...
    }
```
如果有多个变量type_spec可以变为"la"，l表示整型，a表示数组（另外还有b：布尔型，s：字符串型，o：对象）  
，后面则改为&a, &b

### 函数返回值

可以设置return_value，但PHP提供了设置了设置返回值的宏

```c
    #define RETURN_BOOL(b)                     { RETVAL_BOOL(b); return; }
    #define RETURN_NULL()                     { RETVAL_NULL(); return;}
    #define RETURN_LONG(l)                     { RETVAL_LONG(l); return; }
    #define RETURN_DOUBLE(d)                 { RETVAL_DOUBLE(d); return; }
    #define RETURN_STR(s)                     { RETVAL_STR(s); return; }
    #define RETURN_INTERNED_STR(s)            { RETVAL_INTERNED_STR(s); return; }
    #define RETURN_NEW_STR(s)                { RETVAL_NEW_STR(s); return; }
    #define RETURN_STR_COPY(s)                { RETVAL_STR_COPY(s); return; }
    #define RETURN_STRING(s)                 { RETVAL_STRING(s); return; }
    #define RETURN_STRINGL(s, l)             { RETVAL_STRINGL(s, l); return; }
    #define RETURN_EMPTY_STRING()             { RETVAL_EMPTY_STRING(); return; }
    #define RETURN_RES(r)                     { RETVAL_RES(r); return; }
    #define RETURN_ARR(r)                     { RETVAL_ARR(r); return; }
    #define RETURN_OBJ(r)                     { RETVAL_OBJ(r); return; }
    #define RETURN_ZVAL(zv, copy, dtor)        { RETVAL_ZVAL(zv, copy, dtor); return; }
    #define RETURN_FALSE                      { RETVAL_FALSE; return; }
    #define RETURN_TRUE                       { RETVAL_TRUE; return; }
```
# 写个小例子

写一个两个整型变量相加的函数

```c
    /* $Salamander$ */
    
    #ifdef HAVE_CONFIG_H
    #include "config.h"
    #endif
    
    #include "php.h"
    #include "php_ini.h"
    #include "ext/standard/info.h"
    #include "php_my_test.h"
    
    
    
    static int le_my_test;
    
    
    
    PHP_FUNCTION(my_add)
    {
        int argc = ZEND_NUM_ARGS();
        zend_long a;
        zend_long b;
    
        if (zend_parse_parameters(argc, "ll", &a, &b) == FAILURE) 
            RETURN_FALSE;
        RETURN_LONG(a + b);
    }
    
    
    
    PHP_MINIT_FUNCTION(my_test)
    {
        return SUCCESS;
    }
    
    
    PHP_MSHUTDOWN_FUNCTION(my_test)
    {
        return SUCCESS;
    }
    
    
    
    PHP_RINIT_FUNCTION(my_test)
    {
    #if defined(COMPILE_DL_MY_TEST) && defined(ZTS)
        ZEND_TSRMLS_CACHE_UPDATE();
    #endif
        return SUCCESS;
    }
    
    
    
    PHP_RSHUTDOWN_FUNCTION(my_test)
    {
        return SUCCESS;
    }
    
    
    PHP_MINFO_FUNCTION(my_test)
    {
        php_info_print_table_start();
        php_info_print_table_header(2, "my_test support", "enabled");
        php_info_print_table_end();
    
    }
    
    
    const zend_function_entry my_test_functions[] = {
        PHP_FE(my_add, NULL)
        PHP_FE_END
    };
    
    
    zend_module_entry my_test_module_entry = {
        STANDARD_MODULE_HEADER,
        "my_test",
        my_test_functions,
        PHP_MINIT(my_test),
        PHP_MSHUTDOWN(my_test),
        PHP_RINIT(my_test),    
        PHP_RSHUTDOWN(my_test),
        PHP_MINFO(my_test),
        PHP_MY_TEST_VERSION,
        STANDARD_MODULE_PROPERTIES
    };

    
    #ifdef COMPILE_DL_MY_TEST
    #ifdef ZTS
    ZEND_TSRMLS_CACHE_DEFINE()
    #endif
    ZEND_GET_MODULE(my_test)
    #endif
    #endif
```

config.m4中取消以下注释（删除**dnl**即可）

    dnl PHP_ARG_ENABLE(my_test, whether to enable my_test support,
    dnl Make sure that the comment is aligned:
    dnl [  --enable-my_test           Enable my_test support])

然后在my_test目录下执行

    phpize
    
    ./configure --with-php-config=/usr/local/php7.1/bin/php-config

php-config这个脚本是获取PHP安装信息的（PHP安装路径，PHP版本，PHP源码的头文件目录，LDFLAGS，依赖的外部库，PHP编译参数），它在php的安装路径的bin目录下，如果你不指定--with-php-config的话，将到默认的PHP的安装路径下搜索（**安装了多个PHP版本时最好指定一下，可能会编译不通过**）  
然后

    make && make install

得到

    Installing shared extensions:     /usr/local/php7.1/lib/php/extensions/no-debug-zts-20160303/

修改`php.ini`文件，加入`.so`

```ini
    date.timezone = "Asia/Shanghai"
    display_errors = On
    error_reporting = E_ALL
    short_open_tag=Off
    upload_max_filesize = 50M
    post_max_size = 50M
    memory_limit=512M
    
    extension=my_test.so
```
### 测试加载

    php -m

![][5]

### 测试函数

    php -r 'echo my_add(1, 3);'
    

![][6]   
函数调用成功。

[0]: ./img/bV2Gzd.png
[1]: https://github.com/php/php-src
[2]: ./img/bV2F24.png
[3]: ./img/bV2F1r.png
[4]: ./img/bV2Gcg.png
[5]: ./img/bV2Gxy.png
[6]: ./img/bV2Gx3.png