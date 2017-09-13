## Vim 常用操作命令整理

**打开关闭**

`vim xxx`，`vim +num xxx` 命令行打开文件  
`vim + filename` 启动跳到文件结尾  
`vim +143 filename` 打开跳到143行 调试代码有用  
`vim +/search-term filename` 跳到第一个匹配  
`vim +/search-term filename` 跳到最后一个匹配  
`vim -t tag`  
`vim —cmd command filename` 加载文件前执行命令  
`vim -c “:50” filename` 加载文件后执行命令  
`:e xxx` vim中打开文件  
`:w[rite]`,`:up[date]`,`:w!`,`up!`，`:wall`保存文件  
`: x`,`:q`,`:qa`,`:q!`,退出文件  
`gf` 普通模式下打开文件名为光标处文字的文件  
`Ctrl+W f` 新窗口打开匹配的文件  
`Ctrl+W fg` 新tab页打开匹配的文件

**代码相关**

`<`,`>`对代码进行缩进  
`<<`,`>>`用于调整源代码缩进格式  
`=` 用户自动格式化代码缩进，gg=G 即为全文格式化  
`zf`,`zo`,`zc`,`za`,`zR`,`zM`对代码进行折叠打开折叠  
`Ctrl+]`,`Ctrl+T`查找函数的定义和返回  
`大写K`，看光标所指标识符的man帮助

**移动光标**

`h`,`j`,`k`,`l` `←↓↑→` 移动光标，相当于四个方向键  
`w`,`b`,`e`,`ge` 以单词的方式移动，可以`nw`，`nb`，`ne`，`nge`的方式移动n个单词的距离  
`W`，`B`，`E`，`gE` 会以连续字符串的方式移动  
`0`,`^`,`g_`  移动到行首，行尾以及后面第n行的行尾  
`gg`,`G`,`nG`，`ngg` 移动到文件头和文件尾，以及移动到文件的第n行，‘可以回到上一次的地方  
`H`,`M`,L n%定位光标到当前屏幕的某个地方  
`{`,`}`,`[[`,`]]`,`(`,`)` 段落，区块，语句导航  
`z回车`，`z-`,`zz`.  当前行置顶，置底，置中  
`Ctrl+G`  显示当前位置信息  
`Ctrl+F`，`Ctrl+B`，`Ctrl+U`,`Ctrl+D`,`Ctrl+E`,`Ctrl+Y`,向前后滚动一屏，半屏，一行  
`Ctrl+I`,`Ctrl+O` 光标移到下一次和上一次的地方，`Ctrl+I` 和Tab功能一样  
`‘`,`”`,`[`,`]` 为跳转前的位置，最后编辑的光标位置，最后修改的开始位置，最后修改的结束位置  
`Ctrl+^` 在两个文件之间轮换  
`‘“`, ’.，`.  上一次光标的地方，上一次修改的地方

**编辑**

`i`,`a`,`o`,`I`,`A`,`O`分别进入插入模式  
`o` 在当前光标所在行下方插入一行，O在当前光标所在行上方插入一行  
`J` 删除换行符，合并两行  
`Ctrl-R` 重做  
`u`，`nu`撤销上一次更改，撤销n次更改  
`U` 撤销整行的更改  
`Ctrl+R` 重做更改  
`Ctrl+L` 重新加载屏幕内容  
`y`,`d`,`p` 表示拷贝，删除，粘贴，配置位置描述使用   
`yw`,`dw`,`y0`,`d0`,`y`,`yfa`,`dfa`,`yy`,`dd`,`D`,`dG`,`dgg`等，前面可以加数字，表示重复如，`3dd`,`3yy`等，也可以加范围，如`4,8yy`  
`%y+.y+`,`N`,`My+` 拷贝指定的数据  
`:[range] g[lobal[!]] /{pattern}/ [cmd]`  
`:[range] v[global[!]] /{pattern}/ [cmd]`  
`n`,`p`,`next`,`previous`,`Ctrl+^`在编辑的文件中切换  
`:ls` 查看打开的文件  
`:e #n` 打开标号为ls结果中的文件  
`:changes` 查看文件变化  
`ga`查看ASCII,十进制，十六进制

**查找**

`/pattern-回车` 在文件内向后查找pattern的匹配，n重复，N回退，n前面可以带数字  
`?pattern-回车` 在文件内向前查找pattern的匹配，n重复，N回退，n前面可以带数字  
`f{char}/t{char}` 在行内查找下一指定字符， `;`重复， `,`回退  
`F{char}/T{char}` 在行内查找上一指定字符， `;`重复， `,`回退  
`*`，`#`高亮所有匹配光标所在单词，相当于输入了`/word`,如果想单独匹配单词`/\<word\>`,则需要`g*`,`g#` ，`gd`提留在非注释段的第一个匹配  
`\<`,`\>`表示匹配单词的开头和结尾  
`.`,`^`,`$`在查找的过程中作为正则策略，如果需要完全匹配需要转义  
`%` 查找匹配的括号`()` `[]` `{}`

**替换**

`.` 重复上次的修改（一定要是修改） 比如说上次删除一个字符，点就是删除一个字符的意思；如果上次是删除一行，点就是删除一行的意思了。  
`>G` 当前行缩进一个单位（Tab）  
`:[range]s[ubstitute]/{pattern}/{string}/[flags]` 将范围内的from 改为to，替换当前行  
`:[range]%s[ubstitute]/{pattern}/{string}/[flags]` 将范围内的from 改为to,替换所有行  
范围可以指定为m,n的数字形式，当个的数字表示特定的行，`.`表示当前行，如果字符串中本身包含`/`,则可用`+`，`=`替换原来的`/`  
`:[range]s[ubstitute]/{pattern}/{string}/[flags] [count]`

**外部&&内部命令替换**

`:shell` 交互式shell  
`:!cmd` 执行cmd并输出结果  
`:!` 执行上一次的命令  
`:r[ead] !cmd`当前光标写入命令结果  
`:上下方向键` 查找命令  
`:[range]co[py] {address}` 复制指定范围的行到指定地址，简写为t 理解为copyto  
`:[range]m[ove] {address}` 移动命令  
:@:重复VIM命令，.重复普通命令  
`Ctrl+D`补全命令  
`:[range] delete [x]`  
`:[range] yank [x]`  
`:[range] put [x]`  
`:[range] copy {address}`  
`:[range] move {address}`  
`:[range] normal {commands}`  
`:[range] join`  
`:[range] substitute/{pattern}/{string}/[flags]`  
`:[range] global/{pattern}/[cmd]`

**选项设置**

`set xxx` 设置某项  
`set noxxx` 取消某项设置  
`set xxx!` 反置某项值  
`set xxx&` 恢复默认值  
`set xxx?` 查询当前状态+  
`setlocal` 局部有效，set全局有效+  
`:set expandtab` 将tab转换为space  
`:set tabstop=4` 制表符宽度 `tabstop=4`或`ts=4`  
`:retab` 按照设定值重新缩进  
`:set shiftwidth=4` 行缩进时宽度  
`:set ai` 自动缩进

**代码格式化**

=全文格式化 gg=G，比如粘贴的内容  
当前行格式化缩进 ==  
当前光标所在行的后N行格式化 N==  
选中行格式化，=
