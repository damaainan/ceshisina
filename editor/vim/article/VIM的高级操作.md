# [带你领略VIM的高级操作][0]


> 导读 **此文收集了一些简单的 VIM 操作，这些操作要么其它普通文本编辑器不能完成，要么完成起来很慢。通过本文的介绍，可以坚定初学者学习 VIM 的决心与信心。如果你有什么好的易于演示的简易技巧，欢迎留言。另外，如果没有明确说明，本文中的提到的按键均是大小写敏感的。例如，文中提到“按下 G”时，你按的键应该是“Shift + G”。**

![带你领略VIM的高级操作带你领略VIM的高级操作][1]

**1. 准备工作**

首先，我们打开 VIM，输入一段文本，用于今天的演示：

    this is a test
    

**2. 查找替换**

按几下 ESC 进入 Normal 模式，输入以下命令： `:%s/ /\r/g/` 。回车后得到的效果如下：

    this
    is
    a
    test
    

解说：这条命令的作用是，将文章中所有的空格替换为回车。几乎所有的编辑器都支持查找替换，但并不是所有的编辑器都支持把空格替换为回车，因此这个功能在许多别的编辑器里做起来是比较繁琐的。

**3. 行的拼接**

刚才我们把一行文字打碎为 4 行了，那如何再把它们拼接起来呢？当然，我们可以通过前面说的查找替换的方式，将回车再替换为空格，实现行的拼接。但是，这里我们用的是另一种方式。

按几下 ESC 进入 Normal 模式，然后输入这段命令： `ggVG` 。`gg` 表示跳到文本开头，`V` 表示进入行选择模式，`G` 表示选择到文章末尾。通过这 3 条命令，总共 4 个按键，我们选中了整篇文章。

然后，按下冒号 `:` 进入命令模式，状态栏上出现： `:'< ,'>` 字样，在它后面输入 `j`，然后回车，可以看到，整篇文章又被拼接起来了，整个操作包括回车只按了 7 次键：

    this is a test
    

**4. 复制粘贴与重复动作**

按几下 ESC 确认当前处在 Normal 模式下，然后按 `yy` ，即可将当前行复制到默认寄存器中(相当于剪贴板)。然后按下 `12p`，VIM 将执行粘贴动作 12 次，屏幕上出现了 13 行这样的字符：

    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    this is a test
    

解说：在 VIM 中，复制和粘贴操作相当快捷。另外，VIM 中大部分命令都可以通过在命令前加数字重复若干遍。

**5. 列操作**

接下来我们把每一行的开头第一个字母改为大写。

按几下 ESC 确认当前处在 Normal 模式下，然后按 `gg` 跳到第一行，按下 `Ctrl + v` 进入列选择模式(如果你按下 `Ctrl + v` 没能进入列选择模式，请看这里)，然后按 `G`，跳到文章最后一行，此时你应该看到，文本的第一列被选中了，而且只选中了第一列。按下 `U` 键，可以看到，每行的第一个字母都变为大写了。提示：选中文本后按 `u` 可以将文本变为小写，选中文本后按 `~` 可以翻转原有的大小写。

    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    This is a test
    

然后，我们在每行的前面加上一个星号。按下 `gg` 跳到第一行，按 `Ctrl + v` 进入列选择模式，再按 `G`，选中全文的第一列，然后按 `I`，进入列插入状态，输入星号 `*`，再按下 ESC，你会看到，所有行之前都出现了一个星号：

    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    *This is a test
    

解说：对于编写程序的人来说，把一段代码批量注释掉是一个很常见的操作，使用列插入可以很容易地做到这一点。另外，列选择后按 `x` 删除被选中的块，可以批量地解除注释。

**6. 宏的录制**

接下来，我们要将文本的偶数行修改为： `This is another test` 。由于所有的偶数行都要进行同样的操作，因此我们把这个操作录制下来，然后重复播放若干遍，就能很快地完成这项工作了。

首先，按几下 ESC 确认处在 Normal 模式下，再按下 `gg` 跳到第一行，准备开始操作。我们首先按下 `q` 键，然后再按一个其它字母，将这个宏录制到该字母对应的寄存器下。例如我们这里使用 `m 寄存器`，则按 `qm`。此时 VIM 状态栏出现“recording”字样，表明已经进入了录制状态。

