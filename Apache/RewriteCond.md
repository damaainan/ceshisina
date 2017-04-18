# Apache htaccess 中的RewriteCond 规则介绍 (转)

apache 模块mod_rewrite  
提供了一个基于正则表达式分析器的重写引擎来实时重写URL请求。它支持每个完整规则可以拥有不限数量的子规则以及附加条件规则的灵活而且强大的URL操作机制。此URL操作可以依赖于各种测试，比如服务器变量、环境变量、HTTP头、时间标记，甚至各种格式的用于匹配URL组成部分的查找数据库。  
  
  
此模块可以操作URL的所有部分(包括路径信息部分)，在服务器级的(httpd.conf)和目录级的(. htaccess )配置都有效，还可以生成最终请求字符串。此重写操作的结果可以是内部子处理，也可以是外部请求的转向，甚至还可以是内部代理处理。  

这里着重介绍一下**RewriteCond**的规则以及参数说明。  
RewriteCond指令定义了规则生效的条件，即在一个RewriteRule指令之前可以有一个或多个RewriteCond指令。条件之后的重写规则仅在当前URI与Pattern匹配并且满足此处的条件(TestString能够与CondPattern匹配)时才会起作用。 
```
【说明】定义重写发生的条件  
【语法】RewriteCond TestString CondPattern [flags]  
【作用域】server config, virtual host, directory, .htaccess  
【覆盖项】FileInfo  
【状态】 扩展(E)  
【模块】mod_rewrite  
```

###### TestString是一个纯文本的字符串，但是还可以包含下列可扩展的成分：  

> RewriteRule反向引用 ，引用方法是：$N (0 <= N <= 9)引用当前(带有若干RewriteRule指令的)RewriteCond中的与Pattern匹配的分组成分(圆括号!)。 

> RewriteCond反向引用 ，引用方法是：%N (1 <= N <= 9)引用当前若干RewriteCond条件中最后符合的条件中的分组成分(圆括号!)。  

> RewriteMap扩展 ，引用方法是：${mapname:key|default} 细节请参见RewriteMap指令。  

> 服务器变量 ，引用方法是：%{NAME_OF_VARIABLE}


NAME_OF_VARIABLE可以是下表列出的字符串之一：  
```
# HTTP头连接与请求   
HTTP_USER_AGENT  
HTTP_REFERER  
HTTP_COOKIE  
HTTP_FORWARDED  
HTTP_HOST  
HTTP_PROXY_CONNECTION  
HTTP_ACCEPT REMOTE_ADDR  
REMOTE_HOST  
REMOTE_PORT  
REMOTE_USER  
REMOTE_IDENT  
REQUEST_METHOD  
SCRIPT_FILENAME  
PATH_INFO  
QUERY_STRING  
AUTH_TYPE   
# 服务器自身 日期和时间 其它  
DOCUMENT_ROOT  
SERVER_ADMIN  
SERVER_NAME  
SERVER_ADDR  
SERVER_PORT  
SERVER_PROTOCOL  
SERVER_SOFTWARE TIME_YEAR  
TIME_MON  
TIME_DAY  
TIME_HOUR  
TIME_MIN  
TIME_SEC  
TIME_WDAY  
TIME API_VERSION  
THE_REQUEST  
REQUEST_URI  
REQUEST_FILENAME  
IS_SUBREQ  
HTTPS  
```
这些变量都对应于类似命名的HTTP MIME头、Apache服务器的C变量、Unix系统中的struct tm字段，其中的大多数在其他的手册或者CGI规范中都有说明。 

###### 其中为mod_rewrite所特有的变量如下：  
```
IS_SUBREQ  
如果正在处理的请求是一个子请求，它将包含字符串”true”，否则就是”false”。模块为了解析URI中的附加文件，可能会产生子请求。  
API_VERSION  
这是正在使用中的Apache模块API(服务器和模块之间内部接口)的版本， 其定义位于include/ap_mmn.h中。此模块API版本对应于正在使用的Apache的版本(比如在Apache 1.3.14的发行版中这个值是19990320:10)。通常，对它感兴趣的是模块的开发者。  
THE_REQUEST  
这是由浏览器发送的完整的HTTP请求行(比如：”GET /index.html HTTP/1.1″)。 它不包含任何浏览器发送的其它头信息。  
REQUEST_URI  
这是在HTTP请求行中所请求的资源(比如上述例子中的”/index.html”)。  
REQUEST_FILENAME  
这是与请求相匹配的完整的本地文件系统的文件路径名。  
HTTPS  
如果连接使用了SSL/TLS，它将包含字符串”on”，否则就是”off”(无论mod_ssl 是否已经加载，该变量都可以安全的使用)。  
```
**其它注意事项**：  
SCRIPT_FILENAME和REQUEST_FILENAME包含的值是相同的——即Apache服务器内部的request_rec结构中的filename字段。第一个就是大家都知道的CGI变量名，而第二个则是REQUEST_URI(request_rec结构中的uri字段)的一个副本。  

特殊形式：%{ENV:variable} ，其中的variable可以是任意环境变量。它是通过查找Apache内部结构或者(如果没找到的话)由Apache服务器进程通过getenv()得到的。  

