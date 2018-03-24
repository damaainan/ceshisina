## grep&amp;正则表达式

来源：[http://www.178linux.com/92847](http://www.178linux.com/92847)

时间 2018-03-23 11:08:12



## grep&正则表达式


grep（global search regular expression(RE) and print out the line，全面搜索正则表达式并把行打印出来）是一种强大的文本搜索工具，它能使用正则表达式搜索文本，并把匹配的行打印出来。


#### 语法

```
    grep [选项]... PATTERN [FILE]...
```


#### 选项

```
-a 不要忽略二进制数据。
 -A<显示列数> 除了显示符合范本样式的那一行之外，并显示该行之后的内容。
 -b 在显示符合范本样式的那一行之外，并显示该行之前的内容。
 -c 计算符合范本样式的列数。
 -C<显示列数>或-<显示列数>  除了显示符合范本样式的那一列之外，并显示该列之前后的内容。
 -d<进行动作> 当指定要查找的是目录而非文件时，必须使用这项参数，否则grep命令将回报信息并停止动作。
 -e<范本样式> 指定字符串作为查找文件内容的范本样式。
 -E 将范本样式为延伸的普通表示法来使用，意味着使用能使用扩展正则表达式。
 -f<范本文件> 指定范本文件，其内容有一个或多个范本样式，让grep查找符合范本条件的文件内容，格式为每一列的范本样式。
 -F 将范本样式视为固定字符串的列表。
 -G 将范本样式视为普通的表示法来使用。
 -h 在显示符合范本样式的那一列之前，不标示该列所属的文件名称。
 -H 在显示符合范本样式的那一列之前，标示该列的文件名称。
 -i 忽略字符大小写的差别。
 -l 列出文件内容符合指定的范本样式的文件名称。
 -L 列出文件内容不符合指定的范本样式的文件名称。
 -n 在显示符合范本样式的那一列之前，标示出该列的编号。
 -q 不显示任何信息。
 -R/-r 此参数的效果和指定“-d recurse”参数相同。
 -s 不显示错误信息。
 -v 反转查找。
 -w 只显示全字符合的列。
 -x 只显示全列符合的列。
 -y 此参数效果跟“-i”相同。
 -o 只输出文件中匹配到的部分。
```


#### 正则表达式

```
^word           搜寻以word开头的行。 例如：搜寻以#开头的脚本注释行 grep –n '^#' /PATH/TO/FILENAME
 word$           搜寻以word结束的行
 .               匹配任意一个字符。 例如：grep –n 'e.e regular.txt 匹配e和e之间有任意一个字符，可以匹配eee，eae，eve，但是不匹配ee。
 \               转义字符。 例如：搜寻’，’是一个特殊字符，在正则表达式中有特殊含义。必须要先转义。grep –n '\,' /PATH/TO/FILENAME
 *               前面的字符重复0到多次。 例如匹配gle，gogle，google，gooogle等等 grep –n 'go*gle' /PATH/TO/FILENAME
 [list]          匹配一系列字符中的一个。 例如：匹配gl，gf。grep –n 'g[lf]' /PATH/TO/FILENAME
 [n1-n2]         匹配一个字符范围中的一个字符。 例如：匹配数字字符 grep –n '[0-9]' /PATH/TO/FILENAME
 [^list]         匹配字符集以外的字符 例如：grep –n '[^o]' /PATH/TO/FILENAME 匹配非o字符
 \<word          匹配单词开头。 例如：匹配以g开头的单词 grep –n '\<g' /PATH/TO/FILENAME
 word\>          匹配单词结尾 例如：匹配以tion结尾的单词 grep –n 'tion\>' /PATH/TO/FILENAME
 word\{n1\}      前面的字符重复n1 例如：匹配google。 grep –n 'go\{2\}gle' /PATH/TO/FILENAME
 word\{n1,\}     前面的字符至少重复n1 例如：匹配google，gooogle。 grep –n 'go\{2\}gle' /PATH/TO/FILENAME
 word\{n1,n2\}   前面的字符重复n1，n2次 例如：匹配google，gooogle。 grep –n 'go\{2,3\}gle' /PATH/TO/FILENAME
```


#### 扩展正则表达式

```
?    匹配0个或1个在其之前的那个普通字符。
       例如，匹配gd，god   grep –nE 'go?d' /PATH/TO/FILENAME
 
 +    匹配1个或多个在其之前的那个普通字符，重复前面字符1到多次。 
      例如：匹配god，good，goood等等字符串。
      grep –nE 'go+d' /PATH/TO/FILENAME
 
 ()   表示一个字符集合或用在expr中，匹配整个括号内的字符串，
      原来都是匹配单个字符。 例如：搜寻good或者glad
      grep –nE 'g(oo|la)' /PATH/TO/FILENAME
 
 |    表示“或”，匹配一组可选的字符，或（or）的方式匹配多个字串。
      例如：grep –nE 'god|good' /PATH/TO/FILENAME 匹配god或者good。
```


#### 常用的集合方法

```
[:alnum:]       匹配任意一个字母或数字字符
 [:alpha:]       匹配任意一个字母字符（包括大小写字母）
 [:blank:]       空格与制表符（横向和纵向）
 [:digit:]       匹配任意一个数字字符
 [:lower:]       匹配小写字母
 [:upper:]       匹配大写字母
 [:punct:]       匹配标点符号
 [:space:]       匹配一个包括换行符、回车等在内的所有空白符
 [:graph:]       匹配任何一个可以看得见的且可以打印的字符
 [:xdigit:]      任何一个十六进制数（即：0-9，a-f，A-F）
 [:cntrl:]       任何一个控制字符（ASCII字符集中的前32个字符)
 [:print:]       任何一个可以打印的字符
```


#### 实例

```sh
     cat test
     hello world 
     HELLO everyone
     helLo everyone hello world
     HELLO EVERYONE HELLO WORLD
     #I LIKE EVERYTHING
```




* 统计符合范本样式的列数
```sh
 cat test | grep -c "hello"
 2
```

    

* 统计时忽略字符大小写的差别
```sh
 cat test | grep -i "hello"
 hello world 
 HELLO everyone
 helLo everyone hello world
 HELLO EVERYONE HELLO WORLD
```

    

* 统计时显示行数
```sh
 cat test | grep -n "hello"
 1:hello world 
 3:helLo everyone hello world
```

    

* 反向统计，不包含统计字符的所有行
```sh
  cat test | grep -v "hello"
 HELLO everyone
 HELLO EVERYONE HELLO WORLD
 #I LIKE EVERYTHING
```

    

* 统计以”hel”开通第四个字符是”l”或”L”的所有行
```sh
  cat test | grep "hel[lL]o"
 hello world 
 helLo everyone hello world
```

    

* 统计以hello开头的所有行
```sh
  cat test | grep "^hello"
 hello world
```

    

* 统计开通不是“h”或“I”的所有行，注意[^list]list是字符集，不是world
```sh
  cat test | grep "^[^hI]"
 HELLO everyone
 HELLO EVERYONE HELLO WORLD
```

    

* 统计以字符w开头，ld结尾，中间任意两个字符的所有行
```sh
  cat test | grep "w..ld"
 hello world 
 helLo everyone hello world
```

    

* 统计以字符h开头，ld结尾，中间任意字符的所有行
```sh
  cat test | grep "h.*ld"
 hello world 
 helLo everyone hello world
```

    

* 统计以匹配单词统计的所有行
```sh
  cat test | grep "\<hello\>"
 hello world 
 helLo everyone hello world
```

    

* 统计“l”重复2次的所有行
```sh
  cat test | grep "l\{2\}"
 hello world 
 helLo everyone hello world
```

    

* 统计“l”重复1或2次的所有行
```sh
  cat test | grep "l\{1,2\}"
 hello world 
 helLo everyone hello world
```

    

* 统计“l”重复2次或2次以上以上的所有行
```sh
  cat test | grep "l\{2,\}"
 hello world 
 helLo everyone hello world
```

    

* 统计显示空白行的行号
```sh
  cat test | grep -n "^$"
 6:
 7:
 8:
 9:
```

    

* 统计空白行和注释行，带行号
```sh
  cat test | grep -nE "^$|#"
 5:#I LIKE EVERYTHING
 6:
 7:
 8:
 9:
```

    
  

## 综合实例




* 复制/etc/skel目录为/home/tuser1，要求/home/tuser1及其内部文件的属组和其它用户均没有任何访问权限。
```sh
 useradd tuser1;chmod -R go=--- /home/tuser1
  ll -a /home/tuser1
 总用量 12
 drwx------.  2 tuser1 tuser1  62 3月  22 10:05 .
 drwxr-xr-x. 10 root   root   124 3月  22 10:05 ..
 -rw-------.  1 tuser1 tuser1  18 8月   3 2016 .bash_logout
 -rw-------.  1 tuser1 tuser1 193 8月   3 2016 .bash_profile
 -rw-------.  1 tuser1 tuser1 231 8月   3 2016 .bashrc
```

    

* 编辑/etc/group文件，添加组hadoop。
```sh
 let groupid=$( cat /etc/group | grep "\<hadoop\>" || cat /etc/group | tail -1 | cut -d: -f3 )+1 && echo "hadoop:x:$groupid" >>/etc/group && cat /etc/group | grep "\<hadoop\>"
 hadoop:x:1009
```

    

* 手动编辑/etc/passwd文件新增一行，添加用户hadoop，其基本组ID为hadoop组的id号；其家目录为/home/hadoop
```
#!/bin/bash
 #20180322 by eighteenxu
 
 
 uid=$(cat /etc/group | grep "\<hadoop\>" | cut -d: -f3)
 
 id $uid &> /dev/null
 
 if [ $? -eq 0 ];then
         echo "该ID用户已存在"
 else
         echo "hadoop:x:$uid:$uid::/home/hadoop:/bin/bash" >> /etc/passwd && cat /etc/passwd | grep "\<hadoop\>"
 fi
```

    

* 复制/etc/skel目录为/home/hadoop，要求修改hadoop目录的属组和其它用户没有任何访问权限。      

修改/home/hadoop目录及其内部所有文件的属主为hadoop，属组为hadoop。
```
#!/bin/bash
 #20180322 by eighteenxu
 
 
 #检查目录/home/hadoop是否存在,如果存在直接复制，不存在新建目录并复制
 cd /home/hadoop
 if [ $? -eq 0 ];then
         cp -R /etc/skel/. /home/hadoop
 else
         mkdir /home/hadoop && cp -R /etc/skel/. /home/hadoop
 fi
 
 #更改目录属住和属组，更改目录和子目录的权限
 chown -R hadoop:hadoop /home/hadoop && chmod -R go=--- /home/hadoop
 
  ll /home | grep hadoop  && ll -a /home/hadoop
 drwx------. 2 hadoop    hadoop    62 4月  11 2017 hadoop
 总用量 12
 drwx------. 2 hadoop hadoop  62 4月  11 2017 .
 drwxr-xr-x. 9 root   root   106 3月  22 14:13 ..
 -rw-------. 1 hadoop hadoop  18 3月  22 14:08 .bash_logout
 -rw-------. 1 hadoop hadoop 193 3月  22 14:08 .bash_profile
 -rw-------. 1 hadoop hadoop 231 3月  22 14:08 .bashrc
```

    

* 显示/proc/meminfo文件中以大写或小写S开头的行；用两种方式；
```sh
 cat /proc/meminfo | grep "^[sS]"
 SwapCached:            0 kB
 SwapTotal:       2097148 kB
 SwapFree:        2097148 kB
 Shmem:              6868 kB
 Slab:              64376 kB
 SReclaimable:      28160 kB
 SUnreclaim:        36216 kB
```

    

* 显示/etc/passwd文件中其默认shell为非/sbin/nologin的用户；
```sh
 cat /etc/passwd | grep -v "/bin/nologin$" | cut -d: -f1
 root
 bin
 daemon
 adm
 lp
 sync
 shutdown
 halt
 mail
 operator
 games
 ftp
 nobody
 systemd-bus-proxy
 systemd-network
 dbus
 polkitd
 tss
 postfix
 sshd
 chrony
 linux
 centos
 test
 tuser1
 hadoop
```

    

* 显示/etc/passwd文件中其默认shell为/bin/bash的用户；
```sh
 cat /etc/passwd | grep "/bin/bash$" | cut -d: -f1
 root
 linux
 centos
 test
 tuser1
 hadoop
```

    

* 找出/etc/passwd文件中的一位数或两位数；
```sh
 cat /etc/passwd | grep "\<[[:digit:]]\{1,2\}\>"
 root:x:0:0:root:/root:/bin/bash
 bin:x:1:1:bin:/bin:/sbin/nologin
 daemon:x:2:2:daemon:/sbin:/sbin/nologin
 adm:x:3:4:adm:/var/adm:/sbin/nologin
 lp:x:4:7:lp:/var/spool/lpd:/sbin/nologin
 sync:x:5:0:sync:/sbin:/bin/sync
 shutdown:x:6:0:shutdown:/sbin:/sbin/shutdown
 halt:x:7:0:halt:/sbin:/sbin/halt
 mail:x:8:12:mail:/var/spool/mail:/sbin/nologin
 operator:x:11:0:operator:/root:/sbin/nologin
 games:x:12:100:games:/usr/games:/sbin/nologin
 ftp:x:14:50:FTP User:/var/ftp:/sbin/nologin
 nobody:x:99:99:Nobody:/:/sbin/nologin
 dbus:x:81:81:System message bus:/:/sbin/nologin
 tss:x:59:59:Account used by the trousers package to sandbox the tcsd daemon:/dev/null:/sbin/nologin
 postfix:x:89:89::/var/spool/postfix:/sbin/nologin
 sshd:x:74:74:Privilege-separated SSH:/var/empty/sshd:/sbin/nologin
```

    

* 显示/boot/grub2/grub.cfg中以至少一个空白字符开头的行；
```sh
 cat /boot/grub2/grub.cfg | grep -E "^[[:space:]]+"
```

    

* 显示/etc/rc.d/rc.sysinit文件中以#开头，后面跟至少一个空白字符，而后又有至少一个非空白字符的行；
```sh
 cat /etc/rc.d/rc.sysinit | grep "^#[[:space:]][[:graph:]]"
```

    

* 打出netstat -tan命令执行结果中以‘LISTEN’，后或跟空白字符结尾的行；
```sh
 netstat -tan | grep "LISTEN[[:space:]]*$"
 tcp        0      0 0.0.0.0:22              0.0.0.0:*               LISTEN     
 tcp        0      0 127.0.0.1:25            0.0.0.0:*               LISTEN     
 tcp6       0      0 :::22                   :::*                    LISTEN     
 tcp6       0      0 ::1:25                  :::*                    LISTEN
```

    

* 添加用户bash, testbash, basher, nologin (此一个用户的shell为/sbin/nologin)，而后找出当前系统上其用户名和默认shell相同的用户的信息；
```sh
 useradd bash && useradd testbash && useradd basher && useradd nologin -s /sbin/nologin
 [root@localhost cript]# cat /etc/passwd | grep "\(\<[[:alnum:]]\+\>\).*\1$"
 sync:x:5:0:sync:/sbin:/bin/sync
 shutdown:x:6:0:shutdown:/sbin:/sbin/shutdown
 halt:x:7:0:halt:/sbin:/sbin/halt
 bash:x:1010:1010::/home/bash:/bin/bash
 nologin:x:1013:1013::/home/nologin:/sbin/nologin
```

    
  


