# vim进阶 | 使用插件打造实用vim工作环境

卡巴拉的树 关注 2017.02.16 16:30  字数 2869  

首先晒一张我的**vim**截图，基本**IDE**有的功能都能实现了，虽然在日常工作里还是用商用软件**SourceInsight**，但是作为一个开发者，少不了折腾的心。

![myvim][1]

vim，作为与emacs齐名的编辑器，无需更多溢美之词，由于学习曲线陡峭，但是学会之人，无不表示其方便，vim操作的简洁，熟练使用后，形成的肌肉习惯让写代码成为享受。在学会基本的vim使用之后，每个人都会走向使用插件的道路，或者使用业界流行的插件，或者自己造轮子，这么多的插件在过去管理非常混乱，幸运的是我们有了插件管理器`Vundle`，下面正式从`Vundle`带你打造实用的vim工作环境。

## Vundle

**在正式引入Vundle之前，让我们做一些准备工作**  
由于我们的许多插件要从github下载，所以确保本机安装了git, 具体可以自行Google。  
其次确保本机上的vim版本>7.4, 可以运行`vim --version`查看当前机器上的vim版本，我的就显示：

    VIM - Vi IMproved 7.4 (2013 Aug 10, compiled Dec  6 2016 12:07:41)

如果没有安装vim，或者版本低于7.4都可以运行下面的命令安装或更新：  
_MacOS_

```
    brew update
    brew install vim
```
_Linux_

```
    apt-get install vim   # ubuntu
    yum install vim       # centos
```
vim问题解决后，我们就进入主题，介绍下Vundle， Vundle是vim的一款插件管理器，Vundle可以让你在配置文件中管理插件，并且非常方便的查找、安装、更新或者删除插件。 还可以帮你自动配置插件的执行路径和生成帮助文件。这里还介绍另外一个插件管理器，提个名字，`pathogen`,有兴趣可以自行研究，但是相比于`Vundle`，还是弱一线的，所以我们只介绍最好的。

运行下面命令安装Vundle:

    git clone https://github.com/gmarik/Vundle.vim.git ~/.vim/bundle/Vundle.vim

然后在我们的`.vimrc` 中添加设置，一般`.vimrc`在我们的用户主目录下， `cd ~`进入当前用户主目录，.vimrc是vim的设置文件，我们后面会添加很多设置在里面，如果没改过设置，可能一开始不存在，总之我们使用`vim .vimrc`创建或者打开该文件，并添加以下：

```
    set nocompatible              " required
    filetype off                  " required
    dd
    set rtp+=~/.vim/bundle/Vundle.vim
    call vundle#begin()
    Plugin 'gmarik/Vundle.vim'
    call vundle#end()            " required
    filetype plugin indent on    " required
```
然后在 vim中运行`:PluginInstall`即可（或者在 Bash 中运行vim +PluginInstall +qall）。以后只需要在添加一行Plugin 'xxx'并运行:PluginInstall即可自动安装插件。

## NERDTree

在我上面的图的右侧，显示出类似于IDE中的目录树，有了目录树可以更清晰地查看项目的结构，这里就使用了一个叫做NERDTree的插件。

**安装**  
由于上面我们介绍了Vundle,那么NERDTree的安装也水到渠成：

```
    set rtp+=~/.vim/bundle/Vundle.vim
    call vundle#begin()
    Plugin 'gmarik/Vundle.vim'
    Plugin 'scrooloose/nerdtree'
    call vundle#end()            " required
    filetype plugin indent on    " required
```
我们增加了scrooloose/nerdtree,只需要github repo的作者名和项目名就可以了，执行PluginInstal,插件就可以安装完成。  
我们在.vimrc中再添加一下设置：

