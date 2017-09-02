# 使用zephir开发php扩展

 时间 2017-03-18 21:21:41 

原文[https://blog.dmic.studio/posts/create-php-extent-with-zephir/][2]


最近在微信上看到 eechen 推广 淘宝沧龙（ [信海龙][4] ）的小课 [《零基础学习PHP扩展开发》][5] ，今天忽然想起来之前 phalcon 发布 3.0 时同时发布的 zephir 语言，于是心血来潮，按照官方示例写了一个扩展。如此简单。 

当然，还是建议大家看看 [《零基础学习PHP扩展开发》][5] ，了解下扩展机制，加深自己的理解。 

## zephir 是什么？ 

Zephir提供了一种类似php的高级语言语法的方式，来自动生成扩展的c语言代码，使编写php扩展变得非常的简单。

## zephir 安装 

1. composer 安装 【推荐】
```
    composer global require phalcon/zephir
```
1. git clone 编译安装
```
    git clone https://github.com/phalcon/zephir
    cd zephir
    ./install -c #-c 将zepir加入到系统全局变量中
```

ps：根据官方手册，如果是linux下可能需要安装依赖库 

    gcc >= 4.x/clang >= 3.x
    re2c 0.13 or later
    gnu make 3.81 or later
    autoconf 2.31 or later
    automake 1.14 or later
    libpcre3
    php development headers and tools
    

## 检测zephir是否成功 

    ( ⌁ ) zephir help
    ( ⌁ ) zephir help                                         21:31:21
     _____              __    _
    /__  /  ___  ____  / /_  (_)____
      / /  / _ \/ __ \/ __ \/ / ___/
     / /__/  __/ /_/ / / / / / /
    /____/\___/ .___/_/ /_/_/_/
             /_/
    Zephir version 0.9.6a-dev
    Usage:
        command [options]
    Available commands:
        api [--theme-path=/path][--output-directory=/path][--theme-options={json}|/path]Generates a HTML API
        build               Generate/Compile/Install a Zephir extension
        builddev            Generate/Compile/Install a Zephir extension in development mode
        clean               Cleans the generated object files in compilation
        compile             Compile a Zephir extension
        fullclean           Cleans the generated object files in compilation
        generate            Generates C code from the Zephir code
        help                Displays this help
        init [namespace]    Initializes a Zephir extension
        install             Installs the extension (requires root password)
        stubs               Generates extension PHP stubs
        version             Shows the Zephir version
    Options:
        -f([a-z0-9\-]+)     Enables compiler optimizations
        -fno-([a-z0-9\-]+)  Disables compiler optimizations
        -w([a-z0-9\-]+)     Turns a warning on
        -W([a-z0-9\-]+)     Turns a warning off
    

## 简单的例子 

1. 初始化扩展 utils 是扩展的名字 

    $ zephir init utils

1. 查看生成的目录结构
```
    tree ./hainuo
    ./hainuo/
    ├── config.json             #配置文件
    ├── ext                     #扩展目录
    │   └── kernel
    │       ├── README.md
    │       ├── array.c
    │       ├── array.h
    │       ├── assert.c
    │       ├── assert.h
    │       ├── backtrace.c
    │       ├── backtrace.h
    │       ├── debug.c
    │       ├── debug.h
    │       ├── exception.c
    │       ├── exception.h
    │       ├── exit.c
    │       ├── exit.h
    │       ├── extended
    │       │   ├── array.c
    │       │   ├── array.h
    │       │   ├── fcall.c
    │       │   └── fcall.h
    │       ├── fcall.c
    │       ├── fcall.h
    │       ├── file.c
    │       ├── file.h
    │       ├── filter.c
    │       ├── filter.h
    │       ├── globals.h
    │       ├── hash.c
    │       ├── hash.h
    │       ├── iterator.c
    │       ├── iterator.h
    │       ├── main.c
    │       ├── main.h
    │       ├── math.c
    │       ├── math.h
    │       ├── memory.c
    │       ├── memory.h
    │       ├── object.c
    │       ├── object.h
    │       ├── operators.c
    │       ├── operators.h
    │       ├── output.c
    │       ├── output.h
    │       ├── persistent.c
    │       ├── persistent.h
    │       ├── require.c
    │       ├── require.h
    │       ├── session.c
    │       ├── session.h
    │       ├── string.c
    │       ├── string.h
    │       ├── time.c
    │       ├── time.h
    │       ├── variables.c
    │       └── variables.h
    └── hainuo                  #扩展源文件目录
    4 directories, 53 files
```
1. 进入扩展源文件目录 hainuo/hainuo ,创建一个 .zep 类型的文件 greeting.zep ，语法跟PHP很相似。(我用过 hello 不行 用过 welcome 不行) 

```
    namespace Hainuo;                    //命名空间的名字必须为扩展的名字
    class Hello                          //类名必须为文件名           
    {                                   
        public static function hello()   //函数名自定义
        {
            echo "hello world!";
        }
    }
```
1. 切换到 hainuo/ 目录下，执行命令编译扩展 
```
    ( ⌁ ) e ) hainuo ) zephir build                                        22:02:36
    Preparing for PHP compilation...
    Preparing configuration file...
    Compiling...
    Installing...
    Password:
    Extension installed!
    Add extension=hainuo.so to your php.ini
    Don't forget to restart your web server
```
当出现如上代码时说明正常 然后可以通过这样进行调用。 
```
    ( ⌁ ) e ) hainuo ) php -a                                              22:20:21
    Interactive shell
    php > echo hainuo\Greeting::say();
    hello world!
    php >
```

查看扩展是否被调用可以通过以下命令 

    ( ⌁ ) e ) hainuo ) php -m  | grep h                                    22:23:35
    bcmath
    hainuo  # 在这里呢
    hash
    Phar
    shmop
    sysvshm
    

## 搞定 

一般我们完成以上内容，扩展的例子就搞定了。

什么你问我怎么在windows下，抱歉 我还真不知道。应该跟上面没啥区别。官方手册或许会有，去啃一下英文文档吧 [zephir-lang\ document][6]


[2]: https://blog.dmic.studio/posts/create-php-extent-with-zephir/
[4]: http://www.bo56.com
[5]: http://zhijia.io/circle/102336
[6]: https://docs.zephir-lang.com/en/latest/index.html