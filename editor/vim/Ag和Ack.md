# 如何在vim中搜索项目代码 

如何在vim中搜索项目代码

这里使用的工具分别是Ag和Ack

Ag和Ack都是一个全局搜索工具，但是Ag会更快，比Ack和Grep都要快

通过网络搜索后：http://harttle.com/2015/12/21/vim-search.html

使用方式是用Ag来进行搜索，使用Ack用来展示结果。

现在来进行安装步骤总结

# 安装Ag

    # OSX
    brew install the_silver_searcher
    # Archlinux
    pacman -S the_silver_searcher
    # Ubuntu
    apt-get install silversearcher-ag

# 安装Ack.vim

在~/.vimrc中加入：

    Plugin 'mileszs/ack.vim'
    let g:ackprg = 'ag --nogroup --nocolor --column'

安装完之后需要重新启动vim，不然 光是 so ~/.vimrc 不起作用的，

# Ack的基本操作

    :Ack [options] {pattern} [{directories}]

常用快捷键如下：

    ? 帮助，显示所有快捷键
    
    Enter/o 打开文件
    
    O 打开文件并关闭Quickfix
    
    go 预览文件，焦点仍然在Quickfix
    
    t 新标签页打开文件
    
    q 关闭Quickfix

