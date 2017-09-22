
#!/bin/bash
#
# Description:
#  Used to get the hardware config information.
#
# History:
#  rscpass[rscpass@163.com], 2017/07/03, created.
#plantform:CentOS 6.X


# 如果有外省的项目，公司会租用服务器，所以要有一个脚本出个详细的报告给领导看一下服务器硬件情况，已是就有了下面这个脚本

# 该脚本在CentOS 6.x x86_64系统下测试通过


chk_lspci=`whereis lspci`
awk_lspci=`echo ${chk_lspci}| awk -F ":" '{print $2}'`
if [ ${#awk_lspci} -eq 0 ];then
        yum install pciutils -y  
else
        echo "lspci soft installed"
fi

chk_hdparm=`whereis hdparm`
awk_hdparm=`echo ${chk_hdparm}| awk -F ":" '{print $2}'`
if [ ${#awk_hdparm} -eq 0 ];then
        yum install hdparm -y
else
        echo "hdparm soft installed"
fi


chk_dmi=`whereis dmidecode`
awk_dmi=`echo ${chk_dmi}| awk -F ":" '{print $2}'`
if [ ${#awk_dmi} -eq 0 ];then
        yum install dmidecode -y
else
        echo "dmidecode soft installed"
fi

chk_smartctl=`whereis smartctl`
awk_smartctl=`echo ${chk_smartctl}| awk -F ":" '{print $2}'`
if [ ${#awk_smartctl} -eq 0 ];then
        yum install smartmontools -y
else
        echo "smartmontools soft installed"
fi


Machine_Name()
{
dmi=`rpm -qa | grep dmidecode`
if [ -z $dmi  ];then
        yum install  dmidecode -y
else
/usr/sbin/dmidecode | grep "Product Name" >/tmp/machine_name
echo `cat /tmp/machine_name`
rm -rf  /tmp/machine_name
fi
}



system_info(){
Machine_Name
echo "Host Name     : `hostname`"
echo "System Version: `cat /etc/redhat-release`"
echo "Kernel Version: `uname -r`"
echo "CPU Type      :`cat /proc/cpuinfo | grep 'model name' | cut -d : -f2 |uniq`"
echo "Memory Size   : `free -m | grep Mem | awk '{print $2}'`M"
echo "Disk Size     : `fdisk -l | grep Disk | head -n 1 | awk '{print $3$4}' | sed 's/,//g'`"
echo "Mainborad Ver : `dmidecode |grep -A16 "System Information$" |grep "Manufacturer:" |grep -v 'System'|awk '{print $2}'| head -n 1`"
echo "Network Card  :`lspci | grep Ethernet | cut -d : -f3 | head -n 1`"
echo "Net-Card eth0 : ` ifconfig eth0 | grep "inet addr" | awk '{print $2}' |sed 's/^addr://g'`  `ifconfig eth0 |grep 'HWaddr'|awk '{print  $5}'`"
echo "Net-Card eth1 : ` ifconfig eth1 | grep "inet addr" | awk '{print $2}' |sed 's/^addr://g'`  `ifconfig eth1 |grep 'HWaddr'|awk '{print  $5}'`"
echo "Date Time     : `date`"
}
echo " -----------This Computer's Hardware Summary Config Information is: -----------"
system_info


fn_get_cpu_info()
{
 echo -e "CPU Information->>: "
 echo -e "  CPU bits:"`getconf LONG_BIT`"bits"
 echo -e "  CPU Module:"`cat /proc/cpuinfo |grep 'model name' |sort |uniq | awk -F ":" '{print $2}'`
 echo -e "  Physical Num:"`cat /proc/cpuinfo |grep 'physical id' |sort |uniq | wc -l`
 echo -e "  Core num per CPU:"`cat /proc/cpuinfo |grep 'cpu cores' |sort |uniq| awk -F ":" '{print $2}'`
 echo -e "  Process Num is :`cat /proc/cpuinfo | grep processor | wc -l`"
 echo "-------------------------------------------------------------------------------------------------"
}

fn_get_disk_info()
{
 echo -e "Disk Information->>: "
 echo -e "Model Family"`smartctl -a /dev/sda | grep "Model Family"`
 echo -e "Device Modey"`smartctl -a /dev/sda | grep "Device Model"`
 echo -e "User Capacity"`smartctl -a /dev/sda | grep "User Capacity"`
# echo -e "Total Disk Volume"
#/sbin/fdisk  -l | sed -n 2p | awk -F ":" '{print $2}'| awk -F "," '{print $1}'
# for x in `df -h | grep /dev | awk '{print $5 "-" $6 "-" $2 "-" $4}' | sed 's/%//g'`
# do
#  disk_status=(${x//"-"/" "})
#  echo "Disk Directory ${disk_status[1]} DiskTotal=${disk_status[2]} DiskUsed=${disk_status[3]}"
# done
 echo -e "\nFile system and partition"
/bin/df -Th 
echo "Display HDD Type:"
cat /proc/scsi/scsi
 echo "-------------------------------------------------------------------------------------------------"
}


fn_get_mem_info()
{
 echo  -e "\nMemory Information->>: "
 echo -e "Memory clock speed:"`dmidecode | grep "Configured Clock Speed" | head -1`
 echo -e "Maximum Capacity:"`dmidecode | grep "Maximum Capacity"`
 echo -e "Memory brand:"`dmidecode | grep -n  "Manufacturer" | awk -F ":" '{print $3}' | sed  '/Manufacturer.*/d'|egrep  -v "HP|Intel"`

 MemTotal=`free -m | grep Mem | awk '{print  $2}'`
 echo  "  Total Memory is: ${MemTotal} MB "
 free -m
 echo  "  Memory Solt:"
 dmidecode | grep -P -A5 "Memory\s+Device" | grep Size | grep -v Range
 echo "-------------------------------------------------------------------------------------------------"

}


get_net_adapter_info()
{
echo  -e "\nNet Adapter Information->>: "
 echo -e "Net Adapter type:"
  lspci -vvv |grep Ethernet
 echo -e "Net Adapter Speed:"`ethtool eth0 | grep Speed`
 echo -e "Net Adapter Duplex:"`ethtool eth0 | grep Duplex`
 echo -e "Net Adapter Driver:"
lspci -vvv |grep Kernel|grep driver 
}

echo -e "\n -----------This Computer's Hardware Detailed Config Information is: -----------\n"
fn_get_disk_info
fn_get_cpu_info
fn_get_mem_info
get_net_adapter_info

echo -e "\n -----------End -----------\n"