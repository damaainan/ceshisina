 **dd命令** **-->dd是disk dump的缩写，指定大小的块拷贝一个文件，同时进行指定的转换，起到一个初始化磁盘的作用**

****

【**命令作用**】

cp就是复制文件和目录。有使用软/硬链接的选项，保持权限的选项，失败时删掉目标再重试的选项，目标存在时询问的选项，等等。但是怎么写数据它自己说了算，不给你多少选项。dd是把数据从一个文件写到另一个文件，不支持多个文件和目录。只管数据，不管文件本身的各种属性和特性。写数据时它可以指定进行转换、从指定位置开始、指定每次写的大小（块大小）、指定写入多少块，等等

![][0]

【 **测试硬盘的读写速度** 】

测试读速度: dd if=/dev/zero bs=1024 count=1000000 f=/root/1Gb.file   
测试写速度: dd if=/root/1Gb.file bs=64k | dd f=/dev/null   
通过以上两个命令输出的命令执行时间，可以计算出硬盘的读、写速度

为了获得精确的读测试数据，首先在测试前运行下列命令，来将缓存设置为无效：  

    $ flush  
    $ echo 3 | tee /proc/sys/vm/drop_caches  
    $ time dd if=/path/to/bigfile of=/dev/null bs=8k

【 **确定硬盘的最佳块大小** 】   

     $ dd if=/dev/zero bs=1024 count=1000000 of=/root/1Gb.file  
     $ dd if=/dev/zero bs=2048 count=500000 of=/root/1Gb.file  
     $ dd if=/dev/zero bs=4096 count=250000 of=/root/1Gb.file  
     $ dd if=/dev/zero bs=8192 count=125000 of=/root/1Gb.file  
通过比较以上命令输出中所显示的命令执行时间,即可确定系统最佳的块大小

【**如何修复系统盘** 】

    $ dd if=/dev/sda of=/dev/sda

当硬盘较长时间(比如1,2年)放置不使用后,磁盘上会产生magnetic fluxpoint。当磁头读到这些区域时会遇到困难,并可能导致I/O错误。当这种情况影响到硬盘的第一个扇区时,可能导致硬盘报废。上边的命令有可能使这些数据起死回生。且这个过程是安全,高效的

【**常用设备** 】

    /dev/sda # SCSI硬盘

    /dev/hda # IDE硬盘

    /dev/null # 空设备,也称为位桶, 外号叫无底洞，你可以向它输出任何数据，它通吃，并且不会撑着！

    /dev/zero # 是一个输入设备,你可你用它来初始化文件。该设备无穷尽地提供字符串0

    /dev/random # 随机设备

    /dev/urandom # 另外一个随机设备

[0]: ./img/20170625175530170.png