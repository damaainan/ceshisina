## Linux/Unix 多线程下载工具 axel 的使用

来源：[http://www.jianshu.com/p/341f60ad4246](http://www.jianshu.com/p/341f60ad4246)

时间 2018-04-21 15:02:07


我们在通过Linux/Unix学习的时候，尤其是使用Linux server版的时候，一个高效的下载工具会显得尤为重要，尤其是多线程的下载工具，这里就简单说一下多线程下载工具axel的使用。

* 安装        

Linux环境安装        

macOS环境安装      
* 使用
* 总结

## 安装

### Linux环境下安装

     Debian/Ubuntu         系统：

在terminal中输入：
`sudo apt-get install axel`

回车输入密码等待完成

     CentOS/Fedora         系统：

在terminal中输入：
`sudo yum install axel`

回车输入密码等待完成

### macOS环境下安装

首先先安装Homebrew：打开任意terminal（Terminal或iTerm2）并输入
`/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"`

然后回车等待完成

接下来通过homebrew安装axel

在Terminal终端中输入
`brew install axel`

回车并等待完成

## 使用


首先，当我们要下载一个文件的时候，首先需要复制这个文件的下载链接，接下来，我们就需要在terminal中输入命令进行下载

使用方法

```
axel -a -n 10 (下载链接) -o (下载位置目录)
参数说明：
-a: 表示查看整体进度
-n: 表示多线程下载的线程数，后面跟数字，如：后面是10表示10个线程下载，如果不加-n参数，则会默认4个线程下载
-o: 表示要下载的位置目录，如下载到当前位置，则是-o ./ 如下载到home目录下的Downloads目录下，则是-o ~/Downloads
```

## 总结

axel是一个多线程下载工具，支持断点下载，如由于网络或其他原因下载中断了，下次下载仍然可以接着上次的下载，而不必从头下载，大大节省了时间，提高了效率，目前同类型的下载工具还有Aria2，windows的用户可以尝试一下Aria2，需要的同学可以自行Google。
