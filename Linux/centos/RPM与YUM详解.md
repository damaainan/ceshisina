## Linux软件安装管理之——RPM与YUM详解

### 一、序言

上一篇文章《Linux软件安装管理之——源码安装详解》详细介绍了Linux平台下的源码包安装原理，虽然使用源代码进行软件编译可以具有定制化的设置，但对于Linux distribution的发行商来说，则有软件管理不晚的问题，毕竟不是每个人都会进行源代码编译的。这个问题将会严重的影响linux平台上软件的发行与推广。

为了解决上述的问题，厂商先在他们的系统上面编译好了我们用户所需要的软件，然后将这个编译好并可执行的软件直接发布给用户安装。不同的 Linux 发行版使用不同的打包系统，一般而言，大多数发行版分别属于两大包管理技术阵营： Debian 的”.deb”，和 Red Hat的”.rpm”。也有一些重要的例外，比方说 Gentoo， Slackware，和 Foresight，但大多数会使用这两个基本系统中的一个。

这里将要介绍的是Red Hat系列发行版的RPM与YUM的详细使用方法，如需要了解Debian系列发行版的包管理系统可查看我的另一篇文章：《Linux软件安装管理之——dpkg与apt-*详解》。

### 二、RPM

#### 1、简介：

RPM命名“RedHat Package Manager”，简称则为RPM。这个机制最早由Red Hat这家公司开发出来的，后来实在很好用，因此很多distributons就使用这个机制来作为软件安装的管理方式，包括Fedora，CentOS，SuSE等知名的开发商都是用它。

RPM最大的特点就是需要安装的软件已经编译过，并已经打包成RPM机制的安装包，通过里头默认的数据库记录这个软件安装时需要的依赖软件。当安装在你的Linux主机时，RPM会先依照软件里头的数据查询Linux主机的依赖属性软件是否满足，若满足则予以安装，若不满足则不予安装。

#### 2、RPM优缺点

**优点：**

1）软件已经编译打包，所以传输和安装方便，让用户免除编译

2）在安装之前，会先检查系统的磁盘、操作系统版本等，避免错误安装

3）软件的信息都已经记录在linux主机的数据库上，方便查询、升级和卸载

**缺点：**

1）软件包安装的环境必须与打包时的环境一致或相当

2）必须安装了软件的依赖软件

3）卸载时，最底层的软件不能先移除，否则可能造成整个系统不能用  
为了解决RPM属性依赖的问题，下面也将会为你详细介绍YUM的使用方法。

#### 3、rpm包命名的含义

**RPM包的命名格式：**

软件名称-版本号-发布次数.适合linux系统.硬件平台.rpm  
例如：ftp-0.17-74.fc27.i686.rpm

**注意：**

包全名：rpm操作没有安装的软件包，软件包使用的是包全名

包 名：rpm操作的已经安装的软件，软件包使用的是包名

例如上面的例子，包全名为ftp-0.17-74.fc27.i686.rpm，包名为ftp。

#### 4、RPM安装软件的默认路径

/etc 配置文件放置目录

/usr/bin 一些可执行文件

/usr/lib 一些程序使用的动态链接库

/usr/share/doc 一些基本的软件使用手册与说明文件

/usr/share/man 一些man page档案

#### 5、RPM安装原理图

![][0]

### 三、RPM使用手册

#### 1、软件安装（install）

如你需要安装一个emac编辑器，首先要到网上下载一个emac的rpm包，如emacs-25.3-1.fc28.aarch64.rpm。最简单的安装命令如下：

    rpm -i emacs-25.3-1.fc28.aarch64.rpm
    

不过，这样的参数其实无法显示安装的进度，所以通常我们执行的命令是这样：

    rpm -ivh package-name
    

> 参数说明（后面括号内为英文说明）：

> -i：install的意思，安装

> -v：查看更详细的安装信息画面（provide more detailed output）

> -h：以安装信息栏显示安装进度

    rpm -ivh emacs-25.3-1.fc28.aarch64.rpm
    

如果想安装多个用空格间隔然后接上要安装的rpm包即可，同时也支持通过网址的资源来安装。

#### 2、软件升级

> 参数说明（后面括号内为英文说明）：

> -U：upgrade的意思，更新软件，若系统中没有该软件则进行安装（upgrade package(s)）

> -F：freshen的意思，更新系统已安装的某个软件（upgrade package(s) if already instaalled）

    rpm –Uvh  foo-2.0-1.i386.rpm
    

#### 3、查询模式

RPM在查询的时候，其实查询的地方是/var/lib/rpm/这个目录下的数据库文件。另外，RPM也可以查询未安装的RPM文件内的信息。

> RPM的查询语法为：

