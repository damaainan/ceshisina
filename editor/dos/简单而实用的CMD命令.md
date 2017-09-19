# 简单而实用的CMD命令

 时间 2017-02-20 08:07:17  

原文[https://zhuanlan.zhihu.com/p/25194940][1]


![][3]

CMD是command的缩写，即命令提示符。说起cmd.exe就不得不说起DOS系统。

DOS系统早于Windows系统，DOS系统是命令行界面，比起易于操作的图形界面的Windows系统有着安全、效率高、指令传输快的优点。早期版本的Windows系统是以DOS系统为核心的。当以NT内核为核心的Windows系统出现以后，Windows系统不再包括DOS系统而独立运行。DOS系统由于其优点还被广泛使用，所以微软就在Windows系统里集成了cmd.exe这个应用程序，用户可以通过它来实现本来在DOS系统下才能实现的操作。

本文将会介绍一些简单而实用的cmd命令及其使用方法。

（有的命令需要管理员权限才能运行）

**TREE命令**

在整理文件的时候，有时会遇到文件夹和文件非常多又杂乱的情况，这时就可以使用tree命令。

tree命令能够以分支的形式显示指定目录下的全部子目录和文件。 

![][4]

    语法为：tree 指定目录 /f
    例如：tree C:\Users\Desktop /f
    若要显示cmd操作目录下的全部子目录和文件则直接使用：tree /f  即可。

参数/f使tree命令在显示目录信息的同时显示目录中的所有文件。若去掉参数/f，则只会显示文件夹。

有的时候tree命令显示的目录太长，在cmd窗口中无法查看全部目录，这时可以使用|more。 

![][5]

    使用 tree 指定目录 /f |more 之后，每按一次空格键目录就会翻一页。

在cmd窗口中查看结果有时是不太方便的，我们可以将结果保存并输出。 

![][6]

    语法：tree 指定目录 /f >1.txt
    我们也可以指定结果的输出目录：tree 指定目录 /f >d:\1.txt

**CHKDSK命令**

chkdsk即check disk，是用来检测磁盘文件系统错误的命令，它可以列出并纠正磁盘上的错误。由于它对错误的处理方式只有删除，所以有可能使数据丢失，导致文件找不到了，不过多数情况下错误的文件也已经无法打开了，所以也不用担心。

![][7]

    chkdsk /f 将检查所有磁盘，并修复磁盘错误。
    chkdsk /r 将查找坏扇区，并恢复可读信息。
    chkdsk 指定盘符 即可检查指定盘符。
    使用 chkdsk /? 来查看更多使用方法。

![][8]

chkdsk命令也可以修复由于直接拔出U盘而导致的“U盘的文件或目录损坏且无法读取”的问题。

**REN命令 

![][9]**

    ren d:\旧文件名.txt  新文件名.txt
    ren 旧文件名.txt  新文件名.txt

配合通配符*和?使用

* *代表任意多个字符
* ？代表任意一个字符

    ren d:\12？.txt  34？.txt 
    把d盘下12开头的文件名有三个字符的txt文件全部改名成了以34开头的文件。
    合理利用通配符可以一下子改动大量文件。

**SFC命令**

sfc命令会扫描所有保护的系统文件的完整性，并使用正确的 Microsoft 版本替换不正确的版本。 

![][10]

    sfc /SCANNOW 会扫描所有保护的系统文件的完整性，并尽可能修复有问题的文件。
    同样也可以输入 sfc /? 来查看更多使用方法。

**IEXPRESS命令 

![][11]**

IExpress是微软为压缩CAB文件及制作安装程序所开发的小工具。

**FINDSTR命令 

![][12]**

findsrt命令用于在文件中寻找字符串。

    findstr "abcd" d:\1.txt
    findstr "abcd" 1.txt

**SHUTDOWN命令** shutdown命令可以用来关闭计算机，配合参数/t使用，可以定时关闭电脑。 

![][13]

    shutdown /s /t 233（秒数）
    shutdown /a 可以取消定时关机计划。

**ARP命令 

![][14]**

    arp -a 可以查看同一网络下的设备的物理地址，可以用来查看是否有人蹭网。

![][15]

**TASKKILL命令**

taskkill命令可以用来关闭程序。

![][16]

    taskkill /f /im notepad.exe
    也可以输入 tasklist 查看所有的进程，查看进程对应的PID码
    从而使用 taskkill /pid 4708 来关闭进程

![][17]

![][18]

**FC命令**

fc命令可以比较文件的异同，并列出差异处。

![][19]

**ATTRIB命令**

attrib命令可以修改指定文件的属性。

![][20]

    attrib +r 1.txt

IPCONFIG命令

ipconfig命令有很多功能，可以使用参数/?查看。

    ipconfig /all 可以查看电脑网卡信息，包括mac地址、DNS地址、本地IP地址等。

**PING命令**

ping命令可以快速检查网络故障。

    ping 127.0.0.1 如发现本地址无法ping通，就表明本地机TCP/IP协议不能正常工作。
    Ping 本机IP地址 检查本机的IP地址是否设置有误
    Ping 本网网关或本网IP地址 检查硬件设备是否有问题，也可以检查本机与本地网络连接是否正常(在非局域网中这一步骤可以忽略)
    ping 远程IP地址（www.baidu.com ） 检查本机与互联网连接是否正常

**系统管理工具和小软件**

Windows系统中自带了很多有用的系统管理工具和一些实用的小软件，我们可以通过cmd命令来打开它们。

    打开cmd.exe：Win+R，输入cmd，回车。
    打开计算器：calc
    打开画图：mspaint
    打开放大镜：magnify
    打开屏幕键盘：osk
    打开记事本、写字板：notepad、write
    打开字符映射表：charmap
    打开造字程序（专用字符编辑程序）：eudcedit
    打开远程桌面连接：mstsc
    
    打开服务：services.msc
    打开设备管理器：devmgmt
    打开磁盘管理：diskmgmt
    打开系统信息：msinfo32
    打开系统配置：msconfig
    打开DirectX诊断工具：dxdiag
    打开事件查看器：eventvwr
    打开我的电脑：explorer
    打开注册表编辑器：regedt32
    打开资源监视器：resmon
    打开性能监视器：perfmon
    打开计算机管理：compmgmt

**cmd.exe的使用小技巧**

cmd窗口中的内容在默认设置下是无法选取并复制的。

我们可以在标题栏单击右键，在属性中勾选“快速编辑模式”，即可复制窗口中的代码。

也可以在属性中改变字体、字体颜色、背景颜色、缓冲区大小等设置。

前景颜色和背景颜色也可以通过代码来指定。

    title 标题 可以更改CMD窗口的标题
    color 颜色值1 颜色值2 设置cmd窗口的前景颜色和背景颜色
    0=黑、1=蓝、2=绿、3=浅绿、4=红、5=紫、6=黄、7=白、8=灰、9=淡蓝
    A=淡绿、B=淡浅绿、C=淡红、D=淡紫、E=淡黄、F=亮白

**cmd.exe的快捷键**

ESC：清除当前命令行。

F1: 单字符输出上次输入的命令 相当于方向键上的 → 的作用。F2: 可复制字符数量 , 输入上次命令中含有的字符,系统自动删除此字符后的内容。

F3: 重新输入前一次输入的命令（方向键上也是同样的作用）。F4: 可删除字符数量,同于F2的功能。

F5：相当于方向键上的↑的作用。F6：相当按键盘上的Ctrl＋z 键。

F7：显示命令历史记录，以图形列表窗的形式给出所有曾经输入的命令，并可用上下箭头键选择再次执行该命令。F8：搜索命令的历史记录，循环显示所有曾经输入的命令，直到按下回车键为止。

F9：按编号选择命令，以图形对话框方式要求您输入命令所对应的编号(从0开始)，并将该命令显示在屏幕上。Ctrl+H：删除光标左边的一个字符。

Ctrl+C 或者Ctrl+Break，强行中止命令执行。Ctrl+M：表示回车确认键。

Alt+F7：清除所有曾经输入的命令历史记录。Alt+PrintScreen：截取屏幕上当前命令窗里的内容。

Tab键：在命令提示符状态下，我们可以按下Tab键来选择当前目录下面的文件和文件夹，它的选择是按照一定顺序来进行的，按下Shift+Tab组合键还可以进行反方向选择。ALT+Enter 全屏再按退出。

最后附上通过CMD实现仿 **黑客帝国数字雨** 的代码。——引自网络 

    @echo off  
    :line 
    color 0a
    setlocal ENABLEDELAYEDEXPANSION  
     
    for /l %%i in (0) do (  
    set "line="  
    for /l %%j in (1,1,80) do (  
    set /a Down%%j-=2  
    set "x=!Down%%j!"  
    if !x! LSS 0 (  
    set /a Arrow%%j=!random!%%3  
    set /a Down%%j=!random!%%15+10  
    )  
    set "x=!Arrow%%j!"  
    if "!x!" == "2" (  
    set "line=!line!!random:~-1! "  
    ) else (set "line=!line! ")  
    )  
    set /p=!line!<nul  
    )  
    goto line


[1]: https://zhuanlan.zhihu.com/p/25194940

[3]: http://img2.tuicool.com/uq2AV3m.png
[4]: http://img1.tuicool.com/ema6Vfu.png
[5]: http://img1.tuicool.com/FvUb2me.png
[6]: http://img0.tuicool.com/eYFBZj7.png
[7]: http://img2.tuicool.com/ERf6ve2.png
[8]: http://img0.tuicool.com/fUvmQfa.png
[9]: http://img1.tuicool.com/EV7ZBjJ.png
[10]: http://img2.tuicool.com/rIbU3q3.png
[11]: http://img0.tuicool.com/3eMZvaR.png
[12]: http://img0.tuicool.com/ZvuUVvI.png
[13]: http://img1.tuicool.com/3mArMrV.png
[14]: http://img0.tuicool.com/e2I7BbM.png
[15]: http://img2.tuicool.com/aee6fyY.png
[16]: http://img1.tuicool.com/Mriu63N.png
[17]: http://img0.tuicool.com/miQnMzY.png
[18]: http://img1.tuicool.com/IVNbQn6.png
[19]: http://img1.tuicool.com/MvqU3iR.png
[20]: http://img2.tuicool.com/eeqIju7.png