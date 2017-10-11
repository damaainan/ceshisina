# httpd配置文件规则说明和一些基本指令

时间 2017-10-08 11:09:00  

原文[http://www.cnblogs.com/f-ck-need-u/p/7636836.html][2]



本文主要介绍介绍的是httpd的配置文件，包括一些最基本的指令、配置规则、配置合并规则。以下指令完全来自官方手册以及我自己的总结和整理。

## 1.1 httpd命令和apachectl命令

    [root@xuexi ~]# httpd -h
    Usage: httpd [-D name] [-d directory] [-f file]
                 [-C "directive"] [-c "directive"]
                 [-k start|restart|graceful|graceful-stop|stop]
                 [-v] [-V] [-h] [-l] [-L] [-t] [-T] [-S] [-X]
    Options:
      -D name            : 定义一个在< IfDefine name >中使用的name，以此容器中的指令
      -d directory       : 指定ServerRoot
      -f file            : 指定配置文件
      -C "directive"     : 指定在加载配置文件前要处理的指令(directive)
      -c "directive"     : 指定在加载配置文件后要处理的指令
      -e level           : 显示httpd启动时的日志调试级别
      -E file            : 将启动信息记录到指定文件中
      -v                 : 显示版本号
      -V                 : 显示编译配置选项
      -h                 : 显示帮助信息
      -l                 : 显示已编译但非动态编译的模块，即静态编译的模块
      -L                 : 显示静态模块可用的指令列表
      -t -D DUMP_VHOSTS  : 显示虚拟主机的设置信息
      -t -D DUMP_RUN_CFG : 显示运行参数
      -S                 : 等价于-t -D DUMP_VHOSTS -D DUMP_RUN_CFG。在调试如何解析配置文件时非常非常有用
      -t -D DUMP_MODULES : 显示所有已被加载的模块，包括静态和动态编译的模块
      -M                 : 等价于-t -D DUMP_MODULES
      -t                 : 检查配置文件语法
      -T                 : 不检查DocumentRoot，直接启动
      -X                 : 调试模式，此模式下httpd进程依赖于终端
      -k                 : 管理httpd进程，接受start|restart|graceful|graceful-stop|stop

apachectl命令和httpd命令基本相同。httpd接受的选项，apachectl都接受。但apachectl还可以省略"-k"选项直接管理httpd进程。

* `apachectl [-k] start` ：按照默认路径，读取默认配置文件，并启动httpd。
* `apachectl [-k] stop` ：关闭httpd进程。
* `apachectl [-k] restart` ：重启httpd进程。
* `apachectl [-k] graceful-stop` ：graceful stop，表示让已运行的httpd进程不再接受新请求，并给他们足够的时间处理当前正在处理的事情，处理完成后才退出。所以在进程退出前，日志文件暂时不会关闭，正在进行的连接暂时不会断开。
* `apachectl [-k] graceful` ：graceful restart，即graceful-stop+start。
* `apachectl [-k] configtest` ：语法检查。

在systemd环境下，还可以使用 `apacectl status` 或 `systemctl status httpd` 查看httpd进程的详细信息。 

## 1.2 配置文件规则和常见指令

httpd的核心体现在配置文件，各种功能都通过配置文件来实现。使用rpm包安装的httpd默认配置文件为/etc/httpd/conf/httpd.conf。可以使用 `httpd -f config_path` 指定要加载的配置文件。 

配置文件中全是一些指令配置，每个指令都是某个模块提供的。以下是配置文件的一些规则：

1. 指令生效方式是从上往下读取，这一点非常非常重要。很多指令的位置强烈建议不要改变，例如 `Include conf.d/*.conf` 指令建议不要移动位置。
1. "#"开头的行为注释行，只能行头注释，不能行中注释。
1. 对大小写不敏感，但是建议指令名称采用"驼峰式"命名。例如ServerRoot，DocumentRoot。
1. 一行写不下的可以使用"\"续行，但是"\"后不能有任何字符，包括空格也不允许。
1. 指令配置格式为"Directive value"，例如"ServerRoot /etc/httpd"，如果value中包含特殊字符或空格，则必须使用双引号包围。
1. 由于可以通过Include指令包含其他配置文件，又支持各种路径的容器，所以在httpd启动时会先进行配置文件的合并。理解合并规则非常重要，具体见。

### 1.2.1 Listen指令

设置监听套接字。设置方式很简单，包括以下几种情况：

    # 监听两个端口
    Listen 80
    Listen 8000
    
    # 监听套接字绑定在给定地址和端口上
    Listen 192.170.2.1:80
    Listen 192.170.2.5:8000

### 1.2.2 ServerRoot指令

该指令设置httpd的安装位置，也就是常称之为的basedir，在此目录下应该具有module、logs等目录。rpm安装的httpd的ServerRoot默认为/etc/httpd，编译安装的ServerRoot路径由"--prefix"选项指定，例如/usr/local/apache。

    [root@xuexi ~]# ls -l /usr/local/apache/
    total 52
    drwxr-xr-x  2 root root  4096 Sep 27 20:46 bin
    drwxr-xr-x  2 root root  4096 Sep 27 20:46 build
    drwxr-xr-x  2 root root  4096 Sep 27 20:46 cgi-bin
    drwxr-xr-x  3 root root  4096 Sep 27 20:46 error
    drwxr-xr-x  2 root root  4096 Sep 30 11:33 htdocs
    drwxr-xr-x  3 root root  4096 Sep 27 20:46 icons
    drwxr-xr-x  2 root root  4096 Sep 27 20:46 include
    drwxr-xr-x  2 root root  4096 Sep 30 01:40 logs
    drwxr-xr-x  4 root root  4096 Sep 27 20:46 man
    drwxr-xr-x 14 root root 12288 Jul  7 01:38 manual
    drwxr-xr-x  2 root root  4096 Sep 27 20:46 modules

这个指令很关键，安装好apache后一般不会去做任何修改，因为很多指令的路径以及相对路径都是基于此路径的。严格地说，  除了网络路径，基本上所有本地文件系统类的路径只要不是绝对路径，相对路径都基于此路径展开  。 

例如，当指定"ServerRoot /usr/local/apache"时，下面几个指令中描述的本地路径，等号前面的采用的都是相对路径，等号右边的都是他们等价的绝对路径写法。

```apache
    DocumentRoot "htdocs"                    = DocumentRoot "/usr/local/apache/htdocs"
    LoadModule dir_module modules/mod_dir.so = LoadModule dir_module /usr/local/apache/modules/mod_dir.so
    ErrorLog "logs/error_log"                = ErrorLog /usr/local/apache/logs/error_log
    Alias /net_path local_fs_path            = Alias /net_path /usr/local/apache/local_fs_path
    Include conf.d/vhost.conf                = Include /usr/local/apache/conf.d/vhost.conf
```
但注意，容器< Directory PATH >的PATH一般设置为文件系统的绝对路径，因为它是 **路径匹配** 性质的。但它仍可以使用相对路径时，此时它相对的是根文件系统的"/"，而非ServerRoot。 

所以，这个指令强烈不建议做任何修改，修改是很简单，但是牵一发而动全身。

### 1.2.3 DocumentRoot指令

如果说，ServerRoot是httpd中本地文件相对路径的根，那么DocumentRoot就是网络路径相对路径的根。顾名思义，DocumentRoot是文档的根目录，这个文档的意思是展现在网络上的文档。使用rpm包安装的httpd的DocumentRoot默认值为"/var/www"，编译安装的httpd，其DocumentRoot默认为"PREFIX/htdocs"，也就是"$ServerRoot/htdocs"。

设置DocumentRoot后，将需要在网络上访问的文件都放进此目录下即可。

例如，假设httpd所在主机IP为192.168.100.14，DocumentRoot使用默认的/usr/local/apache/htdocs，那么下面几个URL中，左边的是浏览器中输入的值，右边的是其访问的服务器上的资源路径。

    http://192.168.100.14/index.html         ==> /usr/local/apache/htdocs/index.html
    http://192.168.100.14/index.php          ==> /usr/local/apache/htdocs/index.php
    http://192.168.100.14/subdir/index.html  ==> /usr/local/apache/htdocs/subdir/index.html
    http://192.168.100.14/subdir/index.php   ==> /usr/local/apache/htdocs/subdir/php

也就是说，DocumentRoot的值对应的是 http://192.168.100.14/ 的"/"。 

### 1.2.4 DirectoryIndex指令

该指令设置的是"当搜索的URL中的路径使用了"/"结尾时，httpd将搜索该指令所指定的文件响应给客户端"。也就是说，当url表示搜索的是目录时，将查找该目录下的DirectoryIndex。注意，很多时候如果没有给定尾部的"/"，httpd的dir_module模块会自行加上"/"，当然，是否补齐尾随的"/"，也是可以控制的，见 [DirectorySlash][5] 指令。 

DirectoryIndex的设置格式为：

    DirectoryIndex disabled | local-url [local-url]

例如，当设置"DirectoryIndex index.html"时，如果在浏览器中输入下面左边的几个URL，httpd将响应右边对应的文件。

    http://192.168.100.14           ==> $DocumentRoot/index.html
    http://192.168.100.14/newdir/   ==> $DocumentRoot/newdir/index.html

可以指定多个index文件，它们将按顺序从左向右依次查找，并返回第一个找到的index文件。例如：

    <IfModule dir_module>
        DirectoryIndex index.php index.html /mydir/index.html
    </IfModule>

当浏览器中输入 `http://192.168.100.14/` 时，将首先搜索index.php，如果该文件不存在，则再搜索index.html，如果还找不到，则再找该目录的子目录下的文件`/mydir/index.html`。但这不表示 `http://192.168.100.14/mydir/` 会搜索`/mydir/index.html`。 

可以使用多个DirectoryIndex指令进行追加设置，它等价于单行设置多个值，例如下面的设置等价于 `DirectoryIndex index.php index.html` ： 

    DirecotryIndex index.php
    DirectoryIndex index.html

如果要替换某个值，则直接修改或使用disabled关键字禁用其前面的Directoryindex。例如禁用index.php，只提供index.html的索引。

    DirectoryIndex index.php
    DirectoryIndex disabled
    DirectoryIndex index.html

但注意，"disabled"关键字必须独自占用一个DirectoryIndex指令，否则它将被解析成字面意思，也就是说将其当作一个index文件响应给客户端。

DirectoryIndex指令可以设置在Server、Virtual host、Location和Directory上下文。所以，当设置在location或Directory容器中时，它将覆盖全局设置。例如，当DocumentRoot为/usr/local/apache/htdocs时：

    DirectoryIndex index.php
    <directory /usr/local/apache/htdocs/newdir>
        DirectoryIndex index.html
    </directory>
    # 或者
    <location /newdir>
        DirectoryIndex index.html
    </location>

在输入 `http://IP/newdir/` 时，将提供index.html而非index.php。 

当DirectoryIndex提供的索引文件都不存在时，将根据Options中的Indexes选项设置决定是否列出文件列表，除非是提供文件下载，否则出于安全考虑，这个选项是强烈建议关闭的。例如以下设置为打开，当

    <directory /usr/local/apache/htdocs/newdir>
        Options Indexes
        DirectoryIndex index.html
    </directory>

### 1.2.5 ServerName和ServerAlias

ServerName用于唯一标识提供web服务的主机名，只有在基于名称的虚拟主机中该指令才是必须提供的。也就是说，如果不是在基于名称的虚拟主机中，可以任意指定该指令的值，只要你认为它能唯一标识你的主机。但如果不设置该指令，那么httpd在启动时，将会反解操作系统的IP地址。

唯一标识主机的方式，也即ServerName的语法为：

    ServerName {domain-name|ip-address}[:port]

例如，在主机web.longshuai.com上提供了一个httpd web服务，如果还想使用www.longshuai.com提供同样的服务，还想效率更高点，则在设置DNS别名后再配置：

    ServerName www.longshuai.com

ServerAlias用于定义ServerName的别名。如果在定义ServerName之后再定义ServerAlias，那么ServerName和ServerName没有任何区别。当然，为了区分基于名称的虚拟主机，还是必须要定义ServerName。

例如，下面几个ServerName和ServerAlias是完全等价的。

    <VirtualHost *:80>
      ServerName  server.example.com
      ServerAlias server server2.example.com server2
      ServerAlias *.example.com
      # ...
    </VirtualHost>

### 1.2.6 Include指令

在httpd启动时，首先会解析配置文件。httpd支持include指令来包含其他文件，在解析配置文件时会进行配置合并。

支持通配符"*"、"?"和"[]"，但它们不能匹配斜线"/"，如有必要，它们会按照 **文件名的字母顺序依次** 进行加载。如果include指令中指定包含一个目录，则会按照字母顺序加载该目录内的所有文件，这比较容易出错，因为有些时候会产生一些临时文件或非配置类的文件。 

例如：

    Include /usr/local/apache/conf/ssl.conf
    Include /usr/local/apache/conf/vhosts/*.conf

可以使用绝对路径，也可以使用相对路径，如果使用相对路径，则它相对于ServerRoot。

    Include conf/ssl.conf
    Include conf/vhosts/*.conf

如果include包含的文件不存在时，将报错。这时可以使用IncludeOptional指令进行加载，这表示存在则加载，不存在就算了。例如下面的第一条指令中，如果vhosts下没有子目录，或者子目录中没有".conf"文件都将失败，而第二条指令则不会。

    Include conf/vhosts/*/*.conf
    IncludeOptional conf/vhosts/*/*.conf

### 1.2.7 Define和UnDefine指令

该指令用于定义参数或定义向后全局生效的变量。语法格式为：

    Define param [value]

当只给定一个param时，表示定义一个参数，这个参数用于< IfDefine param >容器进行判断，只有定义了的参数param，该容器才返回真，其内封装的指令才生效。它的等价行为是在httpd启动时(必须是启动时)，使用"-D"选项定义参数。例如下面两个方法是等价的：

    # startup command
    shell> httpd -DMyName ......
    # in config 
    Define MyName

当给定了两个参数，即还指定了value时，将表示定义一个变量，该变量具有 **向后全局性** 。也就是说，定义在某个虚拟主机中的变量在后面的另一个虚拟主机中也有效。引用变量时，使用 `${var}` 的方式。注意，变量名中不能包含冒号":"。 

例如：

    <IfDefine !TEST>
      Define servername www.example.com
    </IfDefine>
    
    DocumentRoot "/var/www/${servername}/htdocs"

使用UnDefine指令则是取消Define定义的参数或变量。语法为 `UnDefine param` 。 

### 1.2.8 VirtualHost指令

无疑，这是最重要的指令之一。用于封装一组指令只作用于指定主机名或IP地址的虚拟主机上。

语法格式为：

    <VirtualHost addr[:port] [addr[:port]] ...> ... </VirtualHost>

其中addr部分可以是以下几种情况：

* 虚拟主机的IP地址
* 虚拟主机IP地址对应的FQDN(不推荐)
* 字符"*"，匹配任意IP地址
* 字符串"`_default_`"，是"*"的别名

例如：

```apache
    <VirtualHost 10.1.2.3:80>
      ServerAdmin webmaster@host.example.com
      DocumentRoot "/www/docs/host.example.com"
      ServerName host.example.com
      ErrorLog "logs/host.example.com-error_log"
      TransferLog "logs/host.example.com-access_log"
    </VirtualHost>
```
需要为虚拟主机指定ServerName，否则它将会从主配置继承。对于基于名称的虚拟主机，ServerName更是不可缺少，否则将继承操作系统的FQDN。

当一个请求到达时，将按照最佳匹配进行主机匹配：通配的内容越少，优先级越高，也就越佳。例如"192.168.100.14:80"的优先级高于"*:80"。如果基于名称的虚拟主机无法匹配上，则采用虚拟主机列表中的第一个虚拟主机作为响应主机。如果所有虚拟主机都无法匹配上，则采用从主配置段落中的主机，如果主配置段落中注释了DocumentRoot，则返回对应的错误。

具体配置方法，见 [配置httpd虚拟主机][6] 。 

### 1.2.9 Options和AllowOverride指令

Options启用或禁用指定目录下的某些特性。有效值包括：All、None、ExecCGI、FollowSymLinks、Includes、IncludesNOEXEC、Indexes、MultiViews、SymLinksIfOwnerMatch。

不指定options时，默认为all。一般除了提供下载服务会开启一个Indexes选项，其他选项都会关掉，即使用：

    Options None

AllowOverride指令用于控制是否读取".htaccess"配置文件。

如何设置这个指令要看具体情况，有以下几种值，此外还可以设置为all和none，表示启用、禁用所有特性。

* AuthConfig：基于用户认证时设置该值，此时将可以使用AuthGroupFile, AuthName, AuthType, AuthUserFile, equire等认证相关指令。
* FileInfo： 控制文档类型时使用该值，此时将可以使用ErrorDocument, SetHandler,以及一些URL重写的指令。
* Indexes：控制目录索引时使用该值，此时可以使用AddIcon, DirectoryIndex。
* Limit：是否允许使用order、allow、deny指令，这三个指令已经废弃，目前还存在是为了兼容老版本。

例如下面的指令使得在使用非认证类和索引控制类指令时，将产生服务器类的错误。

    AllowOverride AuthConfig Indexes

### 1.2.10 Require指令

见 [Require指令][7] 。 

### 1.2.11 长连接相关指令

KeepAlive指令用于开启和关闭长连接功能。

    KeepAlive on/off

在没有开启长连接时，客户端每请求一个资源都需重新建立一次TCP连接，而使用了长连接后，客户端只需在最初请求一次TCP连接，之后就可以使用同一个TCP连接发送其他的http请求。长连接的状态是指在服务端处理完某一个请求后，它立即进入长连接状态以保持TCP连接不断开，等待客户端再次发送请求。

但长连接自身的缺陷是会一直占用着连接不释放，所以必须得给出一个长连接的超时时间。这个超时时间由KeepAliveTimeout指令控制，进入长连接后如果在此时间间隔内客户端还没有发送新请求，则TCP连接自动断开。如果在长连接状态下，客户端再次发送了请求，则服务端处理请求，并在处理完请求后又再次进入长连接状态并计算KeepAliveTimeout。

此外，还可以通过指令MaxKeepAliveRequests控制每个长连接下的TCP连接的能接受的最大请求数。无疑，这个值应该设置的大一些，设置为0表示无限制。这个指令是从数量的角度控制长连接的TCP应该何时断开。例如，在长连接超时时间内接受同一个客户端的500个请求才断开，然后该客户端再有新的请求只能重新建立TCP连接。

    MaxKeepAliveRequests 500

## 1.3 容器类指令

路径和条件判断容器包括：

* `< Directory >`、`< DirectoryMatch >`
* `< Files >`、`< FilesMatch >`
* `< Location >`、`< LocationMatch >`
* `< IfModule >`
* `< IfDefine >`
* `< IfVersion >`
* `< if >`
* `< elseif >`
* `< else >`

### 1.3.1 容器< Directory >和< Files >

还包括它们的正则匹配容器`< DirectoryMatch >`、`< FilesMatch >`。

`< Directory >`容器的作用是"对于匹配到的目录，封装一组指令，这些指令只作用于该目录以及它的子目录中的文件"。注意，  `< Directory >`容器通常都是用绝对路径，即`< Directory /PATH/to/DIR >`，如果使用相对路径，则它相对于根文件系统的"/"  。例如`< directory newdir >`等价于`< directory /newdir >`。 

例如：

    <Directory "/">
        AllowOverride none
        require all denied
    </Directory>
    
    <Directory "/usr/local/apache/htdocs">
        require all granted
    </Directory>

第一个容器表示拒绝所有对"/"下内容的访问，包括子目录中的文件，这个根是根文件系统的根，而不是ServerRoot。而第二个容器则表示允许/usr/local/apache/htdocs目录下文件的访问。

由此可以想象得出，出于安全考虑，应该总是先将父目录进行限制，再在需要放宽权限的子目录中指定特定的权限。正如上面的设置，将最顶级目录"/"完全限制，然后在小范围的htdocs目录中放行。

再看< Files >容器，它针对的是某个或某些特定的能被匹配上的文件。它匹配的范围是它所在的上下文。

例如，下面的指令如果写在server上下文，那么将对任意private.html文件拒绝。

    <Files private.html>
        require all denied
    </Files>

而如果将其写在`< directory >`容器中，则只对该目录容器中的所有private.html生效。由于`< directory >`会递归到子目录中，所以子目录中的private.html也会拒绝，但非private.html将被允许。

    <Directory "/usr/local/apache/htdocs">
        require all granted
        <Files private.html>
            require all denied
        </Files>
    </Directory>

`< directory >`和`< files >`容器可以使用通配符，"*"表示任意字符，"?"表示任意单个字符，"[]"表示范围，如`[a-z]`、`[0-9]`，但是这些通配符都不能匹配"/"。所以要跨目录匹配时，必须显式指定各个目录的"/"符号。

例如， `<directory /*/public.html>` 无法匹配`/home/user/public.html`，但 `directory /home/*/public.html` 可以匹配。 

它还可以使用正则表达式匹配，只需使用一个"~"符号即可。这时和使用`< DirectoryMatch >`、`< FilesMatch >`是一样的，只不过Match类指令不需要使用"~"符号。

例如，下面的设置。其中后两个Directory容器是等价的。

    # 匹配不区分大小写的gif/jpg/jpeg/png
    <FilesMatch "\.(?i:gif|jpe?g|png)$">
        Require all denied
    </FilesMatch>
    
    <Directory ~ "^/usr/local/apache/htdocs/[0-9]{3}">
        DirectoryIndex digest.html
    </Directory>
    
    <DirectoryMatch "^/usr/local/apache/htdocs/[0-9]{3}">
        DirectoryIndex digest.html
    </DirectoryMatch>

需要注意的是，httpd采用的pcre库提供的perl兼容正则。以下是官方手册提供的一个示例，使用的命名捕获语法，它将匹配/var/www/combined/目录下的一级子目录，但不进行递归。将每个匹配到的结果保存到命名的分组sitename中，并通过环境变量"MATCH_capturename"进行引用，其中capturename必须转为大写字母，因为它就是这样赋值的。

    <DirectoryMatch "^/var/www/combined/(?<sitename>[^/]+)">
        Require ldap-group cn=%{env:MATCH_SITENAME},ou=combined,o=Example
    </DirectoryMatch>

目前已经不能使用未命名的后向引用，例如$0,$1...。在URL重写时，正则语法至关重要，像grep/sed/awk中天然支持的基础正则和扩展正则语法虽然能解决大部分问题，但想要实现复杂的需求，只能使用语义丰富、完整的正则，如pcre提供的正则。

### 1.3.2 容器< Location >

该容器和`< Directory >`、`< Files >`容器差不多，都是对满足匹配条件的路径封装一组指令，这些指令只生效于这些能匹配的路径。但是`< Location >`和`< Directory >`、`< Files >`最大的区别是：前者匹配的目标是WebSpace，即匹配URL中的路径，而后两者匹配的是本地文件系统的路径。

例如，当设置下面的location容器时，将匹配 http://192.168.100.14/newdir/index.html 。 

    <Location "/newdir">
        ......
    </Location>

location支持三种匹配模式：

* 精确匹配：location的模式和URL中的路径部分精确对应。
* 加尾随斜线：location的模式中加了尾随斜线时，将匹配该目录里面的内容。
* 无尾随斜线：location的模式中没有尾随斜线时，将匹配该目录和目录里面的内容。

例如，下面两个容器，第一个将匹配/private1、/private1/和/private1/file.txt，但不能匹配/private1other，而第二个将匹配/private2/和/private2/file.txt，但不能匹配/private2和/private2other。

    <Location "/private1">
        ......
    </Location>
    
    <Location "/private2/">
        ......
    </Location>

location和sethandler指令一起使用时很方便。例如，开启状态信息页面：

    <Location "/server-status">
        SetHandler server-status
        Require all granted
    </Location>

同样，除了支持"*"、"?"、"[]"的通配符匹配，还支持"~"和LocationMatch指令的正则匹配。方法见上面的`< Directory >`容器。

### 1.3.3 < IfDefine >、< IfModule >和< IfVersion >条件判断

这三个容器都是条件判断容器， **且都只在httpd启动时进行判断** ，判断为真，则封装在其内的指令生效，否则忽略。且都可以在条件前加一个"!"以实现条件的否定，而且都可以嵌套以实现更复杂的配置。 

`< IfModule >`容器是指当启动时加载了某模块时，该容器内的指令生效。可以是静态加载的模块，或者使用LoadModule指令加载的，但如果这样的话，加载对应模块的LoadModule指令必须在`< IfModule >`指令之前。例如：

    LoadModule status_module modules/mod_status.so
    
    <IfModule "status_module">
        <Location "/server-status">
            SetHandler server-status
            Require all granted
        </Location>
    </IfModule>

`< IfDefine param >`容器用于判断参数param是否已经定义，如果定义了，则条件为真，封装在其内的指令生效，否则忽略。加上感叹号则表示取反，例如`< IfDefine !param >`。

那么如何定义参数呢？有两种方法：使用httpd命令的"-D"选项；使用Define指令。

例如，在使用httpd启动时，加上一个"-D"选项定义MyName参数。

    httpd -DMyName ......

或者在配置文件中使用Define指令进行定义，但必须在`< IfDefine >`容器之前定义。例如：

    Define MyName

`< IfDefine >`可以进行嵌套。例如下面是官方的一个示例：

    httpd -DReverseProxy -DUseCache -DMemCache ...
    
    <IfDefine ReverseProxy>
      LoadModule proxy_module   modules/mod_proxy.so
      LoadModule proxy_http_module   modules/mod_proxy_http.so
      <IfDefine UseCache>
        LoadModule cache_module   modules/mod_cache.so
        <IfDefine MemCache>
          LoadModule mem_cache_module   modules/mod_mem_cache.so
        </IfDefine>
        <IfDefine !MemCache>
          LoadModule cache_disk_module   modules/mod_cache_disk.so
        </IfDefine>
      </IfDefine>
    </IfDefine>

`< IfVersion >`容器用于判断httpd的版本。例如：

    <IfVersion >= 2.4>
        # this happens only in versions greater or equal 2.4.0.
    </IfVersion>

### 1.3.4 < If >、< ElseIf >和< Else >容器

意义不言自明。`< If >...< /If >`判断表达式是否为真，如果为真，则封装在其内的指令生效；`< ElseIf >...< /ElseIf >`作用于`< If >...< /If >`之后，而`< Else >...< /Else >`则作用于最后。

表达式的写法和shell脚本的表达式差不多，例如数值比较"-eq"、"-gt"，字符串比较"=="、">="，以及其他一些表达式"-z"、"-n"、"-f"等，此外，它还支持正则匹配表达式"~="、"!~"。具体相关函数、变量、表达式、语法等见 [http://httpd.apache.org/docs/2.4/expr.html][8] 。 

例如：

    # 请求首部没有Host字段时，该段指令将生效。
    <If "-z req('Host')">
    ...
    </If>
    
    # 如果请求主机地址属于0/16，则if段生效，否则如果属于0/8，则elseif段生效，否则else生效
    <If "-R '10.1.0.0/16'">
      #...
    </If>
    <ElseIf "-R '10.0.0.0/8'">
      #...
    </ElseIf>
    <Else>
      #...
    </Else>

## 1.4 配置文件的合并规则

配置文件的段落以一种非常特殊的顺序生效。理解配置文件的合并规则非常重要，否则配置了半天可能发现根本不会生效。

以下是5个组类合并的顺序：

* 1.<` Directory >` (正则匹配的容器除外)
* 2.`< DirectoryMatch >` (以及`< Directory "~" >`)
* 3.`< Files >`和`< FilesMatch >`同时处理
* 4.`< Location >`和`< LocationMatch >`同时处理
* 5.`< If >`

此外，还需要注意的一些规则是：

* 除了`< Directory >`容器，每个组以它们出现的顺序进行合并。例如一个/foo请求可以匹配`< Location "/foo/bar" >`和`< Location "/foo" >`，它们都属于上面列出的第4组，所以对于这两个Location容器，谁配置在前面就匹配谁。
* `< Directory >`容器即上面的第一组处理的顺序是先处理路径"短"的，再处理路径长的。这里的短指的是离根文件系统的"/"越近就越短。由于这个组不包含正则匹配的表达式(即`< Directory ~ >`)，所以这里的"短"就代表它的路径表达式短。例如`< Directory "/var/web/dir" >`将优先于`< Directory "/var/web/dir/subdir" >`被处理。
* 如果出现多个`< Directory >`的路径完全一样的极端情况，那么将按照出现顺序处理。
* 使用Include指令包含的文件将被插入到该指令的位置，然后按规则进行处理。
* `< VirtualHost >`段落的配置将在外部对应的段处理完毕以后再处理，这样就允许虚拟主机覆盖主服务器的设置。
* 当请求是由mod_proxy处理的时候，`< Proxy >`容器将会在处理顺序中取代`< Directory >`容器的位置。

需要注意的是，配置文件中的指令都是由各个模块提供的，所以各指令是由各对于模块来解析、处理、合并的，配置文件的作用只不过是将各个模块的指令整合在一起方便定义。另外，上面定义的5个组别都是由httpd的核心模块提供的，因此它们才有处理顺序的要求。

当在运行时进行请求匹配，将先按照上面合并规则提供的顺序进行匹配，如果某个组中出现了能成功匹配请求的模块，将提升一次合并的层次，使得这次模块的匹配变为第三次匹配。例如下面的配置使用了由mod_headers提供的Header指令用于设置HTTP的首部，如果请求/example/index.html，那么最终设置的CustomHeaderName首部的值是什么呢？

```apache
    <Directory "/">
        Header set CustomHeaderName one
        <FilesMatch ".*">
            Header set CustomHeaderName three
        </FilesMatch>
    </Directory>
    
    <Directory "/example">
        Header set CustomHeaderName two
    </Directory>
```
首先按照前面提供的合并顺序匹配到"/"，这会初始化设置CustomHeaderName的值为one，再匹配到/example，CustomHeaderName被设置为two。最后分组中提供的指令FilesMatch匹配成功，提升一次合并的层次，这是第三次匹配，导致CustomHeaderName最终设置为three。

下面的例子中，如果这些指令都对请求生效，它们将按照"A > B > C > D > E"的顺序生效。

```apache
    <Location "/">
        E
    </Location>
    
    <Files "f.html">
        D
    </Files>
    
    <VirtualHost *>
    <Directory "/a/b">
        B
    </Directory>
    </VirtualHost>
    
    <DirectoryMatch "^.*b$">
        C
    </DirectoryMatch>
    
    <Directory "/a/b">
        A
    </Directory>
```
D和E无疑是最后生效的。再看三个Directory类的容器，对于Directory和DirectoryMatch，前者先生效，所以C排在A和B后，对于A和B，虚拟主机会在外部段落处理完后再处理，所以在A和B进行合并时，B将覆盖A，也即A先生效。所以顺序为"A>B>C>D>E"。但如果将上面的A段落改为：

```apache
    <Directory "/a/b">
        A
        <FilesMatch f.html>
            D1
        </Files>
    </Directory>
```
那么最终的顺序为"A>B>C>D>D1>E"。

以下示例则更有教育意义。尽管Directory设置了更严格的权限，但因为Location比Directory更后生效，它对所有访问都不做任何限制。也就是说，Directory在这里的权限设置是完全多余的。所以说，理解配置文件的合并规则对写配置文件至关重要。

```apache
    <Location "/">
        Require all granted
    </Location>
    
    # Whoops!  This <Directory> section will have no effect
    <Directory "/">
        <RequireAll>
            Require all granted
            Require not host badguy.example.com
        </RequireAll>
    </Directory>
```

[2]: http://www.cnblogs.com/f-ck-need-u/p/7636836.html

[5]: http://httpd.apache.org/docs/2.4/mod/mod_dir.html#directoryslash
[6]: http://www.cnblogs.com/f-ck-need-u/p/7632878.html
[7]: http://www.cnblogs.com/f-ck-need-u/p/7634205.html#blog1.3
[8]: http://httpd.apache.org/docs/2.4/expr.html