## ubuntu18的宽带连接问题

来源：[http://www.jianshu.com/p/79e290d538d9](http://www.jianshu.com/p/79e290d538d9)

时间 2018-04-13 18:27:09

 
我的ubuntu18现在 Adsl连接出现问题，在网络哪里找不到宽带连接，做一个问题解决方案。
 
  
这是参考文献:
 
[ubuntu下pppoe拨号][3]
 
[ubuntu的ADSL拨号上网主要是无线网情况下][4]
 
[ADSL（PPPOE）接入指南][5]
 
[ubuntu 16.04 上不了网？DSL消失？更新网卡驱动？][6]
 
[Ubuntu16.04怎么设置宽带连接][7]
 
[ubuntu16.04拨号上网及无线驱动安装][8]
 
[Ubuntu更换网卡驱动][9]
 
  [升级Ubuntu 16.04 LTS后 DSL拨号上网(ppp)连接自动断开解决办法][10] 
 
 
ubuntu 18网络解决年，按照上面的都尝试了一下，不过为根目录太小，有大佬说一下，或者远程协助为扩容一下吗？ 菜鸟一个。

```
dflx@dflx:~/下载$ df -h
文件系统        容量  已用  可用 已用% 挂载点
udev            1.9G     0  1.9G    0% /dev
tmpfs           381M   15M  367M    4% /run
/dev/sda8       9.4G  6.5G  2.5G   73% /
tmpfs           1.9G   32M  1.9G    2% /dev/shm
tmpfs           5.0M  4.0K  5.0M    1% /run/lock
tmpfs           1.9G     0  1.9G    0% /sys/fs/cgroup
/dev/loop0      3.4M  3.4M     0  100% /snap/gnome-system-monitor/36
/dev/loop2       13M   13M     0  100% /snap/gnome-characters/69
/dev/loop1       87M   87M     0  100% /snap/core/4407
/dev/loop3      1.7M  1.7M     0  100% /snap/gnome-calculator/154
/dev/loop4      141M  141M     0  100% /snap/gnome-3-26-1604/59
/dev/loop5       21M   21M     0  100% /snap/gnome-logs/25
/dev/loop6       83M   83M     0  100% /snap/core/4327
/dev/sda1       196M   30M  167M   15% /boot/efi
tmpfs           381M   48K  381M    1% /run/user/1000
```

![][0]

 
图片.png

 
我的23gb和51gb是原来，ubuntu17的/home和/usr，但是为这次在元基础上，用u盘升级，结果成这样年，为准备扩容失败了。
 
难道要把这70gb作为移动硬盘年，它们需要mount才能使用。

```
dflx@dflx:~/下载$ sudo fdisk -l
Disk /dev/loop0：3.3 MiB，3411968 字节，6664 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节


Disk /dev/loop1：86.5 MiB，90726400 字节，177200 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节


Disk /dev/loop2：12.2 MiB，12804096 字节，25008 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节


Disk /dev/loop3：1.6 MiB，1691648 字节，3304 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节


Disk /dev/loop4：140 MiB，146841600 字节，286800 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节


Disk /dev/loop5：21 MiB，22003712 字节，42976 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节


Disk /dev/loop6：82 MiB，86011904 字节，167992 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 512 字节
I/O 大小(最小/最佳)：512 字节 / 512 字节


Disk /dev/sda：477 GiB，512110190592 字节，1000215216 个扇区
单元：扇区 / 1 * 512 = 512 字节
扇区大小(逻辑/物理)：512 字节 / 4096 字节
I/O 大小(最小/最佳)：4096 字节 / 4096 字节
磁盘标签类型：gpt
磁盘标识符：A6B6EFE4-9402-4DF5-896E-F7A001778046

设备            起点       末尾      扇区   大小 类型
/dev/sda1       2048     411647    409600   200M EFI 系统
/dev/sda2    2508800    2541567     32768    16M Microsoft 保留
/dev/sda3    2541568  538841087 536299520 255.7G Microsoft 基本数据
/dev/sda4  538841088  811468799 272627712   130G Microsoft 基本数据
/dev/sda5  811470848  817864703   6393856   3.1G Microsoft 基本数据
/dev/sda6  817864704  818864127    999424   488M EFI 系统
/dev/sda7  818864128  834865151  16001024   7.6G Linux swap
/dev/sda8  834865152  854865919  20000768   9.6G Linux 文件系统
/dev/sda9  854865920  954865663  99999744  47.7G Linux 文件系统
/dev/sda10 954865664 1000214527  45348864  21.6G Linux 文件系统
```
 
这环境就安装了java9

```
dflx@dflx:~/下载$ java Test
hello world
```
 
以及gcc

```
dflx@dflx:~/下载$ gcc --version
gcc (Ubuntu 7.3.0-15ubuntu2) 7.3.0
Copyright (C) 2017 Free Software Foundation, Inc.
This is free software; see the source for copying conditions.  There is NO
warranty; not even for MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
```
 
以及自带的python

```
dflx@dflx:~/下载$ python
python      python2.7   python3.6   python3m    
python2     python3     python3.6m
```

![][1]

 
图片.png

 
ubuntu18预览

![][2]

 
图片.png


[3]: https://link.jianshu.com?t=https%3A%2F%2Fblog.csdn.net%2Fselous%2Farticle%2Fdetails%2F55520765
[4]: https://link.jianshu.com?t=http%3A%2F%2Fwww.jb51.net%2Fos%2FUbuntu%2F34933.html
[5]: https://link.jianshu.com?t=http%3A%2F%2Fwiki.ubuntu.org.cn%2FADSL%25EF%25BC%2588PPPOE%25EF%25BC%2589%25E6%258E%25A5%25E5%2585%25A5%25E6%258C%2587%25E5%258D%2597
[6]: https://link.jianshu.com?t=https%3A%2F%2Fblog.csdn.net%2Fheimu24%2Farticle%2Fdetails%2F78316823
[7]: https://link.jianshu.com?t=https%3A%2F%2Fjingyan.baidu.com%2Farticle%2F925f8cb8d5df07c0dce05671.html
[8]: https://link.jianshu.com?t=https%3A%2F%2Fblog.csdn.net%2Fessity%2Farticle%2Fdetails%2F52618101
[9]: https://link.jianshu.com?t=https%3A%2F%2Fblog.csdn.net%2Fpiscesq329a%2Farticle%2Fdetails%2F50191035
[10]: https://link.jianshu.com?t=http%3A%2F%2Fwww.cnblogs.com%2FBlackStorm%2Fp%2F5475189.html
[0]: ./img/mA7RjyF.png
[1]: ./img/f6NVri3.png
[2]: ./img/6rI7nyQ.png