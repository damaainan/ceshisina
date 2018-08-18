## Linux系统性能测试脚本合集

来源：[https://www.iamle.com/archives/2335.html](https://www.iamle.com/archives/2335.html)

时间 2017-12-09 10:22:47



## Linux性能测试UnixBench一键脚本

作者 https://teddysun.com/245.html

```
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
```


```sh
#! /bin/bash
#==============================================================#
#   Description:  Unixbench script                             #
#   Author: Teddysun <i@teddysun.com>                          #
#   Intro:  https://teddysun.com/245.html                      #
#==============================================================#
cur_dir=/opt/unixbench

# Check System
[[ $EUID -ne 0 ]] && echo 'Error: This script must be run as root!' && exit 1
[[ -f /etc/redhat-release ]] && os='centos'
[[ ! -z "`egrep -i debian /etc/issue`" ]] && os='debian'
[[ ! -z "`egrep -i ubuntu /etc/issue`" ]] && os='ubuntu'
[[ "$os" == '' ]] && echo 'Error: Your system is not supported to run it!' && exit 1

# Install necessary libaries
if [ "$os" == 'centos' ]; then
    yum -y install make automake gcc autoconf gcc-c++ time perl-Time-HiRes
else
    apt-get -y update
    apt-get -y install make automake gcc autoconf time perl
fi

# Create new soft download dir
mkdir -p ${cur_dir}
cd ${cur_dir}

# Download UnixBench5.1.3
if [ -s UnixBench5.1.3.tgz ]; then
    echo "UnixBench5.1.3.tgz [found]"
else
    echo "UnixBench5.1.3.tgz not found!!!download now..."
    if ! wget -c http://dl.teddysun.com/files/UnixBench5.1.3.tgz; then
        echo "Failed to download UnixBench5.1.3.tgz, please download it to ${cur_dir} directory manually and try again."
        exit 1
    fi
fi
tar -zxvf UnixBench5.1.3.tgz && rm -f UnixBench5.1.3.tgz
cd UnixBench/

#Run unixbench
make
./Run

echo
echo
echo "======= Script description and score comparison completed! ======= "
echo
echo
```

## 一键测试脚本bench.sh

作者 https://teddysun.com/444.html

```
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

https://github.com/teddysun/across
```

```sh
#!/usr/bin/env bash
#
# Description: Auto test download & I/O speed script
#
# Copyright (C) 2015 - 2018 Teddysun <i@teddysun.com>
#
# Thanks: LookBack <admin@dwhd.org>
#
# URL: https://teddysun.com/444.html
#

if  [ ! -e '/usr/bin/wget' ]; then
    echo "Error: wget command not found. You must be install wget command at first."
    exit 1
fi

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;36m'
PLAIN='\033[0m'

get_opsy() {
    [ -f /etc/redhat-release ] && awk '{print ($1,$3~/^[0-9]/?$3:$4)}' /etc/redhat-release && return
    [ -f /etc/os-release ] && awk -F'[= "]' '/PRETTY_NAME/{print $3,$4,$5}' /etc/os-release && return
    [ -f /etc/lsb-release ] && awk -F'[="]+' '/DESCRIPTION/{print $2}' /etc/lsb-release && return
}

next() {
    printf "%-70s\n" "-" | sed 's/\s/-/g'
}

speed_test_v4() {
    local output=$(LANG=C wget -4O /dev/null -T300 $1 2>&1)
    local speedtest=$(printf '%s' "$output" | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}')
    local ipaddress=$(printf '%s' "$output" | awk -F'|' '/Connecting to .*\|([^\|]+)\|/ {print $2}')
    local nodeName=$2
    printf "${YELLOW}%-32s${GREEN}%-24s${RED}%-14s${PLAIN}\n" "${nodeName}" "${ipaddress}" "${speedtest}"
}

speed_test_v6() {
    local output=$(LANG=C wget -6O /dev/null -T300 $1 2>&1)
    local speedtest=$(printf '%s' "$output" | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}')
    local ipaddress=$(printf '%s' "$output" | awk -F'|' '/Connecting to .*\|([^\|]+)\|/ {print $2}')
    local nodeName=$2
    printf "${YELLOW}%-32s${GREEN}%-24s${RED}%-14s${PLAIN}\n" "${nodeName}" "${ipaddress}" "${speedtest}"
}

speed_v4() {
    speed_test_v4 'http://cachefly.cachefly.net/100mb.test' 'CacheFly'
    speed_test_v4 'http://speedtest.tokyo.linode.com/100MB-tokyo.bin' 'Linode, Tokyo, JP'
    speed_test_v4 'http://speedtest.singapore.linode.com/100MB-singapore.bin' 'Linode, Singapore, SG'
    speed_test_v4 'http://speedtest.london.linode.com/100MB-london.bin' 'Linode, London, UK'
    speed_test_v4 'http://speedtest.frankfurt.linode.com/100MB-frankfurt.bin' 'Linode, Frankfurt, DE'
    speed_test_v4 'http://speedtest.fremont.linode.com/100MB-fremont.bin' 'Linode, Fremont, CA'
    speed_test_v4 'http://speedtest.dal05.softlayer.com/downloads/test100.zip' 'Softlayer, Dallas, TX'
    speed_test_v4 'http://speedtest.sea01.softlayer.com/downloads/test100.zip' 'Softlayer, Seattle, WA'
    speed_test_v4 'http://speedtest.fra02.softlayer.com/downloads/test100.zip' 'Softlayer, Frankfurt, DE'
    speed_test_v4 'http://speedtest.sng01.softlayer.com/downloads/test100.zip' 'Softlayer, Singapore, SG'
    speed_test_v4 'http://speedtest.hkg02.softlayer.com/downloads/test100.zip' 'Softlayer, HongKong, CN'
}

speed_v6() {
    speed_test_v6 'http://speedtest.atlanta.linode.com/100MB-atlanta.bin' 'Linode, Atlanta, GA'
    speed_test_v6 'http://speedtest.dallas.linode.com/100MB-dallas.bin' 'Linode, Dallas, TX'
    speed_test_v6 'http://speedtest.newark.linode.com/100MB-newark.bin' 'Linode, Newark, NJ'
    speed_test_v6 'http://speedtest.singapore.linode.com/100MB-singapore.bin' 'Linode, Singapore, SG'
    speed_test_v6 'http://speedtest.tokyo.linode.com/100MB-tokyo.bin' 'Linode, Tokyo, JP'
    speed_test_v6 'http://speedtest.sjc03.softlayer.com/downloads/test100.zip' 'Softlayer, San Jose, CA'
    speed_test_v6 'http://speedtest.wdc01.softlayer.com/downloads/test100.zip' 'Softlayer, Washington, WA'
    speed_test_v6 'http://speedtest.par01.softlayer.com/downloads/test100.zip' 'Softlayer, Paris, FR'
    speed_test_v6 'http://speedtest.sng01.softlayer.com/downloads/test100.zip' 'Softlayer, Singapore, SG'
    speed_test_v6 'http://speedtest.tok02.softlayer.com/downloads/test100.zip' 'Softlayer, Tokyo, JP'
}

io_test() {
    (LANG=C dd if=/dev/zero of=test_$$ bs=64k count=16k conv=fdatasync && rm -f test_$$ ) 2>&1 | awk -F, '{io=$NF} END { print io}' | sed 's/^[ \t]*//;s/[ \t]*$//'
}

calc_disk() {
    local total_size=0
    local array=$@
    for size in ${array[@]}
    do
        [ "${size}" == "0" ] && size_t=0 || size_t=`echo ${size:0:${#size}-1}`
        [ "`echo ${size:(-1)}`" == "K" ] && size=0
        [ "`echo ${size:(-1)}`" == "M" ] && size=$( awk 'BEGIN{printf "%.1f", '$size_t' / 1024}' )
        [ "`echo ${size:(-1)}`" == "T" ] && size=$( awk 'BEGIN{printf "%.1f", '$size_t' * 1024}' )
        [ "`echo ${size:(-1)}`" == "G" ] && size=${size_t}
        total_size=$( awk 'BEGIN{printf "%.1f", '$total_size' + '$size'}' )
    done
    echo ${total_size}
}

cname=$( awk -F: '/model name
```

## bench-sh-2

https://github.com/hidden-refuge/bench-sh-2

```
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
 
```

```sh
#!/bin/bash
#####################################################################
# Benchmark Script 2 by Hidden Refuge from FreeVPS                  #
# Copyright(C) 2015 - 2016 by Hidden Refuge                         #
# Github: https://github.com/hidden-refuge/bench-sh-2               #
#####################################################################
# Original script by akamaras/camarg                                #
# Original: http://www.akamaras.com/linux/linux-server-info-script/ #
# Original Copyright (C) 2011 by akamaras/camarg                    #
#####################################################################
# The speed test was added by dmmcintyre3 from FreeVPS.us as a      #
# modification to the original script.                              #
# Modded Script: https://freevps.us/thread-2252.html                # 
# Copyright (C) 2011 by dmmcintyre3 for the modification            #
#####################################################################
sysinfo () {
    # Removing existing bench.log
    rm -rf $HOME/bench.log
    # Reading out system information...
    # Reading CPU model
    cname=$( awk -F: '/model name/ {name=$2} END {print name}' /proc/cpuinfo | sed 's/^[ \t]*//;s/[ \t]*$//' )
    # Reading amount of CPU cores
    cores=$( awk -F: '/model name/ {core++} END {print core}' /proc/cpuinfo )
    # Reading CPU frequency in MHz
    freq=$( awk -F: ' /cpu MHz/ {freq=$2} END {print freq}' /proc/cpuinfo | sed 's/^[ \t]*//;s/[ \t]*$//' )
    # Reading total memory in MB
    tram=$( free -m | awk 'NR==2 {print $2}' )
    # Reading Swap in MB
    vram=$( free -m | awk 'NR==3 {print $2}' )
    # Reading system uptime
    up=$( uptime | awk '{ $1=$2=$(NF-6)=$(NF-5)=$(NF-4)=$(NF-3)=$(NF-2)=$(NF-1)=$NF=""; print }' | sed 's/^[ \t]*//;s/[ \t]*$//' )
    # Reading operating system and version (simple, didn't filter the strings at the end...)
    opsy=$( cat /etc/issue.net | awk 'NR==1 {print}' ) # Operating System & Version
    arch=$( uname -m ) # Architecture
    lbit=$( getconf LONG_BIT ) # Architecture in Bit
    hn=$( hostname ) # Hostname
    kern=$( uname -r )
    # Date of benchmark
    bdates=$( date )
    echo "Benchmark started on $bdates" | tee -a $HOME/bench.log
    echo "Full benchmark log: $HOME/bench.log" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    # Output of results
    echo "System Info" | tee -a $HOME/bench.log
    echo "-----------" | tee -a $HOME/bench.log
    echo "Processor : $cname" | tee -a $HOME/bench.log
    echo "CPU Cores : $cores" | tee -a $HOME/bench.log
    echo "Frequency : $freq MHz" | tee -a $HOME/bench.log
    echo "Memory        : $tram MB" | tee -a $HOME/bench.log
    echo "Swap      : $vram MB" | tee -a $HOME/bench.log
    echo "Uptime        : $up" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    echo "OS        : $opsy" | tee -a $HOME/bench.log
    echo "Arch      : $arch ($lbit Bit)" | tee -a $HOME/bench.log
    echo "Kernel        : $kern" | tee -a $HOME/bench.log
    echo "Hostname  : $hn" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
}
speedtest4 () {
    ipiv=$( wget -qO- ipv4.icanhazip.com ) # Getting IPv4
    # Speed test via wget for IPv4 only with 10x 100 MB files. 1 GB bandwidth will be used!
    echo "Speedtest (IPv4 only)" | tee -a $HOME/bench.log
    echo "---------------------" | tee -a $HOME/bench.log
    echo "Your public IPv4 is $ipiv" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    # Cachefly CDN speed test
    echo "Location      Provider    Speed"  | tee -a $HOME/bench.log
    cachefly=$( wget -4 -O /dev/null http://cachefly.cachefly.net/100mb.test 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "CDN           Cachefly    $cachefly" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    # United States speed test
    coloatatl=$( wget -4 -O /dev/null http://speed.atl.coloat.com/100mb.test 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Atlanta, GA, US       Coloat      $coloatatl " | tee -a $HOME/bench.log
    sldltx=$( wget -4 -O /dev/null http://speedtest.dal05.softlayer.com/downloads/test100.zip 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Dallas, TX, US        Softlayer   $sldltx " | tee -a $HOME/bench.log
    slwa=$( wget -4 -O /dev/null http://speedtest.sea01.softlayer.com/downloads/test100.zip 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Seattle, WA, US       Softlayer   $slwa " | tee -a $HOME/bench.log
    slsjc=$( wget -4 -O /dev/null http://speedtest.sjc01.softlayer.com/downloads/test100.zip 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "San Jose, CA, US  Softlayer   $slsjc " | tee -a $HOME/bench.log
    slwdc=$( wget -4 -O /dev/null http://speedtest.wdc01.softlayer.com/downloads/test100.zip 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Washington, DC, US    Softlayer   $slwdc " | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    # Asia speed test
    linodejp=$( wget -4 -O /dev/null http://speedtest.tokyo.linode.com/100MB-tokyo.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Tokyo, Japan      Linode      $linodejp " | tee -a $HOME/bench.log
    slsg=$( wget -4 -O /dev/null http://speedtest.sng01.softlayer.com/downloads/test100.zip 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Singapore         Softlayer   $slsg " | tee -a $HOME/bench.log
    hitw=$( wget -4 -O /dev/null http://tpdb.speed2.hinet.net/test_100m.zip 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' ) 
    echo "Taiwan                    Hinet           $hitw " | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    # Europe speed test
    i3d=$( wget -4 -O /dev/null http://mirror.i3d.net/100mb.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Rotterdam, Netherlands    id3.net     $i3d" | tee -a $HOME/bench.log
    leaseweb=$( wget -4 -O /dev/null http://mirror.leaseweb.com/speedtest/100mb.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Haarlem, Netherlands  Leaseweb    $leaseweb " | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
}
speedtest6 () {
    ipvii=$( wget -qO- ipv6.icanhazip.com ) # Getting IPv6
    # Speed test via wget for IPv6 only with 10x 100 MB files. 1 GB bandwidth will be used! No CDN - Cachefly not IPv6 ready...
    echo "Speedtest (IPv6 only)" | tee -a $HOME/bench.log
    echo "---------------------" | tee -a $HOME/bench.log
    echo "Your public IPv6 is $ipvii" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    echo "Location      Provider    Speed" | tee -a $HOME/bench.log
    # United States speed test
    v6atl=$( wget -6 -O /dev/null http://speedtest.atlanta.linode.com/100MB-atlanta.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Atlanta, GA, US       Linode      $v6atl" | tee -a $HOME/bench.log
    v6dal=$( wget -6 -O /dev/null http://speedtest.dallas.linode.com/100MB-dallas.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Dallas, TX, US        Linode      $v6dal" | tee -a $HOME/bench.log
    v6new=$( wget -6 -O /dev/null http://speedtest.newark.linode.com/100MB-newark.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Newark, NJ, US        Linode      $v6new" | tee -a $HOME/bench.log
    v6fre=$( wget -6 -O /dev/null http://speedtest.fremont.linode.com/100MB-fremont.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Fremont, CA, US       Linode      $v6fre" | tee -a $HOME/bench.log
    v6chi=$( wget -6 -O /dev/null http://testfile.chi.steadfast.net/data.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Chicago, IL, US       Steadfast   $v6chi" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    # Asia speed test
    v6tok=$( wget -6 -O /dev/null http://speedtest.tokyo.linode.com/100MB-tokyo.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Tokyo, Japan      Linode      $v6tok" | tee -a $HOME/bench.log
    v6sin=$( wget -6 -O /dev/null http://speedtest.singapore.linode.com/100MB-singapore.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Singapore     Linode      $v6sin" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    # Europe speed test
    v6fra=$( wget -6 -O /dev/null http://speedtest.frankfurt.linode.com/100MB-frankfurt.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "Frankfurt, Germany    Linode      $v6fra" | tee -a $HOME/bench.log
        v6lon=$( wget -6 -O /dev/null http://speedtest.london.linode.com/100MB-london.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
    echo "London, UK        Linode      $v6lon" | tee -a $HOME/bench.log
        v6har=$( wget -6 -O /dev/null http://mirror.nl.leaseweb.net/speedtest/100mb.bin 2>&1 | awk '/\/dev\/null/ {speed=$3 $4} END {gsub(/\(|\)/,"",speed); print speed}' )
        echo "Haarlem, Netherlands  Leaseweb    $v6har" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
}
iotest () {
    echo "Disk Speed" | tee -a $HOME/bench.log
    echo "----------" | tee -a $HOME/bench.log
    # Measuring disk speed with DD
    io=$( ( dd if=/dev/zero of=test_$$ bs=64k count=16k conv=fdatasync && rm -f test_$$ ) 2>&1 | awk -F, '{io=$NF} END { print io}' | sed 's/^[ \t]*//;s/[ \t]*$//' )
    io2=$( ( dd if=/dev/zero of=test_$$ bs=64k count=16k conv=fdatasync && rm -f test_$$ ) 2>&1 | awk -F, '{io=$NF} END { print io}' | sed 's/^[ \t]*//;s/[ \t]*$//' )
    io3=$( ( dd if=/dev/zero of=test_$$ bs=64k count=16k conv=fdatasync && rm -f test_$$ ) 2>&1 | awk -F, '{io=$NF} END { print io}' | sed 's/^[ \t]*//;s/[ \t]*$//' )
    # Calculating avg I/O (better approach with awk for non int values)
    ioraw=$( echo $io | awk 'NR==1 {print $1}' )
    ioraw2=$( echo $io2 | awk 'NR==1 {print $1}' )
    ioraw3=$( echo $io3 | awk 'NR==1 {print $1}' )
    ioall=$( awk 'BEGIN{print '$ioraw' + '$ioraw2' + '$ioraw3'}' )
    ioavg=$( awk 'BEGIN{print '$ioall'/3}' )
    # Output of DD result
    echo "I/O (1st run) : $io" | tee -a $HOME/bench.log
    echo "I/O (2nd run) : $io2" | tee -a $HOME/bench.log
    echo "I/O (3rd run) : $io3" | tee -a $HOME/bench.log
    echo "Average I/O   : $ioavg MB/s" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
}
gbench () {
    # Improved version of my code by thirthy_speed https://freevps.us/thread-16943-post-191398.html#pid191398
    echo "" | tee -a $HOME/bench.log
    echo "System Benchmark (Experimental)" | tee -a $HOME/bench.log
    echo "-------------------------------" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
    echo "Note: The benchmark might not always work (eg: missing dependencies)." | tee -a $HOME/bench.log
    echo "Failures are highly possible. We're using Geekbench for this test." | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
        gb_page=http://www.primatelabs.com/geekbench/download/linux/
        gb_dl=$(wget -qO - $gb_page | \
                 sed -n 's/.*\(https\?:[^:]*\.tar\.gz\).*/\1/p')
        gb_noext=${gb_dl##*/}
        gb_noext=${gb_noext%.tar.gz} 
        gb_name=${gb_noext//-/ }
    echo "File is located at $gb_dl" | tee -a $HOME/bench.log
    echo "Downloading and extracting $gb_name" | tee -a $HOME/bench.log
        wget -qO - "$gb_dl" | tar xzv 2>&1 >/dev/null
    echo "" | tee -a $HOME/bench.log
    echo "Starting $gb_name" | tee -a $HOME/bench.log
    echo "The system benchmark may take a while." | tee -a $HOME/bench.log
    echo "Don't close your terminal/SSH session!" | tee -a $HOME/bench.log
    echo "All output is redirected into a result file." | tee -a $HOME/bench.log
    echo "" >> $HOME/bench.log
    echo "--- Geekbench Results ---" >> $HOME/bench.log
    sleep 2
    $HOME/dist/$gb_noext/geekbench_x86_32 >> $HOME/bench.log
    echo "--- Geekbench Results End ---" >> $HOME/bench.log
    echo "" >> $HOME/bench.log
    echo "Finished. Removing Geekbench files" | tee -a $HOME/bench.log
    sleep 1
    rm -rf $HOME/dist/
    echo "" | tee -a $HOME/bench.log
        gbl=$(sed -n '/following link/,/following link/ {/following link\|^$/b; p}' $HOME/bench.log | sed 's/^[ \t]*//;s/[ \t]*$//' )
    echo "Benchmark Results: $gbl" | tee -a $HOME/bench.log
    echo "Full report available at $HOME/bench.log" | tee -a $HOME/bench.log
    echo "" | tee -a $HOME/bench.log
}
hlp () {
    echo ""
    echo "(C) Bench.sh 2 by Hidden Refuge <me at hiddenrefuge got eu dot org>"
    echo ""
    echo "Usage: bench.sh <option>"
    echo ""
    echo "Available options:"
    echo "No option : System information, IPv4 only speedtest and disk speed benchmark will be run."
    echo "-sys      : Displays system information such as CPU, amount CPU cores, RAM and more."
    echo "-io       : Runs a disk speed test and a IOPing benchmark and displays the results."
    echo "-6        : Normal benchmark but with a IPv6 only speedtest (run when you have IPv6)."
    echo "-46       : Normal benchmark with IPv4 and IPv6 speedtest."
    echo "-64       : Same as above."
    echo "-b        : Normal benchmark with IPv4 only speedtest, I/O test and Geekbench system benchmark."
    echo "-b6       : Normal benchmark with IPv6 only speedtest, I/O test and Geekbench system benchmark."
    echo "-b46      : Normal benchmark with IPv4 and IPv6 speedtest, I/O test and Geekbench system benchmark."
    echo "-b64      : Same as above."
    echo "-h        : This help page."
    echo ""
    echo "The Geekbench system benchmark is experimental. So beware of failure!"
    echo ""
}
case $1 in
    '-sys')
        sysinfo;;
    '-io')
        iotest;;
    '-6' )
        sysinfo; speedtest6; iotest;;
    '-46' )
        sysinfo; speedtest4; speedtest6; iotest;;
    '-64' )
        sysinfo; speedtest4; speedtest6; iotest;;
    '-b' )
        sysinfo; speedtest4; iotest; gbench;;
    '-b6' )
        sysinfo; speedtest6; iotest; gbench;;
    '-b46' )
        sysinfo; speedtest4; speedtest6; iotest; gbench;;
    '-b64' )
        sysinfo; speedtest4; speedtest6; iotest; gbench;;
    '-h' )
        hlp;;
    *)
        sysinfo; speedtest4; iotest;;
esac
#################################################################################
# Contributors:                                 #
# thirthy_speed https://freevps.us/thread-16943-post-191398.html#pid191398  #
#################################################################################

```


