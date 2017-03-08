# linux 运维必备150个命令

```sh
线上查询及帮助命令（1个）
man
目录操作命令（6个）
ls tree pwd mkdir rmdir cd
文件操作命令（7个）
touch cp mv rm ln find rename
文件查看及处理命令（21个）
cat more less head tac head tail cut paste
sort uniq wc iconv dos2unix file diff tree chattr
lsattr rev vimdiff
文件打包压缩命令（3个）
gzip tar unzip
信息显示命令（12个）
uname hostname dmesg uptime file stat du df top free w date
搜索文件命令（4个）
find which whereis locate
用户管理命令（10个）
useradd userdel passwd chage usermod id su sudo visudo
groupadd
基本网络操作命令（10个）
telnet ssh scp wget ping route ifconfig ifup ifdown netstat
深入网络操作命令（6个）
route mail mutt nslookup dig wget
有关磁盘空间的命令（6个）
mount umount df du fsck dd
关机和查看系统信息的命令（7个）
shutdown reboot ps top kill date
安装和登录命令（3个）
shutdown halt reboot
系统管理相关命令（9个）
top free vmstat mpstat iostat sar kill chkconfig last
系统安全相关命令（13个）
passwd su sudo umask chgrp chmod chown chattr lsattr ps
whoami
查看系统用户登陆信息命令（6个）
w who users last lastlog fingers
查看硬件相关命令（6个）
ethtool mii-tool dmidecode dmesg lspci
其它（14个）
chkconfig echo yum watch alias unalias date clear history eject
time nohup nc xargs
监视物理组件的高级 Linux命令
内存:top free vmstat mpstat iostat sar
CPU:top vmstat mpstat iostat sar
I/O:vmstat mpstat iostat sar
进程:ipcs ipcrm
负载:uptime
以上命令属于武功里的《九阴真经》，如果掌握好了，会非常牛。
关机/重启/注销命令
关机:
shutdown -h now ——>立刻关机(生产常用)
shhutdown -h +1 ——>1 分钟以后关机
init 0
halt ——>立即停止系统，需要人工关闭电源
halt -p
poweroff ——>立即停止系统，并且关闭电源
重启:
reboot(生产常用)
shutdown -r now(生产常用)
shhutdown -r +1 ——>1 分钟以后重起
init 6
注销
logout
exit(生产常用)
ctl+d ——>快捷键(生产常用)
进程管理：（16个）
bg：后台运行 fg：挂起程序 jobs：显示后台程序 kill,killall,pkill：杀掉进程
crontab：设置定时 ps：查看进程 pstree：显示进程状态树
top：显示进程 nice：改变优先权 nohup：用户退出系统之后继续工作
pgrep：查找匹配条件的进程 strace：跟踪一个进程的系统调用
ltrace：跟踪进程调用库函数的情 vmstat：报告虚拟内存统计信息
危险的系统命令：
mv rm dd fdisk parted
linux 四剑客（4 个）
grep egrep sed awk

```

