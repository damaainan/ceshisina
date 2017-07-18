# [Apache URL重写规则][0]

**阅读目录**

* [1、简介][1]
* [2、工作流程][2]
* [3、URL重写指令][3]
* [1）、URL重写指令套路][4]
* [2）、RewriteRule Pattern Substitution [flags]][5]
* [3）、RewriteCond TestString CondPattern [flags]][6]
* [4）、Rewrite时服务器变量（仅列出少数）][7]
* [5）、简单正则表达式规则][8]
* [4、例子解析][9]


#### 1、简介

Apached的重写功能，即是mod_rewrite模块功能，它是apache的一个模块。它的功能非常强大，可以操作URL中的所有部分。

因此我们就可以改写url，给用户提供一个简介大方的url，当用户访问时可以通过mod_rewrite模块功能转换为真正的资源路径。通过mod_rewrite能实现的功能还有很多，例如隐藏真实地址、实现URL跳转、域名跳转、防盗链、限制访问资源类型等等。


#### 2、工作流程

mod_rewrite模块在运行时会使用两个Hook程序。

第一个是从URL到文件名转换的Hook。当有访问到达Apache服务器的时，服务器会确认相应主机（或虚拟主机），这时mod_rewrite模块就开始工作，它将会先处理服务器全局中mod_rewrite模块所提供的指令，然后根据用户提供的指令进行改写。

第二个是修正URL的Hook。在此阶段mod_rewrite模块会处理非全局的设置。例如，目录中的.htaccess文件中的设置。但是此时已经完成URL的翻译（由URL转换为文件名），因此是无法在次对目录级别的URL进行改写操作，但是moe_rewrite模块会将已翻译的URL再次转换为URL的状态，继续进行目录级别的URL改写。（mod_rewrite模块将会使用读后请求阶段的回叫函数重新开始一个请求的循环处理）

**Rewirte模块规则集的处理**

当mod_rewrite在这两个API阶段中开始执行时，它会读取配置结构中配置好的 (或者是在服务启动时建立的服务器级的，或者是在遍历目录采集到的目录级的)规则集，然后，启动URL重写引擎来处理(带有一个或多个条件的)规则集。无论是服务器级的还是目录级的规则集，都是由同一个URL重写引擎处理，只是最终结果处理不同而已。

规则集中规则的顺序是很重要的，因为重写引擎是按一种特殊的顺序处理的：逐个遍历每个规则(RewriteRule指令)，如果出现一个匹配条件的规则，则可能回头遍历已有的规则条件(RewriteCond指令)。由于历史的原因，条件规则是前置的，所以控制流程略显冗长，细节见图-1。

![][11]

可见，URL首先与每个规则的Pattern匹配，如果匹配失败，mod_rewrite将立即终止此规则的处理，继而处理下一个规则。如果匹配成功，mod_rewrite将寻找相应的规则条件，如果一个条件都没有，则简单地用Substitution构造的新值来替换URL，然后继续处理其他规则；但是如果条件存在，则开始一个内部循环按其列出的顺序逐个处理。对规则条件的处理有所不同：URL并不与模式进行匹配，而是首先通过扩展变量、反向引用、查找映射表等步骤建立一个TestString字符串，然后用它来与CondPattern匹配。如果匹配失败，则整个条件集和对应的规则失败；如果匹配成功，则执行下一个规则直到所有条件执行完毕。如果所有条件得以匹配，则以Substitution替换URL，并且继续处理。(本部分引用译者：金步国)

网络图片：

![][12]


#### 3、URL重写指令

最简单的重写指令可以简单到让你无法想象！

只需要两步就可以完成了。第一使用RewriteEngine开启mod_rewrite模块功能；第二通过RewriteRule定义URL重写规则


