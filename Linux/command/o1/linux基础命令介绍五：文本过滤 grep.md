# [linux基础命令介绍五：文本过滤 grep][0]

**vvpale**

<font face=微软雅黑>

在linux中经常需要对文本或输出内容进行过滤，最常用的过滤命令是grep    grep [OPTIONS] PATTERN [FILE...]

grep按行检索输入的每一行，如果输入行包含模式PATTERN，则输出这一行。这里的PATTERN是正则表达式(参考[前一篇][6]，本文将结合grep一同举例)。

输出文件/etc/passwd中包含root的行：

    [root@centos7 temp]# grep root /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    operator:x:11:0:operator:/root:/sbin/nologin

或者从标准输入获得：

    [root@centos7 temp]# cat /etc/passwd | grep root
    root:x:0:0:root:/root:/bin/bash
    operator:x:11:0:operator:/root:/sbin/nologin

需要注意的地方是：当grep的输入既来自文件也来自标准输入时，grep将忽略标准输入的内容不做处理，除非使用符号-来代表标准输入：

    [root@centos7 temp]# cat /etc/passwd | grep root /etc/passwd -
    /etc/passwd:root:x:0:0:root:/root:/bin/bash
    /etc/passwd:operator:x:11:0:operator:/root:/sbin/nologin
    (标准输入):root:x:0:0:root:/root:/bin/bash
    (标准输入):operator:x:11:0:operator:/root:/sbin/nologin

此时，grep会标明哪些结果来自于文件哪些来自于标准输入。

输出文件/etc/passwd和文件/etc/group中以root开头的行：

    [root@centos7 temp]# grep "^root" /etc/passwd /etc/group
    /etc/passwd:root:x:0:0:root:/root:/bin/bash
    /etc/group:root:x:0:

输出文件/etc/passwd中以/bin/bash结尾的行：

    [root@centos7 temp]# grep "/bin/bash$" /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    learner:x:1000:1000::/home/learner:/bin/bash

注意以上两个例子中PATTERN被双引号引用起来以防止被shell解析。

输出文件/etc/passwd中不以a-s中任何一个字母开头的行：

    [root@centos7 temp]# grep "^[^a-s]" /etc/passwd 
    tss:x:59:59:Account used by the trousers package to sandbox the tcsd daemon:/dev/null:/sbin/nologin
    tcpdump:x:72:72::/:/sbin/nologin

这里需要理解两个^间不同的含义，第一个^表示行首，第二个在[]内部的首个字符^表示取反。

