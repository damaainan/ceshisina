# openresty使用火焰图排查性能问题

 时间 2017-09-29 19:02:43 

原文[https://juejin.im/post/59ce27fef265da065b66d54b][1]


本文主要是讲解如何在ubuntu安装最新Systemtap,以及绘制火焰图

## 安装调试镜像

    # 导入 GPG key
    # 16.04 and higher
    sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys C8CAB6595FDFF622
    
    #older distributions
    #sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys ECDCAD72428D7C01 
    
    # 设置源
    codename=$(lsb_release -c | awk  '{print $2}')
    sudo tee /etc/apt/sources.list.d/ddebs.list << EOF
    deb http://ddebs.ubuntu.com/ ${codename}      main restricted universe multiverse
    deb http://ddebs.ubuntu.com/ ${codename}-security main restricted universe multiverse
    deb http://ddebs.ubuntu.com/ ${codename}-updates  main restricted universe multiverse
    deb http://ddebs.ubuntu.com/ ${codename}-proposed main restricted universe multiverse
    EOF
    
    # 更新
    sudo apt-get update
    
    # 安装调试镜像
    sudo apt-get install -y linux-image-$(uname -r)-dbgsym

## 安装最新版 systemtap

    $ sudo apt-get install -y build-essential zlib1g-dev elfutils libdw-dev gettext
    
    # https://sourceware.org/elfutils/ftp/?C=M;O=D
    $ wget https://sourceware.org/elfutils/ftp/0.170/elfutils-0.170.tar.bz2
    $ tar xf elfutils-0.170.tar.bz2
    
    # https://sourceware.org/systemtap/ftp/releases/?C=M;O=D
    $ wget https://sourceware.org/systemtap/ftp/releases/systemtap-3.1.tar.gz
    $ tar zxf systemtap-3.1.tar.gz
    
    $ cd systemtap-3.1
    
    $ ./configure --prefix=/opt/stap --disable-docs \
        --disable-publican --disable-refdocs CFLAGS="-g -O2" \
        --with-elfutils=../elfutils-0.170
    
    $ make -j$(getconf _NPROCESSORS_ONLN) && sudo make install
    
    # export STAP_HOME=/opt/stap/
    # export PATH=$PATH:$STAP_HOME
    
    # stap -V
    
    Systemtap translator/driver (version 3.1/0.170, non-git sources)
    Copyright (C) 2005-2017 Red Hat, Inc. and others
    This is free software; see the source for copying conditions.
    tested kernel versions: 2.6.18 ... 4.10-rc8
    enabled features: PYTHON2 PYTHON3 LIBXML2 NLS READLINE

## 测试是否生效

    # stap -v -e 'probe vfs.read {printf("read performed\n"); exit()}'
    Pass 1: parsed user script and 465 library scripts using 77388virt/46648res/5256shr/41840data kb, in 80usr/30sys/333real ms.
    Pass 2: analyzed script: 1 probe, 1 function, 7 embeds, 0 globals using 260440virt/231204res/6736shr/224892data kb, in 1680usr/350sys/7050real ms.
    Pass 3: translated to C into "/tmp/stap8Lyxq5/stap_e1c4934460a3e749f6deefe95dd50015_2729_src.c" using 260440virt/231404res/6936shr/224892data kb, in 10usr/0sys/5real ms.
    Pass 4: compiled C into "stap_e1c4934460a3e749f6deefe95dd50015_2729.ko" in 5260usr/420sys/7185real ms.
    Pass 5: starting run.
    read performed
    Pass 5: run completed in 0usr/20sys/486real ms.

## 绘制火焰图

### 下载各工具包

    # git clone https://github.com/openresty/stapxx.git --depth=1 /opt/stapxx
    # export STAP_PLUS_HOME=/opt/stapxx
    # export PATH=$PATH:$STAP_PLUS_HOME
    # stap++ -e 'probe begin { println("hello") exit() }'
    
    hello
    
    
    # git clone https://github.com/openresty/openresty-systemtap-toolkit.git --depth=1 /opt/openresty-systemtap-toolkit
    
    # git clone https://github.com/brendangregg/FlameGraph.git --depth=1 /opt/FlameGraph

### 绘制火焰图

    # 如果你在nginx.conf中设置了worker_processes auto;类似配置，先将其改成worker_processes 1; 然后reload 
    # ps -ef | grep nginx | grep worker 
    nginx      725   721  0 11:39 ?        00:00:27 nginx: worker process is shutting down
    nginx    14065   721  0 17:20 ?        00:00:14 nginx: worker process
    
    # /opt/stapxx/samples/lj-lua-stacks.sxx --arg time=20 --skip-badvars -x 14065 > /tmp/tmp.bt （-x 是要抓的进程的 pid， 探测结果输出到 tmp.bt）
    # /opt/openresty-systemtap-toolkit/fix-lua-bt tmp.bt > /tmp/flame.bt  (处理 lj-lua-stacks.sxx 的输出，使其可读性更佳)
    # /opt/FlameGraph/stackcollapse-stap.pl /tmp/flame.bt > /tmp/flame.cbt
    # /opt/FlameGraph/flamegraph.pl /tmp/flame.cbt > /tmp/flame.svg

为了突出效果，建议在运行 stap++ 的时候，使用压测工具，以便采集足够的样本 

    # ab -n 10000 -c 100 -k http://localhost/

用浏览器打开 /tmp/flame.svg 尽量用 chromefirefox 别用国产乱七八糟浏览器. 

更多资料请自行谷歌、百度。或者参阅 下面的 **参考连接**

## 参考连接

* [白话火焰图-火丁笔记][4]
* [Build Systemtap-openresty官方文档][5]
* [火焰图-openresty最佳实践][6]
* [Systemtap - ubuntu wiki][7]
* [openresty/stapxx][8]
* [openresty/openresty-systemtap-toolkit][9]
* [brendangregg/FlameGraph][10]


[1]: https://juejin.im/post/59ce27fef265da065b66d54b

[4]: https://link.juejin.im?target=https%3A%2F%2Fhuoding.com%2F2016%2F08%2F18%2F531
[5]: https://link.juejin.im?target=http%3A%2F%2Fopenresty.org%2Fen%2Fbuild-systemtap.html
[6]: https://link.juejin.im?target=https%3A%2F%2Fmoonbingbing.gitbooks.io%2Fopenresty-best-practices%2Fcontent%2Fflame_graph.html
[7]: https://link.juejin.im?target=https%3A%2F%2Fwiki.ubuntu.com%2FKernel%2FSystemtap
[8]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fopenresty%2Fstapxx%2Fblob%2Fmaster%2FREADME.markdown
[9]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fopenresty%2Fopenresty-systemtap-toolkit%2Fblob%2Fmaster%2FREADME.markdown
[10]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fbrendangregg%2FFlameGraph%2Fblob%2Fmaster%2FREADME.md