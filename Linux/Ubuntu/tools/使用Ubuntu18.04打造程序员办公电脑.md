## 使用Ubuntu18.04打造程序员办公电脑

来源：[https://liyang.pro/using-ubuntu-1804/](https://liyang.pro/using-ubuntu-1804/)

时间 2018-05-25 13:10:00

 
gnome美化工具，可改变主题，图标，字体等。

```sh
sudo apt install gnome-tweak-tool


```

 
### uget 
 
一个界面下载工具，可配合aria2

```sh
sudo add-apt-repository ppa:plushuang-tw/uget-stable
sudo apt update
sudo apt install uget


```

 
### aria2 
 
Aria2是一个命令行下轻量级、多协议、多来源的下载工具（支持 HTTP/HTTPS、FTP、BitTorrent、Metalink），内建 XML-RPC 和 JSON-RPC 用户界面。

```sh
sudo apt-get install aria2


```

 
### gnome-shell-extensions 
 
GnomeShell扩展是官方为增强GNOME Shell功能所开发的。

```sh
sudo apt install gnome-shell-extensions


```

 
### chrome-gnome-shell 
 
虽然有chrome的关键字，但是真的和Chrome没多大关系。美化必装~

```sh
sudo apt install chrome-gnome-shell


```

 
### wiz笔记 
 
为知笔记在新版中使用了appimage格式，下载地址为： [https://url.wiz.cn/u/Wizlinux][3]
 
下载完成后，右键WizNote-2.5.9-x86_64.AppImage，赋予可执行权限，或者

```sh
chmod +x WizNote-2.5.9-x86_64.AppImage


```

 
移动或删除此文件会导致winnote无法打开。
 
### WPS 
 
下载地址： [http://wps-community.org/download.html][4]
 
安装完成后会提示字体的问题，可通过 [https://pan.baidu.com/s/1o8ujqhc下载字体包，提取出来以后，双击安装即可。][5]
 
### 搜狗输入法 
 
请自行去官方下载，安装前请执行

```sh
sudo apt install --fix-broken


```

 
否则无法安装，当然这个情况并不是每次都这样，ubuntu18.04我安装过3次，其中2次是直接就安装成功了，有一次安装时提示失败，然后执行了以上命令，再次安装即成功。
 
### Albert 
 
熟悉mac的用户应该知道Alfred，这个软件同样也具备类似的功能

```sh
sudo add-apt-repository ppa:nilarimogard/webupd8
sudo apt-get update
sudo apt-get install albert -y


```

 
### 系统负载指示器 

```sh
sudo apt-get install -y indicator-multiload


```

 
### Nvidia驱动 

```sh
sudo add-apt-repository ppa:graphics-drivers/ppa
sudo apt-get update


```

 
### VS Code 
 
去官方下载安装包，安装即可
 
### Vim 
 
必不可少的工具

```sh
sudo apt-get install vim


```

 
设置默认的编辑器

```sh
update-alternatives --config editor


```

 
然后选择`vim.basic`这项即可，不要选vim.tiny，这个vim功能太少了。
 
### git 
 
必不可少的工具

```sh
sudo apt-get install git


```

 
### Chrome 

```sh
sudo wget http://www.linuxidc.com/files/repo/google-chrome.list -P /etc/apt/sources.list.d/

wget -q -O - https://dl.google.com/linux/linux_signing_key.pub  | sudo apt-key add

sudo apt update

sudo apt install google-chrome-stable


```

 
### shadowsocks-qt5 
 
梯子必备

```sh
sudo add-apt-repository ppa:hzwhuang/ss-qt5
sudo apt-get update
sudo apt-get install shadowsocks-qt5


```

 
此时会报错

```sh
liyang@liyang:~$ sudo add-apt-repository ppa:hzwhuang/ss-qt5
 Shadowsocks-Qt5 is a cross-platform Shadowsocks GUI client.

Shadowsocks is a lightweight tool that helps you bypass firewall(s).

This PPA mainly includes packages for Shadowsocks-Qt5, which means it also includes libQtShadowsocks packages.
 更多信息： https://launchpad.net/~hzwhuang/+archive/ubuntu/ss-qt5
按 [ENTER] 继续或 Ctrl-c 取消安装。

命中:1 http://mirrors.aliyun.com/ubuntu bionic InRelease                       
命中:2 http://mirrors.aliyun.com/ubuntu bionic-updates InRelease               
忽略:3 http://ppa.launchpad.net/hzwhuang/ss-qt5/ubuntu bionic InRelease        
命中:4 http://mirrors.aliyun.com/ubuntu bionic-backports InRelease
命中:5 http://mirrors.aliyun.com/ubuntu bionic-security InRelease              
错误:6 http://ppa.launchpad.net/hzwhuang/ss-qt5/ubuntu bionic Release          
  404  Not Found [IP: 91.189.95.83 80]
正在读取软件包列表... 完成
E: 仓库 “http://ppa.launchpad.net/hzwhuang/ss-qt5/ubuntu bionic Release” 没有 Release 文件。
N: 无法安全地用该源进行更新，所以默认禁用该源。
N: 参见 apt-secure(8) 手册以了解仓库创建和用户配置方面的细节。


```

 
点击 [https://launchpad.net/~hzwhuang/+archive/ubuntu/ss-qt5，发现并没有18.04版本bionic的Package，于是改成17.10版本artful即可。][6]

```sh
sudo gedit（vim） /etc/apt/sources.list.d/hzwhuang-ubuntu-ss-qt5-bionic.list


```

 
修改成如下内容：

```sh
deb http://ppa.launchpad.net/hzwhuang/ss-qt5/ubuntu artful main


```

 
安装完成后，填入ss信息，点击链接，如果没有提示超时并且有延迟的数值就表示已经爬上梯子了。
 
配置代理，在`设置`->`网络`-> 开启VPN中，选择手动代理,代理地址为socks5

```sh
127.0.0.1:1080


```

 
但是此时并不太智能，是所谓的全局模式，于是我们需要配置pac

```sh
sudo apt-get install python-pip
sudo pip install genpac


```

 
在家目录(/home/liyang)下面创建文件夹

```sh
mkdir ~/shadowsocks
cd ~/shadowsocks


```

 
生成PAC

```sh
genpac --pac-proxy "SOCKS5 127.0.0.1:1080
" --gfwlist-proxy="SOCKS5 127.0.0.1:1080
" --gfwlist-url=https://raw.githubusercontent.com/gfwlist/gfwlist/master/gfwlist.txt --output="autoproxy.pac"

```

 
如果有人提示改地址无法连接，那是因为你的ss-qt5没开，或者你的梯子有问题。
 
然后配置代理模式为自动代理，填入如下内容：

```sh
file:///home/liyang/shadowsocks/autoproxy.pac


```

 
### JDK，IDEA 

```sh
mkdir /usr/local/java
tar -zxvf jdk1.8.0_171.tar.gz -C /usr/local/java
sudo vim /etc/profile
## 在最下面填入：
export JAVA_HOME=/usr/local/java/jdk1.8.0_171
export CLASSPATH=.:$JAVA_HOME/lib:$JAVA_HOME/jre/lib:$CLASSPATH  
export PATH=$JAVA_HOME/bin:$JAVA_HOME/jre/bin:$PATH


```

 
IDEA

```sh
mdkir /usr/local/jetbrains
tar -zxvf idea-IU-181.5087.20.tar.gz -C /usr/local/jetbrains/
cd /usr/local/jetbrains/idea-IU-181.5087.20/bin
./idea.sh


```

 
新版的IDEA在执行idea.sh后会自动在/usr/share/applications里面创建桌面图标，无需再手动创建了。
 
### 深度截图 

```sh
sudo apt-get install deepin-screenshot


```

 
  
设置快捷键

![][0]

 
### SecureCRT 
 
这可能是唯一一个Win/macOS/Linux下都比较好用的shell管理工具了，售价也不便宜，财务还未自由的可以选择Crack一下。
 
下载`scrt-8.3.3-1646.ubuntu17-64.x86_64.deb`[下载地址][7] 。
 
下载完成后直接双击安装即可，安装完成后有钱淫直接输入LicenseData，穷如似我这般的就进行如下操作。
 
下载破解脚本

```sh
wget http://download.boll.me/securecrt_linux_crack.pl


```

 
执行如下命令进行破解

```sh
sudo perl securecrt_linux_crack.pl /usr/bin/SecureCRT


```

```sh
sudo perl securecrt_linux_crack.pl /usr/bin/SecureCRT

[sudo] liyang 的密码： 
crack successful

License:

  Name:    xiaobo_l
  Company:  www.boll.me
  Serial Number:  03-94-294583
  License Key:  ABJ11G 85V1F9 NENFBK RBWB5W ABH23Q 8XBZAC 324TJJ KXRE5D
  Issue Date:  04-20-2017

```

 
  
填入LicenseData

![][1]

 
### Telegram 
 
  
曾经,在Ubuntu（Linux）系统下并没有比较称心如意的聊天工具，由于经常需要在不同的系统之间互相传送消息、文件，而wineqq会出现很多问题，于是乎，使用telegram便可以解决这个需求，只不过在用的时候需要踩着梯子。

![][2]

 
## 美化篇 
 
### 主题推荐 
 
#### Sierra-gtk-theme 
 
项目地址： [https://github.com/vinceliuice/Sierra-gtk-theme][8]
 
主题地址： [https://www.gnome-look.org/p/1013714/][9]
 
安装方法：

```sh
sudo apt-get install gtk2-engines-murrine gtk2-engines-pixbuf
sudo add-apt-repository ppa:dyatlov-igor/sierra-theme
sudo apt install sierra-gtk-theme


```

 
#### numix-gtk-theme 

```sh
sudo add-apt-repository ppa:numix/ppa
sudo apt-get update
sudo apt-get install numix-gtk-theme numix-icon-theme-circle


```

 
### Docky 

```sh
sudo apt-get install docky


```

 
把不想要的图标，点击左键网上拖就可以删除。这个软件的效果还不错，可惜无法避免系统自带的dock，于是如果有“洁癖”的同学可以选择dash to dock这个插件。


[3]: https://url.wiz.cn/u/Wizlinux
[4]: http://wps-community.org/download.html
[5]: https://pan.baidu.com/s/1o8ujqhc%E4%B8%8B%E8%BD%BD%E5%AD%97%E4%BD%93%E5%8C%85%EF%BC%8C%E6%8F%90%E5%8F%96%E5%87%BA%E6%9D%A5%E4%BB%A5%E5%90%8E%EF%BC%8C%E5%8F%8C%E5%87%BB%E5%AE%89%E8%A3%85%E5%8D%B3%E5%8F%AF%E3%80%82
[6]: https://launchpad.net/~hzwhuang/+archive/ubuntu/ss-qt5%EF%BC%8C%E5%8F%91%E7%8E%B0%E5%B9%B6%E6%B2%A1%E6%9C%8918.04%E7%89%88%E6%9C%ACbionic%E7%9A%84Package%EF%BC%8C%E4%BA%8E%E6%98%AF%E6%94%B9%E6%88%9017.10%E7%89%88%E6%9C%ACartful%E5%8D%B3%E5%8F%AF%E3%80%82
[7]: https://secure.vandyke.com/cgi-bin/download.php
[8]: https://github.com/vinceliuice/Sierra-gtk-theme
[9]: https://www.gnome-look.org/p/1013714/
[0]: ../img/fENjeyq.jpg
[1]: ../img/2euuYfa.jpg
[2]: ../img/Yrm2uyQ.png