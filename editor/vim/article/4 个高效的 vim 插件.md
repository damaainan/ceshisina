# 干货：4 个能提高 80% 效率的 vim 插件，你竟然还没有安装？（附解决问题的方法）

 时间 2017-07-13 11:25:16  

原文[http://www.jianshu.com/p/7ae2c42b203b][1]


![][3]

myvim

## 干货：4 个能提升 80% 效率的 vim 插件，你竟然还没有安装？（附解决问题的方法）

版权声明：本文为 cdeveloper 原创文章，可以随意转载，但必须在明确位置注明出处！

## 读这篇博客你能学到些什么知识和方法？

这篇博客主要介绍 vim 常用插件的安装配置方法，你可以 **学到下面这些内容** ： 

1. 掌握 vim 安装插件的方法，即使系统不同，也能有把握安装成功
1. 掌握常用 vim 插件的配置和使用方法
1. 通过本篇博客学会类比，掌握解决问题的有效方法

我最想教你的是如何去思考，遇到问题如何使用「 **英文搜索 + Google + stackoverflow + GitHub + 官网** 」自己去解决的能力。 我希望当你看完我的文章，以后再遇到问题的时候，能够首先尝试自己解决，以此形成一套自己的方法，这才是最重要的 ！ 

当然，这篇文章介绍的 vim 插件安装配置方法也很有用，也要学会，下面正式开始，别有压力，我的方法很简单。

## 为何需要安装 vim 插件？

一句话： **既能提高你的编程效率，也能提高你的逼格** ！心动了吗，心动不如行动哦，看完之后一定要去实践。 

## 简单的安装方法 Vundle

我使用的非常简单的 Vundle 来管理 vim 的插件，首先我们先来安装 Vundle。在 Linux 下，开源软件是主流，所以我们先到 GitHub 上看看有没有 Vundle 这个东西，经过搜索还真找到了（如果没有找到，我会去 Google 上搜索 How to install Vundle to Linux ）： 

    Vundle 地址：https://github.com/VundleVim/Vundle.vim

我在它的 [主页][4] 上看到了官方的英文安装文档，因为我平常经常阅读英文文档，所以很容易就安装完成了，你也可以先尝试自己安装，下面是我根据官方文档总结的安装方法（其实就是翻译一下）： 

1.如果你没有安装 git，先安装它：

    sudo apt-get install git

2.使用 git 安装 Vundle 到 ~/.vim/bundle/Vundle.vim 目录下： 

    git clone https://github.com/VundleVim/Vundle.vim.git ~/.vim/bundle/Vundle.vim

3.添加 [官方文档][4] 提供的配置信息到 ~/.vimrc 中： 

    set nocompatible              " be iMproved, required
    filetype off                  " required
    
    " set the runtime path to include Vundle and initialize
    set rtp+=~/.vim/bundle/Vundle.vim
    call vundle#begin()
    " alternatively, pass a path where Vundle should install plugins
    "call vundle#begin('~/some/path/here')
    
    " let Vundle manage Vundle, required
    Plugin 'VundleVim/Vundle.vim'
    ...

配置不止这些，后面还有很多，这里就不列出来了， 你只需要将官方文档提供的配置信息复制到你的 ~/.vimrc中即可 ，原理不要求了解。 

4.打开 vim，第一次安装默认插件：

    # 只在终端键入 vim，后面什么都不加
    vim
    # 然后键入下面的命令
    :PluginInstall
    
    # 之后等待安装完成，[ :q ] 来退出即可

只需要这简单的 4 步即可，通过安装 Vundle， 我想让你知道一个安装软件的思路：就是去看软件的官方安装文档 。因为，所有博客的安装方法几乎都是参考官网的，我的也不例外，但是 **官方文档一般都是英文的** ，这就是为什么你要 **学好英语** 的原因。 

## 需要安装的 4 个常用插件

下面这 4 个插件是我平常比较常用的，这里推荐给大家。

### 1. tagbar

这个插件可以 **浏览当前文件的标签** ，地址在 GitHub 上： [tagbar][5] ，效果如下： 

![][6]

tagbar.png

### 2. nerdtree

这个插件可以 **浏览当前文件所在的目录** ，地址在 GitHub 上： [nerdtree][7] ，效果如下： 

![][8]

nerdtree.png

### 3. vim-airline

这个插件可以 使得你的 vim 状态栏更高逼格，同时也提供一些优秀的显示功能 ，地址在 GitHub 上： [vim-airline][9] ，效果如下（官方的图）： 

![][10]

airline

### 4. minibufexpl

这个插件可以 **允许多个代码窗口切换或分屏使用** ，地址在 GitHub 上： [minibufexpl][11] ，效果如下（官方的图）： 

![][12]

minibuf

下面介绍安装和配置方法。

## 安装和配置方法

安装：因为我们使用 Vundle 来管理插件，这种方法安装插件比较简单，只需要在 ~/.vimrc 文件中加上配置信息，然后打开 vim，键入 :PluginInstall 来等待安装完成即可。 

配置： **所有的插件配置信息都可以自定义** ， **我是在每个插件的官方文档上学到如何配置插件的** ，我建议你也使用这种方法，可以提高你的学习能力和阅读英文文档的能力，例如 [tagbar 的官方文档][13] ，不要怕看不懂，你不尝试看，永远都看不懂。 

注意： 我建议你安装一个，配置一个，然后立刻学会使用这个插件，不要一下全部安装，结果不会使用搞的一团糟 ... 

下面是具体的安装配置过程。

### 1.安装 tagbar 插件

1.该插件需要先安装 ctags

    sudo apt-get install ctags

2.添加插件和其配置信息到 ~/.vimrc 中 

    # ~/.vimrc
    ...
    # 添加 tagbar 插件
    Plugin 'majutsushi/tagbar'
    
    # 配置 tagbar 插件
    let g:tagbar_ctags_bin='ctags'     "ctags 程序的路径
    let g:tagbar_width=30              "窗口宽度设置为 30
    let g:tagbar_left=1                "设置在 vim 左边显示
    let g:tagbar_map_openfold = "zv"   "按 zv 组合键打开标签，默认 zc 关闭标签
    
    "如果是 C 语言的程序的话，tagbar 自动开启
    autocmd BufReadPost *.cpp,*.c,*.h,*.hpp,*.cc,*.cxx call tagbar#autoopen() 
    
    "我设置 F2 为打开或者关闭的快捷键，根据你的习惯更改
    nnoremap <silent> <F2> :TagbarToggle<CR>
    ...

根据我的配置，tagbar 基本使用方法如下：

1. 「上下方向健」移动光标
1. 「zc」 关闭标签，「zv」 打开标签
1. 按「空格」在状态栏显示当前标签的声明
1. 按 「p」定位到该标签的代码处，但不移动焦点
1. 「回车」移动焦点到当前标签所在的代码处

其他用法，参考 [tagbar 官方文档][13] 。 

### 2.安装 nerdtree 插件

这个插件安装比较简单，直接添加并配置即可， 这些配置信息都是我从 [nerdtree 的官方文档][7]上根据自己的需求复制的 ，你可能说有些配置好复杂啊，我不懂原理怎么办？其实我也不懂，我也不需要懂，我的目的是使用插件，而不是开发插件，要搞清楚初衷哦。 

    # ~/.vimrc
    ...
    # 添加 nerdtree 插件
    Plugin 'scrooloose/nerdtree'
    
    # 配置 nerdtree 插件，
    let NERDTreeWinPos='right'             "设置在 vim 右侧显示
    let NERDTreeWinSize=30                 "设置宽度为 30            
    let g:NERDTreeDirArrowExpandable = '▸'
    let g:NERDTreeDirArrowCollapsible = '▾'
    
    autocmd vimenter * NERDTree
    
    wincmd w
    
    autocmd VimEnter * wincmd w
    
    autocmd StdinReadPre * let s:std_in=1
    
    autocmd VimEnter * if argc() == 1 && isdirectory(argv()[0]) && !exists("s:std_in") | exe 'NERDTree' argv()[0] | wincmd p | ene | endif
    
    autocmd StdinReadPre * let s:std_in=1
    
    autocmd VimEnter * if argc() == 0 && !exists("s:std_in") | NERDTree | endif
    
    autocmd bufenter * if (winnr("$") == 1 && exists("b:NERDTree") && b:NERDTree.isTabTree()) | q | endif
    
    " 我设置 F3 为打开或者关闭的快捷键，你可以自定义
    map <F3> :NERDTreeToggle<CR>
    ...

根据我的配置，nerdtree 基本使用方法如下：

1. 「上下方向键」移动光标
1. 「回车」打开新的文件或目录

详细用法，参考 [nerdtree 官方文档][14] 。 

### 3.安装 vim-airline 插件

这个插件的安装非常简单，也不需要很多的配置。

    # 安装 vim-airline
    Plugin 'bling/vim-airline'
    
    # 配置
    set laststatus=2

这个插件主要起指示作用，基本不需要主动去操作，详细的介绍参考 [vim-airline 官方文档][15]

### 4.安装 minibufexpl 插件

安装完这个插件，我们可以使用 Crtl + 方向键 来在各个窗口之间相互切换，非常的方便。 

    # 安装插件
    Plugin 'fholgado/minibufexpl.vim'
    
    # 配置插件信息，官方文档提供配置信息
    let g:miniBufExplMapWindowNavVim = 1   
    let g:miniBufExplMapWindowNavArrows = 1   
    let g:miniBufExplMapCTabSwitchBufs = 1   
    let g:miniBufExplModSelTarget = 1  
    let g:miniBufExplMoreThanOne=0
    
    # 注意：这里设置使用 Ctrl + 上下左右来切换窗口，请查看官方文档来自定义
    noremap <C-Down>  <C-W>j
    noremap <C-Up>    <C-W>k
    noremap <C-Left>  <C-W>h
    noremap <C-Right> <C-W>l
    
    map <T> :MBEbp<CR>
    map <R> :MBEbn<CR>

根据我的配置，基本使用方法如下：

1. 「上下左右方向键」来切换窗口
1. 切换到 minibufexpl 顶部状态栏，按「左右方向键」来选择窗口，「回车」打开并覆盖当前窗口，「s」分割一个新的窗口

详细用法，参考 [minibufexpl 官方文档][16] 。 

## 安装遇到问题怎么办？

因为大家的机器配置不同，可能在我的电脑上安装没有问题，但是到别的电脑上就有问题了。 如果你遇到问题，请自己尝试用 「英文搜索 + Google + stackoverflow + GitHub + 官方文档」的方式自己解决 ，一方面是因为一般 90% 的问题都有很好的解决方案，因为你遇到的问题别人之前就遇到过了，并且已经有了正确的答案，另一方面是因为别人也没有义务来帮助你啊，别人也有事情要忙的，除非你给 Ta 些费用。 

我平常遇到问题，除非上面这种方法解决不了，否则我基本不会问别人（如果这种方法都解决不了，你问的人很有可能也不会的~）。所以，当你习惯了这种解决问题的方法，请不要太感谢我，如果你现在还不习惯，那么可能你的阅读英文文档的能力还要加强，没关系，从现在开始培养吧。

## 完整的 .vimrc 配置文件

这是我的 ~/.vimrc 文件的内容，你可以 [下载][17] 来参考。 

## 总结

这篇文章只介绍了 4 个常用的插件，还有 2 个常用插件我没有写出来，留作后面介绍，主要是防止内容过多，另外也因为那 2 个插件安装稍微有点麻烦，这两个插件分别是：

1. 号称 vim 史上最难安装的代码自动补全插件： YouCompleteMe ，其实掌握了方法也不难。
1. vim 的 MarkDown 插件，我就是用这个 vim 插件来写博客的，逼格很高！

另外，读完之后，如果你还没有实践的话，我并不希望你记住具体的步骤， 我希望你记住的是安装这些插件的思路：GitHub + 官方文档 。作为类比，还是那句话， 我希望你在以后遇到问题时能借助 「英文搜索 + Google + stackoverflow + GitHub + 官方文档」的方式先尝试自己解决，我最希望的是你在看我文章的过程中总结一套适合自己的学习和解决问题的方法 ，因为实际的工作就是以解决问题为驱动的，希望你能重视这一点。 

最后，感谢你在百忙之中的阅读，我们下次再见 :)


[1]: http://www.jianshu.com/p/7ae2c42b203b

[3]: ../img/vayEjui.png
[4]: https://github.com/VundleVim/Vundle.vim
[5]: https://github.com/majutsushi/tagbar
[6]: ../img/jIvABr.png
[7]: https://github.com/scrooloose/nerdtree
[8]: ../img/aUFFj2B.png
[9]: https://github.com/vim-airline/vim-airline
[10]: ../img/yya2u2M.gif
[11]: https://github.com/fholgado/minibufexpl.vim
[12]: ../img/NNv2a2j.gif
[13]: https://github.com/majutsushi/tagbar/blob/master/doc/tagbar.txt
[14]: https://github.com/scrooloose/nerdtree/blob/master/doc/NERDTree.txt
[15]: https://github.com/vim-airline/vim-airline/blob/master/doc/airline.txt
[16]: https://github.com/fholgado/minibufexpl.vim/blob/master/doc/minibufexpl.txt
[17]: https://github.com/cheng-zhi/cheng-zhi.github.io/blob/master/file/myvimrc