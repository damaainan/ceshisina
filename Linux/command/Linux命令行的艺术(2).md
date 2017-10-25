# Linux命令行的艺术(2)-文件及数据处理

 Posted by zhida on May 22, 2016

### 文件及数据处理

* ls 和 ls -l （了解 ls -l 中每一列代表的意义
* head: 是显示一个文件的内容的前多少行. `head -n 10 /etc/profile`
* tail 和 `tail -f` ：从末尾显示文件内容
* ln 和 ln -s （了解硬链接与软链接的区别）
* chown，chmod
* du （硬盘使用情况概述：`du -sh *`）。
* 关于文件系统的管理，学习 df，mount，fdisk，mkfs，lsblk。参照[阿里云Ecs挂载云盘][0]
* 在当前目录下通过文件名查找一个文件，使用类似于这样的命令：`find . -name '*something*'`。
* 在所有路径下通过文件名查找文件，使用 `locate something` （但注意到 updatedb可能没有对最近新建的文件建立索引，所以你可能无法定位到这些未被索引的文件）。
* Markdown，HTML，以及所有文档格式之间的转换，试试 [pandoc][1]。
* 使用 shyaml 处理 YAML。
* 要处理 Excel 或 CSV 文件的话，csvkit 提供了 in2csv，csvcut，csvjoin，csvgrep 等方便易用的工具。
* 了解如何运用 wc 去计算新`行数（-l）`，`字符数（-m）`，`单词数（-w）`以及`字节数（-c）`。
* 要进行一些复杂的计算，比如分组、逆序和一些其他的统计分析，可以考虑使用 datamash。
* 你可以单独指定某一条命令的环境，只需在调用时把环境变量设定放在命令的前面，例如 TZ=Pacific/Fiji date 可以获取斐济的时间。
* 如果你想在 Bash 命令行中写 tab 制表符，键入 '\t'
* 标准的源代码对比及合并工具是 diff 和 patch。使用 diffstat 查看变更总览数据。注意到 `diff -r` 对整个文件夹有效。使用 diff -r tree1 tree2 | diffstat 查看变更的统计数据。vimdiff 用于比对并编辑文件
* 使用 iconv 更改文本编码。需要更高级的功能，可以使用 uconviconv -f GB2312 -t UTF-8 gb1.txt >gb2.txt
* split命令可以将一个大文件分割成很多个小文件.默认是带有字母的后缀文件，如果想用数字后缀可使用-d参数，同时可以使用-a length来指定后缀的长度:split -b 10k date.file -d -a 3
* 操作日期和时间表达式，可以用 dateutils 中的 dateadd、datediff、strptime 等工具。
* 使用 zless、zmore、zcat 和 zgrep 对压缩过的文件进行操作
* 文件属性可以通过chattr 进行设置，它比文件权限更加底层。例如，为了保护文件不被意外删除，可以使用不可修改标记：sudo chattr +i /critical/directory/or/file
* 注意到语言设置（中文或英文等）对许多命令行工具有一些微妙的影响，比如排序的顺序和性能。大多数 Linux 的安装过程会将 LANG 或其他有关的变量设置为符合本地的设置。要意识到当你改变语言设置时，排序的结果可能会改变。明白国际化可能会使 sort 或其他命令运行效率下降许多倍。某些情况下（例如集合运算）你可以放心的使用 export LC_ALL=C 来忽略掉国际化并按照字节来判断顺序。

##### > 输出

将who的输出写入文件中

    who > users.txt
    

##### `<` 输入

    [root@10.170.48.177 ~  testServer ]$  wc -l who.txt
    2 who.txt
    [root@10.170.48.177 ~  testServer ]$  wc -l < who.txt
    2
    

##### stdin stdout stderr

在Linux系统中，系统为每一个打开的文件指定一个文件标识符以便系统对文件进行跟踪。系统占用了3个，分别是0标准输入（stdin）,1标准输出(stdout), 2标准错误(stderr)；

“&”在这里代表标准输出和标准错误，这里无论是正常输出还是错误信息都写到filename中了。

    #ls /dev &>filename

##### inode

文件储存在硬盘上，硬盘的最小存储单位叫做”扇区”（Sector）。每个扇区储存512字节（相当于0.5KB）。

