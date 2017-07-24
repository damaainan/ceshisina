#### apache主配置文件

apache的主配置文件httpd.conf中一些常见的配置项。主配置大约500多行，但其中只有一小部分的apache指令,大多数都是注释，去掉注释大约100多行，下面是配置文件的内容


```apache
    # 是否显示apache的版本信息
    ServerSignature On
    # 在出现错误页的时候不服务器操作系统的名称
    ServerTokens Full
    
    # 整个配置文件的根路径
    ServerRoot "G:/wamp/wamp/bin/apache/apache2.4.9"
    Define APACHE24 Apache2.4
    
    # 监听的服务器和端口号
    Listen 0.0.0.0:80
    Listen [::0]:80
    
    # apache是基于模块化设计的,在此设置加载一些动态模块
    LoadModule access_compat_module modules/mod_access_compat.so
    LoadModule asis_module modules/mod_asis.so
    LoadModule auth_basic_module modules/mod_auth_basic.so
    LoadModule php5_module "G:/wamp/wamp/bin/php/php5.5.12/php5apache2_4.dll"
    
    # 设置apache运行账户及账户组
    <IfModule unixd_module>
        User daemon
        Group daemon
    </IfModule>
    
    # 管理员的邮箱,apache运行出现严重错误可以向管理员发邮件
    ServerAdmin admin@example.com
    # 主机名
    ServerName localhost:80
    # 是否进行域名的解析
    HostnameLookups Off
    # 网站的根目录
    DocumentRoot "G:/wamp/wamp/www/"
    
    # apache下文件的访问权限,下面更具体的设置可以覆盖此处设置
    <Directory ></Directory>
        # .htaccess是否可用
        AllowOverride none
        Require all denied
    </Directory>
    
    # 网站的一些设置
    <Directory "G:/wamp/wamp/www/">
        # Indexes允许查看目录树,设置-Indexes可以关闭目录树
        Options Indexes FollowSymLinks
        # 是否支持.htaccess
        AllowOverride all
    Require all granted
    Order Deny,Allow
    # 允许谁访问 
    Allow from all 
    </Directory>
    
    # 定义一些首页文件
    <IfModule dir_module>
        DirectoryIndex index.php index.php3 index.html index.htm
    </IfModule>
    
    # 单个文件的权限
    <Files ".ht*">
        Require all denied
    </Files>
    
    # 错误日志
    ErrorLog "G:/wamp/wamp/logs/apache_error.log"
    # 定义记录错误的级别
    LogLevel warn
    
    # 定义写日志的一些格式
    <IfModule log_config_module>
        LogFormat "%h %l %u %t \\"%r\\" %>s %b" common
        <IfModule logio_module>
          LogFormat "%h %l %u %t \\"%r\\" %>s %b"
        </IfModule>
        # 日常日志
        CustomLog "G:/wamp/wamp/logs/access.log" common
    </IfModule>
    
    # 在不同目录下有不同网站，但在同一个域名下，这时可以配置alias
    <IfModule alias_module>
        ScriptAlias /cgi-bin/ "G:/wamp/wamp/bin/apache/apache2.4.9/cgi-bin/"
    </IfModule>
    
    # 文件夹权限的设置
    <Directory "G:/wamp/wamp/bin/apache/apache2.4.9/cgi-bin">
        AllowOverride None
        Options None
        Require all granted
    </Directory>
    
    # 设置一些文件类型对应的处理方式
    <IfModule mime_module>
        TypesConfig conf/mime.types
        AddEncoding x-compress .Z
        AddEncoding x-gzip .gz .tgz
        AddType application/x-compress .Z
        AddType application/x-gzip .gz .tgz
        AddType application/x-httpd-php .php
        AddType application/x-httpd-php .php3
    </IfModule>
    
    
    EnableSendfile off
    AcceptFilter http none
    AcceptFilter https none
    # 引入一些配置文件,把一些配置项写入独立的文件,让主配置文件显得简洁
    Include conf/extra/httpd-autoindex.conf
    Include conf/extra/httpd-vhosts.conf
    # 进行条件判断,如果加载了proxy_html_module模块则包含
    <IfModule proxy_html_module>
    Include conf/extra/proxy-html.conf
    </IfModule>
    <IfModule ssl_module>
    SSLRandomSeed startup builtin
    SSLRandomSeed connect builtin
    </IfModule>
    # 代表引入 G:/wamp/wamp/alias 下的所有文件
    Include "G:/wamp/wamp/alias/*"
```
### 虚拟主机配置

1、IP地址相同，但端口号不同：  
现在我的CentOS上，只有一个IP：192.168.0.94，我想分别使用8080和8081两个端口配置两个网站，编辑httpd.conf：  

```apache
    Listen 8080  
    Listen 8081  
    <VirtualHost 192.168.0.94:8080>  
        DocumentRoot /var/www/web1  
        DirectoryIndex index.html index.htm  
        HostNameLookups off  
    </VirtualHost>  
    <VirtualHost 192.168.0.94:8081>  
        DocumentRoot /var/www/web2  
        DirectoryIndex index.html index.htm  
        HostNameLookups off  
    </VirtualHost>
```
重启服务，即可。

2、端口号相同，但IP地址不同，假如一个是94，一个是95：  

```apache
    <VirtualHost 192.168.0.94>  
        ServerName 192.168.0.94:80  
        DocumentRoot /var/www/web1  
        DirectoryIndex index.html index.htm   
    </VirtualHost>  
    <VirtualHost 192.168.0.95>  
        ServerName 192.168.0.95:80  
        DocumentRoot /var/www/web2  
        DirectoryIndex index.html index.htm 
    </VirtualHost>
```
如果本机只有一个网卡，那么就得在这一块网卡上绑定多IP：

    ifconfig eth0:1 192.168.0.95

3、基于域名的虚拟主机

```apache
    NameVirtualHost 192.168.0.94：  
    <VirtualHost www.web1.com>  
        ServerName www.web1.com:80  
        DocumentRoot /var/www/web1  
        DirectoryIndex index.html index.htm   
    </VirtualHost>  
    <VirtualHost www.web2.com>  
        ServerName www.web2.com:80  
        DocumentRoot /var/www/web2  
        DirectoryIndex index.html index.htm 
    </VirtualHost>
```
然后大家在，linux下的/etc/hosts文件或者windows下C:/WINNT/system32/drivers/etc/hosts文件中，加入 192.168.0.94 www.web1.com 192.168.0.94 www.web2.com

虚拟机配置发生改变后，一定要restart。