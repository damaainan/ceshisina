# 协议消息分析方法之-tshark命令分析方法 

> DCC协议是 Diameter Credit Control Protocol 的简称。

tshark命令是wireshark附带的命令行抓包和分析工具，今天我们就使用它来分析一下 DCC 消息。

比如我们已经使用tcpdump工具抓消息包并保存到文件 test.201801092100.cap 中了，抓包命令如下:

* 1.开始抓消息包文件

```
    ## -Z 指定生成文件名属主为testuser , -U 实时写入消息内容 ， -G 保证3600秒生成一个文件，-z gzip 使用gzip压缩每次生成完毕的文件。  
    tcpdump -s 0  -Z testuser -i eth0 -G 3600 -z gzip -U -w /tmp/tcpdump/test.%Y%m%d%H%M.cap -n  '(tcp[tcpflags] & (tcp-push) != 0)  and ( tcp port 1234 )'
```
* 2.接下来我们的的第一个任务就是如何识别出那些消息是 DCC 消息呢？使用下面这条命令:
```
    ## 设置识别为DCC协议打端口范围，与wireshark工具打DecodeAs方法类似  
    port_address=1024-65535  
    ##  设置要读取打消息包文件  
    cap_file="test.201801092100.cap"  
    ## -d ==,  ， 协议识别规则设置,更多信息可以细读man文档  
    ## -V 显示详细打协议字段明细，会显示每个字段值信息，因此我使用了 grep AVP: 只提取包含"AVP: " 行的信息。  
    tshark -r $cap_file -d tcp.port==${port_address},diameter -V | grep 'AVP: '
```

输出效果：


    AVP: Session-Id(263) l=102 f=-M- val=test.3gppnetwork.org;15138742342089340  
    AVP: Origin-Host(264) l=84 f=-M- val=test.org  
    AVP: Origin-Realm(296) l=41 f=-M- val=test.org  
    AVP: Destination-Realm(283) l=16 f=-M- val=xxxx.com  
    AVP: Destination-Host(293) l=25 f=-M- val=xxxxx.cmcc.com  
    AVP: Auth-Application-Id(258) l=12 f=-M- val=Diameter Credit Control Application (4)  
    AVP: CC-Request-Type(416) l=12 f=-M- val=UPDATE_REQUEST (2)  
    AVP: CC-Request-Number(415) l=12 f=-M- val=3  
    AVP: Service-Context-Id(461) l=19 f=-M- val=example@xxxx.com  
    AVP: Event-Timestamp(55) l=12 f=-M- val=Dec 27, 2017 05:41:26.000000000 UTC  
    AVP: Origin-State-Id(278) l=12 f=-M- val=38  
    AVP: Subscription-Id(443) l=44 f=-M-  
     AVP: Subscription-Id-Type(450) l=12 f=-M- val=END_USER_E164 (0)  
     AVP: Subscription-Id-Data(444) l=21 f=-M- val=861234567890  
     ....

* 3.我们还想要用tshark命令来分析CCR消息中每一个session_id产生的流量汇总信息，那么该怎么做呢？

那就用到了 tshark 命令的-z统计选项了(-z help 可以显示更多帮助信息)，结合-q选项只显示我们要统计的信息，使用命令如下：

    port_address=1024-65535  
    cap_file="test.201801092100.cap"  
    ## 272是CCR消息的command_code,这里提取了3个字段  
    dcc_field_list="272,Session-Id,Subscription-Id-Data,Rating-Group,CC-Total-Octets"  
    tshark -r $cap_file -o out.cap -d tcp.port==${port_address},diameter -q -z diameter,avp,${dcc_field_list} | grep "is_request='1'" | awk '{  
                    gsub("'\''","");  
                    out_str=""  
                    for( i = 1; i <=nf; i++){<="" span="">  
                            split($i,arr,"=");  
                            if( $i~/Session-Id/)  out_str=arr[2];  
                            if( $i~/Subscription-Id-Data/)  out_str=out_str" "arr[2]  
                             if( $i~/CC-Total-Octets/) {     dataflow=arr[2]; out_flag=1;  }  
                             if( $i~/Rating-Group/ && out_flag == 1) {  
                                     out_flag=0;  
                                     printf("%s %s %.0f\n", out_str, arr[2], dataflow );  
                             }  
                     }  
            }' | awk '{  
                    idx=$1  
                    for( i = 2; i < NF; i++) { idx = idx" "$i }  
                    flow=$NF;  
                    tot[idx] += flow;  
                    total  += flow;  
            }END{  
                    for( i in tot){  
                            printf("%s %15.0f %10.2f\n", i , tot[i], tot[i]/1024/1024 );  
                    }  
                    printf("total: %.2f MB\n", total/1024/1024 );  
            }'

这段代码可能有些长，第一次使用awk目的是用来将单条CCR携带多组Rating-Group拆成多行输出，保证格式化数据，第二次的awk命令就是用来统计汇总了。  
输出效果：

    test.com.org;12342342;102033  1000000000  1024 1.00  
    test.com.org;12342342;102033  1000000001     0 0.00  
    total: 1024 Byte 1.00 MB

Github上有我已经写好的脚本示例tsk.dcc， [awesome-shell-script][0] 。

OK，先写这么多，关于tshark命令的更多想法和疑问欢迎您的回复留言。

[0]: https://github.com/Awkee/awesome-shell-script