操作系统读取硬盘的时候，不会一个个扇区地读取，这样效率太低，而是一次性连续读取多个扇区，即一次性读取一个”块”（block）。这种由多个扇区组成的”块”，是文件存取的最小单位。”块”的大小，最常见的是4KB，即连续八个 sector组成一个 block。

文件数据都储存在”块”中，那么很显然，我们还必须找到一个地方储存文件的元信息，比如文件的创建者、文件的创建日期、文件的大小等等。这种储存文件元信息的区域就叫做inode，中文译名为”索引节点”。

可以用stat命令，查看某个文件的inode信息：

    ➜  /etc stat locate.rc
    16777220 405905 -rw-r--r-- 1 root wheel 0 616 "Sep 23 03:28:40 2017" "Aug  2 11:07:28 2015" "Jul  9 12:27:51 2016" "Aug  2 11:07:28 2015" 4096 0 0x20 locate.rc
    

查看每个硬盘分区的inode总数和已经使用的数量，可以使用df命令。 df -ils -i命令列出整个目录文件，即文件名和inode号码：

如果要查看文件的详细信息，就必须根据inode号码，访问inode节点，读取信息。ls -l命令列出文件的详细信息。

理解了上面这些知识，就能理解目录的权限。目录文件的读权限（r）和写权限（w），都是针对目录文件本身。由于目录文件内只有文件名和inode号码，所以如果只有读权限，只能获取文件名，无法获取其他信息，因为其他信息都储存在inode节点中，而读取inode节点内的信息需要目录文件的执行权限（x）。

由于inode号码与文件名分离，这种机制导致了一些Unix/Linux系统特有的现象。

* 有时，文件名包含特殊字符，无法正常删除。这时，直接删除inode节点，就能起到删除文件的作用。
* 移动文件或重命名文件，只是改变文件名，不影响inode号码。
* 打开一个文件以后，系统就以inode号码来识别这个文件，不再考虑文件名。因此，通常来说，系统无法从inode号码得知文件名。

第3点使得软件更新变得简单，可以在不关闭软件的情况下进行更新，不需要重启。因为系统通过inode号码，识别运行中的文件，不通过文件名。更新的时候，新版文件以同样的文件名，生成一个新的inode，不会影响到运行中的文件。等到下一次运行这个软件的时候，文件名就自动指向新版文件，旧版文件的inode则被回收。

##### ln 硬连接 / ln -s 软连接:

**软连接**

* 软链接有自己的文件属性及权限等；
* 可对不存在的文件或目录创建软链接；
* 软链接可交叉文件系统；
* 软链接可对文件或目录创建；
* 删除软链接并不影响被指向的文件，但若被指向的原文件被删除，则相关软连接被称为死链接

**硬连接:**

* 文件有相同的inode 及 data block；
* 只能对已存在的文件进行创建；
* 不能**_对目录进行创建_**，只可对文件创建
* 删除一个硬链接文件并不影响其他有相同 inode 号的文件。

##### df du

查看硬盘的容量

查看硬盘的概况

    df -h
    

查看当前目录以下搜索文件和子目录大小

    du -sh *
    

查看指定目录的大小：

    du -sh xxx
    

##### less

* -c 从顶部（从上到下）刷新屏幕，并显示文件内容。而不是通过底部滚动完成刷新；
* -f 强制打开文件，二进制文件显示时，不提示警告；
* -i 搜索时忽略大小写；除非搜索串中包含大写字母；
* -I 搜索时忽略大小写，除非搜索串中包含小写字母；
* -m 显示读取文件的百分比；
* -M 显法读取文件的百分比、行号及总行数；
* -N 在每行前输出行号；
* -p pattern 搜索pattern；比如在/etc/profile搜索单词MAIL，就用 less -p MAIL /etc/profile
* -s 把连续多个空白行作为一个空白行显示；
* -Q 在终端下不响铃；

比如：我们在显示/etc/profile的内容时，让其显示行号；[ ~]# `less -N /etc/profile`动作: 进入less之后的操作

