## Fuzzy finder(fzf+vim) 使用全指南

来源：[https://keelii.com/2018/08/12/fuzzy-finder-full-guide/](https://keelii.com/2018/08/12/fuzzy-finder-full-guide/)

时间 2018-08-12 19:09:55

 
## 简介
 
[Fuzzy finder][7] 是一款使用 GO 语言编写的交互式的 Unix 命令行工具。可以用来查找任何 **`列表`**  内容，文件、Git 分支、进程等。所有的命令行工具可以生成列表输出的都可以再通过管道 pipe 到 fzf 上进行搜索和查找
 
## 优点
 
 
* GO 语言编写，编译完生成可执行文件没有任何依赖 
* 搜索/查找速度飞快 
* 功能全面/可视化界面体验很棒 
* 周边插件丰富 (vim, tmux, fuzzy auto-completion) 
 
 
## 安装
 
以 macOS 为例子，直接使用 homebrew 安装即可
 
```
brew install fzf
# 如果要使用内置的快捷键绑定和命令行自动完成功能的话可以按需安装
$(brew --prefix)/opt/fzf/install
```
 
## 使用
 
命令行下执行`fzf`即可展示当前目录下所有文件列表，可以用键盘上下键或者鼠标点出来选择 
![][0]
 
或许你会觉得这个查找提示看起来挺漂亮的，但是并没有什么卵用，因为查找出来就没有然后了。其实这也是 Fuzzy finder 最核心的地方，他只是一个通用的下拉查找功能，自己本身并不关心你用它来做什么，通常我们需要组合使用才会有很好的效果
 
### 用 vim 打开文件
 
比如我们用 vim 组合 fzf 来查找并打开目录下的文件：
 
```
vim $(fzf)
```
 
![][1]
 
### 切换当前工作目录
 
再比如进入到某个文件夹下面，使用 fzf 的过滤选择真是太方便了
 
```
cd $(find * -type d | fzf)
```
 
这是个组合 (cd+find+fzf) 命令，完成切换到任意子目录的功能。可以看出来当 fzf 和其它命令组合使用时就能使得一些操作更方便：
 
 
* 使用 find 命令找出所有的子目录 
* 把子目录列表 pipe 到 fzf 上进行选择 
* 再把结果以子命令的形式传给 cd 
 
 
![][2]
 
### 切换 git 分支
 
```
git checkout $(git branch -r | fzf)
```
 
![][3]
 
不过这样组合使用命令的实在太长了，如果你不使用自动补全的话巧起来很累的。建议把常用的 alias 放在 .zshrc 中管理嘛
 
### shell 命令行补全
 
fzf 默认使用`**`来补全 shell 命令，比起默认的 tab 补全，fzf 补全不知道高到哪里去了。cd, vim, kill, ssh, export… 统统都能补全，好用哭了
 
![][4]
 
## 配置
 
fzf 提供了两个 环境变量  配置参数，来分别设置默认的调用命令和 fzf 默认配置参数
 
### 核心命令 FZF_DEFAULT_COMMAND
 
对于使用 fzf 来查找文件的情况，fzf 其实底层是调用的 Unix 系统`find`命令，如果你觉得 find 不好用也可以使用其它查找文件的命令行工具「我使用 [fd][8] 」。注意：对原始命令添加一些参数应该在这个环境变量里面添加
 
比如说我们一般都会查找文件`-type f`，通常会忽略一些文件夹/目录`--exclude=...`，下面是我的变量值：
 
```
export FZF_DEFAULT_COMMAND="fd --exclude={.git,.idea,.vscode,.sass-cache,node_modules,build} --type f"
```
 
### 界面展示 FZF_DEFAULT_OPTS
 
界面展示这些参数在`fzf --help`中都有，按需配置即可
 
```
export FZF_DEFAULT_OPTS="--height 40% --layout=reverse --preview '(highlight -O ansi {} || cat {}) 2> /dev/null | head -500'"
```
 
界面配置参数加上后就漂亮多了 
![][5]
 `--preview`表示在右侧显示文件的预览界面，语法高亮的设置使用了 [highlight][9] 如果 highlight 失败则使用最常见的`cat`命令来查看文件内容
 
highlight 安装可能会有个小插曲。highlight 需要手动编译安装，默认安装目录在`/usr/bin`,`/usr/share`下面。然而在 macOS 中由于 `SIP` 保护，用户安装的程序不能在这几个目录下面「即使有 sudo 权限也不行」。我们可以手动更改下 highlight 源代码中 makefile 中的参数即可
 
```
# PREFIX = /usr
PREFIX = /usr/local
```
 
将`PREFIX = /usr`改成`PREFIX = /usr/local`，然后`make`，`sudo make install`就可以了
 
### 触发命令行补全 FZF_COMPLETION_TRIGGER
 
默认是`**`，一般不用修改
 
## VIM fzf 插件
 
如果你使用 vim，那么官方提供的插件会让你的 vim 使用更加流畅
 
### 安装插件
 
如果你本地安装过 fzf 命令行工具了，只需要在 .vimrc 里面添加下面两个插件配置即可
 
```
Plug '/usr/local/opt/fzf'
Plug 'junegunn/fzf.vim'
```
 
注意：使用了 [vim-plug][10] 插件管理
 
插件主要对 fzf 集成绑定了一些和 vim 相关的功能，比如：查找当前 Buffer、Tag、Marks。甚至切换 window 更换 vim 主题配色等
 
命令模式下敲`Files`即可选择当前目录下所有文件，`Buffers`可以过滤当前所有 vim buffer 内容
 
![][6]
 
再配置几个常用快捷键就可以直接取代 CtrlP 插件了
 
 
* Ctrl + p 查看文件列表 
* Ctrl + e 查看当前 Buffer，两次 Ctrl + e 快速切换上次打开的 Buffer 
 
 
```
nmap <C-p> :Files<CR>
nmap <C-e> :Buffers<CR>
let g:fzf_action = { 'ctrl-e': 'edit' }
```
 
## 结语
 
当然 fzf 还可以在很多其它场景下用来。如果你想使用可视化的列表选择而不是咣咣敲命令，那就自己搭配一些组合来使用吧
 


[7]: https://github.com/junegunn/fzf
[8]: https://github.com/sharkdp/fd
[9]: http://www.andre-simon.de/doku/highlight/en/highlight.php
[10]: https://github.com/junegunn/vim-plug
[0]: ../img/yQNf2eq.png
[1]: ../img/viquiuB.gif
[2]: ../img/I3Y3I3z.gif
[3]: ../img/vaIJ7zf.gif
[4]: ../img/FvMvQzq.gif
[5]: ../img/Mz2IJrR.png
[6]: ../img/jQNVRrA.gif