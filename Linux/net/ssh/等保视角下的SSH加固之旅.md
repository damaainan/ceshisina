## 运维安全 | 等保视角下的SSH加固之旅

来源：[http://www.freebuf.com/articles/system/185846.html](http://www.freebuf.com/articles/system/185846.html)

时间 2018-10-18 08:05:35

 
*本文原创作者：ForrestX386，本文属FreeBuf原创奖励计划，未经许可禁止转载
 
## 0×00 前言 
 
前段时间在搞等保，根据等保的安全要求，需要对公司的服务器进行安全加固，其中就涉及到对SSH Server的加固。正好最近有空，笔者将加固过程的一些经验，总结分享一下，于是有了本文。
 
## 0×01 等保视角下的SSH 加固之旅 
 
等保规范中 对主机安全要求有以下一个方面

```
  1）身份鉴别
 2）访问控制
 3）审计
 4）入侵防范


```
 
根据这4点规范要求，结合实际加固经验，总结如下
 
### 一、服务端的加固： 
 
#### 1、登录认证维度的加固
 
1）、选择安全的登录认证方式
 
首推公钥认证方式
 
![][0]
 
通过ansible 批量更新，或者通过堡垒机的定时任务实现对管理的服务器上的公钥进行批量更新
 
如果需要再进一步提升安全性，可在公钥认证的基础上增加二次认证，相关文章有：
 
[基于短信的二次认证][1]
 
[基于TOTP的二次认证][2]
 
严禁选择基于密码的、基于主机的认证方式：

```
PasswordAuthentication no
HostbasedAuthentication no 
禁用用户的 .rhosts 文件
   IgnoreRhosts yes
```
 
如果有条件的可以接入Kerberos 认证
 
2）选择安全的ssh-key生成算法生成的key
 
ssh key 常见算法及安全性
 
DSA： 已被证明不安全，且从OpenSSH Server 7 之后便不再支持
 
RSA： RSA算法产生的私钥的安全性依赖于密钥的长度，如果密钥的长度小于3072，则不够安全，比如常见的2048 位的ssh key 是不够安全的，1024位直接被标记为不安全
 
ECDSA：这个算法产生的密钥安全性依赖于当前机器产生的随机数的强度
 
Ed25519： 目前最为推荐的ssh key 生成算法，安全性最好！
 
如何查看当前认证公钥key加密算法及其强度：

```sh
for key in ~/.ssh/id_*; do ssh-keygen -l -f "${key}"; done | uniq
```
 
如何生成Ed25519算法的key 呢？
 
shell下执行命令：

```sh
ssh-keygen -o -a 100 -t ed25519 -f ~/.ssh/id_ed25519 -C "john@example.com"
```
 
3）基于权限最小化原则，限制不同用户使用不同角色的账户
 
有的同学登录ssh 服务器是为了执行日常的运维操作命令，有的同学则单存为了上传下载文件，根据权限最小化原则，则给与日常运维的同学以普通ssh账户，可以获取shell，限制只有上传下载需求的同学只能sftp登录ssh 服务器
 
建议参考文章:[运维安全 | 如何限制指定账户不能SSH只能SFTP在指定目录][3]
 
#### 2、网络层的访问控制
 
1）禁止端口转发

```
AllowAgentForwarding no
AllowTcpForwarding no
X11Forwarding no
```
 
通过禁止TCP端口转发，可以禁止SSH 远程端口和本地端口转发功能，也可以禁止SSH 远程隧道的建立
 
2) 限制指定的IP才能连接
 
如果接入了堡垒机，则限制只允许堡垒机的IP连接

```
iptables -A INPUT -s 堡垒机IP -p tcp --dport 22 -j ACCEPT
iptables -A INPUT -p tcp --dport 22 -j DROP
```
 
除了在防火墙上做规则限制，还可以通过TCP Wrapper 和sshd_config的配置命令
 
#### 3、审计角度的加固
 
按照等保的要求，服务器对日常的运维行为必须保留日志，便于审计
 
为了实现等保的审计要求，可以选择加入堡垒机，或者将ssh 登录日志、bash 操作日志集中转发之SOC或者内部日志平台（比如通过syslog方式），可以参考的文章有:[安全运维之如何将Linux历史命令记录发往远程Rsyslog服务器][4]
 
#### 4、openssh server 本身的安全加固
 
及时更新openssh server及其依赖的openssl库的补丁，比如openssh server就曾曝出过比较严重漏洞：[OpenSSH现中危漏洞，可致远程代码执行][5]
 
建议关注：openssh 官方安全通告：[https://www.openbsd.org/security.html][6]
 
#### 5、SSH Server 的入侵防范
 
1）ssh 相关后门进行排查、比如openssh 后门等，相关文章有：
 
[一款短小精致的SSH后门分析][7]
 
[Linux安全运维丨OpenSSH安全浅析][8]
 
2）ssh 登录日志的排查
 
[安全运维之如何找到隐匿于last和w命令中的ssh登录痕迹][9]
 
### 二、客户端安全加固 
 
从putty、winscp 被爆携带后门到xshell多个版本被爆后门，客户端软件的安全性值得我们投入更多的精力去关注与改进，不然再牛逼的服务端加固也无济于事
 
[百度软件中心版putty被曝恶意捆绑软件][10]
 
[远程终端管理工具Xshell被植入后门代码事件分析报告][11]
 
从等保安全性要求，建议禁止使用破解版的ssh client 软件，比如SecureCRT 等，避免软件供应链污染导致的安全问题。
 
建议从正规官网下载Xshell、MobaXterm、putty、winscp等ssh 客户端软件。
 
## 0×02 总结 
 
从法律对网络安全要求趋严的大环境下，对服务器的有效的加固是比不可少的环节，本文抛砖引玉，希望更多的业内从业人员分享自己的一线经验。笔者行文匆忙，定有不足之处，还望各位斧正！
 
## 0×03 参考 
 
[http://www.freebuf.com/sectool/159488.html][12]
 
[http://www.freebuf.com/column/163631.html][13]
 
[http://www.freebuf.com/column/163631.html][13]
 
[https://blog.g3rt.nl/upgrade-your-ssh-keys.html][15]
 
*本文原创作者：ForrestX386，本文属FreeBuf原创奖励计划，未经许可禁止转载


[1]: http://www.freebuf.com/articles/web/165139.html
[2]: http://www.freebuf.com/articles/database/136555.html
[3]: http://www.freebuf.com/wp-admin/www.freebuf.com/system/183983.html
[4]: http://www.freebuf.com/articles/system/140543.html
[5]: http://www.freebuf.com/news/123614.html
[6]: https://www.openbsd.org/security.html
[7]: http://www.freebuf.com/system/140880.html
[8]: http://www.freebuf.com/news/153364.html
[9]: http://www.freebuf.com/system/182860.html
[10]: http://www.freebuf.com/news/171211.html
[11]: http://www.freebuf.com/terminal/144822.html
[12]: http://www.freebuf.com/sectool/159488.html
[13]: http://www.freebuf.com/column/163631.html
[14]: http://www.freebuf.com/column/163631.html
[15]: https://blog.g3rt.nl/upgrade-your-ssh-keys.html
[0]: https://img2.tuicool.com/UjuQ3iI.jpg