```
    " NERDTree config
    " open a NERDTree automatically when vim starts up
    autocmd vimenter * NERDTree
    "open a NERDTree automatically when vim starts up if no files were specified
    autocmd StdinReadPre * let s:std_in=1
    autocmd VimEnter * if argc() == 0 && !exists("s:std_in") | NERDTree | endif
    "open NERDTree automatically when vim starts up on opening a directory
    autocmd StdinReadPre * let s:std_in=1
    autocmd VimEnter * if argc() == 1 && isdirectory(argv()[0]) && !exists("s:std_in") | exe 'NERDTree' argv()[0] | wincmd p | ene | endif
    "map F2 to open NERDTree
    map <F2> :NERDTreeToggle<CR>
    "close vim if the only window left open is a NERDTree
    autocmd bufenter * if (winnr("$") == 1 && exists("b:NERDTree") && b:NERDTree.isTabTree()) | q | endif
```
上面我们设置了自动打开NERDTree，直接输入vim会打开NERDTree，打开一个目录也会打开NERDTree，当文件都关闭只有NERDTree时自动退出，同时也设置快捷键F2来自由切换打开或者关闭NERDTree。

下面我们再说一下NERDTree中的一些操作方法

**窗格跳转**  
一般NERDTree会把界面分成左右两个窗格，那么在窗格之间跳转我们可以使用`<C+W><C+W>`(这个意思代表连续按两次`Ctrl+W`)，顺便普及下，当我们桌面窗格非常多时，在vim中我们可以横向纵向打开多个窗格，那我们也可以通过`<C+W><C+h/j/k/l>`来执行左／下／上／右的跳转。在每个窗格，我们都可以输入`:q`或者`:wq`关闭该窗格。

下面还列有一些在目录树中的**进阶操作**：

key | 描述 
-|-
o | 打开文件，目录或者书签，和我们在对应节点上按回车键一个效果 
go | 打开文件，但是光标仍然停留在目录中 
t | 在新的tab上打开选定的节点 
T | 与t相同，但是光标仍然停留在目录中 
i | 在新窗格中打开文件 
gi | 和i 相同，但是光标仍然停留在目录中 
s | 在水平窗格打开一个文件 
gs | 和s相同，但是光标仍然停留在目录中 
A | 放大NERDTree窗口 
p | 跳到根节点 
P | 跳转到当前节点的父节点 
K | 跳转到当前目录的第一个节点 
J | 跳转到当前目录的最后一个节点 
u | 把上层目录设置为根节点 
C | 设置当前节点为root节点 

还有更多的快捷键，help nerdtree查看详细文档

## YouCompleteMe

![YCM][2]

这个大名鼎鼎的插件在github上已经有一万多星了，足以证明其受欢迎程度，在此之前我曾经尝试过多款补齐插件，但是都没有YCM智能,我们依旧使用Vundle安装YCM,添加这个Plugin：

    Plugin 'Valloric/YouCompleteMe'

但是当vim打开一个文件时，会报错：

    The ycmd server SHUT DOWN (restart with ':YcmRestartServer'). YCM core library not detected; you need to compile YCM before using it. Follow the instructions in the documentation

YCM最复杂的部分就在于它的安装，总是会出现不少问题，下面我们将详细描述正确的**安装方式**：

1. 确保你的vim版本是**7.4**以上的，这个我们在本文的一开始部分就已经说明了，如果不是你还可以通过源码安装，当然vim8.0都出了，你也可以选择它，其次确认你的vim是否支持python2和python3的脚本。可以在vim中执行：:echo has('python') || has('python3'),如果显示1，则满足，否则你就要安装支持Python的vim版本；
1. 安装YCM，使用Vundle安装，这一步我们已经说过了。
1. 这一步对于需要支持C语系的语义支持的人很重要，你需要下载libclang，CLang是开源的C/C++/Objective-C/Objective-C++编译器，YCM使用clang来支持强大的语义分析，这样给出的补齐或者跳转更加精确，但是要使用最新的libclang版本，至少3.9以上的。[官方下载地址][3],可以选择下载二进制文件，也可以从源码编译，不过编译真的很慢，建议直接下二进制，注意系统。

```
    #for ubuntu14.04
    wget http://releases.llvm.org/3.9.0/clang+llvm-3.9.0-x86_64-linux-gnu-ubuntu-14.04.tar.xz
    #for macOS
    wget http://releases.llvm.org/3.9.0/clang+llvm-3.9.0-x86_64-apple-darwin.tar.xz
```
下载后解压：

    xz -d  clang+llvm-3.9.0-x86_64-linux-gnu-ubuntu-14.04.tar.xz
    tar -xvf clang+llvm-3.9.0-x86_64-linux-gnu-ubuntu-14.04.tar
    #MacOS上命令相同