#### 1）、URL重写指令套路

 

    1 ---------------------------------------------------------------
    2 RewriteEngine on   #开启mod_rewrite模块功能
    3 RewriteBase 路径     #基准URL（使用alias设置别名则需使用这个）
    4 RewriteCond TestString CondPattern [flags]      #重写条件（可以多个）
    5 RewriteRule Pattern Substitution [flags]          #重写规则
    6 ----------------------------------------------------------------
    7 #4、5行可以可以多个
    8 #按顺序一个一个执行RewriteRule（[flags不终止情况下]）
    9 ##以上是常用的指令，还有一些很少见的指令，需要的自己去查资料了解


#### 2）、RewriteRule Pattern Substitution [flags]

1、pattern是作用于当前URL的perl兼容的正则表达式。当前URL是指该规则生效时刻的URL的值。它可能与被请求时的URL截然不同，因为之前可能被其他RewriteRule或者alias指令修改过。

2、Substitution 是当 URL与 Pattern匹配成功后。用来代替的字符串。

* 可以对pattern反向引用$N(N=0~9)，表示正则表达式中第N个括号中的内容
* 对最后匹配的RewriteCond反向引用%N(N=0~9)，表示最后匹配的RewriteCond第N对括号中的内容
* 服务器变量%{VARNAME}
* 映射函数调用${mapname:key|default} (通过RewriteMap指令定义映射辅助完成)

3、 [flags] ，标志符，多个则用逗号隔开。

标志符(摘抄于网上)：

**redirect|R [=code] (强制重定向 redirect)**

以 http://thishost[:thisport]/(使新的URL成为一个URI) 为前缀的Substitution可以强制性执行一个外部重定向。 如果code没有指定，则产生一个HTTP响应代码302(临时性移动)。如果需要使用在300-400范围内的其他响应代码，只需在此指定这个数值即可， 另外，还可以使用下列符号名称之一: temp (默认的), permanent, seeother. 用它可以把规范化的URL反馈给客户端，如, 重写“/~”为 “/u/”，或对/u/user加上斜杠，等等。

注意: 在使用这个标记时，必须确保该替换字段是一个有效的URL! 否则，它会指向一个无效的位置! 并且要记住，此标记本身只是对URL加上 http://thishost[:thisport]/的前缀，重写操作仍然会继续。通常，你会希望停止重写操作而立即重定向，则还需要使用’L’标记.

**forbidden|F (强制URL为被禁止的 forbidden)**

强制当前URL为被禁止的，即，立即反馈一个HTTP响应代码403(被禁止的)。使用这个标记，可以链接若干RewriteConds以有条件地阻塞某些URL。

**gone|G(强制URL为已废弃的 gone)**

强制当前URL为已废弃的，即，立即反馈一个HTTP响应代码410(已废弃的)。使用这个标记，可以标明页面已经被废弃而不存在了.

**proxy|P (强制为代理 proxy)**

