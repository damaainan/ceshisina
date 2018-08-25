## linux基础命令介绍十二：磁盘与文件系统

来源：[https://segmentfault.com/a/1190000007813965](https://segmentfault.com/a/1190000007813965)

本篇讲述磁盘管理相关的命令。计算机中需要持久化存储的数据一般是保存在硬盘等辅助存储器中。硬盘一般容量较大，为了便于管理和使用，可以将硬盘分成一到多个逻辑磁盘，称为分区；为使分区中的文件组织成操作系统能够处理的形式，需要对分区进行格式化(创建文件系统)；在linux中，对于格式化后的分区，还必须经过挂载(可简单理解为将分区关联至linux目录树中某个已知目录)之后才能使用。

### 1、`df`显示文件系统磁盘空间使用量

```sh
[root@centos7 temp]# df -h
文件系统                 容量  已用  可用 已用% 挂载点
/dev/mapper/centos-root   49G   18G   31G   36% /
devtmpfs                 3.9G     0  3.9G    0% /dev
tmpfs                    3.9G     0  3.9G    0% /dev/shm
tmpfs                    3.9G  367M  3.5G   10% /run
tmpfs                    3.9G     0  3.9G    0% /sys/fs/cgroup
/dev/sda1                497M  125M  373M   26% /boot
/dev/mapper/centos-home   24G  4.0G   20G   17% /home
tmpfs                    783M     0  783M    0% /run/user/0
```

选项`-h`作用是转换数字的显示单位(默认为KB)。  
显示信息`文件系统`列下面带`tmpfs`字样的是虚拟内存文件系统(此处不做展开)。  
文件系统`/dev/mapper/centos-root`的挂载点是`/`(根目录)，即通常所说的根分区(或根文件系统)；`/dev/sda1`(boot分区)中保存了内核映像和一些启动时需要的辅助文件；另外，还对用户家目录单独做了分区(`/dev/mapper/centos-home`)。   
在linux中还可以做一个特殊的分区：`swap分区`(交换分区)。作用是：当系统的物理内存不够用时，会将物理内存中一部分暂时不使用的数据交换至swap分区中，当需要使用这些数据时，再从swap空间交换回内存空间。swap在功能上突破了物理内存的限制，使程序可以操纵大于实际物理内存的空间。但由于硬盘的速度远远低于内存，使swap只能作为物理内存的辅助。通常swap空间的大小是实际物理内存大小的1到2倍。使用命令`free`可以查看swap空间的大小。

选项`-i`显示inode信息

```sh
[root@centos7 tmp]# df -i
文件系统                   Inode 已用(I)  可用(I) 已用(I)% 挂载点
/dev/mapper/centos-root 50425856   78822 50347034       1% /
devtmpfs                  998721     391   998330       1% /dev
tmpfs                    1001340       1  1001339       1% /dev/shm
tmpfs                    1001340     490  1000850       1% /run
tmpfs                    1001340      13  1001327       1% /sys/fs/cgroup
/dev/sda1                 512000     330   511670       1% /boot
/dev/mapper/centos-home 24621056  190391 24430665       1% /home
tmpfs                    1001340       1  1001339       1% /run/user/0
```

这里显示的数字是该文件系统中inode数量的使用情况。

### 2、`fdisk`磁盘分区工具

```sh
fdisk [options] [device...]
```

选项`-l`表示列出分区表

```sh
[root@centos7 tmp]# fdisk -l /dev/sda

磁盘 /dev/sda：85.9 GB, 85899345920 字节，167772160 个扇区
Units = 扇区 of 1 * 512 = 512 bytes
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节
磁盘标签类型：dos
磁盘标识符：0x0001abbc

   设备   Boot      Start         End      Blocks   Id  System
/dev/sda1   *        2048     1026047      512000   83  Linux
/dev/sda2         1026048   167772159    83373056   8e  Linux LVM
[root@centos7 tmp]#
```

当前机械硬盘中包含一到多个固定在`主轴`(spindle)上的`盘片`(platter)，盘片由硬质磁性合金材料构成。每张盘片有上下两个表面，每个表面都包含数量巨大的`扇区`(sector)，扇区是大小为512 byte的区块，这些区块均匀的分布于盘片的同心圆上，这些同心圆被称为`磁道`(track)。上千个磁道的宽度相当于人类头发的直径。

硬盘中使用固定于`磁臂`(disk arm)顶端的`磁头`(disk head 上下两面均有)读写盘面中的数据。硬盘不工作时，磁头停留在`启停区`(盘片上靠近主轴的区域)；启停区外是数据区，盘片最外围磁道称为`0磁道`；硬盘启动后，盘片会围绕主轴高速旋转，盘片旋转产生的气流相当强，足以使磁头托起，并与盘面保持一个微小的距离(大概相当于人类头发直径的千分之一)。磁臂摆动，可以将磁头移动至任意磁道上方。

单一磁道示意图：


![][0] 

当前硬盘转速大概在7200转/分钟到15000转/分钟左右。假设硬盘转速是10000转/分钟，则意味着，转一圈需要的时间是6ms。

所有盘面上的同一磁道构成一个圆柱，通常称做`柱面`(Cylinder)，系统将数据存储到磁盘上时，按柱面、磁头、扇区的方式进行，即最上方0磁头最外围0磁道第一个扇区开始写入，写满一个磁道之后，接着在同一柱面的下一个磁头继续写入。同一个柱面都写满之后才推进到内层的下一个柱面。  

`fdisk`命令中device通常是`/dev/hda`、`/dev/hdb`....(IDE接口类型的硬盘设备名)或`/dev/sda`、`/dev/sdb`....(SCSI接口类型硬盘设备名)，表示整个硬盘，如果硬盘被分区，则在设备名后追加一个数字表示此设备的第几个分区。如上例中的`/dev/sda1`和`/dev/sda2`硬盘磁头存取数据是以扇区(512bytes)为单位的(上例中Start和End列)，但操作系统存取数据是以块(Block)为单位的(注意：这里说的Block的大小不同于fdisk命令输出中的Blocks列，fdisk命令输出中Blocks列的大小为1024 bytes)；扇区是硬件级别的，Block是文件系统级别的，也就是说在创建文件系统(`格式化`)的时候才决定一个block的大小、数量。一个块的大小是一个扇区大小2的n次方倍，本例文件系统Block的默认大小为4096 bytes(格式化时可以指定为其他值)。

我们在252这台机器上新添加三块硬盘(每块200GB)

```sh
[root@idc-v-71252 ~]# ls -l /dev/sd[a-d]*
brw-rw---- 1 root disk 8,  0 12月 13 09:49 /dev/sda
brw-rw---- 1 root disk 8,  1 12月 13 09:49 /dev/sda1
brw-rw---- 1 root disk 8,  2 12月 13 09:49 /dev/sda2
brw-rw---- 1 root disk 8, 16 12月 13 09:49 /dev/sdb
brw-rw---- 1 root disk 8, 32 12月 13 09:49 /dev/sdc
brw-rw---- 1 root disk 8, 48 12月 13 09:49 /dev/sdd
#这里看到除了原有被分过区的sda外，多出了设备sdb、sdc、sdd
#这里的第五列由逗号分隔的两个数字组成，它们是内核用来识别具体设备的标识号。
```

下面使用`fdisk`命令对新磁盘进行分区

```sh
[root@idc-v-71252 ~]# fdisk /dev/sdb
欢迎使用 fdisk (util-linux 2.23.2)。

更改将停留在内存中，直到您决定将更改写入磁盘。
使用写入命令前请三思。

Device does not contain a recognized partition table
使用磁盘标识符 0xc41dfd92 创建新的 DOS 磁盘标签。

命令(输入 m 获取帮助)：
```

在提示符后输入m获取帮助信息（列出了在提示符后可使用的命令及其解释）

```sh
命令(输入 m 获取帮助)：m
命令操作
   a   toggle a bootable flag
   b   edit bsd disklabel
   c   toggle the dos compatibility flag
   d   delete a partition
   g   create a new empty GPT partition table
   G   create an IRIX (SGI) partition table
   l   list known partition types
   m   print this menu
   n   add a new partition
   o   create a new empty DOS partition table
   p   print the partition table
   q   quit without saving changes
   s   create a new empty Sun disklabel
   t   change a partition\'s system id
   u   change display/entry units
   v   verify the partition table
   w   write table to disk and exit
   x   extra functionality (experts only)

命令(输入 m 获取帮助)：
```

命令`n`表示创建一个新分区

```sh
命令(输入 m 获取帮助)：n
Partition type:
   p   primary (0 primary, 0 extended, 4 free)
   e   extended
Select (default p): 
```

此处可选项有两个，`p`表示主分区(primary)，`e`表示扩展分区(extended)，默认为主分区。

每块硬盘分区后，位于0磁头0柱面1扇区的是一个特殊区域，称为`MBR`(Main Boot Record 主引导记录区)，其中前446字节是`Bootloader`(引导加载程序)，之后的64字节是`DPT`(Disk Partition Table 硬盘分区表)，最后两个字节的`Magic Number`(硬盘有效标志)。  
`DPT`中记录了此块硬盘有哪些分区，由于其大小的限制，使得分区表只能包含4条记录，可以是一到四个主分区或一个扩展分区和一到三个主分区。其中扩展分区可以再分区，称为`逻辑分区`。

我们选择默认的主分区：

```sh
Select (default p): 
Using default response p
分区号 (1-4，默认 1)：
起始 扇区 (2048-419430399，默认为 2048)：
将使用默认值 2048
Last 扇区, +扇区 or +size{K,M,G} (2048-419430399，默认为 419430399)：+100G
分区 1 已设置为 Linux 类型，大小设为 100 GiB

命令(输入 m 获取帮助)：
```

每一步骤都有相应提示，可以被使用的扇区从2048号开始(前面的扇区包括MBR用做其他用途)，分区结束扇区的指定可以是扇区号，也可以是+size这样的格式。这里我们指定分区大小为100G

使用`p`命令打印分区信息：

```sh
命令(输入 m 获取帮助)：p

磁盘 /dev/sdb：214.7 GB, 214748364800 字节，419430400 个扇区
Units = 扇区 of 1 * 512 = 512 bytes
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节
磁盘标签类型：dos
磁盘标识符：0xc41dfd92

   设备   Boot      Start         End      Blocks   Id  System
/dev/sdb1            2048   209717247   104857600   83  Linux

命令(输入 m 获取帮助)：
```

注意这里的显示的不同，`Boot`列如果有`*`标志，表示此分区为boot分区。`Id`列表示分区类型，可以使用命令`l`列出所有支持的类型，其中`82`表示`linux swap`，`83`表示linux默认分区类型，`8e`表示`linux lvm`(后述)。

然后我们将信息保存：

```sh
命令(输入 m 获取帮助)：w
The partition table has been altered!

Calling ioctl() to re-read partition table.
正在同步磁盘。
[root@idc-v-71252 ~]# 
```
### 3、`mkfs`创建文件系统

选项`-t`可以指定文件系统类型(包括ext3 ext4 btrfs xfs reiserfs等)

```
[root@idc-v-71252 ~]# mkfs -t ext4 /dev/sdb1 #或者直接执行 mkfs.ext4 /dev/sdb1
mke2fs 1.42.9 (28-Dec-2013)
文件系统标签=
OS type: Linux
块大小=4096 (log=2)
分块大小=4096 (log=2)
Stride=0 blocks, Stripe width=0 blocks
6553600 inodes, 26214400 blocks
1310720 blocks (5.00%) reserved for the super user
第一个数据块=0
Maximum filesystem blocks=2174746624
800 block groups
32768 blocks per group, 32768 fragments per group
8192 inodes per group
Superblock backups stored on blocks: 
        32768, 98304, 163840, 229376, 294912, 819200, 884736, 1605632, 2654208, 
        4096000, 7962624, 11239424, 20480000, 23887872

Allocating group tables: 完成                            
正在写入inode表: 完成                            
Creating journal (32768 blocks): 完成
Writing superblocks and filesystem accounting information: 完成   

[root@idc-v-71252 ~]# 
```

这样，我们就把刚刚分的区格式化成了ext4文件系统，输出信息中显示了inode和block数量等信息。

### 4、`mount`挂载文件系统

将格式化好的文件系统挂载至`/root/temp/tmp`

```sh
[root@idc-v-71252 tmp]# mount /dev/sdb1 /root/temp/tmp
[root@idc-v-71252 tmp]# df -h
文件系统                 容量  已用  可用 已用% 挂载点
/dev/mapper/centos-root   49G   14G   35G   28% /
devtmpfs                 3.9G     0  3.9G    0% /dev
tmpfs                    3.9G     0  3.9G    0% /dev/shm
tmpfs                    3.9G  8.5M  3.9G    1% /run
tmpfs                    3.9G     0  3.9G    0% /sys/fs/cgroup
/dev/sda1                497M  170M  328M   35% /boot
/dev/mapper/centos-home   24G   16G  7.6G   68% /home
tmpfs                    799M     0  799M    0% /run/user/0
/dev/sdb1                 99G   61M   94G    1% /root/temp/tmp
[root@idc-v-71252 tmp]# 
```

可以看到新分区已经可以使用了，在格式化时，系统会将磁盘上一定空间(此处是5%)保留做其他用途，可以使用命令`dumpe2fs /dev/sdb1 2>/dev/null|grep 'Reserved block count'`查看保留块数量。

这样挂载的分区只是临时有效，当系统重启时，并不会自动挂载该分区。如需要永久生效，可以将分区信息写入分区配置文件`/etc/fstab`

```sh
[root@idc-v-71252 ~]# cat /etc/fstab 

#
# /etc/fstab
# Created by anaconda on Fri Jan 15 00:59:59 2016
#
# Accessible filesystems, by reference, are maintained under '/dev/disk'
# See man pages fstab(5), findfs(8), mount(8) and/or blkid(8) for more info
#
/dev/mapper/centos-root /                       xfs     defaults        0 0
UUID=10205c20-bd44-4991-8c84-7b38db63a581 /boot                   xfs     defaults        0 0
/dev/mapper/centos-home /home                   xfs     defaults        0 0
/dev/mapper/centos-swap swap                    swap    defaults        0 0
```

此文件中记录了原有分区及其挂载信息，#开头的行为注释行，其余行被分为6列：  
第一列表示文件系统  
第二列是挂载点  
第三列为文件系统类型  
第四列为选项  
第五列表示是否对该文件系统使用dump工具备份，0表示不备份  
第六列表示是否使用fsck工具对该文件系统做定时检查，0表示不检查  

在文件中追加如下信息后，系统重启时新分区也会被自动挂载：

```sh
/dev/sdb1               /root/temp/tmp          ext4    defaults        0 0
```

在使用`mount`命令挂载时，可以使用选项`-o options`指定挂载选项(/etc/fstab中第四列)  
如对已挂载的新分区重新以只读方式挂载：

```sh
[root@idc-v-71252 home]# mount -o remount,ro /dev/sdb1
[root@idc-v-71252 home]# cd /root/temp/tmp
[root@idc-v-71252 tmp]# touch 1
touch: 无法创建"1": 只读文件系统
[root@idc-v-71252 tmp]# 
```

此时再在目录/root/temp/tmp中创建文件时显示报错：`只读文件系统`

```sh
[root@idc-v-71252 tmp]# mount -o remount,rw /dev/sdb1
[root@idc-v-71252 tmp]# touch 2
[root@idc-v-71252 tmp]# ls
2  lost+found
[root@idc-v-71252 tmp]# 重新以读写方式挂载后可以创建文件
```

配置文件中的`defaults`指的是选项：rw, suid, dev, exec, auto, nouser, 和 async. 它们的意思请查看mount的man手册   
选项`-a`表示读取配置文件中所有记录并重新挂载  
选项`-B`或`--bind`可以使一个目录挂载至另一个目录  

```sh
[root@idc-v-71252 tmp]# ls -l /opt/
总用量 0
[root@idc-v-71252 tmp]# 
[root@idc-v-71252 tmp]# mount --bind /root/temp/tmp /opt
[root@idc-v-71252 tmp]# ls /opt -l
总用量 16
-rw-r--r-- 1 root root     0 12月 13 14:44 2
drwx------ 2 root root 16384 12月 13 12:54 lost+found
[root@idc-v-71252 tmp]#
```

这样挂载的目录使用`df`命令并不能查看到，可以使用`mount`命令查看

```sh
[root@idc-v-71252 tmp]# mount | grep /dev/sdb1
/dev/sdb1 on /root/temp/tmp type ext4 (rw,relatime,data=ordered)
/dev/sdb1 on /opt type ext4 (rw,relatime,data=ordered)
```

选项`-t`表示指定文件系统类型，如挂载光盘：

```sh
[root@centos7 tmp]# mount -t iso9660 /dev/cdrom /mnt
mount: /dev/sr0 写保护，将以只读方式挂载
[root@centos7 tmp]# 
#或者挂载NFS文件系统(x.x.x.x是NFS服务器IP地址)
mount -t nfs x.x.x.x:/src_dir /path/to/local/dest_dir
```
### 5、`umount`卸载文件系统

卸载时既可以指定设备名也可以指定挂载点，当文件系统内有进程正在使用某文件时，卸载会报错：

```sh
[root@idc-v-71252 ~]# umount /root/temp/tmp
umount: /root/temp/tmp：目标忙。
        (有些情况下通过 lsof(8) 或 fuser(1) 可以
         找到有关使用该设备的进程的有用信息)
[root@idc-v-71252 ~]# 
```

此时可使用`lsof`或`fuser`找出进程(见[这里][1])，停止该进程之后再卸载即可。

如果是卸载光盘还可以用`eject`命令

```sh
[root@centos7 tmp]# eject
```
### 6、`fsck`检查并修复文件系统

可以使用`fsck`命令检查分区是否正常，需要在卸载的状态检查

```sh
[root@idc-v-71252 temp]# umount /dev/sdb1
[root@idc-v-71252 temp]# fsck /dev/sdb1
fsck，来自 util-linux 2.23.2
e2fsck 1.42.9 (28-Dec-2013)
/dev/sdb1: clean, 12/6553600 files, 459544/26214400 blocks
```

直接执行命令时，如果检测到受损，会有交互式提示询问是否进行修复坏块  
选项`-a`表示不询问直接修复  
选项`-y`表示总是对交互式询问输入yes  
### 7、`mkswap`创建swap分区

linux的swap分区可以用磁盘分区做，也可以用文件做，当前系统的swap使用的是分区。下面举一个使用文件创建swap分区的例子
首先使用命令`dd`生成一个大小为8G的文件

```
[root@idc-v-71252 tmp]# dd if=/dev/zero of=swapfile bs=1024K count=8192
记录了8192+0 的读入
记录了8192+0 的写出
8589934592字节(8.6 GB)已复制，35.1683 秒，244 MB/秒
[root@idc-v-71252 tmp]#
#命令会在当前目录下创建一个文件swapfile
#if表示指定读取的文件或设备
#of表示指定写入的文件或设备
#bs表示一次读出或写入的大小
#count表示读出或写入次数
[root@idc-v-71252 tmp]# du -sh swapfile 
8.0G    swapfile
```

创建swap分区

```sh
[root@idc-v-71252 tmp]# mkswap swapfile 
正在设置交换空间版本 1，大小 = 8388604 KiB
无标签，UUID=84fbe922-9444-482b-aa55-631ce72161c0
```
### 8、`swapon`/`swapoff`启用/停用swap文件或设备

```
[root@idc-v-71252 tmp]# swapon swapfile
swapon: /root/temp/tmp/swapfile：不安全的权限 0644，建议使用 0600。
[root@idc-v-71252 tmp]# free -m
              total        used        free      shared  buff/cache   available
Mem:           7983         115          53           8        7813        7794
Swap:         16255           0       16255
#此处看到swap分区已被扩大
[root@idc-v-71252 tmp]# swapoff swapfile
[root@idc-v-71252 tmp]# free -m
              total        used        free      shared  buff/cache   available
Mem:           7983         109          59           8        7813        7800
Swap:          8063           0        8063
```
### 9、`parted`磁盘分区工具

前面所述的`MBR`中的分区表不支持大于2TB以上的分区，为了解决这一限制和MBR的其它不足，出现了GTP(全局唯一标识分区表 GUID Partition Table)，是一种磁盘的分区表的结构布局的标准，属于`UEFI`(统一可扩展固件接口)标准的一部分。需要使用命令`parted`划分支持GTP的分区(可兼容MBR分区)。

直接使用命令`parted`时会进入交互界面

```sh
[root@idc-v-71252 ~]# parted /dev/sdb
GNU Parted 3.1
使用 /dev/sdb
Welcome to GNU Parted! Type 'help' to view a list of commands.
(parted)  
```

可以在提示符后输入`help`显示可用命令列表(命令可简写)  
命令`print`(简写p)表示打印分区表

```sh
(parted) p                                                                
Model: VMware Virtual disk (scsi)
Disk /dev/sdb: 215GB
Sector size (logical/physical): 512B/512B
Partition Table: msdos
Disk Flags: 

Number  Start   End    Size   Type     File system  标志
 1      1049kB  107GB  107GB  primary  ext4

(parted) 
```

命令`quit`表示退出交互界面   
选项`-s`表示非交互模式，此时命令写在后面

```sh
[root@idc-v-71252 ~]# parted -s /dev/sdb print
Model: VMware Virtual disk (scsi)
Disk /dev/sdb: 215GB
Sector size (logical/physical): 512B/512B
Partition Table: msdos
Disk Flags: 

Number  Start   End    Size   Type     File system  标志
 1      1049kB  107GB  107GB  primary  ext4

[root@idc-v-71252 ~]# fdisk -l /dev/sdb1

磁盘 /dev/sdb1：107.4 GB, 107374182400 字节，209715200 个扇区
Units = 扇区 of 1 * 512 = 512 bytes
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节

[root@idc-v-71252 ~]#
```
`Partition Table`后的`msdos`表示为MBR分区，之所以两个命令中sdb1分区大小显示为107G而不是100G是因为在进行计算时使用1000 bytes作为1KB计数。

不能在已经做MBR分区的硬盘上做GTP分区，重做会导致原有分区被格式化。

这里在新磁盘`/dev/sdc`上做GTP分区：

```sh
[root@idc-v-71252 ~]# parted /dev/sdc
GNU Parted 3.1
使用 /dev/sdc
Welcome to GNU Parted! Type 'help' to view a list of commands.
(parted) 
```

注意交互模式与`fdisk`命令不同，`parted`的命令一旦按回车确认，命令就马上执行，对磁盘的更改就立即生效。

命令`mklabel`指定分区格式(msdos或gtp)，如果格式未知，使用`print`命令时会报错：`错误: /dev/sdc: unrecognised disk label`

```sh
(parted) mklabel gpt
```

命令`mkpart`表示创建新分区，后面接`分区类型`(主分区还是扩展分区)、`文件系统类型`(ext4等，可省略)、`起始点`、`结束点`。

```sh
(parted) mkpart primary 0KB 100GB
警告: You requested a partition from 0.00B to 100GB (sectors 0..195312500).
The closest location we can manage is 17.4kB to 100GB (sectors 34..195312500).
Is this still acceptable to you?
是/Yes/否/No? yes                                                         
警告: The resulting partition is not properly aligned for best performance.
忽略/Ignore/放弃/Cancel? ignore                                           
(parted) p                                                                
Model: VMware Virtual disk (scsi)
Disk /dev/sdc: 215GB
Sector size (logical/physical): 512B/512B
Partition Table: gpt
Disk Flags: 

Number  Start   End    Size   File system  Name     标志
 1      17.4kB  100GB  100GB               primary
```

命令`rm`表示删除分区，后面接分区号

```sh
(parted) rm 1                                                             
(parted) p                                                                
Model: VMware Virtual disk (scsi)
Disk /dev/sdc: 215GB
Sector size (logical/physical): 512B/512B
Partition Table: gpt
Disk Flags: 

Number  Start  End  Size  File system  Name  标志

(parted) 
```

下面使用非交互模式继续

```
[root@idc-v-71252 ~]# parted -s /dev/sdc mkpart primary ext4 18KB 100GB 
警告: The resulting partition is not properly aligned for best performance.
[root@idc-v-71252 ~]# parted -s /dev/sdc print
Model: VMware Virtual disk (scsi)
Disk /dev/sdc: 215GB
Sector size (logical/physical): 512B/512B
Partition Table: gpt
Disk Flags: 

Number  Start   End    Size   File system  Name     标志
 1      17.9kB  100GB  100GB               primary
```

这里使用1000 bytes作为1KB计数   
格式化并挂载(部分输出略)

```sh
[root@idc-v-71252 temp]# mkfs.ext4 /dev/sdc1
[root@idc-v-71252 temp]# mount /dev/sdc1 /root/temp/tmp_1
[root@idc-v-71252 temp]# df -h|grep ^/dev
/dev/mapper/centos-root   49G   22G   27G   44% /
/dev/sda1                497M  170M  328M   35% /boot
/dev/mapper/centos-home   24G   16G  7.6G   68% /home
/dev/sdb1                 99G   61M   94G    1% /root/temp/tmp
/dev/sdc1                 92G   61M   87G    1% /root/temp/tmp_1
```

最后再用`parted`做一个MBR扩展分区，命令如下：

```sh
parted -s /dev/sdd mklabel msdos
parted -s /dev/sdd mkpart extended 100GB 100%
parted -s /dev/sdd mkpart logical 100GB 200GB
```

结果显示为：

```sh
[root@idc-v-71252 temp]# parted -s /dev/sdd print
Model: VMware Virtual disk (scsi)
Disk /dev/sdd: 215GB
Sector size (logical/physical): 512B/512B
Partition Table: msdos
Disk Flags: 

Number  Start  End    Size   Type      File system  标志
 1      100GB  215GB  115GB  extended               lba
 5      100GB  200GB  100GB  logical

[root@idc-v-71252 temp]# fdisk -l /dev/sdd

磁盘 /dev/sdd：214.7 GB, 214748364800 字节，419430400 个扇区
Units = 扇区 of 1 * 512 = 512 bytes
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节
磁盘标签类型：dos
磁盘标识符：0x0006d495

     设备 Boot      Start         End      Blocks   Id  System
/dev/sdd1       195311616   419430399   112059392    f  W95 Ext\'d (LBA)
/dev/sdd5       195313664   390625279    97655808   83  Linux
```

格式化及挂载(省略部分输出)

```sh
[root@idc-v-71252 temp]# mkfs.ext4 /dev/sdd5
[root@idc-v-71252 temp]# mount /dev/sdd5 /root/temp/tmp_2
[root@idc-v-71252 temp]# df -h|grep ^/dev
/dev/mapper/centos-root   49G   22G   27G   44% /
/dev/sda1                497M  170M  328M   35% /boot
/dev/mapper/centos-home   24G   16G  7.6G   68% /home
/dev/sdb1                 99G   61M   94G    1% /root/temp/tmp
/dev/sdc1                 92G   61M   87G    1% /root/temp/tmp_1
/dev/sdd5                 92G   61M   87G    1% /root/temp/tmp_2
```

这些新分区都可以写入配置文件`/etc/fstab`中实现重启后自动挂载

##`LVM`逻辑卷管理
`LVM`是linux环境下对磁盘分区进行管理的一种机制，能够使系统管理员更方便的为应用与用户分配存储空间。

### 术语
`物理存储介质`(The physical media)：指的是系统的存储设备，如上面制作的分区/dev/sdb1、/dev/sdc1、/dev/sdd5   
`物理卷`(PV: Physical Volume)：相当于物理存储介质，但添加了与LVM相关的管理参数。   
`卷组`(VG: Volume Group)：由一个或多个物理卷组成。   
`逻辑卷`(LV: Logical Volume)：在卷组的基础上划分的逻辑分区(文件系统)。   
`PE`(physical extent)：每一个物理卷被划分为称为PE的基本单元，具有唯一编号的PE是可以被LVM寻址的最小单元。PE的大小是可配置的，默认为4MB。   
`LE`(logical extent)：逻辑卷也被划分为被称为LE的可被寻址的基本单位。在同一个卷组中，LE的大小和PE是相同的，并且一一对应。   

### 步骤

**`1、创建分区`** 
可以使用`fdisk`或`parted`进行分区，和前面举例中的区别仅仅是分区类型要选8e。这里将三块新硬盘的剩余空间做成LVM分区，parted方式(仅举一例，其余略)：

```sh
parted -s /dev/sdb mkpart primary 107GB 100%
parted -s /dev/sdb toggle 2 lvm  #表示将第二个分区定义为lvm类型(8e)
```

**`2、创建PV`** 

```sh
[root@idc-v-71252 ~]# pvcreate /dev/sd[bcd]2
  Physical volume "/dev/sdb2" successfully created
  Physical volume "/dev/sdc2" successfully created
  Physical volume "/dev/sdd2" successfully created
[root@idc-v-71252 ~]# 
#查看
[root@idc-v-71252 ~]# pvscan 
  PV /dev/sda2   VG centos   lvm2 [79.51 GiB / 64.00 MiB free]
  PV /dev/sdb2               lvm2 [100.00 GiB]
  PV /dev/sdc2               lvm2 [106.87 GiB]
  PV /dev/sdd2               lvm2 [93.13 GiB]
  Total: 4 [379.50 GiB] / in use: 1 [79.51 GiB] / in no VG: 3 [300.00 GiB]
```

**`3、创建VG`** 

```sh
[root@idc-v-71252 ~]# vgcreate -s 8M test_lvm /dev/sd[bcd]2
  Volume group "test_lvm" successfully created
#这里使用选项-s指定PE大小为8M，卷组起名为test_lvm
#查看
[root@idc-v-71252 ~]# vgscan 
  Reading all physical volumes.  This may take a while...
  Found volume group "centos" using metadata type lvm2
  Found volume group "test_lvm" using metadata type lvm2
```

**`4、创建LV`** 

```sh
[root@idc-v-71252 ~]# lvcreate -n test_1 -L 50G test_lvm 
  Logical volume "test_1" created.
[root@idc-v-71252 ~]# 
#选项-n指定LV名为test_1，-L指定大小，也可以用选项-l指定LE的数量
#查看
[root@idc-v-71252 ~]# lvscan 
  ACTIVE            '/dev/centos/swap' [7.88 GiB] inherit
  ACTIVE            '/dev/centos/home' [23.48 GiB] inherit
  ACTIVE            '/dev/centos/root' [48.09 GiB] inherit
  ACTIVE            '/dev/test_lvm/test_1' [50.00 GiB] inherit
[root@idc-v-71252 ~]# 
```

**`5、格式化及挂载`** 

```sh
#在这里进行格式化，第一步分区之后并不需要格式化。
#这里我们格式化成xfs格式
[root@idc-v-71252 ~]# mkfs.xfs /dev/test_lvm/test_1
meta-data=/dev/test_lvm/test_1   isize=256    agcount=4, agsize=3276800 blks
         =                       sectsz=512   attr=2, projid32bit=1
         =                       crc=0        finobt=0
data     =                       bsize=4096   blocks=13107200, imaxpct=25
         =                       sunit=0      swidth=0 blks
naming   =version 2              bsize=4096   ascii-ci=0 ftype=0
log      =internal log           bsize=4096   blocks=6400, version=2
         =                       sectsz=512   sunit=0 blks, lazy-count=1
realtime =none                   extsz=4096   blocks=0, rtextents=0
[root@idc-v-71252 ~]# mount /dev/test_lvm/test_1 /root/temp/test_1
[root@idc-v-71252 ~]# df -h|grep ^/dev
/dev/mapper/centos-root       49G   22G   27G   44% /
/dev/sda1                    497M  170M  328M   35% /boot
/dev/mapper/centos-home       24G   16G  7.6G   68% /home
/dev/sdb1                     99G   61M   94G    1% /root/temp/tmp
/dev/sdc1                     92G   61M   87G    1% /root/temp/tmp_1
/dev/sdd5                     92G   61M   87G    1% /root/temp/tmp_2
/dev/mapper/test_lvm-test_1   50G   33M   50G    1% /root/temp/test_1
```

这里文件系统之所以显示为`/dev/mapper/....`是因为内核利用Mapper Device机制将设备做了映射：

```sh
[root@idc-v-71252 ~]# ls -l /dev/mapper/test_lvm-test_1
lrwxrwxrwx 1 root root 7 12月 14 09:58 /dev/mapper/test_lvm-test_1 -> ../dm-3
[root@idc-v-71252 ~]# ls -l /dev/test_lvm/test_1
lrwxrwxrwx 1 root root 7 12月 14 09:58 /dev/test_lvm/test_1 -> ../dm-3
```

实际上`/dev/test_lvm/test_1`和`/dev/mapper/test_lvm-test_1`指向了同一个设备`/dev/dm-3`(在配置文件`/etc/fstab`中写任意一种都可以)，这里就不对映射机制做详细展开了。
### 命令

前面举例中说到了几个创建和查看命令，除此之外，LVM还有一系列的命令，它们都以pv/vg/lv开头，所起的作用大多是增加、删除、扩充、缩减、查看、改变等等。

**`创建命令`** 

```sh
pvcreate vgcreate lvcreate
```

**`查看命令`** 分三类，显示信息侧重或详细程度不同：

```sh
pvs pvscan pvdisplay
vgs vgscan vgdisplay
lvs lvscan lvdisplay
```

**`改变属性`** (分别改变本层次上对象的属性)

```sh
pvchange vgchange lvchange
```

**`扩容`** 

```sh
vgextend lvextend
```

扩容LV举例(注意内核可能不支持对某些文件系统的在线扩容，此时需要先将文件系统卸载)：

```sh
[root@idc-v-71252 dev]# lvextend -L +10G /dev/test_lvm/test_1
  Size of logical volume test_lvm/test_1 changed from 50.00 GiB (6400 extents) to 60.00 GiB (7680 extents).
  Logical volume test_1 successfully resized.
[root@idc-v-71252 ~]# df -h /dev/mapper/test_lvm-test_1
文件系统                     容量  已用  可用 已用% 挂载点
/dev/mapper/test_lvm-test_1   50G   33M   50G    1% /root/temp/test_1
#此时扩容还没有生效，使用xfs_growfs对xfs文件系统进行在线扩容
[root@idc-v-71252 dev]# xfs_growfs /dev/test_lvm/test_1
meta-data=/dev/mapper/test_lvm-test_1 isize=256    agcount=4, agsize=3276800 blks
         =                       sectsz=512   attr=2, projid32bit=1
         =                       crc=0        finobt=0
data     =                       bsize=4096   blocks=13107200, imaxpct=25
         =                       sunit=0      swidth=0 blks
naming   =version 2              bsize=4096   ascii-ci=0 ftype=0
log      =internal               bsize=4096   blocks=6400, version=2
         =                       sectsz=512   sunit=0 blks, lazy-count=1
realtime =none                   extsz=4096   blocks=0, rtextents=0
data blocks changed from 13107200 to 15728640
[root@idc-v-71252 ~]# df -h /dev/mapper/test_lvm-test_1
文件系统                     容量  已用  可用 已用% 挂载点
/dev/mapper/test_lvm-test_1   60G   33M   60G    1% /root/temp/test_1
```

ext系列的文件系统扩容时需要使用命令`resize2fs`进行在线扩容

**`缩减`** (慎用)

```sh
vgreduce lvreduce
```

**`改名`** 

```sh
vgrename lvrename
```

还有一些其他命令这里就不再列出了，关于它们的用法请查看相关手册

本文简要介绍了磁盘和LVM相关的管理命令，另外，还有一个介于物理磁盘和磁盘分区的中间层：`RAID`(独立冗余磁盘阵列)，它提供磁盘级别的数据冗余能力。当前服务器上一般都有RAID卡(硬件)，关于它的设置以及原理就不在此叙述了，请搜索相关文档。

[1]: https://segmentfault.com/a/1190000007649899#articleHeader6
[0]: ./img/bVGWLz.png