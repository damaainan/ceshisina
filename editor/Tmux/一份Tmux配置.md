# 又双叒叕一份Tmux配置：专为远程设计的按键方案

关注 2017.06.11 14:44  字数 941  

Tmux是一个终端复用器，网上配置要多少有多少，但是秉承“自己的才是最好的”原则，我决定照葫芦画瓢写一份自己的Tmux配置。

然后发现，网上大部分（99%）Tmux配置都没有把远程的情况考虑进去，例如VNC远程快捷键、ssh远程快捷键的传送等问题都没考虑进去，于是几乎重新设计了所有常用的快捷键。然后又使用了一段时间，不断改进，今天决定把它分享出来。

![][1]



Tmux

特点：

* 为远程操作设计的快捷键，完美实现在Tmux里跑Tmux的场景。
* 自动连接上一次退出的会话，无缝衔接上一次操作（防止意外退出而中断操作）。
* 快捷键合理分配，所有快捷键操作都不需要移动手腕（笔记本电脑而言，移动手腕算我输）。
* 插件安装/更新、自定义外观、自定义设置均可一键操作（设置了快捷键直接编辑）。
* 一条命令即可安装。

项目地址：[https://github.com/izuolan/.tmux][2]

# Prefix

#### 最佳：Alt-z

Prefix，这是整个Tmux能否高效使用的关键，因此Prefix两个按键必须键程合适，不需要移动太远的范围。

Tmux默认是`Ctrl-b`，大部分Tmux配置选择改为`Ctrl-a`，或者`Ctrl-z`，老实说`Ctrl-z`这个方案最容易让人接受，键程最短，不容易误按，然而大部分Terminal都把`Ctrl-z`设为suspend快捷键，这可就尴尬了，特别是远程时用vim打着代码，突然按两下Prefix就把vim挂起了，非常影响使用。

于是尝试不少组合之后，决定把` Alt-z` 设为 Prefix，超短键程，没有常用的快捷键冲突（大概）。考虑到键盘布局差异，这两个键一般都不会相隔太远。

有了Prefix，接下来就是围绕z键设置快捷键。接下来不废话，直接介绍快捷键设置，以及在解释为何在远程中这样设置最科学。

## 会话

#### 创建会话（new session，所以使用快捷键n）：

    # 创建, tmux new -s <name-of-my-session> 创建一个新的会话
    $ tmux new -s basic
    # 在tmux中创建一个会话
    [PREFIX-n] new -s <name-of-my-session>

![][3]



新建会话

#### 离开会话（后台运行，默认d）

    # 分离会话 detach
    [PREFIX-d]
    [detached (from session basic)]
    # or
    $ tmux detach

#### 查看会话列表，切换会话

    # 查看已有会话列表(list-session)
    $ tmux ls
    basic: 1 windows (created Wed Aug  5 14:54:04 2015) [200x49]
    
    # 在tmux中查看会话列表并切换
    [PREFIX-s]

#### 重新进入会话

    # 连接会话(只有一个)
    $ tmux attach
    $ tmux attach -t basic
    $ tmux a -t basic

#### 关闭会话

    # 退出会话
    $ tmux kill-session -t <Num>
    # or
    [PREFIX-Alt-q]
    # 杀掉全部会话
    $ tmux kill-server

#### 重命名会话

    # 重命名会话
    [PREFIX-$]

会话我基本没有改动默认配置，因为我一般不会遇到需要操作多个会话的情况。

## 窗口

#### 创建窗口（create window，所以设置为c）

    # 创建一个新的窗口
    [PREFIX-c]

#### 重命名窗口（这个保留默认,）

    # 重命名一个窗口
    [PREFIX-,] 之后输入名字回车

#### 切换窗口

    # 切换窗口
    [PREFIX-[]
    [PREFIX-]]
    # 设置这两个键是因为不需要移动手腕，一般就在回车键上面，又刚好成对。vi中常用的h、l后面会用到，所以这里不用。
    # 切换到对应窗口
    [PREFIX-1/2/3]
    # 切换到上一个窗口
    [PREFIX-Tab]
    # 可视化选择切换到的窗口
    [PREFIX-w]

#### 退出窗口

    # 退出窗口
    exit 
    # 与窗口列表快捷键类似，`Alt-w`就是关闭，直接`w`就是窗口列表，简单好记。
    [PREFIX-Alt-w] 会有确认

## 面板

#### 分割面板（键盘上唯有这两个键最直观表达分屏效果，所以就是`和-` 啦）

    # 垂直/水平分割窗口
    [PREFIX--] / [PREFIX-\]

#### 关闭面板

    # 关闭一个面板, 要确认
    [PREFIX-x]
    # 或者
    exit [面板里执行]

#### 切换面板

    [PREFIX-hjkl]   pane之间移动
    [PREFIX-arrow]  pane之间移动
    
    [PREFIX-Space]  最近使用两个窗口之间切换
    [PREFIX-q]    展示窗口数字并选择跳转

> 为了统一远程与本地的快捷键，即便是Pane操作我也设置了需要Prefix才能触发，不喜欢的话可以改为直接触发，但这样本地与远程快捷键不统一，反而有些麻烦。

#### 移动面板

    [PREFIX-<] 当前pane移到左边
    [PREFIX->] 当前pane移到右边

#### 调整面板

    [PREFIX-HJKL]      pane大小调整
    [PREFIX-Alt-arrow] pane大小调整
    # 此处的Pane调整算是我最不满意的一个地方，因为Alt-h刚好是man命令的快捷键，避免冲突只能放弃Alt-hjkl的方式，改为PREFIX-HJKL，这里手指要多移动一次到Shift键上面真是让人不爽。因此补充一组快捷键，使用方向键调整。
    [PREFIX-z]    暂时把窗口变大

#### 其他

    [PREFIX-!]     当前面板在新的窗口中打开
    [PREFIX-space] 会自动切换依次使用这些布局(几种窗口布局轮流切换)

## 复制粘贴

    [PREFIX-[]      进入复制模式
    [PREFIX-Enter]  进入复制模式
    
    => 可以进行的操作
    space/v    开始选择
    Ctrl-v     整块选择
    hjkl       方向键移动
    w/b        向前向后移动一个单词
    fx/Fx      行内移动到下一个字符位置
    ctrl-b/f   在缓冲区里面翻页
    g/G        到缓冲区最顶/底端
    / ?        向下, 向上查找
    n/N        查找后下一个, 上一个
    Enter/y    复制
    [PREFIX-]] 粘贴
    
    # 其他增强:
    
    # 复制整个pane可见区域
    [PREFIX-:] capture-pane
    
    # 查看缓冲区内容
    [PREFIX-:] show-buffer
    
    # 列出缓冲区列表
    [PREFIX-:] list-buffers
    
    # 从缓冲区列表选择并插入到当期面板
    [PREFIX-:] choose-buffer => 回车

## 其他

#### 显示全部快捷键

    [PREFIX-?]  查看所有快捷键
    [PREFIX-e]  编辑Tmux配置
    [PREFIX-E]  编辑主题
    [PREFIX-`]  同步Pane操作
    [PREFIX-~]  取消同步操作

#### 命令模式

    [PREFIX-:]
    
    # 一些命令模式下的命令
    # 新建窗口
    new-window -n console
    
    # 新建并执行命令
    new-window -n processes "top"

## 增强

#### 1. Tmuxinator

Tmuxinator 是一个 Ruby 的 gem 包，可用于创建 Tmux 的会话。它的工作方式是先在配置文件中定义会话中的细节，然后用 1 条命令创建出这些会话

    gem install tmuxinator
    tmuxinator new project_a => ~/.tmuxinator/project_a.yml => 配置
    
    启动: tmuxinator start project_a
    可以别名: mux start project_a

#### 2. vim 插件

christoomey/vim-tmux-navigator, 安装更便捷的导航跳转

- - -

# 参考链接：

[Tmux][4]

[gpakosz/.tmux][5]

[jbnicolai/tmux][6]

[tmux-plugins][7]

[Arch Wiki][8]

[tmuxifier][9]


[1]: //upload-images.jianshu.io/upload_images/137499-c741ac437f4f4e23.png?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240
[2]: https://github.com/izuolan/.tmux
[3]: //upload-images.jianshu.io/upload_images/137499-640df64b7fe9989b.gif?imageMogr2/auto-orient/strip
[4]: https://github.com/tmux/tmux
[5]: https://github.com/gpakosz/.tmux/blob/master/.tmux.conf
[6]: https://github.com/jbnicolai/tmux
[7]: https://github.com/tmux-plugins
[8]: https://wiki.archlinux.org/index.php/Tmux_(%E7%AE%80%E4%BD%93%E4%B8%AD%E6%96%87)#.E6.89.93.E5.BC.80URL
[9]: https://github.com/jimeh/tmuxifier