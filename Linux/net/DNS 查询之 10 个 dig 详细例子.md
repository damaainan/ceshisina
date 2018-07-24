## DNS 查询之 10 个 dig 详细例子

来源：[https://lellansin.wordpress.com/2018/07/23/dns-查询之-10-个-dig-详细例子/](https://lellansin.wordpress.com/2018/07/23/dns-查询之-10-个-dig-详细例子/)

时间 2018-07-23 13:46:22



## 本文索引：



* 简单的 dig 用法 (理解输出)
* 仅列出结果段
* 查询 MX 记录
* 查询 NS 记录
* 查看所有 DNS 记录
* 使用 +short 查看精简输出
* 使用 -x 进行 DNS 反向查询
* @dnsserver 指定解析域名的 NDS 服务器
* 批量 DNS 查询
* 使用 $HOME/.digrc 文件来设置默认项
  

*nix 上的 dig 命令是专门用来挖取域名信息的。本文详细列举了 10 个例子来解析 dig 命令。


## 1. 简单的 dig 用法 (理解输出)

当你给 dig 命令传一个域名时，默认情况下它会返回该域名的 A 记录 (查询到的站点的 ip 地址)，如下例所示。

在本例中，结果段（ ANSWER SECTION ）中 显示了 redhat.com 的 A 记录。

```sh
$ dig redhat.com

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> redhat.com
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 62863
;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 4, ADDITIONAL: 3

;; QUESTION SECTION:
;redhat.com.                    IN      A

;; ANSWER SECTION:
redhat.com.             37      IN      A       209.132.183.81

;; AUTHORITY SECTION:
redhat.com.             73      IN      NS      ns4.redhat.com.
redhat.com.             73      IN      NS      ns3.redhat.com.
redhat.com.             73      IN      NS      ns2.redhat.com.
redhat.com.             73      IN      NS      ns1.redhat.com.

;; ADDITIONAL SECTION:
ns1.redhat.com.         73      IN      A       209.132.186.218
ns2.redhat.com.         73      IN      A       209.132.183.2
ns3.redhat.com.         73      IN      A       209.132.176.100

;; Query time: 13 msec
;; SERVER: 209.144.50.138#53(209.144.50.138)
;; WHEN: Thu Jan 12 10:09:49 2012
;; MSG SIZE  rcvd: 164
```

dig 命令的输出由以下几个部分组成：



* 头部(Header): 这里显示了 dig 命令的版本，以及使用的全局选项（+cmd），和一些附加的头信息。
* 查询段（QUESTION SECTION）：dig 命令查询的输入域名。例如我们运行 “dig redhat.com”，那么默认配置的 dig 命令就会去取 redhat.com 的 A 记录。而 “redhat.com. IN A” 就是在暗示我们取的是该域名的 A 记录。
* 结果段（ANSWER SECTION）：查询到的结果。“redhat.com. 37 IN A 209.132.183.81” 即 redhat.com 的 A 记录 ip 地址为 209.132.183.81。
* 来源段（AUTHORITY SECTION）：返回该结果段的授信 DNS 域名服务器。实际上这里就是 redhat.com 这个域名的 DNS 解析服务器。
* 附加段（ADDITIONAL SECTION）：这里列出了来源段中 DNS 服务器的地址。
* 统计段（底部内容）：这里列出一些 dig 命令的统计信息，包括查询花了多长时间等。
  


## 2. 仅列出结果段

大多数情况下，你只需要查看结果段（ANSWER SECTION）的内容。所以，我们可以关掉其他部分的显示内容。



* +nocomments – 关闭注释行
* +noauthority – 关闭来源段
* +noadditional – 关闭附加段
* +nostats – 关闭统计段
* +noanswer – 关闭结果段 (Emmm, 我想你应该不会这样)
  

所以，下例的命令仅仅只显示了结果段的信息：

```sh
$ dig redhat.com +nocomments +noquestion +noauthority +noadditional +nostats

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> redhat.com +nocomments +noquestion +noauthority +noadditional +nostats
;; global options: +cmd
redhat.com.             9       IN      A       209.132.183.81
```

不想带那么多关闭项？你可以使用 +noall 来关闭所有的内容（包括结果段），然后再加上 +answer 来开启结果段的输出。

```sh
$ dig redhat.com +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> redhat.com +noall +answer
;; global options: +cmd
redhat.com.             60      IN      A       209.132.183.81
```


## 3. 查询 MX 记录

```sh
$ dig redhat.com MX +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> redhat.com MX +noall +answer
;; global options: +cmd
redhat.com.             513     IN      MX      5 mx1.redhat.com.
redhat.com.             513     IN      MX      10 mx2.redhat.com.
```

你也可以使用 -t 参数指定一个类型来查询 MX 记录。例如

```sh
$ dig -t MX redhat.com +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> -t MX redhat.com +noall +answer
;; global options: +cmd
redhat.com.             489     IN      MX      10 mx2.redhat.com.
redhat.com.             489     IN      MX      5 mx1.redhat.com.
```


## 4. 查询 NS 记录

```sh
$ dig redhat.com NS +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> redhat.com NS +noall +answer
;; global options: +cmd
redhat.com.             558     IN      NS      ns2.redhat.com.
redhat.com.             558     IN      NS      ns1.redhat.com.
redhat.com.             558     IN      NS      ns3.redhat.com.
redhat.com.             558     IN      NS      ns4.redhat.com.
```

也可以用 -t 指定 NS 类型。

```sh
$ dig -t NS redhat.com +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> -t NS redhat.com +noall +answer
;; global options: +cmd
redhat.com.             543     IN      NS      ns4.redhat.com.
redhat.com.             543     IN      NS      ns1.redhat.com.
redhat.com.             543     IN      NS      ns3.redhat.com.
redhat.com.             543     IN      NS      ns2.redhat.com.
```

也可以用 -t 指定 NS 类型。


## 5. 查看所有 DNS 记录

```sh
$ dig redhat.com ANY +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> redhat.com ANY +noall +answer
;; global options: +cmd
redhat.com.             430     IN      MX      5 mx1.redhat.com.
redhat.com.             430     IN      MX      10 mx2.redhat.com.
redhat.com.             521     IN      NS      ns3.redhat.com.
redhat.com.             521     IN      NS      ns1.redhat.com.
redhat.com.             521     IN      NS      ns4.redhat.com.
redhat.com.             521     IN      NS      ns2.redhat.com.
```

或使用 -t

```sh
$ dig -t ANY redhat.com  +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> -t ANY redhat.com +noall +answer
;; global options: +cmd
redhat.com.             367     IN      MX      10 mx2.redhat.com.
redhat.com.             367     IN      MX      5 mx1.redhat.com.
redhat.com.             458     IN      NS      ns4.redhat.com.
redhat.com.             458     IN      NS      ns1.redhat.com.
redhat.com.             458     IN      NS      ns2.redhat.com.
redhat.com.             458     IN      NS      ns3.redhat.com.
```


## 6. 使用 +short 查看精简输出

```sh
$ dig redhat.com +short
209.132.183.81
```

你也可以指定记录类型：

```sh
$ dig redhat.com ns +short
ns2.redhat.com.
ns3.redhat.com.
ns1.redhat.com.
ns4.redhat.com.
```


## 7. 使用 -x 进行 DNS 反向查询

```
$ dig -x 209.132.183.81

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> -x 209.132.183.81
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 62435
;; flags: qr rd ra; QUERY: 1, ANSWER: 1, AUTHORITY: 4, ADDITIONAL: 3

;; QUESTION SECTION:
;81.183.132.209.in-addr.arpa.   IN      PTR

;; ANSWER SECTION:
81.183.132.209.in-addr.arpa. 600 IN     PTR     www.redhat.com.

;; AUTHORITY SECTION:
183.132.209.in-addr.arpa. 248   IN      NS      ns2.redhat.com.
183.132.209.in-addr.arpa. 248   IN      NS      ns1.redhat.com.
183.132.209.in-addr.arpa. 248   IN      NS      ns3.redhat.com.
183.132.209.in-addr.arpa. 248   IN      NS      ns4.redhat.com.

;; ADDITIONAL SECTION:
ns1.redhat.com.         363     IN      A       209.132.186.218
ns2.redhat.com.         363     IN      A       209.132.183.2
ns3.redhat.com.         363     IN      A       209.132.176.100

;; Query time: 35 msec
;; SERVER: 209.144.50.138#53(209.144.50.138)
;; WHEN: Thu Jan 12 10:15:00 2012
;; MSG SIZE  rcvd: 193
```

简要版：

```sh
$ dig -x 209.132.183.81 +short
www.redhat.com.
```


## 8. @dnsserver 指定解析域名的 NDS 服务器

默认情况下 dig 会使用你本机上 /etc/resolv.conf 文件中定义的 DNS  服务器。

如果你想使用不同的 DNS 服务器来执行查询，可以通过 @dnsserver 来在命令行指定。

```
$ dig @ns1.redhat.com redhat.com

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> @ns1.redhat.com redhat.com
; (1 server found)
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 20963
;; flags: qr aa rd; QUERY: 1, ANSWER: 1, AUTHORITY: 4, ADDITIONAL: 4
;; WARNING: recursion requested but not available

;; QUESTION SECTION:
;redhat.com.                    IN      A

;; ANSWER SECTION:
redhat.com.             60      IN      A       209.132.183.81

;; AUTHORITY SECTION:
redhat.com.             600     IN      NS      ns1.redhat.com.
redhat.com.             600     IN      NS      ns4.redhat.com.
redhat.com.             600     IN      NS      ns3.redhat.com.
redhat.com.             600     IN      NS      ns2.redhat.com.

;; ADDITIONAL SECTION:
ns1.redhat.com.         600     IN      A       209.132.186.218
ns2.redhat.com.         600     IN      A       209.132.183.2
ns3.redhat.com.         600     IN      A       209.132.176.100
ns4.redhat.com.         600     IN      A       209.132.188.218

;; Query time: 160 msec
;; SERVER: 209.132.186.218#53(209.132.186.218)
;; WHEN: Thu Jan 12 10:22:11 2012
;; MSG SIZE  rcvd: 180
```


## 9. 批量 DNS 查询

你可以通过创建一个文件存储多行域名来告诉 dig 批量查询多个域名。

首先我们可以创建一个简单的 names.txt 文件，里面包含两个准备批量查询的域名。

```sh
$ vi names.txt
redhat.com
centos.org
```

接下来，我们可以通过 -f 标志来告诉 dig 读取该文件进行批量查询：

```sh
$ dig -f names.txt MX +noall +answer
redhat.com.             600     IN      MX      10 mx2.redhat.com.
redhat.com.             600     IN      MX      5 mx1.redhat.com.
centos.org.             3600    IN      MX      10 mail.centos.org.
```

当然你也可以通过命令行来进行该操作：

```sh
$ dig redhat.com mx +noall +answer centos.org ns +noall +answer

; <<>> DiG 9.7.3-RedHat-9.7.3-2.el6 <<>> redhat.com mx +noall +answer centos.org ns +noall +answer
;; global options: +cmd
redhat.com.             332     IN      MX      10 mx2.redhat.com.
redhat.com.             332     IN      MX      5 mx1.redhat.com.
centos.org.             3778    IN      NS      ns3.centos.org.
centos.org.             3778    IN      NS      ns4.centos.org.
centos.org.             3778    IN      NS      ns1.centos.org.
```

上例中同时查询了 redhat.com 的 MX 记录以及 centos.org 的 NS 记录。


## 10. 使用 $HOME/.digrc 文件来设置默认项

你可能不想每次执行 dig 命令都带上一串选线，甚至 +short 也会觉得麻烦。这个时候你可以将这些选项配置到 .digrc 文件中，之后每次 dig 调用时，默认就会带上这些选项。配置如下选项：

```sh
$ cat $HOME/.digrc
+noall +answer
```

那么在进行 dig 操作时你可以省略这些不写了：

```sh
$ dig redhat.com
redhat.com.             60      IN      A       209.132.183.81

$ dig redhat.com MX
redhat.com.             52      IN      MX      5 mx1.redhat.com.
redhat.com.             52      IN      MX      10 mx2.redhat.com.
```

原文地址：https://www.thegeekstuff.com/2012/02/dig-command-examples


