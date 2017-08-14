# Vim扫盲: buffer,window,tab 

9月 17, 2015  发布在 [Vim][0]

`Vim` 中的 `window` 和 `tab` 非常具有迷惑性，跟我们平时所说的 “窗口” 和 “标签页” ，是完全不同的两个概念，请看 [vimdoc][1] 给出的定义:

```
A buffer is the in-memory text of a file.

A window is a viewport on a buffer.

A tab page is a collection of windows.
```

简单来说就是:

* `buffer` 可以看做是内存中的文本文件，在没写到磁盘上时，所有的修改都发生在内存中;
* `window` 用来显示 `buffer`，同一个 `buffer` 可以被多个 `window` 显示(一个 window 只能显示一个 `buffer`);
* `tab page` 包含了一系列的 `window`，其实叫 `layout` 更合适，看 [这里][2]

来看 `Vim` 官网上的一幅图:

![Tabs-windows-buffers.png][3]

### 如何选择

目前在 `Vim` 中比较成熟的方案是使用 `buffer` 来模拟我们平时所说的 “标签页”，这样在终端中使用 `Vim` 的时候，也可以获得一致的体验。有很多的插件可以供选择：

* [vim-tabbar-mod][4]
* [vim-bufferline][5]
* [minibufexpl.vim][6]
* [vim-airline][7] (配合 vim-`bufferline` 一起使用)

我比较推荐 [vim-bufferline][8] + [vim-airline][9] 的组合，下面给一张截图供参考:

![vim-airline-bufferline.png][10]

可以在 `~/.vimrc` 中添加如下配置，来使用 `\ + [1-9]` 在 “tab” 中切换:

```
let g:airline#extensions#tabline#buffer_idx_mode = 1

nmap <leader>1 <Plug>AirlineSelectTab1

nmap <leader>2 <Plug>AirlineSelectTab2

nmap <leader>3 <Plug>AirlineSelectTab3

nmap <leader>4 <Plug>AirlineSelectTab4

nmap <leader>5 <Plug>AirlineSelectTab5

nmap <leader>6 <Plug>AirlineSelectTab6

nmap <leader>7 <Plug>AirlineSelectTab7

nmap <leader>8 <Plug>AirlineSelectTab8

nmap <leader>9 <Plug>AirlineSelectTab9
```

### 参考链接

* [Vim Tab Madness. Buffers vs Tabs][11]
* [stackoverflow上关于Vim tab和buffer的回答][2]
* [Buffers, windows, and tabs][12]

[0]: /all-categories/Vim/
[1]: http://vimdoc.sourceforge.net/htmldoc/windows.html#windows-intro
[2]: http://stackoverflow.com/questions/102384/using-vims-tabs-like-buffers/103590#103590
[3]: ../img/Tabs-windows-buffers.png
[4]: https://github.com/NsLib/vim-tabbar-mod
[5]: https://github.com/bling/vim-bufferline
[6]: https://github.com/fholgado/minibufexpl.vim
[7]: https://github.com/bling/vim-airline
[8]: https://github.com/~vim-bufferline
[9]: https://github.com/~/vim-airline
[10]: ../img/vim-airline-bufferline.png
[11]: https://joshldavis.com/2014/04/05/vim-tab-madness-buffers-vs-tabs/
[12]: http://blog.sanctum.geek.nz/buffers-windows-tabs/