### PS 命令是什么

ps命令能够给出当前系统中进程的快照。它能捕获系统在某一事件的进程状态。如果你想不断更新查看的这个状态，可以使用top命令。

### 1. 不加参数执行ps命令

这是一个基本的 **ps** 使用。在控制台中执行这个命令并查看结果。
```
  $ ps  
  PID TTY          TIME CMD  
  1476 pts/0    00:00:00 ps  
  29392 pts/0    00:00:00 bash
```
* PID: 运行着的命令(CMD)的进程编号
* TTY: 命令所运行的位置（终端）
* TIME: 运行着的该命令所占用的CPU处理时间
* CMD: 该进程所运行的命令

### 2. 显示所有当前进程

使用 **-a** 参数。**-a 代表 all**。同时加上x参数会显示没有控制终端的进程。

    $ ps -ax

这个命令的结果或许会很长。为了便于查看，可以结合less命令和管道来使用。
```
  $ ps -ax | less  
  PID TTY      STAT   TIME COMMAND  
    1 ?        Ss     0:05 /usr/lib/systemd/systemd --switched-root --system --deserialize 23  
    2 ?        S      0:00 [kthreadd]  
    3 ?        S      0:00 [ksoftirqd/0]  
    5 ?        S<     0:00 [kworker/0:0H]  
    7 ?        S      0:00 [migration/0]  
    8 ?        S      0:00 [rcu_bh]  
    9 ?        S      0:00 [rcuob/0]  
   10 ?        S      0:00 [rcuob/1]  
   11 ?        S      0:00 [rcuob/2]  
   12 ?        S      0:00 [rcuob/3]  
   13 ?        S      0:00 [rcuob/4]  
   14 ?        S      0:00 [rcuob/5]  
   15 ?        S      0:00 [rcuob/6]  
   16 ?        S      0:00 [rcuob/7]  
   17 ?        S      0:20 [rcu_sched]  
   18 ?        S      0:08 [rcuos/0]  
   19 ?        S      0:09 [rcuos/1]  
   20 ?        S      0:06 [rcuos/2]  
   21 ?        S      0:04 [rcuos/3]  
   22 ?        S      0:00 [rcuos/4]  
   23 ?        S      0:00 [rcuos/5]  
   24 ?        S      0:00 [rcuos/6]  
   25 ?        S      0:00 [rcuos/7]  
   26 ?        S      0:00 [watchdog/0]  
   27 ?        S      0:00 [watchdog/1]
```
### 3. 根据用户过滤进程

