#### 查看进程 
1. ps 
    *  `-a` 代表 `all`，同时加上 `x` 参数会显示没有控制终端的进程
    *  在需要查看特定用户进程的情况下，我们可以使用 `-u` 参数 ，例如`ps -u root `
    * `aux` ，显示全面的信息， `ps -aux | grep nginx` ，查看某个程序
    * `ps -aux --sort -pcpu` ，根据 CPU 使用来升序排序
    * ` ps -aux --sort -pmem`，根据 内存使用 来升序排序
    * 使用 ``-C`` 参数，后面跟你要找的进程的名字，`ps -C gvim`，显示一个名为 `gvim` 的进程的信息
    * 以树形结构显示进程，可以使用 `-axjf` 参数
    * `ps -eLf`

2. top
    * `top` 按`M`，按内存分配排序 ，按 `P`，按CPU使用排序
    * `-Hp`，显示一个进程的线程运行信息列表,` top -Hp 2816`
    * `top -c` ，显示进程运行信息列表， 键入`P` (`大写p`)，进程按照`CPU`使用率排序
    * `top -d 2`：每2秒刷新一次
    * `top -d 2 -p 3690` 查看某个PID
    * `top -b -n 2 > /tmp/top.txt` 将`top`的信息刷新两次的结果输出到`/tmp/top.txt`


#### 查看端口
1. telnet 
2. netstat
    * `netstat -antp | grep  skynet`   所占端口

3. lsof 
    * `lsof -i:80`  查看端口占用

#### 查看磁盘
1. du 
2. df 
3. fdisk


#### 查看内存
1. free 

#### 查看 `I/O`
1. 