然后，我们把第二行的 `a` 修改为 another。首先按 `j` 进入第二行，然后按 `$` 跳到行末，再按两下 `b` 往前跳两个单词，此时光标停在字母 `a` 上。然后我们按下 `caw` 键删除 a 并进入插入状态，然后输入 another ，按 ESC 回到 Normal 状态，按 `j` 进入下一行，整个操作步骤就完成了。最后，我们再按一下 `q`，结束该宏的录制。

接下来我们播放这个宏，完成整个操作步骤。在键盘上输入 `1000@m`，表示将` m 寄存器`里的宏播放 1000 次，马上可以看到，文章中所有偶数行的 `a` 都变成了 another。

    *This is a test
    *This is another test
    *This is a test
    *This is another test
    *This is a test
    *This is another test
    *This is a test
    *This is another test
    *This is a test
    *This is another test
    *This is a test
    *This is another test
    *This is a test
    

解说：虽然我们指定播放 1000 次，但事实上，执行到第 6 次的时候，光标挪到了屏幕最下方，于是执行过程就自动停止了。因此，在批量操作的时候，我们可以指定足够大的数字，而不用担心出现问题。

另外，修改 `a` 的时候，我们跳到行末后再使用 `b` 命令以单词为单位跳转，而没使用 `h` 一个字母一个字母往回挪，我们使用`caw` 修改整个单词，而不使用 `s` 命令删除单个字母并进入 `Insert` 模式。这些细节可以保证录制得到的宏更具有一般性。

**7. 行尾块操作**

注：本章由 Jason Han 网友贡献，感谢他来信指出滇狐原先对于行尾块操作理解的错误。

下面，我们要在每行的尾部都添加一个感叹号。之前我们在每行头部添加一个星号的时候，用的是 Ctrl-V 列操作。现在要在行尾添加，能不能继续用列操作呢？直观上似乎是不行的，每行的长度不一样，行尾位置参差不齐，如何使用列模式往行尾添加东西呢？

事实上，Vim 提供了一种特殊的列模式，叫做行尾块模式，也就是说，我们是可以通过 Ctrl-V 模式来选中长度不同的行的行尾，然后对行尾作统一操作的，操作步骤如下：

按下 `gg` 跳到第一行，按 Ctrl-V 进入列选择模式，再按 `G`，选中全文的第一列，然后按下 `$`，进入行尾块模式，按下 `A`，进入块插入状态，输入`星号 !`，再按下 ESC，你会看到，所有行尾部都出现了一个感叹号：

    *This is a test!
    *This is another test!
    *This is a test!
    *This is another test!
    *This is a test!
    *This is another test!
    *This is a test!
    *This is another test!
    *This is a test!
    *This is another test!
    *This is a test!
    *This is another test!
    *This is a test!
    

**8. 点命令**

接下来，我们在每行的末尾加上一个小于号 `<` 。每行下面插入一个新行，写上一个大于号 `>`。

由于我们需要在每行后面添加新行，因此我们无法使用块选择方式批量添加小于大于号。使用宏录制的方式是可以做到这点的，但操作稍嫌繁琐了一些。使用点命令，可以非常方便地做到这一点。

先按几下 ESC 确认当前出于 Normal 模式，然后使用 `gg` 跳到第一行，按 `A` 进行行尾插入，输入 `<` ，然后按下回车，输入 `>`，最后 ESC 回到 Normal 状态，第一行修改就完成了。

然后，我们按 `j` 进入下一行，也就是第三行，再按 `.`，可以看到，第三行尾部也出现了小于号，并且自动添加了第四行的大于号。反复按 `j.j.j.` ，直到每一行都完成了这个编辑动作为止。

    *This is a test!<>
    *This is another test!<>
    *This is a test!<>
    *This is another test!<>
    *This is a test!<>
    *This is another test!<>
    *This is a test!<>
    *This is another test!<>
    *This is a test!<>
    *This is another test!<>
    *This is a test!<>
    *This is another test!<>
    *This is a test!<>
    

解说：点命令的作用是，重复最近一次所做的编辑操作。由于在第一行里做的操作是行尾添加并插入新行，因此在第三行（原先的第二行）重复这个动作的时候，也会在行尾添加同样的字符。点命令功能不如宏强大，但它使用起来比宏简便，因此也有着广泛的用途。

[0]: http://www.linuxprobe.com/vim-advanced-operation.html
[1]: http://www.linuxprobe.com/wp-content/uploads/2017/09/vim.jpg