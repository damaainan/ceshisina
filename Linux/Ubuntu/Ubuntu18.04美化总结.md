## Ubuntu18.04美化总结

来源：[http://www.kowen.cn/2018/08/08/2018-08-Ubuntu18-04美化总结/](http://www.kowen.cn/2018/08/08/2018-08-Ubuntu18-04美化总结/)

时间 2018-08-08 17:11:25

 
  
bykowen [简书][7]
 
话不多说先来几张图片镇镇楼，这里用的是仿Mac主题，喜欢其他的主题可以去gnome-look上下载

![][0]

![][1]

![][2]

 
## 桌面美化 
 
### 资源下载 
 
gnome3主题资源： [https://www.gnome-look.org/][8]
 
在此使用MacOs主题： [https://www.gnome-look.org/s/Gnome/p/1013714/][9] ，选择file下载需要的主题、图标、字体和背景等资源。其中有多个主题（以Sierra开头），可以只下载需要的，带solid的是非透明主题。
 
主题解压后的目录放到/usr/share/themes/，图标放到/usr/share/icons/，背景放到/usr/share/backgrounds/
 
### 工具安装 

```
sudo apt-get update
sudo apt-get install gnome-tweak-tool
sudo apt-get install gnome-shell-extensions
sudo apt-get install  gnome-shell-extension-dashtodock


```
 
### 美化过程 

 
* 打开tweak，选择扩展，打开User themes选项 
* 选择外观，按以下图片进行设置
 
如果shell上有感叹号，关闭tweak，按Alt+F2，输入r，执行后重新打开tweak再设置shell选项。

![][3]

  
* 调整标题栏按钮位置
选择窗口，滑到最下面，把放置改为左
  
* 虽然桌面现在看起来很像mac了，但是dock这时依旧是长条状的，下面使用dash to dock插件使dock居中 
 选择扩展，开启Dash to dock，点击设置图标，按图片进行设置 

![][4]

 
## 启动美化 

 
* 去 [https://www.opendesktop.org/p/1154790/下载文件，解压备用][10]  
* 把解压的Ubuntu-paw文件夹复制到/usr/share/plymouth/themes/ 
* 备份并编辑文件

```
sudo cp /etc/alternatives/default.plymouth /etc/alternatives/default.plymouth.bak
sudo gedit /etc/alteernatives/default.plymouth


```
  
* 最后两行按照以下方式修改为新加入的启动主题

![][5]

 
## 登录界面美化 
 
登录界面美化即是修改GDM (GNOME Display Manager,GDM)主题，可以使用 [https://www.opendesktop.org/s/Gnome/p/1207015/][11] ，但我在试验中发现一些问题。所以只改了登录背景。

```
sudo cp /etc/alternatives/gdm3.css /etc/alternatives/gdm3.css.bak
sudo gedit /etc/alternatives/gdm3.css


```
 
找到lockDialogGroup并更改为以下内容：

```
#lockDialogGroup {
  background: #2c001e url(file:///usr/share/backgrounds/HighSierra-wallpapers/Sierra.jpg);         
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center; 
}


```
 
  
锁定之后即可看到效果，以下是手机拍摄的。

![][6]

 
以上是对桌面、启动和锁屏界面的美化过程，还有字体、光标等美化感觉Ubuntu自带的已经非常不错了，没有深究，喜欢的快试试吧！


[7]: https://www.jianshu.com/u/e938fd073edf
[8]: https://www.gnome-look.org/
[9]: https://www.gnome-look.org/s/Gnome/p/1013714/
[10]: https://www.opendesktop.org/p/1154790/%E4%B8%8B%E8%BD%BD%E6%96%87%E4%BB%B6%EF%BC%8C%E8%A7%A3%E5%8E%8B%E5%A4%87%E7%94%A8
[11]: https://www.opendesktop.org/s/Gnome/p/1207015/
[0]: ./img/FrmiaiR.png
[1]: ./img/ruymamb.png
[2]: ./img/rYjEFza.png
[3]: ./img/yYzAZzQ.png
[4]: ./img/ARRjI3i.png
[5]: ./img/VnyUr2Q.png
[6]: ./img/mAZj63b.jpg