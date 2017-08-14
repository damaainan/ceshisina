## [vim切换tab标签快捷键][0]

 作者: JavasBoy  分类: [Vim][1]  发布时间: 2012-08-03 12:43  

这几天在学习VIM，在WIN7下装了GVIM，在折腾好配置文件后，就在弄这个  
切换标签快捷键的问题。  
vim从vim7开始加入了多标签切换的功能，相当于多窗口。  
之前的版本虽然也有多文件编辑功能，但是总之不如这个方便啦。  
用法

```
:tabnew [++opt选项] ［＋cmd］ 文件            建立对指定文件新的tab
:tabc       关闭当前的tab
:tabo       关闭所有其他的tab
:tabs       查看所有打开的tab
:tabp      前一个
:tabn      后一个
标准模式下：
gt , gT 可以直接在tab之间切换。
更多可以查看帮助 :help table ， help -p
```

使用`alt+数字键`来切换tab (vim7+)

不过用`gt`,`gT`来一个个切换有点不方便, 如果用`:tabnext {count}`, 又按键太多. 加入以下代码后, 可以用 `alt+n`来切换,  
比如`alt+1`切换到第一个tab,`alt+2`切换到第二个tab。

把以下代码加到vimrc, 或者存为`.vim`文件,再放到`plugin`目。

```
function! TabPos_ActivateBuffer(num)
    let s:count = a:num
    exe "tabfirst"
    exe "tabnext" s:count
endfunction
 
function! TabPos_Initialize()
for i in range(1, 9)
        exe "map <M-" . i . "> :call TabPos_ActivateBuffer(" . i . ")<CR>"
    endfor
    exe "map <M-0> :call TabPos_ActivateBuffer(10)<CR>"
endfunction
 
autocmd VimEnter * call TabPos_Initialize()

```

上面的看上去太复杂了，来个简单的。

```
:nn <M-1> 1gt
:nn <M-2> 2gt
:nn <M-3> 3gt
:nn <M-4> 4gt
:nn <M-5> 5gt
:nn <M-6> 6gt
:nn <M-7> 7gt
:nn <M-8> 8gt
:nn <M-9> 9gt
```
把这个放进`_vimrc`配置文件里。

[0]: https://www.liurongxing.com/vim-tab-shortcut.html
[1]: https://www.liurongxing.com/category/linux/vim
