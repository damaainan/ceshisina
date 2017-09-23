# [以普通用户启动的Vim如何保存需要root权限的文件](http://www.francissoung.com/2015/12/28/%E4%BB%A5%E6%99%AE%E9%80%9A%E7%94%A8%E6%88%B7%E5%90%AF%E5%8A%A8%E7%9A%84Vim%E5%A6%82%E4%BD%95%E4%BF%9D%E5%AD%98%E9%9C%80%E8%A6%81root%E6%9D%83%E9%99%90%E7%9A%84%E6%96%87%E4%BB%B6/)

 Posted by Francis Soung on December 28, 2015

在Linux上工作的朋友很可能遇到过这样一种情况，当你用Vim编辑完一个文件时，运行:wq保存退出，突然蹦出一个错误：

    E45: 'readonly' option is set (add ! to override) 这表明文件是只读的，按照提示，加上!强制保存：:w!，结果又一个错误出现：
    
    "readonly-file-name" E212: Can't open file for writing 文件明明存在，为何提示无法打开？这错误又代表什么呢？查看文档:help E212：
    
    For some reason the file you are writing to cannot be created or overwritten.
    The reason could be that you do not have permission to write in the directory
    or the file name is not valid. 原来是可能没有权限造成的。此时你才想起，这个文件需要root权限才能编辑，而当前登陆的只是普通用户，在编辑之前你忘了使用sudo来启动Vim，所以才保存失败。于是为了防止修改丢失，你只好先把它保存为另外一个临时文件`temp-file-name`，然后退出Vim，再运行`sudo mv temp-file-name readonly-file-name`覆盖原文件。
    

但这样操作过于繁琐。而且如果只是想暂存此文件，还需要接着修改，则希望保留Vim的工作状态，比如编辑历史，buffer状态等等，该怎么办？能不能在不退出Vim的情况下获得root权限来保存这个文件？

## 解决方案

答案是可以，执行这样一条命令即可：

    :w !sudo tee % 接下来我们来分析这个命令为什么可以工作。首先查看文档:help :w，向下滚动一点可以看到：
    
                                *:w_c* *:write_c*
    :[range]w[rite] [++opt] !{cmd}
                Execute {cmd} with [range] lines as standard input
                (note the space in front of the '!').  {cmd} is
                executed like with ":!{cmd}", any '!' is replaced with
                the previous command |:!|.
    
    The default [range] for the ":w" command is the whole buffer (1,$) 把这个使用方法对应前面的命令，如下所示：
    
    :   w   !sudo tee %
    |   |   |  |
    :[range]w[rite] [++opt] !{cmd} 我们并未指定range，参见帮助文档最下面一行，当range未指定时，默认情况下是整个文件。此外，这里也没有指定opt。
    

**Vim中执行外部命令**

接下来是一个叹号!，它表示其后面部分是外部命令，即sudo tee %。文档中说的很清楚，这和直接执行:!{cmd}是一样的效果。后者的作用是打开shell执行一个命令，比如，运行:!ls，会显示当前工作目录下的所有文件，这非常有用，任何可以在shell中执行的命令都可以在不退出Vim的情况下运行，并且可以将结果读入到Vim中来。试想，如果你要在Vim中插入当前工作路径或者当前工作路径下的所有文件名，你可以运行：

    :r !pwd或:r !ls 此时所有的内容便被读入至Vim，而不需要退出Vim，执行命令，然后拷贝粘贴至Vim中。有了它，Vim可以自由的操作shell而无需退出。
    

**命令的另一种表示形式**

再看前面的文档:

    Execute {cmd} with [range] lines as standard input 所以实际上这个:w并未真的保存当前文件，就像执行:w new-file-name时，它将当前文件的内容保存到另外一个new-file-name的文件中，在这里它相当于一个另存为，而不是保存。它将当前文档的内容写到后面cmd的标准输入中，再来执行cmd，所以整个命令可以转换为一个具有相同功能的普通shell命令：
    
    $ cat readonly-file-name | sudo tee % 这样看起来”正常”些了。其中sudo很好理解，意为切换至root执行后面的命令，tee和%是什么呢？
    

**%的意义**

我们先来看%，执行:help cmdline-special可以看到：

    In Ex commands, at places where a file name can be used, the following
    characters have a special meaning.  These can also be used in the expression
    function expand() |expand()|.
        %   Is replaced with the current file name.       *:_%* *c_%* 在执行外部命令时，%会扩展成当前文件名，所以上述的cmd也就成了sudo tee readonly-file-name。此时整个命令即：
    
    $ cat readonly-file-name | sudo tee readonly-file-name 注意：在另外一个地方我们也经常用到%，没错，替换。但是那里%的作用不一样，执行:help :%查看文档：
    
    Line numbers may be specified with:     *:range* *E14* *{address}*
        {number}    an absolute line number
        ...
        %       equal to 1,$ (the entire file)        *:%* 在替换中，%的意义是代表整个文件，而不是文件名。所以对于命令:%s/old/new/g，它表示的是替换整篇文档中的old为new，而不是把文件名中的old换成new。
    

