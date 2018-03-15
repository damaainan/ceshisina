## linux基础命令介绍十：文本流编辑 sed

来源：[https://segmentfault.com/a/1190000007693562](https://segmentfault.com/a/1190000007693562)

与`vim`不同，`sed`是一种非交互式的文本编辑器，同时它又是面向字符流的，每行数据经过`sed`处理后输出。

```sh
sed [OPTION]... [script] [file]...
```
`sed`的工作过程是这样的：首先，初始化两个数据缓冲区`模式空间`和`保持空间`；`sed`读取一行输入(来自标准输入或文件)，去掉结尾的换行符(\n)后置于模式空间中，然后针对模式空间中的字符串开始执行‘sed命令’，每个命令都可以有`地址`与之相关联，地址可以看成是条件，只有在条件成立时，相关的命令才被执行；所有可执行命令都处理完毕后，仍处于模式空间中的字符串会被追加一个换行符后打印输出；之后读取下一行输入做同样的处理，直到主动退出(q)或输入结束。
## 地址

`地址`可以是如下的形式

1、`number`表示行号
2、`first~step`表示从first(数字)行开始，每隔step(数字)行
3、`$`表示最后一行(注意当出现在正则表达式中时表示行尾)
4、`/regexp/`表示匹配正则表达式regexp(关于正则表达式，请参见[这一篇][0])
5、\%regexp% 表示匹配正则表达式regexp，%可以换成任意其他单个字符。(用于regexp包含斜线/的情况)
6、`/regexp/I`匹配正则表达式regexp时不区分大小写
7、`/regexp/M`启用正则多行模式，使$不止匹配行尾，还匹配\n或\r之前的位置；使^不止匹配行首，还匹配\n或\r之后的位置。此时可以用（\`）匹配模式空间的开头位置，用（\'）匹配模式空间的结束位置。

还可以用逗号`,`分隔两个地址来表示一个范围

表示从匹配第一个地址开始，直到匹配第二个地址或文件结尾为止。如果第二个地址是个正则表达式，则不会对第一个地址匹配行进行第二个地址的匹配；如果第二个地址是行号，但小于或等于第一个地址匹配行行号，则只会匹配一行(第一个地址匹配行)。

8、`0,/regexp/`这种情况下，正则表达式regexp会在第一行就开始进行匹配。只有第二个地址是正则表达式时，第一个地址才能用0。
9、`addr1,+n`表示匹配地址addr1和其后的n行。
10、`addr1,~n`表示从匹配地址addr1开始，直到n的倍数行为止。
如果没有给出地址，所有的行都会匹配；在地址或地址范围后追加字符`!`表示对地址取反，所有不匹配的行才会被处理。
## 选项
`-n`默认时每一行处理过的字符串都会被打印输出，此选项表示关闭此默认行为。只有被命令`p`作用的字符串才会被输出。`-f file`表示从file中读取sed命令`-i`表示原地修改。应用此选项时，`sed`会创建一个临时文件，并将处理结果输出到此文件，处理完毕后，会将此临时文件覆盖至原文件。`-r`表示使用扩展的正则表达式
## 命令

**``p``** 表示打印模式空间内容，通常配合选项`-n`一起使用

```sh
[root@centos7 ~]# seq 5
1
2
3
4
5
[root@centos7 ~]# 只输出第二行到第四行
[root@centos7 ~]# seq 5|sed -n '2,4p'
2
3
4
[root@centos7 ~]# 
```

**``d``**  删除模式空间内容，立即处理下一行输入。

```sh
#删除最后一行
[root@centos7 ~]# seq 5|sed '$d'
1
2
3
4
[root@centos7 ~]#
```

**``q``**  立即退出，不再处理任何命令和输入(只接受单个地址)

```sh
[root@centos7 ~]# seq 5|sed '/3/q'
1
2
3
[root@centos7 ~]#
```

**``n``**  如果没有使用选项`-n`，输出模式空间中内容后，读取下一行输入并覆盖当前模式空间内容。如果没有更多的输入行，sed会退出执行。

```sh
[root@centos7 ~]# seq 9|sed -n 'n;p'
2
4
6
8
[root@centos7 ~]# 注意多个命令用分号分隔
```

**``s/regexp/replacement/flag``**  表示用replacement替换模式空间中匹配正则表达式regexp的部分。在这里符号`/`可以换成任意单个字符。

```sh
[root@centos7 ~]# echo "hello123world"|sed 's/[0-9]\+/,/'  
hello,world
#注意这里+需要转义，如果使用选项-r则无需转义
```

**`在replacement中`** 

1、\n (n为1-9中的一个数字)表示对正则表达式中分组`(...)`的引用；

```sh
[root@centos7 ~]# echo "hello123world"|sed -r 's/[a-z]+([0-9]+)[a-z]+/\1/'
123
[root@centos7 ~]# echo "hello123world"|sed -r 's/([a-z]+)[0-9]+([a-z]+)/\1,\2/'
hello,world
```

2、`&`表示模式空间中所有匹配regexp的部分；

```sh
[root@centos7 ~]# echo "hello123world"|sed -r 's/[0-9]+/:&:/'
hello:123:world
```

3、\L 将后面的字符转化成小写直到 \U 或 \E 出现；
4、\l 将下一个字符转化为小写；
5、\U 将后面的字符转化成大写直到 \L 或 \E 出现；
6、\u 将下一个字符转化为大写；
7、\E 停止由 \L 或 \U 起始的大小写转化；

```sh
[root@centos7 ~]# echo "hello123world"|sed -r 's/^([a-z]+)[0-9]+([a-z]+)$/\U\1\E,\u\2/'
HELLO,World
[root@centos7 ~]# 
```

**`flag`** 

1、`n`数字n表示替换第n个匹配项

```sh
[root@centos7 ~]# head -1 /etc/passwd
root:x:0:0:root:/root:/bin/bash
#替换冒号分隔的第五部分为空
[root@centos7 ~]# head -1 /etc/passwd|sed 's/[^:]\+://5'
root:x:0:0:/root:/bin/bash
```

2、`g`表示全局替换

```sh
[root@centos7 ~]# echo "hello123world"|sed 's/./\U&\E/'
Hello123world
[root@centos7 ~]# 
[root@centos7 ~]# echo "hello123world"|sed 's/./\U&\E/g'
HELLO123WORLD
[root@centos7 ~]#
#当数字n和g同时使用时，表示从第n个匹配项开始替换一直到最后匹配项
[root@centos7 ~]# head -1 /etc/passwd|sed 's/[^:]\+://4g'
root:x:0:/bin/bash/
```

3、`p`表示如果替换成功，则打印模式空间内容。
4、`w file`表示如果替换成功，则输出模式空间内容至文件file中。
5、`I`和`i`表示匹配regexp时不区分大小写。

```sh
[root@centos7 ~]# echo 'HELLO123world'|sed -r 's/[a-z]+//Ig'
123
[root@centos7 ~]#
```

6、`M`和`m`表示启用正则多行模式(如前所述)。(讲命令`N`时再举例)

**``y/source-chars/dest-chars/``** 把source-chars中的字符替换为dest-chars中对应位置的字符，`/`可以换为其他任意单个字符，source-chars和dest-chars中字符数量必须一致且不能用正则表达式。

```sh
[root@centos7 ~]# echo hello|sed 'y/el/LE/'      
hLEEo
[root@centos7 ~]#
```

**``a text``** 表示输出模式空间内容后追加输出text内容

```sh
[root@centos7 ~]# seq 3|sed '1,2a hello' 
1
hello
2
hello
3
[root@centos7 ~]#
```

**``i text``** 表示输出模式空间内容之前，先输出text内容

```sh
[root@centos7 ~]# seq 3|sed '$ihello'
1
2
hello
3
[root@centos7 ~]# 
```

**``c text``** 表示删除匹配地址或地址范围的模式空间内容，输出text内容。如果是单地址，则每个匹配行都输出，如果是地址范围，则只输出一次。

```sh
[root@centos7 ~]# seq 5|sed '1,3chello'
hello
4
5
[root@centos7 ~]# seq 5|sed '/^[^3-4]/c hello' 
hello
hello
3
4
hello
```

**``=``** 表示打印当前输入行行号

```sh
[root@centos7 ~]# seq 100|sed -n '$='
100
[root@centos7 ~]# seq 100|sed -n '/^10\|^20/='
10
20
100
[root@centos7 ~]# 转义的|表示逻辑或
```

**``r file``** 表示读取file的内容，并在当前模式空间内容输出之后输出

```sh
[root@centos7 ~]# cat file 
hello world
[root@centos7 ~]# seq 3|sed '1,2r file'
1
hello world
2
hello world
3
[root@centos7 ~]# 
```


**``w file``** 表示输出模式空间内容至file中

**``N``** 读入一行内容至模式空间后，再追加下一行内容至模式空间(此时模式空间中内容形如  line1\nline2 )，如果不存在下一行，`sed`会退出。


```sh
[root@centos7 ~]# seq 10|sed -n 'N;s/\n/ /p'
1 2
3 4
5 6
7 8
9 10
[root@centos7 ~]#
#s命令的m flag举例
[root@centos7 ~]# seq 3|sed 'N;s/^2/xxx/' 
1
2
3
[root@centos7 ~]# seq 3|sed 'N;s/^2/xxx/m'    
1
xxx
3
[root@centos7 ~]# seq 3|sed 'N;s/1$/xxx/' 
1
2
3
[root@centos7 ~]# seq 3|sed 'N;s/1$/xxx/M'
xxx
2
3
```

**``D``** 如果模式空间中没有新行(如命令`N`产生的新行)，则和命令`d`起同样作用；如果包含新行，则会删除第一行内容，然后对模式空间中剩余内容重新开始一轮处理。(注意：D后面的命令将会被忽略)

```sh
[root@centos7 ~]# seq 5|sed 'N;D'  
5
[root@centos7 ~]# seq 5|sed 'N;N;D'   
3
4
5
```

**``P``** 打印模式空间中第一行内容

```sh
[root@centos7 ~]# seq 10|sed -n 'N;P' 
1
3
5
7
9
[root@centos7 ~]# seq 10|sed -n 'N;N;P'
1
4
7
#注意另一种写法输出中的不同
[root@centos7 ~]# seq 10|sed -n '1~3P' 
1
4
7
10
```

**``g``** 用保持空间中的内容替换模式空间中的内容

```sh
[root@centos7 ~]# seq 5|sed -n 'g;N;s/\n/xx/p'
xx2
xx4
[root@centos7 ~]# 
```

**``G``** 追加一个换行符到模式空间，然后再将保持空间中的内容追加至换行符之后。(此时模式空间中内容形如 PATTERN\nHOLD )

```sh
[root@centos7 ~]# seq 5|sed 'G;s/\n/xx/'  
1xx
2xx
3xx
4xx
5xx
```

**``h``** 用模式空间中的内容替换保持空间中的内容(注意此时模式空间中的内容并没有被清除)

```sh
[root@centos7 ~]# seq 5|sed -n 'h;G;s/\n/xx/p'
1xx1
2xx2
3xx3
4xx4
5xx5
[root@centos7 ~]# seq 5|sed -n 'h;G;G;s/\n/xx/gp'
1xx1xx1
2xx2xx2
3xx3xx3
4xx4xx4
5xx5xx5
```

**``H``** 追加一个换行符到保持空间，然后再将模式空间中的内容追加至换行符之后。(此时保持空间中内容形如 HOLD\nPATTERN )

```sh
[root@centos7 ~]# seq 3|sed -n 'H;G;s/\n/xx/gp'
1xxxx1
2xxxx1xx2
3xxxx1xx2xx3
[root@centos7 ~]# 
```

**``x``** 交换模式空间和保持空间的内容

```sh
[root@centos7 ~]# seq 9|sed -n '1!{x;N};s/\n//p'
3
25
47
69
#处于{...}之中的是命令组
```


**``: label``** 为分支命令指定标签位置(不允许地址匹配)

**``b label``** 无条件跳转到label分支，如果省略了label，则跳转到整条命令结尾(即开始下一次读入)


```sh
#如删除xml文件中注释部分(<!--...-->之间的部分是注释，可以多行)
sed '/<!--/{:a;/-->/!{N;ba};d}' server.xml
#表示匹配<!--开始，在匹配到-->之前一直执行N，匹配到-->之后删除模式空间中内容
#如在nagios的配置文件中，有许多define host{...}的字段，如下所示：
define host{
use windows-server
host_name serverA
hostgroups 060202
alias 060202
contact_groups yu
address 192.168.1.1
}
#现在需要删除ip地址是192.168.1.1的段，可以这样：
sed -i '/define host/{:a;N;/}/!ba;/192\.168\.1\.1/d}' file
#注意和前一个例子中的区别
```

**``t label``** 在一次输入后有成功执行的`s`替换命令才跳转到label，如果省略了label，则跳转到整条命令结尾(即开始下一次读入)

```sh
#如行列转换
[root@centos7 ~]# seq 10|sed ':a;$!N;s/\n/,/;ta'
1,2,3,4,5,6,7,8,9,10
[root@centos7 ~]#
#如将MAC地址78A35114F798改成带冒号的格式78:A3:51:14:F7:98
[root@centos7 temp]# echo '78A35114F798'|sed -r ':a;s/\B\w{2}\b/:&/;ta'
78:A3:51:14:F7:98
[root@centos7 temp]#
#这里\b表示匹配单词边界，\B表示匹配非单词边界的其他任意字符
#当然也可以采用其他的方式实现：
[root@centos7 temp]# echo '78A35114F798'|sed -r 's/..\B/&:/g'
78:A3:51:14:F7:98
[root@centos7 temp]#
```


**``T label``** 在一次输入后只要没有替换命令被成功执行就跳转到label，如果省略了label，则跳转到整条命令结尾(即开始下一次读入)

**``z``** 表示清除模式空间中内容，和`s/.*//`起相同的作用，但更有效。


## 更多例子
### 1、删除匹配行的上一行和下一行

```sh
#例如输入数据为命令seq 10的输出(当然也可以是任意其他文件内容)
#要求删除匹配5那一行的前一行和后一行
[root@centos7 temp]# seq 10|sed -n '$!N;/\n5/{s/.*\n//p;N;d};P;D'
1
2
3
5
7
8
9
10
```
### 2、合并奇偶数行

```sh
#输入数据为命令seq 11的输出，要求分别将奇数和偶数分别放在同一行
#输出第一行`1 3 5 7 9 11`,第二行`2 4 6 8 10`
[root@centos7 ~]# seq 11|sed -nr '$!N;2!G;s/([^\n]+)\n((.+)\n)?(.+)\n(.+)/\4 \1\n\5 \3/;h;$p'
1 3 5 7 9 11
2 4 6 8 10 
[root@centos7 ~]# 
```
### 3、合并多文件

```sh
#文本a.txt的内容：
01 12510101 4001
02 12310001 4002
03 12550101 4003
04 12610001 4004
05 12810001 4005
06 12310001 4006
07 12710001 4007
08 12310001 4008
09 12810101 4009
10 12510101 4010
11 12310001 4011
12 12610001 4012
13 12310001 4013
#文本b.txt的内容：
A 12410101 2006/02/15 2009/01/31 4002
B 12310001 2006/08/31 2008/08/29 4001
C 12610001 2008/05/23 2008/05/22 4002
D 12810001 1992/12/10 1993/06/30 4001
E 12660001 1992/05/11 1993/06/01 4005
#要求输出a.txt内容中第二列和b.txt中第二列相同的行，并追加b.txt中对应的两个日期列。
#形如：02 12310001 4002 2006/08/31 2008/08/29
sed -rn '/^[01]/ba;H;:a;G;s/^((..)( .*)( [^\n]+)).*\3(( [^ ]*){2}).*/\1\5/p' b.txt a.txt
#当然如果使用awk来处理的话，解决思路更容易理解一些：
awk 'NR==FNR{a[$2]=$3FS$4;next}{if($2 in a)print $0,a[$2]}' b.txt a.txt
```

为加深对sed各种命令特性的理解，请自行分析这三个例子。

各种命令的组合使用，再加上正则表达式的强大能力，使得`sed`可以处理所有能够计算的问题。但由于代码可读性不强，理解起来比较困难，通常使用`sed`作为一个文本编辑器，对文本做非交互的流式处理。理解上述各个命令的含义，熟练使用它们，就会发现`sed`的强大之处。

[0]: https://segmentfault.com/a/1190000007405687#articleHeader0