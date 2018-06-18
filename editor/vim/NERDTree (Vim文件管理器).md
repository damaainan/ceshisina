## NERDTree (Vim文件管理器)

来源：[http://blog.collin2.xyz/index.php/archives/94/](http://blog.collin2.xyz/index.php/archives/94/)

时间 2018-06-15 01:14:37



#### 安装

使用[Vundle][0]安装


#### 配置


#### 在vim启动时自动打开NerdTree

``` 
autocmd vimenter * NERDTree
```


#### 在启动vim时没有指定文件，自动打开NerdTreee

``` 
autocmd StdinReadPre * let s:std_in=1
autocmd VimEnter * if argc() == 0 && !exists("s:std_in") | NERDTree | endif
```


#### 当打开目录时，自动打开NerdTree

``` 
autocmd StdinReadPre * let s:std_in=1
autocmd VimEnter * if argc() == 1 && isdirectory(argv()[0]) && !exists("s:std_in") | exe 'NERDTree' argv()[0] | wincmd p | ene | endif
```


#### 快捷键设置

``` 
map <C-n> :NERDTreeToggle<CR>
```


#### 当NerdTree是唯一窗口时，自动关闭NerdTree

``` 
autocmd bufenter * if (winnr("$") == 1 && exists("b:NERDTree") && b:NERDTree.isTabTree()) | q | endif
```


#### 更改默认箭头

``` 
let g:NERDTreeDirArrowExpandable = '▸'
let g:NERDTreeDirArrowCollapsible = '▾'
```


#### 设置NerdTree的宽度

``` 
let NERDTreeWinSize=22
```


#### 常见快捷键



* `？`： 快速帮助文档    
* `o`： 打开一个目录或者打开文件，创建的是buffer，也可以用来打开书签    
* `go`： 打开一个文件，但是光标依然在NERDTree上，创建的是buffer    
* `t`： 打开一个文件，创建的是Tab，对书签依然有效    
* `T`： 打开一个文件，但是光标依然在NERDTree上，创建的是Tab，对书签同样有效    
* `i`: 水平分割创建文件的窗口，创建的是 buffer    
* `gi`: 水平分割创建文件的窗口，但是光标仍然留在 NERDTree    
* `s`: 垂直分割创建文件的窗口，创建的是 buffer    
* `gs`: 和 gi，go 类似    
* `:`收起当前打开的目录    
* `X`: 收起所有打开的目录    
* `e`: 以文件管理的方式打开选中的目录    
* `D`: 删除书签    
* `P`: 大写，跳转到当前根路径    
* `p`: 小写，跳转到光标所在的上一级路径    
* `K`: 跳转到第一个子路径    
* `J`: 跳转到最后一个子路径    
* `<C-j>`和`<C-k>`: 在同级目录和文件间移动，忽略子目录和子文件    
* `C`: 将根路径设置为光标所在的目录    
* `u`: 设置上级目录为根路径    
* `U`: 设置上级目录为跟路径，但是维持原来目录打开的状态    
* `r`: 刷新光标所在的目录    
* `R`: 刷新当前根路径    
* `I`: 显示或者不显示隐藏文件    
* `f`: 打开和关闭文件过滤器    
* `q`: 关闭 NERDTree    
* `A`: 全屏显示 NERDTree，或者关闭全屏    
  


### 多个tab中共享NerdTree

要实现此功能，需要在安装 [vim-nerdtree-tabs][1]插件


#### 设置快捷键

``` 
// 输入/n时，会执行NerdTreeTabsToggle
map <Leader>nNERDTreeTabsToggle<CR>
```


#### 更多可设置的快捷键

``` 
NERDTreeTabsOpen
NERDTreeTabsClose
NERDTreeTabsToggle
NERDTreeTabsFind
NERDTreeMirrorOpen
NERDTreeMirrorToggle
NERDTreeSteppedOpen
NERDTreeSteppedClose
```


### git支持

需要安装    [nerdtree-git-plugin][2]


#### 配置

``` 
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
```


* 显示忽略状态:`let g:NERDTreeShowIgnoredStatus = 1`(消耗比较高，可能会导致比较慢)    
  

[0]: https://github.com/VundleVim/Vundle.vim
[1]: https://github.com/jistr/vim-nerdtree-tabs
[2]: https://github.com/Xuyuanp/nerdtree-git-plugin