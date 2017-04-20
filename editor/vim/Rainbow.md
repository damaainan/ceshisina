### 使用Vundle安装:

    Plugin 'luochen1990/rainbow'
    let g:rainbow_active = 1 "0 if you want to enable it later via :RainbowToggle

### 手动安装:

* 首先，将下载到的rainbow.vim文件放到vimfiles/plugin文件夹（在linux系统里是~/.vim/plugin文件夹）中。

* 然后，将以下句子，加入到你的vim配置文件中（windows下配置文件是_vimrc，而linux下是.vimrc） 
```
    let g:rainbow_active = 1 "0 if you want to enable it later via :RainbowToggle
```
* 最后，重新启动你的vim，你就可以享受coding了。

## 高级配置：

以下是一个配置的样例（也是我在用的配置），将它加入到你的vimrc并按照你喜欢的方式修改它（但是保持格式）你就可以精确地控制插件的行为了。

        let g:rainbow_conf = {
        \   'guifgs': ['royalblue3', 'darkorange3', 'seagreen3', 'firebrick'],
        \   'ctermfgs': ['lightblue', 'lightyellow', 'lightcyan', 'lightmagenta'],
        \   'operators': '_,_',
        \   'parentheses': ['start=/(/ end=/)/ fold', 'start=/\[/ end=/\]/ fold', 'start=/{/ end=/}/ fold'],
        \   'separately': {
        \       '*': {},
        \       'tex': {
        \           'parentheses': ['start=/(/ end=/)/', 'start=/\[/ end=/\]/'],
        \       },
        \       'lisp': {
        \           'guifgs': ['royalblue3', 'darkorange3', 'seagreen3', 'firebrick', 'darkorchid3'],
        \       },
        \       'vim': {
        \           'parentheses': ['start=/(/ end=/)/', 'start=/\[/ end=/\]/', 'start=/{/ end=/}/ fold', 'start=/(/ end=/)/ containedin=vimFuncBody', 'start=/\[/ end=/\]/ containedin=vimFuncBody', 'start=/{/ end=/}/ fold containedin=vimFuncBody'],
        \       },
        \       'html': {
        \           'parentheses': ['start=/\v\<((area|base|br|col|embed|hr|img|input|keygen|link|menuitem|meta|param|source|track|wbr)[ >])@!\z([-_:a-zA-Z0-9]+)(\s+[-_:a-zA-Z0-9]+(\=("[^"]*"|'."'".'[^'."'".']*'."'".'|[^ '."'".'"><=`]*))?)*\>/ end=#</\z1># fold'],
        \       },
        \       'css': 0,
        \   }
        \}

* 'operators': describe the operators you want to highlight (note: be careful about special characters which needs escaping, you can find more examples [here][0], and you can also read the [vim help about syn-pattern][1])
* 'guifgs': GUI界面的括号颜色(将按顺序循环使用)
* 'ctermfgs': 终端下的括号颜色(同上,插件将根据环境进行选择)
* 'operators': 描述你希望哪些运算符跟着与它同级的括号一起高亮(注意：留意需要转义的特殊字符，更多样例见[这里][0], 你也可以读[vim帮助 :syn-pattern][1])
* 'parentheses': 描述哪些模式将被当作括号处理,每一组括号由两个vim正则表达式描述
* 'separately': 针对文件类型(由&ft决定)作不同的配置,未被单独设置的文件类型使用*下的配置,值为0表示仅对该类型禁用插件
* 省略某个字段以使用默认设置

[0]: https://github.com/luochen1990/rainbow/issues/3
[1]: http://vimdoc.sourceforge.net/htmldoc/syntax.html#:syn-pattern