# Xdebug常用设置解析

 时间 2017-06-26 18:41:54  

原文[http://www.blogsir.com.cn/safe/374.html][1]


Xdebug 是一个开放源代码的php程序调试器(及一个Debug工具）)可以用来跟踪，调试和分析php程序的运行状况功能强大的神器，对审计有非常大的帮助

窝的xdebug的设置如下:

```ini
    xdebug.auto_trace = 1
    xdebug.trace_format = 0
    xdebug.trace_output_dir ="D:\Database\phpStudy\phpStudy\tmp\xdebug"
    xdebug.trace_options = 0
    xdebug.trace_format = 0
    xdebug.trace_output_name = "trace.%f%s%R"
    xdebug.collect_params = 4
    xdebug.collect_return = 1
    xdebug.collect_vars = 1
    xdebug.collect_assignments = 1
    xdebug.profiler_append = 0
    xdebug.profiler_enable = 1
    xdebug.profiler_enable_trigger = 0
    xdebug.profiler_output_dir ="D:\Database\phpStudy\phpStudy\tmp\xdebug"
    xdebug.profiler_output_name = "cache.out.%t-%s"
    xdebug.remote_enable = 1
    xdebug.remote_handler = "dbgp"
    xdebug.remote_host = "127.0.0.1:82"
    zend_extension="D:\Database\phpStudy\phpStudy\php55n\ext\xdebug.dll"
```

#### 代码跟踪相关配置

- xdebug.auto_trace
boolean类型，默认值0用于设定在脚本运行前是否自动跟踪方法的调用信息。- xdebug.collect_assignments
boolean 类型， 默认值: 0,在Xdebug 2.1中控制Xdebug是否应该对函数轨迹添加变量分配追踪.- xdebug.collect_return
boolean类型，默认值0。用于设定是否返回调用方法的返回值。- xdebug.trace_format
类型:integer（整型）, 默认值:0,轨迹文件的格式。- xdebug.trace_output_dir
--日志追踪输出目录

xdebug.trace_options

1=追加 ， 0=覆盖- xdebug.trace_output_name
日志文件名,但后缀还是.xt
#### 分析PHP脚本

- xdebug.profiler_append
整型 默认值：0当这个参数被设置为1时，文件将不会被追加，当一个新的需求到一个相同的文件时(依靠xdebug.profiler_output_name的设置)。相反的设置的话，文件将被附加成一个新文件。- xdebug.profiler_output_dir
字符串 默认值：/tmp

这个文件是profiler文件输出写入的，确信PHP用户对这个目录有写入的权限。这个设置不能通过在你的脚本中调用ini_set()来设置。

- xdebug.profiler_output_name
类型：字符串 默认值：cachegrind.out%p,这个设置决定了转储跟踪写入的文件的名称。
#### 远程Debug

- xdebug.remote_autostart
类型：布尔型 默认值：0一般来说，你需要使用明确的HTTP GET/POST变量来开启远程debug。而当这个参数设置为On，xdebug将经常试图去开启一个远程debug session并试图去连接客户端，即使GET/POST/COOKIE变量不是当前的。- xdebug.remote_enable
类型：布尔型 默认值：0这个开关控制xdebug是否应该试着去连接一个按照xdebug.remote_host和xdebug.remote_port来设置监听主机和端口的debug客户端。- xdebug.profiler_enable_trigger
boolean类型，默认值0。如果开启该选项，则在每次请求中如果GET/POST或cookie中包含XDEBUG_PROFILE变量名，则才会生成性能报告文件(前提是必须关闭xdebug.profiler_enable选项，否则该选项不起作用)。- xdebug.remote_host
类型：字符串 默认值：localhost选择debug客户端正在运行的主机，你不仅可以使用主机名还可以使用IP地址- xdebug.remote_port
类型：整型 默认值：9000

这个端口是xdebug试着去连接远程主机的。9000是一般客户端和被绑定的debug客户端默认的端口。许多客户端都使用这个端口数字，最好不要去修改这个设置。


## xdebug的格式解析

    TRACE START [2017-06-26 08:44:41]
        0.0292     119248   -> {main}() C:\Users\lj\AppData\Local\Temp\tmpg_tofw.php:0
        0.0296     119304     -> phpversion() C:\Users\lj\AppData\Local\Temp\tmpg_tofw.php:1
                               >=> '5.5.17'
        0.0300     119336     -> ini_get('include_path') C:\Users\lj\AppData\Local\Temp\tmpg_tofw.php:1
                               >=> '.;C:\\php\\pear'
                             >=> 1
        0.0305 zu
    TRACE END   [2017-06-26 08:44:41]
     
     
     
    

可以看到如上，是xdebug追踪的php执行情况

* 首先是{main}()表示是入口文件，写明了文件名和行号，用:隔开
* 箭头缩进，表示进入该函数入口，执行了phpversion()函数
* 接着执行了 init_get(include_path') 函数,表示是一个get请求 //>=>每看明白是啥
* >=>1 表示返回到上一层，即结束了整个文件入口

需要注意的是:

1) 每隔箭头的递进和并列都有其意义,返回上一层也会有>=>1提示

2) xdebug对字符串中的单引号会自动转义，这点要搞清楚

