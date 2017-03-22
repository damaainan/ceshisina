# [Apache日志配置参数详细说明][0]

**Apache日志按时间分段记录**  
在apache的配置文件httpd.conf中找到  

    ErrorLog logs/error_log及CustomLog logs/access_log common  
Linux系统配置方法：  
将其改为  

    ErrorLog “| /usr/local/apache/bin/rotatelogs /home/logs/www/%Y_%m_%d_error_log 86400 480″  
    CustomLog “| /usr/local/apache/bin/rotatelogs /home/logs/www/%Y_%m_%d_access_log 86400 480″ common  
Windows系统下配置方法：  

    ErrorLog “|bin/rotatelogs.exe logs/site1/error-%y%m%d.log 86400 480″  
    CustomLog “|bin/rotatelogs.exe logs/site1/access-%y%m%d.log 86400 480″ common  
其中common为日志记录格式里设置的名称。  
若有多个站点，则应将以上配置写到各站点的VirtualHost节点中，这样才会分开文件记录各站点日志。  
> 附rotatelogs说明  

    rotatelogs logfile [ rotationtime [ offset ]] | [ filesizeM ]  
选项  
logfile  

它加上基准名就是日志文件名。如果logfile中包含’%’，则它会被视为用于的strftime(3)的格式字串；否则，它会被自动加上以秒为单位的.nnnnnnnnnn后缀。这两种格式都表示新的日志开始使用的时间。  
rotationtime  

日志文件回卷的以秒为单位的间隔时间，86400 表示一天，即每天生成一个新的日志文件。  
offset  

相对于UTC的时差的分钟数。如果省略，则默认为0，并使用UTC时间。比如，要指定UTC时差为-5小时的地区的当地时间，则此参数应为-300，北京时间为+8时间，应设置为480。这样日志里的时间才会和服务器上的时间一致，方便查看日志。  
filesizeM  

指定回卷时以兆字节为单位的后缀字母M的文件大小，而不是指定回卷时间或时差。  
  
**apache日志记录格式的设置**  
定制日志文件的格式涉及到两个指令，即LogFormat指令和CustomLog指令，默认httpd.conf文件提供了关于这两个指令的几个示例。  
LogFormat指令定义格式并为格式指定一个名字，以后我们就可以直接引用这个名字。CustomLog指令设置日志文件，并指明日志文件所用的格式（通常通过格式的名字）。  
LogFormat指令的功能是定义日志格式并为它指定一个名字。例如，在默认的httpd.conf文件中，我们可以找到下面这行代码：  

    LogFormat "%h %l %u %t \"%r\" %>s %b" common  
该指令创建了一种名为“common”的日志格式，日志的格式在双引号包围的内容中指定。格式字符串中的每一个变量代表着一项特定的信息，这些信息按照格式串规定的次序写入到日志文件。  
Apache文档已经给出了所有可用于格式串的变量及其含义，下面是其译文：  

**
%…a: 远程IP地址  
%…A: 本地IP地址  
%…B: 已发送的字节数，不包含HTTP头  
%…b: CLF格式的已发送字节数量，不包含HTTP头。例如当没有发送数据时，写入‘-’而不是0。  
%…{FOOBAR}e: 环境变量FOOBAR的内容  
%…f: 文件名字  
%…h: 远程主机  
%…H 请求的协议  
%…{Foobar}i: Foobar的内容，发送给服务器的请求的标头行。  
%…l: 远程登录名字（来自identd，如提供的话）  
%…m 请求的方法  
%…{Foobar}n: 来自另外一个模块的注解“Foobar”的内容  
%…{Foobar}o: Foobar的内容，应答的标头行  
%…p: 服务器响应请求时使用的端口  
%…P: 响应请求的子进程ID。  
%…q 查询字符串（如果存在查询字符串，则包含“?”后面的部分；否则，它是一个空字符串。）  
%…r: 请求的第一行  
%…s: 状态。对于进行内部重定向的请求，这是指*原来*请求 的状态。如果用%…>s，则是指后来的请求。  
%…t: 以公共日志时间格式表示的时间（或称为标准英文格式）  
%…{format}t: 以指定格式format表示的时间  
%…T: 为响应请求而耗费的时间，以秒计  
%…u: 远程用户（来自auth；如果返回状态（%…s）是401则可能是伪造的）  
%…U: 用户所请求的URL路径  
%…v: 响应请求的服务器的ServerName  
%…V: 依照UseCanonicalName设置得到的服务器名字   
**

在所有上面列出的变量中，“…”表示一个可选的条件。如果没有指定条件，则变量的值将以“-”取代。分析前面来自默认httpd.conf文件的 LogFormat指令示例，可以看出它创建了一种名为“common”的日志格式，其中包括：远程主机，远程登录名字，远程用户，请求时间，请求的第一 行代码，请求状态，以及发送的字节数。  
有时候我们只想在日志中记录某些特定的、已定义的信息，这时就要用到“…”。如果在“%”和变量之间放入了一个或者多个HTTP状态代码，则只有当请 求返回的状态代码属于指定的状态代码之一时，变量所代表的内容才会被记录。例如，如果我们想要记录的是网站的所有无效链接，那么可以使用：  
LogFormat %404{Referer}i BrokenLinks  
反之，如果我们想要记录那些状态代码不等于指定值的请求，只需加入一个“!”符号即可：  
    LogFormat %!200U SomethingWrong  
  
**专门记录某个蜘蛛记录**  

    SetEnvIfNoCase User-Agent Baiduspider baidu_robot  
    LogFormat “%h %t \”%r\” %>s %b” robot  
linux下  

    CustomLog “|/usr/local/apache2.2.0/bin/rotatelogs /usr/local/apache2.2.0/logs/baidu_%Y%m%d.txt 86400 480″ robot env=baidu_robot  
windows下  

    CustomLog “|bin/rotatelogs.exe logs/baidu_%Y%m%d.txt 86400 480″ robot env=baidu_robot  
这样在logs目录下，就会每天产生baidu_年月日.txt的日志了，每条的记录和下面的类似：  

    61.135.168.14 [22/Oct/2008:22:21:26 +0800] “GET / HTTP/1.1″ 200 8427  
  
**去掉日志中的图片、js、css、swf文件**  

    <FilesMatch "\.(ico|gif|jpg|png|bmp|swf|css|js)">  
    SetEnv IMAG 1  
    </FilesMatch>  
    CustomLog "|bin/cronolog.exe logs/cpseadmin/access_%Y%m%d.log" combined env=!IMAG

后话：至于为什么要加日志，当你被攻击恶意访问的时候，能很快的定位到哪个站点，悲剧的我给人攻击了，关键是我没加日志，差不多，2个小时60G流量没有啦！！！！

[0]: http://www.cnblogs.com/EasonJim/p/5411337.html