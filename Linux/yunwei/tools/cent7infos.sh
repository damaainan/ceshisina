#!/bin/bash

# 服务器型号
dmidecode | grep "Product" > server.temp.info
serverModelInfo="服务器型号:"
while read lineStr
do 
    temp=`echo ${lineStr##*:}` 
    serverModelInfo="${serverModelInfo}${temp} "
done < server.temp.info
# 操作系统
os=`cat /etc/centos-release`
osInfo="操作系统:${os}"
# 处理器
cpuNameInfo=`cat /proc/cpuinfo | grep "model name" | uniq`
cpuNameInfo=`echo ${cpuNameInfo##*:}`
cpuNameInfo="处理器：${cpuNameInfo}"
# 主板
boradNameStr=`dmidecode -t 2 | grep "Product Name" | uniq`
boardName=`echo ${boradNameStr##*:}`
boardNameInfo="主板:${boardName}"
# 内存
# Memory
memStr=`dmidecode -t 17 | grep "Size:.*MB" | uniq`
memSizeStr=`echo ${memStr##*:}`
memSize=`echo ${memSizeStr} | tr -cd "[0-9]"`
memSizeInGB=`expr ${memSize} / 1024`
# MaxMemory
maxMemStr=`dmidecode -t 16 | grep Maximum | uniq`
maxMem=`echo ${maxMemStr##*:} | sed s/[[:space:]]//g`
memoryInfo="内存：${memSizeInGB}GB（最大内存${maxMem}）"
# 硬盘
lsblk -o name,type,size,kname,fstype,MODEL,size | grep disk > server.temp.info
diskCountStr=`lsblk  -o  TYPE  | grep  -i  disk | wc  -l`
hardDiskInfo="硬盘：${diskCountStr}个硬盘  "
while read lineStr
do 
    arr=(${lineStr})
    info="硬盘${arr[0]}(${arr[2]}) "
    hardDiskInfo="${hardDiskInfo}${info} "
done < server.temp.info
# 网卡
networkNameStr=`lspci | grep Ethernet | uniq`
networkName=`echo ${networkNameStr##*:}`
networkNameInfo="网卡：${networkName}" 
# 概况
serverTotal="\n  ${serverModelInfo}\n  ${osInfo}\n  ${cpuNameInfo}\n  ${boardNameInfo}\n  ${memoryInfo}\n  ${hardDiskInfo}\n  ${networkNameInfo}\n"
echo -e "${serverTotal}" > GetServerInfo.txt
echo -e "${serverTotal}"
rm  -f server.temp.info


# 在 CentOS 7 服务器上运行可得到基本的系统信息：


# 服务器型号:VMware Virtual Platform 440BX Desktop Reference Platform 
# 操作系统:CentOS Linux release 7.4.1708 (Core) 
# 处理器：Intel(R) Xeon(R) CPU E5-2620 0 @ 2.00GHz
# 主板:440BX Desktop Reference Platform
# 内存：8GB（最大内存1TB）
# 硬盘：3个硬盘  硬盘fd0(4K)  硬盘sda(50G)  硬盘sdb(50G)  
# 网卡：VMware VMXNET3 Ethernet Controller (rev 01)