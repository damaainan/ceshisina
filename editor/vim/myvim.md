## 常用快捷键

#### 切换窗口



**NERDTree窗格跳转**  
一般 NERDTree 会把界面分成左右两个窗格，那么在窗格之间跳转我们可以使用`<C+W><C+W>`(这个意思代表连续按两次`Ctrl+W`)，顺便普及下，当我们桌面窗格非常多时，在vim中我们可以横向纵向打开多个窗格，那我们也可以通过`<C+W><C+h/j/k/l>`来执行左／下／上／右的跳转。在每个窗格，我们都可以输入`:q`或者`:wq`关闭该窗格。

`F3` 切换侧边栏出现隐藏

光标切换到侧边栏目录 

#### 切换 buffer


【 **vim切换buffer** 】

命令 **:ls** 可查看当前已打开的buffer   
命令 **:b num** 可切换buffer (num为buffer list中的编号)   
  
其它命令:   

    :bn -- buffer列表中下一个 buffer   
    :bp -- buffer列表中前一个 buffer   
    :b# -- 你之前所在的前一个 buffer

    :bdelete num -- 删除第num编号buffer

####  命令行（在状态行下）的高度

    "set cmdheight=1     " 命令行（在状态行下）的高度，设置为1  


### vim 中改变文件编码方式

    :set fileencoding  # 查看
    :set fileencoding=utf-8

### vim dos/unix互转

1.使用命令：dos2unixor

2.使用vim命令:`%s/^M//g` ，注意用 `Ctrl - v`  `Ctrl - m` 输入 `^M`

3.使用vim命令 `:set ff=unix`


## Gvim 64位下载地址

[https://github.com/vim/vim-win32-installer/releases](https://github.com/vim/vim-win32-installer/releases)


## gVim 设置字体

Linux 和 Windows 下写法不同 