输出文件/etc/passwd中字符0连续出现3次及以上的行(注意转义字符'\')：

    [root@centos7 temp]# grep "0\{3,\}" /etc/passwd
    learner:x:1000:1000::/home/learner:/bin/bash
    

如输出文件/etc/passwd中以字符r或l开头的行：

    [root@centos7 temp]# grep "^[r,l]" /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    lp:x:4:7:lp:/var/spool/lpd:/sbin/nologin
    learner:x:1000:1000::/home/learner:/bin/bash

选项-i使grep在匹配模式时忽略大小写：

    [root@centos7 temp]# grep -i abcd file 
    ABCD
    function abcd() {
    [root@centos7 temp]#

选项-o表示只输出匹配的字符，而不是整行：

    [root@centos7 temp]# grep -oi abcd file 
    ABCD
    abcd
    [root@centos7 temp]#

选项-c统计匹配的行数：

    [root@centos7 temp]# grep -oic abcd file 
    2
    [root@centos7 temp]#

选项-v表示取反匹配，如输出/etc/passwd中不以/sbin/nologin结尾的行：

    [root@centos7 temp]# grep -v "/sbin/nologin$" /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    sync:x:5:0:sync:/sbin:/bin/sync
    shutdown:x:6:0:shutdown:/sbin:/sbin/shutdown
    halt:x:7:0:halt:/sbin:/sbin/halt
    learner:x:1000:1000::/home/learner:/bin/bash

选项-f FILE表示以文件FILE中的每一行作为模式匹配：

    [root@centos7 temp]# cat test
    abcd
    ABCD
    [root@centos7 temp]# grep -f test file 
    ABCD
    function abcd() {
    [root@centos7 temp]# 

选项-x表示整行匹配：

    [root@centos7 temp]# grep -xf test file 
    ABCD
    [root@centos7 temp]#

选项-w表示匹配整个单词：

    [root@centos7 temp]# grep here file
    here
    there
    [root@centos7 temp]# grep -w here file
    here
    [root@centos7 temp]# 

选项-h表示当多个文件时不输出文件名：

    [root@centos7 temp]# cat /etc/passwd|grep ^root - /etc/passwd -h
    root:x:0:0:root:/root:/bin/bash
    root:x:0:0:root:/root:/bin/bash

选项-n表示显示行号：

    [root@centos7 temp]# grep -n "^[r,l]" /etc/passwd
    1:root:x:0:0:root:/root:/bin/bash
    5:lp:x:4:7:lp:/var/spool/lpd:/sbin/nologin
    24:learner:x:1000:1000::/home/learner:/bin/bash

选项-A N、-B N、-C N表示输出匹配行和其'周围行'

    -A N 表示输出匹配行和其之后(after)的N行
    -B N 表示输出匹配行和其之前(before)的N行
    -C N 表示输出匹配行和其之前之后各N行
    [root@centos7 temp]# grep -A 2 ^operator /etc/passwd
    operator:x:11:0:operator:/root:/sbin/nologin
    games:x:12:100:games:/usr/games:/sbin/nologin
    ftp:x:14:50:FTP User:/var/ftp:/sbin/nologin
    [root@centos7 temp]# grep -B2 ^operator /etc/passwd   
    halt:x:7:0:halt:/sbin:/sbin/halt
    mail:x:8:12:mail:/var/spool/mail:/sbin/nologin
    operator:x:11:0:operator:/root:/sbin/nologin
    [root@centos7 temp]# grep -C1 ^operator /etc/passwd  
    mail:x:8:12:mail:/var/spool/mail:/sbin/nologin
    operator:x:11:0:operator:/root:/sbin/nologin
    games:x:12:100:games:/usr/games:/sbin/nologin

选项-F视PATTERN为它的字面意思匹配(忽略字符的特殊含义)，等同于执行命令fgrep：

    [root@centos7 temp]# grep -F ^root /etc/passwd
    [root@centos7 temp]# 

命令无输出

选项-E可以使用扩展的正则表达式，如同执行egrep命令：

    [root@centos7 temp]# egrep "^root|^learner" /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    learner:x:1000:1000::/home/learner:/bin/bash

使用扩展正则表达式意味着不需要转义就能表示字符的特殊含义，包括?,+,{,|,(和)。

选项-P表示使用perl的正则表达式进行匹配  
如：

    [root@centos7 ~]# echo "helloworld123456"| grep -oP "\d+"
    123456
    [root@centos7 ~]#

perl正则中"\d"表示数字，+表示匹配一到多次(同vim)。

选项-a将二进制文件当成文本文件处理：

    [root@centos7 ~]# grep -a online /usr/bin/ls
    %s online help: <%s>
    [root@centos7 ~]#

选项--exclude=GLOB和--include=GLOB分别表示排除和包含匹配GLOB的文件，GLOB表示通配符(find及xargs用法见[基础命令介绍三][7])：

    [root@centos7 temp]# find . -type f | xargs grep --exclude=*.txt --include=test* bash
    ./test.sh:#!/bin/bash
    [root@centos7 temp]#

grep强大的过滤能力来自于各种选项以及正则表达式的配合，在今后的文章中还有更多的例子。

</font>

[0]: /a/1190000007416745
[6]: https://segmentfault.com/a/1190000007405687
[7]: https://segmentfault.com/a/1190000007354176