* 回车键 向下移动一行；
* y 向上移动一行；
* 空格键 向下滚动一屏；
* b 向上滚动一屏；
* d 向下滚动半屏；
* h less的帮助；
* u 向上洋动半屏；
* w 可以指定显示哪行开始显示，是从指定数字的下一行显示；比如指定的是6，那就从第7行显示；
* g 跳到第一行；
* G 跳到最后一行；
* p n% 跳到n%，比如 10%，也就是说比整个文件内容的10%处开始显示；
* /pattern 搜索pattern ，比如 /MAIL表示在文件中搜索MAIL单词；
* v 调用vi编辑器；
* q 退出less

##### vim

vim的常用操作命令

:q 退出 :q! 不保存退出 :wq 保存退出 yy p 复制 G 文末 :set number 显示行号 / 查找 

vim乱码处理:

    vim ~/.vimrc
    
    set fileencodings=utf-8,ucs-bom,gb18030,gbk,gb2312,cp936
    set termencoding=utf-8
    set encoding=utf-8
    

##### tee

将标准输入复制到文件甚至标准输出，例如ls -al | tee file.txt; tee - read from standard input and write to standard output and files

##### scp跨主机传输文件

    scp -P 2222 source  root@192.168.1.250:/home  
    

##### rsync

rsync是一个快速且非常灵活的文件复制工具。它闻名于设备之间的文件同步，但其实它在本地情况下也同样有用。在安全设置允许下，用 rsync 代替 scp 可以实现文件续传，而不用重新从头开始。它同时也是删除大量文件的最快方法之一：

    ## --delete  delete extraneous files from dest dirs
    rsync -r --delete empty/ some-dir/
    

##### getfacl && setfacl

使用 getfacl 和 setfacl 以保存和恢复文件权限。例如：

    getfacl -R /some/path > permissions.txt
    setfacl --restore=permissions.txt
    

##### tar

**简单的打包命令**：

    ## - j :使用bzip2进行解压缩
    tar -jcv -f filename.tar.bz2 source
    

**解压适用格式类型：**

    -zxvf ：tar.gz
    -xvf  ：.tar
    -xjvf ：.bz2
    -xZvf : file.tar.Z
    

##### cut & join & paste

**cut 命令参数：**

* 使用-f提取指定字段 `cut -f 2,3 a.md`
* –complement 指定字段之外的。`cut -f 2,3 --complement a.md`
* -d 指定分隔符 `cut -f 2 -d ";" a.md`
* 打印1到3的字符 `cut -c 1-3 a.md`
* 打印前2个字符 `cut -c -2 a.md`
* 打印从第5个到结尾 `cut -c 5- a.md`

**paste**命令用于将多个文件按照列队列进行合并

    paste a.md c.md 
    ## 输出两个文件的文本内容
    

**join**指定栏位内容相同的行连接起来。

    ## cat a.md
    hello   world
    main  land
    large Size
    
    
    ## cat b.md
    hello china
    big apple
    hello   buy
    hello u
    
    ## join a.md b.md 
    hello world china
    

* a<1或者2> 左连接或者右连接
* v<1或者2> 和a参数相同 但是显示没有相同栏位的行

##### uniq & sort

**uniq**命令用于报告或忽略文件中的(相邻)重复行，一般与sort命令结合使用

* c或——count：在每列旁边显示该行重复出现的次数；
* d或–repeated：仅显示重复出现的行列；
* u或——unique：仅显示出一次的行列；

**sort**它将文件进行排序，并将排序结果标准输出。sort命令既可以从特定的文件，也可以从stdin中获取输入。

* b：忽略每行前面开始出的空格字符；
* d：排序时，处理英文字母、数字及空格字符外，忽略其他的字符；
* f：排序时，将小写字母视为大写字母；
* n：依照数值的大小排序；
* `o<输出文件>`：将排序后的结果存入制定的文件；
* r：以相反的顺序来排序；

```
    sort a.md | uniq 
```

##### ag

    ag 'text' filename
    

使用 ag 在源代码或数据文件里检索（`grep -r` 同样可以做到，但相比之下 `ag` 更加先进）。需要下载第三方库

当你要处理棘手的 XML 时候，xmlstarlet 算是上古时代流传下来的神器。

##### 使用 jq 处理 JSON。