特殊形式：%{SSL:variable} ，其中的variable可以是一个SSL环境变量 的名字，无论mod_ssl 模块是否已经加载都可以使用(未加载时为空字符串)。比如：%{SSL:SSL_CIPHER_USEKEYSIZE}将会被替换为128。  

特殊形式：%{HTTP:header} ，其中的header可以是任意HTTP MIME头的名称。它总是可以通过查找HTTP请求而得到。比如：%{HTTP:Proxy-Connection}将被替换为Proxy-Connection:HTTP头的值。  

预设形式：%{LA-U:variable} ，variable的最终值在执行一个内部(基于URL的)子请求后确定。当需要使用一个目前未知但是会在之后的过程中设置的变量的时候，就可以使用这个方法。例如，需要在服务器级配置(httpd.conf文件)中根据REMOTE_USER变量进行重写，就必须使用%{LA-U:REMOTE_USER}。 因为此变量是由URL重写(mod??_rewrite)步骤之后的认证步骤设置的。但是另一方面，因为mod_rewrite是通过API修正步骤来实现目录级(.htaccess文件)配置的，而认证步骤先于API修正步骤，所以可以用%{REMOTE_USER}。  

预设形式：%{LA-F:variable} ，variable的最终值在执行一个内部(基于文件名的)子请求后确定。大多数情况下和上述的LA-U是相同的。  
  
CondPattern是条件模式，即一个应用于当前TestString实例的正则表达式。TestString将被首先计算，然后再与CondPattern匹配。  
注意：CondPattern是一个perl兼容的正则表达式，但是还有若干增补：  

> 1、可以在CondPattern串的开头使用’!'(惊叹号)来指定 不匹配 。  
> 2、CondPatterns有若干特殊的变种。

除了正则表达式的标准用法，还有下列用法：  

```
‘<CondPattern ‘(词典顺序的小于)  
将CondPattern视为纯字符串，与TestString按词典顺序进行比较。如果TestString小于CondPattern则为真。  

‘>CondPattern ‘(词典顺序的大于)  
将CondPattern视为纯字符串，与TestString按词典顺序进行比较。如果TestString大于CondPattern则为真。  

‘=CondPattern ‘(词典顺序的等于)  
将CondPattern视为纯字符串，与TestString按词典顺序进行比较。如果TestString等于CondPattern(两个字符串逐个字符地完全相等)则为真。如果CondPattern是”"(两个双引号)，则TestString将与空字符串进行比较。  

‘-d ‘(目录)  
将TestString视为一个路径名并测试它是否为一个存在的目录。  

‘-f ‘(常规文件)  
将TestString视为一个路径名并测试它是否为一个存在的常规文件。  

‘-s ‘(非空的常规文件)  
将TestString视为一个路径名并测试它是否为一个存在的、尺寸大于0的常规文件。 

‘-l ‘(符号连接)  
将TestString视为一个路径名并测试它是否为一个存在的符号连接。  

‘-x ‘(可执行)  
将TestString视为一个路径名并测试它是否为一个存在的、具有可执行权限的文件。
该权限由操作系统检测。  
‘-F ‘(对子请求存在的文件)  
检查TestString是否为一个有效的文件，而且可以在服务器当前的访问控制配置下被访问。它使用一个内部子请求来做检查，由于会降低服务器的性能，所以请谨慎使用！  

‘-U ‘(对子请求存在的URL)  
检查TestString是否为一个有效的URL，而且可以在服务器当前的访问控制配置下被访问。它使用一个内部子请求来做检查，由于会降低服务器的性能，所以请谨慎使用！ 
```

注意： 所有这些测试都可以用惊叹号作前缀(‘!’)以实现测试条件的反转。  


> 3、还可以在CondPattern之后追加特殊的标记[flags] 作为RewriteCond指令的第三个参数。flags是一个以逗号分隔的以下标记的列表：  

```
‘nocase|NC ‘(忽略大小写)  
它使测试忽略大小写，扩展后的TestString和CondPattern中’AZ’ 和’a-z’是没有区别的。此标记仅用于TestString和CondPattern的比较，而对文件系统和子请求的检查不起作用。  

‘ornext|OR ‘(或下一条件)  
它以OR方式组合若干规则的条件，而不是隐含的AND。 
```

典型的例子如下：  
RewriteCond %{REMOTE_HOST} ^host1.* [OR]  
RewriteCond %{REMOTE_HOST} ^host2.* [OR]  
RewriteCond %{REMOTE_HOST} ^host3.*  
RewriteRule …针对这3个主机的规则集…如果不用这个标记，你就必须要书写三次条件/规则对。  
举例  
如果要按请求头中的”User-Agent:”重写一个站点的主页，可以这样写：  
RewriteCond % { HTTP_USER_AGENT } ^Mozilla.* RewriteRule ^/$ /homepage. max .html [ L ]  
  
RewriteCond % { HTTP_USER_AGENT } ^Lynx.* RewriteRule ^/$ /homepage. min .html [ L ]  
  
RewriteRule ^/$ /homepage .std.html [ L ]  
解释：  
如果你使用的浏览器识别标志是’Mozilla’，则你将得到内容最大化的主页(含有Frames等等)。  
如果你使用的是(基于终端的)Lynx， 则你得到的是内容最小化的主页(不含table等等)。  
如果上述条件都不满足(使用的是其他浏览器)，则你得到的是一个标准的主页。