#### xdebug.trace_format 的一些显示取值格式:

值 | 描述 
-|-
0 | 显示人类可读的轨迹文件内容: 时间索引, 内存使用, 内存增量 (如果xdebug.show_mem_delta参数被启用), 层级, 函数名, 函数参数 (如果xdebug.collect_params被启用), 文件名 和 行号. 
1 | 写入含有两种不同记录的计算机可读的格式。 有很多不同的记录可以用来进入或离开一个堆栈结构。下面的表格列出了每种类型的记录的相关栏目，栏目之间通过制表位隔开。 
2 | 写入HTML格式的轨迹。 

xdebug.trace_format = 0时

    TRACE START [2017-06-26 10:14:50]
        0.0121     126152   -> {main}() F:\T00LS\database\html\shenji\zzcms\test.php:0
        0.0124     126184     -> phpinfo() F:\T00LS\database\html\shenji\zzcms\test.php:2
                               >=> TRUE
                               => $a = '1\' and 1=1 ' F:\T00LS\database\html\shenji\zzcms\test.php:3
                             >=> 1
        0.0297 zu
    TRACE END   [2017-06-26 10:14:50]
     
     
     
    

xdebug.trace_format = 1时

    Version: 2.2.5
    File format: 2
    TRACE START [2017-06-26 10:13:50]
    1   0   0   0.001764    125208  {main}  1       F:\T00LS\database\html\shenji\zzcms\test.php    0   0
    2   1   0   0.002148    125240  phpinfo 0       F:\T00LS\database\html\shenji\zzcms\test.php    2   0
    2   1   1   0.007957    215576
    1   0   1   0.008230    215624
                0.020850    8776
    TRACE END   [2017-06-26 10:13:50]
     
     
     
    

xdebug.trace_format = 2时

    <table class='xdebug-trace' dir='ltr' border='1' cellspacing='0'>
        <tr><th>#</th><th>Time</th><th>Mem</th><th colspan='2'>Function</th><th>Location</th></tr>
        <tr><td>0</td><td>0.012145</td><td align='right'>126152</td><td align='left'>-></td><td>{main}()</td><td>F:\T00LS\database\html\shenji\zzcms\test.php:0</td></tr>
        <tr><td>1</td><td>0.012535</td><td align='right'>126184</td><td align='left'>   -></td><td>phpinfo()</td><td>F:\T00LS\database\html\shenji\zzcms\test.php:2</td></tr>
    </table>
     
     
    

#### xdebug.trace_output_name的一些命令格式

操作符 | 意义 | 示例格式 | 示例文件名 
-|-|-|-
%c | crc32 |  of the current working directory trace.%c | trace.1258863198.xt 
%p | pid |  trace.%p | trace.5174.xt 
%r | random |  number trace.%r | trace.072db0.xt 
%s | script |  name 2 cachegrind.out.%s | `cachegrind.out._home_httpd_html_test_xdebug_test_php` 
%t | timestamp (seconds) |  trace.%t | trace.1179434742.xt 
%u | timestamp (microseconds) |  trace.%u | trace.1179434749_642382.xt 
%H | `$_SERVER['HTTP_HOST']` |  trace.%H | trace.kossu.xt 
%R | `$_SERVER['REQUEST_URI']` |  trace.%R | `trace._test_xdebug_test_php_var=1_var2=2.xt` 
%U | `$_SERVER['UNIQUE_ID']` |  3 trace.%U | trace.TRX4n38AAAEAAB9gBFkAAAAB.xt 
%S | session_id |  (from $_COOKIE if set) | trace.%S | trace.c70c1ec2375af58f74b390bbdd2a679d.xt 
%% | literal |  % trace.%% | trace.%%.xt 

参考文章:http://sunlufu2009.blog.163.com/blog/static/149068329201343132415573/

http://www.xingfeilong.cn/article/2013-08-28/86.shtml

[0]: /sites/3mQvQvI
[1]: http://www.blogsir.com.cn/safe/374.html?utm_source=tuicool&utm_medium=referral
[2]: /topics/11120015