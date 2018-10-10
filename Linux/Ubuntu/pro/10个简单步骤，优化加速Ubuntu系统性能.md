## 10个简单步骤，优化加速Ubuntu系统性能

来源：[https://www.sysgeek.cn/speed-up-ubuntu-linux-top-10/](https://www.sysgeek.cn/speed-up-ubuntu-linux-top-10/)

时间 2018-08-28 21:34:20

 
![][0]
 
如果您一直观注 Ubuntu 系统性能，可能已经注意到，系统会随着使用时间的推移而逐渐变慢。这本身也不算是什么严重问题，无论你使用 Windows、Linux 或是 macOS 系统，也都是如此。
 
本文中系统极客将向大家汇总 10 个简单步骤，完成这些任务您的 Ubuntu 机器就可以再次以最佳速度运行，并为您提供所需的高效系统性能。
 
## 1.限制「启动应用程序」
 
很多随 Ubuntu 系统自动启动的应用程序其实都没有太大的必要，反而会拖慢系统的启动和登录时间。您可以搜索「启动应用程序」来管理「启动应用程序首选项」。
 
![][1]
 
## 2.减少Grub加载时间
 
当您的 Ubuntu 系统启动时，会显示一个加载选项，让您可以进入双系统启动或进入恢复模式。这个 Grub 加载选单默认会等待 10 秒种才能通过或需要用户手动按「回车」键才能跳过。
 
其实，我们可以将 Grub 等待时间设置到 10 秒以下来让机器启动更快，例如可以通过以下命令来将GRUB_TIMEOUT=10  改成GRUB_TIMEOUT=2  ，这样就只需等待 2 秒：

```
sudo gedit /etc/default/grub
sudo update-grub
```
 
![][2]
 
将 GRUB 等待超时设置得过短将无法选择要引导的（多个）操作系统。
 
## 3. 用TLP降低发热
 
TLP 是一个有助于「系统冷却」的应用程序，可以让 Ubuntu 运行得更快、更顺畅。安装完成后，运行命令启动它即可，而无需任何配置。

```
sudo add-apt-repository ppa:linrunner/tlp
sudo apt update
sudo apt install tlp tlp-rdw
sudo tlp start
```
 
![][3]
 
您还可以 [使用 TLP 延长 Ubuntu 笔记本电池续航时间][8] 。
 
## 4. 设置软件更新镜像
 
无论您的网速是否畅快，确保 Ubuntu 从最佳服务器获取更新始终是一个好习惯。
 
您可以搜索打开「软件和更新」——选择「Ubuntu 软件」选项卡——在「下载自…」中选择一个最适合您的软件源。
 
![][4]
 
国内用户建议选择「阿里云（Aliyun）」或「中国科学技术大学（USTC）」的更新源镜像。
 
## 5.使用apt-fast取代apt-get
 
如果您是 Ubuntu 系统的老鸟用户，应该经常使用apt-get  命令。如果您希望下载速度更快，可以安装apt-fast  并在使用apt-get  命令的地方用apt-fast  来替换。

```
sudo add-apt-repository ppa:apt-fast/stable
sudo apt-get update
sudo apt-get install apt-fast
```
 
[Linux 中 apt 与 apt-get 命令的区别与解释][9]
 
## 6.清理Ubuntu
 
在 Ubuntu 安装之后的带个生命周期中，您一定安装和卸载过很多应用程序。这些使用过的软件都会在系统中留下缓存、应用程序依赖和历史索引等等，积累过多就会限制 Ubuntu 计算机的性能。
 
考虑到这一点，保持您的 PC 清洁就是管理员的一项重要职责。如果您想以最快速、最简单的方式来清理 Ubuntu，可以执行如下两条命令：

```
sudo apt clean
sudo apt autoremove
```
 
![][5]
 
## 7. 启用专有驱动程序
 
在 Ubuntu 系统中为特殊硬件安装和启用专有驱动程序可以大大提高 Ubuntu 计算机的性能，您可以搜索打开「软件和更新」——在「附加驱动」选项卡中为显卡等硬件启用专有驱动程序。
 
![][6]
 
[通过 PPA 为 Ubuntu 安装 Nvidia 驱动][10]
 
## 8.安装Preload
 
Preload（预加载）会在后台工作，以「研究」您如何使用计算机并增强计算机的应用程序处理能力。安装好 Preload 后，您使用频率最高的应用程序的加载速度就会明显快于不经常使用的应用程序。

```
sudo apt install preload
```
 
## 9.使用轻量级的桌面环境
 
Ubuntu 系统可以与许多 DE（桌面环境）兼容，让桌面拥有不同的风格来吸引各种用户。目前可以让您 Ubuntu 性能显着提升的轻量级桌面环境就有 Xfce 和 LXDE。
 
[7 款应用最广泛的 Linux 桌面环境盘点][11]
 
## 10. 移除Apt-Get的翻译包
 
如果你在sudo apt-get update  之后仔细观注过「终端」输出，定然会在其中发现一些与语言翻译有关的行。如果您在服务器上只使用英文，就无需翻译包数据库了。

```
sudo gedit /etc/apt/apt.conf.d/00aptitude
```
 
将这行代码附加到文件末尾：

```
Acquire::Languages "none";
```
 
![][7]


[8]: https://www.sysgeek.cn/improve-battery-life-ubuntu/
[9]: https://www.sysgeek.cn/apt-vs-apt-get/
[10]: https://www.sysgeek.cn/ubuntu-install-nvidia-drivers-ppa/
[11]: https://www.sysgeek.cn/linux-desktop-environments-inventory/
[0]: ../img/ZVRbEfq.jpg
[1]: ../img/Z3A7riM.jpg
[2]: ../img/aAV3UnQ.jpg
[3]: ../img/fMR7Nb7.jpg
[4]: ../img/YbaeIfv.jpg
[5]: ../img/IFFJVzN.jpg
[6]: ../img/VVNnQv3.jpg
[7]: ../img/yaieYrY.jpg