> rpm {-q|--query} [select-options] [query-options] 常用参数说明（后面括号内为英文说明）：

> -a：all，列出已经安装在本机的所有软件（Query all instaled packages.）

> -p：package，查询一个RPM文件的信息（Query an (uninstalled) package. ）

> -f：file，由后面接的文件名称找出该文件属于哪狐假虎威已安装的软件（Query package owning file.）

> -i：information，列出该软件的详细信息，包含开发商、版本与说明等（Display package information, including name, version, and description.）

> -l：list，列出该软件所有的文件与目录所在完整文件名（List file in package）

> -c：configuration，列出该软件的所有设置文件(找出在/etc/下面的文件名而已)（List only configuration files）

> -d：documentation，列出该软件所有的帮助文档（List only documentation files）

> -R：required，列出与该软件有关的依赖软件所含的文件（List capabilities on which this depends.）

> 注意：在查询的部分，所有的参数之前都要加上-q才是所谓的查询。

使用案例：

1）查询你的Linux是否有安装某个软件

    rpm -q yum
    

2）查询属于该软件所提供的所有目录与文件

    rpm -ql yum
    
    rpm -qc yum    #仅列出该软件的所有设置文件
    
    rpm -qd yum    #仅列出该软件的所有帮助文档
    

3）列出gcc这个软件的相关数据说明

    rpm -qi gcc
    

4）找出/bin/sh是由哪个软件提供的

    rpm -qf /bin/sh
    

5）假设我有下载一个RPM文件,包名为wget-1.19.1-3.fc27.aarc64.rpm，想要知道该文件的需求文件，该如何办？

    rpm -qpR wget-1.19.1-3.fc27.aarc64.rpm
    

#### 4、卸载软件

使用rpm的卸载过程一定要由最上层往下卸载，以rp-pppoe为例，这个软件主要是依据ppp这个软件来安装的，所以当你要卸载ppp的时候，就必须先卸载rp-pppoe才行！

删除的命令非常简单，通过-e参数就可以完成。不过，很常发生软件属性依赖导致无法山洼某些软件的问题。

例子：

    rpm -e gcc
    

欲了解rpm的更多使用方法，可以自行去查阅rpm的man手册，这里只是列出了一些常用的操作。

### 四、YUM

#### 1、简介：

YUM可以看作是CS架构的软件，YUM的存在很好的解决了RPM的属性依赖问题。

YUM通过依赖rpm软件包管理器, 实现了rpm软件包管理器在功能上的扩展, 因此YUM是不能脱离rpm而独立运行的。

#### 2、YUM的特点

1）可以同时配置多个资源库(Repository)

2）简洁的配置文件(/etc/yum.conf)

3）自动解决增加或删除rpm包时遇到的依赖性问题

4）使用方便

5）保持与RPM数据库的一致性

#### 3、YUM原理说明

Server端先对程序包进行分类后存储到不同repository容器中; 再通过收集到大量的rpm的数据库文件中程序包之间的依赖关系数据, 生成对应的依赖关系和所需文件在本地的存放位置的说明文件(.xml格式), 存放在本地的repodata目录下供Client端取用

