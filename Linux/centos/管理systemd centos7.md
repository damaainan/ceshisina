# 管理systemd centos7
 阅读 54 评论 0 喜欢 2

> systemd

      POST --> Boot Sequence --> Bootloader --> kernel + initramfs(initrd) --> rootfs--> /sbin/init
        init：
          CentOS 5: SysVinit
          CentOS 6: Upstart
          CentOS 7: Systemd  /usr/sbin/init(软连接) --> ../lib/systemd/systemd
                             /sbin/init (软连接) --> ../lib/systemd/systemd
      Systemd
        系统启动和服务器守护进程管理器，负责在系统启动或运行时，激活系统资源，服务器进程和其它进程；
      Systemd新特性
        系统引导时实现服务并行启动
        按需启动守护进程
        自动化的服务依赖关系管理(a启动时，依赖于b，b未启动，就激活b)
        同时采用socket式与D-Bus(图形桌面)总线式激活服务
        系统状态快照
            socket式：http --> socket ip:80 
            (socket连接远程，http必须启动，从而监管socket；现在，socket负债监管，http睡觉，有人访问，就叫醒http，按需激活)  
      核心概念 unit
        unit表示不同类型的systemd对象，通过配置文件进行标识和配置；
        文件中主要包含了系统服务、监听socket、保存的系统快照以及其它与init相关的信息
      配置文件
        /usr/lib/systemd/system:每个服务最主要的启动脚本设置，类似于之前的/etc/init.d/  (服务文件的真身)
        /run/systemd/system: 系统执行过程中所产生的服务脚本，比上面目录优先运行 (一般不修改)
        /etc/systemd/system: 管理员建立的执行脚本，类似于/etc/rc.d/rcN.d/Sxx类的功能，比上面目录优先运行(服务文件的软连接)

![][1]



![][2]



> Unit类型

        systemctl -t help  查看unit类型
        以下文件都放在/usr/lib/systemd/system目录下
        service unit: 文件扩展名为.service, 用于定义系统服务
        (*)Target unit: 文件扩展名为.target，用于模拟实现运行级别
        Device unit: .device, 用于定义内核识别的设备
        Mount unit: .mount, 定义文件系统挂载点
        Socket unit: .socket, 用于标识进程间通信用的socket文件，也可在系统启动时，延迟启动服务，实现按需启动
        Snapshot unit: .snapshot, 管理系统快照
        Swap unit: .swap, 用于标识swap设备
        Automount unit: .automount，文件系统的自动挂载点
        Path unit: .path，用于定义文件系统中的一个文件或目录使用,常用于当文件系统变化时，延迟激活服务，如：spool 目录

![][3]



> 特性

        关键特性
            基于socket的激活机制：socket与服务程序分离
            基于d-bus的激活机制：
            基于device的激活机制：
            基于path的激活机制：
            系统快照：保存各unit的当前状态信息于持久存储设备中
            向后兼容sysvinit脚本
        不兼容
            systemctl命令固定不变，不可扩展
            非由systemd启动的服务，systemctl无法与之通信和控制

> 管理服务

        管理系统服务
            CentOS 7: service unit
            注意：能兼容早期的服务脚本
        命令：systemctl COMMAND name.service
        启动：service name start ==> systemctl start name.service(可以一条命令启动多个服务)
        停止：service name stop ==> systemctl stop name.service
        重启：service name restart ==> systemctl restart name.service
        状态：service name status ==> systemctl status name.service
             systemctl -l status name.service  查看更加详细的内容(排错用)
        (*)条件式重启：已启动才重启，否则不做操作
            service name condrestart==> systemctl try-restart name.service
            service name restart ==> systemctl restart name.service  (关闭之后，重启)
        (*)重载或重启服务：先加载，再启动
            systemctl reload-or-restart name.service
        (*)重载或条件式重启服务：
            systemctl reload-or-try-restart name.service
        禁止自动和手动启动：(类似于 chattr -i 禁止删除文件)
            systemctl mask name.service(本质就是创建一个软连接)
        取消禁止：
            systemctl unmask name.service(本质就是把创建的软连接删除)

![][4]



![][5]



![][6]



