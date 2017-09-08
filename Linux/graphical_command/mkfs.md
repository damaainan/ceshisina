 **mkfs命令** **-->make filesystem的缩写；用来在特定的分区建立Linux文件系统**

 ****

 【**命令作用** 】

 该命令用来在特定的分区创建linux文件系统，常见的文件系统有ext2,ext3,vfat等， 执行mkfs命令其实是在调用:mkfs.ext3 | mkfs.reiserfs | mkfs.ext2 | mkdosfs | mkfs.msdos | mkfs.vfat

 

![][0]

 比如：
   
    mkfs.ext3 /dev/sda6  # 把该设备格式化成ext3文件系统  
    mke2fs -j /dev/sda6  # 把该设备格式化成ext3文件系统  
    mkfs.reiserfs /dev/sda6  # 格式化成reiserfs文件系统  
    mkfs.vfat /dev/sda6  # 格式化成fat32文件系统  
    mkfs.msdos /dev/sda6 # 格式化成fat16文件系统,msdos就是fat16  
    mkdosfs /dev/sda6  # 格式化成msdos文件系统

 

![][1]

[0]: ./img/20170429204031352.png
[1]: ./img/20170429204320824.png