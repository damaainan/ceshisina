 **badblocks**命令-->用来检查坏道位置的工具，用来检查磁盘中损坏的区块

![][0]

**说明:** 如何检查硬盘损坏区域并防止使用这部分区域

步骤1: 使用fdisk命令识别硬盘信息  
    
    # fdisk -l  
步骤2: 扫描硬盘的损坏扇区或区块  

    # badblocks -v /dev/sdb > /tmp/bad-blocks.txt  
步骤3: 提示操作系统不要使用损坏区块存储  

    # e2fsck -l /tmp/bad-blocks.txt /dev/sdb

备注: 执行e2fsck命令前，需要先挂载设备

[0]: ./img/20160806153900020.png