Cilent端通过yum命令安装软件时发现缺少某些依赖性程序包, Client会根据本地的配置文件(/etc/yum.repos.d/*.repo)找到指定的Server端, 从Server端repo目录下获取说明文件xxx.xml后存储在本地/var/cache/yum中方便以后读取, 通过xxx.xml文件查找到需要安装的依赖性程序包在Server端的存放位置, 再进入Server端yum库中的指定repository容器中获取所需程序包, 下载完成后在本地实现安装。

![][1]

注意：YUM是一个在线软件管理工具，所以使用YUM进行的操作大都是需要在联网的条件下才能正常使用。

### 五、YUM的配置文件

#### 1、容器说明

虽然yum是你在联网后就能直接使用，不过，由于你系统的站点镜像没选择好，会导致连接速度非常慢！所以，这时候就需要我们去手动修改yum的设置文档了。

如你连接到CentOS的镜像站点（[http://ftp.twaren.net/Linux/C...][2]）后，就会发现里面有一堆链接，那些链接就是这个yum服务器所提供的容器了，包括centosplus、extras、fasttrack、os、updates等容器，最好认的就是os(系统默认的软件)与updates(软件升级版本)。

在yum服务器的容器里面，最重要的一个目录就是那个“repodata”，该目录是分析RPM软件后所产生的软件依赖数据放置处。因此，当你找到容器所在网址时，最重要的就是该网址下面一定要有一个名为“repodata”的目录存在，那就是容器的网址了。

下面都是以我的主机为例：CentOS 7.4.1708

#### 2、容器查询

首先，可以先查询一下目录yum server所使用的容器有哪些。

使用命令：yum repolist all，查询结果如下：

![][3]

如上图，只有当最右边的status为enabled该容器才算激活，

/etc/yum.repos.d/里面会有多个配置文件（文件名以.repo结尾），yum会从里面逐个查找，所以里面的容器名称不能有重复。

#### 3、配置文件修改

打开配置文件：vi /etc/yum.repos.d/CenOS-Base.repo，内容如下

![][4]

如上只是部分容器的截图，该配置文件的说明如下：

> [base]：代表容器的名字。中括号一定要存在，里面的名称可以随意起，但不能有两个相同的容器名称，否则yum会不知道去哪里找容器相关软件列表文件。

> name：只是说明一下这个容器的意义而已，重要性不高。

> mirrorlist：列出这个容器可以使用的镜像站点，如果不想使用可以批注掉这一行。

> baseurl：这个最重要，因为后面接的就是容器的实际网址。mirrorlist是由yum程序自行去找镜像站点，  
> baseurl则是指定固定的一个容器网址。

> enable=1：启动这个容器，默认值也为1。关闭这个容器可以设置enable=0。

> gpgcheck=1：指定是否需要查阅RPM文件内的数字证书。

> gpgkey：数字证书的公钥文件所在位置，使用默认值即可。

**注意：**

1）yum会自动识别/etc/yum.repos.d/目录以.repo结尾的文件。

2）当我们修改了配置文件的网址却没有修改容器名称，可以会造成本机的列表与yum服务器的列表不同步，这时就需要手动来清除容器的数据了：

> 语法：yum clean [packages|headers|all] 

> 参数：

> packages：将已下载的软件文件删除

> headers：将下载的软件文件头删除

> all：将所有容器数据都删除

> 例：删除已下载过的所有容器相关数据（含软件本身与列表）

> yum clean all

### 六、YUM使用手册

#### 1、查询

> 查询相关的命令：

> search：搜索某个软件名称或者是描述的重要关键字；

> list：列出目前yum所管理的所有的软件名称与版本，有点类似于rpm -qa

> info：同上，不过有点类似于rpm -qai

> provides：从文件去搜索软件！类似于rpm -qf

    1)查询与ftp相关的软件有哪些
    
    yum search ftp
    
    2)查询gcc这个软件的功能
    
    yum info gcc
    
    可以查询到该软件的版本号、描述信息、是否已安装等信息。
    
    3）列出yum服务器上所提供的所有软件名称
    
    yum list
    
    4)列出目前服务器上可供本机进行升级的软件有哪些
    
    yum list updates
    
    5)列出提供passwd这个文件的软件有哪些
    
    yum provides passwd
    
    6)查找以pam开头的软件名称有哪些
    
    yum list pam*
    

#### 2、安装与升级

> 相关的命令：

> install：后面接要安装的软件。

> update：后面接要升级的软件。若要整个系统都升级，就直接update即可。

    例：安装一个emacs编辑器软件
    
    yum install emacs
    

**小技巧：**  
使用参数-y，当遇到需要等待用户输入时，这个选项会提供yes的响应，如上面的例子可以写成：yum -y install emacs

#### 4、卸载

> 相关命令：  
> remove

    例：卸载上面例子安装的emacs
    
    yum remove emacs
    

#### 5、软件组管理

还记得全新安装CentOS时，不是可以选择所需的软件么？而那些软件不是利用GNOME/KDE/X Window之类的名称存在吗？其实这就是软件组。软件级的存在，对于大量的一系列软件安装是非常有用的一个功能。

> 相关命令说明：

> grouplist：列出所有可用的组列表

> groupinfo：后面接group name，则可以了解该组内含的所有组名称

> groupstall：安装一整级的软件

> groupremove：删除某个组

    1）查询目前容器与本机上面的可用与安装过的软件组有哪些
    
    yum grouplist
    
    2）查看一个软件组的信息
    
    yum groupinfo GNOME
    
    3）安装桌面环境GNOME
    
    yum groupstall GNOME

欲了解yum的更多使用方法，也可以自行去查阅yum的man手册。

学完了yum的操作，是不是突然觉得Linux上的软件管理变得简单多了，似乎前面学的rpm也可以不需要了！虽然是如此，但是yum毕竟是架构在rpm上面的，所以关于rpm的相关知识也还是要掌握的。

### 参考文献：

《鸟哥的Linux私房菜——基础学习篇》鸟哥

《The Linux Command Line》William E. Shotts, Jr.

[0]: ../img/bVU9Sm.png
[1]: ../img/bVU9S5.png
[2]: http://ftp.twaren.net/Linux/CentOS/7.3.1611/
[3]: ../img/bVU9Td.png
[4]: ../img/bVU9Tn.png