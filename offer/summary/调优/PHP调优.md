<font face=微软雅黑>

###   PHP优化对于PHP的优化主要是对==php.ini==中的相关主要参数进行合理调整和设置，以下我们就来看看php.ini中的一些对性能影响较大的参数应该如何设置。

(1) PHP函数禁用找到：

    disable_functions =

(2) PHP脚本执行时间找到：

    max_execution_time = 30

(3) PHP脚本处理内存占用找到：

    memory_limit = 8M

该选项指定PHP脚本处理所能占用的最大内存，默认为8MB，如果您的服务器内存为1GB以上，则该选项可以设置为12MB以获得更快的PHP脚本处理效率。

(4) PHP全局函数声明找到：

    register_globals = Off

(5) PHP上传文件大小限制找到：

    upload_max_filesize = 2M

(6) Session存储介质找到：

    session.save_path



</font>