# Vim常用技巧

 时间 2017-09-26 19:49:44  

原文[http://www.linuxidc.com/Linux/2017-09/147110.htm][1]


#### 1. cscope操作

    cscope -bqRCIi
    

#### 2. ctags操作

    ctags -R --fields=+iaS --extra=+q *
    ctags -R --fields=+iaS --extra=+q * --c++-kinds=+cdefglmnpstuvx --c-kinds=+cdefglmnpstuvx --java-kinds=+cefgilm
    

3. vim不能使用 退格键 进行删除操作的解决办法 

两个步骤：

1）去掉讨厌的有关vi一致性模式，避免以前版本的一些bug和局限

    set nocompatible
    

2）backspace有几种工作方式，默认是vi兼容的。对新手来说很不习惯。对老vi 不那么熟悉的人也都挺困扰的。可以用下面的配置来解决：

    set backspace=indent,eol,start
    

indent：如果用了:set indent,:set ai 等自动缩进，想用退格键将字段缩进的删掉，必须设置这个选项。否则不响应。 

eol：如果插入模式下在行开头，想通过退格键合并两行，需要设置eol。 

start：要想删除此次插入前的输入，需设置这个。 

将以上两个命令加到vim的系统配置文件里就可以了，一般在当时用户的家目录里面：`~/.vimrc`。

#### 4. vim窗口大小

使用vim编程时候，不可避免的要分割窗口。如果要水平的平分窗口，可以使用”`:split`“命令，要垂直的平分窗口，则可以使用”`:vsplit`“或者 “`:vertical split`”命令。

如果要改变窗口尺寸，可以用`ctrl + w + +\-` 来改变窗口尺寸，这个操作方式等同于命令” `:<C-W>+` "或者” `:<C-W>-` "，如果一次要增加3个char或者减少3个char，则命令是” `:<C-W>+ 3`"或” `:<C-W>-3`"，其实这两个命令也不是真正的命令，仅仅是替代了操作方式而已。

上面这个问题需要使用到vim的resize命令，命令如下：” `:<C-W>+3` "等效于“`:resize +3`”，命令” `:<C-W>-3` "等效于“`:resize -3`”。

垂直分割窗口时的命令就是在分割窗口命令”`split`“前面加上”`vertical`“，同样地，垂直分割窗口时修改窗口尺寸的命令就是在水平分割窗口时改变窗口尺寸的命令”resize“前面也加上”`vertical`“为”`vertical resize`“。

因此，垂直分割窗口时要给窗口增加3个char或者减少3个char 的命令是"`:vertical resize +3`" 或 "`:vertical resize -3`"。

如果你嫌弃敲命令不方便，则可以在你的.vimrc里面添加下面几行代码，这样就方便多了，可以通过两个键盘操作来改变窗口尺寸了：

    nmap w= :resize +3<CR>
    nmap w- :resize -3<CR>
    nmap w, :vertical resize -3<CR>
    nmap w. :vertical resize +3<CR>

[1]: http://www.linuxidc.com/Linux/2017-09/147110.htm
