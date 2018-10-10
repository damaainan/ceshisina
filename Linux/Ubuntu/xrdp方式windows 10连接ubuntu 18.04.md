## xrdp方式windows 10连接ubuntu 18.04

来源：[https://zhuanlan.zhihu.com/p/40937988](https://zhuanlan.zhihu.com/p/40937988)

时间 2018-07-31 13:16:14

 
![][0]
 
你一定已经查看了很多远程桌面的复制粘贴文章，稀里糊涂的配置了一大堆，可能连得上也可能连不上，这里记录了确定可以工作的一个解决方案，希望能帮到你。
 
我这边因为需要vmplayer这个软件，此软件一定需要一个gui界面才能启动，所以安装了桌面版的ubuntu，但是远程维护的话，就需要用到远程桌面了。
 
## xrdp vs vnc vs ...
 
xrdp是原生方案，兼容性是最好的，也不需要太多额外的软件支持。其他的请自行google。
 
## 软件安装
 
仅需要

```
sudo apt install xrdp
```
 
## 编辑配置

```
sudo vim /etc/xrdp/startwm.sh
```
 
把最下面的test和exec两行注释掉，添加一行

```
gnome-session
```
 
## 重新启动ubuntu，不要登录！
 
## windows远程桌面连接

 
* windows打开远程桌面输入ubuntu主机ip或者主机名 
* 选择xorg，输入用户名密码 
* 会提示几次授权修改主机的颜色设置什么的，都可以cancel掉，然后即可登陆成功 
 


[0]: ./img/bAnEZbz.jpg