在需要查看特定用户进程的情况下，我们可以使用 **-u** 参数。比如我们要查看用户’liunkor’的进程，可以通过下面的命令：
```
 $ ps -u liunkor  
 PID TTY          TIME CMD  
 1839 pts/0    00:00:00 ps  
 3096 ?        00:00:00 gnome-keyring-d  
 3098 ?        00:00:00 gnome-session  
 3106 ?        00:00:00 dbus-launch  
 3107 ?        00:00:01 dbus-daemon  
 3172 ?        00:00:00 gvfsd  
 3176 ?        00:00:00 gvfsd-fuse  
 3252 ?        00:00:00 ssh-agent  
 3280 ?        00:00:00 at-spi-bus-laun  
 3284 ?        00:00:00 dbus-daemon  
 3288 ?        00:00:00 at-spi2-registr  
 3298 ?        00:00:26 gnome-settings-  
 3314 ?        00:09:40 pulseaudio  
 3327 ?        00:00:00 gvfs-udisks2-vo  
 3345 ?        00:00:00 gvfs-gphoto2-vo  
 3349 ?        00:00:00 gvfs-mtp-volume  
 3353 ?        00:00:00 gvfs-goa-volume  
 3356 ?        00:00:04 goa-daemon  
 3365 ?        00:00:00 gvfs-afc-volume  
 3369 ?        01:12:19 gnome-shell  
 3384 ?        00:00:00 gsd-printer  
 3390 ?        00:00:00 dconf-service  
 3400 ?        00:01:32 ibus-daemon  
 3405 ?        00:00:00 ibus-dconf  
 3408 ?        00:00:00 ibus-x11  
 3415 ?        00:00:00 gnome-shell-cal  
 3421 ?        00:00:00 evolution-sourc  
 3424 ?        00:00:06 mission-control  
 3456 ?        00:00:30 nautilus  
 3479 ?        00:00:27 tracker-store  
 3480 ?        00:00:00 gconfd-2  
 3493 ?        00:00:00 seapplet  
 3503 ?        00:00:00 gvfsd-trash  
 3507 ?        00:00:06 tracker-miner-f  
 3524 ?        00:00:00 evolution-alarm  
 3526 ?        00:00:00 abrt-applet  
 3546 ?        00:00:23 escd  
 3548 ?        00:00:00 evolution-addre  
 3572 ?        00:00:20 ibus-engine-lib  
 3603 ?        00:00:00 evolution-calen  
 3663 ?        00:00:00 gvfsd-burn  
 3670 ?        00:00:00 gvfsd-metadata  
 3745 ?        00:00:21 evince  
 3750 ?        00:00:00 evinced  
 3842 ?        00:00:04 ibus-engine-sim  
16944 ?        00:00:00 gvfsd-afc  
24998 ?        01:29:28 firefox  
25168 ?        00:11:17 plugin-containe  
29386 ?        00:00:03 gnome-terminal-  
29391 ?        00:00:00 gnome-pty-helpe  
29392 pts/0    00:00:00 bash
```
### 4. 通过cpu和内存使用来过滤进程(这个非常有用）

也许你希望把结果按照 CPU 或者内存用量来筛选，这样你就找到哪个进程占用了你的资源。要做到这一点，我们可以使用 **aux 参数**，来显示全面的信息:

    $ ps -aux | less

当结果很长时，我们可以使用管道和less命令来筛选。

默认的结果集是未排好序的。可以通过 **–sort**命令来排序。

根据 **CPU 使用**来升序排序
```
$ ps -aux --sort -pcpu | less  
USER       PID %CPU %MEM    VSZ   RSS TTY      STAT START   TIME COMMAND  
liunkor  24998 11.4 17.5 1866604 625512 ?      Rl   Jan27  90:42 /usr/lib64/firefox/firefox  
liunkor   3369  3.2  8.8 1854456 313608 ?      Sl   Jan26  72:38 /usr/bin/gnome-shell  
liunkor   3314  0.4  0.2 557000  7788 ?        Sl   Jan26   9:50 /usr/bin/pulseaudio --start  
root         2  0.0  0.0      0     0 ?        S    Jan26   0:00 [kthreadd]  
root         3  0.0  0.0      0     0 ?        S    Jan26   0:00 [ksoftirqd/0]  
root         5  0.0  0.0      0     0 ?        S<   Jan26   0:00 [kworker/0:0H]  
root         7  0.0  0.0      0     0 ?        S    Jan26   0:00 [migration/0]  
root         8  0.0  0.0      0     0 ?        S    Jan26   0:00 [rcu_bh]
```
还可以根据 **内存使用** 来升序排序

    $ ps -aux --sort -pmem | less

### 5. 通过进程名和PID过滤

使用 **-C 参数**，后面跟你要找的进程的名字。比如想显示一个名为 gvim 的进程的信息，就可以使用下面的命令：
```
    $ ps -C gvim  
    PID TTY          TIME CMD  
    2082 ?        00:00:00 gvim
```
### 7. 树形显示进程

