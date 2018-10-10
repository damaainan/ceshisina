## 在 Ubuntu 18.04 LTS 上安装 Microsoft Windows 字体

来源：[https://linux.cn/article-9860-1.html](https://linux.cn/article-9860-1.html)

时间 2018-07-23 23:33:46

 
大多数教育机构仍在使用 Microsoft 字体， 我不清楚其他国家是什么情况。但在泰米尔纳德邦（印度的一个州）， **`Times New Roman`**  和 **`Arial`**  字体主要被用于大学和学校的几乎所有文档工作、项目和作业。不仅是教育机构，而且一些小型组织、办公室和商店仍在使用 MS Windows 字体。以防万一，如果你需要在 Ubuntu 桌面版上使用 Microsoft 字体，请按照以下步骤安装。
 
免责声明： Microsoft 已免费发布其核心字体。 但 **`请注意 Microsoft 字体是禁止使用在其他操作系统中`**  。在任何 Linux 操作系统中安装 MS 字体之前请仔细阅读 EULA 。我们不负责这种任何种类的盗版行为。
 
（LCTT 译注：本文只做技术探讨，并不代表作者、译者和本站鼓励任何行为。）
 
### 在 Ubuntu 18.04 LTS 桌面版上安装 MS 字体
 
如下所示安装 MS TrueType 字体：

```sh
$ sudo apt update
$ sudo apt install ttf-mscorefonts-installer
```
 
然后将会出现 Microsoft 的最终用户协议向导，点击 **`OK`**  以继续。
 
![][0]
 
点击 **`Yes`**  已接受 Microsoft 的协议：
 
![][1]
 
安装字体之后， 我们需要使用命令行来更新字体缓存：

```sh
$ sudo fc-cache -f -v
```
 
#### 示例输出：

```sh
/usr/share/fonts: caching, new cache contents: 0 fonts, 6 dirs
/usr/share/fonts/X11: caching, new cache contents: 0 fonts, 4 dirs
/usr/share/fonts/X11/Type1: caching, new cache contents: 8 fonts, 0 dirs
/usr/share/fonts/X11/encodings: caching, new cache contents: 0 fonts, 1 dirs
/usr/share/fonts/X11/encodings/large: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/X11/misc: caching, new cache contents: 89 fonts, 0 dirs
/usr/share/fonts/X11/util: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/cMap: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/cmap: caching, new cache contents: 0 fonts, 5 dirs
/usr/share/fonts/cmap/adobe-cns1: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/cmap/adobe-gb1: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/cmap/adobe-japan1: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/cmap/adobe-japan2: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/cmap/adobe-korea1: caching, new cache contents: 0 fonts, 0 dirs
/usr/share/fonts/opentype: caching, new cache contents: 0 fonts, 2 dirs
/usr/share/fonts/opentype/malayalam: caching, new cache contents: 3 fonts, 0 dirs
/usr/share/fonts/opentype/noto: caching, new cache contents: 24 fonts, 0 dirs
/usr/share/fonts/truetype: caching, new cache contents: 0 fonts, 46 dirs
/usr/share/fonts/truetype/Gargi: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/Gubbi: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/Nakula: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/Navilu: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/Sahadeva: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/Sarai: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/abyssinica: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/dejavu: caching, new cache contents: 6 fonts, 0 dirs
/usr/share/fonts/truetype/droid: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/fonts-beng-extra: caching, new cache contents: 6 fonts, 0 dirs
/usr/share/fonts/truetype/fonts-deva-extra: caching, new cache contents: 3 fonts, 0 dirs
/usr/share/fonts/truetype/fonts-gujr-extra: caching, new cache contents: 5 fonts, 0 dirs
/usr/share/fonts/truetype/fonts-guru-extra: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/fonts-kalapi: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/fonts-orya-extra: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/fonts-telu-extra: caching, new cache contents: 2 fonts, 0 dirs
/usr/share/fonts/truetype/freefont: caching, new cache contents: 12 fonts, 0 dirs
/usr/share/fonts/truetype/kacst: caching, new cache contents: 15 fonts, 0 dirs
/usr/share/fonts/truetype/kacst-one: caching, new cache contents: 2 fonts, 0 dirs
/usr/share/fonts/truetype/lao: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/liberation: caching, new cache contents: 16 fonts, 0 dirs
/usr/share/fonts/truetype/liberation2: caching, new cache contents: 12 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-assamese: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-bengali: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-devanagari: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-gujarati: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-kannada: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-malayalam: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-oriya: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-punjabi: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-tamil: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-tamil-classical: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/lohit-telugu: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/malayalam: caching, new cache contents: 11 fonts, 0 dirs
/usr/share/fonts/truetype/msttcorefonts: caching, new cache contents: 60 fonts, 0 dirs
/usr/share/fonts/truetype/noto: caching, new cache contents: 2 fonts, 0 dirs
/usr/share/fonts/truetype/openoffice: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/padauk: caching, new cache contents: 4 fonts, 0 dirs
/usr/share/fonts/truetype/pagul: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/samyak: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/samyak-fonts: caching, new cache contents: 3 fonts, 0 dirs
/usr/share/fonts/truetype/sinhala: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/tibetan-machine: caching, new cache contents: 1 fonts, 0 dirs
/usr/share/fonts/truetype/tlwg: caching, new cache contents: 58 fonts, 0 dirs
/usr/share/fonts/truetype/ttf-khmeros-core: caching, new cache contents: 2 fonts, 0 dirs
/usr/share/fonts/truetype/ubuntu: caching, new cache contents: 13 fonts, 0 dirs
/usr/share/fonts/type1: caching, new cache contents: 0 fonts, 1 dirs
/usr/share/fonts/type1/gsfonts: caching, new cache contents: 35 fonts, 0 dirs
/usr/local/share/fonts: caching, new cache contents: 0 fonts, 0 dirs
/home/sk/.local/share/fonts: skipping, no such directory
/home/sk/.fonts: skipping, no such directory
/var/cache/fontconfig: cleaning cache directory
/home/sk/.cache/fontconfig: cleaning cache directory
/home/sk/.fontconfig: not cleaning non-existent cache directory
fc-cache: succeeded
```
 
### 在 Linux 和 Windows 双启动的机器上安装 MS 字体
 
如果你有 Linux 和 Windows 的双启动系统，你可以轻松地从 Windows C 驱动器上安装 MS 字体。 你所要做的就是挂载 Windows 分区（C:/windows)。
 
