## 为 vim + tmux 开启真彩色(true color)

来源：[http://lotabout.me/2018/true-color-for-tmux-and-vim/](http://lotabout.me/2018/true-color-for-tmux-and-vim/)

时间 2018-07-12 00:50:18

有一些 vim 主题（如 [gruvbox][2] 或 [solarized8][3] ）在 GUI 和终端下效果不同，有可能是因为这个主题需要 true color (24 位颜色) 的支持，而通常终端只开启 256 色的支持（如`xterm-256color`）。下面来看看怎么开启 true color 支持。
 
## 验证终端的色彩支持 
 
真彩色的支持是需要终端的支持的，常用的终端（如 iterm2, konsole 等) 都已经支持了，详细的列表可以参考 [Colours in terminal][4] 。
 
当然，我们可以自己验证终端是否支持真彩色。在终端里执行 [24-bit-color.sh][5] 脚本，如果支持真彩色，则显示如下：

![][0]
 
否则则类似下图：

![][1]
 
tmux > 2.2 后开始支持真彩色。在`.tmux.conf`中添加如下内容：

```
set -g default-terminal screen-256color
set-option -ga terminal-overrides ",*256col*:Tc" # 这句是关键
```

重新开启 tmux 即可。注意要先退出所有正在运行的 tmux 后再重开 tmux。
 `vim >= 7.4.1770`及`neovim >= 0.2.2`都支持真彩色，但需要少许配置。在`.vimrc`中加入：

```
if has("termguicolors")
    " fix bug for vim
    set t_8f=^[[38;2;%lu;%lu;%lum
    set t_8b=^[[48;2;%lu;%lu;%lum

    " enable true color
    set termguicolors
endif
```

其中`termguicolors`用来开启真彩色，前面两行用来解决 vim 的 BUG (neovim 不需要），其中`^[`是代表 ESC 键，需要在 vim 中按`Ctrl-v ESC`来输入。
 
最后可以在 vim 中开启 terminal (vim 8 或 neovim 中执行`:terminal`)，执行上面的`24-bit-color.sh`来验证是否成功。祝你的终端生活“丰富多彩”！
 
[2]: https://github.com/morhetz/gruvbox
[3]: https://github.com/lifepillar/vim-solarized8
[4]: https://gist.github.com/XVilka/8346728#now-supporting-truecolour
[5]: https://github.com/gnachman/iTerm2/blob/master/tests/24-bit-color.sh
[0]: https://img1.tuicool.com/jqMriqJ.png 
[1]: https://img1.tuicool.com/za6BRbi.png 