源文件

    [
      {
        commit:{
          message:"Merge pull request #162 from stedolan/utf8-fixes\n\nUtf8 fixes. Closes #161",
          committer:{
            name:Github
          }
          ...
        }
        ....
      }
    ]
    

    curl 'https://api.github.com/repos/stedolan/jq/commits?per_page=5' | jq .[]
    
    ## result
    {
        commit:{
          message:"Merge pull request #162 from stedolan/utf8-fixes\n\nUtf8 fixes. Closes #161",
          committer:{
            name:Github
          }
          ...
        }
        ....
      }
    
    

    jq '.[0] | {message: .commit.message, name: .commit.committer.name}'
    
    ## result
    {
      "message": "Merge pull request #162 from stedolan/utf8-fixes\n\nUtf8 fixes. Closes #161",
      "name": "Stephen Dolan"
    }
    

### awk

##### 定义

awk脚本是由**模式**和**操作**组成的。

**模式**可以是以下任意一个：

* /正则表达式/：使用通配符的扩展集。
* 关系表达式：使用运算符进行操作，可以是字符串或数字的比较测试。
* 模式匹配表达式：用运算符~（匹配）和~!（不匹配）。
* BEGIN语句块、pattern语句块、END语句块：参见awk的工作原理

**操作**由一个或多个命令、函数、表达式组成，之间由换行符或分号隔开，并位于大括号内，主要部分是：

* 变量或数组赋值
* 输出命令
* 内置函数
* 控制流语句

awk脚本基本结构：awk 'BEGIN{ print "start" } pattern{ commands } END{ print "end" }' file* 一个awk脚本通常由：BEGIN语句块、能够使用模式匹配的通用语句块、END语句块3部分组成，这三个部分是**可选**
* END 代表将所有行的执行结果最后输出
* awk的脚本必须在**单引号或双引号**中
* /正则表达式/：使用通配符的扩展集

##### 具体场景用法示例

示例文本 netstat | tee a.md

    Active Internet connections (w/o servers)
    Proto Recv-Q Send-Q Local Address           Foreign Address         State
    tcp        0      0 localhost:32000         localhost:31001         ESTABLISHED
    tcp      401      0 119.23.130.150:35700    140.205.140.205:http    CLOSE_WAIT
    tcp        0      0 119.23.130.150:ssh      219.134.180.194:28602   ESTABLISHED
    Active UNIX domain sockets (w/o servers)
    Proto RefCnt Flags       Type       State         I-Node   Path
    unix  2      [ ]         DGRAM                    55137275 /run/user/0/systemd/notify
    unix  2      [ ]         DGRAM                    47568241 /run/user/1008/systemd/notify
    unix  2      [ ]         DGRAM                    9511     /run/systemd/journal/syslog
    unix  3      [ ]         STREAM     CONNECTED     12898
    unix  2      [ ]         DGRAM                    55137253
    unix  3      [ ]         STREAM     CONNECTED     13689    /var/run/dbus/system_bus_socket
    

**输出指定列**

    [root]$  awk '{print $1 "==>" $2}' a.md
    Active==>Internet
    Proto==>Recv-Q
    tcp==>0
    tcp==>401
    Active==>UNIX
    Proto==>RefCnt
    unix==>2
    unix==>2
    unix==>2
    unix==>3
    unix==>2
    unix==>3
    

**关系表达式：过滤记录**

