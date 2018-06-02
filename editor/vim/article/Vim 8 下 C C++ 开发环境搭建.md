## Vim 8 下 C/C++ 开发环境搭建

来源：[http://www.skywind.me/blog/archives/2084](http://www.skywind.me/blog/archives/2084)

时间 2018-04-22 06:38:07

 
2018 年了，网上很多 Vim 下开发 C/C++ 的文章都太过老旧，且不成体系。目前各大 Linux 发行版和 Mac OS X自带的 Vim 都已经跟进到 8了，少数老旧系统也可以下载最新代码重新编译一下。那如何高效的再 Vim 8 中开发 C/C++ 项目呢？
 
假设你已经有一定 Vim 使用经验，并且折腾过 Vim 配置，能够相对舒适的在 Vim 中编写其他代码的时候，准备在 Vim 开始 C/C++ 项目开发，或者你已经用 Vim 编写了几年 C/C++ 代码，想要更进一步，让自己的工作更加顺畅的话，本文就是为你准备的：
 
### 插件管理
 
为什么把插件管理放在第一个来讲呢？这是比较基本的一个东西，如今 Vim 下熟练开发的人，基本上手都有 20-50 个插件，遥想十年前，Vim里常用的插件一只手都数得过来。过去我一直使用老牌的 [Vundle][1] 来管理插件，但是随着插件越来越多，更新越来越频繁，Vundle 这种每次更新就要好几分钟的东西实在是不堪重负了，在我逐步对 Vundle 失去耐心之后，我试用了 [vim-plug][2] ，用了两天以后就再也回不去 Vundle了，它支持全异步的插件安装，安装50个插件只需要一分钟不到的时间，这在 Vundle 下面根本不可想像的事情，插件更新也很快，不像原来每次更新都可以去喝杯茶去，最重要的是它支持插件延迟加载：
 
```
" 定义插件，默认用法，和 Vundle 的语法差不多
Plug 'skywind3000/asyncrun.vim'
Plug 'skywind3000/quickmenu.vim'

" 延迟按需加载，使用到命令的时候再加载或者打开对应文件类型才加载
Plug 'scrooloose/nerdtree', { 'on':  'NERDTreeToggle' }
Plug 'tpope/vim-fireplace', { 'for': 'clojure' }

" 确定插件仓库中的分支或者 tag
Plug 'rdnetto/YCM-Generator', { 'branch': 'stable' }
Plug 'nsf/gocode', { 'tag': 'v.20150303', 'rtp': 'vim' }
```
 
定义好插件以后一个：`:PlugInstall`命令就并行安装所有插件了，比 Vundle 快捷不少，关键是 vim-plug 只有单个文件，正好可以放在我 github 上的 vim 配置仓库中，每次需要更新 vim-plug 时只需要`:PlugUpgrade`，即可自我更新。
 
抛弃 Vundle 切换到 vim-plug 以后，不仅插件安装和更新快了一个数量级，大量的插件我都配置成了延迟加载，Vim 启动速度比 Vundle 时候提高了不少。使用 Vundle 的时候一旦插件数量超过30个，管理是一件很痛苦的事情，而用了 vim-plug 以后，50-60个插件都轻轻松松。
 
### 符号索引
 
现在有好多 ctags 的代替品，比如 gtags, etags 和 cquery。然而我并不排斥 ctags，因为他支持 50+ 种语言，没有任何一个符号索引工具有它支持的语言多。同时 Vim 和 ctags 集成的相当好，大量基础工作可以直接通过 ctags 进行，然而到现在为止，我就没见过几个人把 ctags 用对了的。
 
就连配置文件他们都没写对，正确的 ctags 配置应该是：
 
```
set tags=./.tags;,.tags
```
 
这里解释一下，首先我把 tag 文件的名字从`tags`换成了`.tags`，前面多加了一个点，这样即便放到项目中也不容易污染当前项目的文件，删除时也好删除，gitignore 也好写，默认忽略点开头的文件名即可。
 
前半部分`./.tags;`代表在文件的所在目录下（不是`:pwd`返回的 Vim 当前目录）查找名字为`.tags`的符号文件，后面一个分号代表查找不到的话向上递归到父目录，直到找到`.tags`文件或者递归到了根目录还没找到，这样对于复杂工程很友好，源代码都是分布在不同子目录中，而只需要在项目顶层目录放一个`.tags`文件即可；逗号分隔的后半部分`.tags`是指同时在 Vim 的当前目录（`:pwd`命令返回的目录，可以用`:cd ..`命令改变）下面查找`.tags`文件。
 
### 自动索引
 
过去写几行代码又需要运行一下 ctags 来生成索引，每次生成耗费不少时间。如今 Vim 8 下面自动异步生成 tags 的工具有很多，这里推荐最好的一个： [vim-gutentags][3] ，这个插件主要做两件事情：
 
 
* 确定文件所属的工程目录，即文件当前路径向上递归查找是否有`.git`,`.svn`,`.project`等标志性文件（可以自定义）来确定当前文档所属的工程目录。  
* 检测同一个工程下面的文件改动，能会自动增量更新对应工程的`.tags`文件。每次改了几行不用全部重新生成，并且这个增量更新能够保证`.tags`文件的符号排序，方便 Vim 中用二分查找快速搜索符号。  
 
 
vim-gutentags 需要简单配置一下：
 
```
" gutentags 搜索工程目录的标志，碰到这些文件/目录名就停止向上一级目录递归
let g:gutentags_project_root = ['.root', '.svn', '.git', '.hg', '.project']

" 所生成的数据文件的名称
let g:gutentags_ctags_tagfile = '.tags'

" 将自动生成的 tags 文件全部放入 ~/.cache/tags 目录中，避免污染工程目录
let s:vim_tags = expand('~/.cache/tags')
let g:gutentags_cache_dir = s:vim_tags

" 配置 ctags 的参数
let g:gutentags_ctags_extra_args = ['--fields=+niazS', '--extra=+q']
let g:gutentags_ctags_extra_args += ['--c++-kinds=+px']
let g:gutentags_ctags_extra_args += ['--c-kinds=+px']

" 检测 ~/.cache/tags 不存在就新建
if !isdirectory(s:vim_tags)
    silent! call mkdir(s:vim_tags, 'p')
endif
```
 
有了上面的设置，你平时基本感觉不到 tags 文件的生成过程了，只要文件修改过，gutentags 都在后台为你默默打点是否需要更新数据文件，你根本不用管，还会帮你：
 
```
setlocal tags+=...
```
 
为当前文件添加上对应的 tags 文件的路劲而不影响其他文件。得益于 Vim 8 的异步机制，你可以任意随时使用 ctags 相关功能，并且数据库都是最新的。需要注意的是，gutentags 需要靠上面定义的 project_root 里的标志，判断文件所在的工程，如果一个文件没有托管在 .git/.svn 中，gutentags 找不到工程目录的话，就不会为该野文件生成 tags，这也很合理。想要避免的话，你可以在你的野文件目录中放一个名字为`.root`的空白文件，主动告诉 gutentags 这里就是工程目录。
 
最后啰嗦两句，少用`CTRL-]`直接在当前窗口里跳转到定义，多使用`CTRL-W ]`用新窗口打开并查看光标下符号的定义，或者`CTRL-W }`使用 preview 窗口预览光标下符号的定义。
 
我自己还写过不少关于 ctags 的 vimscript，例如在最下面命令行显示函数的原型而不用急着跳转，或者重复按`ALT+;`在 preview 窗口中轮流查看多个定义，不切走当前窗口，不会出一个很长的列表让你选择，有兴趣可以刨我的 vim dotfiles。
 
### 编译运行
 
再 Vim 8 以前，编译和运行程序要么就让 vim 傻等着结束，不能做其他事情，要么切到一个新的终端下面去单独运行编译命令和执行命令，要么开个 tmux 左右切换。如今新版本的异步模式可以让这个流程更加简化，这里我们使用 [AsyncRun][4] 插件，简单设置下：
 
```
Plug 'skywind3000/asyncrun.vim

" 自动打开 quickfix window ，高度为 6
let g:asyncrun_open = 6

" 任务结束时候响铃提醒
let g:asyncrun_bell = 1

" 设置 F10 打开/关闭 Quickfix 窗口
nnoremap <F10> :call asyncrun#quickfix_toggle(6)<cr>
```
 
该插件可以在后台运行 shell 命令，并且把结果输出到 quickfix 窗口：
 
![][0]
 
最简单的编译单个文件，和 sublime 的默认 build system 差不多，我们定义 F9 为编译单文件:
 
```
nnoremap <silent> <F9> :AsyncRun gcc -Wall -O2 "$(VIM_FILEPATH)" -o "$(VIM_FILEDIR)/$(VIM_FILENOEXT)" <cr>
```
 
其中`$(...)`形式的宏在执行时会被替换成实际的文件名或者文件目录
 
这样按 F9 就可以编译当前文件，同时按 F5 运行：
 
```
nnoremap <silent> <F5> :AsyncRun -raw -cwd=$(VIM_FILEDIR) "$(VIM_FILEDIR)/$(VIM_FILENOEXT)" <cr>
```
 
用双引号引起来避免文件名包含空格，`-cwd=$(VIM_FILEDIR)`的意思时在文件文件的所在目录运行可执行，后面可执行使用了全路径，避免 linux 下面当前路径加`./`而 windows 不需要的跨平台问题。
 
参数`-raw`表示输出不用匹配错误检测模板 (errorformat) ，直接原始内容输出到 quickfix 窗口。这样你可以一边编辑一边 F9 编译，出错了可以在 quickfix 窗口中按回车直接跳转到错误的位置，编译正确就接着执行。
 
接下来是项目的编译，不管你直接使用 make 还是 cmake，都是对一群文件做点什么，都需要定位到文件所属项目的目录，AsyncRun 识别当前文件的项目目录方式和 gutentags相同，从文件所在目录向上递归，直到找到名为`.git`,`.svn`,`.hg`或者`.root`文件或者目录，如果递归到根目录还没找到，那么文件所在目录就被当作项目目录，你重新定义项目标志：
 
```
let g:asyncrun_rootmarks = ['.svn', '.git', '.root', '_darcs', 'build.xml']
```
 
然后在 AsyncRun 命令行中，用`<root>`或者`$(VIM_ROOT)`来表示项目所在路径，于是我们可以定义按 F7 编译整个项目：
 
```
nnoremap <silent> <F7> :AsyncRun -cwd=<root> make <cr>
```
 
并且按 F8 运行它：
 
```
nnoremap <silent> <F8> :AsyncRun -cwd=<root> -raw make run <cr>
```
 
当然，你的 makefile 中需要定义怎么 run ，接着按 F6 执行测试：
 
```
nnoremap <silent> <F6> :AsyncRun -cwd=<root> -raw make test <cr>
```
 
如果你使用了 cmake 的话，还可以照葫芦画瓢，定义 F4 为更新 Makefile 文件，如果不用 cmake 可以忽略：
 
```
nnoremap <silent> <F4> :AsyncRun -cwd=<root> cmake . <cr>
```
 
如果你在 Windows 下使用 GVim 的话，可以弹出新的 cmd.exe 窗口来运行刚才的程序：
 
```
nnoremap <silent> <F5> :AsyncRun -cwd=$(VIM_FILEDIR) -mode=4 "$(VIM_FILEDIR)/$(VIM_FILENOEXT)" <cr>
nnoremap <silent> <F8> :AsyncRun -cwd=<root> -mode=4 make run <cr>
```
 
在 Windows 下使用`-mode=4`选项可以跟 Visual Studio 执行命令行工具一样，弹出一个新的 cmd.exe窗口来运行程序或者项目，于是我们有了下面的快捷键：
 
 
* F4：使用 cmake 生成 Makefile 
* F5：单文件：运行 
* F6：项目：测试 
* F7：项目：编译 
* F8：项目：运行 
* F9：单文件：编译 
* F10：打开/关闭底部的 quickfix 窗口 
 
 
恩，编译和运行基本和 NotePad++ / GEdit 的体验差不多了。如果你重度使用 cmake 的话，你还可以写点小脚本，将 F4 和 F7 的功能合并，检测 CMakeLists.txt 文件改变的话先执行 cmake 更新一下 Makefile，然后再执行 make，否则直接执行 make，这样更自动化些。
 
### 代码补全
 


[1]: https://github.com/VundleVim/Vundle.vim
[2]: https://github.com/junegunn/vim-plug
[3]: https://github.com/ludovicchabant/vim-gutentags
[4]: https://github.com/skywind3000/asyncrun.vim
[0]: https://img2.tuicool.com/nmeQN3y.gif