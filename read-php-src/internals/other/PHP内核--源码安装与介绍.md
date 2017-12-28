# [PHP内核--源码安装与介绍][0]

 2016-10-21 00:03  1105人阅读  

 分类：

版权声明：本文为博主原创文章，转载请说明出处。

****

**获取PHP源码**

为了学习PHP的实现，首先需要下载PHP的源代码。下载源码首选是去[PHP官方网站http://php.net/downloads.php][5]下载， 如果你喜欢使用svn/git等版本控制软件，也可以使用svn/git来获取最新的源代码。



    # git 官方地址
    git clone https://git.php.net/repository/php-src.git
    # 也可以访问github官方镜像
    git clone git://github.com/php/php-src.git
    cd php-src && git checkout PHP-5.3 
    # 签出5.3分支 **PHP源码目录结构**

俗话讲：重剑无锋，大巧不工。PHP的源码在结构上非常清晰。下面先简单介绍一下PHP源码的目录结构。

* 根目录: / 这个目录包含的东西比较多，主要包含一些说明文件以及设计方案。 其实项目中的这些README文件是非常值得阅读的例如：
* /README.PHP4-TO-PHP5-THIN-CHANGES 这个文件就详细列举了PHP4和PHP5的一些差异。
* 还有有一个比较重要的文件/CODING_STANDARDS，如果要想写PHP扩展的话，这个文件一定要阅读一下， 不管你个人的代码风格是什么样，怎么样使用缩进和花括号，既然来到了这样一个团体里就应该去适应这样的规范，这样在阅读代码或者别人阅读你的 代码是都会更轻松。
* build 顾名思义，这里主要放置一些和源码编译相关的一些文件，比如开始构建之前的buildconf脚本等文件，还有一些检查环境的脚本等。
* ext 官方扩展目录，包括了绝大多数PHP的函数的定义和实现，如array系列，pdo系列，spl系列等函数的实现，都在这个目录中。个人写的扩展在测试时也可以放到这个目录，方便测试和调试。
* main 这里存放的就是PHP最为核心的文件了，主要实现PHP的基本设施，这里和Zend引擎不一样，Zend引擎主要实现语言最核心的语言运行环境。
* Zend Zend 引擎的实现目录，比如脚本的词法语法解析，opcode的执行以及扩展机制的实现等等。
* pear “PHP 扩展与应用仓库”，包含PEAR的核心文件。
* sapi 包含了各种服务器抽象层的代码，例如apache的mod_php，cgi，fastcgi以及fpm等等接口。
* TSRM PHP的线程安全是构建在TSRM库之上的，PHP实现中常见的*G宏通常是对TSRM的封装，TSRM(Thread Safe Resource Manager)线程安全资源管理器。
* tests PHP的测试脚本集合，包含PHP各项功能的测试文件
* win32 这个目录主要包括Windows平台相关的一些实现，比如sokcet的实现在Windows下和*Nix平台就不太一样，同时也包括了Windows下编译PHP相关的脚本。
* 
* ![][7] **PHP中的全局变量宏**

在PHP的源码中经常会看到的一些很常见的宏，或者有些对于才开始接触源码的读者比较难懂的代码。 这些代码在PHP的源码中出现的频率极高，基本在每个模块都会有他们的身影。

在PHP代码中经常能看到一些类似PG()， EG()之类的函数，他们都是PHP中定义的宏，这系列宏主要的作用是解决线程安全所写的全局变量包裹宏， 如$PHP_SRC/main/php_globals.h文件中就包含了很多这类的宏。例如PG这个PHP的核心全局变量的宏。 如下所示代码为其定义。

```c
    #ifdef ZTS   // 编译时开启了线程安全则使用线程安全库
    # define PG(v) TSRMG(core_globals_id, php_core_globals *, v)
    extern PHPAPI int core_globals_id;
    #else
    # define PG(v) (core_globals.v) // 否则这其实就是一个普通的全局变量
    extern ZEND_API struct _php_core_globals core_globals;
    #endif
```
  
如上，ZTS是线程安全的标记，这个在以后的章节会详细介绍，这里就不再说明。下面简单说说，PHP运行时的一些全局参数， 这个全局变量为如下的一个结构体，各字段的意义如字段后的注释：

```c
    struct _php_core_globals {
            zend_bool magic_quotes_gpc; //  是否对输入的GET/POST/Cookie数据使用自动字符串转义。
            zend_bool magic_quotes_runtime; //是否对运行时从外部资源产生的数据使用自动字符串转义
            zend_bool magic_quotes_sybase;  //   是否采用Sybase形式的自动字符串转义
     
            zend_bool safe_mode;    //  是否启用安全模式
     
            zend_bool allow_call_time_pass_reference;   //是否强迫在函数调用时按引用传递参数
            zend_bool implicit_flush;   //是否要求PHP输出层在每个输出块之后自动刷新数据
     
            long output_buffering;  //输出缓冲区大小(字节)
     
            char *safe_mode_include_dir;    //在安全模式下，该组目录和其子目录下的文件被包含时，将跳过UID/GID检查。
            zend_bool safe_mode_gid;    //在安全模式下，默认在访问文件时会做UID比较检查
            zend_bool sql_safe_mode;
            zend_bool enable_dl;    //是否允许使用dl()函数。dl()函数仅在将PHP作为apache模块安装时才有效。
     
            char *output_handler;   // 将所有脚本的输出重定向到一个输出处理函数。
     
            char *unserialize_callback_func;    // 如果解序列化处理器需要实例化一个未定义的类，这里指定的回调函数将以该未定义类的名字作为参数被unserialize()调用，
            long serialize_precision;   //将浮点型和双精度型数据序列化存储时的精度(有效位数)。
     
            char *safe_mode_exec_dir;   //在安全模式下，只有该目录下的可执行程序才允许被执行系统程序的函数执行。
     
            long memory_limit;  //一个脚本所能够申请到的最大内存字节数(可以使用K和M作为单位)。
            long max_input_time;    // 每个脚本解析输入数据(POST, GET, upload)的最大允许时间(秒)。
     
            zend_bool track_errors; //是否在变量$php_errormsg中保存最近一个错误或警告消息。
            zend_bool display_errors;   //是否将错误信息作为输出的一部分显示。
            zend_bool display_startup_errors;   //是否显示PHP启动时的错误。
            zend_bool log_errors;   // 是否在日志文件里记录错误，具体在哪里记录取决于error_log指令
            long      log_errors_max_len;   //设置错误日志中附加的与错误信息相关联的错误源的最大长度。
            zend_bool ignore_repeated_errors;   //   记录错误日志时是否忽略重复的错误信息。
            zend_bool ignore_repeated_source;   //是否在忽略重复的错误信息时忽略重复的错误源。
            zend_bool report_memleaks;  //是否报告内存泄漏。
            char *error_log;    //将错误日志记录到哪个文件中。
     
            char *doc_root; //PHP的”根目录”。
            char *user_dir; //告诉php在使用 /~username 打开脚本时到哪个目录下去找
            char *include_path; //指定一组目录用于require(), include(), fopen_with_path()函数寻找文件。
            char *open_basedir; // 将PHP允许操作的所有文件(包括文件自身)都限制在此组目录列表下。
            char *extension_dir;    //存放扩展库(模块)的目录，也就是PHP用来寻找动态扩展模块的目录。
     
            char *upload_tmp_dir;   // 文件上传时存放文件的临时目录
            long upload_max_filesize;   // 允许上传的文件的最大尺寸。
     
            char *error_append_string;  // 用于错误信息后输出的字符串
            char *error_prepend_string; //用于错误信息前输出的字符串
     
            char *auto_prepend_file;    //指定在主文件之前自动解析的文件名。
            char *auto_append_file; //指定在主文件之后自动解析的文件名。
     
            arg_separators arg_separator;   //PHP所产生的URL中用来分隔参数的分隔符。
     
            char *variables_order;  // PHP注册 Environment, GET, POST, Cookie, Server 变量的顺序。
     
            HashTable rfc1867_protected_variables;  //  RFC1867保护的变量名，在main/rfc1867.c文件中有用到此变量
     
            short connection_status;    //  连接状态，有三个状态，正常，中断，超时
            short ignore_user_abort;    //  是否即使在用户中止请求后也坚持完成整个请求。
     
            unsigned char header_is_being_sent; //  是否头信息正在发送
     
            zend_llist tick_functions;  //  仅在main目录下的php_ticks.c文件中有用到，此处定义的函数在register_tick_function等函数中有用到。
     
            zval *http_globals[6];  // 存放GET、POST、SERVER等信息
     
            zend_bool expose_php;   //  是否展示php的信息
     
            zend_bool register_globals; //  是否将 E, G, P, C, S 变量注册为全局变量。
            zend_bool register_long_arrays; //   是否启用旧式的长式数组(HTTP_*_VARS)。
            zend_bool register_argc_argv;   //  是否声明$argv和$argc全局变量(包含用GET方法的信息)。
            zend_bool auto_globals_jit; //  是否仅在使用到$_SERVER和$_ENV变量时才创建(而不是在脚本一启动时就自动创建)。
     
            zend_bool y2k_compliance;   //是否强制打开2000年适应(可能在非Y2K适应的浏览器中导致问题)。
     
            char *docref_root;  // 如果打开了html_errors指令，PHP将会在出错信息上显示超连接，
            char *docref_ext;   //指定文件的扩展名(必须含有’.')。
     
            zend_bool html_errors;  //是否在出错信息中使用HTML标记。
            zend_bool xmlrpc_errors;   
     
            long xmlrpc_error_number;
     
            zend_bool activated_auto_globals[8];
     
            zend_bool modules_activated;    //  是否已经激活模块
            zend_bool file_uploads; //是否允许HTTP文件上传。
            zend_bool during_request_startup;   //是否在请求初始化过程中
            zend_bool allow_url_fopen;  //是否允许打开远程文件
            zend_bool always_populate_raw_post_data;    //是否总是生成$HTTP_RAW_POST_DATA变量(原始POST数据)。
            zend_bool report_zend_debug;    //  是否打开zend debug，仅在main/main.c文件中有使用。
     
            int last_error_type;    //  最后的错误类型
            char *last_error_message;   //  最后的错误信息
            char *last_error_file;  //  最后的错误文件
            int  last_error_lineno; //  最后的错误行
     
            char *disable_functions;    //该指令接受一个用逗号分隔的函数名列表，以禁用特定的函数。
            char *disable_classes;  //该指令接受一个用逗号分隔的类名列表，以禁用特定的类。
            zend_bool allow_url_include;    //是否允许include/require远程文件。
            zend_bool exit_on_timeout;  //  超时则退出
    #ifdef PHP_WIN32
            zend_bool com_initialized;
    #endif
            long max_input_nesting_level;   //最大的嵌套层数
            zend_bool in_user_include;  //是否在用户包含空间
     
            char *user_ini_filename;    //  用户的ini文件名
            long user_ini_cache_ttl;    //  ini缓存过期限制
     
            char *request_order;    //  优先级比variables_order高，在request变量生成时用到，个人觉得是历史遗留问题
     
            zend_bool mail_x_header;    //  仅在ext/standard/mail.c文件中使用，
            char *mail_log;
     
            zend_bool in_error_log;
    };
```
上面的字段很大一部分是与php.ini文件中的配置项对应的。 在PHP启动并读取php.ini文件时就会对这些字段进行赋值， 而用户空间的ini_get()及ini_set()函数操作的一些配置也是对这个全局变量进行操作的。

在PHP代码的其他地方也存在很多类似的宏，这些宏和PG宏一样，都是为了将线程安全进行封装，同时通过约定的 G 命名来表明这是全局的， 一般都是个缩写，因为这些全局变量在代码的各处都会使用到，这也算是减少了键盘输入。 我们都应该[尽可能的懒][8]不是么？

如果你阅读过一些PHP扩展话应该也见过类似的宏，这也算是一种代码规范，在编写扩展时全局变量最好也使用这种方式命名和包裹， 因为我们不能对用户的PHP编译条件做任何假设。

[0]: http://blog.csdn.net/ty_hf/article/details/52877294
[5]: http://php.net/downloads.php
[6]: #
[7]: ../img/20161020214520627.png
[8]: http://blogoscoped.com/archive/2005-08-24-n14.html