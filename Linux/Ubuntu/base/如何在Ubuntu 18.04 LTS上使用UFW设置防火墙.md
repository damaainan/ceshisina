## 如何在Ubuntu 18.04 LTS上使用UFW设置防火墙

来源：[https://www.sysgeek.cn/ubuntu-18-04-ufw/](https://www.sysgeek.cn/ubuntu-18-04-ufw/)

时间 2018-08-13 12:31:36

 
![][0]
 
正确配置防火墙是整个系统安全中最重要的方面之一。默认情况下，Ubuntu 18.04 LTS 附带了一个名为 UFW（ **`U`**  ncomplicated **`F`**  irewall）的防火墙配置工具，UFW 是一个「用户友好」的前端，可以用于 [管理 iptables 防火墙规则][8] ，其主要目的就是让管理 iptables 更加轻松容易。
 
## 安装UFW
 
Ubuntu 18.04 LTS 系统中已经默认附带了 UFW 工具，如果您的系统中没有安装，可以在「终端」中执行如下命令进行安装：

```sh
sudo apt install ufw
```
 
## 检查UFW状态
 
安装完成后，您可以使用以下命令检查 UFW 的状态：

```sh
sudo ufw status verbose
```
 
无论您使用的是 Ubuntu 18.04 系统附带还是刚手动安装的 UFW，默认都是禁用状态，所以输出是「不活动」：
 
![][1]
 
如果激活 UFW，输出会类似于以下内容：
 
![][2]
 
## UFW默认策略
 
防火墙策略是构建用户自定义规则的基础，在绝大多数情况下，初始的 UFW 默认策略就是一个很好的起点。
 
而默认情况下，UFW 将阻止所有传入连接并允许所有传出连接。也就是说，除非您专门打开特定端口，否则任何尝试访问您的服务器的人都无法连接，但服务器上运行的应用程序和服务却能够对外访问。
 
UFW 默认策略在/etc/default/ufw  文件中进行定义，可以使用sudo ufw default  命令对策略进行更改。
 
## 应用程序策略
 
当您使用 apt 安装软件包时，应用程序配置文件就会添加到/etc/ufw/applications.d  目录当中，该目录主要用于 **`描述服务`**  并 **`存放 UFW 设置`**  。
 
我们可以使用以下命令列出所有应用程序配置策略：

```sh
sudo ufw app list
```
 
根据当前系统上已安装的软件包，输出类似于：
 
![][3]
 
如果要查找有关配置文件和包含规则的更多信息，可以使用类似以下命令：

```sh
sudo ufw app info 'Nginx Full'
```
 
![][4]
 
从上面的输出可以看出「Nginx Full」配置文件会打开 80 和 443 端口 。
 
## 允许SSH连接
 
在服务器上正式启用 UFW 防火墙之前，需要事先添加允许 SSH 连接的传入规则。不然 UFW 启用之后 SSH 连不上了不要跑来闹哦……
 
要配置 UFW 防火墙以允许传入 SSH 连接，请键入以下命令：

```sh
sudo ufw allow ssh
```
 
![][5]
 
如果您的 SSH 端口是自定义的，没使用默认的 22 端口，可以通过如下命令侦听并允许该端口上的连接，例如 4422 端口：

```sh
sudo ufw allow 4422/tcp
```
 
## 启用UFW
 
如果您的 UFW 防火墙已配置为允许传入 SSH 连接，则可以执行以下命令启用 UFW：

```sh
sudo ufw enable
```
 
![][6]
 
如果看到启用防火墙可能会中断现有的 ssh 连接的警告，只需键入y  同意并按「回车」键即可。
 
## 允许侦听传入端口连接
 
根据 Ubuntu 服务器上运行的应用程序和特定需要，您可能需要允许其他端口的传入连接。下面系统极客将演示如何允许常见服务的示例。
 
### 打开80端口——HTTP
 
可以使用以下命令允许 HTTP 连接：

```sh
sudo ufw allow http
```
 
也可以直接指定端口号 80：

```sh
sudo ufw allow 80/tcp
```
 
或者也可以使用应用程序配置文件，在本例中为「Nginx HTTP」：

```sh
sudo ufw allow 'Nginx HTTP'
```
 
### 打开443端口——HTTPS
 
可以使用以下命令允许 HTTPS 连接：

```sh
sudo ufw allow https
```
 
也可以直接指定端口号 443：

```sh
sudo ufw allow 443/tcp
```
 
或者也可以使用应用程序配置文件，在本例中为「Nginx HTTPS」：

```sh
sudo ufw allow 'Nginx HTTPS'
```
 
### 打开8080端口
 
如果运行 Tomcat 或使用侦听 8080 端口的应用程序，可以执行以下命令允许传入连接：

```sh
sudo ufw allow 8080/tcp
```
 
## 允许端口范围
 
除允许单个端口连接之外，UFW 还允许直接配置端口范围。在使用 UFW 的端口范围时，必需指定 tcp 或 udp 协议。例如，要开启服务器上 7100 到 7200 的 tcp 和 udp 端口，可以运行以下命令：

```sh
sudo ufw allow 7100:7200/tcp
sudo ufw allow 7100:7200/udp
```
 
## 允许特定IP地址
 
如果您要允许某个 IP 地址的所有端口访问，可以使用如下命令：

```sh
sudo ufw allow from 123.123.123.123
```
 
## 允许子网
 
如果要允许特定子网范围的计算机对服务器某个端口的访问，例如：允许从 192.168.1.1 到 192.168.1.254 网段到服务器 3306（MySQL）端口的访问，可以执行如下命令：

```sh
sudo ufw allow from 192.168.1.0/24 to any port 3306
```
 
## 拒绝连接
 
前面已经介绍过，传入连接的默认策略都被设置为拒绝。假设您打开了 80 和 443 端口，而服务器又受到来自 23.34.45.0/24 的攻击，可以通过如下命令拒绝该网络的所有连接：

```sh
sudo ufw deny from 23.34.45.0/24
```
 
如果只想拒绝访问 80 和 443 端口，则可以使用以下命令：

```sh
sudo ufw deny from 23.34.45.0/24 to any port 80
sudo ufw deny from 23.34.45.0/24 to any port 443
```
 
编写拒绝规则与编写允许规则相同，您只需要将 allow 替换为 deny 就成。
 
## 删除UFW策略
 
我们可以根据 **`规则编号`**  和 **`指定实际规则`**  这 2 种方式来删除 UFW 规则。
 
对新手用户而言，通过规则编号来删除特定规则比较好，不过在此之前需要先用命令列出规则编号的数字：

```sh
sudo ufw status numbered
```
 
![][7]
 
例如要删除开放 8080 端口的规则 4 可以使用如下命令：

```sh
sudo ufw delete 4
```
 
第二种方法是通过 **`指定实际规则`**  来进行删除操作，例如要删除打开8069 端口的规则，可以使用如下命令：

```sh
sudo ufw delete allow 8069
```
 
## 禁用UFW
 
如果想要停止使用 UFW 并停用所有规则，可以直接选择禁用 UFW：

```sh
sudo ufw disable
```
 
## 重置UFW
 
在重置 UFW 时，系统会禁用 UFW 并删除所有活动规则。如果您想要还原所有更改并重新开始，可以使用如下命令：

```sh
sudo ufw reset
```


[8]: https://www.sysgeek.cn/iptables-secure-linux-desktop/
[0]: ../img/6ZZna2A.jpg
[1]: ../img/NN3aeqM.jpg
[2]: ../img/nInyeuF.jpg
[3]: ../img/EryINrz.jpg
[4]: ../img/ruEniqv.jpg
[5]: ../img/Ezymaui.jpg
[6]: ../img/RvmQBbe.jpg
[7]: ../img/bqiAv2Y.jpg