## Vim缩进有关的设置总结

来源：[https://www.kawabangga.com/posts/2817](https://www.kawabangga.com/posts/2817)

时间 2018-03-29 16:22:10


```
set expandtab       "Use softtabstop spaces instead of tab characters for indentation
set shiftwidth=4    "Indent by 4 spaces when using >>, <<, == etc.
set softtabstop=4   "Indent by 4 spaces when pressing <TAB>
 
set autoindent      "Keep indentation from previous line
set smartindent     "Automatically inserts indentation in some cases
set cindent         "Like smartindent, but stricter and more customisable

```

https://stackoverflow.com/questions/30408178/indenting-after-newline-following-new-indent-level-vim



* `tabstop` ： 一个tab等于多少个空格，当`expandtab`的情况下，会影响在插入模式下按下`<tab>`键输入的空格，以及真正的`\t` 用多少个空格显示；当在`noexpandtab` 的情况下，只会影响`\t` 显示多少个空格（因为插入模式下按`<tab>` 将会输入一个字符`\t` ）    
* `expandtab` ：设为真，在插入模式下按`<tab>`会插入空格，用`>`缩进也会用空格空出来；如果设置为假`noexpandtab`，那么插入模式下按`<tab>`就是输入`\t`，用`>`缩进的结果也是在行前插入`\t`。    
* `softtabstop` ：按下`<tab>` 将补出多少个空格。在`noexpandtab` 的状态下，实际补出的是`\t` 和空格的组合。所以这个选项非常奇葩，比如此时`tabstop=4 softtabstop=6` ，那么按下`<tab>` 将会出现一个`\t` 两个空格。    
* `shiftwidth` ：使用`>>` `<<` 或`==` 来缩进代码的时候补出的空格数。这个值也会影响`autoindent` 自动缩进的值。    
  

Vim的官方文档给出了4种常用的设置：

```
'tabstop' 'ts'      number  (default 8)
            local to buffer
    Number of spaces that a <Tab> in the file counts for.  Also see
    |:retab| command, and 'softtabstop' option.
 
    Note: Setting 'tabstop' to any other value than 8 can make your file
    appear wrong in many places (e.g., when printing it).
 
    There are four main ways to use tabs in Vim:
    1. Always keep 'tabstop' at 8, set 'softtabstop' and 'shiftwidth' to 4
       (or 3 or whatever you prefer) and use 'noexpandtab'.  Then Vim
       will use a mix of tabs and spaces, but typing <Tab> and <BS> will
       behave like a tab appears every 4 (or 3) characters.
    2. Set 'tabstop' and 'shiftwidth' to whatever you prefer and use
       'expandtab'.  This way you will always insert spaces.  The
       formatting will never be messed up when 'tabstop' is changed.
    3. Set 'tabstop' and 'shiftwidth' to whatever you prefer and use a
       |modeline| to set these values when editing the file again.  Only
       works when using Vim to edit the file.
    4. Always set 'tabstop' and 'shiftwidth' to the same value, and
       'noexpandtab'.  This should then work (for initial indents only)
       for any tabstop setting that people use.  It might be nice to have
       tabs after the first non-blank inserted as spaces if you do this
       though.  Otherwise aligned comments will be wrong when 'tabstop' is
       changed.

```

作为一个 Pythoner ，`\t`和空格混用的应该拉出去烧死。所以我推荐的配置是：

```
set tabstop=4
set shiftwidth=4 " 默认用4个空格
set autoindent  " 自动缩进
set expandtab  " tab键永远输入的是空格
set softtabstop=0  " 关闭softtabstop 永远不要将空格和tab混合输入

```

然后对于下列文件类型，4个空格太宽了，看起来比较累，可以换成2个空格。

```
autocmd FileType coffee,html,css,xml,yaml,json set sw=2 ts=2

```

最近硅谷第五季回归了，不知道大家记得不记得 Hendricks 和女朋友因为tab还是空格吵架的事情，我觉得 Hendricks 是对的啊，如果用`\t`，那么可能不同的IDE对`\t`可以更本地化地对齐一些，但是明显四个SPACE更稳啊，如果混用，到时候你咋看出来空的地方是`\t`哪个地方是SPACE呢。以前碰到很多下载下来代码打开，对齐乱七八糟的情况，简直十恶不赦。


