> 平时需要对数据包进行分析和统计，尽管使用python scapy库来开发很方便，但若是熟悉tshark（wireshark的命令行)，tcpdump 等工具，含editcap，mergecap 等，写个简单的shell分析脚本，那会更加方便!

## 简介

1. tcpdump

* man tcpdump

tshark

* tshark 是一次性将整个数据包读入内存的，分析好后再统一输出，所以针对超大文件的分析，需要注意！**但是和wireshark相比，tshark能分析的文件已经很大了**，具体和系统配置有关！
* 同tshark一起的还有其他工具，是一套的，如 editcap, mergecap, capinfos
* man tshark, man wireshark-filter, man editcap, man mergecap
* 一个很棒的[网页][0]，自己一直收藏着
    * This is a place for scripts and tools related to Wireshark / TShark that users may like to share, and for links to related NetworkTroubleshooting tools.

## 常用实例

## tshark (editcap, capinfos)

* 过滤出特定时间段的数据包

```
    # 过滤出 src.pcap 中 2017-06-17 10:40:00 到 2017-06-17 10:50:00 之间的数据包，其中 -F 参数表示文件格式，即 the file format of the output capture file！ 留意 pcapng 格式的数据包
    
    editcap -A "2017-06-17 10:40:00" -B "2017-06-17 10:50:00" src.pcap -F pcap dst.pcap
```

* 统计重传数据包的个数

```
    # -n 不进行域名解析， 其他参数的意思 man tshark
    tshark -n -r src.pcap -Y "tcp.analysis.retransmission" -T fields -e tcp.stream | wc -l
    echo -e "The number of retransmission packets"
    
    # 通过 -z 参数
    tshark -z io,stat,0,"tcp.analysis.retransmission" -n -q -r src.pcap
```

* 查看抓包文件的信息

```
    # -c 显示文件中数据包的个数
    capinfos -c -M src.pcap
    
    content=$(capinfos -c -M src.pcap)
    total=$(echo $content | grep packet | cut -d : -f 3) # 获取文件中数据包的个数
```
* 以5秒为单位，统计不同方向上的数据包个数

```
    tshark -z io,stat,5,"ip.addr==180.153.15.118","ip.src==180.153.15.118","ip.dst==180.153.15.118" -n -q -r 1030_1038_8300.pcap > five_second.csv
```

* 以5秒为单位，不同方向上的**重传**数据包的个数, 含字节数 (注意：,后不能有空格)

```
    tshark -z io,stat,5,"ip.addr==180.153.15.118 && tcp.analysis.retransmission",\
    "ip.src==180.153.15.118 && tcp.analysis.retransmission",\
    "ip.dst==180.153.15.118 && tcp.analysis.retransmission" \
    -n -q -r src.pcap > dst.csv
```

* 以5秒为单位，统计不同方向上的含SYN, FIN, RST等标记的数据包个数 (注意：,后不能有空格)

```
    tshark -z io,stat,5,\
    "FRAMES()ip.src==${SERVERIP} && tcp.flags.syn==1 && !(tcp.flags.ack==1)",\
    "FRAMES()ip.dst==${SERVERIP} && tcp.flags.syn==1 && !(tcp.flags.ack==1)",\
    "FRAMES()ip.src==${SERVERIP} && tcp.flags.fin==1",\
    "FRAMES()ip.dst==${SERVERIP} && tcp.flags.fin==1",\
    "FRAMES()ip.src==${SERVERIP} && tcp.flags.reset==1",\
    "FRAMES()ip.dst==${SERVERIP} && tcp.flags.reset==1",\
    "FRAMES()ip.src==${SERVERIP} && tcp.flags.syn==1 && !(tcp.flags.ack==1) && (!tcp.analysis.retransmission)",\
    "FRAMES()ip.dst==${SERVERIP} && tcp.flags.syn==1 && !(tcp.flags.ack==1) && (!tcp.analysis.retransmission)",\
    "FRAMES()ip.src==${SERVERIP} && tcp.flags.fin==1 && (!tcp.analysis.retransmission)",\
    "FRAMES()ip.dst==${SERVERIP} && tcp.flags.fin==1 && (!tcp.analysis.retransmission)",\
    "FRAMES()ip.src==${SERVERIP} && tcp.flags.reset==1 && (!tcp.analysis.retransmission)",\
    "FRAMES()ip.dst==${SERVERIP} && tcp.flags.reset==1 && (!tcp.analysis.retransmission)" \
    -n -q -r src.pcap > dst.csv
```

## tcpdump

> 过滤速度最快，而且是实时输出！

* 最简单的--过滤出 src.pcap 中端口号为 22 的数据包

```
    tcpdump -Z root -r src.pcap "tcp port 22" -w dst.pcap
```

* 过滤出端口为22， 且含有 FIN 标记的数据包

```
    tcpdump -Z root -r src.pcap "tcp port 22 and (tcp[tcpflags] & tcp-fin != 0)" -w dst.pcap
```

* 根据应用层数据进行过滤，如HTTP GET的请求路径， 注意tcp[xx:offset]中的偏移最多为4

```
    ## 示例: GET /bidimg/hello
    # tcp[24:4]==0x2f626964 匹配 /bid; tcp[28:4]==696d67ef 匹配 img/ 字段; 至于GET字段的匹配，可以自己去尝试！
    tcpdump -Z root -r src.pcap "((tcp[24:4]==0x2f626964 and tcp[28:4]==696d67ef) and dst port 80)" -w dst.pcap
```
[0]: https://link.zhihu.com/?target=https%3A//wiki.wireshark.org/Tools