# Vim插入相关的命令介绍

 时间 2017-09-12 10:26:23  

原文[http://www.epubit.com.cn/article/1341][1]
<font face=微软雅黑>

![][3]

## 在插入模式下运行命令

在插入模式下，按下`ctrl+o`两个键，可以暂时离开插入模式，执行命令，命令执行完自动返回插入模式。比如按下`ctrl+o`，然后，输入`2w`，输入的位置会移动到当前之后的第二个单词的开头字符。有一些常用的`mapping`可以加入vim的配置文件

    inoremap <C-f> <Right>
    inoremap <C-b> <Left>
    inoremap <C-a> <C-o>^
    inoremap <C-e> <C-o>$

然后打开vim，在插入模式下，按住`ctrl+f`，右移光标，`ctrl+b`，左移光标，`ctrl+a`移动到开头，`ctrl+e`移动到结尾。

## 各个进入插入模式的命令

命令 | 描述
:-|:-
a | 在当前光标的后面开始插入文本
A | 在当前行的末尾开始插入文本
i | 在光标的位置开始插入文本
I | 在当前行的第一个非空字符前开始插入文本
gI | 在当前行首开始插入文本
gi | 在上一次插入的位置开始插入文本
O | 在当前行的上面新起一行开始插入文本
o | 在当前行的下面新起一行开始插入文本
s或者cl | 删除光标当前位置的字符，然后进入插入模式
S或者cc | 删除当前一行，然后进入插入模式
C | 删除当前光标到行末尾的所有字符，然后进入插入模式
c数字c | 删除指定行数的文本，然后进入插入模式

## 插入模式下的一些快捷键

快捷键 | 描述
:-|:-
`ctrl+w` | 删除光标前的一个单词
`ctrl+t` | 相当于在行首按了一下TAB键
`ctrl+d` | 与`ctrl+t`
`ctrl+a` | 插入按esc前插入的那个数据]
`ctrl+h` | 删除光标前一个字符
`ctrl+y` | 输入上一行该位置的字符
`ctrl+o` | 临时执行普通命令
`ctrl+n` | 向后补全
`ctrl+p` | 向前补全
`ctrl+v` | 以十进制的ASCII值插入一个字符
`ctrl+vx` | 以十刘进制的ASCII值插入一个字符
`ctrl+vu` | 以十刘进制的Unicode值插入一个字符
`ctrl+k` | 输入有向图

## 一次在多行插入数据

1. 按下`ctrl+v`，进入块选择模式
1. 通过上下左右，选择你要插入的行
1. 按下`shift+i`，进入编辑模式
1. 输入你要插入的字符
1. 按下`Esc`
1. 这种情况下，按`Ctrl+c`是没有用的

## 粘贴数据的方法

需要在命令模式下，输入`:set paste`，然后，你按下`i`进入到编辑模式后，左下角就会显示：-- INSERT (paste) --，这样，粘贴进来的数据格式就不会乱，退出编辑模式，就是`:set nopaste`

## 一些高级的进入编辑模式的方法

命令 | 描述
:-|:-
`g + ? + m` | Perform rot13 encoding, on movement m
`n + ctrl + a` | 当前光标下的数字加n，如果不是数字没影响
`n + ctrl + x` | 当前光标下的数字减n，如果不是数字没影响
`g + q+ m` | Format lines of movement m to fixed width
`!mc` | Filter lines of movement m through command c
`n!!c` | Filter n lines through command c
`:r!c` | Filter range r lines through command c

### 转载自我的博客 [捕蛇者说][4]


</font>

[1]: http://www.epubit.com.cn/article/1341

[3]: http://img2.tuicool.com/36rAn2N.png
[4]: http://www.bugcode.cn/vim_insert.html