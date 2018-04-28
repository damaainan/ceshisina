## Apache配置文件解读学习笔记

来源：[https://segmentfault.com/a/1190000014483249](https://segmentfault.com/a/1190000014483249)

Apache配置解释

* ServerRoot


```
 # ServerRoot: The top of the directory tree under which the server's
 # configuration, error, and log files are kept.
```

主要用于指定Apache的安装，此选项参数值在安装Apache的时候系统会自动把Apache路径写入。


-----

* Mutex default:logs


```
  #Mutex: Allows you to set the mutex mechanism and mutex file
  #directory
  #for individual mutexes, or change the global defaults
```

互斥：允许为多个不同的互斥对象设置互斥机制（nutex mechanism）和互斥文件目录，或者修改全局默认值。如果互斥的对象是基于文件的以及默认的互斥目录不在本地磁盘或因为其他原因而不适用，那么取消注释并改变目录。


-----

* Listen


```
# Listen: Allows you to bind Apache to specific IP addresses and/or
# ports, instead of the default. See also the <VirtualHost>
# directive.
```

listen主要侦听web服务器端口状态，默认为80。也可以写成IP地址，不写地址的默认为0.0.0.0


-----

* Dynamic Shared Object (DSO) Support


```
# To be able to use the functionality of a module which was built as
# a DSO you
# have to place corresponding `LoadModule' lines at this location so >     #the
# directives contained in it are actually available _before_ they are >     used.
# Statically compiled modules (those listed by `httpd -l') do not >    >     # need
# to be loaded here.


# Example:
# LoadModule foo_module modules/mod_foo.so
#
```

Apache是一个模块化设计的服务，核心只包含主要功能，扩展功能通过模块实现，不同的模块可以被静态的编译进程序，也可以动态加载。

主要用于添加Apache一些动态模块，比如php支持模块。重定向模块，认证模块支持，注意如果需要添加某些模块支持，只需把相关模块前面注释符号取消掉


-----

* Apache 运行用户配置


```apache
<IfModule unixd_module>
#
# If you wish httpd to run as a different user or group, you must run
# httpd as root initially and it will switch.
#
# User/Group: The name (or #number) of the user/group to run httpd > >     # as.
# It is usually good practice to create a dedicated user and group >   >     # for
# running httpd, as with most system services.
#
User daemon
Group daemon

</IfModule>
```

指定Apache服务的运行用户和用户组，默认为：daemon


-----

* Apache的默认服务名及端口设置


```php
# ServerName gives the name and port that the server uses to identify
#itself.

ServerName localhost:80
```

用以指定Apache默认的服务器名以及端口，默认的参数配置为ServerName localhost:80


-----

* Apache服务默认管理员地址设置


```php
# ServerAdmin: Your address, where problems with the server should be
# e-mailed.  This address appears on some server-generated pages,
# such
# as error documents.  e.g. admin@your-domain.com
#
ServerAdmin admin@example.com
```

指定Apache服务管理员通知邮箱地址，选择默认值即可，如果有真实的邮箱地址也可以设置此值


-----

* HostnameLookups

HostnameLookups off
是否进行域名的解析，一般关掉,会占用资源，而且一般的ip地址没有反向解析，或者不允许.


-----

* DocumentRoot


```php
# DocumentRoot: The directory out of which you will serve your
# documents. By default, all requests are taken from this directory, but
# symbolic links and aliases may be used to point to other locations.
```

默认的网站根目录

主站点的网页存储位置


-----

* Apache根目录访问控制设置，以及默认的网站根目录访问控制


```php
# Each directory to which Apache has access can be configured with
# respect
# to which services and features are allowed and/or disabled in that
# directory (and its subdirectories).
#
# First, we configure the "default" to be a very restrictive set of
# features.
#
<Directory />
    AllowOverride none
    Require all granted
</Directory>
```

Apache的根目录访问控制设置,针对用户对根目录下所有的访问权限控制，默认Apache对根目录访问都是拒绝访问。


```php
    <Directory "/####/####/">
    #
    # Possible values for the Options directive are "None", "All",
    # or any combination of:
    #   Indexes Includes FollowSymLinks SymLinksifOwnerMatch ExecCGI MultiViews
    #
    # Note that "MultiViews" must be named *explicitly* --- "Options All"
    # doesn't give it to you.
    #
    # The Options directive is both complicated and important.  Please see
    # http://httpd.apache.org/docs/2.4/mod/core.html#options
    # for more information.
    #
    Options Indexes FollowSymLinks

    #
    # AllowOverride controls what directives may be placed in .htaccess files.
    # It can be "All", "None", or any combination of the keywords:
    #   AllowOverride FileInfo AuthConfig Limit
    #
    AllowOverride all

    #
    # Controls who can get stuff from this server.
    #

#   onlineoffline tag - don't remove
#    Require local
Allow from all
</Directory>
```

Apache的默认网站根目录设置及访问控制

<Directory "/mnt/web/clusting">

Options Indexex FollowSymLinks

AllowOverride None

Order allow,deny

Allow from all

</Directory>


* options:配置在特定目录中适用哪些特性，常用参数

参数                      含义                                      
ExecCGI                 在该目录下允许执行CGI脚本                          
FollowSymLinks          在该目录下允许文件系统使用符号链接                       
Indexes                 当用户访问该目录时，如果用户找不到DirectoryIndex指定的主页文件(例如index.html),则返回该目录下的文件列表给用户。
SymLinksIfOwnerMatch    当使用符号连接时，只有当符号连接的文件拥有者与实际文件的拥有者相同时才可以访问。


* AllowOverride:允许存在于.htaccess文件中的指令类型（.htaccess文件名是可以改变的，其文件名由AccessFileName指令决定）。
 当AllowOverride设置为none时，不搜索该目录下的htaccess文件（可以减小服务器的开销）
* Order:控制在访问Allow和deny两个访问规则那个优先
* Allow:允许访问的主机列表（可用域名或者子网）
* Deny: 拒绝访问的主机列表



-----

* Apache 默认首页设置


```php
# DirectoryIndex: sets the file that Apache will serve if a directory
# is requested.
#
<IfModule dir_module>
    DirectoryIndex index.php index.php3 index.html index.htm
</IfModule>
```

设置Apache默认支持的首页，默认只支持:index.html首页，如要支持其他类型的首页，需要在此区域添加


-----

* Apache关于.ht文件访问配置


```php
#
# The following lines prevent .htaccess and .htpasswd files from being
# viewed by Web clients.
#
<Files ".ht*">
    Require all denied
</Files>
```

此选项是针对 .ht 文件访问控制，默认具有访问权限，此区域文件默认即可


-----

* Apache关于日志文件配置


```php
# LogLevel: Control the number of messages logged to the error_log.
# Possible values include: debug, info, notice, warn, error, crit,
# alert, emerg.
#
LogLevel warn

<IfModule log_config_module>
    #
    # The following directives define some format nicknames for use with
    # a CustomLog directive (see below).
    #
    LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" combined
    LogFormat "%h %l %u %t \"%r\" %>s %b" common

    <IfModule logio_module>
      # You need to enable mod_logio.c to use %I and %O
      LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %I %O" combinedio
    </IfModule>

    #
    # The location and format of the access logfile (Common Logfile Format).
    # If you do not define any access logfiles within a <VirtualHost>
    # container, they will be logged here.  Contrariwise, if you *do*
    # define per-<VirtualHost> access logfiles, transactions will be
    # logged therein and *not* in this file.
    #
    CustomLog "/####/logs/access.log" common

    #
    # If you prefer a logfile with access, agent, and referer information
    # (Combined Logfile Format) you can use the following directive.
    #
    #CustomLog "logs/access.log" combined
</IfModule>
```

针对Apache默认的日志级别，默认的访问日志路径，默认的错误日志路径等相关设置，此选项内容默认即可


-----

* URL重定向，cgi模块配置说明


```php
<IfModule alias_module>
    #
    # Redirect: Allows you to tell clients about documents that used to
    # exist in your server's namespace, but do not anymore. The client
    # will make a new request for the document at its new location.
    # Example:
    # Redirect permanent /foo http://www.example.com/bar

    #
    # Alias: Maps web paths into filesystem paths and is used to
    # access content that does not live under the DocumentRoot.
    # Example:
    # Alias /webpath /full/filesystem/path
    #
    # If you include a trailing / on /webpath then the server will
    # require it to be present in the URL.  You will also likely
    # need to provide a <Directory> section to allow access to
    # the filesystem path.

    #
    # ScriptAlias: This controls which directories contain server scripts.
    # ScriptAliases are essentially the same as Aliases, except that
    # documents in the target directory are treated as applications and
    # run by the server when requested rather than as documents sent to the
    # client.  The same rules about trailing "/" apply to ScriptAlias
    # directives as to Alias.
    #
    ScriptAlias /cgi-bin/ "/####/cgi-bin/"

</IfModule>

<IfModule cgid_module>
    #
    # ScriptSock: On threaded servers, designate the path to the UNIX
    # socket used to communicate with the CGI daemon of mod_cgid.
    #
    #Scriptsock cgisock
</IfModule>

# "/####/cgi-bin" should be changed to whatever your ScriptAliased
# CGI directory exists, if you have that configured.
#
<Directory "/####/cgi-bin">
    AllowOverride None
    Options None
    Require all granted
</Directory>
```

包含一些url重定向，别名，脚本别名等相关设置，以及一些特定的处理程序，CGI设置说明。


-----

* MIME媒体文件，以及相关http文件解析配置说明


```php
<IfModule mime_module>
    #
    # TypesConfig points to the file containing the list of mappings from
    # filename extension to MIME-type.
    #
    TypesConfig conf/mime.types

    #
    # AddType allows you to add to or override the MIME configuration
    # file specified in TypesConfig for specific file types.
    #
    #AddType application/x-gzip .tgz
    #
    # AddEncoding allows you to have certain browsers uncompress
    # information on the fly. Note: Not all browsers support this.
    #
    AddEncoding x-compress .Z
    AddEncoding x-gzip .gz .tgz
    #
    # If the AddEncoding directives above are commented-out, then you
    # probably should define those extensions to indicate media types:
    #
    AddType application/x-compress .Z
    AddType application/x-gzip .gz .tgz
    AddType application/x-httpd-php .php
    AddType application/x-httpd-php .php3

    #
    # AddHandler allows you to map certain file extensions to "handlers":
    # actions unrelated to filetype. These can be either built into the server
    # or added with the Action directive (see below)
    #
    # To use CGI scripts outside of ScriptAliased directories:
    # (You will also need to add "ExecCGI" to the "Options" directive.)
    #
    #AddHandler cgi-script .cgi

    # For type maps (negotiated resources):
    #AddHandler type-map var

    #
    # Filters allow you to process content before it is sent to the client.
    #
    # To parse .shtml files for server-side includes (SSI):
    # (You will also need to add "Includes" to the "Options" directive.)
    #
    #AddType text/html .shtml
    #AddOutputFilter INCLUDES .shtml
</IfModule>
```

此区域文件主要包含一些mime文件支持，以及添加一些指令在给定的文件扩展名与特定的内容类型之间建立映射关系。比如添加对PHP文件扩展名映射关系


-----

* 服务器页面提示设置


```php
#
# The mod_mime_magic module allows the server to use various hints from the
# contents of the file itself to determine its type.  The MIMEMagicFile
# directive tells the module where the hint definitions are located.
#
#MIMEMagicFile conf/magic

#
# Customizable error responses come in three flavors:
# 1) plain text 2) local redirects 3) external redirects
#
# Some examples:
#ErrorDocument 500 "The server made a boo boo."
#ErrorDocument 404 /missing.html
#ErrorDocument 404 "/cgi-bin/missing_handler.pl"
#ErrorDocument 402 http://www.example.com/subscription_info.html
#

#
# MaxRanges: Maximum number of Ranges in a request before
# returning the entire resource, or one of the special
# values 'default', 'none' or 'unlimited'.
# Default setting is to accept 200 Ranges.
#MaxRanges unlimited

#
# EnableMMAP and EnableSendfile: On systems that support it,
# memory-mapping or the sendfile syscall may be used to deliver
# files.  This usually improves server performance, but must
# be turned off when serving from networked-mounted
# filesystems or if support for these functions is otherwise
# broken on your system.
# Defaults: EnableMMAP On, EnableSendfile Off
#
EnableMMAP off
EnableSendfile off
```

此区域可以定制访问错误相应提示，支持三种方式：1明文，2本地重定向，3外部重定向，另外还包括内存映射或者“发送文件系统调用”可被用于分发文件等配置


-----

* 

```php
# AcceptFilter: On Windows, none uses accept() rather than AcceptEx() and
# will not recycle sockets between connections. This is useful for network
# adapters with broken driver support, as well as some virtual network
# providers such as vpn drivers, or spam, virus or spyware filters.
AcceptFilter http none
AcceptFilter https none
```


-----

* Apache服务器补充设置


```php
# Supplemental configuration
#
# The configuration files in the conf/extra/ directory can be
# included to add extra features or to modify the default configuration of
# the server, or you may simply copy their contents here and change as
# necessary.

# Server-pool management (MPM specific)
#Include conf/extra/httpd-mpm.conf

# Multi-phpuage error messages
#Include conf/extra/httpd-multiphp-errordoc.conf

# Fancy directory listings
Include conf/extra/httpd-autoindex.conf

# phpuage settings
#Include conf/extra/httpd-phpuages.conf

# User home directories
#Include conf/extra/httpd-userdir.conf

# Real-time info on requests and configuration
#Include conf/extra/httpd-info.conf

# Virtual hosts
Include conf/extra/httpd-vhosts.conf

# Local access to the Apache HTTP Server Manual
#Include conf/extra/httpd-manual.conf

# Distributed authoring and versioning (WebDAV)
#Include conf/extra/httpd-dav.conf

# Various default settings
#Include conf/extra/httpd-default.conf

# Configure mod_proxy_html to understand HTML4/XHTML1
<IfModule proxy_html_module>
Include conf/extra/proxy-html.conf
</IfModule>
```

此区域包括：服务器池管理，多语言错误信息，动态目录列表 形式配置，语言设置，用户家庭目录，请求和配置上的实时信息，虚拟主机


-----

* Apache服务器安全连接设置


```php
# Secure (SSL/TLS) connections
#Include conf/extra/httpd-ssl.conf
#
# Note: The following must must be present to support
#       starting without SSL on platforms with no /dev/random equivalent
#       but a statically compiled-in mod_ssl.
#
<IfModule ssl_module>
SSLRandomSeed startup builtin
SSLRandomSeed connect builtin
</IfModule>
#
# uncomment out the below to deal with user agents that deliberately
# violate open standards by misusing DNT (DNT *must* be a specific
# end-user choice)
#
#<IfModule setenvif_module>
#BrowserMatch "MSIE 10.0;" bad_DNT
#</IfModule>
#<IfModule headers_module>
#RequestHeader unset DNT env=bad_DNT
#</IfModule>

Include "C:/wamp64/alias/*"
```

此区域主要是关于服务器安全连接设置，用于使用https连接服务器等设置的地方

配置选项及参数含义


* ServerTokens : 该参数设置http头部返回的apache版本信息（仅软件名称，主版本，次版本，仅Apache的完整版本号，包括系统类型）
* ServerSignature Off :在页面产生错误时是否出现服务器版本信息。推荐设置为Off
* 持久性连接设置
KeepAlive On :开启持久性连接功能。即当客户端连接到服务器，下载完数据后仍然保持连接状态。 
MaxKeepAliveRequests 100: 一个连接服务的最多请求次数。 
KeepAliveTimeout 30 : 持续连接多长时间，该连接没有再请求数据，则断开该连接。缺省为15秒。
* Timeout 60 : 不论接收或发送，当持续连接等待超过60秒则该次连接就中断
* DefaultType text/plain DefaultType : 定义当不能确定MIME类型时服务器提供的默认MIME类型（默认不更改）
如果你的服务器主要包含text 或者HTML文档,“text/plain” 是一个好的选择
* 别名设置：对于不在DocumentRoot指定的目录内的页面，既可以使用符号连接，也可以使用别名。
* CGI设置 ：
* 日志的设置：


* ErrorLog logs/error_log ：日志的保存位置
* LogLevel warn ：日志的级别
* 访问日志的设置

%h                  客户端的ip地址或主机名                            
%l                  由客户端 identd 判断的RFC 1413身份，输出中的符号 "-" 表示此处信息无效
%u                  由HTTP认证系统得到的访问该网页的客户名。有认证时才有效，输出中的符号 "-" 表示此处信息无效。
%t                  服务器完成对请求的处理时的时间。                        
"%r"                引号中是客户发出的包含了许多有用信息的请求内容。                
%>s                 服务器返回给客户端的状态码                           
%b                  返回给客户端的不包括响应头的字节数                       
"%{Referer}i"       指明了该请求是从被哪个网页提交过来的                      
"%{User-Agent}i"    客户浏览器提供的浏览器识别信息








-----

prefork和worker模式的比较

prefork模式使用多个子进程，每个子进程只有一个线程。每个进程在某个确定的时间只能维持一个连接。在大多数平台上，Prefork MPM在效率上要比Worker MPM要高，但是内存使用大得多。prefork的无线程设计在某些情况下将比worker更有优势：它可以使用那些没有处理好线程安全的第三方模块，并且对于那些线程调试困难的平台而言，它也更容易调试一些。

worker模式使用多个子进程，每个子进程有多个线程。每个线程在某个确定的时间只能维持一个连接。通常来说，在一个高流量的HTTP服务器上，Worker MPM是个比较好的选择，因为Worker MPM的内存使用比Prefork MPM要低得多。但worker MPM也由不完善的地方，如果一个线程崩溃，整个进程就会连同其所有线程一起"死掉".由于线程共享内存空间，所以一个程序在运行时必须被系统识别为"每个线程都是安全的"。