其中的==为比较运算符。其他比较运算符：!=, >, <, >=, <= ,可以搭配表达式使用

    [root]$  cat a.md | awk '$1=="tcp"'
    tcp        0      0 localhost:32000         localhost:31001         ESTABLISHED
    tcp      401      0 119.23.130.150:35700    140.205.140.205:http    CLOSE_WAIT
    
    [root]$  cat a.md | awk '$2 > 2'
    Active Internet connections (w/o servers)
    Proto Recv-Q Send-Q Local Address           Foreign Address         State
    tcp      401      0 119.23.130.150:35700    140.205.140.205:http    CLOSE_WAIT
    Active UNIX domain sockets (w/o servers)
    Proto RefCnt Flags       Type       State         I-Node   Path
    unix  3      [ ]         STREAM     CONNECTED     12898
    unix  3      [ ]         STREAM     CONNECTED     13689    /var/run/dbus/system_bus_socket
    
    
    [root]$ cat a.md | awk '$2 > 2 || NR==1 {printf "%-20s %-20s %s\n",$1,$2,$3}'
    Active               Internet             connections
    Proto                Recv-Q               Send-Q
    tcp                  401                  0
    Active               UNIX                 domain
    Proto                RefCnt               Flags
    unix                 3                    [
    unix                 3                    [
    

**内置函数**

    [root]$  awk '$2 > 2 || NR==1 {printf "%-20s %-20s %-20s %s\n",$1,$2,$3,FILENAME} ' a.md
    Active               Internet             connections          a.md
    Proto                Recv-Q               Send-Q               a.md
    tcp                  401                  0                    a.md
    Active               UNIX                 domain               a.md
    Proto                RefCnt               Flags                a.md
    unix                 3                    [                    a.md
    unix                 3                    [                    a.md
    
    
    [root ]$  awk 'BEGIN{FS=":"} {print $1,$2,$3}' /etc/passwd
    root x 0
    daemon x 1
    bin x 2
    sys x 3
    sync x 4
    
    ## 和上文等价
    [root ]$  awk  -F: '{print $1,$3,$6}' /etc/passwd
    root 0 /root
    daemon 1 /usr/sbin
    bin 2 /bin
    sys 3 /dev
    sync 4 /bin
    
    ## 增加制表符输出
    [root@10.27.170.181 common  ifex-test ]$  awk  -F: '{print $1,$3,$6}' OFS="\t" /etc/passwd
    root    0   /root
    daemon  1   /usr/sbin
    bin   2   /bin
    sys   3   /dev
    
    

**字符串匹配**

~ 表示模式开始。/ /中是模式。这就是一个正则表达式的匹配。

    [root]$  awk '$2 ~/2/' a.md
    unix  2      [ ]         DGRAM                    55137275 /run/user/0/systemd/notify
    unix  2      [ ]         DGRAM                    47568241 /run/user/1008/systemd/notify
    unix  2      [ ]         DGRAM                    9511     /run/systemd/journal/syslog
    unix  2      [ ]         DGRAM                    55137253
    

**取反**

    [root@10.27.170.181 common  ifex-test ]$  awk '!/unix/' a.md
    Active Internet connections (w/o servers)
    Proto Recv-Q Send-Q Local Address           Foreign Address         State
    tcp        0      0 localhost:32000         localhost:31001         ESTABLISHED
    tcp      401      0 119.23.130.150:35700    140.205.140.205:http    CLOSE_WAIT
    Active UNIX domain sockets (w/o servers)
    Proto RefCnt Flags       Type       State         I-Node   Path
    

**拆分**

    ## NR!=1表示不处理表头
    [root@10.27.170.181 common  ifex-test ]$   awk 'NR!=1{print > $1}' a.md
    [root@10.27.170.181 common  ifex-test ]$  ls
    Active  Proto  a.md  tcp  unix  
    
    [root@10.27.170.181 common  ifex-test ]$  cat tcp
    tcp        0      0 localhost:32000         localhost:31001         ESTABLISHED
    tcp      401      0 119.23.130.150:35700    140.205.140.205:http    CLOSE_WAIT
    
    
    ## 复杂的脚本解释器
    
    $ awk 'NR!=1{if($6 ~ /TIME|ESTABLISHED/) print > "1.txt";
      else if($6 ~ /LISTEN/) print > "2.txt";
      else print > "3.txt" }' netstat.txt
    

**统计**

    [root]$ ls -l *.sh | awk '{sum+=$5} END {print sum}'
    
    45
    
    
    ## 指定脚本执行
    
    [root@10.27.170.181 common  ifex-test ]$  cat sum.md
    Marry   2143 78 84 77
    Jack    2321 66 78 45
    Tom     2122 48 77 71
    Mike    2537 87 97 95
    Bob     2415 40 57 62
    
    
    [root@10.27.170.181 common  ifex-test ]$  cat awk.txt
    
    BEGIN {
        math = 0
        english = 0
        computer = 0
    
        printf "NAME    NO.   MATH  ENGLISH  COMPUTER   TOTAL\n"
        printf "---------------------------------------------\n"
    }
    
    {
        math+=$3
        english+=$4
        computer+=$5
        printf "%-6s %-6s %4d %8d %8d %8d\n", $1, $2, $3,$4,$5, $3+$4+$5
    }
    
    END {
        printf "---------------------------------------------\n"
        printf "  TOTAL:%10d %8d %8d \n", math, english, computer
        printf "AVERAGE:%10.2f %8.2f %8.2f\n", math/NR, english/NR, computer/NR
    }
    
    
    [root@10.27.170.181 common  ifex-test ]$  awk -f awk.txt  sum.md
    NAME    NO.   MATH  ENGLISH  COMPUTER   TOTAL
    ---------------------------------------------
    Marry  2143     78       84       77      239
    Jack   2321     66       78       45      189
    Tom    2122     48       77       71      196
    Mike   2537     87       97       95      279
    Bob    2415     40       57       62      159
    ---------------------------------------------
      TOTAL:       319      393      350
    AVERAGE:     63.80    78.60    70.00
    

##### sed

* 它是文本处理中非常中的工具，能够完美的配合**正则表达式**使用
* 处理时，把当前处理的行存储在临时缓冲区中，称为“模式空间”（pattern space），接着用sed命令处理缓冲区中的内容，处理完成后，把缓冲区的内容送往屏幕。接着处理下一行，这样不断重复，直到文件末尾。
* 文件内容并没有 改变，除非你使用重定向存储输出
* 格式： s/source/repalce/g

基本使用

    [root@10.27.170.181 ~  ifex-test ]$  echo hehehehe | sed s/he/ha/g
    hahahaha
    [root@10.27.170.181 ~  ifex-test ]$  echo hehehehe | sed s/he/ha/2g
    hehahaha
    

正则表达式

    [root]$  echo 'hello world' | awk '{printf "%-20s\n%-20s\n",$1,$2}' | sed s/^/linux-/g
    linux-hello
    linux-world
    
    [root]$  echo 'hello world' | awk '{printf "%-20s\n%-20s\n",$1,$2}' | sed s/$/linux-/g
    hello               linux-
    world               linux-
    
    [root]$  echo 'hello1 world' | awk '{printf "%-20s\n%-20s\n",$1,$2}' | sed s/[0-9]/-china/g
    hello-china
    world
    

每行的第1个

    
    [root]$  echo 'hello hello world world' | awk '{printf "%-20s%-20s\n%-20s%-20s\n",$1,$2,$3,$4}' | sed s/o/O/1
    hellO               hello
    wOrld               world
    

第一行到第三行的my替换成your，第二个则把第3行以后的This替换成了That

    $ sed '1,3s/my/your/g; 3,$s/This/That/g' my.txt
    

N命令：把下一行的内容纳入当成缓冲区做匹配。

    ## 把原文本中的偶数行纳入奇数行匹配，而s只匹配并替换一次，所以，就成了下面的结果：
    
    $ sed 'N;s/my/your/' pets.txt
    This is your cat
    my cat's name is betty
    This is your dog
    my dog's name is frank
    

a命令就是append， i命令就是insert，它们是用来添加行的。

    $  echo 'hello hello world world' | awk '{printf "%-20s%-20s\n%-20s%-20s\n",$1,$2,$3,$4}' | sed "1 i start----"
    start----
    hello               hello
    world               world
    
    $  echo 'hello hi world world' | awk '{printf "%-20s%-20s\n%-20s%-20s\n",$1,$2,$3,$4}' | sed "/hi/ a add hi line"
    hello               hi
    add hi line
    world               world
    

c 命令是替换匹配行

    $  echo 'hello hi world world' | awk '{printf "%-20s%-20s\n%-20s%-20s\n",$1,$2,$3,$4}' | sed "2 c replace line 2"
    hello               hi
    replace line 2
    

d 删除匹配行

    $  echo 'hello hi world world' | awk '{printf "%-20s%-20s\n%-20s%-20s\n",$1,$2,$3,$4}' | sed "2 d"
    hello               hi
    $  echo 'hello hi world world' | awk '{printf "%-20s%-20s\n%-20s%-20s\n",$1,$2,$3,$4}' | sed "/hi/ d"
    world               world

[0]: http://www.paraller.com/2016/10/22/阿里云Ecs挂载云盘/
[1]: http://pandoc.org/