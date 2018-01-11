[awk 多行合并【next 使用介绍】（常见应用4）][0]

在awk进行文本处理时候，我们可能会遇到。将多行合并到一行显示问题。 有点象sql里面，经常遇到的行转列的问题。 这里需要用到next语句。

**awknext语句使用**： 在循环逐行匹配，如果遇到next,就会跳过当前行，直接忽略下面语句。而进行下一行匹配。

> text.txt 内容是：

    a  
    b  
    c  
    d  
    e 

> [chengmo@centos5 shell]$ awk 'NR%2==1{next}{print NR,$0;}' text.txt   
> 2 b  
> 4 d 

> 当记录行号除以2余 1，就跳过当前行。下面的print NR,$0也不会执行。 下一行开始，程序有开始判断NR%2 值。这个时候记录行号是：2 ，就会执行下面语句块：'print NR,$0' 

**awk next使用实例：**

> **要求：**

> 文件：text.txt 格式：  
    web01[192.168.2.100]

    httpd ok  
    tomcat ok  
    sendmail ok  
    web02[192.168.2.101]  
    httpd ok  
    postfix ok  
    web03[192.168.2.102]  
    mysqld ok  
    httpd ok 

> 需要通过awk将输出格式变成：

    web01[192.168.2.100]: httpd ok  
    web01[192.168.2.100]: tomcat ok  
    web01[192.168.2.100]: sendmail ok  
    web02[192.168.2.101]: httpd ok  
    web02[192.168.2.101]: postfix ok  
    web03[192.168.2.102]: mysqld ok  
    web03[192.168.2.102]: httpd ok 

> **分析：**

> 分析发现需要将包含有“web”行进行跳过，然后需要将内容与下面行合并为一行。

    [chengmo@centos5 shell]$ awk '/^web/{T=$0;next;}{print T":\t"$0;}' test.txt

    web01[192.168.2.100]: httpd ok  
    web01[192.168.2.100]: tomcat ok  
    web01[192.168.2.100]: sendmail ok  
    web02[192.168.2.101]: httpd ok  
    web02[192.168.2.101]: postfix ok  
    web03[192.168.2.102]: mysqld ok  
    web03[192.168.2.102]: httpd ok

next在多行合并，以及选择性输出方面，非常方便。大家在使用时候不妨试试。

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/13/1850145.html