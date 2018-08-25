# grep命令实例详解 | 全局正则表达式输出神器

 时间 2017-10-13 15:19:00  薄雾's Blog

原文[http://www.bowu8.com/archives/358/][1]


## I. 概述

所有的类linux系统都会提供一个名为 `grep`(`global regular expression print` ，全局正则表达式输出)的搜索工具。 `grep` 命令在对一个或多个文件的内容进行基于模式的搜索的情况下是非常有用的。模式可以是单个字符、多个字符、单个单词、或者是一个句子。 

当命令匹配到执行命令时指定的模式时， `grep` 会将包含模式的一行输出，但是并不对原文件内容进行修改。 

![][4]

在本文中，我们将会讨论到 `grep` 命令实例详解 

## II. 实例详解

### 例1 在文件中查找模式（单词）

在 `/etc/passwd` 文件中查找单词 `linuxtechi`

```shell
    root@Linux-world:~# grep linuxtechi /etc/passwd
    linuxtechi:x:1000:1000:linuxtechi,,,:/home/linuxtechi:/bin/bash
    root@Linux-world:~#
```
### 例2 在多个文件中查找模式

```shell
    root@Linux-world:~# grep linuxtechi /etc/passwd /etc/shadow /etc/gshadow
    /etc/passwd:linuxtechi:x:1000:1000:linuxtechi,,,:/home/linuxtechi:/bin/bash
    /etc/shadow:linuxtechi:$6$DdgXjxlM$4flz4JRvefvKp0DG6re:16550:0:99999:7:::/etc/gshadow:adm:*::syslog,linuxtechi
    /etc/gshadow:cdrom:*::linuxtechi
    /etc/gshadow:sudo:*::linuxtechi
    /etc/gshadow:dip:*::linuxtechi
    /etc/gshadow:plugdev:*::linuxtechi
    /etc/gshadow:lpadmin:!::linuxtechi
    /etc/gshadow:linuxtechi:!::
    /etc/gshadow:sambashare:!::linuxtechi
```
### 例3 使用 `-l` 参数列出包含指定模式的文件的文件名 

    root@Linux-world:~# grep -l linuxtechi /etc/passwd /etc/shadow /etc/fstab /etc/mtab
    /etc/passwd
    /etc/shadow

### 例4 使用 `-n` 参数，在文件中查找指定模式并显示匹配行的行号 

    root@Linux-world:~# grep -n linuxtechi /etc/passwd
    39:linuxtechi:x:1000:1000:linuxtechi,,,:/home/linuxtechi:/bin/bash

### 例5 使用 `-v` 参数输出不包含指定模式的行 

输出`/etc/passwd`文件中所有不含单词“linuxtechi”的行

    root@Linux-world:~# grep -v linuxtechi /etc/passwd

### 例6 使用 `^` 符号输出所有以某指定模式开头的行 

Bash脚本将 `^` 符号视作特殊字符，用于指定一行或者一个单词的开始。例如输出 `/etc/passwd` 文件中所有以“root”开头的行 

```shell
    root@Linux-world:~# grep ^root /etc/passwd
    root:x:0:0:root:/root:/bin/bash
```
### 例7 使用 `$` 符号输出所有以指定模式结尾的行 

输出`/etc/passwd`文件中所有以 `bash` 结尾的行 

```shell
    root@Linux-world:~# grep bash$ /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    linuxtechi:x:1000:1000:linuxtechi,,,:/home/linuxtechi:/bin/bash
```
Bash脚本将美元 `$` 符号视作特殊字符，用于指定一行或者一个单词的结尾。 

### 例8 使用 -r 参数递归地查找特定模式 

```shell
    root@Linux-world:~# grep -r linuxtechi /etc/
    /etc/subuid:linuxtechi:100000:65536
    /etc/group:adm:x:4:syslog,linuxtechi
    /etc/group:cdrom:x:24:linuxtechi
    /etc/group:sudo:x:27:linuxtechi
    /etc/group:dip:x:30:linuxtechi
    /etc/group:plugdev:x:46:linuxtechi
    /etc/group:lpadmin:x:115:linuxtechi
    /etc/group:linuxtechi:x:1000:
    /etc/group:sambashare:x:131:linuxtechi
    /etc/passwd-:linuxtechi:x:1000:1000:linuxtechi,,,:/home/linuxtechi:/bin/bash
    /etc/passwd:linuxtechi:x:1000:1000:linuxtechi,,,:/home/linuxtechi:/bin/bash
    ............................................................................
```
上面的命令将会递归的在 /etc 目录中查找 linuxtechi 单词 

### 例9 使用 grep 查找文件中所有的空行 

```shell
    root@Linux-world:~# grep ^$ /etc/shadow
```
由于 `/etc/shadow` 文件中没有空行，所以没有任何输出 

### 例10 使用 `-i` 参数查找模式 

grep 命令的 `-i` 参数在查找时忽略字符的大小写。 

我们来看一个例子，在 passwd 文件中查找 LinuxTechi 单词。 

```shell
    nextstep4it@localhost:~$ grep -i LinuxTechi /etc/passwd
    linuxtechi:x:1001:1001::/home/linuxtechi:/bin/bash
    nextstep4it@localhost:~$
```
### 例11 使用 `-e` 参数查找多个模式 

例如，我想在一条 grep 命令中查找 linuxtechi 和 root 单词，使用 `-e` 参数，我们可以查找多个模式。 

```shell
    root@Linux-world:~# grep -e "linuxtechi" -e "root" /etc/passwd
    root:x:0:0:root:/root:/bin/bash
    linuxtechi:x:1000:1000:linuxtechi,,,:/home/linuxtechi:/bin/bash
```
### 例12 使用 `-f` 用文件指定待查找的模式 

首先，在当前目录中创建一个搜索模式文件 `grep_pattern` ，我想文件中输入的如下内容。 

```shell
    root@Linux-world:~# cat grep_pattern
    ^linuxtechi
    root
    false$
```
现在，试试使用 grep_pattern 文件进行搜索 

```shell
    root@Linux-world:~# grep -f grep_pattern /etc/passwd
```
![][5]

### 例13 使用 `-c` 参数计算模式匹配到的数量 

继续上面例子，我们在 grep 命令中使用 `-c` 命令计算匹配指定模式的数量 

    root@Linux-world:~# grep -c -f grep_pattern /etc/passwd
    22

### 例14 输出匹配指定模式行的前或者后面N行

* 使用 `-B` 参数输出匹配行的前 4 行 

```shell
    root@Linux-world:~# grep -B 4 "games" /etc/passwd
```
![][6]
* 使用 `-A` 参数输出匹配行的后 4 行 

```shell
    root@Linux-world:~# grep -A 4 "games" /etc/passwd
```
![][7]
* 使用 `-C` 参数输出匹配行的前后各 4 行 

```shell
    root@Linux-world:~# grep -C 4 "games" /etc/passwd
```
![][8]

]

### 例15 grep 搜索目录时，排除某些目录 

使用 grep 搜索目录时，会将一些隐藏目录也给搜进去，比如 `.git` 目录，如何在使用 grep 时排除这些目录 

使用 `--exclude-dir` 选项。

语法

```shell
    --exclude-dir=DIR
    Exclude directories matching the pattern DIR from recursive searches.
```
* 单个目录示例

```shell
    grep -E "http"  ./ -R --exclude-dir=.git
```
* 多个目录示例

```shell
    grep -E "http"  . -R --exclude-dir={.git,res,bin}
```
* 多个文件示例   
排除扩展名为 java 和 js 的文件 

```shell
    grep -E "http"  . -R --exclude=*.{java,js}
```
### 例16 使用 `-L` 参数列出包含指定模式的文件的文件名 

逆转输出，使用 `-L` 选项来输出那些不匹配的文件的文件名 

```shell
    grep -L "word" filename
    grep -L root /etc/*
```

[1]: http://www.bowu8.com/archives/358/

[4]: ../img/mMNNRvI.png
[5]: ../img/FnUVJnq.jpg
[6]: ../img/ERfuUvz.jpg
[7]: ../img/iemQ32m.jpg
[8]: ../img/bue63qF.jpg