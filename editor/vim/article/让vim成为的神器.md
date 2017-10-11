 ## [如何让 vim 成为我们的神器](https://segmentfault.com/a/1190000011466454)

![][0]

# 安装

    sudo apt-get install vim  // Ubuntu

# 新手指南

    vimtutor  // vim 教程

## `移动光标`

    # hjkl
    # 2w 向前移动两个单词
    # 3e 向前移动到第 3 个单词的末尾
    # 0 移动到行首
    # $ 当前行的末尾
    # gg 文件第一行
    # G 文件最后一行
    # 行号+G 指定行
    # <ctrl>+o 跳转回之前的位置
    # <ctrl>+i 返回跳转之前的位置

## `退出`

    # <esc> 进入正常模式
    # :q! 不保存退出
    # :wq 保存后退出

## `删除`

    # x 删除当前字符
    # dw 删除至当前单词末尾
    # de 删除至当前单词末尾，包括当前字符
    # d$ 删除当前行尾
    # dd 删除整行
    # 2dd 删除两行

## `修改`

    # i 插入文本
    # A 当前行末尾添加
    # r 替换当前字符
    # o 打开新的一行并进入插入模式

## `撤销`

    # u 撤销
    # <ctrl>+r 取消撤销

## `复制粘贴剪切`

    # v 进入可视模式
    # y 复制
    # p 粘贴
    # yy 复制当前行
    # dd 剪切当前行

## `状态`

    # <ctrl>+g 显示当前行以及文件信息

## `查找`

    # / 正向查找（n：继续查找，N：相反方向继续查找）
    # ？ 逆向查找
    # % 查找配对的 {，[，(
    # :set ic 忽略大小写
    # :set noic 取消忽略大小写
    # :set hls 匹配项高亮显示
    # :set is 显示部分匹配

## `替换`

    # :s/old/new 替换该行第一个匹配串
    # :s/old/new/g 替换全行的匹配串
    # :%s/old/new/g 替换整个文件的匹配串

## `执行外部命令`

    # :!shell 执行外部命令

# .vimrc

    cd Home               // 进入 Home 目录
    touch .vimrc          // 配置文件
    
    # Unix
    # vim-plug
    # Vim
    curl -fLo ~/.vim/autoload/plug.vim --create-dirs \
        https://raw.githubusercontent.com/junegunn/vim-plug/master/plug.vim
    # Neovim
    curl -fLo ~/.local/share/nvim/site/autoload/plug.vim --create-dirs \
        https://raw.githubusercontent.com/junegunn/vim-plug/master/plug.vim

## 基本配置

### `取消备份`

    set nobackup
    set noswapfile

### `文件编码`

    set encoding=utf-8

### `显示行号`

    set number

### `取消换行`

    set nowrap

### `显示光标当前位置`

    set ruler

### `设置缩进`

    set cindent
    
    set tabstop=2
    set shiftwidth=2

### `突出显示当前行`

    set cursorline

### `左下角显示当前vim模式`

    set showmode

### `代码折叠`

    # 启动 vim 时关闭折叠代码
    set nofoldenable

### `主题`

    syntax enable
    set background=dark
    colorscheme solarized

* [altercation/vim-colors-solarized][1]
* [Anthony25/gnome-terminal-colors-solarized][2]

## 插件配置

### `树形目录`

    Plug 'scrooloose/nerdtree'
    Plug 'jistr/vim-nerdtree-tabs'
    Plug 'Xuyuanp/nerdtree-git-plugin'
    
    autocmd vimenter * NERDTree
    map <C-n> :NERDTreeToggle<CR>
    let NERDTreeShowHidden=1
    let g:NERDTreeShowIgnoredStatus = 1
    let g:nerdtree_tabs_open_on_console_startup=1
    let g:NERDTreeIndicatorMapCustom = {
        \ "Modified"  : "✹",
        \ "Staged"    : "✚",
        \ "Untracked" : "✭",
        \ "Renamed"   : "➜",
        \ "Unmerged"  : "═",
        \ "Deleted"   : "✖",
        \ "Dirty"     : "✗",
        \ "Clean"     : "✔︎",
        \ 'Ignored'   : '☒',
        \ "Unknown"   : "?"
        \ }
    
    # o 打开关闭文件或目录
    # e 以文件管理的方式打开选中的目录
    # t 在标签页中打开
    # T 在标签页中打开，但光标仍然留在 NERDTree
    # r 刷新光标所在的目录
    # R 刷新当前根路径
    # I 显示隐藏文件
    # C 将根路径设置为光标所在的目录
    # u 设置上级目录为根路径
    # ctrl + w + w 光标自动在左右侧窗口切换
    # ctrl + w + r 移动当前窗口的布局位置
    # :tabc 关闭当前的 tab
    # :tabo   关闭所有其他的 tab
    # :tabp   前一个 tab
    # :tabn   后一个 tab
    # gT      前一个 tab
    # gt      后一个 tab

