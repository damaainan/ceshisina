## Ubuntu18.04美化主题(mac主题)

来源：[http://www.cnblogs.com/lishanlei/p/9090404.html](http://www.cnblogs.com/lishanlei/p/9090404.html)

时间 2018-05-25 19:36:00

 
前端时间Ubuntu18.04LTS发布，碰巧之前用的Ubuntu16.04出了一点问题，懒得解决，索性就换了Ubuntu18.04。
 
成果：
 
![][0]
 
![][1]
 
![][2]
 
![][3]
 
![][4]
 
参考博客： [https://www.cnblogs.com/feipeng8848/p/8970556.html][40]
 
下面开始进行美化配置：
 
安装主题工具： **`GNOME Tweaks`**  （Ubuntu18.04对软件中心也做了强化，也可以去软件中心进行下载）

```


sudo apt-get update
sudo apt-get install gnome-tweak-tool


```
 
安装完成后打开 **`Tweaks`**  :
 
![][5]
 
修改窗口的按钮位置：
 
![][6]
 
将按钮位置修改到左边：
 
![][7]
 
显示或隐藏桌面上的图标：
 
![][8]
 
去掉shell上无法修改的叹号：
 
![][9]
 
执行命令

```


sudo apt-get install gnome-shell-extensions


```
 
安装完成后打开Tweaks，选择“ **`Extensions`**  ”选项
 
![][10]
 
将“ **`User themes`**  ”设置为 **`ON`** 
 
![][11]
 
开启后去“Appearances”选项中，此时shell的感叹号就没了
 
![][12]
 
现在已经完成了工具的安装配置，下面进行主题美化
 
1. 安装GTK主题
 
链接： [点击打开链接][41]
 
![][13]
 
点击“Files”标签，点击文件名就可以下载
 
![][14]
 
可以看到里面一共有6个压缩文件，分别包装了各种主题，每一个文件名中有一个“2”，这个“2”的意思是该压缩包下有两个主题。
 
我选的是 Gnome-OSC-HS--2themes.tar.xz   （第一个压缩文件），进行下载
 
进行解压：

```


xz -d Gnome-OSC-HS--2themes.tar.xz
tar xvf Gnome-OSC-HS--2themes.tar.xz


```
 
解压后得到的文件夹中有两个文件夹
 
![][15]
 
这两个文件夹分别是两个主题，将其移动到/usr/share/themes/  下就行了。
 
打开之前的工具Tweaks(中文名叫“优化”)，在“外观”选项下可以选择刚刚安装的主题，安装后的截图：
 
![][16]
 
刚刚的两个文件夹就是两个主题，这两个主题从名字上看只有transparent前面是否有个not，意思就是没有透明效果。到现在已经修改了外观样式了。
 
2.修改图标
 
链接： [点击打开链接][42]
 
效果：
 
![][17]
 
我下载的时macOS11的，解压后把文件放到/usr/share/icons/  下
 
![][18]
 
然后去Tweaks中应用一下
 
![][19]
 
3. 修改桌面Shell
 
链接： [点击打开链接][43]
 
下载红框中的
 
![][20]
 
解压后将文件夹放到/usr/share/themes/  目录下。
 
去Tweaks中进行应用：
 
![][21]
 
效果如下：
 
![][22]
 
4.设置开机动画
 
执行命令：

```


vi /etc/alteernatives/default.plymouth


```
 
![][23]
 
如上图所示，default.plymouth文件指定了一个logo文件夹，指定了一个执行脚本。开机的时候就用这个文件制定的logo和脚本执行。那么把这个logo文件夹和脚本制定成我们想要的就可以修改开机动画。
 
开机动画主题链接： [点击打开链接][44]
 
下载后进行解压：
 
![][24]
 
把解压的文件移动到/usr/share/plymouth/themes/  目录下
 
![][25]
 
然后去修改一下/etc/alternatives/default.plymouth
 
先做一下备份：

```


sudo mv /etc/alternatives/default.plymouth /etc/alternatives/default.plymouth.bak


```
 
然后修改成：
 
![][26]
 
5. GDM (GNOME Display Manager,GDM)主题
 
所谓的GDM主题就是登录界面的主题
 
链接： [点击打开链接][45]
 
效果：
 
![][27]
 
解压压缩包后：
 
![][28]
 
修改登录界面样式的原理：
 
在/usr/share/gnome-shell/theme/ubuntu.css  就配置了登录界面的样式。
 
(上面下载的包中，非系统自带的这个ubuntu.css文件)
 
在/usr/share/gnome-shell/theme/ubuntu.css中有这样一行代码：
 
![][29]
 
Ubuntu18.04的登录界面是用css文件渲染的。那么如果只是想替换登录界面的背景，把系统自带的这个css文件中指定图像文件中的位置修改成自己图片的绝对目录就行了。当然我们也可以修改css文件渲染你想要的结果，让你的登录界面炫酷一些。在我们解压的SetAsWallpaperV1.3中，还有一个脚本文件，内容如下：
 
![][30]
 
这个脚本的作用是把你现在正在用的壁纸进行模糊处理，然后放到~/Pictures/gdm_look.jpg,执行过脚本后，你的~/Pictures目录下就会多一个gdm_look.jpg文件，这个文件就是当前是用的壁纸的模糊处理后的图片。
 
然后~/Pictures/gdm_look.jpg又被复制到/usr/share/backgrounds/目录下，再看这个图：
 
![][29]
 
这个包中提供的css文件制定的登录页面壁纸，也就是脚本处理完后复制到/usr/share/backgrounds的gdm_look.jpg。
 
以上就是修改登录界面的原理，操作如下：

```


sudo cp /usr/share/gnome-shell/theme/ubuntu.css /usr/share/gnome-shell/theme/ubuntu.css.backup


```
 
用下图中的ubuntu.css替换系统自带的/usr/share/gnome-shell/theme/ubuntu.css
 
![][32]
 
把SetAsWallpaper脚本文件复制到~/.local/share/nautilus/scripts/  目录下，然后修改下权限

```


sudo chmod +x SetAsWallpaper


```
 
然后重启nautilus

```


nautilus -q 该命令时关闭


```
 
点击桌面右下角“所有应用”,查找“nautilus”
 
![][33]
 
修改/usr/share/backgrounds的权限

```


sudo chmod 777 /usr/share/backgrounds/


```
 
之后去~/.local/share/nautilus/scripts/目录下执行SetAsWallpaper脚本。（执行脚本后你的桌面壁纸可能会没了，重新设置下就好了）
 
最后重启系统，放一张效果图（手机拍的，像素不是很高）：
 
![][34]
 
6. 修改TopBar
 
之前设置的gnome-shell主题是 Sierra-compact-light  ,它的topbar是这样的
 
![][35]
 
字体很粗且很宽，略丑，修改后：
 
![][36]
 ，比较美观，下面开始修改：
 
我们用的Sierra-compact-light主题，所以需要去这个主题下的配置文件（.css）进行修改

```


/usr/share/themes/Sierra-compact-light/gnome-shell/gnome-shell.css


```
 
当然你也如果可以修改Ubuntu默认的TopBar就不能去上面的目录了，而是应该去Ubuntu默认的shell的目录，应该是修改下面这几个文件： gnome-classic.css, gnome-classic-high-contrast.css, gnome-shell.css 
 
![][37]
 
回到/usr/share/theme/Sierra-compact-light/gnome-shell/gnome-shell.css  文件，查找#panel
 
修改TopBar高度：
 
![][38]
 
加粗字体改成正常字体：
 
![][39]
 
然后注销用户再次进入就可以了。
 
以上。
 
### 参考博客：点击打开链接 


[40]: https://www.cnblogs.com/feipeng8848/p/8970556.html
[41]: https://www.opendesktop.org/s/Gnome/p/1171688/
[42]: https://www.opendesktop.org/s/Gnome/p/1102582/
[43]: https://www.opendesktop.org/s/Gnome/p/1013741/
[44]: https://www.opendesktop.org/p/1176419/
[45]: https://www.opendesktop.org/s/Gnome/p/1207015/
[0]: ./img/zqyaauM.png
[1]: ./img/6J7Vfau.png
[2]: ./img/Jr22YnV.png
[3]: ./img/J36bmyY.jpg
[4]: ./img/IfMJbar.png
[5]: ./img/EFr63am.png
[6]: ./img/uQZrmer.png
[7]: ./img/2EFRvij.png
[8]: ./img/JBFNzaZ.png
[9]: ./img/AbQVviv.png
[10]: ./img/b2Irquu.png
[11]: ./img/3ARVZf7.png
[12]: ./img/NfA36j2.png
[13]: ./img/I36vMbQ.png
[14]: ./img/imiM3eF.png
[15]: ./img/Af2myyR.png
[16]: ./img/QJJn6zn.png
[17]: ./img/jIrqAvM.png
[18]: ./img/3eeeA3r.png
[19]: ./img/rQ3AfaE.png
[20]: ./img/JbmIbq2.png
[21]: ./img/nERJBbe.png
[22]: ./img/mIJjemA.png
[23]: ./img/Eb2ay2R.png
[24]: ./img/yiqu6rR.png
[25]: ./img/va2Qrmv.png
[26]: ./img/32iM73N.png
[27]: ./img/A7nEna7.png
[28]: ./img/2IjMZjq.png
[29]: ./img/2qQ3a2b.png
[30]: ./img/2eENb2U.png
[31]: ./img/2qQ3a2b.png
[32]: ./img/fmMJZnU.png
[33]: ./img/BjMr6v7.png
[34]: ./img/rmUBNvA.jpg
[35]: ./img/UNNZ3mf.png
[36]: ./img/yaErQvn.png
[37]: ./img/E7n2Uny.png
[38]: ./img/IzqeI37.png
[39]: ./img/BZfyi2F.png