> 服务查看

        查看某服务当前激活与否的状态：
            systemctl is-active name.service
        查看所有已经激活的服务：
            systemctl list-units --type service
            systemctl -t service
        查看所有服务：
            systemctl list-units --type service -all
            systemctl list-units --type service -a
      chkconfig命令的对应关系：
        查看服务是否开机自启：
            systemctl is-enabled name.service
        设定某服务开机自启：
            chkconfig name on ==> systemctl enable name.service(本质就是创建一个软连接：ln -s 源 目标)
        设定某服务开机禁止启动：
            chkconfig name off ==> systemctl disable name.service(本质就是把创建的软连接删除)
        查看所有服务的开机自启状态：
            chkconfig --list ==> systemctl list-unit-files --type service
        用来列出该服务在哪些运行级别下启用和禁用
            chkconfig sshd --list ==>
            ls /etc/systemd/system/*.wants/sshd.service
            ls /etc/systemd/system/multi-user.target.wants/sshd.service
            ls /etc/systemd/system/graphical.target.wants/sshd.service
      其它命令：
        查看服务的依赖关系：
            systemctl list-dependencies name.service
        杀掉进程：
            systemctl kill 进程名(服务)

![][7]



> 服务状态

        systemctl list-unit-files --type service --all  显示状态
        loaded:Unit配置文件已处理
        active(running):一次或多次持续处理的运行
        active(exited):成功完成一次性的配置
        active(waiting):运行中，等待一个事件
        inactive:不运行
        enabled:开机启动
        disabled:开机不启动
        static:开机不启动，但可被另一个启用的服务激活
          例子：yum -y install telnet-server

![][8]



![][9]



![][10]



> systemctl 命令示例

        显示所有单元状态
            systemctl 或 systemctl list-units
        只显示服务单元的状态
            systemctl --type=service
        显示sshd服务单元
            systemctl status sshd.service -l  信息详细，排错用
        验证sshd服务当前是否活动
            systemctl is-active sshd
        启动，停止和重启sshd服务
            systemctl start sshd.service
            systemctl stop sshd.service
            systemctl restart sshd.service
        重新加载配置
            systemctl reload sshd.service
        列出活动状态的所有服务单元
            systemctl list-units --type=service
        列出所有服务单元
            systemctl list-units --type=service --all
        查看服务单元的启用和禁用状态
            systemctl list-unit-files --type=service
        列出失败的服务
            systemctl --failed --type=service
        列出依赖的单元
            systemctl list-dependencies sshd
        验证sshd服务是否开机启动
            systemctl is-enabled sshd
        禁用network，使之不能自动启动,但手动可以
            systemctl disable network
        启用network
            systemctl enable network
        禁用network，使之不能手动或自动启动
            systemctl mask network
        启用network
            systemctl unmask network

> 运行级别

        target units：
            unit配置文件：.target
            ls /usr/lib/systemd/system/*.target
            systemctl list-unit-files --type target --all
        运行级别：
            0 ==> runlevel0.target, poweroff.target
            1 ==> runlevel1.target, rescue.target
            2 ==> runlevel2.target, multi-user.target
            3 ==> runlevel3.target, multi-user.target
            4 ==> runlevel4.target, multi-user.target
            5 ==> runlevel5.target, graphical.target
            6 ==> runlevel6.target, reboot.target
        查看依赖性：
            systemctl list-dependencies graphical.target
        级别切换：initN ==> systemctl isolate name.target
            systemctl isolate multi-user.target  切换为3模式
            systemctl isolate graphical.target  切换为5模式
            注：只有/lib/systemd/system/*.target文件中AllowIsolate=yes 才能切换(修改文件需执行systemctl daemon-reload才能生效)
                例子
                vim /lib/systemd/system/multi-user.target：更改AllowIsolate=yes变为no
                systemctl daemon-reload
                systemctl isolate multi-user.target  转换失败
        查看target：
            runlevel; who -r
            systemctllist-units --type target
        获取默认运行级别：
            /etc/inittab==> systemctl get-default
        修改默认级别：
            /etc/inittab==> systemctl set-default name.target(本质就是删除一个软连接同时创建另一个软连接)
                systemctl set-default multi-user.target
                ls -l /etc/systemd/system/default.target

![][11]



![][12]



![][13]



    其它命令
      切换至紧急救援模式：(单用户模式，没网络)
            systemctl rescue
      切换至emergency模式：
            systemctl emergency
      其它常用命令：
            传统命令init，poweroff，halt，reboot都成为systemctl的软链接
            关机：systemctl halt、systemctl poweroff
            重启：systemctl reboot
            挂起：systemctl suspend
            休眠：systemctl hibernate
            休眠并挂起：systemctl hybrid-sleep

![][14]



> CentOS7引导顺序

        UEFi或BIOS初始化，运行POST开机自检
        选择启动设备
        引导装载程序, centos7是grub2
        加载装载程序的配置文件：/etc/grub.d/ /etc/default/grub /boot/grub2/grub.cfg
        加载initramfs驱动模块
        加载内核选项
        内核初始化，centos7使用systemd代替init
        执行initrd.target所有单元，包括挂载/etc/fstab
        从initramfs根文件系统切换到磁盘根目录
        systemd执行默认target配置，配置文件/etc/systemd/system/default.target(默认启动模式)
        systemd执行sysinit.target初始化系统及basic.target准备操作系统
        systemd启动multi-user.target下的本机与服务器服务
        systemd执行multi-user.target下的/etc/rc.d/rc.local
        Systemd执行multi-user.target下的getty.target及登录服务
        systemd执行graphical需要的服务

> service unit文件格式

        /etc/systemd/system：/usr/lib/systemd/system存放服务
            centos6里的/etc/init.d，存放服务脚本
        以“#” 开头的行后面的内容会被认为是注释
        相关布尔值，1、yes、on、true 都是开启，0、no、off、false 都是关闭
        时间单位默认是秒，所以要用毫秒（ms）分钟（m）等须显式说明
        service unit file文件通常由三部分组成：
            [Unit]：定义与Unit类型无关的通用选项；用于提供unit的描述信息、unit行为及依赖关系等
            [Service]：与特定类型相关的专用选项；此处为Service类型
            [Install]：定义由“systemctlenable”以及"systemctldisable“命令在实现服务启用或禁用时用到的一些选项
        Unit段的常用选项：
            Description：描述信息
            After：定义unit的启动次序，表示当前unit应该晚于哪些unit启动，其功能与Before相反
            Requires：依赖到的其它units，强依赖，被依赖的units无法激活时，当前unit也无法激活
            Wants：依赖到的其它units，弱依赖
            Conflicts：定义units间的冲突关系(nigx端口80，与http冲突)
        Service段的常用选项：
            Type：定义影响ExecStart及相关参数的功能的unit进程启动类型
                simple：默认值，这个daemon主要由ExecStart接的指令串来启动，启动后常驻于内存中
                forking：由ExecStart启动的程序透过spawns延伸出其他子程序来作为此daemon的主要服务。原生父程序在启动结束后就会终止
                oneshot：与simple类似，不过这个程序在工作完毕后就结束了，不会常驻在内存中
                dbus：与simple类似，但这个daemon必须要在取得一个D-Bus的名称后，才会继续运作.因此通常也要同时设定BusNname= 才行
                notify：在启动完成后会发送一个通知消息。还需要配合NotifyAccess 来让Systemd 接收消息
                idle：与simple类似，要执行这个daemon必须要所有的工作都顺利执行完毕后才会执行。这类的daemon通常是开机到最后才执行即可的服务
            EnvironmentFile：环境配置文件(存放服务内容变量)
            ExecStart：指明启动unit要运行命令或脚本的绝对路径(systemctl start ...)
            ExecStartPre：ExecStart前运行
            ExecStartPost：ExecStart后运行
            ExecStop：指明停止unit要运行的命令或脚本(systemctl stop ...)
            Restart：当设定Restart=1 时，则当次daemon服务意外终止后，会再次自动启动此服务
        Install段的常用选项：
            Alias：别名，可使用systemctl command Alias.service
            RequiredBy：被哪些units所依赖，强依赖
            WantedBy：被哪些units所依赖，弱依赖
            Also：安装本服务的时候还要安装别的相关服务
        注意：对于新创建的unit文件，或者修改了的unit文件，要通知systemd重载此配置文件,而后可以选择重启 systemctl daemon-reload
        服务Unit文件示例：
            vim /etc/systemd/system/bak.service  (此路径/usr/lib/systemd/system也可以)
                [Unit]
                Description=backup /etc
                Requires=atd.service
                [Service]
                Type=simple
                ExecStart=/bin/bash -c "echo /testdir/bak.sh|at now"(立即启动)
                [Install]
                WantedBy=multi-user.target
            (mkdir /testdir; vim /testdir/bak.sh：)
                #!/bin/bash
                cp -a /etc/ /testdir/back-`date %F`/
            chmod +x /testdir/bak.sh
            systemctl start atd
            systemctl daemon-reload
            systemctl start bak

> 设置内核参数(某个服务故障，系统无法启动，可以用此方法)

    设置内核参数(某个服务故障，系统无法启动，可以用此方法)
        systemctl set-default name.target  设置默认值，永久生效
        设置内核参数，只影响当次启动
        启动时，“白条界面”-->按ESC-->按e，在linux16行后添加systemd.unit=desired.target
            systemd.unit=emergency.target
            systemd.unit=recure.target
        recure.target 比 emergency 支持更多的功能，例如日志等

![][15]



> 启动排错(系统自动会进入emergency shell)

        文件系统损坏
            先尝试自动修复，失败则进入emergency shell，提示用户修复
        在/etc/fstab不存在对应的设备和UUID
            等一段时间，如不可用，进入emergency shell
        在/etc/fstab不存在对应挂载点
            systemd尝试创建挂载点，否则提示进入emergency shell.
        在/etc/fstab不正确的挂载选项
            提示进入emergency shell

> 破解CentOS7的root口令

    方法一
        启动时任意键暂停启动
        按e键进入编辑模式
        将光标移动linux16开始的行，添加内核参数rd.break
        按ctrl-x启动
        mount -o remount,rw /sysroot
        chroot /sysroot(注意不是/mnt/sysimage/)
        passwd root
        touch /.autorelabel
        exit
        reboot

![][16]



    方法二
        启动时任意键暂停启动
        按e键进入编辑模式
        将光标移动linux16开始的行，改为rw init=/sysroot/bin/sh(即：不用挂载systemd)
        按ctrl-x启动
        chroot /sysroot
        passwd root
        touch /.autorelabel
        exit
        reboot

![][17]



> 修复GRUB2

        GRUB“the Grand Unified Bootloader”
            引导提示时可以使用命令行界面
            可从文件系统引导
        主要配置文件/boot/grub2/grub.cfg
        修复配置文件
            grub2-mkconfig > /boot/grub2/grub.cfg
            grub2-mkconfig -o /boot/grub2/grub.cfg
        修复grub
            grub2-install /dev/sda  BIOS环境
            grub2-install  UEFI环境
        调整默认启动内核
            vim /etc/default/grub
            GRUB_DEFAULT=0

![][18]



![][19]



    dd if=/dev/zero of=/dev/sda bs=1 count=446  模拟故障
    hexdump -C -n 512 /dev/sda 
    reboot，白条界面，ESC，直接光盘引导，Troubleshooting-->rescue-->1-->回车-->进入shell进程
    grub2-install --root-directory=/mnt/sysimage/ /dev/sda
        切根就不用--root-directory=/mnt/sysimage/
    hexdump -C -n 512 /dev/sda
    exit
    菜单界面出现，即表示已恢复grub；

    rm -rf /boot/grub2/  模拟故障
    reboot，白条界面，ESC，直接光盘引导，Troubleshooting-->rescue-->1-->回车-->进入shell进程
    chroot /mnt/sysimage  切根
    (cd /boot;) grub2-install /dev/sda;( pwd )
    grub2-mkconfig -o /boot/grub2/grub.cfg;( cd grub2; ls; cat grub.cfg )
    reboot
    菜单界面出现，即恢复成功

![][20]

[0]: http://www.jianshu.com/p/8d7c9867e165
[1]: http://upload-images.jianshu.io/upload_images/6044565-921e379a68e719a9.png
[2]: http://upload-images.jianshu.io/upload_images/6044565-55b8ceed67bbea5a.png
[3]: http://upload-images.jianshu.io/upload_images/6044565-37d7da96856d6241.png
[4]: http://upload-images.jianshu.io/upload_images/6044565-3cdeeac379c28d3c.png
[5]: http://upload-images.jianshu.io/upload_images/6044565-a8e4e3866e3e6177.png
[6]: http://upload-images.jianshu.io/upload_images/6044565-d9b777305af420fc.png
[7]: http://upload-images.jianshu.io/upload_images/6044565-ab02dad691682b92.png
[8]: http://upload-images.jianshu.io/upload_images/6044565-c57165fad97865e3.png
[9]: http://upload-images.jianshu.io/upload_images/6044565-abf2b02c8524df1c.png
[10]: http://upload-images.jianshu.io/upload_images/6044565-1373d90fcc1ee5f4.png
[11]: http://upload-images.jianshu.io/upload_images/6044565-26029f5e1c73e78a.png
[12]: http://upload-images.jianshu.io/upload_images/6044565-7ed977d04822b0a6.png
[13]: http://upload-images.jianshu.io/upload_images/6044565-283067dd90e757bc.png
[14]: http://upload-images.jianshu.io/upload_images/6044565-6e9d89262b085e1b.png
[15]: http://upload-images.jianshu.io/upload_images/6044565-dbcda78a3c4252c3.png
[16]: http://upload-images.jianshu.io/upload_images/6044565-317b980ce078af46.png
[17]: http://upload-images.jianshu.io/upload_images/6044565-163acc36fcafd2cb.png
[18]: http://upload-images.jianshu.io/upload_images/6044565-faedd0b6deb343de.png
[19]: http://upload-images.jianshu.io/upload_images/6044565-4412d74e8ff5723e.png
[20]: http://upload-images.jianshu.io/upload_images/6044565-d5807cc3bbbf03d1.png