1. 下一步，我们需要编译一个ycm_core的库给YCM用，这样它就可以快速语义分析产生补全或者函数变量快速跳转了。首先我们需要安装cmake来生成makefiles文件：

```
    #ubuntu
    sudo apt-get install cmake  
    #macOS
    brew install cmake
```
其次，需要安装Python头文件：

```
    sudo apt-get install python-dev python3-dev 
    #mac上应该已经默认安装了
```
我们默认你已经使用Vundle安装了YCM在~/.vim/bundle/YouCompleteMe中了。  
下面我们创建一个目录用来编译：

```
    cd ~
    mkdir ycm_build
    cd ycm_build
```
我们先 生成makefiles文件，如果不关心对C系语言支持的话：

    cmake -G " Unix Makefiles" ~/.vim/bundle/YouCompleteMe/third_party/ycmd/cpp

当然，我们都已经下载了Clang3.9了，最好这样：

```
    #将下载的clang移到一个自己建的llvm目录中
    mkdir -p ycm_temp/llvm_root_dir
    mv ~/clang+llvm-3.9.0-x86_64-linux-gnu-ubuntu-14.04/* ~/ycm_temp/llvm_root_dir/
    cd  ycm_build
    cmake -G "Unix Makefiles" -DUSE_SYSTEM_BOOST=ON -DPATH_TO_LLVM_ROOT=~/ycm_temp/llvm_root_dir ~/.vim/bundle/YouCompleteMe/third_party/ycmd/cpp
```
这样就会基于最新的clang生成makefiles文件，再下一步就可以编译了：

    cmake --build . --target ycm_core --config Release

这样就差不多安装完了，当然这仅仅对C系语言进行了语义支持，如果需要支持别的语言，需要自行查看[官方教程][4]。

**使用教程：**  
要使用YCM的强大功能，就需要给libclang提供你项目的编译标志(compile flags)，也就是让libclang能够解析你的代码，这样它才能给出智能的语义分析。  
有两种方式，自动生成的编译数据库或者手动添加编译标志。

1. **自动生成：**  
最简单的方式就是使用你自己项目的编译工具生成一个编译数据的数据库，如前面我们使用的CMake,当然很多时候我们在Linux下使用的都是Gun Make,我们就需要下载一个[Bear][5]的工具，下载源码后安装：

