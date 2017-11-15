# 工欲善其事必先利其器 —— 配置vim

 时间 2017-11-14 10:43:04  

原文[http://yq.aliyun.com/articles/247401][1]


工欲善其事必先利其器

[toc]

要看项目源代码必须有一个优秀的代码编辑器，就我知道支持代码跳转的编辑器有source insight, sublime, geany, vim。

* source insight 不用配置，一般在windows系统用；
* sublime 有个monokai主题比较漂亮；
* geany 功能比较简单，只支持在已打开文件代码里跳转；
* 默认的vim 体验感比较差，配置后就很强大了，下图；

![][3]

![][4]

vim 的基本操作请自行百度，为师不教这个。下面是vim配置内容。

## 配置代码提示功能

最重要的是安装vim和python

    sudo apt-get update         #更新软件源
    sudo apt-get clang          #安装clang
    sudo apt-get cmake          #安装cmake
    sudo apt-get install vim        #安装vim
    sudo apt-get install python python-dev  #安装Python相关

接下来正式安装YCM

    sudo apt-get install vim-addon-manager  #这应该是一个vim的插件管理器
    sudo apt-get install vim-youcompleteme  #安装YCM
    vim-addons install youcompleteme    #将YCM加入addons管理器中

直接上我的配置，将这个复制到用户目录下，命名为 .vimrc 即可。

    syntax on
    set nocompatible
    set tags+=~/.vim/systags
    set nu
    set autoindent
    set shiftwidth=4
    set ignorecase
    set cindent
    set hls is
    set hlsearch
    set ts=4
    set history=100
    set syntax=c
    highlight Function cterm=bold,underline ctermbg=red ctermfg=green
    highlight TabLine term=underline cterm=bold ctermfg=9 ctermbg=4
    highlight TabLineSel term=bold cterm=bold ctermbg=Red ctermfg=yellow
    highlight Pmenu ctermbg=darkred
    highlight PmenuSel ctermbg=red ctermfg=yellow
    set ruler
    colorscheme desert
    let g:winManagerWindowLayout='FileExplorer|TagList|BufExplorer'
    let g:winManagerWidth=35
    "let Tlist_Auto_Open=1
    let Tlist_Ctags_Cmd = '/usr/bin/ctags'
    let Tlist_Show_One_File = 1
    let Tlist_Exit_OnlyWindow =  1
    let Tlist_Use_Left_Window = 1
    "F7 NERDTree 
    map <F7> :NERDTreeToggle<CR>
    imap <F7> <ESC>:NERDTreeToggle<CR>
    map <F8> :WMToggle<CR>
    imap <F8> <ESC>:WMToggle<CR>

这样就配置好代码提示功能，可自行体验一下。

## 配置代码主题

## 配置系统默认主题

首先：在终端输入

    # $ ls /usr/share/vim/vim73/colors
    
    wu_being@UbuntuKylin1704:~/Github/leveldb$ ls /usr/share/vim/vim80/colors/
    blue.vim      delek.vim    evening.vim   morning.vim  peachpuff.vim  shine.vim  zellner.vim
    darkblue.vim  desert.vim   industry.vim  murphy.vim   README.txt     slate.vim
    default.vim   elflord.vim  koehler.vim   pablo.vim    ron.vim        torte.vim
    wu_being@UbuntuKylin1704:~/Github/leveldb$ 
    wu_being@UbuntuKylin1704:~/Github/leveldb$

查看是否有上面提到的某些配色，所有配色均是以.vim结束的，如果有的话，再输入：

    $ cd ~/

到用户主目录，然后输入

    $ vim .vimrc

创建配置文件，将vim的内容设置如下：

    set nu 
    colorscheme desert 
    syntax on

即配置好desert.vim这种主题方案了，如果想使用其他主题方案，就把desert换成对应的名字就ok啦～～～

下面开始愉快的使用vim编程吧！！！

## 配置molokai主题

sublime text的配色主题比较绚丽多彩，今天浏览网页时发现一款vim的molokai配色，它是基于textmate的monokai主题，

和sublime text 的默认主题monokai很像，喜欢使用sublime text的童鞋可以试试。

molokai.vim代码如下：

    " Vim color file
    "
    " Author: Tomas Restrepo <tomas@winterdom.com>
    "
    " Note: Based on the monokai theme for textmate
    " by Wimer Hazenberg and its darker variant
    " by Hamish Stuart Macpherson
    "
    
    hi clear
    
    set background=dark
    set t_Co=256 "告知molokai，终端支持256色。
    if version > 580
        " no guarantees for version 5.8 and below, but this makes it stop
        " complaining
        hi clear
        if exists("syntax_on")
            syntax reset
        endif
    endif
    let g:colors_name="molokai"
    
    if exists("g:molokai_original")
        let s:molokai_original = g:molokai_original
    else
        let s:molokai_original = 0
    endif
    
    
    hi Boolean         guifg=#AE81FF
    hi Character       guifg=#E6DB74
    hi Number          guifg=#AE81FF
    hi String          guifg=#E6DB74
    hi Conditional     guifg=#F92672               gui=bold
    hi Constant        guifg=#AE81FF               gui=bold
    hi Cursor          guifg=#000000 guibg=#F8F8F0
    hi Debug           guifg=#BCA3A3               gui=bold
    hi Define          guifg=#66D9EF
    hi Delimiter       guifg=#8F8F8F
    hi DiffAdd                       guibg=#13354A
    hi DiffChange      guifg=#89807D guibg=#4C4745
    hi DiffDelete      guifg=#960050 guibg=#1E0010
    hi DiffText                      guibg=#4C4745 gui=italic,bold
    
    hi Directory       guifg=#A6E22E               gui=bold
    hi Error           guifg=#960050 guibg=#1E0010
    hi ErrorMsg        guifg=#F92672 guibg=#232526 gui=bold
    hi Exception       guifg=#A6E22E               gui=bold
    hi Float           guifg=#AE81FF
    hi FoldColumn      guifg=#465457 guibg=#000000
    hi Folded          guifg=#465457 guibg=#000000
    hi Function        guifg=#A6E22E
    hi Identifier      guifg=#FD971F
    hi Ignore          guifg=#808080 guibg=bg
    hi IncSearch       guifg=#C4BE89 guibg=#000000
    
    hi Keyword         guifg=#F92672               gui=bold
    hi Label           guifg=#E6DB74               gui=none
    hi Macro           guifg=#C4BE89               gui=italic
    hi SpecialKey      guifg=#66D9EF               gui=italic
    
    hi MatchParen      guifg=#000000 guibg=#FD971F gui=bold
    hi ModeMsg         guifg=#E6DB74
    hi MoreMsg         guifg=#E6DB74
    hi Operator        guifg=#F92672
    
    " complete menu
    hi Pmenu           guifg=#66D9EF guibg=#000000
    hi PmenuSel                      guibg=#808080
    hi PmenuSbar                     guibg=#080808
    hi PmenuThumb      guifg=#66D9EF
    
    hi PreCondit       guifg=#A6E22E               gui=bold
    hi PreProc         guifg=#A6E22E
    hi Question        guifg=#66D9EF
    hi Repeat          guifg=#F92672               gui=bold
    hi Search          guifg=#FFFFFF guibg=#455354
    " marks column
    hi SignColumn      guifg=#A6E22E guibg=#232526
    hi SpecialChar     guifg=#F92672               gui=bold
    hi SpecialComment  guifg=#465457               gui=bold
    hi Special         guifg=#66D9EF guibg=bg      gui=italic
    hi SpecialKey      guifg=#888A85               gui=italic
    if has("spell")
        hi SpellBad    guisp=#FF0000 gui=undercurl
        hi SpellCap    guisp=#7070F0 gui=undercurl
        hi SpellLocal  guisp=#70F0F0 gui=undercurl
        hi SpellRare   guisp=#FFFFFF gui=undercurl
    endif
    hi Statement       guifg=#F92672               gui=bold
    hi StatusLine      guifg=#455354 guibg=fg
    hi StatusLineNC    guifg=#808080 guibg=#080808
    hi StorageClass    guifg=#FD971F               gui=italic
    hi Structure       guifg=#66D9EF
    hi Tag             guifg=#F92672               gui=italic
    hi Title           guifg=#ef5939
    hi Todo            guifg=#FFFFFF guibg=bg      gui=bold
    
    hi Typedef         guifg=#66D9EF
    hi Type            guifg=#66D9EF               gui=none
    hi Underlined      guifg=#808080               gui=underline
    
    hi VertSplit       guifg=#808080 guibg=#080808 gui=bold
    hi VisualNOS                     guibg=#403D3D
    hi Visual                        guibg=#403D3D
    hi WarningMsg      guifg=#FFFFFF guibg=#333333 gui=bold
    hi WildMenu        guifg=#66D9EF guibg=#000000
    
    if s:molokai_original == 1
       hi Normal          guifg=#F8F8F2 guibg=#272822
       hi Comment         guifg=#75715E
       hi CursorLine                    guibg=#3E3D32
       hi CursorColumn                  guibg=#3E3D32
       hi LineNr          guifg=#BCBCBC guibg=#3B3A32
       hi NonText         guifg=#BCBCBC guibg=#3B3A32
    else
       hi Normal          guifg=#F8F8F2 guibg=#1B1D1E
       hi Comment         guifg=#465457
       hi CursorLine                    guibg=#293739
       hi CursorColumn                  guibg=#293739
       hi LineNr          guifg=#BCBCBC guibg=#232526
       hi NonText         guifg=#BCBCBC guibg=#232526
    end
    
    "
    " Support for 256-color terminal
    "
    if &t_Co > 255
       hi Boolean         ctermfg=135
       hi Character       ctermfg=144
       hi Number          ctermfg=135
       hi String          ctermfg=144
       hi Conditional     ctermfg=161               cterm=bold
       hi Constant        ctermfg=135               cterm=bold
       hi Cursor          ctermfg=16  ctermbg=253
       hi Debug           ctermfg=225               cterm=bold
       hi Define          ctermfg=81
       hi Delimiter       ctermfg=241
    
       hi DiffAdd                     ctermbg=24
       hi DiffChange      ctermfg=181 ctermbg=239
       hi DiffDelete      ctermfg=162 ctermbg=53
       hi DiffText                    ctermbg=102 cterm=bold
    
       hi Directory       ctermfg=118               cterm=bold
       hi Error           ctermfg=219 ctermbg=89
       hi ErrorMsg        ctermfg=199 ctermbg=16    cterm=bold
       hi Exception       ctermfg=118               cterm=bold
       hi Float           ctermfg=135
       hi FoldColumn      ctermfg=67  ctermbg=16
       hi Folded          ctermfg=67  ctermbg=16
       hi Function        ctermfg=118
       hi Identifier      ctermfg=208
       hi Ignore          ctermfg=244 ctermbg=232
       hi IncSearch       ctermfg=193 ctermbg=16
    
       hi Keyword         ctermfg=161               cterm=bold
       hi Label           ctermfg=229               cterm=none
       hi Macro           ctermfg=193
       hi SpecialKey      ctermfg=81
    
       hi MatchParen      ctermfg=16  ctermbg=208 cterm=bold
       hi ModeMsg         ctermfg=229
       hi MoreMsg         ctermfg=229
       hi Operator        ctermfg=161
    
       " complete menu
       hi Pmenu           ctermfg=81  ctermbg=16
       hi PmenuSel                    ctermbg=244
       hi PmenuSbar                   ctermbg=232
       hi PmenuThumb      ctermfg=81
    
       hi PreCondit       ctermfg=118               cterm=bold
       hi PreProc         ctermfg=118
       hi Question        ctermfg=81
       hi Repeat          ctermfg=161               cterm=bold
       hi Search          ctermfg=253 ctermbg=66
    
       " marks column
       hi SignColumn      ctermfg=118 ctermbg=235
       hi SpecialChar     ctermfg=161               cterm=bold
       hi SpecialComment  ctermfg=245               cterm=bold
       hi Special         ctermfg=81  ctermbg=232
       hi SpecialKey      ctermfg=245
    
       hi Statement       ctermfg=161               cterm=bold
       hi StatusLine      ctermfg=238 ctermbg=253
       hi StatusLineNC    ctermfg=244 ctermbg=232
       hi StorageClass    ctermfg=208
       hi Structure       ctermfg=81
       hi Tag             ctermfg=161
       hi Title           ctermfg=166
       hi Todo            ctermfg=231 ctermbg=232   cterm=bold
    
       hi Typedef         ctermfg=81
       hi Type            ctermfg=81                cterm=none
       hi Underlined      ctermfg=244               cterm=underline
    
       hi VertSplit       ctermfg=244 ctermbg=232   cterm=bold
       hi VisualNOS                   ctermbg=238
       hi Visual                      ctermbg=235
       hi WarningMsg      ctermfg=231 ctermbg=238   cterm=bold
       hi WildMenu        ctermfg=81  ctermbg=16
    
       hi Normal          ctermfg=252 ctermbg=233
       hi Comment         ctermfg=59
       hi CursorLine                  ctermbg=234   cterm=none
       hi CursorColumn                ctermbg=234
       hi LineNr          ctermfg=250 ctermbg=234
       hi NonText         ctermfg=250 ctermbg=234
    end

使用方法：

将molokai.vim文件放到~/.vim/colors/文件夹下即可。

在~/.vimrc 中配置 :colorscheme molokai 则默认使用此配色。

现在可以用vim打开任意代码享受这个主题吧！

## 配置代码间跳转--ctags

安装ctags

    sudo apt install ctags

在程序项目主目录（想实现代码间跳转的目录）输入 ctags -R ，会在当前生成一个tags文件。 

    wu_being@UbuntuKylin1704:~$ cd Github/leveldb/
    wu_being@UbuntuKylin1704:~/Github/leveldb$ ctags -R
    wu_being@UbuntuKylin1704:~/Github/leveldb$ ls -ltr
    总用量 1952
    ...
    -rw-rw-r-- 1 wu_being wu_being   1287 11月 14 12:57 test.cpp
    -rw-r--r-- 1 wu_being wu_being 291705 11月 14 13:19 tags
    wu_being@UbuntuKylin1704:~/Github/leveldb$ 
    wu_being@UbuntuKylin1704:~/Github/leveldb$ pwd
    /home/wu_being/Github/leveldb
    wu_being@UbuntuKylin1704:~/Github/leveldb$

在vimrc文件末行添加：

    set tags+=/home/wu_being/Github/leveldb/tags

注意：必须使用“+=”，并且两边不能有空格。

使用方法：

* `Ctrl + ]`
* `Ctrl + o`

在Linux环境下任意目录下的程序文件里的函数，要实现跳转到相关定义代码进行查看，只需要将vim光标移动到函数名或宏定义名称上，使用快捷键“ `Ctrl+]` ”，即可跳转定义中的函数或宏定义的地方进行查看，有多个要跳转的路径时会在vim下边出现几行选项，直接输入数字加回车可以进行对应的函数或宏定义选择； 

要想返回上一级函数或宏定义，只需要使用快捷键“ `Ctrl+o` ”，即可跳会上次的查看的函数。 

## vim多窗口使用技巧

1、打开多个窗口

    打开多个窗口的命令以下几个：
    横向切割窗口
    :new+窗口名(保存后就是文件名) 
    :split+窗口名，也可以简写为:sp+窗口名
    纵向切割窗口名
    :vsplit+窗口名，也可以简写为：vsp+窗口名

2、关闭多窗口

    可以用：q!，也可以使用：close，最后一个窗口不能使用close关闭。使用close只是暂时关闭窗口，其内容还在缓存中，只有使用q!、w!或x才能真能退出。
    :tabc 关闭当前窗口
    :tabo 关闭所有窗口

3、窗口切换

    :ctrl+w+j/k，通过j/k可以上下切换，或者:ctrl+w加上下左右键，还可以通过快速双击ctrl+w依次切换窗口。

4、窗口大小调整

    纵向调整
    :ctrl+w + 纵向扩大（行数增加）
    :ctrl+w - 纵向缩小 （行数减少）
    :res(ize) num  例如：:res 5，显示行数调整为5行
    :res(ize)+num 把当前窗口高度增加num行
    :res(ize)-num 把当前窗口高度减少num行
    横向调整
    :vertical res(ize) num 指定当前窗口为num列
    :vertical res(ize)+num 把当前窗口增加num列
    :vertical res(ize)-num 把当前窗口减少num列

5、给窗口重命名

    :f file

6、vi打开多文件

    vi a b c
    :n 跳至下一个文件，也可以直接指定要跳的文件，如:n c，可以直接跳到c文件
    :e# 回到刚才编辑的文件

7、文件浏览

    :Ex 开启目录浏览器，可以浏览当前目录下的所有文件，并可以选择
    :Sex 水平分割当前窗口，并在一个窗口中开启目录浏览器
    :ls 显示当前buffer情况

8、vi与shell切换

    :shell 可以在不关闭vi的情况下切换到shell命令行
    :exit 从shell回到vi

## 设置代码折叠

## 1. 折叠方式

可用选项来设定折叠方式：

可在Vim 配置文件中设置 set fdm=XXX

可直接在文件中使用注释调用vim命令 / _vim: set fdm=XXX:_ / 

有6种方法来选定折叠：

    manual          手工定义折叠         
    indent           更多的缩进表示更高级别的折叠         
    expr              用表达式来定义折叠         
    syntax           用语法高亮来定义折叠         
    diff                对没有更改的文本进行折叠         
    marker           对文中的标志折叠

注意，每一种折叠方式不兼容，如不能既用expr又用marker方式，我主要轮流使用indent和marker方式进行折叠。

使用时，用 set fdm=marker 命令来设置成marker折叠方式（fdm是foldmethod的缩写）。

要使每次打开vim时折叠都生效，则在.vimrc文件中添加设置，如添加：set fdm=syntax，就像添加其它的初始化设置一样。

## 2. 折叠命令

选取了折叠方式后，我们就可以对某些代码实施我们需要的折叠了，由于我使用indent和marker稍微多一些，故以它们的使用为例：如果使用了indent方式，vim会自动的对大括号的中间部分进行折叠，我们可以直接使用这些现成的折叠成果。

在可折叠处（大括号中间）：

    zc      折叠
    zC     对所在范围内所有嵌套的折叠点进行折叠
    zo      展开折叠
    zO     对所在范围内所有嵌套的折叠点展开
    [z       到当前打开的折叠的开始处。
    ]z       到当前打开的折叠的末尾处。
    zj       向下移动。到达下一个折叠的开始处。关闭的折叠也被计入。
    zk      向上移动到前一折叠的结束处。关闭的折叠也被计入。

当使用marker方式时，需要用标计来标识代码的折叠，系统默认是{{{和}}}，最好不要改动

我们可以使用下面的命令来创建和删除折叠：

    zf      创建折叠，比如在marker方式下：                  
             zf56G，创建从当前行起到56行的代码折叠；                  
             10zf或10zf+或zf10↓，创建从当前行起到后10行的代码折叠。                  
             10zf-或zf10↑，创建从当前行起到之前10行的代码折叠。                  
             在括号处zf%，创建从当前行起到对应的匹配的括号上去（（），{}，[]，<>等）。
    
    zd      删除 (delete) 在光标下的折叠。
             仅当 'foldmethod' 设为 "manual" 或 "marker" 时有效。
    
    zD     循环删除 (Delete) 光标下的折叠，即嵌套删除折叠。
             仅当 'foldmethod' 设为 "manual" 或 "marker" 时有效。
    
    zE      除去 (Eliminate) 窗口里“所有”的折叠。
             仅当 'foldmethod' 设为 "manual" 或 "marker" 时有效。

Wu_Being博客声明：本人博客欢迎转载，请标明博客原文和原链接！谢谢！ 

《工欲善其事必先利其器 —— 配置vim》： [https://yq.aliyun.com/articles/247401/][5]

[1]: http://yq.aliyun.com/articles/247401
[3]: https://img1.tuicool.com/jmYr2me.png
[4]: https://img1.tuicool.com/fYvauaZ.png
[5]: https://yq.aliyun.com/articles/247401/