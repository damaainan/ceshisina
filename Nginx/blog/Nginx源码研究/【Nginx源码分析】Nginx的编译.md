## 【Nginx源码分析】Nginx的编译

来源：[https://segmentfault.com/a/1190000017059036](https://segmentfault.com/a/1190000017059036)

周生政
## nginx构建

使用shell语言生成makefile，以及ngx_auto_config.h,ngx_auto_headers.h,ngx_modules.c。其中ngx_auto_config.h为各种常量定义，ngx_auto_headers.h为头文件，ngx_modules.c为nginx模块。makefile用来组织编译流程。### configure主流程
* auto/options

根据configure的参数，初始化和configure参数相关的变量值
* auto/init

定义自动生成的文件名称
* auto/source

定义模块名称, 头文件查找目录, 头文件，源文件
* auto/cc/conf

选择编译器。假设选择gcc。会构造gcc的编译选项, 赋值CFLAGS, 针对gcc版本、操作系统、cpu等添加参数。
* auto/headers

向ngx_auto_headers.h写入通用的头文件
* auto/os/conf

检查操作系统特性。以linux,x86为例。
检查epoll,sendfile, sched_setaffinity, crypt_r, crypt_r等功能
* auto/unix

检查poll,kqueue, crypt, fcntl, posix_fadvise, directio, statfs, dlopen, sched_yield, setsockopt, getsockopt,accept4等特性。定义指针，size_t,time_t长度,一些typedef，机器大小端。
* auto/modules

根据用户编译参数,定义一些常量;
```LANG
#ifndef NGX_HAVE_PWRITE
#define NGX_HAVE_PWRITE  1
#endif

#ifndef NGX_SYS_NERR
#define NGX_SYS_NERR  135
#endif
```

生成ngx_modules.c
```LANG
extern ngx_module_t  ngx_http_range_body_filter_module;
extern ngx_module_t  ngx_http_not_modified_filter_module;

ngx_module_t *ngx_modules[] = {
    &ngx_core_module,
    &ngx_errlog_module,
    &ngx_conf_module,
```
* auto/lib/conf

pcre, openssl, md5,libgd,zlib等库
* auto/make

创建makefile脚本
* auto/lib/make

依赖库makefile
* auto/install

makefile的install部分
* auto/summary

汇总一些检查信息
### makefile 分析

nginx精简版makefile

```LANG
CC =    cc
CFLAGS =  -pipe  -O -W -Wall -Wpointer-arith -Wno-unused -Werror -g -ggdb3 -O0 -Wno-error
CPP =    cc -E
LINK =    $(CC)


ALL_INCS = -I src/core \
    -I src/event \
    ...
    -I src/mail


CORE_DEPS = src/core/nginx.h \
    src/core/ngx_config.h \
    ...
    src/core/ngx_palloc.h 
CORE_INCS = -I src/core \
    -I src/event \
    ...
    -I objs

HTTP_DEPS = src/http/ngx_http.h \
    src/http/ngx_http_request.h \
    ...
    src/http/modules/ngx_http_ssi_filter_module.h

HTTP_INCS = -I src/http \
    -I src/http/modules

objs/nginx:    objs/src/core/nginx.o \
    objs/src/core/ngx_log.o \
    ...
    objs/src/core/ngx_palloc.o 
    $(LINK) -o objs/nginx \
    ...
    objs/src/core/ngx_log.o  \
    -lpthread -lcrypt -lpcre -lcrypto -lcrypto -lz

objs/ngx_modules.o:    $(CORE_DEPS) \
    objs/ngx_modules.c
    $(CC) -c $(CFLAGS) $(CORE_INCS) \
        -o objs/ngx_modules.o \
        objs/ngx_modules.c

objs/src/http/modules/ngx_http_upstream_keepalive_module.o:    $(CORE_DEPS) $(HTTP_DEPS) \
    src/http/modules/ngx_http_upstream_keepalive_module.c
    $(CC) -c $(CFLAGS) $(CORE_INCS) $(HTTP_INCS) \
        -o objs/src/http/modules/ngx_http_upstream_keepalive_module.o \
        src/http/modules/ngx_http_upstream_keepalive_module.c


objs/src/http/modules/ngx_http_stub_status_module.o:    $(CORE_DEPS) $(HTTP_DEPS) \
    src/http/modules/ngx_http_stub_status_module.c
    $(CC) -c $(CFLAGS) $(CORE_INCS) $(HTTP_INCS) \
        -o objs/src/http/modules/ngx_http_stub_status_module.o \
        src/http/modules/ngx_http_stub_status_module.c
```
### 生成makefile的循环脚本

在makefile中有很多`objs/src/*.o`为target的规则,是通过脚本批量生成。
以下脚本为核心原文件生成makefile的规则。

```LANG

# the core sources

for ngx_src in $CORE_SRCS
do
    ngx_src=`echo $ngx_src | sed -e "s/\//$ngx_regex_dirsep/g"`
    ngx_obj=`echo $ngx_src \
        | sed -e "s#^\(.*\.\)cpp\\$#$ngx_objs_dir\1$ngx_objext#g" \
              -e "s#^\(.*\.\)cc\\$#$ngx_objs_dir\1$ngx_objext#g" \
              -e "s#^\(.*\.\)c\\$#$ngx_objs_dir\1$ngx_objext#g" \
              -e "s#^\(.*\.\)S\\$#$ngx_objs_dir\1$ngx_objext#g"`

    cat << END                                                >> $NGX_MAKEFILE

$ngx_obj:    \$(CORE_DEPS)$ngx_cont$ngx_src
    $ngx_cc$ngx_tab$ngx_objout$ngx_obj$ngx_tab$ngx_src$NGX_AUX

END

done

```

在auto/make中一共会有四个大循环,来自动化生成大量的规则。

* 核心源文件
* http源文件
* mail源文件
* misc源文件


### 采用shell编写的原因

nginx是模块化开发，有大量的模块可供用户选择。nginx为不同的系统做了大量的编译优化，充分挖掘性能。nginx代码量大，手动编写makefile枯燥易出错。基于以上原因，nginx的configure采用shell脚本开发，只编译选择模块，为不同系统提供不同编译参数，手动生成makefile规则。
