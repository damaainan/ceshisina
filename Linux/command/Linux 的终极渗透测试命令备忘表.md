## Linux 的终极渗透测试命令备忘表

来源：[https://www.oschina.net/translate/important-penetration-testing-cheat-sheet](https://www.oschina.net/translate/important-penetration-testing-cheat-sheet)

时间 2018-01-18 18:24:20


  
如下是一份 Linux 机器的渗透测试备忘录，是在后期开发期间或者执行命令注入等操作时的一些典型命令，设计为测试人员进行本地枚举检查之用。

此外，你还可以从[这儿][0]
阅读到许多关于渗透测试的文章。

| 命令 | 描述 |
|-|-|
| netstat -tulpn | 在 Linux 中显示对应了进程ID（PID）的网络端口。 |
| watch ss -stplu | 通过套接字实时观察 TCP, UDP 端口。 |
| lsof -i | 显示确认了的连接。 |
| macchanger -m MACADDR INTR | 在 KALI Linux 上修改 MAC 地址。 |
| ifconfig eth0 192.168.2.1/24 | 在 Linux 中设置 ID 地址。 |
| ifconfig eth0:1 192.168.2.3/24 | 在 Linux 中向现有的网络接口添加 IP 地址。 |
| ifconfig eth0 hw ether MACADDR | 使用 ifconfig 修改 Linux 中的 MAC 地址。 |
| ifconfig eth0 mtu 1500 | 在 Linux 中使用 ifconfig 修改 MTU 的大小，将 1500 改为你想要的 MTU。 |
| dig -x 192.168.1.1 | 对 IP 地址进行反向查找。 |
| host 192.168.1.1 | 在一个 IP 地址上进行反向查找，适用于没有安装 dig 的情况。 |
| dig @192.168.2.2 domain.com -t AXFR | 使用 dig 执行一次 DNS 区域传输。 |
| host -l domain.com nameserver | 使用 host 执行一次 DNS 区域传输。 |
| nbtstat -A x.x.x.x | 获取 IP 地址对应的域名。 |
| ip addr add 192.168.2.22/24 dev eth0 | 向 Linux 添加一个隐藏的 IP 地址，在执行 ifconfig 命令时不会显示这个 IP 地址。 |
| tcpkill -9 host google.com | 阻止从主机访问 google.com。 |
| echo "1" > /proc/sys/net/ipv4/ip_forward | 启用 IP 转发，将 Linux 盒子变成一个路由器——这样就方便通过这个盒子来进行路由流量的控制。 |
| echo "8.8.8.8" > /etc/resolv.conf | 使用 Google 的 DNS。 |




  
  
### 系统信息命令    

   对于本地的枚举检查很有用。

| 命令 | 描述 |
|-|-|
| `whoami` | 显示 Linux 上当前已登录用户。 |
| `id` | 向用户显示当前已登录的用户和组。 |
| `last` | 显示最后一次登陆的用户。 |
| `mount` | 显示已挂载的驱动。 |
| df -h | 用人类可读的输出显示磁盘使用情况。 |
| echo "user:passwd" | chpasswd | 用一行命令重置密码。 |
| getent passwd | 列出 Linux 上的用户。 |
| strings /usr/local/bin/blah | 显示非文本文件的内容，例如：一个二进制文件里面有什么。 |
| uname -ar | 显示运行中的内核版本。 |
| PATH=$PATH:/my/new-path | 添加一个新的路径，方便进行本地文件系统（FS）操作。 |
| `history` | 显示用户在之前执行的 bash 脚本历史记录，还有敲入的命令。 |


#### 基于 Redhat / CentOS / RPM 的发行版

| 命令 | 描述 |
|-|-|
| cat /etc/redhat-release | 显示 Redhat / CentOS 版本号。 |
| rpm -qa | 在基于 RPM 的 Linux 上列出所有已经安装上的 RPM 包。 |
| rpm -q --changelog openvpn | 检查已安装的 RPM 是否针对 CVE 打了补丁，可以用 grep 命令过滤出跟 CVE 有关的输出。 |




  
  
#### YUM 命令    

   基于 RPM 的系统使用了包管理器, 你可以用这些命令获取到有关已安装包或者其它工具的有用信息。

| 命令 | 描述 |
|-|-|
| yum update | 使用 YUM 更新所有的 RPM 包，也会显示出哪些已经过时了。 |
| yum update httpd | 更新单独的包，在此例中是 HTTPD (Apache)。 |
| yum install package | 使用 YUM 安装一个包。 |
| yum --exclude=package kernel* update | 在使用 YUM 时将一个包排除在外不更新。 |
| yum remove package | 使用 YUM 删除包。 |
| yum erase package | 使用 YUM 删除包。 |
| yum list package | 列出有关 yum 包的信息。 |
| yum provides httpd | 显示一个包是的用途，例如： Apache HTTPD Server。 |
| yum info httpd | 显示包信息，架构，版本等信息。 |
| yum localinstall blah.rpm | 使用 YUM 来安装本地 RPM， 从资源库进行安装。 |
| yum deplist package | 显示包的提供方信息。 |
| yum list installed | more | 列出所有已安装的包。 |
| yum grouplist | more | 显示所有的 YUM 分组。 |
| yum groupinstall 'Development Tools' | 安装 YUM 分组。 |




  
  
#### 基于 Debian / Ubuntu / .deb 的发行版

| 命令 | 描述 |
|-|-|
| cat /etc/debian_version | 显示 Debian 版本号。 |
| cat /etc/*-release | 显示 Ubuntu 版本号。 |
| dpkg -l | 在基于 Debian / .deb 的 Linux 发行版上列出所有已安装的包。 |


### Linux 用户管理

| 命令 | 描述 |
|-|-|
| useradd new-user | 创建一个新的 Linux 用户。 |
| passwd username | 重置 Linux 用户密码, 如果你是 root 用户，只要输入密码就行了。 |
| deluser username | 删除一个 Linux 用户。 |


### Linux 解压缩命令    

   如何在 Linux 上解析不同的压缩包 (tar, zip, gzip, bzip2 等等) ，以及其它的一些用来在压缩包中进行搜索等操作的小技巧。

| 命令 | 描述 |
|-|-|
| unzip archive.zip | 在 Linux 上提取 zip 包中的文件。 |
| zipgrep *.txt archive.zip | 在一个 zip 压缩包中搜索。 |
| tar xf archive.tar | 在 Linux 上提取 tar 包中的文件。 |
| tar xvzf archive.tar.gz | 在 Linux 上提取 tar.gz 包中的文件。 |
| tar xjf archive.tar.bz2 | 在 Linux 上提取 tar.bz2 包中的文件。 |
| tar ztvf file.tar.gz | grep blah | 在一个 tar.gz 文件中搜索。 |
| gzip -d archive.gz | 在 Linux 上提取 gzip 中的文件。 |
| zcat archive.gz | 在 Linux 以不解压缩的方式读取一个 gz 文件。 |
| zless archive.gz | 用较少的命令实现对 .gz 压缩包相同的功能。 |
| zgrep 'blah' /var/log/maillog*.gz | 在 Linux 上对 .gz 压缩包里面的内容执行搜索，比如搜索被压缩过的日志文件。 |
| vim file.txt.gz | 使用 vim 读取 .txt.gz 文件（我个人的最爱）。 |
| upx -9 -o output.exe input.exe | 在 Linux 上使用 UPX 压缩 .exe 文件。 |




  
  
### Linux 压缩命令

| 命令 | 描述 |
|-|-|
| zip -r file.zip /dir/* |   在 Linux 上创建一个 .zip 文件。|
| tar cf archive.tar files | 在 Linux 上创建一个 tar 文件。 |
| tar czf archive.tar.gz files | 在 Linux 上创建一个 tar.gz 文件。 |
| tar cjf archive.tar.bz2 files | 在 Linux 上创建一个 tar.bz2 文件。 |
| gzip file | 在 Linux 上创建一个 .gz 文件。 |


### Linux 文件命令

| 命令 | 描述 |
|-|-|
| df -h blah | 在 Linux 上显示文件/目录的大小。 |
| diff file1 file2 | 在 Linux 上比对/显示两个文件之间的差别。 |
| md5sum file | 在 Linux 上生成 MD5 摘要。 |
| md5sum -c blah.iso.md5 | 在 Linux 上检查文件的 MD5 摘要，这里假设文件和 .md5 处在相同的路径下。 |
| file blah | 在 Linux 上查找出文件的类型，也会将文件是 32 还是 64 位显示出来。 |
| dos2unix | 将 Windows 的行结束符转成 Unix/Linux 的。 |
| base64 < input-file > output-file | 对输入文件进行 Base64 编码，然后输出一个叫做 output-file 的 Base64 编码文件。 |
| base64 -d < input-file > output-file | 对输入文件进行 Base64 解码，然后输出一个叫做 output-file 的 Base64 解码文件。 |
| touch -r ref-file new-file | 使用来自于引用文件的时间戳数据创建一个新文件，放上 -r 以简单地创建一个文件。 |
| rm -rf | 不显示确认提示就删除文件和目录。 |


### Samba 命令

   从 Linux 连接到 Samba 共享。

 
```sh
$ smbmount //server/share /mnt/win -o user=username,password=password1
$ smbclient -U user \\\\server\\share
$ mount -t cifs -o username=user,password=password //x.x.x.x/share /mnt/share
```

  

  
  
### 打破 shell 的限制

   要谢谢 G0tmi1k(（或者他参考过的内容）。

   Python 小技巧：

 
```sh
python -c 'import pty;pty.spawn("/bin/bash")'
```

 
```sh
echo os.system('/bin/bash')
```

 
```sh
/bin/sh -i
```

  
### Misc 命令

| 命令 | 描述 |
|-|-|
| init 6 | 从命令行重启 Linux 。 |
| gcc -o output.c input.c | 编译 C 代码。 |
| gcc -m32 -o output.c input.c | 交叉编译 C 代码，在 64 位 Linux 上将编译出 32 位的二进制文件。 |
| unset HISTORYFILE | 关闭 bash 历史日志记录功能。 |
| rdesktop X.X.X.X | 从 Linux 连接到 RDP 服务器。 |
| `kill -9 $$` | 关掉当前的会话。 |
| chown user:group blah | 修改文件或者目录的所有者。 |
| chown -R user:group blah | 修改文件或者目录，以及目录下面文件/目录的拥有者 —— 递归执行 chown。 |
| chmod 600 file | 修改文件/目录的权限设定, 详情见 [Linux 文件系统权限](#linux-file-system-permissions) 。 |

清除 bash 历史：

 
```sh
      $ ssh user@X.X.X.X | cat /dev/null > ~/.bash_history
```

  

  
  
### Linux 文件系统权限

| 取值 | 意义 |
|-|-|
| 777 | rwxrwxrwx 没有限制，完全可读可写可执行（RWX），用户可以做任何事情。 |
| 755 | rwxr-xr-x 拥有者可完全访问，其他人只能读取和执行文件。 |
| 700 | rwx------ 拥有者可完全访问，其他人都不能访问。 |
| 666 | rw-rw-rw- 所有人可以读取和写入，但不可执行。 |
| 644 | rw-r--r-- 拥有者可以读取和写入，其他人只可以读取。 |
| 600 | rw------- 拥有者可以读取和写入，其他人都不能访问。 |


### Linux 文件系统的渗透测试备忘录

| 目录 | 描述 |
|-|-|
| `/` | / 也被称为“斜杠”或者根。 |
| `/bin` | 由系统、系统管理员以及用户共享的通用程序。 |
| `/boot` | Boot 文件, 启动加载器(grub), 内核, vmlinuz |
| `/dev` | 包含了对系统设备、带有特殊属性的文件的引用。 |
| `/etc` | 重要的系统配置文件。 |
| `/home` | 系统用户的主目录。 |
| `/lib` | 库文件，包括系统和用户都需要的所有类型的程序的文件。 |
| `/lost+found` | 文件操作失败会被保存在这里。 |
| `/mnt` | 外部文件系统的标准挂载点。 |
| `/media` | 外部文件系统（或者某些发行版）的挂载点。 |
| `/net` | 整个远程文件系统的标准挂载点 —— nfs。 |
| `/opt` | 一般都是包含一些附加的或者第三方软件。 |
| `/proc` | 一个包含了系统资源相关信息的虚拟文件系统。 |
| `/root` | root 用户的主目录。 |
| `/sbin` | 由系统和系统管理员来使用的程序。 |
| `/tmp` | 供系统使用的临时空间，重启时会被清空。 |
| `/usr` | 供所有用户相关程序使用的程序、库、文档等等。 |
| `/var` | 存储所有由用户创建的可变文件和临时文件，比如日志文件、邮件队列、后台打印程序，Web服务器，数据库等等。 |




  
  
### Linux 中有趣的文件/目录    

   如果你想尝试进行特权升级/执行后期开发，这些都是你值得一瞧的命令。

| 路径 | 描述 |
|-|-|
| `/etc/passwd` | 包含了本地 Linux 的用户。 |
| `/etc/shadow` | 包含了哈希过的本地账户密码。 |
| `/etc/group` | 包含了本地账户分组。 |
| `/etc/init.d/` | 包含了服务网初始化脚本 – 具体都安装了些啥应该值得一瞧。 |
| `/etc/hostname` | 系统的 hostname。 |
| `/etc/network/interfaces` | 网络接口。 |
| `/etc/resolv.conf` | 系统的 DNS 服务。 |
| `/etc/profile` | 系统的环境变量。 |
| `~/.ssh/` | SSH 密钥。 |
| `~/.bash_history` | 用户的 bash 历史日志。 |
| `/var/log/` | Linux 系统的日志文件一般就被存放在这里。 |
| `/var/adm/` | UNIX 系统的日志文件一般就被存在在这里。 |
| `/var/log/httpd/access.log` | Apache 访问日志文件通常的存在路径。 |
| `/etc/fstab` | 挂载的文件系统。 |

[0]: https://gbhackers.com/category/pentesting/