# [xdebug 常用函数][0]

转自：http://blog.csdn.net/samxx8/article/details/7050282

string xdebug_call_class() 返回当前被调用的函数或方法所属的类的类名

string xdebug_call_file() 返回调用当前函数的文件名

string xdebug_call_function() 返回调用当前正在执行的函数的函数名

int xdebug_call_line() 返回该函数是在哪一行被调用的。

void xdebug_disable()/xdebug_enable() 禁止/激活显示错误的跟踪栈信息

xdebug_start_error_collection()

xdebug_stop_error_collection()

xdebug_get_collected_errors()   
错误收集开始函数，当此函数被执行的时候，xdebug将不在页面上显示错误信息，而是将错误信息以xdebug  
自己的规则记录在缓冲区。直到遇到xdebug_stop_error_collection()函数。缓冲区的内容将由xdebug_get_collected_errors()  
函数的调用而显示。此功能可以让你的页面不被xdebug的错误显示破坏。

array xdebug_get_headers() 返回所有由php设置的头信息。比如由header(),setcookie函数设置的头信息.

xdebug_is_enabled() 返回xdebug的跟踪状态是否被激活 xdebug.default_enable的值

int xdebug_memory_usage() 返回脚本当前的内存使用数

int xdebug_peak_memory_usage() 返回脚本直达目前为止这段过程中的使用内存的最高值

float xdebug_time_index() 返回脚本开始到现在所使用的秒数

### 变量显示功能

var_dump( [mixed var [, ...]])   
void `xdebug_debug_zval`( [stringvarname [, ...]] )   
void `xdebug_debug_zval_stdout`([string varname [, ...]] )   
返回一个变量的标准输出信息，包括类型，值，引用次数等。。

void xdebug_dump_superglobals() 返回全局变量的信息

void `xdebug_var_dump`( [mixed var [,...]] ) 显示变量的详细信息  
  
### 堆栈跟踪

array xdebug_get_declared_vars() 返回申明的变量集合

array xdebug_get_function_stack() 返回跟踪栈的详细信息(跟踪函数执行步骤)

### 函数跟踪  
xdebug_start_code_coverage（）   

###开始跟踪

arrayxdebug_get_code_coverage()   
返回代码执行去向

[0]: http://www.cnblogs.com/hxphp/p/6559465.html