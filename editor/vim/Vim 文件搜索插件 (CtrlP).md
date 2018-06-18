## Vim 文件搜索插件 (CtrlP)

来源：[http://blog.collin2.xyz/index.php/archives/99/](http://blog.collin2.xyz/index.php/archives/99/)

时间 2018-06-15 01:17:41


vim的模糊搜索工具，支持文件、缓冲区、MRU文件和标签等，可以通过正则表达式搜索(ctrl+r切换)，速度相对同类软件较慢，但是完全由vimscript实现，所以依赖少，安装和配置都比较简单。如果认为速度慢的话，可以调用ag等外部程序来提升搜索速度


#### 安装

使用[Vundle][0]安装


#### 提供的命令



* `:CtrlP`或者`CtrlP [起始目录]`: 在查找文件模式下调用CtrlP    
* `:CtrlPBuffer`或`:CtrlPMRU`: 在查找缓冲区调用CtrlP或者查找MRU文件模式    
* `<F5>`: 清除当前目录的缓存以获取新文件，删除已删除的文件并应用新的忽略选项    
* `<c-f>`和`<c-b>`: 在模式之间切换    
* `<c-d>`: 切换到仅文件名搜索而不是完整路径搜索    
* `<c-r>`: 切换到正则表达式模式    
* `<c-j>`和`<c-k>`或者方向键: 在结果列表中导航    
* `<c-t>`或`<c-v>`,`<c-x>`: 在新的Tab或者分割屏幕中打开选定文件    
* `<c-n>`,`<c-p>: 悬着历史记录中的下一个/上一个    
* `<c-y>`: 创建一个新文件及其父目录    
* `<c-z>`: 标记/取消标记多个文件，并使用`<c-o>`打开它们    
* `:number`: 打开选中文件，并跳转到指定行数    
* `:diffthis`: 打开多个文件进    
* `:help ctrlp-options`: 获取帮助文档    
  


#### 配置

大部分快捷键默认都给了，就不做修改了，如果你想修改默认的快捷键的话，可以参考下文档。

使用vim的wildignore和CtrlP的g:ctrlp_custom_ignore来排除文件

``` 
set wildignore+=*/tmp/*,*.so,*.swp,*.zip    " MacOSX/Linux
set wildignore+=*\\tmp\\*,*.swp,*.zip,*.exe " Windows

let g:ctrlp_custom_ignore = '\v[\/]\.(git|hg|svn)'
let g:ctrlp_custom_ignore = {
  \ 'dir': '\v[\/]\.(git|hg|svn)',
  \ 'file': '\v\.(exe|so|dll)$',
  \ 'link': 'some_bad_symbolic_links',
  \ }
```

如果要使用ag的话，则必须开启用户自定义命令(`g:ctrlp_user_command`)选项，但是开启这个选项的话，会带来一些副作用:



* g:ctrlp_show_hidden(用于搜索隐藏的文件和目录)失效
* g:ctrl_custom_ignore(自定忽略文件)失效
  

对了，这个插件是有中文文档的，一般情况下在~/.vim/bundle/ctrlp.vim/doc 目录里面的ctrlp.cnx文件，如果你安装后没有这个文件的话，你可以去 [github][1]
上查看

``` 
"调用ag进行搜索提升速度，同时不使用缓存文件
if executable('ag')
  set grepprg=ag\ --nogroup\ --nocolor
  let g:ctrlp_user_command = 'ag %s -l --nocolor -g ""'
  let g:ctrlp_use_caching = 0
endif
```



[0]: https://github.com/VundleVim/Vundle.vim
[1]: https://github.com/vimcn/ctrlp.cnx