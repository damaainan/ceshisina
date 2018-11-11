## 保护SSH端口安全性的多种技巧介绍

来源：[https://www.freebuf.com/articles/network/185956.html](https://www.freebuf.com/articles/network/185956.html)

时间 2018-10-28 13:08:05

 
## 前言
 
SSH是一种可以让你在不安全的网络上，安全的运行网络服务的网络协议的.ssh的标准TCP端口为22端口，其最佳应用场景是用户远程登录至计算机系统。因此，SSH端口也是攻击者必扫的端口之一。本文将就SSH端口的安全性展开讨论，并为大家提供多种保护SSH端口安全性的建议和方法。
 
在开始之前，让我们先在计算机上安装SSH服务器。命令如下：

```
sudo apt-get install openssh-server
```
 
![][0]

 
## 端口转发
 
配置并运行SSH服务，然后我们使用NMAP进行扫描，可以看到当前SSH正在22端口上运行。
 
![][1]
 
切换至客户端机器的/etc/ssh目录下，我们可以看到一个名为sshd_config的配置文件。

```
cd /etc/ssh
```
 
![][2]
 
使用nano  命令打开sshd_config的文件。
 
![][3]
 
我们将其中的默认端口跟改为2222（如下图所示），保存并关闭。通过这种方式，我们将端口从22转发到2222。
 
![][4]
 
现在，我们再次使用nmap进行扫描。

```
nmap 192.168.1.104
```
 
nmap的输出显示TCP端口2222已打开；但在服务描述中只显示了EthernetIP-1，并没有给出运行服务的准确描述。因此，因此，让我们再来运行带有版本检测选项的nmap命令看看：

```
nmap  -sV 192.168.1.104
```
 
通过输出我们可以清楚地看到SSH服务当前正在TCP 2222端口上运行，以及OpenSSH版本的描述。
 
![][5]
 
## 公钥保护
 
首先，我们需要下载并安装PuTTY Key Generator。
 
注：PuTTYgen是一个密钥生成器，用于为PuTTY创建SSH密钥并且以自己的格式存储密钥（.ppk扩展名）
 
打开它，然后单击生成。
 
![][6]
 
单击生成将启动生成公钥和私钥的过程，如图所示：
 
![][7]
 
生成公钥和私钥后，单击“保存公钥”。这会将密钥作为一个公钥保存。
 
![][8]
 
现在，打开我们服务器的Ubuntu的终端并输入SSH-凯基。
 
![][9]
 
以上命令将创建一个名为.ssh的文件夹，然后我们在同一文件夹中创建一个名为authorized_keys的空文本文件。然后，我们复制之前使用PuTTy Key Generator创建的“ssh_login.ppk”文件，并将其粘贴到的.ssh文件夹中，如图所示：
 
![][10]
 
在终端中，进入.ssh文件夹并键入以下命令：

```
puttygen -L "ssh_login.ppk"
```
 
此命令将会生成一个密钥。
 
![][11]
 
现在，我们复制该密钥并使用纳米命令将其粘贴到名为authorized_keys中的空文件中并保存。
 
![][12]
 
接着，我们打开腻子配置选项卡，然后转到会话选项卡，为你的客户端机器提供IP地址和端口号。
 
![][13]
 
继续转到数据选项，并提供自动登录用户名（自动登录用户名）。
 
![][14]
 
导航到SSH>验证并提供ssh_login.ppk文件的路径（之前生成的公钥），然后单击“打开”。
 
![][15]
 
此时，它将使用公钥登录SSH服务器，而无需输入密码。
 
![][16]
 
使用的gedit命令打开的/ etc / SSH中的sshd_config中文件。我们将修改#PasswordAuthentication选项，如图所示。
 
#### 当前配置

```
#PasswordAuthentication yes


```
 
![][17]
 
现在，我们将参数值是改为无并去掉注释符（如下图所示），完成后保存并关闭文件。此更改将禁止任何用户使用密码登录SSH服务器。

```
PasswordAuthentication no


```
 
![][18]
 
正如你所看到的，这些设置已禁用基于密码的登录，并要求使用公钥登录。
 
![][19]
 
## 禁用根登录并限制SSH用户的访问权限
 
该安全措施，需要我们先使用的adduser命令创建一些新用户（这里我已经创建了：H1，H2，H3，H4用户），然后使用的gedit命令在sshd_config的文件的#Authentication身份下添加以下行：

```
#No root login allowed（h2 can login as sudo -s）
PermitRootLogin no
##only allow 1 users h2 （sysadmin）
AllowUsers h2
```
 
切记更改后及时进行保存，这将禁用Root Login，并且只允许h2用户远程登录ssh服务器。
 
![][20]
 
正如你所看到的，只有H2用户能够成功登录SSH服务器，而H1和H3用户权限则被拒绝登录。
 
![][21]
 
## Google身份验证器 
 
要通过SSH服务器进行双因素身份验证，你需要在手机上下载安装Google Authenticator，并使用以下命令为Ubuntu安装所需的依赖包：

```
sudo apt-get install libpam-google-authenticator
```
 
注– Google Authenticator在安装过程中会有多次询问，你只需选择是即可。
 
![][22]
 
安装完成后，打开终端并输入命令：

```
google-authenticator
```
 
此时，将会生成一个二维码，我们使用手机上的Google Authenticator进行扫描。
 
![][23]
 
成功扫描后，它将为我们生成一个动态口令，如图所示。
 
![][24]
 
现在，我们使用的gedit命令打开sshd的文件并进行以下更改：
 
在@include common-auth前添加注释符；
 
添加行（auth required pam_google_authenticator.so）到@include common-password下。
 
如图所示：
 
![][25]
 
继续更改的sshd_config文件中的以下选项。

```
ChallengeResponseAuthentication yes


```
 
![][26]
 
现在，当我们登录SSH服务器时，它会提示要求输入验证码。这里，我们必须输入在Google Authenticator上生成的动态口令。如下所示，我们已经使用一次性密码成功登录至SSH服务器。
 
![][27]
 
## 计划任务限制
 
在该安全措施中，我们将在服务器上设置SSH服务的时间限制。
 
克龙是一个用于调度任务的Linux的内置服务，它能够在指定的时间和日期自动运行服务器上的命令或脚本。
 
在这里，我们将使用crontab的计划SSH服务。
 
我们在/等中使用的纳米命令打开的crontab现在，让我们创建一个计划任务让SSH服务每2分钟启动一次，4分钟停止一次用于调度SSH服务的命令如下。：

```
* / 2 * * * * root service ssh start
* / 4 * * * * root service ssh stop
```
 
保存更改并关闭文件。
 
![][28]
 
等待服务重启，使用nmap扫描22端口。

```
nmap  -p 22 192.168.1.104
```
 
运行扫描后，我们将观察端口22上的SSH服务是否已被关闭，因为它已持续了4分钟的时间。
 
现在，如果我们的命令正常工作，它应该每隔2分钟启动一次服务，为了进一步的确认我们将再次使用nmap进行扫描。

```
nmap –p 22 192.168.1.104
```
 
可以看到端口现在处于开放状态。
 
![][29]
 
## 禁用空密码
 
从安全最佳实践来看，我们应该始终禁用空密码登录SSH服务器要启用此设置，我们只需将sshd_config的文件的以下选项参数值更为号即可：

```
PermitEmptyPasswords no


```
 
这将禁用空密码登录SSH服务器。
 
![][30]
 
 ** *参考来源：[hackingarticles][31] ，FB小编secist编译，转载请注明来自FreeBuf.COM ** 


[31]: http://www.hackingarticles.in/multiple-ways-to-secure-ssh-port/
[0]: https://img2.tuicool.com/73IviaU.jpg
[1]: https://img2.tuicool.com/ArANJfM.jpg
[2]: https://img1.tuicool.com/3aMnuiB.jpg
[3]: https://img2.tuicool.com/ueYR3qf.jpg
[4]: https://img0.tuicool.com/Mbq6ne3.jpg
[5]: https://img1.tuicool.com/BRV7baZ.jpg
[6]: https://img0.tuicool.com/jeQZzam.jpg
[7]: https://img1.tuicool.com/riEviyr.jpg
[8]: https://img2.tuicool.com/vYRbey3.jpg
[9]: https://img1.tuicool.com/YzQNnia.jpg
[10]: https://img1.tuicool.com/IvEr6vy.jpg
[11]: https://img1.tuicool.com/v2muMrj.jpg
[12]: https://img1.tuicool.com/zQj6jmN.jpg
[13]: https://img1.tuicool.com/QVnmqee.jpg
[14]: https://img0.tuicool.com/fi2aAzu.jpg
[15]: https://img0.tuicool.com/uiMvM3R.jpg
[16]: https://img0.tuicool.com/JjuQRjj.jpg
[17]: https://img2.tuicool.com/vEVbmeY.jpg
[18]: https://img0.tuicool.com/N7Fzemm.jpg
[19]: https://img2.tuicool.com/neEBRnJ.jpg
[20]: https://img1.tuicool.com/buMVbae.jpg
[21]: https://img1.tuicool.com/UJzmquy.jpg
[22]: https://img1.tuicool.com/JJBjuaz.jpg
[23]: https://img2.tuicool.com/Fn2eiyZ.jpg
[24]: https://img0.tuicool.com/a2eUbaI.jpg
[25]: https://img0.tuicool.com/uaYRNbr.jpg
[26]: https://img0.tuicool.com/e6FRJjy.jpg
[27]: https://img2.tuicool.com/UnEVvy6.jpg
[28]: https://img0.tuicool.com/6Bruuyy.jpg
[29]: https://img2.tuicool.com/IZBfIrq.jpg
[30]: https://img0.tuicool.com/vAbUfe3.jpg