# dig

May 7, 2016

## dig 是什么？

dig 是一个调试dns server相关问题的工具。比nslookup,host 功能更强悍，或许是现在最好使的工具。

## 能用来做什么

* 解析域名的A记录
* 解析域名的MX记录
* 解析域名的CNAME记录
* 解析域名的NS记录

```
    dig soul11201.com  ;;A 记录
    dig ns soul11201.com ;;ns 记录
    dig CNAME soul11201.com ;;CNAME 记录
    
```
* 在指定的ns上解析域名的A记录

> dig @223.125.43.67 -p 2345 www.google.com

## 输出结果表示什么

输出的结果是dns响应报文和请求报文查询问题的可读形式，分别在下面三个区域中 Question/Answer/Authority sections。dns报文查询和响应格式详细参考经典著作TCPv1,14[1][0]。或者简单的可以参考一下[这篇文章][1]

输出结果大概分为下面几个区域：

* Header: 展示版本信息
* QUESTION SECTION: dns请求报文查询的问题，默认是A类型的查询
* ANSWER SECTION: 从dns收到的应答报文
* AUTHORITY SECTION: 授权的dns
* ADDITIONAL SECTION:授权dns的ip
* Stats section:最下面的统计部分，包括执行查询的时间和实际进行dns应答的机器ip和端口


```
    ; <<>> DiG 9.9.5-3ubuntu0.8-Ubuntu <<>> redhat.com
    ;; global options: +cmd
    ;; Got answer:
    ;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 61818
    ;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 4, ADDITIONAL: 5
    
    ;; OPT PSEUDOSECTION:
    ; EDNS: version: 0, flags:; udp: 4096
    ;; QUESTION SECTION:
    ;redhat.com.            IN  A
    
    ;; ANSWER SECTION:
    redhat.com.     600 IN  A   209.132.183.105
    
    ;; AUTHORITY SECTION:
    redhat.com.     384 IN  NS  ns2.redhat.com.
    redhat.com.     384 IN  NS  ns4.redhat.com.
    redhat.com.     384 IN  NS  ns1.redhat.com.
    redhat.com.     384 IN  NS  ns3.redhat.com.
    
    ;; ADDITIONAL SECTION:
    ns3.redhat.com.     30  IN  A   209.132.176.100
    ns4.redhat.com.     393 IN  A   209.132.188.218
    ns2.redhat.com.     9   IN  A   209.132.183.2
    ns1.redhat.com.     175 IN  A   209.132.186.218
    
    ;; Query time: 2010 msec
    ;; SERVER: 127.0.1.1#53(127.0.1.1)
    ;; WHEN: Sat May 07 11:53:30 CST 2016
    ;; MSG SIZE  rcvd: 191
```

**输出结果控制**

对于上面的输出结果区域，通过下面的选项是可以控制是否显示的

    +nocomments – 关闭 comment lines
    +noauthority – 关闭 authority section
    +noadditional – 关闭 additional section
    +nostats – 关闭 stats section
    +noanswer – 关闭 answer section
    +noall  - 关闭所有
    

只显示answer记录:

> dig redhat.com +nocomments +noquestion +noauthority +noadditional +nostats

或者

> dig redhat.com +noall +answer

## 参考

* [DIG command explained with examples in Linux][2]
* [UNDERSTANDING THE DIG COMMAND][3]
* [Understanding the dig command output][4]
* [10 Linux DIG Command Examples for DNS Lookup][5]

## tips

1. TCP/IP 详解卷1：协议，第14章 dns：域名系统 [↩][6]

[0]: #fn:tip-tcp
[1]: http://www.cnblogs.com/feng-qi/archive/2013/05/05/DNS_packet_analysis.html
[2]: http://www.linuxnix.com/surendras-dig-notes/
[3]: https://mediatemple.net/community/products/grid/204644130/understanding-the-dig-command
[4]: http://www.cyberciti.biz/faq/linux-unix-dig-command-examples-usage-syntax/dig-command-output/
[5]: http://www.thegeekstuff.com/2012/02/dig-command-examples/
[6]: #fnref:tip-tcp