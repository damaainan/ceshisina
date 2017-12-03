# Vim最好的PHP語法高亮插件

 时间 2017-11-28 15:55:59  牧碼志

原文[http://0x3f.org/post/the-best-php-syntax-file-of-vim/][1]


[StanAngeloff/php.vim][4] 應該是目前最新、最全的PHP語法高亮插件了，它解決了舊版本無法高亮 @throws 的問題。 

默認會把方法注釋全部當做普通注釋顯示，也就是沒有高亮，需要專門做配置：

    function!PhpSyntaxOverride()
        hi! def link phpDocTags  phpDefine
        hi! def link phpDocParam phpType
    endfunction
    
    augroup phpSyntaxOverride
        autocmd!
        autocmd FileType php call PhpSyntaxOverride()
    augroup END


[1]: http://0x3f.org/post/the-best-php-syntax-file-of-vim/

[4]: https://github.com/StanAngeloff/php.vim