我假设你已经在 Linux 中将`C:\Windows`分区挂载在了`/Windowsdrive`目录下。
 
现在，将字体位置链接到你的 Linux 系统的字体文件夹，如下所示：

```sh
ln -s /Windowsdrive/Windows/Fonts /usr/share/fonts/WindowsFonts
```
 
链接字体文件之后，使用命令行重新生成 fontconfig 缓存：

```sh
fc-cache
```
 
或者，将所有的 Windows 字体复制到`/usr/share/fonts`目录下并使用一下命令安装字体：

```sh
mkdir /usr/share/fonts/WindowsFonts
cp /Windowsdrive/Windows/Fonts/* /usr/share/fonts/WindowsFonts
chmod 755 /usr/share/fonts/WindowsFonts/*
```
 
最后，使用命令行重新生成 fontconfig 缓存：

```sh
fc-cache
```
 
### 测试 Windows 字体
 
安装 MS 字体后打开 LibreOffice 或 GIMP。 现在，你将会看到 Microsoft coretype 字体。
 
![][2]
 
就是这样， 希望这本指南有用。我再次警告你，在其他操作系统中使用 MS 字体是被禁止的。在安装 MS 字体之前请先阅读 Microsoft 许可协议。
 
如果你觉得我们的指南有用，请在你的社区、专业网络上分享并支持我们。还有更多好东西在等着我们。持续访问！
 
庆祝吧!!
 
via: [https://www.ostechnix.com/install-microsoft-windows-fonts-ubuntu-16-04/][3]
 
作者： [SK][4] 选题： [lujun9972][5] 译者： [Auk7F7][6] 校对： [wxy][7]
 
本文由 [LCTT][8] 原创编译，Linux中国 荣誉推出


[3]: https://www.ostechnix.com/install-microsoft-windows-fonts-ubuntu-16-04/
[4]: https://www.ostechnix.com/author/sk/
[5]: https://github.com/lujun9972
[6]: https://github.com/Auk7F7
[7]: https://github.com/wxy
[8]: https://github.com/LCTT/TranslateProject
[0]: ./img/YJzqu2Y.png
[1]: ./img/Y7vMVjI.png
[2]: ./img/aUJ7jaE.png