## 【Nginx源码分析】Nginx配置文件解析（一）

来源：[https://segmentfault.com/a/1190000016913713](https://segmentfault.com/a/1190000016913713)

运营研发团队 李乐

配置文件是nginx的基础，对于学习nginx源码甚至开发nginx模块的同学来说更是必须深究。本文将从源码从此深入分析nginx配置文件的解析，配置存储，与配置查找。

看本文之前读者可以先思考两个问题：
* 1.nginx源码中随处可以看到类似于这样的代码。

```LANG
//获取限流相关配置
lrcf = ngx_http_get_module_loc_conf(r, ngx_http_limit_req_module);
//获取fastcgi相关配置
flcf = ngx_http_get_module_loc_conf(r, ngx_http_fastcgi_module);
```

为什么可以这样获取到限流和fastcgi相关的配置呢？
* 2.server配置块中可以有多个location配置，那么location配置的匹配优先级是怎样的？比如说配置了三个location：“location ^~ /a { }”、“location /a/b { }”和“location ~ /a/* { }”，请求url为/a/b，最终能匹配到哪个location配置块呢？

相信学习本文之后，这两个问题将不在话下。

在学习nginx配置文件的解析过程之前，需要先了解一下nginx模块与指令的一些基本知识。

nginx的配置指令可以分为两大类：指令块（如events、http、server和location）与单条指令（如worker_processes、root、rewrite等）。

nginx规定指令块可以嵌套（如http块中可以嵌套server指令，server块中可以嵌套location指令），指令可以同时出现在不同的指令块（如root指令可以同时出现在http、server和location指令块）。

配置文件这种层次的复杂性，导致配置文件的解析与存储等的复杂性。
## 1.1 nginx模块

结构体ngx_module_t用于定义一个nginx模块，这里需要重点关注以下几个字段。

```LANG
struct ngx_module_s {
    ngx_uint_t            ctx_index; //用于给同类型的模块编号
    ngx_uint_t            index;  //用于给所有模块编号

    void                 *ctx;  //模块上下文；很重要；不同类型的模块通常指向不同类型的结构体，结构体通常包含若干函数指针
    ngx_command_t        *commands; //指令数组
    ngx_uint_t            type;  //模块类型编码
```

type字段表示模块类型编码。ctx指向模块上下文结构体，且不同类型的模块通常指向不同类型的结构体，该结构体中通常会包含若干函数指针。

nginx常用模块可以分为这么几类：核心模块，事件模块语http模块（conf类模块与mail类模块暂不考虑）。见下表

![][0]

从上表列出的三种类型的模块上下文结构体可以看出：

* 1）核心模块上下文结构只有三个字段：name表示核心模块名称；create_conf用于创建模块配置结构体；init_conf用于初始化模块配置结构体；
* 2）事件模块上下文结构前三个字段与核心模块相同，但是多了一个类型为ngx_event_actions_t结构的字段；该结构同样包含若干函数指针，表示该事件模块对外提供的若干API，比如添加事件还与删除事件等，这里不做详述；
* 3）我们都知道http相关配置可以分为三类，http指令块、server指令块和location指令块，对应的配置结构体称为main_conf、srv_conf和loc_conf；相应的create_  conf和init  _conf方法用于创建和初始化相关配置结构体。


而http模块上下文结构的preconfiguration和postconfiguration用于初始化http处理流程相关操作。

index字段用于给所有模块编号，比如：

```LANG
ngx_max_module = 0;
for (i = 0; ngx_modules[i]; i++) {
    ngx_modules[i]->index = ngx_max_module++;
}
ctx_index用于给同类型的模块编号，比如：

ngx_http_max_module = 0;
for (m = 0; ngx_modules[m]; m++) {
    if (ngx_modules[m]->type != NGX_HTTP_MODULE) {
        continue;
    }

    ngx_modules[m]->ctx_index = ngx_http_max_module++;
}
```
## 1.2 nginx配置指令

nginx的各个模块组合形成了其强大的处理能力，而每个模块只实现一个特定的功能。比如限流功能由模块ngx_http_limit_conn_module或者模块实现ngx_http_limit_req_module；fastcgi转发功能由模块ngx_http_fastcgi_module

实现；proxy转发功能由ngx_http_proxy_module（当然转发功能的实现还必须有模块ngx_http_upstream_module）。

当我们配置了指令proxy_pass或者fastcgi_pass时，该指令应该由哪个模块来解析呢？显然应该由实现此功能的模块来解析。即nginx配置文件的解析是分散到各个模块的。

每个模块都有一个commands数组，存储该模块可以解析的所有配置指令。指令结构体由ngx_command_t定义：

```LANG
struct ngx_command_s {
    ngx_str_t             name;
    ngx_uint_t            type;
    char               *(*set)(ngx_conf_t *cf, ngx_command_t *cmd, void *conf);
    ngx_uint_t            conf;
    ngx_uint_t            offset;
    void                 *post;
};
```

* name：配置指令名称，如“proxy_pass”；
* type：指令类型，可以将指令类型分为两类，1）说明指令可以出现的位置，比如配置文件（只能在配置文件最外层，不能出现在任何指令块内部），http指令块，或者events指令块，或者server指令块，或者location指令块；


用于校验参数数目。常用指令类型如下：

![][1]

* set：处理函数，当读取到该配置指令时，会执行此函数；
* conf和offset其实都表示的是偏移量，但是用处不同，解析指令时会详述，这里暂时跳过。
* post可以指向多种结构，不同指令可能不同，大多都为NUll，解析到具体指令时会详述，这里同样跳过。


下面这张图展示了指令的基本分类（通过颜色区分，各种颜色的文字描述指令类型以及该指令只能被哪种类型的模块解析）：

![][2]
## 1.3 配置存储格式方案设计

http配置相对复杂，h指令块嵌套，模块众多，导致http配置解析与存储的复杂性。因此本小节重点讲述http相关配置存储的方案设计。

前面提到每个模块负责解析和存储自己关心的配置指令，即每个模块都应该有个可以存储配置的结构体，该结构体通过模块上下文结构体的函数create_conf，create_main_conf，create_srv_conf或者create_loc_conf创建。

比如说如下表：

![][3]

问题来了，每个模块创建自己的配置结构体，存储是完全分散的，如何能快速查找到这些配置结构体呢？

最容易想到的就是声明一个void*的数组，数组元素数目就是模块数目，以模块的index字段作为数组的索引，数组的每个元素都指向对应模块的配置结构体。

但是不要忘记，nginx配置文件是有层次结构的，如http指令块中可以声明多个单条指令和多个server指令块，server指令块中可以声明多个单条指令和多个location指令块，location配置又可以声明多个单条指令。

我们可以这样来设计：
* 1）配置文件可以包含多条指令，指令块同样可以包含多条指令，为此我们可以定义指令作用域或者称为指令上下文；

![][4]

* 2）指令块的嵌套等价为上下文的嵌套，而上下文表现为某种类型的结构体，因此可通过结构体的互相引用实现指令块的嵌套；
* 3）指令或者指令块只能被特定类型的模块解析。比如，配置文件上下文包含的所有指令只能被核心模块（NGX_CORE_MODULE）解析；events指令块包含的所有指令只能被事件模块（NGX_EVENT_MODULE）解析；


http指令块内包含的所有指令或者指令块只能被http模块（NGX_HTTP_MODULE）解析。
* 4）http模块可以解析http指令块，server指令块和location指令块的指令；因此，http模块的指令结构分为3种：main_conf、srv_conf和loc_conf，其通过函数create_main_conf，create_srv_conf和create_loc_conf创建。

参考这四点设计，我们可以简单画出http配置存储结构示意图：

![][5]

这个结构似乎是可以的，但是我们忘记了一件事：一些指令可以同时出现在http指令块、server指令块和location指令块。

即http块中的指令类型可以是NGX_HTTP_MAIN_CONF，也可以是NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONF，还可以是NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONFNGX_HTTP_LOC_CONF；

而server块中的指令类型可以是NGX_HTTP_SRV_CONF，也可以是NGX_HTTP_SRV_CONFNGX_HTTP_LOC_CONF。（位或运算表示同时属于多种类型）

比如说指令root的类型位NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONF|NGX_HTTP_LOC_CONF，此时该配置应该存储在loc_conf配置结构，但是其可能会配置在http指令块中、server指令块或者location指令块。

为此我们修改上面的结构如下：

![][6]

上面我们分析了http指令块内部的所有指令可能的存储格式，events指令块内部存储格式相比较简单很多，读者可以试着画一画。

那么这是否是nginx采用的存储格式呢？可以说和上图非常类似，nginx设计的配置存储格式见下图，这里暂时留两个疑问：

* 1）如何实现http_ctx嵌套srv_ctx，srv_ctx嵌套loc_ctx；
* 2）当某条指令同时出现在http指令块、server指令块和location指令块时，以哪个配置为准。


![][7]
## 总结

本文作为nginx配置文件解析的第一小篇，简要介绍了nginx模块和指令的基本概念，同时针对http相关配置的存储格式进行了初步设计与讲解，为下文[《nginx配置文件解析（二）》][9]讲解。配置文件解析源码分析打下基础。

希望交流，一起学习Nginx PHP Redis 等源码的朋友请入微信群：

![][8]

[9]: https://segmentfault.com/a/1190000016922188
[0]: ./img/bVbi8cn.png
[1]: ./img/bVbi8bG.png
[2]: ./img/bVbi8bL.png
[3]: ./img/bVbi8bO.png
[4]: ./img/bVbi8bP.png
[5]: ./img/bVbi8bR.png
[6]: ./img/bVbi8bW.png
[7]: ./img/bVbi8bX.png
[8]: ./img/bVbi8j9.png