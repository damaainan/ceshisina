# Linux系统要如何分析Nginx日志

 时间 2017-09-19 17:40:06  

原文[http://www.jianshu.com/p/1036566dea2d][1]


也许在目前许多学者都不知道如何分析Nginx日志，Linux系统日志下的Nginx 日志可以查看系统运行记录和出错说明，对Nginx 日志的分析可以了解系统运行的状态。那么Linux系统Nginx日志怎么分析呢?下面小编为你详解一下Linux系统入门学习的内容： 

![][4]

Nginx 日志相关配置有 2 个地方：`Access_log` 和 `log_format` 。

默认的格式：

    access_log /data/logs/nginx-access.log;

    log_format old '$remote_addr [$time_local] $status $request_time $body_bytes_sent '

    '"$request" "$http_referer" "$http_user_agent"';

相信大部分用过 Nginx 的人对默认 Nginx 日志格式配置都很熟悉，对日志的内容也很熟悉。但是默认配置和格式虽然可读，但是难以计算。

Nginx 日志刷盘相关策略可配置：

比如，设置 buffer，buffer 满 32k 才刷盘;假如 buffer 不满 5s 钟强制刷盘的配置如下：

    access_log /data/logs/nginx-access.log buffer=32k flush=5s;

这决定了是否实时看到日志以及日志对磁盘 IO 的影响。

Nginx 日志能够记录的变量还有很多没出现在默认配置中：

比如：

请求数据大小：$request_length

返回数据大小：$bytes_sent

请求耗时：$request_time

所用连接序号：$connection

当前连接发生请求数：$connection_requests

Nginx 的默认格式不可计算，需要想办法转换成可计算格式，比如用控制字符 ^A (Mac 下 ctrl+v ctrl+a 打出)分割每个字段。

log_format 的格式可以变成这样：

    log_format new '$remote_addr^A$http_x_forwarded_for^A$host^A$time_local^A$status^A'

    '$request_time^A$request_length^A$bytes_sent^A$http_referer^A$request^A$http_user_agent';

这样之后就通过常见的 Linux 命令行工具进行分析了：

查找访问频率最 高的 URL 和次数：

    cat access.log | awk -F '^A' '{print $10}' | sort | uniq -c

查找当前日志文件 500 错误的访问：

    cat access.log | awk -F '^A' '{if($5 == 500) print $0}'

查找当前日志文件 500 错误的数量：

    cat access.log | awk -F '^A' '{if($5 == 500) print $0}' | wc -l

查找某一分钟内 500 错误访问的数量：

    cat access.log | awk -F '^A' '{if($5 == 500) print $0}' | grep '09:00' | wc-l

查找耗时超过 1s 的慢请求：

    tail -f access.log | awk -F '^A' '{if($6》1) print $0}'

假如只想查看某些位：

    tail -f access.log | awk -F '^A' '{if($6》1) print $3″|"$4}'

查找 502 错误最 多的 URL：

    cat access.log | awk -F '^A' '{if($5==502) print $11}' | sort | uniq -c

查找 200 空白页

    cat access.log | awk -F '^A' '{if($5==200 && $8 《 100) print $3″|"$4″|"$11″|"$6}'

查看实时日志数据流

    tail -f access.log | cat -e

或者

    tail -f access.log | tr '^A' '|'

照着这个思路可以做很多其他分析，比如 UA 最 多的访问;访问频率最 高的 IP;请求耗时分析;请求返回包大小分析;等等。

这就是一个大型 Web 日志分析系统的原型，这样的格式也是非常方便进行后续大规模 `batching` 和 `streaming` 计算。

Linux系统入门学习的基础学会了吗，以上就是Linux系统Nginx日志怎么分析的全部内容了，Linux系统日志可以看出来Nginx日志还是有很强大的作用的。


[1]: http://www.jianshu.com/p/1036566dea2d

[4]: http://img0.tuicool.com/Qviiquz.jpg