# Linux系统性能测试脚本合集

 时间 2017-12-09 10:22:47  

原文[https://www.iamle.com/archives/2335.html][1]


## Linux性能测试UnixBench一键脚本

作者 https://teddysun.com/245.html

    UnixBench是一个类unix系（Unix，BSD，Linux）统下的性能测试工具，一个开源工具，被广泛用与测试linux系统主机的性能。
    Unixbench的主要测试项目有：系统调用、读写、进程、图形化测试、2D、3D、管道、运算、C库等系统基准性能提供测试数据。
     
    最新版本UnixBench5.1.3，包含system和graphic测试，如果你需要测试graphic，
    则需要修改Makefile,不要注释掉”GRAPHIC_TESTS = defined”，同时需要系统提供x11perf命令gl_glibs库。
    下面的脚本使用了最新版UnixBench5.1.3来测试，注释了关于graphic的测试项
    （大多数VPS都是没有显卡或者是集显，所以图像性能无需测试），运行10-30分钟后（根据CPU内核数量，运算时间不等）得出分数，越高越好。
     
     
    测试方法：
     
    wget --no-check-certificate https://github.com/teddysun/across/raw/master/unixbench.sh
    chmod +x unixbench.sh
    ./unixbench.sh
     
    

## 一键测试脚本bench.sh

作者 https://teddysun.com/444.html

    使用方法：
    命令1：
     
    wget -qO- bench.sh | bash
    或者
     
    curl -Lso- bench.sh | bash
    命令2：
     
    wget -qO- 86.re/bench.sh | bash
    或者
     
    curl -so- 86.re/bench.sh | bash
    备注：
    bench.sh 既是脚本名，同时又是域名。所以不要怀疑我写错了或者你看错了。
     
    下载地址：
    https://github.com/teddysun/across/blob/master/bench.sh
     
    

## bench-sh-2

https://github.com/hidden-refuge/bench-sh-2

    <br />bench-sh-2
    Benchmark Script Version 2
     
    Demo Output: http://pastebin.com/zqtBpZDU
     
     
    Parameters
     
    Help Page:
    ./bench.sh -h
     
    System Info + Speedtest IPv4 + Drive Speed:
    ./bench.sh
    Classic mode. This will use 1 GB bandwidth!
     
    System Info + Speedtest IPv6 + Drive Speed:
    ./bench.sh -6
    IPv6 only speed test. This will use 1 GB bandwidth!
     
    System Info + Speedtest IPv4 & IPv6 + Drive Speed:
    ./bench.sh -46 or ./bench.sh -64
    Dual stack speed test. This will use 2 GB bandwidth!
     
    System Info:
    ./bench.sh -sys
    System information only.
     
    Drive Speed:
    ./bench.sh -io
    Drive speed test via DD only.
     
    System Info + Speedtest IPv4 + Drive Speed + System Benchmark:
    ./bench.sh -b
    Classic mode with system benchmark. This will use 1 GB bandwidth!
     
    System Info + Speedtest IPv6 + Drive Speed + System Benchmark:
    ./bench.sh -b6
    IPv6 only speed test with system benchmark. This will use 1 GB bandwidth!
     
    System Info + Speedtest IPv4 & IPv6 + Drive Speed + System Benchmark:
    ./bench.sh -b46 or ./bench.sh -b64
    Dual stack speed test with system benchmark. This will use 2 GB bandwidth.


[1]: https://www.iamle.com/archives/2335.html
