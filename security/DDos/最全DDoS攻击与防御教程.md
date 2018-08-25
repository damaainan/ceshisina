## 史上最全DDoS攻击与防御教程

来源：[https://blog.csdn.net/qq_38461232/article/details/81490835](https://blog.csdn.net/qq_38461232/article/details/81490835)

 
 **`可怕的 **`DDoS`** `** 


出于打击报复、敲诈勒索、政治需要等各种原因，加上 **`攻击`** 成本越来越低、效果特别明显等特点， **`DDoS`**  **`攻击`** 已经演变成全球性网络安全威胁。

 **`本文大纲：`** 

* 可怕的 **`DDoS`** 
	* **`DDoS`**  **`攻击`** 科普
	* **`DDoS`** 防护科普
	* **`DDoS`**  **`攻击`** 与防护实践
	* 企业级 **`DDoS`** 清洗系统架构探讨

#### 危害

根据卡巴斯基2016Q3的调查报告， **`DDoS`**  **`攻击`** 造成 **`61%的公司无法访问其关键业务信息，38%公司无法访问其关键业务，33%的受害者因此有商业合同或者合同上的损失。`** 

![][0]

#### 趋势

总结起来，现在的 **`DDoS`**  **`攻击`** 具有以下趋势：

1 **`国际化`** 


现在的 **`DDoS`**  **`攻击`** 越来越国际化，而我国已经成为仅次于美国的第二大 **`DDoS`**  **`攻击`** 受害国，而国内来自海外的 **`DDoS`**  **`攻击`** 源占比也越来越高。


2 **`超大规模化`** 


因为跨网调度流量越来越方便、流量购买价格越来越低廉，现在 **`DDoS`**  **`攻击`** 流量规模越来越大。特别是2014年底，某云还遭受了高达450Gbps的 **`攻击`** 。

![][1]

3 **`市场化`** 

市场化势必带来成本优势，现在各种在线 **`DDoS`** 平台、肉鸡交易渠道层出不穷，使得 **`攻击`** 者能以很低的成本发起规模化 **`攻击`** 。针对流量获取方式的对比可以参考下表。

![][2]
###  **` **`DDoS`**  **`攻击`** 科普`** 
 **`DDoS`** 的 **`攻击`** 原理，往简单说，其实就是利用TCP/UDP协议规律，通过占用协议栈资源或者发起大流量拥塞，达到消耗目标机器性能或者网络的目的， **`下面我们先简单回顾TCP“三次握手”与“四次挥手”以及UDP通信流程。`** 
 **`TCP三次握手与四次挥手`** 

![][3]

##### TCP建立连接：三次握手

1.client: syn

2.server: syn+ack

3.client: ack

##### TCP断开连接：四次挥手

1.client: fin

2.server: ack

3.server: fin

4.client: ack

##### UDP通信流程

![][4]

根据上图可发现，UDP通信是无连接、不可靠的，数据是直接传输的，并没有协商的过程。
 
#### **`攻击`** 原理与 **`攻击`** 危害

按照 **`攻击`** 对象的不同，将 **`攻击`** 原理和 **`攻击`** 危害的分析分成3类，分别是 **`攻击`** 网络带宽资源、应用以及系统。

 **` **`攻击`** 网络带宽资源：`** 


![][5]
 **` **`攻击`** 系统资源：`** 


![][6]
 **` **`攻击`** 应用资源：`** 

![][7]

###  **` **`DDoS`** 防护科普`** 
 **`攻击`** 防护原理
 **`从TCP/UDP协议栈原理介绍 **`DDoS`** 防护原理：`** 

![][8]
#### **`syn flood：`** 

可以在收到客户端第三次握手reset 、第二次握手发送错误的ack，等Client回复Reset，结合信任机制进行判断。

#### **`ack flood：`** 

丢弃三次ack，让对方重连：重发syn建立链接，后续是syn flood防护原理；学习正常ack的源，超过阈值后，该ack没有在正常源列表里面就丢弃ack三次，让对方重连：重发syn建立链接，后续是syn flood防护。
 **`udp flood：`** 

![][9]

#### 不同层面的防护

1 **`按 **`攻击`** 流量规模分类`** 
 **`较小流量：`** 


小于1000Mbps，且在服务器硬件与应用接受范围之内，并不影响业务的： 利用iptables或者 **`DDoS`** 防护应用实现软件层防护。
 **`大型流量：`** 

大于1000Mbps，但在 **`DDoS`** 清洗设备性能范围之内，且小于机房出口，可能影响相同机房的其他业务的：利用iptables或者 **`DDoS`** 防护应用实现软件层防护，或者在机房出口设备直接配置黑洞等防护策略，或者同时切换域名，将对外服务IP修改为高负载Proxy集群外网IP，或者CDN高仿IP，或者公有云 **`DDoS`** 网关IP，由其代理到RealServer；或者直接接入 **`DDoS`** 清洗设备。
 **`超大规模流量：`** 

在 **`DDoS`** 清洗设备性能范围之外，但在机房出口性能之内，可能影响相同机房的其他业务，或者大于机房出口，已经影响相同机房的所有业务或大部分业务的：联系运营商检查分组限流配置部署情况并观察业务恢复情况。

2 **`按 **`攻击`** 流量协议分类`** 
 **`syn/fin/ack等tcp协议包：`** 

设置预警阀值和响应阀值，前者开始报警，后者开始处理，根据流量大小和影响程度调整防护策略和防护手段，逐步升级。
 **`UDP/DNS query等UDP协议包`** ：

对于大部分游戏业务来说，都是TCP协议的，所以可以根据业务协议制定一份TCP协议白名单，如果遇到大量UDP请求，可以不经产品确认或者延迟跟产品确认，直接在系统层面/HPPS或者清洗设备上丢弃UDP包。
 **`http flood/CC等需要跟数据库交互的 **`攻击`** ：`** 

这种一般会导致数据库或者webserver负载很高或者连接数过高，在限流或者清洗流量后可能需要重启服务才能释放连接数，因此更倾向在系统资源能够支撑的情况下调大支持的连接数。相对来说，这种 **`攻击`** 防护难度较大，对防护设备性能消耗很大。
 **`其他：`** 

icmp包可以直接丢弃，先在机房出口以下各个层面做丢弃或者限流策略。现在这种 **`攻击`** 已经很少见，对业务破坏力有限。
 **` **`DDoS`**  **`攻击`** 与防护实践`** 

自建 **`DDoS`** 平台

现在有开源的 **`DDoS`** 平台源代码，只要有足够机器和带宽资源，随时都能部署一套极具杀伤力的 **`DDoS`** 平台，如下图的第三种方案。

![][10]
 **`发包工具：`** 

下面提供一款常用 **`DDoS`** 客户端的发包代码，可以看到 **`攻击`** 方式非常丰富，ip、端口、tcp flag、包大小都是自定义的。

```python
def func():
	os.system("./txDDoS -a "+type+" -d "+ip+" -y "+port+" -f 0x10 -s 10.10.10.10 -l 1300")
	if __name__ == "__main__":
	pool = multiprocessing.Pool(processes=int(nbproc))
	for i in xrange(int(nbproc)):
	pool.apply_async(func)
	pool.close()
	pool.join()
```

讲完了 **`DDoS`**  **`攻击`** 的实现方式， **`下面介绍如何从iptables、应用自身和高性能代理等角度去 **`防御`**  **`DDoS`**  **`攻击`** 。`** 

iptables防护

	sysctl -w net.ipv4.ip_forward=1 &>/dev/null
	#打开转发
	sysctl -w net.ipv4.tcp_syncookies=1 &>/dev/null
	#打开 syncookie （轻量级预防 DOS  **`攻击`** ）
	sysctl -w net.ipv4.netfilter.ip_conntrack_tcp_timeout_established=3800 &>/dev/null
	#设置默认 TCP 连接最大时长为 3800 秒（此选项可以大大降低连接数）
	sysctl -w net.ipv4.ip_conntrack_max=300000 &>/dev/n
	#设置支持最大连接树为 30W（这个根据你的内存和 iptables 版本来，每个 connection 需要 300 多个字节）
	iptables -N syn-flood
	iptables -A INPUT -p tcp --syn -j syn-flood
	iptables -I syn-flood -p tcp -m limit --limit 3/s --limit-burst 6 -j RETURN
	iptables -A syn-flood -j REJECT
	#防止SYN **`攻击`**  轻量级预防
	iptables -A INPUT -i eth0 -p tcp --syn -m connlimit --connlimit-above 15 -j DROP
	iptables -A INPUT -p tcp -m state --state ESTABLISHED,RELATED -j ACCEPT
	#防止DOS太多连接进来,可以允许外网网卡每个IP最多15个初始连接,超过的丢弃


应用自身防护

以Nginx为例，限制单个ip请求频率。

```nginx
http {
	limit_req_zone $binary_remote_addr zone=one:10m rate=10r/s; #触发条件，所有访问ip 限制每秒10个请求
	server {
		location ~ .php$ {
			limit_req zone=one burst=5 nodelay; #执行的动作,通过zone名字对应 
		}
		location /download/ {
			limit_conn addr 1; # 限制同一时间内1个连接，超出的连接返回503
		}
	}
}
```

高性能代理

Haproxy+keepalived

1 **`Haproxy配置`** 
 **`前端：`** 

```
frontend http
bind 10.0.0.20:80
acl anti_ **`DDoS`**  always_true
#白名单
acl whiteip src -f /usr/local/haproxy/etc/whiteip.lst
#标记非法用户
stick-table type ip size 20k expire 2m store gpc0
tcp-request connection track-sc1 src
tcp-request inspect-delay 5s
#拒绝非法用户建立连接
tcp-request connection reject if anti_ **`DDoS`**  { src_get_gpc0 gt 0 }
```
 **`后端：`** 

```
backend xxx.xxx.cn
mode http
option forwardfor
option httplog
balance roundrobin
cookie SERVERID insert indirect
option httpchk GET /KeepAlive.ashx HTTP/1.1rnHost: server.1card1.cn
acl anti_ **`DDoS`**  always_false
#白名单
acl whiteip src -f /usr/local/haproxy/etc/whiteip.lst
#存储client10秒内的会话速率
stick-table type ip size 20k expire 2m store http_req_rate(10s),bytes_out_rate(10s)
tcp-request content track-sc2 src
#十秒内会话速率超过50个则可疑
acl conn_rate_limit src_http_req_rate(server.1card1.cn) gt 80
#判断http请求中是否存在SERVERID的cookie
acl cookie_present cook(SERVERID) -m found
#标记为非法用户
acl mark_as_abuser sc1_inc_gpc0 gt 0
tcp-request content reject if anti_ **`DDoS`**  !whiteip conn_rate_limit mark_as_abuser
```

2 **`keepalived配置`** 

```
global_defs {
router_id {{ server_id }}
}
vrrp_ chk_haproxy{
"/home/proxy/keepalived/{{ project }}/check_haproxy_{{ server_id }}.sh"
interval 2
weight -10
}
vrrp_instance VI_1 {
state {{ role }}
interface {{ interface }}
virtual_router_id 10{{ tag }}
priority {{ value }}
advert_int 1
authentication {
auth_type PASS
auth_pass keepalived_ **`DDoS`** 
track_ {
chk_haproxy
}
}
virtual_ipaddress {
{{ vip }}/24 dev {{ interface }} label {{ interface }}:{{ tag }}
}
```

接入CDN高防IP/公有云智能 **`DDoS`**  **`防御`** 系统


由于CDN高防IP和公有云智能 **`DDoS`**  **`防御`** 原理比较相近，都是利用代理或者DNS调度的方式进行“引流->清洗->回注”的 **`防御`** 流程，因此将两者合并介绍。

CDN高防IP：

是针对互联网服务器在遭受大流量的 **`DDoS`**  **`攻击`** 后导致服务不可用的情况下，推出的付费增值服务，用户可以通过配置高防IP，将 **`攻击`** 流量引流到高防IP，确保源站的稳定可靠。通常可以提供高达几百Gbps的防护容量，抵御一般的 **`DDoS`**  **`攻击`** 绰绰有余。

公有云智能 **`DDoS`**  **`防御`** 系统：

如下图，主要由以下几个角色组成：


* 调度系统：在 **`DDoS`** 分布式 **`防御`** 系统中起着智能域名解析、网络监控、流量调度等作用。
	* 源站：开发商业务服务器。
	* **`攻击`** 防护点：主要作用是过滤 **`攻击`** 流量，并将正常流量转发到源站。
	* 后端机房：在 **`DDoS`** 分布式 **`防御`** 系统中会与 **`攻击`** 防护点配合起来，以起到超大流量的防护作用，提供双重防护的能力。

![][11]

一般CDN或者公有云都有提供邮件、Web系统、微信公众号等形式的申请、配置流程，基本上按照下面的思路操作即可：

![][12]

### **`DDoS`**  **`攻击`** 处理技巧荟萃

1 **`发现`** 

Rsyslog
流量监控报警

查看`/var/log/messages（freebsd）`，`/var/log/syslog（debian）`，是否有被 **`攻击`** 的信息：

	*SYN Flood**RST
	limit xxx to xxx**
	listen queue limit*

查看系统或者应用连接情况，特别是连接数与系统资源占用情况

	netstat -antp | grep -i '业务端口' | wc -l
	sar -n DEV


2 **` **`攻击`** 类型分析`** 
 **`Tcpdump+wireshark：`** 

使用Tcpdump实时抓包给wireshark进行解析，有了wireshark实现自动解析和可视化展示，处理效率非一般快。

	Tcpdump -i eth0 -w test.pcap

比如通过目标端口和特殊标记识别ssdp flood：

	udp.dstport == 1900
	(udp contains "HTTP/1.1") and (udp contains 0a:53:54:3a)

![][13]
 **`高效的 **`DDoS`**  **`攻击`** 探测与分析工具FastNetMon：`** 

也可以使用FastNetMon进行实时流量探测和分析，直接在命令行展示结果，但是如果 **`攻击`** 流量很大，多半是派不上用场了。

![][14]

#### **` **`攻击`** 溯源：`** 

Linux服务器上开启uRPF 反向路径转发协议，可以有效识别虚假源ip，将虚假源ip流量抛弃。另外，使用unicast稀释 **`攻击`** 流量，因为unicast的特点是源-目的=1:n，但消息只会发往离源最近的节点，所以可以把 **`攻击`** 引导到某个节点，确保其他节点业务可用。

* 对于Input方向的数据包，检查访问控制列表ACL是否允许通过；
	* 按照Unicast RPF检查是否由最优路径抵达；
	* Forwarding Information Base（FIB）查找；
	* 对于Output方向，检查访问控制列表ACL是否允许通过；
	* 转发数据包。

 **`企业级 **`DDoS`** 清洗系统架构探讨`** 

#### 自研

使用镜像/分光（采集）+sflow/netflow（分析）+ **`DDoS`** 清洗设备（清洗）三位一体的架构是目前很多企业采用的防D架构，但是一般只适用于有自己机房或者在IDC业务规模比较大的企业。

如下图所示，在IDC或者自建机房出口下通过镜像/分光采集流量，集中到异常流量监测系统中进行分析，一旦发现异常流量，则与 **`DDoS`** 清洗设备进行联动，下发清洗规则和路由规则进行清洗。

![][15]

#### 商用


现在很多网络设备厂商/安全厂商都有成体系的流量采集、异常流量检测和清洗产品，比如阿里云、绿盟、华为等，相关产品在业界都很出名且各有市场，愿意通过采购构建企业 **`DDoS`** 防护体系的企业可以了解、购买相应的产品，这里不多赘述。

至此， **`DDoS`**  **`攻击`** 与 **`防御`** ：从原理到实践第一部分介绍完毕，欢迎大家多提真知灼见。

[16]: https://blog.csdn.net/qq_38461232/article/details/81569883
[0]: ./img/ac52e55e7c504e0db1e76b5d15050d19.jpeg
[1]: ./img/c9ea75e2fafa4b4a85b5facef6958bd7.jpeg
[2]: ./img/aad3bff433854bc5b73bb11e57f99dd9_th.png
[3]: ./img/c1110f7aef8a468eb435412727862215_th.jpeg
[4]: ./img/d0c41dc043c647369c17ed558bda9c66_th.jpeg
[5]: ./img/267bba0d5c10476dababef77334a0393_th.jpeg
[6]: ./img/3fed298fefe64ab98c07a533f261327d_th.jpeg
[7]: ./img/14f2b1d70c214e3ba954826cb37c4273_th.jpeg
[8]: ./img/0648d837f01344aaab6e8729ed3036b9_th.jpeg
[9]: ./img/75f26c62bde84353b482b86bd687a811.png
[10]: ./img/15da0ddde877452eb9085b8a9a04b1bb_th.jpeg
[11]: ./img/74c11e815d134f81acea979b66e4a4d4_th.jpeg
[12]: ./img/646fc8905880432382a6963dc14668bf.png
[13]: ./img/18112ed03088460fa7fa5f949e4c06b3_th.jpeg
[14]: ./img/c243955e5c444f05a337277bb9191443.jpeg
[15]: ./img/310c197bea9241c489e571f289a899a1_th.jpeg