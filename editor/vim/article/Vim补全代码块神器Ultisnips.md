# Vim补全代码块神器Ultisnips

 时间 2018-01-22 16:45:54  

原文[https://www.kawabangga.com/posts/2657][1]


爬虫项目中的testcase有很多重复的代码，一般情况都是：mock掉发向服务器的请求 -> 初始化数据库 -> 模拟抓取过程 -> 检查副作用，比如对比存进来的数据等。这些代码不被调用，只是保证之前的代码正常工作，所以我觉得这种“复制、粘贴”编程是可以接受的。而且这种复制粘贴让每一个testcase更直观，抽象度不高。当CI炸了的时候可以很快定位到问题。

以前的做法是复制之前的一个testcase然后修改。这样很不爽。

后来写了一个代码生成器，每次运行命令都自动生成一段代码，然后去修改它。这样的好处是可以在代码生成器做一些事情，比如下载要处理的网页，自动生成运行目标测试的命令等。缺点是每次运行这个生成器都需要一些参数，而且没有和编辑器结合。

所以我觉得解决这个问题最好的办法还是编辑器原生的功能，Vim（一般的代码编辑器都会有这个功能）有Snippet代码片段的功能。自动插入一段代码，然后你可以在placeholder之间跳转修改，非常方便。

![][3]

SirVer/ultisnips 效果

我根据参考[1]的推荐选择了Ultisnips。安装过程如下：

    " Track the engine.
    Plugin 'SirVer/ultisnips'
     
    " Snippets are separated from the engine. Add this if you want them:
    Plugin 'honza/vim-snippets'
     
    " Trigger configuration. Do not use <tab> if you use https://github.com/Valloric/YouCompleteMe.
    let g:UltiSnipsExpandTrigger="<tab>"
    let g:UltiSnipsJumpForwardTrigger="<c-b>"
    let g:UltiSnipsJumpBackwardTrigger="<c-z>"
     
    " If you want :UltiSnipsEdit to split your window.
    let g:UltiSnipsEditSplit="vertical"
    

这是 [Github上官方的安装][4] 。我没有安装vim-snippets，这个插件是一些常用的片段，由网友贡献的。大体看了一下 [Python][5] 的，大都比较实用。不过我觉得用这些玩意儿很容易把Python写的跟Java似的，所以没弄这些。我只要自定义的想补全的片段就够了，其余的可以手写，毕竟写了这么长时间Python了没用这个也没感觉不适。 

第三部分是对快捷键的设置。这三个let分别对应：触发展开片段的键、跳到下一个占位符的键，跳到上一个占位符的键。由于tab和YCM补全插件冲突，以及我认为Linux所有的编辑器和命令行都默认F是向前，B是向后的默契，所以 [我的设置][6] 这样的： 

    let g:UltiSnipsExpandTrigger="<c-j>"
    let g:UltiSnipsJumpForwardTrigger="<c-f>"
    let g:UltiSnipsJumpBackwardTrigger="<c-b>"
    

读取Ultisnips的路径是 ~/.vim/UltiSnips 。但是也可以自定义。 

写UltiSnips很简单，可以参考vim-snippets中的一个：

    snippet with "with" b
    with ${1:expr}`!p snip.rv = " as " if t[2] else ""`${2:var}:
     ${3:${VISUAL:pass}}
    $0
    endsnippet
    

可以看到，大致的语法是：

    snippet <name>
    <body>
    endsnippet
    

这样在编辑器中打出 <name> 的时候，可选的snippets就会展开，然后用 <tab> 选择到该名字上（ <shift> + <tab> 可以向上选择），按你定义的“触发”快捷键就可以将<body>替换到编辑器中。 

其中，placeholder在<body>中可以这样规定： ${2:var} 。数字用来标志placeholder的id，用于跳转。另外很好用的一点多光标编辑：如果在一个 $2上面编辑，那么会改动所有的$2。 

更高级的功能可以仔细阅读 [:help][7]。 对了，在编辑器外也要经常用一些片段，比如mongo查询等，推荐一个剪切板管理工具： [Clipmenu][8] ，可以将常用的Snippet放到里面，随时取出来粘贴。 

参考：

1. [http://mednoter.com/UltiSnips.html][9]

[1]: https://www.kawabangga.com/posts/2657
[3]: ../img/VVZZz2R.gif
[4]: https://github.com/SirVer/ultisnips#quick-start
[5]: https://github.com/honza/vim-snippets/blob/master/UltiSnips/python.snippets
[6]: https://github.com/laixintao/myrc/blob/master/.vimrc#L173
[7]: https://github.com/SirVer/ultisnips/blob/master/doc/UltiSnips.txt
[8]: http://www.clipmenu.com/
[9]: http://mednoter.com/UltiSnips.html