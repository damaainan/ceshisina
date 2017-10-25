## [Linux命令行的艺术(1)-大纲](https://segmentfault.com/a/1190000011709542)

转载请注明出处 [http://www.paraller.com][0]  
原文排版地址 [点击获取更好阅读体验][1]

本文的内容主纲领是参照[jlevy/the-art-of-command-line][2],增加了使用说明和讲解,增加了一些常用的命令。作为一个glossary

### 日常使用

* 在 Bash 中，可以通过按 Tab 键实现自动补全参数，
* 使用 ctrl-r 搜索命令行历史记录（按下按键之后，输入关键字便可以搜索，重复按下 ctrl-r 会向后查找匹配项，按下 Enter 键会执行当前匹配的命令，而按下右方向键会将匹配项放入当前行中，不会直接执行，以便做出修改）。
* 在 Bash 中，可以按下 ctrl-w 删除你键入的最后一个单词，
* ctrl-u 可以删除行内光标所在位置之前的内容
* ctrl-k 可以删除光标至行尾的所有内容
* ctrl-a 可以将光标移至行首
* ctrl-e 可以将光标移至行尾
* ctrl-l 可以清屏。
* alt-. 循环地移向前一个参数
* cd 命令可以切换工作路径，输入 cd ~ 可以进入 home 目录。要访问你的 home 目录中的文件，可以使用前缀 ~（例如 ~/.bashrc）。在 sh 脚本里则用环境变量 $HOME 指代 home 目录的路径。
* 回到前一个工作路径：cd -。
* 如果你输入命令的时候中途改了主意，按下 alt-# 在行首添加 # 把它当做注释再按下回车执行。这样做的话，之后借助命令行历史记录，你可以很方便恢复你刚才输入到一半的命令。
* pstree -p 以一种优雅的方式展示进程树。
* 使用 pgrep 和 pkill 根据名字查找进程或发送信号（-f 参数通常有用）。
* 了解你可以发往进程的信号的种类。比如，使用 kill -STOP [pid] 停止一个进程。使用 man 7 signal 查看详细列表。 kill -9 强制结束
* 使用 nohup 或 disown 使一个后台进程持续运行。
* 使用 netstat -lntp 或 ss -plat 检查哪些进程在监听端口（默认是检查 TCP 端口; 添加参数 -u 则检查 UDP 端口）。
* lsof 来查看开启的套接字和文件。 lsof -i:port
* 使用 uptime 或 w 来查看系统已经运行多长时间。
* 使用 alias 来创建常用命令的快捷形式。例如：alias ll='ls -latr' 创建了一个新的命令别名 ll。 使用 type ll可以查看
* 可以把别名、shell 选项和常用函数保存在 ~/.bashrc，具体看下这篇[文章][3]。这样做的话你就可以在所有 shell 会话中使用你的设定。
* 把环境变量的设定以及登陆时要执行的命令保存在 ~/.bash_profile。而对于从图形界面启动的 shell 和 cron 启动的 shell，则需要单独配置文件。
* 要想在几台电脑中同步你的配置文件（例如 .bashrc 和 .bash_profile），可以借助 Git。
* 当变量和文件名中包含空格的时候要格外小心。Bash 变量要用引号括起来，比如 "$FOO"。尽量使用 -0 或 -print0 选项以便用 NULL 来分隔文件名，例如 locate -0 pattern | xargs -0 ls -al 或 find / -print0 -type d | xargs -0 ls -al。如果 for 循环中循环访问的文件名含有空字符（空格、tab 等字符），只需用 IFS=$'\n' 把内部字段分隔符设为换行符。

### 系统调试

* curl 和 curl -I 可以被轻松地应用于 web 调试中，它们的好兄弟 wget 也是如此，或者也可以试试更潮的 httpie。
* 获取 CPU 和硬盘的使用状态，通常使用使用 top（htop 更佳），iostat 和 iotop。而 iostat -mxz 15 可以让你获悉 CPU 和每个硬盘分区的基本信息和性能表现。
* 使用 netstat 和 ss 查看网络连接的细节。
* dstat 在你想要对系统的现状有一个粗略的认识时是非常有用的。然而若要对系统有一个深度的总体认识，使用 glances，它会在一个终端窗口中向你提供一些系统级的数据。
* 若要了解内存状态，运行并理解 free 和 vmstat 的输出。值得留意的是**cached**的值，它指的是 Linux内核用来作为文件缓存的内存大小，而与空闲内存无关。
* Java 系统调试则是一件截然不同的事，一个可以用于 Oracle 的 JVM 或其他 JVM 上的调试的技巧是你可以运行 kill -3 <pid>同时一个完整的栈轨迹和堆概述（包括 GC 的细节）会被保存到标准错误或是日志文件。JDK 中的 jps，jstat，jstack，jmap 很有用。SJK tools 更高级。
* 用 ncdu 来查看磁盘使用情况，它比寻常的命令，如 du -sh *，更节省时间。
* 查找正在使用带宽的套接字连接或进程，使用 iftop 或 nethogs。
* ab 工具（Apache 中自带）可以简单粗暴地检查 web 服务器的性能。对于更复杂的负载测试，使用 siege。 ab -c 10 -n 100 http://www.baidu.com/index.html
* 学会使用 /proc。它在调试正在出现的问题的时候有时会效果惊人。比如：/proc/cpuinfo，/proc/meminfo，/proc/cmdline，/proc/xxx/cwd，/proc/xxx/exe，/proc/xxx/fd/，/proc/xxx/smaps（这里的 xxx 表示进程的 id 或 pid）。
* 当调试一些之前出现的问题的时候，sar 非常有用。它展示了 cpu、内存以及网络等的历史数据。
* 关于更深层次的系统分析以及性能分析，看看 stap（SystemTap），perf，以及sysdig。
* 查看你当前使用的系统，使用 uname，uname -a（Unix／kernel 信息）或者 lsb_release -a（Linux 发行版信息）。
* 如果你删除了一个文件，但通过 du 发现没有释放预期的磁盘空间，请检查文件是否被进程占用：lsof | grep deleted | grep "filename-of-my-big-file"

### 单行脚本

* 使用 grep . *（每行都会附上文件名）或者 head -100 *（每个文件有一个标题）来阅读检查目录下所有文件的内容。这在检查一个充满配置文件的目录（如 /sys、/proc、/etc）时特别好用。
* 如果你想在文件树上查看大小/日期，这可能看起来像递归版的 ls -l 但比 ls -lR 更易于理解:find . -type f -ls
* 假设你有一个类似于 web 服务器日志文件的文本文件，并且一个确定的值只会出现在某些行上，假设一个 acct_id 参数在 URI 中。如果你想计算出每个 acct_id 值有多少次请求，使用如下代码：cat access.log | egrep -o 'acct_id=[0-9]+' | cut -d= -f2 | sort | uniq -c | sort -rn
* 要持续监测文件改动，可以使用 watch，例如检查某个文件夹中文件的改变，可以用 watch -d -n 2 'ls -rtlh | tail'；
* 或者在排查 WiFi 设置故障时要监测网络设置的更改，可以用 watch -d -n 2 ifconfig。

##### type / help / apropos / man 阅读文档获取帮助信息

* type 命令来判断这个命令到底是可执行文件、shell 内置命令还是别名; type passwd => passwd is /usr/bin/passwd , type ll => ll is aliased to "ls -al"
* 使用 help 和 help -d 命令获取帮助信息 ; touch --help
* apropos 去查找文档 apropos passwd
* man 命令去阅读文档 man ls

### 网络相关工具

##### ifconfig

**显示信息**

    eth1      Link encap:Ethernet  HWaddr 00:16:3e:00:56:6e
              inet addr:120.24.242.119  Bcast:120.24.243.255  Mask:255.255.252.0
              UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
              RX packets:1798839 errors:0 dropped:0 overruns:0 frame:0
              TX packets:1979705 errors:0 dropped:0 overruns:0 carrier:0
              collisions:0 txqueuelen:1000
              RX bytes:182577364 (182.5 MB)  TX bytes:1107427131 (1.1 GB)
    
    lo        Link encap:Local Loopback
              inet addr:127.0.0.1  Mask:255.0.0.0
              UP LOOPBACK RUNNING  MTU:65536  Metric:1
              RX packets:254273 errors:0 dropped:0 overruns:0 frame:0
              TX packets:254273 errors:0 dropped:0 overruns:0 carrier:0
              collisions:0 txqueuelen:0
              RX bytes:15769084 (15.7 MB)  TX bytes:15769084 (15.7 MB)

* eth0 表示第一块网卡， 其中 HWaddr 表示网卡的物理地址，可以看到目前这个网卡的物理地址(MAC地址）是 00:50:56:BF:26:20
* inet addr 用来表示网卡的IP地址，此网卡的 IP地址是 192.168.120.204，广播地址， Bcast:192.168.120.255，掩码地址Mask:255.255.255.0
* lo 是表示主机的回坏地址，这个一般是用来测试一个网络程序，但又不想让局域网或外网的用户能够查看，只能在此台主机上运行和查看所用的网络接口。比如把 HTTPD服务器的指定到回坏地址，在浏览器输入 127.0.0.1 就能看到你所架WEB网站了。但只是您能看得到，局域网的其它主机或用户无从知道。
* 第一行：连接类型：Ethernet（以太网）HWaddr（硬件mac地址）
* 第二行：网卡的IP地址、子网、掩码
* 第三行：UP（代表网卡开启状态）RUNNING（代表网卡的网线被接上）MULTICAST（支持组播）MTU:1500（最大传输单元）：1500字节
* 第四、五行：接收、发送数据包情况统计
* 第七行：接收、发送数据字节数统计信息。

**设置:** (ssh登陆linux服务器操作要小心，关闭了就不能开启了，除非你有多网卡)

    ## 启动关闭指定网卡
    ifconfig eth0 up
    ifconfig eth0 down
    
    ## 为网卡配置和删除IPv6地址
    ifconfig eth0 add 33ffe:3240:800:1005::2/64
    ifconfig eth0 del 33ffe:3240:800:1005::2/64
    
    ## 修改MAC地址
    ifconfig eth0 hw ether 00:AA:BB:CC:DD:EE

##### dig

查看DNS信息:dig .用google-DNS来查baidu.com的A记录

    # dig @dnsserver name querytype
    dig @8.8.8.8 www.baidu.com A

##### nc

测试某网址或IP端口能否访问

    nc -v -w 1   120.76.77.58 -z 9761

##### 修改DNS

    vim /etc/resolv.conf
    
    Hong Kong (HKG)    
    nameserver 120.136.32.62 
    nameserver 120.136.32.63
    
    Northern Virginia (IAD)    
    nameserver 69.20.0.164 
    nameserver 69.20.0.196
    
    London (LON)    
    nameserver 83.138.151.80 
    nameserver 83.138.151.81
    
    Chicago (ORD)    
    nameserver 173.203.4.8 
    nameserver 173.203.4.9
    
    Dallas/Fort Worth (DFW)    
    nameserver 72.3.128.240 
    nameserver 72.3.128.241.
    
    Sydney (SYD)    
    nameserver 119.9.60.62 
    nameserver 119.9.60.63.

##### curl

**GET**

    curl -G url

**POST**

    
    curl -X POST -d
    curl -X POST --data "param1=value1¶m2=value2" 
    curl --request POST https://example.com/resource.cgi
    curl -H "Content-Type: application/json" -X POST -d '{"username":"xyz","password":"xyz"}' http://localhost:3000/api/login
    

**PUT**

    curl -X PUT -d arg=val -d arg2=val2 localhost:8080

**DELETE**

    curl -X "DELETE" http://www.url.com/page
    

**其他参数**

* curl --form "fileupload=@my-file.txt" [https://example.com/resource.cgi][4]
* curl --data "param1=value1¶m2=value2" [https://example.com/resource.cgi][4]
* -F 上传文件 curl -F upload=@localfilename -F press=OK URL
* curl -i 输出时包括protocol头信息
* 认证 curl -u name:password www.secrets. com
* -v 输出时打印详细信息
* -# 用进度条显示当前的传送状态
* O 保留文件名

##### Bash 中的任务管理工具：

& | 后台运行   
ctrl-z | 将当前进程挂起，可搭配bg使用，让任务后台运行  
ctrl-d | 表示一个特殊的二进制值，表示 EOF，如在输入无法结束，提示 ">" 符号（大于号）时，按下该组合来结束输入  
ctrl-c | 结束  
jobs | 查看当前有多少在后台运行的命令  
fg | 返回后台进程到前台运行  
bg | 唤醒任务缩小运行  
kill | 结束进程 -9强制

##### ssh-copy-id 添加公钥到远程服务器

    ssh-copy-id -i ~/.ssh/id_rsa.pub remote-host

无密码登录 vim config

    Host g
        IdentityFile ~/.ssh/gitlab
        Hostname gitlab.umiit.cn
        Port 2222
        User git

##### grep

正则表达式的学习参考这篇文章：[正则表达式快速入门][5]

grep的一些参数：

* o 只打印匹配的文本
* E 使用正则
* i或--ignore-case 忽略字符大小写的差别。
* v或--revert-match 反转查找。
* A<显示列数>或--after-context=<显示列数> 显示该列之后的内容。
* B<显示列数>或--before-context=<显示列数> 显示该列之前的内容。
* C<显示列数>或--context=<显示列数>或-<显示列数> 显示该列之前后的内容。
* r 循环遍历文件夹下的所有文件内容
```
    ## 并且只打印出文件名 
    find ./ | xargs grep -ri 'text' -l
```
##### date

系统时间和硬件时间

    hwclock --set --date="8/10/17 18:16" 
    
    date -s "2017-08-10 18:19:00"

##### history

使用:在输入命令前 Crtl/control + R ，可以打开历史命令提示

**执行历史命令:**

再用 !n（n 是命令编号）就可以再次执行; 

**history命令的记录如何删除？**

    vim /etc/profile
    HISTSIZE=1   // 或者0

    echo '' > ~/.bash_history

**立即清空里的history当前历史命令的记录:**

    history -c  

**立即更新历史命令:**

bash执行命令时不是马上把命令名称写入history文件的，而是存放在内部的buffer中，等bash退出时会一并写入。   
不过，可以调用'history -w'命令要求bash立即更新history文件。

    history -w

##### disown

* 用disown -h jobspec来使某个作业忽略HUP信号。
* 用disown -ah 来使所有的作业都忽略HUP信号。

当使用过 disown 之后，会将把目标作业从作业列表中移除，我们将不能再使用jobs来查看它，但是依然能够用ps -ef查找到它。

    [root@pvcent107 build]# cp -r testLargeFile largeFile &
    [1] 4825
    [root@pvcent107 build]# jobs
    [1]+  Running                 cp -i -r testLargeFile largeFile &
    [root@pvcent107 build]# disown -h %1
    [root@pvcent107 build]# ps -ef |grep largeFile
    root      4825   968  1 09:46 pts/4    00:00:00 cp -i -r testLargeFile largeFile
    root      4853   968  0 09:46 pts/4    00:00:00 grep largeFile

##### xargs

* xargs命令是**给其他命令传递参数的一个过滤器**，也是组合多个命令的一个工具。它擅长将**标准输入数据转换成命令行参数**
* xargs能够处理管道或者stdin并将其转换成特定命令的命令参数。
* xargs也可以将单行或多行文本输入转换为其他格式，例如多行变单行，单行变多行。
* xargs的默认命令是echo，空格是默认定界符。这意味着通过管道传递给xargs的输入将会包含换行和空白，不过通过xargs的处理，换行和空白将被空格取代

**简单输出：**  
xargs命令从标准输入获得输入(默认),然后对得到的输入执行/bin/echo命令; 输入文本后 Crtl-d

    root@iZwz93msbqzlxk30oxj7s1Z:~# xargs
    hello wolrd
    hello wolrd

**多行输入单行输出：**

    cat a.md | xargs

**多行输出，指定个数单词为一行:**

    cat test.txt | xargs -n3 
    a b c 
    d e f

**指定限定符 -d:**

    echo 'hello-world' | xargs -d-
    hello world

**使用-I指定一个替换字符串{}:**

{}这个字符串在xargs扩展时会被替换掉，当-I与xargs结合使用，每一个参数命令都会被执行一次

复制所有图片文件到 /data/images 目录下：

    ls *.jpg | xargs -n1 -I cp {} /data/images

##### 小知识点

* cal：漂亮的日历
* env：执行一个命令（脚本文件中很有用）
* printenv：打印环境变量（调试时或在写脚本文件时很有用）
* look：查找以特定字符串开头的单词或行
* fmt：格式化文本段落
* pr：将文本格式化成页／列形式
* fold：指定一行显示几个单词 fold a.md -10
* column：将文本格式化成多个对齐、定宽的列或表格
* expand 和 unexpand：制表符与空格之间转换
* nl：添加行号
* seq：打印数字
* bc：计算器
* factor：分解因数
* gpg：加密并签名文件
* nc：网络调试及数据传输
* socat：套接字代理，与 netcat 类似
* slurm：网络流量可视化
* dd：文件或设备间传输数据 dd if=a.md of=b.md bs=1024
* file：确定文件类型
* tree：以树的形式显示路径和文件，类似于递归的 ls
* stat：文件信息
* time：执行命令，并计算执行时间
* lockfile：使文件只能通过 rm -f 移除
* watch：重复运行同一个命令，展示结果并／或高亮有更改的部分
* tac：反向输出文件
* shuf：文件中随机选取几行
* comm：一行一行的比较排序过的文件
* iconv 或 uconv：文本编码转换
* split 和 csplit：分割文件
* sponge：在写入前读取所有输入，在读取文件后再向同一文件写入时比较有用，例如 grep -v something some-file | sponge some-file
* units：将一种计量单位转换为另一种等效的计量单位（参阅 /usr/share/units/definitions.units）
* apg：随机生成密码
* xz：高比例的文件压缩
* ldd：动态库信息
* ab：web 服务器性能分析
* strace：调试系统调用
* mtr：更好的网络调试跟踪工具
* rsync：通过 ssh 或本地文件系统同步文件和文件夹
* wireshark 和 tshark：抓包和网络调试工具
* lsof：列出当前系统打开文件的工具以及查看端口信息
* dstat：系统状态查看
* iostat：硬盘使用状态
* mpstat： CPU 使用状态
* vmstat： 内存使用状态
* htop：top 的加强版
* last：登入记录
* w：查看处于登录状态的用户
* id：用户/组 ID 信息
* sar：系统历史数据
* iftop 或 nethogs：套接字及进程的网络利用情况
* ss：套接字数据
* dmesg：引导及系统错误信息
* l 和 dmidecode：查看硬件信息，包括 CPU、BIOS、RAID、显卡、USB设备等
* lsmod 和 modinfo：列出内核模块，并显l示其细节

## 参考网站

[DNS][6]

[技巧：Linux I/O重定向的一些小技巧][7]

[jlevy/the-art-of-command-line][2]

[理解inode & 链接 & rwx权限][8]

[ifconfig][9]

[《dig挖出DNS的秘密》-linux命令五分钟系列之三十四][10]

[xargs命令][11]

[Linux 技巧：让进程在后台可靠运行的几种方法][12]

[jq][13]

[AWK 简明教程][14]

[sed 简明教程][15]

[0]: http://www.paraller.com
[1]: http://www.paraller.com/2016/05/22/Linux%E5%91%BD%E4%BB%A4%E8%A1%8C%E7%9A%84%E8%89%BA%E6%9C%AF%281%29-%E5%A4%A7%E7%BA%B2/
[2]: https://github.com/jlevy/the-art-of-command-line/blob/master/README-zh.md
[3]: https://superuser.com/questions/183870/difference-between-bashrc-and-bash-profile/183980#183980
[4]: https://example.com/resource.cgi
[5]: http://www.paraller.com/2016/05/22/
[6]: https://support.rackspace.com/how-to/changing-dns-settings-on-linux/
[7]: https://www.ibm.com/developerworks/cn/linux/l-iotips/index.html
[8]: http://www.ruanyifeng.com/blog/2011/12/inode.html
[9]: http://www.cnblogs.com/peida/archive/2013/02/27/2934525.html
[10]: http://roclinux.cn/?p=2449
[11]: http://man.linuxde.net/xargs
[12]: https://www.ibm.com/developerworks/cn/linux/l-cn-nohup/index.html
[13]: https://stedolan.github.io/jq/tutorial/
[14]: https://coolshell.cn/articles/9070.html
[15]: https://coolshell.cn/articles/9104.html