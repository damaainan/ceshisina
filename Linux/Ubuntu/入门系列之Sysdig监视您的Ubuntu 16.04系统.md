## 入门系列之Sysdig监视您的Ubuntu 16.04系统

来源：[https://zhuanlan.zhihu.com/p/41107425](https://zhuanlan.zhihu.com/p/41107425)

时间 2018-08-02 16:22:47

 
![][0]
 
 **欢迎大家前往腾讯云+社区，获取更多腾讯海量技术实践干货哦~** 
 
本文由乌鸦 发表于云+社区专栏
 
##  **介绍**  
 
Sysdig是一个全面的开源系统活动监控，捕获和分析应用程序。它具有强大的过滤语言和可自定义的输出，以及可以使用称为 `chisels` 的Lua脚本扩展的核心功能。
 
应用程序通过访问内核来工作， 内核允许它查看每个系统调用以及通过内核传递的所有信息。这也使其成为监视和分析系统上运行的应用程序容器生成的系统活动和事件的出色工具。
 
核心Sysdig应用程序监视其安装的服务器。但是，该项目背后的公司提供了一个名为Sysdig Cloud的托管版本，可以远程监控任意数量的服务器。
 
独立应用程序可在大多数Linux发行版上使用，但在Windows和macOS上也可用，功能更为有限。除了`sysdig`命令行工具，Sysdig还带有一个`csysdig`带有类似选项的交互式UI 。
 
在本教程中，您将安装并使用Sysdig来监视Ubuntu 16.04服务器。您将流式传输实时事件，将事件保存到文件，过滤结果以及浏览`csysdig`交互式UI。
 
##  **准备**  
 
要完成本教程，您需要：

 
* 一台Ubuntu 16.04 的服务器，已经设置好一个可以使用`sudo`命令的非`root`的账户。  
 
 
##  **第1步 - 使用官方脚本安装Sysdig**  
 
在Ubuntu存储库中有一个Sysdig包，但它通常是当前版本的一两个版本。例如，在发布时，使用Ubuntu的软件包管理器安装Sysdig将为您提供Sysdig 0.8.0。但是，您可以使用项目开发页面中的官方脚本来安装它，这是推荐的安装方法。这是我们将使用的方法。
 
但首先，更新包数据库以确保您拥有最新的可用包列表：

```
$ sudo apt-get update
```
 
现在`curl`使用以下命令下载Sysdig的安装脚本：

```
$ curl https://s3.amazonaws.com/download.draios.com/stable/install-sysdig -o install-sysdig
```
 
这会将安装脚本下载`install-sysdig`到当前文件夹的文件中。您需要使用提升的权限执行此脚本，并且运行从Internet下载的脚本是危险的。在执行脚本之前，通过在文本编辑器中打开它或使用`less`命令在屏幕上显示内容来审核其内容：

```
$ less ./install-sysdig
```
 
一旦您熟悉脚本将运行的命令，请使用以下命令执行脚本：

```
$ cat ./install-sysdig | sudo bash
```
 
命令将安装所有依赖项，包括内核头文件和模块。安装的输出类似于以下内容：

```
* Detecting operating system
* Installing Sysdig public key
OK
* Installing sysdig repository
* Installing kernel headers
* Installing sysdig
​
...
​
sysdig-probe:
Running module version sanity check.
 - Original module
   - No original module exists within this kernel
 - Installation
   - Installing to /lib/modules/4.4.0-59-generic/updates/dkms/
​
depmod....
​
DKMS: install completed.
Processing triggers for libc-bin (2.23-0ubuntu5) ...
```
 
现在您已经安装了Sysdig，让我们看一下使用它的一些方法。
 
##  **第2步 - 实时监控您的系统**  
 
在本节中，您将使用`sysdig`命令查看Ubuntu 16.04服务器上的某些事件。`sysdig`命令需要root权限才能运行，并且它需要任意数量的选项和过滤器。运行 命令最简单的方法是不带任何参数。这将为您提供每两秒刷新一次的系统数据的实时视图：

```
$ sudo sysdig
```
 
但是，正如您在运行命令时所看到的那样，分析正在写入屏幕的数据可能很困难，因为它会持续流动，并且您的服务器上发生了很多事件。按下`CTRL+C`停止`sysdig`。
 
在我们使用一些选项再次运行命令之前，让我们通过查看命令的示例输出来熟悉输出：

```
253566 11:16:42.808339958 0 sshd (12392) > rt_sigprocmask
253567 11:16:42.808340777 0 sshd (12392) < rt_sigprocmask
253568 11:16:42.808341072 0 sshd (12392) > rt_sigprocmask
253569 11:16:42.808341377 0 sshd (12392) < rt_sigprocmask
253570 11:16:42.808342432 0 sshd (12392) > clock_gettime
253571 11:16:42.808343127 0 sshd (12392) < clock_gettime
253572 11:16:42.808344269 0 sshd (12392) > read fd=10(<f>/dev/ptmx) size=16384
253573 11:16:42.808346955 0 sshd (12392) < read res=2 data=..
```
 
输出的列是：

```
%evt.num %evt.outputtime %evt.cpu %proc.name (%thread.tid) %evt.dir %evt.type %evt.info
```
 
以下是每列的含义：

 
* **evt.num**  是增量事件编号。  
* **evt.outputtime**  是事件时间戳，您可以自定义。  
* **evt.cpu**  是捕获事件的CPU编号。在上面的输出中，  **evt.cpu**  为  **0**  ，这是服务器的第一个CPU。  
* **proc.name**  是生成事件的进程的名称。  
* **thread.tid**  是生成事件的TID，它对应于单线程进程的PID。  
* **evt.dir**  是事件方向。您将看到  **>**  用于输入事件和  **<**  用于退出事件。  
* **evt.type**  是事件的名称，例如'open'，'read'，'write'等。  
* ** [ http:// evt.info  ][4] **  是事件参数列表。在系统调用的情况下，这些往往对应于系统调用参数，但情况并非总是如此：出于简单性或性能原因，排除了一些系统调用参数。  
 
 
像上一个`sysdig`命令一样，运行几乎没有任何价值，因为流入的信息太多了。但是您可以使用以下语法对命令应用选项和过滤器：

```
$ sudo sysdig [option] [filter]
```
 
您可以使用以下方法查看可用过滤器的完整列表：

```
$ sysdig -l
```
 
有一个广泛的过滤器列表，涵盖了几个类或类别。以下是一些课程：

 
* **fd**  ：过滤文件描述符（FD）信息，如FD编号和FD名称。  
* **process**  ：过滤进程信息，例如生成事件的进程的id和名称。  
* **evt**  ：过滤事件信息，如事件编号和时间。  
* **user**  ：过滤用户信息，如用户ID，用户名，用户主目录或登录shell。  
* **group**  ：过滤组信息，例如组ID和名称。  
* **syslog**  ：过滤syslog信息，如设施和严重性。  
* **fdlist**  ：过滤轮询事件的文件描述符。  
 
 
由于本教程中的每个过滤器都不实用，所以让我们尝试一下，从  **syslog**  类中的  **syslog.severity.str**  过滤器开始，它允许您查看以特定严重性级别发送到syslog的消息。此命令显示在“信息”级别发送到syslog的消息：

```
$ sudo sysdig syslog.severity.str=info
```
 
 **注意：**  根据服务器上的活动级别，在输入此命令后可能看不到任何输出，或者在看到任何输出之前可能需要很长时间。要强制发出问题，请打开另一个终端模拟器并执行将向syslog生成消息的操作。例如，执行包更新，升级系统或安装任何包。
 
按下`CTRL+C`即可终止命令。
 
输出应 很容易解释，看起来应 是这样的：

```
10716 03:15:37.111266382 0 sudo (26322) < sendto syslog sev=info msg=Jan 24 03:15:37 sudo: pam_unix(sudo:session): session opened for user root b
618099 03:15:57.643458223 0 sudo (26322) < sendto syslog sev=info msg=Jan 24 03:15:57 sudo: pam_unix(sudo:session): session closed for user root
627648 03:16:23.212054906 0 sudo (27039) < sendto syslog sev=info msg=Jan 24 03:16:23 sudo: pam_unix(sudo:session): session opened for user root b
629992 03:16:23.248012987 0 sudo (27039) < sendto syslog sev=info msg=Jan 24 03:16:23 sudo: pam_unix(sudo:session): session closed for user root
639224 03:17:01.614343568 0 cron (27042) < sendto syslog sev=info msg=Jan 24 03:17:01 CRON[27042]: pam_unix(cron:session): session opened for user
639530 03:17:01.615731821 0 cron (27043) < sendto syslog sev=info msg=Jan 24 03:17:01 CRON[27043]: (root) CMD (   cd / && run-parts --report /etc/
640031 03:17:01.619412864 0 cron (27042) < sendto syslog sev=info msg=Jan 24 03:17:01 CRON[27042]: pam_unix(cron:session):
```
 
您还可以过滤单个流程。例如，从`nano`上查看事件，请执行以下命令：

```
$ sudo sysdig proc.name=nano
```
 
由于此命令文件管理器已启用`nano`，您必须使用`nano`文本编辑器打开文件以查看任何输出。打开另一个终端编辑器，连接到您的服务器，然后使用`nano`打开文本文件。写几个字符并保存文件。然后返回原始终端。
 
然后，您将看到类似于此的一些输出：

```
21840 11:26:33.390634648 0 nano (27291) < mmap res=7F517150A000 vm_size=8884 vm_rss=436 vm_swap=0
21841 11:26:33.390654669 0 nano (27291) > close fd=3(<f>/lib/x86_64-linux-gnu/libc.so.6)
21842 11:26:33.390657136 0 nano (27291) < close res=0
21843 11:26:33.390682336 0 nano (27291) > access mode=0(F_OK)
21844 11:26:33.390690897 0 nano (27291) < access res=-2(ENOENT) name=/etc/ld.so.nohwcap
21845 11:26:33.390695494 0 nano (27291) > open
21846 11:26:33.390708360 0 nano (27291) < open fd=3(<f>/lib/x86_64-linux-gnu/libdl.so.2) name=/lib/x86_64-linux-gnu/libdl.so.2 flags=4097(O_RDONLY|O_CLOEXEC) mode=0
21847 11:26:33.390710510 0 nano (27291) > read fd=3(<f>/lib/x86_64-linux-gnu/libdl.so.2) size=832
```
 
再次，通过按下`CTRL+C`来终止命令。
 
获取系统事件的实时视图`sysdig`并不总是使用它的最佳方法。幸运的是，还有另一种方法 - 将事件捕获到文件中以便稍后进行分析。我们来看看如何。
 
##  **第3步 - 使用Sysdig捕获系统活动到文件**  
 
使用系统事件捕获文件可以`sysdig`让您在以后分析这些事件。为了节省系统事件记录到文件中，传递`sysdig`的`-w`选项，并指定目标文件名，如下所示：

```
$ sudo sysdig -w sysdig-trace-file.scap
```
 
Sysdig将继续将生成的事件保存到目标文件，直到您按下为止`CTRL+C`。随着时间的推移， 文件可能会变得非常大。但是，使用`-n`选项，您可以指定希望Sysdig捕获的事件数。捕获目标事件数后，它将退出。例如，要将300个事件保存到文件，请输入：

```
$ sudo sysdig -n 300 -w sysdig-file.scap
```
 
虽然您可以使用Sysdig将指定数量的事件捕获到文件中，但更好的方法是使用  **-C**  选项将捕获分解为特定大小的较小文件。为了不淹没本地存储，您可以指示Sysdig只保留一些保存的文件。换句话说，Sysdig支持在一个命令中将事件捕获到具有文件轮换的日志。
 
例如，要将事件连续保存到大小不超过1 MB的文件中，并且只保留最后五个文件（这是  **-W**  选项的作用），请执行以下命令：

```
$ sudo sysdig -C 1 -W 5 -w sysdig-trace.scap
```
 
列出使用的文件`ls -l sysdig-trace*`，你会看到与此类似的输出，有五个日志文件：

```
-rw-r--r-- 1 root root 985K Nov 23 04:13 sysdig-trace.scap0
-rw-r--r-- 1 root root 952K Nov 23 04:14 sysdig-trace.scap1
-rw-r--r-- 1 root root 985K Nov 23 04:13 sysdig-trace.scap2
-rw-r--r-- 1 root root 985K Nov 23 04:13 sysdig-trace.scap3
-rw-r--r-- 1 root root 985K Nov 23 04:13 sysdig-trace.scap4
```
 
与实时捕获一样，您可以对已保存的事件应用过滤器。例如，要保存进程生成的200个事件`nano`，请输入以下命令：

```
$ sudo sysdig -n 200 -w sysdig-trace-nano.scap proc.name=nano
```
 
然后，在连接到服务器的另一个终端中，打开文件`nano`并通过输入文本或保存文件生成一些事件。将捕获事件`sysdig-trace-nano.scap`直到`sysdig`记录200个事件。
 
您将如何捕获服务器上生成的所有写入事件？你会像这样应用过滤器：

```
$ sudo sysdig -w sysdig-write-events.scap evt.type=write
```
 
片刻之后按下`CTRL+C`退出。在将系统活动保存到文件时，您可以做更多的事情`sysdig`，但是这些示例应 让您非常清楚如何去做。我们来看看如何分析这些文件。
 
##  **第4步 - 使用Sysdig读取和分析事件数据**  
 
使用Sysdig从文件中读取捕获的数据就像将  **-r**  开关传递给`sysdig`命令一样简单，如下所示：

```
$ sudo sysdig -r sysdig-trace-file.scap
```
 
这会将文件的整个内容转储到屏幕上，这不是最好的方法，特别是如果文件很大的话。幸运的是，您可以在读取写入时应用的文件时应用相同的过滤器。
 
例如，要读取`sysdig-trace-nano.scap`您创建的跟踪文件，但只查看特定类型的事件（如写入事件），请输入以下命令：

```
$ sysdig -r sysdig-trace-nano.scap evt.type=write
```
 
输出应类似于：

```
21340 13:32:14.577121096 0 nano (27590) < write res=1 data=.
21736 13:32:17.378737309 0 nano (27590) > write fd=1 size=23
21737 13:32:17.378748803 0 nano (27590) < write res=23 data=#This is a test file..#
21752 13:32:17.611797048 0 nano (27590) > write fd=1 size=24
21753 13:32:17.611808865 0 nano (27590) < write res=24 data= This is a test file..#  
21768 13:32:17.992495582 0 nano (27590) > write fd=1 size=25
21769 13:32:17.992504622 0 nano (27590) < write res=25 data=TThis is a test file..# T
21848 13:32:18.338497906 0 nano (27590) > write fd=1 size=25
21849 13:32:18.338506469 0 nano (27590) < write res=25 data=hThis is a test file..[5G
21864 13:32:18.500692107 0 nano (27590) > write fd=1 size=25
21865 13:32:18.500714395 0 nano (27590) < write res=25 data=iThis is a test file..[6G
21880 13:32:18.529249448 0 nano (27590) > write fd=1 size=25
21881 13:32:18.529258664 0 nano (27590) < write res=25 data=sThis is a test file..[7G
21896 13:32:18.620305802 0 nano (27590) > write fd=1 size=25
```
 
让我们看一下您在上一节中保存的文件的内容：`sysdig-write-events.scap`文件。我们知道保存到文件中的所有事件都是写事件，所以让我们查看内容：

```
$ sudo sysdig -r sysdig-write-events.scap evt.type=write
```
 
这是部分输出。如果捕获事件时服务器上有任何SSH活动，您将看到类似的内容：

```
42585 19:58:03.040970004 0 gmain (14818) < write res=8 data=........
42650 19:58:04.279052747 0 sshd (22863) > write fd=3(<4t>11.11.11.11:43566->22.22.22.22:ssh) size=28
42651 19:58:04.279128102 0 sshd (22863) < write res=28 data=.8c..jp...P........s.E<...s.
42780 19:58:06.046898181 0 sshd (12392) > write fd=3(<4t>11.11.11.11:51282->22.22.22.22:ssh) size=28
42781 19:58:06.046969936 0 sshd (12392) < write res=28 data=M~......V.....Z...\..o...N..
42974 19:58:09.338168745 0 sshd (22863) > write fd=3(<4t>11.11.11.11:43566->22.22.22.22:ssh) size=28
42975 19:58:09.338221272 0 sshd (22863) < write res=28 data=66..J.._s&U.UL8..A....U.qV.*
43104 19:58:11.101315981 0 sshd (12392) > write fd=3(<4t>11.11.11.11:51282->22.22.22.22:ssh) size=28
43105 19:58:11.101366417 0 sshd (12392) < write res=28 data=d).(...e....l..D.*_e...}..!e
43298 19:58:14.395655322 0 sshd (22863) > write fd=3(<4t>11.11.11.11:43566->22.22.22.22:ssh) size=28
43299 19:58:14.395701578 0 sshd (22863) < write res=28 data=.|.o....\...V...2.$_...{3.3|
43428 19:58:16.160703443 0 sshd (12392) > write fd=3(<4t>11.11.11.11:51282->22.22.22.22:ssh) size=28
43429 19:58:16.160788675 0 sshd (12392) < write res=28 data=..Hf.%.Y.,.s...q...=..(.1De.
43622 19:58:19.451623249 0 sshd (22863) > write fd=3(<4t>11.11.11.11:43566->22.22.22.22:ssh) size=28
43623 19:58:19.451689929 0 sshd (22863) < write res=28 data=.ZT^U.pN....Q.z.!.i-Kp.o.y..
43752 19:58:21.216882561 0 sshd (12392) > write fd=3(<4t>11.11.11.11:51282->22.22.22.22:ssh) size=28
```
 
请注意，前面输出中的所有行都包含  **11.11.11.11:51282->22.22.22.22:ssh**  。这些是从客户端的外部IP地址`11.11.11.11`到服务器`22.22.22.22`的IP地址的事件。这些事件发生在与服务器的SSH连接上，因此需要这些事件。但是有没有其他SSH写事件不是来自这个已知的客户端IP地址？这很容易找到。
 
您可以使用Sysdig的许多比较运算符。你看到的第一个是  **=**  。其他的是  **！=**  ，  **>**  ，  **> =**  ，  **<**  和  **<=**  。在以下命令中，  **fd.rip**  过滤远程IP地址。我们将使用  **！=**  比较运算符来查找来自以下IP地址的事件：

```
$ sysdig -r sysdig-write-events.scap fd.rip!=11.11.11.11
```
 
以下输出中显示了部分输出， 输出显示存在来自客户端IP地址以外的IP地址的写入事件：

```
294479 21:47:47.812314954 0 sshd (28766) > read fd=3(<4t>33.33.33.33:49802->22.22.22.22:ssh) size=1
294480 21:47:47.812315804 0 sshd (28766) < read res=1 data=T
294481 21:47:47.812316247 0 sshd (28766) > read fd=3(<4t>33.33.33.33:49802->22.22.22.22:ssh) size=1
294482 21:47:47.812317094 0 sshd (28766) < read res=1 data=Y
294483 21:47:47.812317547 0 sshd (28766) > read fd=3(<4t>33.33.33.33:49802->22.22.22.22:ssh) size=1
294484 21:47:47.812318401 0 sshd (28766) < read res=1 data=.
294485 21:47:47.812318901 0 sshd (28766) > read fd=3(<4t>33.33.33.33:49802->22.22.22.22:ssh) size=1
294486 21:47:47.812320884 0 sshd (28766) < read res=1 data=.
294487 21:47:47.812349108 0 sshd (28766) > fcntl fd=3(<4t>33.33.33.33:49802->22.22.22.22:ssh) cmd=4(F_GETFL)
294488 21:47:47.812350355 0 sshd (28766) < fcntl res=2(<f>/dev/null)
294489 21:47:47.812351048 0 sshd (28766) > fcntl fd=3(<4t>33.33.33.33:49802->22.22.22.22:ssh) cmd=5(F_SETFL)
294490 21:47:47.812351918 0 sshd (28766) < fcntl res=0(<f>/dev/null)
294554 21:47:47.813383844 0 sshd (28767) > write fd=3(<4t>33.33.33.33:49802->22.22.22.22:ssh) size=976
294555 21:47:47.813395154 0 sshd (28767) < write res=976 data=........zt.....L.....}....curve25519-sha256@libssh.org,ecdh-sha2-nistp256,ecdh-s
294691 21:47:48.039025654 0 sshd (28767) > read fd=3(<4t>221.229.172.117:49802->45.55.71.190:ssh) size=8192
```
 
进一步调查还显示，流氓IP地址`33.33.33.33`属于中国的一台机器。这是值得担心的事情！这只是一个例子，说明如何使用Sysdig密切注意服务器上的流量。
 
让我们看一下使用一些额外的脚本来分析事件流。
 
##  **第5步 - 使用Sysdigchisels进行系统监控和分析**  
 
在Sysdig的说法中， `chisels` 是可以使用的Lua脚本，用于分析Sysdig事件流以执行有用的操作。每个Sysdig安装都附带有近50个脚本，您可以使用以下命令查看系统上可用的chisels列表：

```
$ sysdig -cl
```
 
一些更有趣的chisels包括：

 
* **netstat**  ：列出（并可选择过滤）网络连接。  
* **shellshock_detect**  ：打印shellshock攻击  
* **spy_users**  ：显示交互式用户活动。  
* **list login shells**  ：列出登录shell ID。  
* **spy_ip**  ：显示使用给定IP地址交换的数据。  
* **spy_port**  ：显示使用给定IP端口号交换的数据。  
* **spy_file**  ：回显任何进程对所有文件的读取或写入。（可选）您可以提供文件名，以仅拦截对文件的读取或写入。  
* **httptop**  ：显示最热门的HTTP请求  
 
 
有关chisels的更详细描述（包括任何相关参数），请使用`-i`标志，然后使用chisels的名称。因此，例如，要查看有关`netstat`chisels的更多信息，请输入：

```
$ sysdig -i netstat
```
 
既然您已经了解了使用`netstat`chisels所需要知道的一切，请通过运行以下方式来监控系统：

```
$ sudo sysdig -c netstat
```
 
输出应类似于以下内容：

```
Proto Server Address           Client Address           State          TID/PID/Program Name
tcp   22.22.22.22:22           11.11.11.11:60422        ESTABLISHED    15567/15567/sshd
tcp   0.0.0.0:22               0.0.0.0:*                LISTEN         1613/1613/sshd
```
 
如果您在“  **客户端地址”**  列中看到来自您的IP地址以外的  **ESTABLISHED**  SSH连接，那么这应 是一个红色标记，您应 进行更深入的探测。
 
一个更有趣的chisels`spy_users`，它允许您查看系统上的交互式用户活动。
 
退出此命令：

```
$ sudo sysdig -c spy_users
```
 
然后，打开第二个终端并连接到您的服务器。在第二个终端中执行一些命令，然后返回到终端运行`sysdig`。您在第一个终端中输入的命令将在您执行`sysdig -c spy_users`命令的终端上回显。
 
接下来，让我们探索一个图形工具Csysdig。
 
##  **第6步 - 使用Csysdig进行系统监控和分析**  
 
Csysdig是Sysdig附带的另一个实用程序。它具有交互式用户界面，提供与命令行相同的功能`sysdig`。这就像`top`，`htop`和`strace`，但功能丰富的多。
 
与`sysdig`命令一样，`csysdig`命令可以执行实时监视，并可以将事件捕获到文件中以供以后分析。但是`csysdig`，您可以每两秒刷新一次更实用的系统数据实时视图。要查看示例，请输入以下命令：

```
$ sudo csysdig
```
 
这将打开如下图所示的界面， 界面显示受监控主机上的所有用户和应用程序生成的事件数据。

 
 ![][1]
 
Csysdig的主界面
 
在界面的底部有几个按钮，您可以使用它们来访问程序的不同方面。最值得注意的是“  **视图”**  按钮，它类似于收集的指标类别`csysdig`。开箱即用的视图有29个，包括  **进程**  ，  **系统调用**  ，  **线程**  ，  **容器**  ，  **进程CPU**  ，  **页面错误**  ，  **文件**  和  **目录**  。
 
当您在`csysdig`没有参数的情况下启动时 ，您将从“  **进程”**  视图中看到实时事件。通过单击“  **视图”**  按钮或`F2`按键，您将看到可用视图列表，包括列的说明。您还可以通过`F7`按键或单击“  **图例”**  按钮来查看列的说明。`csysdig`通过`F1`按键或单击“  **帮助”**  按钮，可以访问应用程序本身（）的摘要手册页。
 
下图显示了应用程序的  **Views**  界面的列表。

 
 ![][2]
 
Csysdig视图窗口
 
 **注意：**  对于每个按钮，按钮的左侧都有相应的键盘快捷键或热键。按两次快捷键将返回上一个窗口。按下`ESC`键将获得相同的结果。
 
虽然你可以在`csysdig`没有任何选项和参数的情况下运行，但命令的语法与`sysdig`s一样，通常采用以下形式：

```
$ sudo csysdig [option]...  [filter]
```
 
最常见的选项是  **-d**  ，用于修改更新之间的延迟（以毫秒为单位）。例如，要查看`csysdig`每10秒更新一次的输出，而不是默认值2秒，请输入：

```
$ sudo csysdig -d 10000
```
 
您可以使用  **-E**  选项从视图中排除用户和组信息：

```
$ sudo csysdig -E
```
 
这可以使`csysdig`启动更快，但在大多数情况下速度增益可以忽略不计。
 
要指示`csysdig`在一定数量的事件后停止捕获，请使用  **-n**  选项。应用程序将在达到 数字后退出。被捕获事件的数量必须在五个数字中; 否则你甚至不会看到`csysdig`UI：

```
$ sudo csysdig -n 100000
```
 
分析跟踪文件，通过`csysdig`了  **-r**  选项，如下所示：

```
$ sudo csysdig -r sysdig-trace-file.scap
```
 
您可以使用与之相同的过滤器`sysdig`来限制`csysdig`输出。因此，例如，您可以通过`csysdig`使用以下命令启动用户来过滤用户输出，而不是查看系统上所有用户生成的事件数据， 命令将显示仅由root用户生成的事件数据：

```
$ sudo csysdig user.name=root
```
 
输出应类似于下图中显示的输出，但输出将反映服务器上正在运行的内容：

 
 ![][3]
 
root生成的Csysdig数据
 
要查看生成事件的可执行文件的输出，请将过滤器的名称传递给不带路径的二进制文件。以下示例将显示`nano`命令生成的所有事件。换句话说，它将显示文本编辑器所在的所有打开文件`nano`：

```
$ sudo csysdig proc.name=nano
```
 
有几十个可用的过滤器，您可以使用以下命令查看：

```
$ sudo csysdig -l
```
 
您会注意到，这与您用于查看`sysdig`命令可用的过滤器的选项相同。所以`sysdig`，`csysdig`几乎是一样的。主要区别在于`csysdig`鼠标友好的交互式UI。要`csysdig`随时退出，请按`Q`键盘上的键。
 
##  **结论**  
 
Sysdig可帮助您监控服务器并对其进行故障排除。它将使您深入了解受监视主机上的所有系统活动，包括应用程序容器生成的活动。虽然本教程未特别涵盖容器，但监视容器生成的系统活动的能力使Sysdig与类似的应用程序区别开来。项目主页上提供了更多信息。
 
Sysdig的chisels是核心Sysdig功能的强大扩展。它们是用Lua编写的，所以你总是可以自定义它们或者从头开始编写它们。要了解有关制作chisels的更多信息，请访问 项目的 href=" [ http://www. sysdig.org/wiki/writing -a-sysdig-chisel,-a-tutorial  ][5] ">官方chisels 页面。
 
如果您对其他监视系统的工具，例如： [使用Ubuntu 16.04上的osquery监视系统安全性][6] ，可以访问腾讯云访问更多的教程。
 
  
参考文献：《How To Monitor Your Ubuntu 16.04 System with Sysdig》
 
 **问答** 
 
[在Ubuntu上安装cassandra？][7]
 
 **相关阅读** 
 
[如何保护PostgreSQL免受攻击][8]
 
[Ubuntu 16.04上如何使用Alertmanager和Blackbox导出程序监视Web服务器][9]
 
  [MariaDB Galera集群入门教程][10] 
 
 
此文已由作者授权腾讯云+社区发布，原文链接：https://cloud.tencent.com/developer/article/1172074?fromSource=waitui
 
欢迎大家前往腾讯云+社区或关注云加社区微信公众号（QcloudCommunity），第一时间获取更多海量技术实践干货哦~
 
海量技术实践经验，尽在云加社区！


[4]: https://link.zhihu.com/?target=http%3A//evt.info
[5]: https://link.zhihu.com/?target=http%3A//www.sysdig.org/wiki/writing-a-sysdig-chisel%2C-a-tutorial
[6]: https://link.zhihu.com/?target=https%3A//cloud.tencent.com/developer/article/1170720
[7]: https://link.zhihu.com/?target=https%3A//cloud.tencent.com/developer/ask/33934%3FfromSource%3Dwaitui
[8]: https://link.zhihu.com/?target=https%3A//cloud.tencent.com/developer/article/1174215%3FfromSource%3Dwaitui
[9]: https://link.zhihu.com/?target=https%3A//cloud.tencent.com/developer/article/1174204%3FfromSource%3Dwaitui
[10]: https://link.zhihu.com/?target=https%3A//cloud.tencent.com/developer/article/1174128%3FfromSource%3Dwaitui
[0]: ./img/iEJBfyY.jpg
[1]: ./img/riUNRfA.jpg
[2]: ./img/EFraIfJ.jpg
[3]: ./img/emI7Fvq.jpg