此标记使替换成分被内部地强制为代理请求，并立即(即， 重写规则处理立即中断)把处理移交给代理模块。你必须确保此替换串是一个有效的(比如常见的以 http://hostname开头的)能够为Apache代理模块所处理的URI。使用这个标记，可以把某些远程成分映射到本地服务器名称空间， 从而增强了ProxyPass指令的功能。

注意: 要使用这个功能，代理模块必须编译在Apache服务器中。 如果你不能确定，可以检查“httpd -l”的输出中是否有mod_proxy.c。 如果有，则mod_rewrite可以使用这个功能；如果没有，则必须启用mod_proxy并重新编译“httpd”程序。

**last|L (最后一个规则 last)**

立即停止重写操作，并不再应用其他重写规则。 它对应于Perl中的last命令或C语言中的break命令。这个标记可以阻止当前已被重写的URL为其后继的规则所重写。 举例，使用它可以重写根路径的URL(’/’)为实际存在的URL, 比如, ‘/e/www/’.

**next|N (重新执行 next round)**

重新执行重写操作(从第一个规则重新开始). 这时再次进行处理的URL已经不是原始的URL了，而是经最后一个重写规则处理的URL。它对应于Perl中的next命令或C语言中的continue命令。 此标记可以重新开始重写操作，即, 立即回到循环的头部。  
但是要小心，不要制造死循环!

**chain|C (与下一个规则相链接 chained)**

此标记使当前规则与下一个(其本身又可以与其后继规则相链接的， 并可以如此反复的)规则相链接。 它产生这样一个效果: 如果一个规则被匹配，通常会继续处理其后继规则， 即，这个标记不起作用；如果规则不能被匹配，则其后继的链接的规则会被忽略。比如，在执行一个外部重定向时， 对一个目录级规则集，你可能需要删除“.www” (此处不应该出现“.www”的)。

**type|T=MIME-type(强制MIME类型 type)**

强制目标文件的MIME类型为MIME-type。 比如，它可以用于模拟mod_alias中的ScriptAlias指令，以内部地强制被映射目录中的所有文件的MIME类型为“application/x-httpd-cgi”。

**nosubreq|NS (仅用于不对内部子请求进行处理 no internal sub-request)**

在当前请求是一个内部子请求时，此标记强制重写引擎跳过该重写规则。比如，在mod_include试图搜索可能的目录默认文件(index.xxx)时， Apache会内部地产生子请求。对子请求，它不一定有用的，而且如果整个规则集都起作用，它甚至可能会引发错误。所以，可以用这个标记来排除某些规则。

根据你的需要遵循以下原则: 如果你使用了有CGI脚本的URL前缀，以强制它们由CGI脚本处理，而对子请求处理的出错率(或者开销)很高，在这种情况下，可以使用这个标记。

**nocase|NC (忽略大小写 no case)**

它使Pattern忽略大小写，即, 在Pattern与当前URL匹配时，’A-Z’ 和’a-z’没有区别。

**qsappend|QSA (追加请求串 query string append)**

此标记强制重写引擎在已有的替换串中追加一个请求串，而不是简单的替换。如果需要通过重写规则在请求串中增加信息，就可以使用这个标记。

**noescape|NE (在输出中不对URI作转义 no URI escaping)**

此标记阻止mod_rewrite对重写结果应用常规的URI转义规则。 一般情况下，特殊字符(如’%’, ‘$’, ‘;’等)会被转义为等值的十六进制编码。 此标记可以阻止这样的转义，以允许百分号等符号出现在输出中，如：

RewriteRule /foo/(.*) /bar?arg=P1=$1 [R,NE] 可以使’/foo/zed’转向到一个安全的请求’/bar?arg=P1=zed’.

**passthrough|PT (移交给下一个处理器 pass through)**

此标记强制重写引擎将内部结构request_rec中的uri字段设置为 filename字段的值，它只是一个小修改，使之能对来自其他URI到文件名翻译器的 Alias，ScriptAlias, Redirect 等指令的输出进行后续处理。举一个能说明其含义的例子：如果要通过mod_rewrite的重写引擎重写/abc为/def，然后通过mod_alias使/def转变为/ghi，可以这样:

RewriteRule ^/abc(.*) /def$1 [PT]

Alias /def /ghi  
如果省略了PT标记，虽然mod_rewrite运作正常， 即, 作为一个使用API的URI到文件名翻译器，它可以重写uri=/abc/…为filename=/def/…，但是，后续的mod_alias在试图作URI到文件名的翻译时，则会失效。

注意: 如果需要混合使用不同的包含URI到文件名翻译器的模块时， 就必须使用这个标记。。混合使用mod_alias和mod_rewrite就是个典型的例子。

**For Apache hackers**

如果当前Apache API除了URI到文件名hook之外，还有一个文件名到文件名的hook， 就不需要这个标记了! 但是，如果没有这样一个hook，则此标记是唯一的解决方案。 Apache Group讨论过这个问题，并在Apache 2.0 版本中会增加这样一个hook。

**skip|S=num (跳过后继的规则 skip)**

此标记强制重写引擎跳过当前匹配规则后继的num个规则。 它可以实现一个伪if-then-else的构造: 最后一个规则是then从句，而被跳过的skip=N个规则是else从句. (它和’chain|C’标记是不同的!)

**env|E=VAR:VAL (设置环境变量 environment variable)**

此标记使环境变量VAR的值为VAL, VAL可以包含可扩展的反向引用的正则表达式$N和%N。 此标记可以多次使用以设置多个变量。这些变量可以在其后许多情况下被间接引用，但通常是在XSSI (via ) or CGI (如 $ENV{’VAR’})中， 也可以在后继的RewriteCond指令的pattern中通过%{ENV:VAR}作引用。使用它可以从URL中剥离并记住一些信息。

**cookie|CO=NAME:VAL:domain[:lifetime[:path]] (设置cookie)**

它在客户端浏览器上设置一个cookie。 cookie的名称是NAME，其值是VAL。 domain字段是该cookie的域，比如’.apache.org’, 可选的lifetime是cookie生命期的分钟数，可选的path是cookie的路径。


#### 3）、RewriteCond TestString CondPattern [flags]

Rewritecond指令定义一条规则**条件**。在一条rewriterule指令前面可能会有一条或者多条rewritecond指令，只有当自身模板匹配成功且这些条件也满足时（即RewriteRule中的pattern匹配成功），规则**条件**才被应用于当前URL处理。

1、TestString是一个纯文本的字符串

* 可以对pattern反向引用$N(N=0~9)，紧跟在RewriteCond后面的RewriteRule正则表达式中第N个括号中的内容
* 反向引用%N(N=0~9)，表示RewriteCond中CondPattern中第N对括号中的内容
* 服务器变量%{VARNAME}

2、CondPattern是条件pattern，一个应用于当前实例TestString的正则表达式。即TestString与条件pattern条件进行匹配。如果匹配则RewriteCond的值为Rrue，反之为False

可以使用以下特殊变量（**可使用'!'实现反转**）：

**'>CondPattern’** (大于) 将condPattern当作一个普通字符串，将它和TestString进行比较，当TestString 的字符大于CondPattern为真。

**‘=CondPattern’** (等于) 将condPattern当作一个普通字符串，将它和TestString进行比较，当TestString 与CondPattern完全相同时为真.如果CondPattern只是 “” (两个引号紧挨在一起) 此时需TestString 为空字符串方为真。

**‘-d’** (是否为目录) 将testString当作一个目录名，检查它是否存在以及是否是一个目录。

**‘-f’** (是否是regular file) 将testString当作一个文件名，检查它是否存在以及是否是一个regular文件。

**‘-s’** (是否为长度不为0的regular文件) 将testString当作一个文件名，检查它是否存在以及是否是一个长度大于0的regular文件。

**‘-l’** (是否为symbolic link) 将testString当作一个文件名，检查它是否存在以及是否是一个 symbolic link。

**‘-F’** (通过subrequest来检查某文件是否可访问) 检查TestString是否是一个合法的文件，而且通过服务器范围内的当前设置的访问控制进行访问。这个检查是通过一个内部subrequest完成的, 因此需要小心使用这个功能以降低服务器的性能。

**‘-U’** (通过subrequest来检查某个URL是否存在) 检查TestString是否是一个合法的URL，而且通过服务器范围内的当前设置的访问控制进行访问。这个检查是通过一个内部subrequest完成的, 因此需要小心使用这个功能以降低服务器的性能。

3、[flags]是第三个参数，多个标志之间用逗号隔开

**’nocase|NC’** (不区分大小写) 在扩展后的TestString和CondPattern中，比较时不区分文本的大小写。注意，这个标志对文件系统和subrequest检查没有影响.

**’ornext|OR’** (建立与下一个条件的或的关系) 默认的情况下，二个条件之间是AND的关系，用这个标志将关系改为OR。


#### **4）、Rewrite****时服务器变量（仅列出少数）**

HTTP headers：HTTP_USER_AGENT, HTTP_REFERER, HTTP_COOKIE, HTTP_HOST, HTTP_ACCEPT

connection & request：REMOTE_ADDR, QUERY_STRING

server internals:：DOCUMENT_ROOT, SERVER_PORT, SERVER_PROTOCOL

system stuff： TIME_YEAR, TIME_MON, TIME_DAY


#### **5）、简单正则表达式规则**

. 匹配任何单字符

[chars] 匹配字符串:chars

[^chars] 不匹配字符串:chars

text1|text2 可选择的字符串:text1或text2

? 匹配0到1个字符

\* 匹配0到多个字符

\+ 匹配1到多个字符

^ 字符串开始标志

$ 字符串结束标志

\n 转义符标志

【注意】： 一代 Apache 要求URL有斜杠而二代 Apache 却不允许，因此使用 ^/?


#### 4、例子解析

例1（简单例子）：

（在.htaccess里进行规制重写）

    RewriteEngine ON 
    RewriteRule  ^user/(w+)/?$user.php?id=$1

^：输入的开头 以user/开头请求的地址

(w+)：提取所有的字母，传给$1

/?：可选斜杠

$：结束符

替换为：user.php?id=*

注意：有些apache（具体哪个版本忘啦）不兼容简写模式 w+ => [a-zA-Z_-]

例2（禁止IE和Opera浏览器访问）：

    RewriteEngine on
    RewriteCond %{HTTP_USER_AGENT} ^MSIE [NC,OR]
    RewriteCond %{HTTP_USER_AGENT} ^Opera [NC]
    RewriteRule ^.* - [F,L]       #'-'表示不替换URL

例3（不合法路径返回首页）：

    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]