**tee的作用**

现在只剩一个难点: tee。它究竟有何用？维基百科上对其有一个详细的解释，你也可以查看man page。下面这幅图很形象的展示了tee是如何工作的：

![tee.png][0]

ls -l的输出经过管道传给了tee，后者做了两件事，首先拷贝一份数据到文件file.txt，同时再拷贝一份到其标准输出。数据再次经过管道传给less的标准输入，所以它在不影响原有管道的基础上对数据作了一份拷贝并保存到文件中。看上图中间部分，它很像大写的字母T，给数据流动增加了一个分支，tee的名字也由此而来。

现在上面的命令就容易理解了，tee将其标准输入中的内容写到了readonly-file-name中，从而达到了更新只读文件的目的。当然这里其实还有另外一半数据：tee的标准输出，但因为后面没有跟其它的命令，所以这份输出相当于被抛弃。当然也可以在后面补上> /dev/null，以显式的丢弃标准输出，但是这对整个操作没有影响，而且会增加输入的字符数，因此只需上述命令即可。

**命令执行之后**

运行完上述命令后，会出现下面的提示：

    W12: Warning: File "readonly-file-name" has changed and the buffer was changed in Vim as well
    See ":help W12" for more info.
    [O]K, (L)oad File: Vim提示文件更新，询问是确认还是重新加载文件。建议直接输入O，因为这样可以保留Vim的工作状态，比如编辑历史，buffer等，撤消等操作仍然可以继续。而如果选择L，文件会以全新的文件打开，所有的工作状态便丢失了，此时无法执行撤消，buffer中的内容也被清空。
    

## 更简单的方案：映射

上述方式非常完美的解决了文章开始提出的问题，但毕竟命令还是有些长，为了避免每次输入一长串的命令，可以将它映射为一个简单的命令加到.vimrc中：

    1 " Allow saving of files as sudo when I forgot to start vim using sudo.
    2 cmap w!! w !sudo tee > /dev/null % 这样，简单的运行:w!!即可。命令后半部分> /dev/null在前面已经解释过，作用为显式的丢掉标准输出的内容。
    

## 另一种思路

至此，一个比较完美但很tricky的方案已经完成。你可能会问，为什么不用下面这样更常见的命令呢？这不是更容易理解，更简单一些么？

    :w !sudo cat > % **重定向的问题**
    

我们来分析一遍，像前面一样，它可以被转换为相同功能的shell命令：

    $ cat readonly-file-name | sudo cat > % 这条命令看起来一点问题没有，可一旦运行，又会出现另外一个错误：
    
    /bin/sh: readonly-file-name: Permission denied
    
    shell returned 1 这是怎么回事？不是明明加了sudo么，为什么还提示说没有权限？稍安勿躁，原因在于重定向，它是由shell执行的，在一切命令开始之前，shell便会执行重定向操作，所以重定向并未受sudo影响，而当前的shell本身也是以普通用户身份启动，也没有权限写此文件，因此便有了上面的错误。
    

**重定向方案**

这里介绍了几种解决重定向无权限错误的方法，当然除了tee方案以外，还有一种比较方便的方案：以sudo打开一个shell，然后在该具有root权限的shell中执行含重定向的命令，如：

    :w !sudo sh -c 'cat > %' 可是这样执行时，由于单引号的存在，所以在Vim中%并不会展开，它被原封不动的传给了shell，而在shell中，一个单独的%相当于nil，所以文件被重定向到了nil，所有内容丢失，保存文件失败。
    

既然是由于%没有展开导致的错误，那么试着将单引号’换成双引号”再试一次：

    :w !sudo sh -c "cat > %" 成功！这是因为在将命令传到shell去之前，%已经被扩展为当前的文件名。有关单引号和双引号的区别可以参考这里，简单的说就是单引号会将其内部的内容原封不动的传给命令，但是双引号会展开一些内容，比如变量，转义字符等。
    

当然，也可以像前面一样将它映射为一个简单的命令并添加到.vimrc中：

    1 " Allow saving of files as sudo when I forgot to start vim using sudo.
    2 cmap w!! w !sudo sh -c "cat > %" 注意：这里不再需要把输出重定向到/dev/null中。
    

## 写在结尾

至此，借助Vim强大的灵活性，实现了两种方案，可以在以普通用户启动的Vim中保存需root权限的文件。两者的原理类似，都是利用了Vim可以执行外部命令这一特性，区别在于使用不同的shell命令。如果你还有其它的方案，欢迎给我留言。

[0]: http://7xl0td.com1.z0.glb.clouddn.com/2015/12/28/1936978556.png