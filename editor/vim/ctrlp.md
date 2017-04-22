使用频率最高的插件之一

作用: 模糊搜索, 可以搜索文件/buffer/mru/tag等等github: 原始[kien/ctrlp](https://github.com/kien/ctrlp.vim), 使用的是国人改进版本 [ctrlpvim/ctrlp.vim](https://github.com/ctrlpvim/ctrlp.vim)
安装

    Plugin 'ctrlpvim/ctrlp.vim'

##### 使用

绑定快捷键

<leader>-f模糊搜索最近打开的文件(MRU)（当前配置下，即 ;f  ）
<leader>-p模糊搜索当前目录及其子目录下的所有文件（ ;p ）

搜索框出来后, 输入关键字, 然后

    ctrl + j 进行下选择
    ctrl + k 进行上选择
    ctrl + v 在当前窗口水平分屏打开文件
    ctrl + x 同上, 垂直分屏
    ctrl + t 在tab中打开


##### 最终配置

    Bundle 'ctrlpvim/ctrlp.vim'
    let g:ctrlp_map = '<leader>p'
    let g:ctrlp_cmd = 'CtrlP'
    map <leader>f :CtrlPMRU<CR>
    let g:ctrlp_custom_ignore = {
         'dir':  '\v[\/]\.(git|hg|svn|rvm)$',
         'file': '\v\.(exe|so|dll|zip|tar|tar.gz|pyc)$',
         }
    let g:ctrlp_working_path_mode=0
    let g:ctrlp_match_window_bottom=1
    let g:ctrlp_max_height=15
    let g:ctrlp_match_window_reversed=0
    let g:ctrlp_mruf_max=500
    let g:ctrlp_follow_symlinks=1



## 附: ctrlp的插件 ctrlp - funky 作用: 模糊搜索当前文件中所有函数 

github: [ctrlp-funky](https://github.com/tacahiroy/ctrlp-funky)

## 安装 

    Bundle 'tacahiroy/ctrlp-funky'

## 使用 

绑定快捷键 

    <leader> fu 进入当前文件的函数列表搜索 
    <leader> fU 搜索当前光标下单词对应的函数

## 最终配置 

    Bundle 'tacahiroy/ctrlp-funky'
    nnoremap <Leader>fu :CtrlPFunky<Cr>
    " narrow the list down with a word under cursor
    nnoremap <Leader>fU :execute 'CtrlPFunky ' . expand('<cword>')<Cr>
    let g:ctrlp_funky_syntax_highlight = 1
    
    let g:ctrlp_extensions = ['funky']

