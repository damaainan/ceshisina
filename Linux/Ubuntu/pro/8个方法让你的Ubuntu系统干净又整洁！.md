## 8个方法让你的Ubuntu系统干净又整洁！

来源：[http://server.51cto.com/sOS-577156.htm](http://server.51cto.com/sOS-577156.htm)

时间 2018-06-27 08:58:58

 
无论你使用哪种操作系统，有时操作系统会因大量无用的文件而臃肿不堪。更糟糕的是，即使不久前升级了硬盘，硬盘的剩余容量还是不够。因此，有必要不时对PC进行一番维护，清理不必要的文件，这些文件在占用硬盘的大片存储空间。
 
#### 以下是Ubuntu用户可以清理Ubuntu的8个方法。
 
#### 1. 分析磁盘使用情况
 
你要做的头一件事是找出哪些文件在占用硬盘的大部分存储空间。从“应用程序”列表（位于“Utilities”文件夹）启动“磁盘使用分析工具”，然后单击硬盘来分析磁盘使用模式。你能够看到哪些文件/文件夹占用了大部分空间。
 
![][0]
 
![][1]
 
一旦确定了占用大量存储空间的文件，你可以执行下列操作：
 
确定这些文件有没有用处。要是没有用处，将它们发送到垃圾箱（或永久删除）。
 
如果目前不用该文件，但将来需要用到它，可以备份到外部硬盘，或者如果文件太大，可以使用压缩文件，分割成几个小文件以便存储。
 
#### 2. 清除重复的文件和损坏的符号链接
 
久而久之，同一个文件的好多副本可能散布于系统的不同地方。最好的想法是，把它们找出来并清除，以免它们占满了硬盘。
 
FSlint（http://www.pixelbeat.org/fslint/）是一个用于在文件系统上查找和清除各种lint的工具，尤其是重复文件和损坏的符号链接。
 
（1）在终端中使用下列命令来安装FSlint：
 
#### 1.sudo apt install fslint 
 
（2）从应用程序列表启动“FSlint Janitor”。添加想要搜索的文件路径。点击左边的“重复文件”选项卡，点击底部的“查找”。
 
![][2]
 
除了查找重复文件外，FSlint还可以查找损坏的符号链接、空目录、错误的ID，甚至多余的临时文件，这些统统可以删除，有助于收回宝贵的磁盘空间。
 
#### 3. 清理安装的软件包
 
如果你安装并卸载了大量应用程序，系统很可能安装有许多绝对没用的依赖文件。下面几个有用的命令可清除任何不完整的软件包，并删除任何未用的依赖项：
 
清理不完整的软件包：

```sh
sudo apt autoclean  


```
 
清理apt缓存内容：

```sh
sudo apt-get clean  


```
 
清理任何未用的依赖项：

```sh
sudo apt autoremove  


```
 
避免任何遗留内容的一个好做法是，每当你想卸载一个应用程序，就使用autoremove命令。

```sh
sudo apt autoremove application-name  


```
 
#### 4. 清除残留的旧配置包
 
将软件升级到更高版本后，以前版本的软件包仍会留在系统中。你可以清除残留的旧配置包来释放一些空间。
 
在这个例子中，我们将使用Synaptic Package Manager，默认情况下它未安装。（它已被Ubuntu Software取代。）不妨先安装Synaptic Package Manger：

```sh
sudo apt install synaptic  


```
 
注意：Synaptic Package Manager无法在Wayland显示服务器上运行，这是Ubuntu 17.10中的默认服务器。你可以按照此处的说明（https://www.maketecheasier.com/switch-xorg-wayland-ubuntu1710/），换成Ubuntu中的Xorg显示服务器。
 
安装后，从“应用程序”列表运行“Synaptic Package Manager”。它要求你在启动期间输入密码。点击左边的“状态”按钮。你会在左上方的窗格中看到几个选项。如果有“未安装（残余配置）”选项，点击它。这会显示系统中所有的残余配置包。
 
勾选配置包旁边的选择框，选择“标为彻底删除”。单击“应用”。
 
#### 5. 删除孤立的软件包
 
除了依赖文件外，你卸载应用程序后，软件包也可能变成孤立的。为了清除孤立文件，我们可以使用“gtkorphan”，这是“deborphan”的图形化前端。
 
通过终端安装gtkorphan：

```sh
sudo apt install gtkorphan  


```
 
从“应用程序”列表打开GtkOrphan。
 
它将分析系统，并在主窗口中显示所有孤立的软件包。勾选没有用处的软件包，并卸载。
 
#### 6. 跟踪已安装的内容
 
Debfoster创建依赖文件，让你能够跟踪已安装的内容。卸载一个应用程序时，它会检查任何残留的依赖文件或孤立的软件包，询问你是否要删除它们。
 
通过终端安装debfoster：

```sh
sudo apt install debfoster  


```
 
创建初始的keeper文件：

```sh
sudo debfoster -q  


```
 
迫使系统符合keeper文件

```sh
sudo debfoster -f  


```
 
如果你有几个永远不想卸载的软件包，又不希望debfoster处理那些软件包，可以编辑keeper文件（位于“/var/lib/debfoster/keepers”），从列表中删除那些软件包。
 
想查看是否存在需要删除的任何孤立软件包或依赖文件：

```sh
sudo debfoster  


```
 
#### 7. 删除区域设置文件
 
除非你需要始终切换到各种区域设置，否则可以卸载未使用的区域设置，释放系统中的一些存储空间。
 
通过终端安装localepurge：

```sh
sudo apt install localepurge  


```
 
一旦安装完毕，它会从系统删除你不需要的所有区域设置文件。
 
#### 8. 清理grub菜单
 
有时你执行更新时，会发现内核升级到新版本，而旧版本仍在。如果你在启动计算机时不想看到grub菜单中长长的列表，这个简单的方法可以清理grub菜单。
 
（1）在Synaptic中，搜索“已安装”状态的“linux-headers”。删除不是最新版本的那些条目。
 
（2）完成后，打开终端并输入下列命令：

```sh
sudo update-grub  


```
 
随后这会清理grub菜单。
 
注意：你可以查看这个Grub教程（https://www.maketecheasier.com/mastering-grub-2-the-easy-way/），了解定制Grub 2的更多方法。
 
#### 结论
 
上述技巧应该让你的Ubuntu系统干净又整洁。我遗漏了什么方法没有？你如何清理Ubuntu机器？


[0]: ../img/u6bMFzY.jpg
[1]: ../img/b6Nbmin.jpg
[2]: ../img/qiIvUjF.jpg