例4（防盗链）：

    RewriteEngine On
    RewriteCond %{HTTP_REFERER} !^http://(.+.)?mysite.com/ [NC]       #判断请求的是否是自己的域名
    RewriteCond %{HTTP_REFERER} !^$　　　　　　　　　　　　　　　　　　　　 #{HTTP_REFERER}不为空
    RewriteRule .*.(jpe?g|gif|bmp|png)$ /images/nohotlink.jpg [L]      #返回警告图片

例5（改变访问URL目录名）：

即隐藏真实的目录名字

    RewriteEngine On
    RewriteRule ^/?old_dir/([a-z\.]+)$  new_dir/$1 [R=301,L]
    #new_dir为真正目录

例6（创建无文件后缀链接）：

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME}.php -f #判断该后缀文件是否存在
    RewriteRule ^/?([a-zA-Z0-9]+)$ $1.php [L]
    RewriteCond %{REQUEST_FILENAME}.html -f #判断该后缀文件是否存在
    RewriteRule ^/?([a-zA-Z0-9]+)$ $1.html [L]

例7（限制只能显示图片）：

    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME}  !^.*\.(gif|jpg|jpeg|png|swf)$
    RewriteRule .*$ - [F,L]

例8（文件不存在重定向404）：

    RewriteEngine on
    RewriteCond  %{REQUEST_FILENAME}  !f
    RewriteCond  %{REQUEST_FILENAME}  !d
    RewriteRule .? /404.php [L]

 （以上是自己的一些见解与总结，若有不足或者错误的地方请各位指出）

作者：[那一叶随风][13]

声明：以上只代表本人在工作学习中某一时间内总结的观点或结论。转载时请在文章页面明显位置给出原文链接

[0]: http://www.cnblogs.com/phpstudy2015-6/p/6715892.html
[1]: #_label0
[2]: #_label1
[3]: #_label2
[4]: #_label3
[5]: #_label4
[6]: #_label5
[7]: #_label6
[8]: #_label7
[9]: #_label8
[10]: #_labelTop
[11]: ./img/751643582.jpg
[12]: ./img/523904519.png
[13]: http://www.cnblogs.com/phpstudy2015-6/