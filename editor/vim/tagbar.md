先安装插件

    Plugin 'majutsushi/tagbar'


_vimrc 配置

    " tagbar 配置
    let g:tagbar_ctags_bin='D:/implement/ctags58/ctags.exe'
    nmap <F4> :TagbarToggle<CR>
    " 启动时自动focus
    let g:tagbar_autofocus = 1
    
    
需要配置 ctags  目录

CTags 下载地址 https://packagecontrol.io/packages/CTags