```
    cmake <Bear源码目录>
    make all
    make install # to install
    make check   # to run tests
    make package # to make packages
```
然后回到你的工程,bear make整个工程， 会生成compile_commands.json文件，YCM就是利用这个文件做语义分析，使用CMake的话就不需要借助Bear,只需要在编译时添加-DCMAKE_EXPORT_COMPILE_COMMANDS=ON或者在CMakeLists.txt添加上set( CMAKE_EXPORT_COMPILE_COMMANDS ON来把生成的编译数据库信息拷贝到根目录。
1. **手动添加**  
如果无法自动生成上述文件，我们使用一个.ycm_extra_conf.py的模块，去根据你的文件名，就可以自动给出一些编译选项，让YCM知道如何解析你的代码，在~/.vim/bundle/YouCompleteMe/cpp/ycm/.ycm_extra_conf.py中提供了默认的模板, 一般我们会自定义它的flags数组，然后拷贝一份到~目录中，因为YCM总是在当前目录，或者递归上层目录，找到一个可用的.ycm_extra_conf.py

**定义跳转**

* 跳转到定义GoToDefinition
* 跳转到声明GoToDeclaration
* 以及两者的合体GoToDefinitionElseDeclaration  
在.vimrc中可以定义快捷键：

```
    nnoremap <leader>gl :YcmCompleter GoToDeclaration<CR>
    nnoremap <leader>gf :YcmCompleter GoToDefinition<CR>
    nnoremap <leader>gg :YcmCompleter GoToDefinitionElseDeclaration<CR>
```
`<leader>`键可以自定义，有个很火的Space-vim建议定义为空格`let mapleader="\<Space>"`,这样我们在函数上按空格键加gg,就可以实现跳转了。  
YCM还支持语义诊断：

```
    let g:ycm_error_symbol = '>>'
    let g:ycm_warning_symbol = '>*'
```
这样，不合法的语句，在行首会显示错误，基本和IDE无异了。

## TagBar

使用一般IDE都会在侧面生成一个当前文件的结构图，就不说sublime里面还有个文件缩略图，那么在vim里我们也能添加这么一个tagbar，让我们在处理一个文件时，快速定位到函数变量，对代码了如指掌。但是使用TagBar之前先确保已经有ctags。

```
    #Linux
    sudo apt-get install ctags
    #MacOS
    brew install ctags
```
![tagbar][6]

**安装**

```
    Plugin 'majutsushi/tagbar'
```
再运行安装命令，然后在.vimrc中这样设置：

```
    " Tagbar
    let g:tagbar_width=35
    let g:tagbar_autofocus=1
    let g:tagbar_left = 1
    nmap <F3> :TagbarToggle<CR>
```
这样通过按F3就可以调出TagBar的窗格。

## Ctrap

在一开始的图中，我的下窗格是专门用来搜索文件的，使用**`Ctrap`**这个插件可以支持搜索。  
**安装**

```
    Plugin 'ctrlpvim/ctrlp.vim'
```
执行完安装命令:PluginInstall后，我们做一些设置：

```
    " 打开ctrlp搜索
    let g:ctrlp_map = '<leader>ff'
    let g:ctrlp_cmd = 'CtrlP'
    " 相当于mru功能，show recently opened files
    map <leader>fp :CtrlPMRU<CR>
    "set wildignore+=*/tmp/*,*.so,*.swp,*.zip     " MacOSX/Linux"
    let g:ctrlp_custom_ignore = {
        \ 'dir':  '\v[\/]\.(git|hg|svn|rvm)$',
        \ 'file': '\v\.(exe|so|dll|zip|tar|tar.gz)$',
        \ }
    "\ 'link': 'SOME_BAD_SYMBOLIC_LINKS',
    let g:ctrlp_working_path_mode=0
    let g:ctrlp_match_window_bottom=1
    let g:ctrlp_max_height=15
    let g:ctrlp_match_window_reversed=0
    let g:ctrlp_mruf_max=500
    let g:ctrlp_follow_symlinks=1
```
这样你可以`空格+ff`启用搜索，`空格+fp`显示最近打开文件，在文件列表里上下移动都用`Ctrl+k/j`，`Ctrl+p/n`来在输入的搜索历史上下切换，更多可以查看`:help ctrlp-commands`。搜索默认用的是grep,现在谁都知道`ag`效率更高更快，所以如果想切换搜索的工具可以这么改：

```
    if executable('ag')
      " Use Ag over Grep
      set grepprg=ag\ --nogroup\ --nocolor
      " Use ag in CtrlP for listing files.
      let g:ctrlp_user_command = 'ag %s -l --nocolor -g ""'
      " Ag is fast enough that CtrlP doesn't need to cache
      let g:ctrlp_use_caching = 0
    endif
```
## vim-powerline

这个工具主要用来增强状态栏的，显示更多的信息，文件格式，当前状态，路径

```
    Plugin 'Lokaltog/vim-powerline'
    let g:Powerline_symbols = 'fancy'
    set encoding=utf-8 
    set laststatus=2
```
## 其它的一些设置

**配色**  
对于颜值控来说，一个好看的色彩搭配也能让工作愉悦不少。我的主题配色是[solarized][7],也可以用Vundle安装。然后直接设置：

```
    syntax enable
    set background=dark
    colorscheme solarized
```
**一些基本设置**

```
    "==========================================  
    "General  
    "==========================================  
    " history存储长度。  
    set history=1000         
    "检测文件类型  
    filetype on  
    " 针对不同的文件类型采用不同的缩进格式    
    filetype indent on                 
    允许插件    
    filetype plugin on  
    启动自动补全  
    filetype plugin indent on  
    "兼容vi模式。去掉讨厌的有关vi一致性模式，避免以前版本的一些bug和局限  
    set nocompatible        
    set autoread          " 文件修改之后自动载入。  
    set shortmess=atI       " 启动的时候不显示那个援助索马里儿童的提示  
    
    " 取消备份。  
    "urn backup off, since most stuff is in SVN, git et.c anyway...  
    set nobackup  
    set nowb  
    set noswapfile  
    
    "贴时保持格式  
    set paste  
    "- 则点击光标不会换,用于复制  
    set mouse-=a           " 在所有的模式下面打开鼠标。  
    set selection=exclusive    
    set selectmode=mouse,key  
    
    " No annoying sound on errors  
    " 去掉输入错误的提示声音  
    set noerrorbells  
    set novisualbell  
    set t_vb=  
    set tm=500    
    
    "==========================================  
    " show and format  
    "==========================================  
    "显示行号：  
    set number  
    set nowrap                    " 取消换行。  
    "为方便复制，用<F6>开启/关闭行号显示:  
    nnoremap <F6> :set nonumber!<CR>:set foldcolumn=0<CR>  
    
    "括号配对情况  
    set showmatch  
    " How many tenths of a second to blink when matching brackets  
    set mat=2  
    
    "设置文内智能搜索提示  
    " 高亮search命中的文本。  
    set hlsearch            
    " 搜索时忽略大小写  
    set ignorecase  
    " 随着键入即时搜索  
    set incsearch  
    " 有一个或以上大写字母时仍大小写敏感  
    set smartcase  
    
    " 代码折叠  
    set foldenable  
    " 折叠方法  
    " manual    手工折叠  
    " indent    使用缩进表示折叠  
    " expr      使用表达式定义折叠  
    " syntax    使用语法定义折叠  
    " diff      对没有更改的文本进行折叠  
    " marker    使用标记进行折叠, 默认标记是 {{{ 和 }}}  
    set foldmethod=syntax  
    " 在左侧显示折叠的层次  
    "set foldcolumn=4  
    
    set tabstop=4                " 设置Tab键的宽度        [等同的空格个数]  
    set shiftwidth=4  
    set expandtab                " 将Tab自动转化成空格    [需要输入真正的Tab键时，使用 Ctrl+V + Tab]  
    " 按退格键时可以一次删掉 4 个空格  
    set softtabstop=4  
    
    set ai "Auto indent  
    set si "Smart indent  
    
    "==========================================  
    " status  
    "==========================================  
    "显示当前的行号列号：  
    set ruler  
    "在状态栏显示正在输入的命令  
    set showcmd  
    
    " Set 7 lines to the cursor - when moving vertically using j/k 上下滚动,始终在中间  
    set so=7    
    "set cursorline              " 突出显示当前行
```
**由于篇幅问题，再推荐其它一些好用的插件**

```
    "  Improved C++ STL syntax highlighting
    Plugin 'STL-improved'
    
    " recommend fetch it from https://github.com/tczengming/autoload_cscope.vim.git which support c and cpp
    Plugin 'tczengming/autoload_cscope.vim'
    
    Plugin 'CmdlineComplete'
    Plugin 'xptemplate'
    
    "  Ultimate auto completion system for Vim
    Plugin 'neocomplcache'
    
    Plugin 'genutils'
    Plugin 'lookupfile'
    
    " Fast file navigation
    Plugin 'wincent/Command-T'
    
    " Preview the definition of variables or functions in a preview window
    Plugin 'autopreview'
    
    " Echo the function declaration in the command line for C/C++
    Plugin 'echofunc.vim'
    
    " Under linux need exec 'dos2unix ~/.vim/bundle/QFixToggle/plugin/qfixtoggle.vim'
    Plugin 'Toggle'
    
    Plugin 'Color-Sampler-Pack'
    Plugin 'txt.vim'
    Plugin 'mru.vim'
    Plugin 'YankRing.vim'
    Plugin 'tpope/vim-surround.git'
    Plugin 'DoxygenToolkit.vim'
    Plugin 'tczengming/headerGatesAdd.vim'
    Plugin 'ShowMarks'
    Plugin 'Lokaltog/vim-powerline'
```

[1]: ../img/4d0b51fde4728b21.PNG
[2]: ../img/2e89f883cf99830b.gif
[3]: http://releases.llvm.org/download.html
[4]: https://github.com/Valloric/YouCompleteMe#full-installation-guide
[5]: https://github.com/rizsotto/Bear
[6]: ../img/1266ad3512c09012.png
[7]: https://github.com/altercation/vim-colors-solarized