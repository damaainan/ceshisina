## Ubuntu中添加软件源与ppa源

来源：[https://blog.mythsman.com/2016/01/02/1/](https://blog.mythsman.com/2016/01/02/1/)

时间 2017-07-18 23:34:21


Ubuntu下的软件的更新是以一种不同与windows下的方式进行的。windows下的软件更新，是通过打开应用后，应用自动联网查看更新然后来提醒用户。而Ubuntu下，用户只需要隔三差五的运行下`apt-get update`命令就可以通过查看软件的各个源来获取所有软件的更新信息。那么，apt-get 命令为什么能做到这个呢？其实是因为apt-get 命令有一个源列表，他所有提供的软件都是从这个列表上获取的，那么很自然，每当软件有了最新的版本，apt总能够通过查看当前的列表获取得到，从而反馈给用户。


## 软件源

这个源列表就是`/etc/apt/sources.list`。这里记录了源的地址，格式基本如下所示：

```
# deb cdrom:[Ubuntu 14.04.2 LTS _Trusty Tahr_ - Release amd64 (20150218.1)]/ trusty main restricted

# See http://help.ubuntu.com/community/UpgradeNotes for how to upgrade to
# newer versions of the distribution.
deb http://ubuntu.cn99.com/ubuntu/ trusty main restricted
deb-src http://ubuntu.cn99.com/ubuntu/ trusty main restricted

## Major bug fix updates produced after the final release of the
## distribution.
deb http://ubuntu.cn99.com/ubuntu/ trusty-updates main restricted
deb-src http://ubuntu.cn99.com/ubuntu/ trusty-updates main restricted
......


```

这里的ubuntu.cn99.com就是我们镜像的地址，决定着源的更新访问的速度。可以通过修改这个来使用最佳的源。当然，每次修改完源之后，还要执行update 命令来使系统重新识别一下。


## ppa源

当然，系统自带的源是很有限的，我们肯定需要一些其他的软件包，然而如果是直接下载deb格式的文件的话，又不能获取到更新和维护。所以这就用到了十分重要的ppa源了。

所谓ppa源，就是指“Personal Package Archives”，也就是个人软件包集。这其实是一个网站，即－    [launchpad.net][0]
。Launchpad是Ubuntu母公司canonical有限公司所架设的网站，是一个提供维护、支援或联络Ubuntu开发者的平台。由于不是所有的软件都能进入Ubuntu的官方的软件库，launchpad.net 提供了ppa，允许开发者建立自己的软件仓库，自由的上传软件。供用户安装和查看更新。

加入ppa源的命令：`sudo add-apt-repository ppa:user/ppa-name`删除ppa源的命令：`sudo add-apt-repository -r ppa:user/ppa-name`比如我们要添加wine的源，就可以执行：`sudo add-apt-repository ppa:ubuntu-wine/ppa`好了，让我们看看添加完ppa源之后到底发生了什么：

```
myths@Business:~$ cd /etc/apt/sources.list.d/
myths@Business:/etc/apt/sources.list.d$ ls
myie-browser.list       sogoupinyin.list
myie-browser.list.save  sogoupinyin.list.save
openalpr.list           ubuntu-wine-ppa-trusty.list
openalpr.list.save


```

恩，其实是在`/etc/apt/sources.list.d/`文件夹里放了一个文件，我们打开来看看：

```
myths@Business:/etc/apt/sources.list.d$ cat ubuntu-wine-ppa-trusty.list 
deb http://ppa.launchpad.net/ubuntu-wine/ppa/ubuntu trusty main
# deb-src http://ppa.launchpad.net/ubuntu-wine/ppa/ubuntu trusty main


```

原来就是添加了一个跟软件源一模一样的东西，他们的作用殊途同归啊。我想其实只是为了分辨官方的源和第三方的源，Ubuntu才设计成在sources.list 和 sources.list.d/ 这两个地方中存储源。因为第三方的源毕竟不太可信，如果随便更新的话可是会出事情的。


[0]: http://launchpad.net