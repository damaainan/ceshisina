## 教你一步一步在vim中配置史上最难安装的You Complete Me

来源：[https://juejin.im/post/5b597a9cf265da0f9402b434](https://juejin.im/post/5b597a9cf265da0f9402b434)

时间 2018-07-30 15:59:08

 
最近在Linux下用vim写Python，vim被称作编辑器之神，写起代码来也是6的飞起，不用鼠标纯键盘操作看起来就有种高大上的感觉，但是美中不足的是，vim并没有自动补全，这对于用惯了IDE的朋友们来说肯定是一大不习惯，于是我查了各种资料，终于一步一步的，在Ubuntu下配置好了号称史上最难安装的自动补全插件——YouCompleteMe 。 YouCompleteMe是vim中一款非常好用的自动补全插件，支持多种语言的自动补全。vim搭配YCM使用算是如虎添翼。接下来，就来手把手的教你在Linux系统下配置YCM 安装后的效果：
 
 ![][0]
 系统版本：Ubuntu 16.04 LTS 需要的其他东西： `git``cmake``Vundle``YouCompleteMe
 
### git
 
首先，检查系统是否安装了git，git是前期准备工作，是为了使用这个工具在github上面下载Vundle和Youcomplete的源码，在终端中输入以下命令
 
```
git —version
```
 
如果终端返回了git的版本，那么恭喜你，当前系统中已经安装了git，如果终端提示命令没有找到，那么在终端输入：
 
```
sudp apt-get install git
```
 
等进度条走完，git就成功的安装在你的系统中了。 **`BTW，记得联网 。`** 
 
### cmake
 
使用同样的方式，检查cmake是否安装在系统中，如果没有，在最后一部编译的时候会报错。同样的，在终端输入:
 
```
sudo apt-get install cmake
```
 
### Vundle
 
这一步，我们要用到git工具了。在终端中输入以下命令：
 
```
git clone https://github.com/VundleVim/Vundle.vim.git ~/.vim/bundle/Vundle.vim
```
 
等待系统clone完成。 输入命令：
 
```
cd ~
```
 
### 进入home路径下
 
输入命令：
 
```
gedit .vimrc
```
 
### 编辑配置文件，在文件的开头添加如下代码：
 
```
set nocompatible              "  必需
filetype off                  " 必需
"  将运行时的路径设置为包括Vundle并初始化
set rtp+=~/.vim/bundle/Vundle.vim
call vundle#begin()
" 使Vuldle管理自己
Plugin 'VundleVim/Vundle.vim'
"最后要写入YouCompleteMe管理语句的位置
" 你所有的插件必需在这一行之前添加
call vundle#end()            " required
filetype plugin indent on    " required
```
 
启动vim，并输入：
 
```
:PluginInstall
```
 
注意：区分大小写
 
### YouCompleteMe
 
输入以下命令
 
```
cd ~/.vim/bundle
git clone https://github.com/Valloric/YouCompleteMe.git
```
 
首先进入到YouCompleteMe目录下：
 
```
cd ~/.vim/bundle/YouCompleteMe
```
 
输入以下命令：
 
```
git submodule update —init -recursive
```
 
上面的过程可能要持续几分钟，等带完成后，开始编译YCM所有支持的语言：
 
```
./install.py —all
```
 
编译的过程同样也是比较慢的，请耐心等待。 等待编译完成后，在.vimrc文件中添加：
 
```
Plugin 'VundleVim/YouCompleteMe'
```
 
添加位置已经在上文中给出。 现在打开vim新建一个文件，已经敲过一次的代码，就已经有了补全功能了，部分Python标准库中的方法、变量都可以补全，开启你的大神之路吧！ 参考链接：
 
[YCM][1]
 
[Vunlde][2]
 


[1]: https://github.com/FValloric/YouCompleteMe/blob/master/README.md
[2]: https://github.com/FVundleVim/Vundle.vim/blob/master/README.md
[0]: ./img/jIrAj2U.gif