# DVWA-1.9全级别教程之Command Injection

 时间 2016-10-21 10:06:19  FreeBuf

_原文_[http://www.freebuf.com/articles/web/116714.html][1]



 目前，最新的DVWA已经更新到1.9版本（ [http://www.dvwa.co.uk/][4] ），而网上的教程大多停留在旧版本，且没有针对DVWA high级别的教程，因此萌发了一个撰写新手教程的想法，错误的地方还请大家指正。

## DVWA简介

DVWA （Damn Vulnerable Web Application）是一个用来进行安全脆弱性鉴定的PHP/MySQL Web应用，旨在为安全专业人员测试自己的专业技能和工具提供合法的环境，帮助web开发者更好的理解web应用安全防范的过程。

DVWA 共有十个模块，分别是Brute Force（暴力（破解））、Command Injection（命令行注入）、CSRF（跨站请求伪造）、File Inclusion（文件包含）、File Upload（文件上传）、Insecure CAPTCHA（不安全的验证码）、SQL Injection（SQL注入）、SQL Injection（Blind）（SQL盲注）、XSS（Reflected）（反射型跨站脚本）、XSS（Stored）（存储型跨站脚本）。

需要注意的是， DVWA 1.9 的代码分为四种安全级别：Low，Medium，High，Impossible。初学者可以通过比较四种级别的代码，接触到一些PHP代码审计的内容。

![][5]

DVWA的搭建

Freebuf 上的这篇文章《新手指南：手把手教你如何搭建自己的渗透测试环境》（[http://www.freebuf.com/sectool/102661.html][6]）已经写得非常好了，在这里就不赘述了。

之前介绍了 Brute Force 模块的内容（ [http://www.freebuf.com/articles/web/116437.html][7] ），本文介绍的是 Command Injection模块，后续教程会在之后的文章中给出。

Command Injection

Command Injection ，即命令注入，是指通过提交恶意构造的参数破坏命令语句结构，从而达到执行恶意命令的目的。PHP命令注入攻击漏洞是PHP应用程序中常见的脚本漏洞之一，国内著名的Web应用程序Discuz!、DedeCMS等都曾经存在过该类型漏洞。

![][8]

下面对四种级别的代码进行分析。

Low

服务器端核心代码

    {$cmd}

    "; } ?> 

相关函数介绍

stristr(string,search,before_search)

stristr 函数搜索字符串在另一字符串中的第一次出现，返回字符串的剩余部分（从匹配点），如果未找到所搜索的字符串，则返回FALSE。参数string规定被搜索的字符串，参数search规定要搜索的字符串（如果该参数是数字，则搜索匹配该数字对应的ASCII值的字符），可选参数before_true为布尔型，默认为“false”，如果设置为“true”，函数将返回search参数第一次出现之前的字符串部分。

_php_uname(mode)_

 这个函数会返回运行 php 的操作系统的相关描述，参数mode可取值  ”  a  ”  （此为默认，包含序列  ”  s n r v m  ”  里的所有模式），  ”  s  ”  （返回操作系统名称），  ”  n  ”  （返回主机名），  ”  r  ”  （返回版本名称），  ”  v  ”  （返回版本信息），  ”  m  ”  （返回机器类型）。

可以看到，服务器通过判断操作系统执行不同 ping 命令，但是对ip参数并未做任何的过滤，导致了严重的命令注入漏洞。

漏洞利用

window 和linux系统都可以用&&来执行多条命令

127.0.0.1&&net user

![][9]

Linux 下输入127.0.0.1&&cat /etc/shadow甚至可以读取shadow文件，可见危害之大。

Medium

服务器端核心代码

     '', 
            ';'  => '', 
        ); 
        // Remove any of the charactars in the array (blacklist). 
        $target = str_replace( array_keys( $substitutions ), $substitutions, $target ); 
        // Determine OS and execute the ping command. 
        if( stristr( php_uname( 's' ), 'Windows NT' ) ) { 
            // Windows 
            $cmd = shell_exec( 'ping  ' . $target ); 
        } 
        else { 
            // *nix 
            $cmd = shell_exec( 'ping  -c 4 ' . $target ); 
        } 
        // Feedback for the end user 
        echo "

    {$cmd}

    "; } ?>

 可以看到，相比 Low 级别的代码，服务器端对ip参数做了一定过滤，即把  ”  &&  ”  、  ”  ;  ”  删除，本质上采用的是黑名单机制，因此依旧存在安全问题。

漏洞利用

1 、127.0.0.1&net user

 因为被过滤的只有  ”  &&  ”  与  ”  ;  ”  ，所以  ”  &  ”  不会受影响。

![][10]

 这里需要注意的是  ”  &&  ”  与  ”  &  ”  的区别：

Command 1&&Command 2

先执行 Command 1 ，执行成功后执行Command 2，否则不执行Command 2

![][11]

Command 1&Command 2

先执行 Command 1 ，不管是否成功，都会执行Command 2

![][12]

 2、  由于使用的是 str_replace 把  ”  &&  ”  、  ”  ;  ”  替换为空字符，因此可以采用以下方式绕过：

127.0.0.1&;&ipconfig

 ![][13]

 这是因为  ”  127.0.0.1&;&ipconfig  ”  中的  ”  ;  ”  会被替换为空字符，这样一来就变成了  ”  127.0.0.1&& ipconfig  ”  ，会成功执行。

High

服务器端核心代码

     '', 
            ';'  => '', 
            '|  ' => '', 
            '-'  => '', 
            '$'  => '', 
            '('  => '', 
            ')'  => '', 
            '`'  => '', 
            '||' => '', 
        ); 
        // Remove any of the charactars in the array (blacklist). 
        $target = str_replace( array_keys( $substitutions ), $substitutions, $target ); 
        // Determine OS and execute the ping command. 
        if( stristr( php_uname( 's' ), 'Windows NT' ) ) { 
            // Windows 
            $cmd = shell_exec( 'ping  ' . $target ); 
        } 
        else { 
            // *nix 
            $cmd = shell_exec( 'ping  -c 4 ' . $target ); 
        } 
        // Feedback for the end user 
        echo "

    {$cmd}

    "; } ?> 

相比 Medium 级别的代码，High级别的代码进一步完善了黑名单，但由于黑名单机制的局限性，我们依然可以绕过。

漏洞利用

 黑名单看似过滤了所有的非法字符，但仔细观察到是把  ”  |  ”  （注意这里 | 后有一个空格）替换为空字符，于是  ”  |  ”  成了“漏网之鱼”。

127.0.0.1|net user

![][14]

Command 1 | Command 2

 “  |  ”  是管道符，表示将 Command 1 的输出作为Command 2的输入，并且只打印Command 2执行的结果。

Impossible

服务器端核心代码

    {$cmd}

    "; } else { // Ops. Let the user name theres a mistake echo '

    ERROR: You have entered an invalid IP.

    '; } } // Generate Anti-CSRF token generateSessionToken(); ?> 

相关函数介绍

_stripslashes(string)_

stripslashes 函数会删除字符串string中的反斜杠，返回已剥离反斜杠的字符串。

_explode(separator,string,limit)_

把字符串打散为数组，返回字符串的数组。参数separator规定在哪里分割字符串，参数string是要分割的字符串，可选参数limit规定所返回的数组元素的数目。

_is_numeric(string)_

检测string是否为数字或数字字符串，如果是返回TRUE，否则返回FALSE。

可以看到， Impossible 级别的代码加入了Anti-CSRF token，同时对参数ip进行了严格的限制，只有诸如“数字.数字.数字.数字”的输入才会被接收执行，因此不存在命令注入漏洞。


[1]: http://www.freebuf.com/articles/web/116714.html?utm_source=tuicool&utm_medium=referral
[4]: http://www.dvwa.co.uk/
[5]: http://img2.tuicool.com/MzyMZnm.jpg!web
[6]: http://www.freebuf.com/sectool/102661.html
[7]: http://www.freebuf.com/articles/web/116437.html
[8]: http://img1.tuicool.com/zMZ7FbU.jpg!web
[9]: http://image.3001.net/images/20161016/14765776761898.png!small
[10]: http://image.3001.net/images/20161016/14765777056753.png!small
[11]: http://image.3001.net/images/20161016/1476577730171.png!small
[12]: http://image.3001.net/images/20161016/1476577748635.png!small
[13]: http://image.3001.net/images/20161016/14765777625148.png!small
[14]: http://image.3001.net/images/20161016/14765777809564.png!small