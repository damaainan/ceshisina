### df 显示文件系统磁盘空间使用量


#### df(disk free)

**功能说明：**显示磁盘的相关信息。

**语 法：**df [-ahHiklmPT][–block-size=<区块大小>][-t <文件系统类型>][-x <文件系统类型>][–help][–no-sync][–sync][–version][文件或设备]

**补充说明：**df可显示磁盘的文件系统与使用情形。

**参 数：**  
-a或–all 包含全部的文件系统。  
–block-size=<区块大小> 以指定的区块大小来显示区块数目。  
-h或–human-readable 以可读性较高的方式来显示信息。  
-H或–si 与-h参数相同，但在计算时是以1000 Bytes为换算单位而非1024 Bytes。  
-i或–inodes 显示inode的信息。  
-k或–kilobytes 指定区块大小为1024字节。  
-l或–local 仅显示本地端的文件系统。  
-m或–megabytes 指定区块大小为1048576字节。  
–no-sync 在取得磁盘使用信息前，不要执行sync指令，此为预设值。  
-P或–portability 使用POSIX的输出格式。  
–sync 在取得磁盘使用信息前，先执行sync指令。  
-t<文件系统类型>或–type=<文件系统类型> 仅显示指定文件系统类型的磁盘信息。  
-T或–print-type 显示文件系统的类型。  
-x<文件系统类型>或–exclude-type=<文件系统类型> 不要显示指定文件系统类型的磁盘信息。  
–help 显示帮助。  
–version 显示版本信息。  
[文件或设备] 指定磁盘设备。



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

选项-h作用是转换数字的显示单位(默认为KB)。  
显示信息文件系统列下面带tmpfs字样的是虚拟内存文件系统(此处不做展开)。  
文件系统/dev/mapper/centos-root的挂载点是/(根目录)，即通常所说的根分区(或根文件系统)；/dev/sda1(boot分区)中保存了内核映像和一些启动时需要的辅助文件；另外，还对用户家目录单独做了分区(/dev/mapper/centos-home)。  
在linux中还可以做一个特殊的分区：swap分区(交换分区)。作用是：当系统的物理内存不够用时，会将物理内存中一部分暂时不使用的数据交换至swap分区中，当需要使用这些数据时，再从swap空间交换回内存空间。swap在功能上突破了物理内存的限制，使程序可以操纵大于实际物理内存的空间。但由于硬盘的速度远远低于内存，使swap只能作为物理内存的辅助。通常swap空间的大小是实际物理内存大小的1到2倍。使用命令free可以查看swap空间的大小。

选项-i显示inode信息

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

这里显示的数字是该文件系统中inode数量的使用情况。