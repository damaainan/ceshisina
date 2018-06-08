# 使用Tmux提高终端环境下的工作效率

 时间 2016-05-27 08:00:00  [cpper][0]

_原文_[http://cpper.info/2016/05/27/tmux.html][1]

 主题 [tmux][2]

## 0. 介绍

Tmux（Terminal Multiplexer）是一个优秀的终端复用软件，类似GNU Screen，但来自于OpenBSD，采用BSD授权。使用它最直观的好处就是，通过一个终端登录远程主机并运行tmux后，在其中可以开启多个控制台而无需再“浪费”多余的终端来连接这台远程主机，这允许我们在单个屏幕的灵活布局下开出很多终端，我们就可以协作地使用它们。比如在一个终端页上我们可用Vim修改一些文件，在另一个面板可以跟踪一些日志。

mux基于典型的c/s模型，主要分为会话、窗口和面板三个元素：

* Session：输入tmux后就创建了一个会话，一个会话是一组窗体的集合。
* Window：会话中一个可见的窗口。
* Pane:一个窗口可以分成多个面板。

![][3]

## 1. 安装

如果能直接apt-get或者yum在线安装就更好了，否则就需要源码安装了。

安装tmux所需要的依赖：

    wget https://sourceforge.net/projects/levent/files/libevent/libevent-2.0/libevent-2.0.22-stable.tar.gz
    tar xf libevent-2.0.22-stable.tar.gz && cd libevent-2.0.22-stable
    ./configure
    make && make install
    ln -s /usr/local/lib/libevent-2.0.so.5 /usr/lib64/libevent-2.0.so.5
    

安装tmux软件包：

    wget http://iweb.dl.sourceforge.net/project/tmux/tmux/tmux-2.0/tmux-2.0.tar.gz
    tar xf tmux-2.0.tar.gz  && cd tmux-2.0
    ./configure --prefix=/usr/local/tmux
    make && make install
    

## 2. 使用

Tmux的所有操作必须使用一个前缀进入命令模式，默认前缀为Ctrl+b，很多人会改为Ctrl+a（gnu screen的命令前缀）,你可以修改tmux.conf配置文件来修改默认前缀：

    # 前缀设置为<Ctrl-a>
    set -g prefix C-a
    # 解除<Ctrl-b>
    ubind C-b
    

### 在不同会话下工作

使用Tmux的最好方式是使用会话的方式，这样你就可以以你想要的方式，将任务和应用组织到不同的会话中。如果你想改变一个会话，会话里面的任何工作都无须停止或者杀掉。

让我们开始一个叫做"my_session"的会话：

    tmux new -s my_session
    

然后输入CTRL-b d从此会话脱离，想要重新连接此会话，需输入：

    tmux attach-session -t my_session
    

这里列出一些常用的session管理的命令：

* tmux list-session
* tmux new-session <会话名>
* tmux attach-session -t <会话名>
* tmux rename-session -t <会话名>
* tmux choose-session -t <会话名>
* tmux kill-session -t <会话名>

### 在不同窗口下工作

很多情况下，你需要在一个会话中运行多个命令，执行多个任务。我们可以在一个会话的多个窗口里组织他们。

这里列出一些常用的窗口操作的命令：

* CTRL-b <窗口号>： 窗口号是从0开始编号的，该命令可以快速跳转到其他窗口
* CTRL-b f： 如果我们给窗口起了名字，我们可以使用该命令找到它们：
* CTRL-b w： 列出所有窗口
* CTRL-b n：按照顺序切换到下一个窗口
* CTRL-b p：按照顺序切换到上一个窗口
* CTRL-b d：从Tmux会话中脱离出来
* CTRL-b &：想要离开一个窗口，可以输入 exit 或者

### 把窗口分成许多面板

有时候你在vim中写代码的同时，需要查看日志文件或者编译测试。Tmux可以让我们把窗口分成许多面板。 这里列出一些常用的面板操作的命令：

* CRTL-b "：水平分割窗口，形成上下两个面板
* CRTL-b %：竖直分割窗口，形成左右两个面板
* CTRL-b <光标键>：在不同面板间移动

下面以一个例子来说明：

    tmux new -s test_panel  # 打开一个新的会话
    CRTL-b "       # 在会话中水平分隔窗口
    CRTL-b %       # 在会话中竖直分隔窗口
    

最终形成的效果如下所示：

![][4]

这里再列举下更多tmux命令：

![][5]

![][6]

![][7]

## 3. Reference

[终端复用软件之tmux简介][8]

[如何使用Tmux提高终端环境下的效率][9]

[使用ansible编译安装运维工具tmux][10]

[0]: /sites/fUvyiim
[1]: http://cpper.info/2016/05/27/tmux.html?utm_source=tuicool&utm_medium=referral
[2]: /topics/11200054
[3]: http://i.imgur.com/d0kzqmV.jpg
[4]: http://i.imgur.com/xX61bQ4.png
[5]: http://i.imgur.com/dafc83w.png
[6]: http://i.imgur.com/p3742Ci.png
[7]: http://i.imgur.com/w4cNQkk.png
[8]: http://www.ezlippi.com//blog/2016/01/tmux-guide.html
[9]: https://linux.cn/article-3952-1.html
[10]: http://www.cnblogs.com/tae44/p/4816414.html