* [scrooloose/nerdtree][3]
* [vim-nerdtree-tabs][4]
* [nerdtree-git-plugin][5]

### `代码，引号，路径补全`

    Plug 'Valloric/YouCompleteMe'
    Plug 'Raimondi/delimitMate'
    Plug 'Shougo/deoplete.nvim', { 'do': ':UpdateRemotePlugins' }

* [Valloric/YouCompleteMe][6]
* [Raimondi/delimitMate][7]
* [Shougo/deoplete.nvim][8]

### `语法高亮，检查`

    Plug 'sheerun/vim-polyglot'
    Plug 'w0rp/ale', {
      \ 'do': 'yarn install',
      \ 'for': ['javascript', 'typescript', 'css', 'less', 'scss', 'json', 'graphql']
    \ }
    
    let g:ale_fixers = {
    \    'javascript': ['eslint'],
    \}
    let g:ale_fix_on_save = 1
    let g:ale_sign_error = '●'
    let g:ale_sign_warning = '▶'

* [w0rp/ale][9]
* [sheerun/vim-polyglot][10]

### `文件，代码搜索`

    Plug 'rking/ag.vim'
    Plug 'kien/ctrlp.vim'

* [kien/ctrlp.vim][11]
* [ggreer/the_silver_searcher][12]
* [rking/ag.vim][13]

### `加强版状态栏`

    Plug 'vim-airline/vim-airline'
    Plug 'vim-airline/vim-airline-themes'
    
    let g:airline_theme='papercolor'

* [vim-airline/vim-airline][14]
* [vim-airline/vim-airline-themes][15]

### `代码注释`

    Plug 'scrooloose/nerdcommenter'
    
    # <leader>cc // 注释
    # <leader>cm 只用一组符号注释
    # <leader>cA 在行尾添加注释
    # <leader>c$ /* 注释 */
    # <leader>cs /* 块注释 */
    # <leader>cy 注释并复制
    # <leader>c<space> 注释/取消注释
    # <leader>ca 切换　// 和 /* */
    # <leader>cu 取消注释
    
    let g:NERDSpaceDelims = 1
    let g:NERDDefaultAlign = 'left'
    let g:NERDCustomDelimiters = {
                \ 'javascript': { 'left': '//', 'leftAlt': '/**', 'rightAlt': '*/' },
                \ 'less': { 'left': '/**', 'right': '*/' }
            \ }

* [scrooloose/nerdcommenter][16]
### `git`
```
    Plug 'airblade/vim-gitgutter'
    Plug 'tpope/vim-fugitive'
```
* [airblade/vim-gitgutter][17]
* [tpope/vim-fugitive][18]

### `Markdown`

    Plug 'suan/vim-instant-markdown'
    
    let g:instant_markdown_slow = 1
    let g:instant_markdown_autostart = 0
    # :InstantMarkdownPreview

* [suan/vim-instant-markdown][19]
### `Emmet`
```
    Plug 'mattn/emmet-vim'
    
    let g:user_emmet_leader_key='<Tab>'
    let g:user_emmet_settings = {
             \ 'javascript.jsx' : {
                \ 'extends' : 'jsx',
            \ },
             \ }
```
* [mattn/emmet-vim][20]
### `html 5`
```
    Plug 'othree/html5.vim'
```
* [othree/html5.vim][21]
### `css 3`
```
    Plug 'hail2u/vim-css3-syntax'
    Plug 'ap/vim-css-color'
    
    augroup VimCSS3Syntax
      autocmd!
    
      autocmd FileType css setlocal iskeyword+=-
    augroup END
```
* [hail2u/vim-css3-syntax][22]
* [ap/vim-css-color][23]

