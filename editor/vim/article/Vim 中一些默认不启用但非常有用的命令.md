## [译] Vim 中一些默认不启用但非常有用的命令

来源：[https://mp.weixin.qq.com/s/17izCMWOnkPwRBj7nTw76g](https://mp.weixin.qq.com/s/17izCMWOnkPwRBj7nTw76g)

时间 2018-10-26 07:48:10

 
![][0]
 
作者 | Girish Managoli
 
译者 | 无明
 
了解这些命令，使用Vim更高效
 
vim 是一款功能强大的通用编辑器，它提供了一组丰富的命令，成为众多用户的编辑器首选。本文将介绍 vim 中默认未被启用但仍然十分有用的命令。虽然我们可以在每个 vim 会话中单独启用这些命令，但本文的目的是创建一个开箱即用的高效率开发环境，所以建议将这些命令配置在 vim 配置文件中。
 
在开始之前
 
这里讨论的命令或配置属于 vim 启动配置文件 vimrc，这个文件位于用户主目录中。可以按照以下说明在 vimrc 中设置命令：
 
（注意：在 Linux 上，vimrc 文件也被用于系统范围的配置，例如 /etc/vimrc 或 /etc/vim/vimrc。在本文中，我们只考虑存在于用户主目录中特定于用户的 vimrc。）
 
在 Linux 上：

 
* 使用 vi $HOME/.vimrc 打开文件；
  
* 将末尾给出的命令输入或复制 / 粘贴到文件中；
  
* 保存并关闭（:wq）。

 
在 Windows 上：

 
* 首先，安装 gvim；
  
* 打开 gvim；
  
* 单击 Edit --> Startup settings，打开 _vimrc 文件；
  
* 将末尾给出的命令输入或复制 / 粘贴到文件中；
  
* 单击 File --> Save。

 
现在让我们深入研究各个 vi 命令。这些命令可以分为以下几类：

 
* 缩进和 Tab；
  
* 显示和格式化；
  
* 搜索；
  
* 浏览和滚动；
  
* 拼写；
  
* 杂项。

 
缩进和 Tab
 
自动对齐文件中行的缩进：

```
set autoindent
```
 
智能缩进使用了代码语法和样式来对齐：

```
set smartindent
```
 
提示：vim 具有语言感知功能，并根据文件中所使用的编程语言提供了默认的设置，让工作更高效。有很多默认配置命令，包括 axs cindent、cinoptions、indentexpr 等，这里就不做进一步的介绍。syn 是一个有用的命令，用于显示或设置文件语法。
 
设置 Tab 的空格数量：

```
set tabstop=4
```
 
设置“移位操作”（例如“>>”或“<<”）的空格数量：

```
set shiftwidth=4
```
 
如果你更喜欢使用空格而不是制表符，那么在按下 Tab 键时将插入空格。对于依赖制表符而不是空格的语言（如 Python）这可能是个问题。对于这种情况，你可以根据文件类型来设置这个选项。

```
set expandtab
```
 
显示和格式化
 
要显示行号：

```
set number
```
 
![][1]
 
在文本超过最大宽度时换行：

```
set textwidth = 80
```
 
根据距离右边的列数来换行：

```
set wrapmargin = 2
```
 
在遍历文件时识别括弧的起始和结束位置：

```
set showmatch
```
 
![][2]
 
搜索   
 
在文件中高亮显示搜索关键词：

```
set hlsearch
```
 
![][3]
 
进行增量搜索：

```
set incsearch
```
 
![][4]
 
搜索时忽略大小写（很多用户选择不使用这个命令，不过可以在你认为有用时设置它）：

```
set ignorecase
```
 
在设置了 ignorecase 和 smartcase 并且搜索关键字包含大写字母时，搜索时不考虑 ignorecase：

```
set smartcase
```
 
例如，如果文件中包含：
 
test
 
Test
 
当设置了 ignorecase 和 smartcase，搜索“test”会找到并突出显示 test 和 Test。搜索“Test”只突出显示或只找到第二 Test。
 
浏览和滚动
 
为了获得更好的视觉体验，你可能更喜欢将光标放在中间的位置而不是第一行。设置下面的选项可以将光标位置设置为第 5 行。

```
set scrolloff = 5
```
 
例如：
 
第一张图像的 scrolloff = 0，第二张图像的 scrolloff = 5。
 
![][5]
 
提示：如果你设置了 nowrap，那么 set sidescrolloff 会非常有用。
 
在 vim 屏幕底部显示永久的状态栏，用于显示文件名、行号、列号等：

```
set laststatus = 2
```
 
![][6]
 
拼写   
 
vim 有一个内置的拼写检查器，在编辑文本和些代码时非常有用。vim 会识别文件类型并检查代码注释的拼写情况。可以使用以下命令打开英语拼写检查：

```
set spell spelllang = en_us
```
 
杂项   
 
禁用备份文件：如果启用了这个选项，vim 将为上一次编辑创建备份。如果你不想要这个功能，请像下面那样将其禁用。备份文件的文件名末尾有个波浪号（~）。

```
set nobackup
```
 
禁用交换文件：如果启用了这个选项，vim 会创建一个交换文件，直到你开始编辑文件。在发生崩溃或冲突时，交换文件用于恢复文件。交换文件是隐藏文件，以. 开头，并以.swp 结尾。

```
set noswapfile
```
 
假设你需要在同一个 vim 会话中编辑多个文件，并在它们之间切换。问题是，工作目录通常是你打开第一个文件的那个目录。所以，将工作目录自动切换到正在编辑的文件所在的目录是很有用的。可以启用这个选项：

```
set autochdir
```
 
vim 维护了一个撤消历史记录，允许你撤消更改。默认情况下，历史记录只在文件打开时处于活动状态。vim 通过了一个非常好用的特性，即使在文件关闭后也可以维护撤消历史记录，这意味着即使在保存、关闭和重新打开文件后，你仍然可以撤消更改。撤消文件是隐藏文件，扩展名为.un~。

```
set undofile
```
 
设置声音警报铃声（如果你试图滚动超过行尾，会发出警告）：

```
set errorbells
```
 
如果你愿意，还可以设置视觉警报：

```
set visualbell
```
 
一些额外的 tips
 
vim 提供了长格式和短格式命令，都可用于设置或取消设置。
 
autoindent 命令的长格式：

```
set autoindent
```
 
autoindent 命令的短格式：

```
set ai
```
 
查看命令的当前设置，并且不改变当前的设置值，请在命令末尾添加问号：

```
set autoindent？
```
 
要取消或关闭命令，在命令前面加上 no 前缀（对大部分命令适用）：

```
set noautoindent
```
 
可以只为一个文件设置命令，而不是全局。要做到这个，需要打开文件并输入冒号:，然后跟上 set 命令。这个配置只对当前文件编辑会话有效。
 
![][7]
 
查看命令帮助：

```
:help autoindent
```
 
![][8]
 
注意：本文列出的命令针对 Linux 上 7.4 版本（2013 年 8 月 10 日）的 Vim 和 Windows 上 8.0 版本（2016 年 9 月 12 日）的 Vim 进行了测试。
 
备忘单
 
在 vimrc 文件中复制 / 粘贴这些命令：

```
" Indentation & Tabs

set autoindent

set smartindent

set tabstop=4

set shiftwidth=4

set expandtab

set smarttab

" Display & format

set number

set textwidth=80

set wrapmargin=2

set showmatch

" Search

set hlsearch

set incsearch

set ignorecase

set smartcase

" Browse & Scroll

set scrolloff=5

set laststatus=2

" Spell

set spell spelllang=en_us

" Miscellaneous

set nobackup

set noswapfile

set autochdir

set undofile

set visualbell

set errorbells
```
 
英文原文：https://opensource.com/article/18/9/vi-editor-productivity-powerhouse
 
[0]: https://img0.tuicool.com/JB77Fjr.jpg
[1]: https://img1.tuicool.com/rIVbMvb.jpg
[2]: https://img1.tuicool.com/B3AJfay.jpg
[3]: https://img2.tuicool.com/mmUzeeu.jpg
[4]: https://img1.tuicool.com/nMZRBfr.jpg
[5]: https://img1.tuicool.com/rQZvueB.jpg
[6]: https://img2.tuicool.com/B7BbM3z.jpg
[7]: https://img2.tuicool.com/BVvMzqV.jpg
[8]: https://img1.tuicool.com/VNVNBru.jpg
