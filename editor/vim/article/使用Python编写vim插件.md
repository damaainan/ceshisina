# 如何使用Python编写vim插件

 时间 2017-11-28 13:41:22  

原文[http://blog.csdn.net/archofortune/article/details/78653853][1]

**将其中的 `:py` 替换为 `:py3` 即可使用   **

## 前言

vim是个伟大的编辑器，不仅在于她特立独行的编辑方式，还在于她强大的扩展能力。然而，vim自身用于写插件的语言vimL功能有很大的局限性，实现功能复杂的插件往往力不从心，而且运行效率也不高。幸好，vim早就想到了这一点，她提供了很多外部语言接口，比如Python，ruby，lua，Perl等，可以很方便的编写vim插件。本文主要介绍如何使用Python编写vim插件。

## 准备工作

### 1. 编译vim，使vim支持Python

在编译之前， `configure` 的时候加上 `--enable-pythoninterp` 和 `--enable-python3interp` 选项，使之分别支持Python2和Python3 

编译好之后，可以通过 `vim --version | grep +python` 来查看是否已经支持Python，结果中应该包含 `+python` 和 `+python3` ，当然也可以编译成只支持Python2或Python3。 

现在好多平台都有直接编译好的版本，已经包含Python支持，直接下载就可以了：

* Windows：可以在 [这里][4] 下载。
* Mac OS：可以直接 brew install vim 来安装。
* Linux：也有快捷的安装方式，就不赘言了。

### 2. 如何让Python能正常工作

虽然vim已经支持Python，但是可能 `:echo has("python")` 或 `:echo has("python3")` 的结果仍是 0 ，说明Python还不能正常工作。 

此时需要检查：

1. 系统上是否装了Python?
1. Python是32位还是64位跟vim是否匹配?
1. Python的版本跟编译时的版本是否一致（编译时的版本可以使用 `:version` 查看）
1. 通过 pythondll 和 pythonthreedll 来分别指定Python2和Python3所使用的动态库。   
例如，可以在vimrc里添加   
```
    set pythonthreedll=D:/python/python35.dll # windows
    set pythondll=/Users/yggdroot/.python2.7.6/lib/libpython2.7.so# linux
```
经此4步，99%能让Python工作起来，剩下的1%就看人品了。

补充一点： 

对于neovim，执行

    pip2 install --user --upgrade neovim
    pip3 install --user --upgrade neovim

就可以添加Python2和Python3的支持，具体参见 `:h provider-python` 。 

## 从hello world开始

在命令行窗口执行 `:pyx print("hello world!")` ，输出“hello world！”，说明Python工作正常，此时我们已经可以使用Python来作为vim的 `EX` 命令了。 

## 操作vim像vimL一样容易

怎么用Python来访问vim的信息以及操作vim呢？很简单，vim的Python接口提供了一个叫vim的模块（module）。vim模块是Python和vim沟通的桥梁，通过它，Python可以访问vim的一切信息以及操作vim，就像使用vimL一样。所以写脚本，首先要 `:py import vim` 。 

也可以将命令写入 vimrc 打开时即执行

### vim模块

vim模块提供了两个非常有用的函数接口:

* vim.command(str) 执行vim中的命令 str (ex-mode)，返回值为None，例如： 
```
    :py vim.command("%s/\s\+$//g")
    :py vim.command("set shiftwidth=4")
    :py vim.command("normal! dd")
```
* vim.eval(str) 求vim表达式 str 的值，（什么是vim表达式，参见 `:h expr` ），返回结果类型为： 


  * string : 如果vim表达式的值的类型是 string 或 number
  * list ：如果vim表达式的值的类型是一个vim list（ :h list ）
  * dictionary ：如果vim表达式的值的类型是一个vim dictionary（ :h dict ）

例如：

    :py sw = vim.eval("&shiftwidth")
    :py print vim.eval("expand('%:p')")
    :py print vim.eval("@a")

vim模块还提供了一些有用的对象:

* Tabpage 对象（ `:h python-tabpage` ）   
一个 Tabpage 对象对应vim的一个Tabpage。
* Window 对象（ `:h python-window` ）   
一个 Window 对象对应vim的一个Window。
* Buffer 对象（ `:h python-buffer` ） 

一个 `Buffer` 对象对应vim的一个buffer，Buffer对象提供了一些属性和方法，可以很方便操作buffer。 

例如 (假定 b 是当前的buffer) : 

    :py print b.name            # write the buffer file name
    :py b[0] = "hello!!!"       # replace the top line
    :py b[:] = None             # delete the whole buffer
    :py del b[:]                # delete the whole buffer
    :py b[0:0] = [ "a line" ]   # add a line at the top
    :py del b[2]                # delete a line (the third)
    :py b.append("bottom")      # add a line at the bottom
    :py n = len(b)              # number of lines
    :py (row,col) = b.mark('a') # named mark
    :py r = b.range(1,5)        # a sub-range of the buffer
    :py b.vars["foo"] = "bar"   # assign b:foo variable
    :py b.options["ff"] = "dos" # set fileformat
    :py del b.options["ar"]     # same as :set autoread<
* vim.current 对象（ `:h python-current` ） 

vim.current 对象提供了一些属性，可以方便的访问“ **当前** ”的vim对象 

属性 | 含义 | 类型 
-|-|-
vim.current.line | The current line (RW) | String 
vim.current.buffer | The current buffer (RW) | Buffer 
vim.current.window | The current window (RW) | Window 
vim.current.tabpage | The current tab page (RW) | TabPage 
vim.current.range | The current line range (RO) | Range

### python访问vim中的变量

访问vim中的变量，可以通过前面介绍的 vim.eval(str) 来访问，例如： 

    :py print vim.eval("v:version")

但是， 还有更 **pythonic** 的方法： 

* 预定义vim变量（ `v:var` ） 

可以通过 `vim.vvars` 来访问预定义vim变量， `vim.vvars` 是个类似 Dictionary 的对象。例如，访问 `v:version` ： 

    :py print vim.vvars["version"]
* 全局变量（ g:var ） 

可以通过 vim.vars 来访问全局变量， `vim.vars` 也是个类似 `Dictionary` 的对象。例如，改变全局变量 `g:global_var` 的值： 

    :py vim.vars["global_var"] = 123
* tabpage变量（ `t:var` ） 

例如：

    :py vim.current.tabpage.vars["var"] = "Tabpage"
* window变量（ `w:var` ） 

例如：

    :py vim.current.window.vars["var"] = "Window"
* buffer变量（ `b:var` ） 

例如：

    :py vim.current.buffer.vars["var"] = "Buffer"

### python访问vim中的选项（ options ） 

访问vim中的选项，可以通过前面介绍的 vim.command(str) 和 vim.eval(str) 来访问，例如： 

    :py vim.command("set shiftwidth=4")
    :py print vim.eval("&shiftwidth")

当然， 还有更 **pythonic** 的方法： 

* 全局选项设置（ `:h python-options` ） 

例如：

    :py vim.options["autochdir"] = True

注意：如果是 `window-local` 或者 buffer-local 选项，此种方法会报 KeyError 异常。对于 `window-local` 和 `buffer-local` 选项，请往下看。
* window-local 选项设置 

例如：

    :py vim.current.window.options["number"] = True
* buffer-local 选项设置 

例如：

    :py vim.current.buffer.options["shiftwidth"] = 4

## 两种方式写vim插件

* 内嵌式
```
    py[thon] << {endmarker}
    {script}
    {endmarker}
```
`{script}` 中的内容为Python代码， `{endmarker}` 是一个标记符号，可以是任何字符串，不过 `{endmarker}` 前面不能有任何的空白字符，也就是要顶格写。 

例如，写一个函数，打印出当前buffer所有的行( Demo.vim )： 

    function! Demo()
    py << EOF
    import vim
    for line in vim.current.buffer:
        print line
    EOF
    endfunction
    call Demo()

运行 `:source %` 查看结果。 

* 独立式

把Python代码写到 `*.py` 中，vimL只用来定义全局变量、map、command等， [LeaderF][5] 就是采用这种方式。个人更喜欢这种方式，可以把全部精力集中在写Python代码上。

## 异步

* 多线程

可以通过Python的 `threading` 模块来实现多线程。但是，线程里面只能实现与vim无关的逻辑，任何试图在线程里面操作vim的行为都可能（也许用“肯定会”更合适）导致vim崩溃，甚至包括只 **读** 一个vim选项。虽然如此，也比vimL好多了，毕竟聊胜于无。
* subprocess

可以通过Python的 `subprocess` 模块来调用外部命令。 

例如：

    :py import subprocess
    :py print subprocess.Popen("ls -l", shell=True, stdout=subprocess.PIPE).stdout.read()

也就是说，从支持Python起，vim就已经支持异步了（虽然直到vim7.4才基本没有bug），Neovim所增加的异步功能，对用Python写插件的小伙伴来说，没有任何吸引力。好多Neovim粉竟以引入异步（job）而引以为傲，它什么时候能引入真正的多线程支持我才会服它。

## 案例

著名的补全插件YCM和模糊查找神器 [LeaderF][5] 都是使用Python编写的。 

## 缺陷

由于GIL的原因，Python线程无法并行处理；而vim又不支持Python的进程（ [https://github.com/vim/vim/issues/906][6] ），计算密集型任务想利用多核来提高性能已不可能。 

## 奇技淫巧

* 把buffer中所有单词首字母变为大写字母
```
    :%pydo return line.title()
```
* 把buffer中所有的行镜像显示

例如，把

    vim is very useful
    123 456 789
    abc def ghi
    who am I

变为

    lufesu yrev si miv
    987 654 321
    ihg fed cba
    I ma ohw

可以执行此命令： `:%pydo return line[::-1]`

## 总结

以上只是简单的介绍，更详细的资料可以参考 `:h python` 。


[1]: http://blog.csdn.net/archofortune/article/details/78653853

[4]: https://github.com/vim/vim-win32-installer/releases
[5]: https://github.com/Yggdroot/LeaderF
[6]: https://github.com/vim/vim/issues/906