### `JavaScipt`

    Plug 'pangloss/vim-javascript'
    
    let g:javascript_plugin_jsdoc = 1
    let g:javascript_plugin_ngdoc = 1
    let g:javascript_plugin_flow = 1
    set foldmethod=syntax
    let g:javascript_conceal_function             = "ƒ"
    let g:javascript_conceal_null                 = "ø"
    let g:javascript_conceal_this                 = "@"
    let g:javascript_conceal_return               = "⇚"
    let g:javascript_conceal_undefined            = "¿"
    let g:javascript_conceal_NaN                  = "ℕ"
    let g:javascript_conceal_prototype            = "¶"
    let g:javascript_conceal_static               = "•"
    let g:javascript_conceal_super                = "Ω"
    let g:javascript_conceal_arrow_function       = "⇒"
    let g:javascript_conceal_noarg_arrow_function = "🞅"
    let g:javascript_conceal_underscore_arrow_function = "🞅"
    set conceallevel=1

* [pangloss/vim-javascript][24]
### `React`
```
    Plug 'mxw/vim-jsx'
    
    let g:jsx_ext_required = 0
```
* [mxw/vim-jsx][25]
### `Prettier`
```
    Plug 'prettier/vim-prettier'
```
* [prettier/vim-prettier][26]
# 总结

最后，呈上参考配置[.vimrc][27]，如果关于 vim 有更好的 idea，欢迎在评论中交流

```

"==========================================
" 基本设置
"==========================================
" 取消备份
set nobackup
set noswapfile

" 设置文件编码为 UTF-8
set encoding=utf-8






"==========================================
"  显示设置
"==========================================
" 显示行号
set number

" 取消换行
set nowrap

" 显示光标当前位置
set ruler

" 设置缩进的宽度
set tabstop=2

" 突出显示当前行
set cursorline

" 左下角显示当前vim模式
set showmode

" 启动 vim 时关闭折叠代码
set nofoldenable

" 主题
syntax enable
set background=dark
colorscheme solarized






"==========================================
" vim-plug
"==========================================

call plug#begin('~/.vim/plugged')                                                                     

" -----------------------------------------------
" 树形目录
" -----------------------------------------------
Plug 'scrooloose/nerdtree'
Plug 'jistr/vim-nerdtree-tabs'
Plug 'Xuyuanp/nerdtree-git-plugin'

autocmd vimenter * NERDTree
map <C-n> :NERDTreeToggle<CR>
let NERDTreeShowHidden=1
let g:NERDTreeShowIgnoredStatus = 1
let g:nerdtree_tabs_open_on_console_startup=1
let g:NERDTreeIndicatorMapCustom = {
    \ "Modified"  : "✹",
    \ "Staged"    : "✚",
    \ "Untracked" : "✭",
    \ "Renamed"   : "➜",
    \ "Unmerged"  : "═",
    \ "Deleted"   : "✖",
    \ "Dirty"     : "✗",
    \ "Clean"     : "✔︎",
    \ 'Ignored'   : '☒',
    \ "Unknown"   : "?"
    \ }



" -----------------------------------------------
" 代码，引号，路径自动补全
" -----------------------------------------------
Plug 'Valloric/YouCompleteMe'
Plug 'Raimondi/delimitMate'
Plug 'Shougo/deoplete.nvim', { 'do': ':UpdateRemotePlugins' }



" -----------------------------------------------
" 语法高亮，检查
" -----------------------------------------------
Plug 'prettier/vim-prettier', {
  \ 'do': 'yarn install',
  \ 'for': ['javascript', 'typescript', 'css', 'less', 'scss', 'json', 'graphql'] }
Plug 'sheerun/vim-polyglot'
Plug 'w0rp/ale', {
  \ 'do': 'yarn install',
  \ 'for': ['javascript', 'typescript', 'css', 'less', 'scss', 'json', 'graphql']
\ }

let g:ale_fixers = {
\   'javascript': ['eslint'],
\}
let g:ale_fix_on_save = 1
let g:ale_sign_error = '●'
let g:ale_sign_warning = '▶'



" -----------------------------------------------
" 文件，代码搜索
" -----------------------------------------------
Plug 'rking/ag.vim'
Plug 'kien/ctrlp.vim'



" -----------------------------------------------
" 加强版状态条
" -----------------------------------------------
Plug 'vim-airline/vim-airline'
Plug 'vim-airline/vim-airline-themes'

let g:airline_theme='papercolor'



" -----------------------------------------------
" 代码注释
" -----------------------------------------------
Plug 'scrooloose/nerdcommenter'

let g:NERDSpaceDelims = 1
let g:NERDDefaultAlign = 'left'
let g:NERDCustomDelimiters = {
            \ 'javascript': { 'left': '//', 'leftAlt': '/**', 'rightAlt': '*/' },
            \ 'less': { 'left': '/**', 'right': '*/' }
        \ }




" -----------------------------------------------
" git
" -----------------------------------------------
Plug 'airblade/vim-gitgutter'
Plug 'tpope/vim-fugitive'



" -----------------------------------------------
" Vim Markdown
" -----------------------------------------------
Plug 'suan/vim-instant-markdown'

let g:instant_markdown_slow = 1
let g:instant_markdown_autostart = 0



" -----------------------------------------------
" Emmet
" -----------------------------------------------
Plug 'mattn/emmet-vim'

let g:user_emmet_leader_key='<C-Z>'
let g:user_emmet_settings = {
        \ 'javascript.jsx' : {
            \ 'extends' : 'jsx',
        \ },
        \ }



" -----------------------------------------------
" html5
" -----------------------------------------------
Plug 'othree/html5.vim'




" -----------------------------------------------
" css3
" -----------------------------------------------
Plug 'hail2u/vim-css3-syntax'
Plug 'ap/vim-css-color'

augroup VimCSS3Syntax
  autocmd!

  autocmd FileType css setlocal iskeyword+=-
augroup END



" -----------------------------------------------
" JavaScript
" -----------------------------------------------
Plug 'pangloss/vim-javascript'

let g:javascript_plugin_jsdoc = 1
let g:javascript_plugin_ngdoc = 1
let g:javascript_plugin_flow = 1
set foldmethod=syntax
let g:javascript_conceal_function             = "ƒ"
let g:javascript_conceal_null                 = "ø"
let g:javascript_conceal_this                 = "@"
let g:javascript_conceal_return               = "⇚"
let g:javascript_conceal_undefined            = "¿"
let g:javascript_conceal_NaN                  = "ℕ"
let g:javascript_conceal_prototype            = "¶"
let g:javascript_conceal_static               = "•"
let g:javascript_conceal_super                = "Ω"
let g:javascript_conceal_arrow_function       = "⇒"
let g:javascript_conceal_noarg_arrow_function = "🞅"
let g:javascript_conceal_underscore_arrow_function = "🞅"
set conceallevel=1




" -----------------------------------------------
" React
" -----------------------------------------------
Plug 'mxw/vim-jsx'

let g:jsx_ext_required = 0





call plug#end()

" PlugInstall
" PlugUpdate
" PlugClean
" PlugUpgrade
" PlugStatus
" PlugDiff
" PlugSnapshot
```

