apache自带的rotatelogs.exe工具实现每天生成新日志



网上很多资料都有对Apache的access.log按天生成的方法，但在Windows server下稍有不同：

1、打开httpd.conf配置文件找到：

CustomLog "logs/access.log" common

2、将其改为：

CustomLog "|bin/rotatelogs.exe logs/%Y_%m_%d.access.log 86400 480" common

  
红色部分与网上大部分资料不同，windows下应使用相对路径，使用绝对路径Apache会启动会报错。

http://blog.csdn.net/liyan_5976/article/details/5636913

===========================================================================

在apache的配置文件中找到  
ErrorLog logs/error_log  
CustomLog logs/access_log common

**Linux系统配置方法：**

将其改为   
    
    ErrorLog "| /usr/local/apache/bin/rotatelogs /home/logs/www/%Y_%m_%d_error_log 86400 480"  
    CustomLog "| /usr/local/apache/bin/rotatelogs /home/logs/www/%Y_%m_%d_access_log 86400 480" common

**Windows系统下配置方法：**

    #ErrorLog "|bin/rotatelogs.exe logs/vicp_net_error-%y%m%d.log 86400 480"  
    #CustomLog "|bin/rotatelogs.exe logs/vicp_net_access-%y%m%d.log 86400 480" common

第一次不知道设置480这个参数，导致日志记录时间和服务器时间相差8小时，原来是rotatelogs有一个offset参数，表示相对于UTC的时差分钟数，中国是第八时区，相差480分钟。86400是表示1天。

附rotatelogs说明

rotatelogs logfile [ rotationtime [ offset ]] | [ filesizeM ]

#### 选项  
** logfile  **
它加上基准名就是日志文件名。如果logfile中包含’%'，则它会被视为用于的strftime(3)的格式字串；否则，它会被自动加上以秒为单位的.nnnnnnnnnn后缀。这两种格式都表示新的日志开始使用的时间。  
** rotationtime  **
日志文件回卷的以秒为单位的间隔时间  
** offset  **
相对于UTC的时差的分钟数。如果省略，则假定为0，并使用UTC时间。比如，要指定UTC时差为-5小时的地区的当地时间，则此参数应为-300。  
** filesizeM  **
指定回卷时以兆字节为单位的后缀字母M的文件大小，而不是指定回卷时间或时差。

---------------

httpd.conf中CustomLog logs/access.log common 改成

CustomLog "|c:/apache/bin/rotatelogs.exe c:/apache/logs/access_%Y_%m_%d.log 86400" common

其中把c:改成你安装apache所在的路径.

重启Apache

其中c:/apache/是你安装apache的路径这样每一天生成一个日志文件

