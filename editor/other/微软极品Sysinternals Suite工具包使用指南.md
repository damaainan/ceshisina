## 微软极品Sysinternals Suite工具包使用指南

来源：[https://blog.csdn.net/hzm8341/article/details/57419442](https://blog.csdn.net/hzm8341/article/details/57419442)

时间：

<link rel="stylesheet" href="https://csdnimg.cn/release/phoenix/template/css/ck_htmledit_views-e2445db1a8.css">
						
                
为什么把Sysinternals Suite冠以极品二字？其实从07年[Vista之家][0]开始运行的时候，就推荐过这套软件10几次。被微软官方收购的这套软件包，确实有强悍的过人之处，鉴学习了这套软件的很多功能和思想。

今天，把这套工具包里面的每个实用软件都整理出来，按照名称首字母排序，点击每个蓝色标题链接都可以转到微软的对应官方页面，有对这些工具包的直接下载地址和更详尽的用法。因为每个软件几乎都可以长篇大论的介绍，所以，在此就只做简介和罗列，希望能够对大家有所帮助。

每个软件都可以单独下载，当然更建议直接下载他们的集成版——Sysinternals Suite 系统工具套装。其实，这套工具包的下载地址几乎是常年不变的，基本都保持在10M大小，下载地址大家可以记住：[http://download.sysinternals.com/Files/SysinternalsSuite.zip][1]。

好吧，下面是列表，都是中文说明。 **`一、各工具简介和微软官方网页`** 

[AccessChk][2]

为了确保创建安全的环境，Windows 管理员通常需要了解特定用户或用户组对文件、目录、注册表项和 Windows 服务等资源具有哪种访问权限。AccessChk 能够通过直观的界面和输出快速回答这些问题。

[AccessEnum][3]

这一简单但强大的安全工具可以向您显示，谁可以用何种访问权限访问您系统中的目录、文件和注册表项。使用此工具可查找权限漏洞。

[AdExplorer][4]

Active Directory Explorer 是一个高级的 Active Directory (AD) 查看器和编辑器。

[AdInsight][5]

一种 LDAP（轻型目录访问协议）实时监视工具，旨在对 Active Directory 客户端应用程序进行故障排除。

[AdRestore][6]

恢复已删除的 Server 2003 Active Directory 对象。

[Autologon][7]

登录过程中跳过密码屏幕。

[Autoruns][8]

查看哪些程序被配置为在系统启动和您登录时自动启动。Autoruns 还能够完整列出应用程序可以配置自动启动设置的注册表和文件位置。

[BgInfo][9]

此完全可配置程序会自动生成桌面背景，其中包含有关系统的 IP 地址、计算机名称、网络适配器及更多内容的重要信息。

[BlueScreen][10]

此屏幕保护程序不仅精确模拟“蓝屏”，而且也模拟重新启动（完成 CHKDSK），并可在 Windows NT 4、Windows 2000、Windows XP、Server 2003 和 Windows 9x 上工作。

[CacheSet][11]

CacheSet 是一个允许您利用 NT 提供的功能来控制缓存管理器的工作集大小的程序。它与 NT 的所有版本都兼容。

[ClockRes][12]

查看系统时钟的分辨率，亦即计时器最大分辨率。

[Contig][13]

您是否希望迅速对您频繁使用的文件进行碎片整理？使用 Contig 优化单个的文件，或者创建连续的新文件。

[Coreinfo][14]

Coreinfo 是一个新的命令行实用工具，可向您显示逻辑处理器与物理处理器之间的映射、NUMA 节点和它们所处的插槽，以及分配给每个逻辑处理器的缓存。

[Ctrl2cap][15]

这是一个内核模式的驱动程序，可在键盘类驱动程序上演示键盘输入过滤，以便将 Caps-Lock 转变为控制键。在此级别过滤允许在 NT 刚好要“看到”键之前变换和隐藏键。Ctrl2cap 还显示如何使用 NtDisplayString() 打印初始化蓝屏的消息。

[DebugView][16]

Sysinternals 的另一个优先程序：此程序截取设备驱动程序对 DbgPrint 的调用和 Win32 程序生成的 OutputDebugString。它允许在不使用活动的调试器的情况下，在本地计算机上或通过 Internet 查看和记录调试会话输出。

[Desktops][17]

使用这一新的实用工具可以创建最多四个虚拟桌面，使用任务栏界面或热键预览每个桌面上的内容并在这些桌面之间轻松地进行切换。

[Disk2vhd][18]

Disk2vhd 可简化从物理系统到虚拟机 (p2v) 的迁移。

[DiskExt][19]

显示卷磁盘映射。

[Diskmon][20]

此实用工具会捕捉所有硬盘活动，或者在您的系统任务栏中象软件磁盘活动灯一样工作。

[DiskView][21]

图形磁盘扇区实用工具。

[Disk Usage (DU)][22]

按目录查看磁盘使用情况。

[EFSDump][23]

查看加密文件的信息。

[Handle][24]

此易用命令行实用工具将显示哪些进程打开了哪些文件，以及更多其他信息。

[Hex2dec][25]

将十六进制数字转换为十进制及反向转换。

[接合点][26]

创建 Win2K NTFS 符号链接。

[LDMDump][27]

转储逻辑磁盘管理器在磁盘上的数据库内容，其中说明了 Windows 2000 动态磁盘的分区情况。

[ListDLLs][28]

列出所有当前加载的 DLL，包括加载位置及其版本号。2.0 版将打印已加载模块的完整路径名。

[LiveKd][29]

使用 Microsoft 内核调试程序检查真实系统。

[LoadOrder][30]

查看设备加载到 WinNT/2K 系统中的顺序。

[LogonSessions][31]

列出系统中的活动登录会话。

[MoveFile][32]

使您可以安排在系统下一次重新启动时执行移动和删除命令。

[NTFSInfo][33]

用 NTFSInfo 可以查看有关 NTFS 卷的详细信息，包括主文件表 (MFT) 和 MFT 区的大小和位置，以及 NTFS 元数据文件的大小。

[PageDefrag][34]

对您的分页文件和注册表配置单元进行碎片整理。

[PendMoves][35]

枚举在系统下一次启动时所要执行的文件重命名和删除命令的列表。

[PipeList][36]

显示系统上的命名管道，包括每个管道的最大实例数和活动实例数。

[PortMon][37]

通过高级监视工具监视串行端口和并行端口的活动。它能识别所有的标准串行和并行 IOCTL，甚至可以显示部分正在发送和接收的数据。3.x 版具有强大的新 UI 增强功能和高级筛选功能。

[ProcDump][38]

这一新的命令行实用工具旨在捕获其他方式难以隔离和重现 CPU 峰值的进程转储。该工具还可用作用于创建进程转储的一般实用工具，并可以在进程具有挂起的窗口或未处理的异常时监视和生成进程转储。

[Process Explorer][39]

找出进程打开了哪些文件、注册表项和其他对象以及已加载哪些 DLL 等信息。这个功能异常强大的实用工具甚至可以显示每个进程的所有者。

[Process Monitor][40]

实时监视文件系统、注册表、进程、线程和 DLL 活动。

[ProcFeatures][41]

这一小程序会报告处理器和 Windows 对“物理地址扩展”和“无执行”缓冲区溢出保护的支持情况。

[PsExec][42]

在远程系统上执行进程。

[PsFile][43]

查看远程打开的文件。

[PsGetSid][44]

显示计算机或用户的 SID。

[PsInfo][45]

获取有关系统的信息。

[PsKill][46] v1.13（2009 年 12 月 1 日） 

终止本地或远程进程。

[PsList][47]

显示有关进程和线程的信息。

[PsLoggedOn][48]

显示登录到某个系统的用户。

[PsLogList][49]

转储事件日志记录。

[PsPasswd][50]

更改帐户密码。

[PsService][51]

查看和控制服务。

[PsShutdown][52]

关闭并重新启动（可选）计算机。

[PsSuspend][53]

挂起和继续进程。

[PsTools][54]

PsTools 套件包括一些命令行程序，可列出本地或远程计算机上运行的进程、远程运行进程、重新启动计算机、转储事件日志，以及执行其他任务。

[RegDelNull][55]

扫描并删除包含嵌入空字符的注册表项，标准注册表编辑工具不能删除这种注册表项。

[RegJump][56]

跳至 Regedit 中指定的注册表路径。

[RootkitRevealer][57]

扫描系统以找出基于 Rootkit 的恶意软件。

[SDelete][58]

安全地覆盖敏感文件，并使用此符合 DoD 的安全删除程序清理先前删除文件所在的可用空间。

[ShareEnum][59]

扫描网络上的文件共享并查看其安全设置，以关闭安全漏洞。

[ShellRunas][60]

通过方便的 shell 上下文菜单项，作为另一个用户启动程序。

[Sigcheck][61]

转储文件版本信息并检查系统中的映像是否已进行数字签名。

[Streams][62]

显示 NTFS 备用数据流。

[Strings][63]

在二进制映像中搜索 ANSI 和 UNICODE 字符串。

[Sync][64]

将缓存数据刷新到磁盘。

[TCPView][65]

活动套接字命令行查看器。

[VMMap][66]

VMMap 是进程虚拟和物理内存分析实用工具。

[VolumeId][67]

设置 FAT 或 NTFS 驱动器的卷 ID。

[Whois][68]

查看 Internet 地址的所有者。

[WinObj][69]

基本对象管理器命名空间查看器。

[ZoomIt][70]

在屏幕上进行缩放和绘图的演示实用工具。 **`二、Sysinternals Suite 小工具下载排行前10名（靠上的越受欢迎）`** 

Process Explorer 

AutoRuns 

Process Monitor 

PsTools 

PageDefrag 

RootkitRevealer 

TcpView 

BgInfo 

BlueScreen 

NewSid


[0]: http://vista.ithome.com/
[1]: http://download.sysinternals.com/Files/SysinternalsSuite.zip
[2]: http://technet.microsoft.com/zh-cn/sysinternals/bb664922.aspx
[3]: http://technet.microsoft.com/zh-cn/sysinternals/bb897332.aspx
[4]: http://technet.microsoft.com/zh-cn/sysinternals/bb963907.aspx
[5]: http://technet.microsoft.com/zh-cn/sysinternals/bb897539.aspx
[6]: http://technet.microsoft.com/zh-cn/sysinternals/bb963906.aspx
[7]: http://technet.microsoft.com/zh-cn/sysinternals/bb963905.aspx
[8]: http://technet.microsoft.com/zh-cn/sysinternals/bb963902.aspx
[9]: http://technet.microsoft.com/zh-cn/sysinternals/bb897557.aspx
[10]: http://technet.microsoft.com/zh-cn/sysinternals/bb897558.aspx
[11]: http://technet.microsoft.com/zh-cn/sysinternals/bb897561.aspx
[12]: http://technet.microsoft.com/zh-cn/sysinternals/bb897568.aspx
[13]: http://technet.microsoft.com/zh-cn/sysinternals/bb897428.aspx
[14]: http://technet.microsoft.com/zh-cn/sysinternals/cc835722.aspx
[15]: http://technet.microsoft.com/zh-cn/sysinternals/bb897578.aspx
[16]: http://technet.microsoft.com/zh-cn/sysinternals/bb896647.aspx
[17]: http://technet.microsoft.com/zh-cn/sysinternals/cc817881.aspx
[18]: http://technet.microsoft.com/zh-cn/sysinternals/ee656415.aspx
[19]: http://technet.microsoft.com/zh-cn/sysinternals/bb896648.aspx
[20]: http://technet.microsoft.com/zh-cn/sysinternals/bb896646.aspx
[21]: http://technet.microsoft.com/zh-cn/sysinternals/bb896650.aspx
[22]: http://technet.microsoft.com/zh-cn/sysinternals/bb896651.aspx
[23]: http://technet.microsoft.com/zh-cn/sysinternals/bb896735.aspx
[24]: http://technet.microsoft.com/zh-cn/sysinternals/bb896655.aspx
[25]: http://technet.microsoft.com/zh-cn/sysinternals/bb896736.aspx
[26]: http://technet.microsoft.com/zh-cn/sysinternals/bb896768.aspx
[27]: http://technet.microsoft.com/zh-cn/sysinternals/bb897413.aspx
[28]: http://technet.microsoft.com/zh-cn/sysinternals/bb896656.aspx
[29]: http://technet.microsoft.com/zh-cn/sysinternals/bb897415.aspx
[30]: http://technet.microsoft.com/zh-cn/sysinternals/bb897416.aspx
[31]: http://technet.microsoft.com/zh-cn/sysinternals/bb896769.aspx
[32]: http://technet.microsoft.com/zh-cn/sysinternals/bb897556.aspx
[33]: http://technet.microsoft.com/zh-cn/sysinternals/bb897424.aspx
[34]: http://technet.microsoft.com/zh-cn/sysinternals/bb897426.aspx
[35]: http://technet.microsoft.com/zh-cn/sysinternals/bb897556.aspx
[36]: http://technet.microsoft.com/zh-cn/sysinternals/dd581625.aspx
[37]: http://technet.microsoft.com/zh-cn/sysinternals/bb896644.aspx
[38]: http://technet.microsoft.com/zh-cn/sysinternals/dd996900.aspx
[39]: http://technet.microsoft.com/zh-cn/sysinternals/bb896653.aspx
[40]: http://technet.microsoft.com/zh-cn/sysinternals/bb896645.aspx
[41]: http://technet.microsoft.com/zh-cn/sysinternals/bb897554.aspx
[42]: http://technet.microsoft.com/zh-cn/sysinternals/bb897553.aspx
[43]: http://technet.microsoft.com/zh-cn/sysinternals/bb897552.aspx
[44]: http://technet.microsoft.com/zh-cn/sysinternals/bb897417.aspx
[45]: http://technet.microsoft.com/zh-cn/sysinternals/bb897550.aspx
[46]: http://technet.microsoft.com/zh-cn/sysinternals/bb896683.aspx
[47]: http://technet.microsoft.com/zh-cn/sysinternals/bb896682.aspx
[48]: http://technet.microsoft.com/zh-cn/sysinternals/bb897545.aspx
[49]: http://technet.microsoft.com/zh-cn/sysinternals/bb897544.aspx
[50]: http://technet.microsoft.com/zh-cn/sysinternals/bb897543.aspx
[51]: http://technet.microsoft.com/zh-cn/sysinternals/bb897542.aspx
[52]: http://technet.microsoft.com/zh-cn/sysinternals/bb897541.aspx
[53]: http://technet.microsoft.com/zh-cn/sysinternals/bb897540.aspx
[54]: http://technet.microsoft.com/zh-cn/sysinternals/bb896649.aspx
[55]: http://technet.microsoft.com/zh-cn/sysinternals/bb897448.aspx
[56]: http://technet.microsoft.com/zh-cn/sysinternals/bb963880.aspx
[57]: http://technet.microsoft.com/zh-cn/sysinternals/bb897445.aspx
[58]: http://technet.microsoft.com/zh-cn/sysinternals/bb897443.aspx
[59]: http://technet.microsoft.com/zh-cn/sysinternals/bb897442.aspx
[60]: http://technet.microsoft.com/zh-cn/sysinternals/cc300361.aspx
[61]: http://technet.microsoft.com/zh-cn/sysinternals/bb897441.aspx
[62]: http://technet.microsoft.com/zh-cn/sysinternals/bb897440.aspx
[63]: http://technet.microsoft.com/zh-cn/sysinternals/bb897439.aspx
[64]: http://technet.microsoft.com/zh-cn/sysinternals/bb897438.aspx
[65]: http://technet.microsoft.com/zh-cn/sysinternals/bb897437.aspx
[66]: http://technet.microsoft.com/zh-cn/sysinternals/dd535533.aspx
[67]: http://technet.microsoft.com/zh-cn/sysinternals/bb897436.aspx
[68]: http://technet.microsoft.com/zh-cn/sysinternals/bb897435.aspx
[69]: http://technet.microsoft.com/zh-cn/sysinternals/bb896657.aspx
[70]: http://technet.microsoft.com/zh-cn/sysinternals/bb897434.aspx