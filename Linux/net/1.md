# linux dig 命令

 时间 2017-11-06 08:24:00  

原文[http://www.cnblogs.com/sparkdev/p/7777871.html][1]


dig 命令主要用来从 DNS 域名服务器查询主机地址信息。

##  查询单个域名的 DNS 信息 dig 命令最典型的用法就是查询单个主机的信息。

    $ dig baidu.com
    

 ![][4]

dig 命令默认的输出信息比较丰富，大概可以分为 5 个部分。

第一部分显示 dig 命令的版本和输入的参数。

第二部分显示服务返回的一些技术详情，比较重要的是 status。如果 status 的值为 NOERROR 则说明本次查询成功结束。

第三部分中的 "QUESTION SECTION" 显示我们要查询的域名。

第四部分的 "ANSWER SECTION" 是查询到的结果。

第五部分则是本次查询的一些统计信息，比如用了多长时间，查询了哪个 DNS 服务器，在什么时间进行的查询等等。

默认情况下 dig 命令查询 A 记录，上图中显示的 A 即说明查询的记录类型为 A 记录。在尝试查询其它类型的记录前让我们先来了解一下常见的 DNS 记录类型。

##  常见 DNS 记录的类型  
类型 | 目的 
-|-
A |  地址记录，用来指定域名的 IPv4 地址，如果需要将域名指向一个 IP 地址，就需要添加 A 记录。 
AAAA |  用来指定主机名(或域名)对应的 IPv6 地址记录。 
CNAME |  如果需要将域名指向另一个域名，再由另一个域名提供 ip 地址，就需要添加 CNAME 记录。 
MX |  如果需要设置邮箱，让邮箱能够收到邮件，需要添加 MX 记录。 
NS |  域名服务器记录，如果需要把子域名交给其他 DNS 服务器解析，就需要添加 NS 记录。 
SOA |  SOA 这种记录是所有区域性文件中的强制性记录。它必须是一个文件中的第一个记录。 
TXT |  可以写任何东西，长度限制为 255。绝大多数的 TXT记录是用来做 SPF 记录(反垃圾邮件)。

##  查询 CNAME 类型的记录 除了 A 记录，常见的 DNS 记录还有 CNAME，我们可以在查询时指定要查询的 DNS 记录类型：

    $ dig abc.filterinto.com CNAME
    

 ![][5]

这样结果中就只有 CNAME 的记录。其实我们可以在查询中指定任何 DNS 记录的类型。

##  从指定的 DNS 服务器上查询 由于一些原因，希望从指定的 DNS 服务器上进行查询(从默认的 DNS 服务器上获得的结果可能不准确)。指定 DNS 服务器的方式为使用 @ 符号：

    $ dig @8.8.8.8 abc.filterinto.com
    

 ![][6]

从上图可以看到本次查询的 DNS 服务器为 8.8.8.8。

如果不指定 DNS 服务器，dig 会依次使用 /etc/resolv.conf 里的地址作为 DNS 服务器：

 ![][7]

    $ dig abc.filterinto.com
    

上面查询的 DNS 服务器就变成了：

 ![][8]

##  反向查询 在前面的查询中我们指定了查询服务器为 8.8.8.8，这是谁家的 DNS 服务器？其实我们可以使用 dig 的 -x 选项来反向解析 IP 地址对应的域名：

    $ dig -x 8.8.8.8 +short
    

 ![][9]

好吧，应该是谷歌家的，可以放心使用了。

##  控制显示结果 dig 命令默认返回的结果展示详细的信息，如果要获得精简的结果可以使用 +short 选项：

    $ dig +short abc.filterinto.com
    

 ![][10]

这下显示的结果就清爽多了。

其实我们还可以通过更多选项来控制输出的内容，比如只想显示 "ANSWER SECTION" 的内容：

    $ dig abc.filterinto.com +nocomments +noquestion +noauthority +noadditional +nostats
    

 ![][11]

这个结果很不错，就是使用的选项太多了(dig 命令有很多这样的选项，详情请参考使用手册)。我们可以换一种优雅一些的方式来实现和上面相同的结果：

    $ dig abc.filterinto.com +noall +answer
    

##  查看 TTL(Time to Live) TTL 是 DNS 解析中很重要的指标，主要是控制 DNS 记录在 DNS 服务器上的缓存时间：

    $ dig abc.filterinto.com
    

 ![][12]

查询结果中的单位是秒。通过下面的命令可以显示精简一些测结果：

    $ dig +nocmd +noall +answer +ttlid abc.filterinto.com
    

 ![][13]

##  跟踪整个查询过程 如果你好奇 dig 命令执行查询时都经历了哪些过程，你可以尝试使用 +trace 选项。它会输出从根域到最终结果的所有信息：

    $ dig +trace abc.filterinto.com
    

 ![][14]

上图中显示的并不是一个完整的结果，感兴趣的朋友可以自己尝试。

##  总结 dig 是一个很给力 DNS 查询工具，本文仅介绍了其常见用法，更多的命令选项及使用方法请查看 man page。


[1]: http://www.cnblogs.com/sparkdev/p/7777871.html

[4]: https://img2.tuicool.com/iUbUzyI.png
[5]: https://img2.tuicool.com/Q3QN7zM.png
[6]: https://img1.tuicool.com/v2MnuiN.png
[7]: https://img0.tuicool.com/FjaiqeF.png
[8]: https://img0.tuicool.com/EvMzYjz.png
[9]: https://img2.tuicool.com/ry2aQvQ.png
[10]: https://img2.tuicool.com/Fve6Zn6.png
[11]: https://img2.tuicool.com/e2YFRvj.png
[12]: https://img0.tuicool.com/yi2Unur.png
[13]: https://img1.tuicool.com/NNNnUjj.png
[14]: https://img1.tuicool.com/2IfINz3.png