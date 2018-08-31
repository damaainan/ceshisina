## Linux查看分区文件系统类型总结

来源：[http://www.importnew.com/29492.html](http://www.importnew.com/29492.html)

时间 2018-08-18 11:53:35

 
在Linux 中如何查看分区的文件系统类型，下面总结几种查看分区文件系统类型的方法。
 
### 1、df -T 命令查看
 
这个是最简单的命令，文件系统类型在Type列输出。只可以查看已经挂载的分区和文件系统类型。如下所示：

```
[root@mylnx008 ~]# df -T /dev/sdb
Filesystem     Type 1K-blocks    Used Available Use% Mounted on
/dev/sdb       xfs  315467264 4356404 311110860   2% /mysql

[root@mylnx008 ~]# df -T
Filesystem     Type     1K-blocks     Used Available Use% Mounted on
/dev/sda2      xfs       30929148 22455300   8473848  73% /
devtmpfs       devtmpfs   1746644        0   1746644   0% /dev
tmpfs          tmpfs      1757220        0   1757220   0% /dev/shm
tmpfs          tmpfs      1757220    24868   1732352   2% /run
tmpfs          tmpfs      1757220        0   1757220   0% /sys/fs/cgroup
/dev/sda1      xfs         508580    63024    445556  13% /boot
/dev/sdc1      ext4     139203080  8699072 123409840   7% /mnt/resource
tmpfs          tmpfs       351448        0    351448   0% /run/user/1000
/dev/sdb       xfs      315467264  4356404 311110860   2% /mysql
```
 
### 2、parted -l命令查看
 
如下所示，parted -l 命令会输出文件系统类型（File system）， 其中参数l表示列出所有设备的分区信息。

```
[root@DB-Server ~]# parted -l

Model: ATA ST500DM002-1BD14 (scsi)
Disk /dev/sda: 500GB
Sector size (logical/physical): 512B/512B
Partition Table: msdos

Number  Start   End    Size   Type     File system  Flags
 1      32.3kB  107MB  107MB  primary  ext3         boot 
 2      107MB   500GB  500GB  primary               lvm
```
 
![][0]

 
### 3、blkid命令查看
 
查看已格式化分区的UUID和文件系统。使用blkid可以输出分区或分区的文件系统类型，查看TYPE字段输出。

```
[root@DB-Server ~]# blkid
/dev/mapper/VolGroup00-LogVol01: TYPE="swap" 
/dev/mapper/VolGroup00-LogVol00: UUID="1c0d5470-1503-4a18-b184-53483466d948" TYPE="ext3" 
/dev/sda1: LABEL="/boot" UUID="582b189c-396c-4da8-a7a3-1effaa3e4000" TYPE="ext3" 
/dev/VolGroup00/LogVol00: UUID="1c0d5470-1503-4a18-b184-53483466d948" TYPE="ext3" 
/dev/VolGroup00/LogVol01: TYPE="swap" 
/dev/mapper/VolGroup00-LogVol03: UUID="f037ba1e-77a1-439a-8a10-b78c3cca68ec" SEC_TYPE="ext2" TYPE="ext3" 
[root@DB-Server ~]# blkid  /dev/sda1
/dev/sda1: LABEL="/boot" UUID="582b189c-396c-4da8-a7a3-1effaa3e4000" TYPE="ext3"
```
 
![][1]
 
### 4、命令lsblk -f 查看
 
有些系统可能没有这个命令，需要安装。注意：lsblk -f也可以查看未挂载的文件系统类型

```
[root@mylnx008 ~]# lsblk -f
NAME   FSTYPE LABEL UUID                                 MOUNTPOINT
fd0                                                      
sda                                                      
├─sda1 xfs          b98659b2-5f8c-493e-9304-658905ef1391 /boot
└─sda2 xfs          b7559ac5-b3a4-4b00-b98a-a2a2611806d0 /
sdb    xfs          6fcc5417-3c1b-4c71-aac7-344bac7654a4 /mysql
sdc                                                      
└─sdc1 ext4         1ad7da45-2366-4c4f-acd4-484600c4153a /mnt/resource
```
 
![][2]


[0]: ./img/vEfamuA.png
[1]: ./img/bIjuIjA.png
[2]: ./img/eiqiAjQ.png