以树形结构显示进程，可以使用 **-axjf** 参数。
```
$ps -axjf  
PPID   PID  PGID   SID TTY      TPGID STAT   UID   TIME COMMAND  
    0     2     0     0 ?           -1 S        0   0:00 [kthreadd]  
    2     3     0     0 ?           -1 S        0   0:00  \_ [ksoftirq  
    2     5     0     0 ?           -1 S<       0   0:00  \_ [kworker/  
    2     7     0     0 ?           -1 S        0   0:00  \_ [migratio  
    2     8     0     0 ?           -1 S        0   0:00  \_ [rcu_bh]  
    2     9     0     0 ?           -1 S        0   0:00  \_ [rcuob/0]  
    2    10     0     0 ?           -1 S        0   0:00  \_ [rcuob/1]  
    2    11     0     0 ?           -1 S        0   0:00  \_ [rcuob/2]  
    2    12     0     0 ?           -1 S        0   0:00  \_ [rcuob/3]  
    2    13     0     0 ?           -1 S        0   0:00  \_ [rcuob/4]  
    2    14     0     0 ?           -1 S        0   0:00  \_ [rcuob/5]  
    2    15     0     0 ?           -1 S        0   0:00  \_ [rcuob/6]  
    2    16     0     0 ?           -1 S        0   0:00  \_ [rcuob/7]  
    2    17     0     0 ?           -1 S        0   0:20  \_ [rcu_sche  
    2    18     0     0 ?           -1 S        0   0:09  \_ [rcuos/0]  
    2    19     0     0 ?           -1 S        0   0:09  \_ [rcuos/1]  
    2    20     0     0 ?           -1 S        0   0:06  \_ [rcuos/2]  
    2    21     0     0 ?           -1 S        0   0:04  \_ [rcuos/3]  
    2    22     0     0 ?           -1 S        0   0:00  \_ [rcuos/4]  
    2    23     0     0 ?           -1 S        0   0:00  \_ [rcuos/5]  
    2    24     0     0 ?           -1 S        0   0:00  \_ [rcuos/6]  
    2    25     0     0 ?           -1 S        0   0:00  \_ [rcuos/7
```
用 pstree （效果更直观）
```
$ pstree  
systemd─┬─ModemManager───2*[{ModemManager}]  
        ├─NetworkManager─┬─dhclient  
        │                └─3*[{NetworkManager}]  
        ├─2*[abrt-watch-log]  
        ├─abrtd  
        ├─accounts-daemon───2*[{accounts-daemon}]  
        ├─alsactl  
        ├─at-spi-bus-laun─┬─dbus-daemon───{dbus-daemon}  
        │                 └─3*[{at-spi-bus-laun}]  
        ├─at-spi2-registr───{at-spi2-registr}  
        ├─atd  
        ├─auditd─┬─audispd─┬─sedispatch  
        │        │         └─{audispd}  
        │        └─{auditd}  
        ├─avahi-daemon───avahi-daemon  
        ├─bluetoothd  
        ├─chronyd  
        ├─colord───{colord}  
        ├─crond  
        ├─cupsd  
        ├─2*[dbus-daemon───{dbus-daemon}]  
        ├─dbus-launch  
        ├─dconf-service───2*[{dconf-service}]  
        ├─escd───{escd}  
        ├─evince───3*[{evince}]  
        ├─evinced───{evinced}  
        ├─evolution-addre───4*[{evolution-addre}]  
        ├─evolution-calen───4*[{evolution-calen}]  
        ├─evolution-sourc───2*[{evolution-sourc}]  
        ├─firewalld───{firewalld}  
        ├─gconfd-2  
        ├─gdm─┬─gdm-simple-slav─┬─Xorg  
        │     │                 ├─gdm-session-wor─┬─gnome-session─┬─a+  
        │     │                 │                 │               ├─e+  
        │     │                 │                 │               ├─g+  
        │     │                 │                 │               ├─g+  
        │     │                 │                 │               ├─s+  
        │     │                 │                 │               ├─s+  
        │     │                 │                 │               ├─t+  
        │     │                 │                 │               └─3+  
        │     │                 │                 └─2*[{gdm-session-w+  
        │     │                 └─2*[{gdm-simple-slav}]  
        │     └─2*[{gdm}]
```
### 8 结合 watch 实时监控进程状态

    $ watch -n 1 ps -aux --sort -pcpu

使用 ps命令来监控你的 Linux 系统是最好的方法之一，在使用过程中记得man！！