[0]: ../img/bVWhNF.png
[1]: https://github.com/altercation/vim-colors-solarized
[2]: https://github.com/Anthony25/gnome-terminal-colors-solarized
[3]: https://github.com/scrooloose/nerdtree
[4]: https://github.com/jistr/vim-nerdtree-tabs
[5]: https://github.com/Xuyuanp/nerdtree-git-plugin
[6]: https://github.com/Valloric/YouCompleteMe
[7]: https://github.com/Raimondi/delimitMate
[8]: https://github.com/Shougo/deoplete.nvim
[9]: https://github.com/w0rp/ale
[10]: https://github.com/sheerun/vim-polyglot
[11]: https://github.com/kien/ctrlp.vim
[12]: https://github.com/ggreer/the_silver_searcher
[13]: https://github.com/rking/ag.vim
[14]: https://github.com/vim-airline/vim-airline
[15]: https://github.com/vim-airline/vim-airline-themes
[16]: https://github.com/scrooloose/nerdcommenter
[17]: https://github.com/airblade/vim-gitgutter
[18]: https://github.com/tpope/vim-fugitive
[19]: https://github.com/suan/vim-instant-markdown
[20]: https://github.com/mattn/emmet-vim
[21]: https://github.com/othree/html5.vim
[22]: https://github.com/hail2u/vim-css3-syntax
[23]: https://github.com/ap/vim-css-color
[24]: https://github.com/pangloss/vim-javascript
[25]: https://github.com/mxw/vim-jsx
[26]: https://github.com/prettier/vim-prettier
[27]: https://github.com/FengShangWuQi/to-vim/blob/master/.vimrc