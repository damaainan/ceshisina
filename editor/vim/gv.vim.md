# 用gv.vim查看git提交歷史

 时间 2017-10-18 13:28:16  牧碼志

原文[http://0x3f.org/post/gv-vim/][1]


[gv.vim][5] 是fugitive的插件，用於查看git提交歷史，特點是速度快、好用。我现在用它做code review。 

    nnoremap <leader>gll :GV --no-merges<CR>
    nnoremap <leader>glc :GV!<CR>
    nnoremap <leader>gla :GV --no-merges --author<space>
    nnoremap <leader>glg :GV --no-merges --grep<space>

#### 另一个 插件 [https://github.com/gregsexton/gitv](https://github.com/gregsexton/gitv)

[1]: http://0x3f.org/post/gv-vim/

[5]: https://github.com/junegunn/gv.vim