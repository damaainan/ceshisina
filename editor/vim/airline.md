
安装字体
  1. Linux: 下载 [powerline fonts](https://github.com/powerline/fonts),并按指示安装。
  2. windows:下载里面的四种字体并安装 [powerline fonts](https://github.com/eugeii/consolas-powerline-vim)

使用Vundle安装：在vimrc配置的Vundle插件列表加入
    
    Plugin 'bling/vim-airline'
并在Vim 执行 
    
    :PluginInstall。
    
给gvim更换字体。
打开gvim，执行

    :set guifont=*
这样可以打开字体选择库
找到名字中带powerline的字体，确认。

在vim中执行

    :set guifont
得到你目前使用字体的名字，比如我得到的内容为
    
    guifont=Sauce_Code_Powerline:h13:cANSI
然后在你的vimrc中找到
set guifont 一项（若没有，新建）
将其改为

    set guifont=Sauce_Code_Powerline:h13:cANSI
 
其中h13的意思是字号设置为13px

配置airline

     if !exists('g:airline_symbols')
        let g:airline_symbols = {}
      endif
    " 复制自插件文档 doc
      " old vim-powerline symbols
      let g:airline_left_sep = '⮀'
      let g:airline_left_alt_sep = '⮁'
      let g:airline_right_sep = '⮂'
      let g:airline_right_alt_sep = '⮃'
      let g:airline_symbols.branch = '⭠'
      let g:airline_symbols.readonly = '⭤'
  
  
  
