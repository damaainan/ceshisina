## [ Linux学习笔记<Sed最全整理>](http://blog.csdn.net/u012759878/article/details/48908989)

> 本文分为四部分，前两部分都是比较基础的用法。   
> 如果你对Sed感兴趣的话，可以去尝试读一下后面几个章节。   
> 你完全可以根据你的水平去选择其中的某一个章节阅读。

<font face=微软雅黑>

## 目录

* * [目录][0]
* [初级入门][1]
    * [主要应用场景][2]
    * [删除][3]
    * [查找替换][4]
    * [字符转换][5]
    * [插入文本][6]

* [鸟哥私房菜][7]
* [Sed到底是如何工作的][8]
* [高级用法][9]
    * [高级应用实例][10]
    * [编号][11]
    * [文本转换和替代][12]
    * [选择性地显示特定行][13]
    * [选择性地删除特定行][14]
    * [特殊应用][15]

* [BSD版本Sed的文档][16]

- - -

# 初级入门

sed工具是一种非交互式的流编辑器。默认情况下只会影响输出，不会改变输入。sed处理文档时是以行为单位的。功能有：删除、查找替换、添加、插入、从其他文件读取。

其实这些功能看起来都可以用vim等编辑器来实现。那么，为什么要有sed呢?

## **主要应用场景**

* 太过庞大的文本
* 有规律的文本修改

sed的命令格式

     sed [option] command [file ...]


## **删除**

```shell
    #删除第一行
    sed '1d' file
```


**注意，这只影响到输出流。如果想保存的话**

```shell
    sed -i '1d' filename
```

或者

```shell
    #输出到新文件
    sed '1d'>newfilename
```


**其中1d命令中，我们把1称为地址，这里指代的是第一行。**  
**删除第一行到最后一行**

```shell
    sed '1,$d' filename
```


**删除包含了某个pattern的行**

```shell
    sed '/pattern/d' filename
    #例如
    sed '/^$/d' filename
```


- - -

## **查找替换**

```shell
    #普通替换 将每行的第一个line替换成LINE
    sed 's/line/LINE/' filename
```


> sed ‘s/line/LINE/[number]   
> 表示对这一行来说至多替换number个line，如果number为g，则全部替换

- - -

## **字符转换**

    现在还没有见过重要的用法



- - -

## **插入文本**

```shell
    #在第二行前插入一行
    sed '2 i insert_context' filename
    #在第二行之后插入一行
    sed '2 a insert_context' filename
    #在匹配的行之前插入一行
    sed '/pattern/i\new_word' filename
```


**打印**

```shell
    #只打印出第一行 ，不加n的话会默认输出每一行
    sed -n '1p' filename
    #只打印出被修改的一行
    sed -n 's/the/THE/p' filename
```


- - -

# 鸟哥私房菜

> sed 是一种在线编辑器，它一次处理一行内容。处理时，把当前处理的行存储在临时缓冲区中，称为“模式空间”（pattern space），接着用sed命令处理缓冲区中的内容，处理完成后，把缓冲区的内容送往屏幕。接着处理下一行，这样不断重复，直到文件末尾。文件内容并没有 改变，除非你使用重定向存储输出。Sed主要用来自动编辑一个或多个文件；简化对文件的反复操作；编写转换程序等。

- - -

> [root@www ~]# sed [-nefr] [动作]   
> 选项与参数：   
> -n ：使用安静(silent)模式。在一般 sed 的用法中，所有来自 STDIN 的数据一般都会被列出到终端上。但如果加上 -n 参数后，则只有经过sed 特殊处理的那一行(或者动作)才会被列出来。   
> -e ：直接在命令列模式上进行 sed 的动作编辑；   
> -f ：直接将 sed 的动作写在一个文件内， -f filename 则可以运行 filename 内的 sed 动作；   
> -r ：sed 的动作支持的是延伸型正规表示法的语法。(默认是基础正规表示法语法)   
> -i ：直接修改读取的文件内容，而不是输出到终端。

> 动作说明： [n1[,n2]]function   
> n1, n2 ：不见得会存在，一般代表『选择进行动作的行数』，举例来说，如果我的动作是需要在 10 到 20 行之间进行的，则『 10,20[动作行为] 』

> function：   
> a ：新增， a 的后面可以接字串，而这些字串会在新的一行出现(目前的下一行)～   
> c ：取代， c 的后面可以接字串，这些字串可以取代 n1,n2 之间的行！   
> d ：删除，因为是删除啊，所以 d 后面通常不接任何咚咚；   
> i ：插入， i 的后面可以接字串，而这些字串会在新的一行出现(目前的上一行)；   
> p ：列印，亦即将某个选择的数据印出。通常 p 会与参数 sed -n 一起运行～   
> s ：取代，可以直接进行取代的工作哩！通常这个 s 的动作可以搭配正规表示法！例如 1,20s/old/new/g 就是啦！   
> sed使用参数


```shell
    #以行为单位的新增/删除
    
    #将 /etc/passwd 的内容列出并且列印行号，同时，请将第 2~5 行删除！
    [root@www ~]# nl /etc/passwd | sed '2,5d'
    1 root:x:0:0:root:/root:/bin/bash
    6 sync:x:5:0:sync:/sbin:/bin/sync
    7 shutdown:x:6:0:shutdown:/sbin:/sbin/shutdown
    .....(后面省略).....
```

```shell
    #只要删除第 2 行
    nl /etc/passwd | sed '2d' 
```

```shell
    #要删除第 3 到最后一行
    nl /etc/passwd | sed '3,$d'
```

```shell
    #在第二行后(亦即是加在第三行)加上『drink tea?』字样！
    nl /etc/passwd | sed '2 a drink tea' 
```

```shell
    #如果是要增加两行以上，在第二行后面加入两行字，例如『Drink tea or .....』与『drink beer?』
    nl /etc/passwd | sed '2 a drind tea or\
    >drink beer?'
```

```shell
    #将第2-5行的内容取代成为『No 2-5 number』呢？
    nl /etc/passwd|sed '2,5c No 2-5 number'
```

```shell
    #数据的搜寻并删除
    #删除/etc/passwd所有包含root的行，其他行输出
    nl /etc/passwd | sed -n '/root/d'
```


**重点，命令的拼接**

```shell
    #数据的搜寻并执行命令
    #找到匹配模式eastern的行后，
    
    #搜索/etc/passwd,找到root对应的行，执行后面花括号中的一组命令，每个命令之间用分号分隔，这里把bash替换为blueshell，再输出这行：
    nl /etc/passwd | sed '/root/{s/bash/blueshell;p}' 
```


**多点编辑**

```shell
    #多点编辑
    #一条sed命令，删除/etc/passwd第三行到末尾的数据，并把bash替换为#blueshell
    nl /etc/passwd | sed -e '3,$d' -e 's/bash/blueshell'
```


**直接修改文件内容(危险动作)**

sed 可以直接修改文件的内容，不必使用管道命令或数据流重导向！ 不过，由於这个动作会直接修改到原始的文件，所以请你千万不要随便拿系统配置来测试！ 我们还是使用下载的 regular_express.txt 文件来测试看看吧！

利用 sed 将 regular_express.txt 内每一行结尾若为 . 则换成 !

```shell
    #注意，由于.和!都是正则表达符，所以需要转义
    sed -i 's/\.$/\!/g regular_express.txt'
```


**利用 sed 直接在 regular_express.txt 最后一行加入『# This is a test』**

    sed -i '$i #this is a test' regular_express.txt



- - -

# Sed到底是如何工作的？

这里有一个比较不错的[文章][17]，可以看到Linux和Unix版本之间的区别以及简略的用法。   
如果没有耐心看看上面的内容是完全足够了的。

> 本文作者的水平实在平平。只能试着翻译一下文档。但是如果没有办法读原始文档，那么我就只能永远看翻译过来的不知所云的文章。我认为这一步是必须的。

可能文档也并不足够，有些概念没有解释清楚。先看看这段话再读文档好了：）

    How `sed' Works
    ===================
    
    `sed' maintains two data buffers: the active _pattern_ space, and the
    auxiliary _hold_ space. Both are initially empty.
    
       `sed' operates by performing the following cycle on each lines of input: first, `sed' reads one line from the input stream, removes any trailing newline, and places it in the pattern space.  Then commands are executed; each command can have an address associated to it:addresses are a kind of condition code, and a command is only executed if the condition is verified before the command is to be executed.
    
       When the end of the script is reached, unless the `-n' option is in use, the contents of pattern space are printed out to the output stream, adding back the trailing newline if it was removed.(1) Then the next cycle starts for the next input line.
    
       Unless special commands (like `D') are used, the pattern space is deleted between two cycles. The hold space, on the other hand, keeps its data between cycles (see commands `h', `H', `x', `g', `G' to move
    data between both buffers).
    
       ---------- Footnotes ----------
    
       (1) Actually,   if `sed' prints a line without the terminating newline, it will   nevertheless print the missing newline as soon as more text is sent to   the same output stream, which gives the "least expected surprise"   even though it does not make commands like `sed -n p' exactly   identical to `cat'.



简单地说就是：

    先读入一行,去掉尾部换行,把处理过的这行存入pattern space这个变量,再当空间里的某些地址存在时,执行命令
    这两步执行完毕,把现在的pattern space打印出来,在后边打印曾去掉的换行
    把pattern space内容给hold space,把pattern space置空
    读下一行



有了pattern space和hold space的基础，读下面这篇文档就会轻松很多。

# 高级用法

如果你没有看上面的文档，那么看看这篇[《【转】sed命令n，N，d，D，p，P，h，H，g，G，x解析》][18]

    1、
    
    sed执行模板=sed '模式{命令1;命令2}'
    
    即逐行读入模式空间，执行命令，最后输出打印出来
    
    2、
    
    为方便下面，先说下p和P，p打印当前模式空间内容，追加到默认输出之后，P打印当前模式空间开端至\n的内容，并追加到默认输出之前。
    
    sed并不对每行末尾\n进行处理，但是对N命令追加的行间\n进行处理，因为此时sed将两行看做一行。
    
    2-1、n命令
    
    n命令简单来说就是提前读取下一行，覆盖模型空间前一行（并没有删除，因此依然打印至标准输出），如果命令未执行成功（并非跳过：前端条件不匹配），则放弃之后的任何命令，并对新读取的内容，重头执行sed。
    
    2-2、N命令
    
    N命令简单来说就是追加下一行到模式空间，同时将两行看做一行，但是两行之间依然含有\n换行符，如果命令未执行成功（并非跳过：前端条件不匹配），则放弃之后任何命令，并对新读取的内容，重头执行sed。
    
    2-3、d命令
    
    d命令是删除当前模式空间内容（不在传至标准输出），并放弃之后的命令，并对新读取的内容，重头执行sed。
    
    2-4、D命令
    
    D命令是删除当前模式空间开端至\n的内容（不在传至标准输出），放弃之后的命令，但是对剩余模式空间重新执行sed。
    
    2-5、y命令
    
    y命令的作用在于字符转换
    
    2-6、h命令，H命令，g命令，G命令
    
    h命令是将当前模式空间中内容覆盖至保持空间，H命令是将当前模式空间中的内容追加至保持空间
    
    g命令是将当前保持空间中内容覆盖至模式空间，G命令是将当前保持空间中的内容追加至模式空间
    
    2-7、x命令
    
    x命令是将当前保持空间和模式空间内容互换



## **高级应用实例**


```shell
         # 在每一行后面增加一空行
         # 因为holdspace一直为空，所以G命令只是在加空行罢了
         sed G
    
         # 将原来的所有空行删除并在每一行后面增加一空行。
         # 这样在输出的文本中每一行后面将有且只有一空行。
         sed '/^$/d;G'
    
         # 在每一行后面增加两行空行
         sed 'G;G'
    
         # 将第一个脚本所产生的所有空行删除（即删除所有偶数行）
         sed 'n;d'
    
         # 在匹配式样“regex”的行之前插入一空行
         sed '/regex/{x;p;x;}'
    
         # 在匹配式样“regex”的行之后插入一空行
         sed '/regex/G'
    
         # 在匹配式样“regex”的行之前和之后各插入一空行
         sed '/regex/{x;p;x;G;}'
```


- - -

## **编号**：

> N   
> Append the next line of input to the pattern space, using an embedded newline character to separate the appended material from the original contents. Note that the current line number changes.


```shell
         # 为文件中的每一行进行编号（简单的左对齐方式）。这里使用了“制表符”
         # （tab，见本文末尾关于'\t'的用法的描述）而不是空格来对齐边缘。
         sed = filename | sed 'N;s/\n/\t/'
         # 对文件中的所有行编号（行号在左，文字右端对齐）。
         sed = filename | sed 'N; s/^/ /; s/ *\(.\{6,\}\)\n/\1 /'
         # 对文件中的所有行编号，但只显示非空白行的行号。
         sed '/./=' filename | sed '/./N; s/\n/ /'
         # 计算行数 （模拟 "wc -l"）
         sed -n '$='
```


## 文本转换和替代：

```shell
        --------
         # Unix环境：转换DOS的新行符（CR/LF）为Unix格式。
         sed 's/.$//' # 假设所有行以CR/LF结束
         sed 's/^M$//' # 在bash/tcsh中，将按Ctrl-M改为按Ctrl-V
         sed 's/\x0D$//' # ssed、gsed 3.02.80，及更高版本
         # Unix环境：转换Unix的新行符（LF）为DOS格式。
         sed "s/$/`echo -e \\\r`/" # 在ksh下所使用的命令
         sed 's/$'"/`echo \\\r`/" # 在bash下所使用的命令
         sed "s/$/`echo \\\r`/" # 在zsh下所使用的命令
         sed 's/$/\r/' # gsed 3.02.80 及更高版本
         # DOS环境：转换Unix新行符（LF）为DOS格式。
         sed "s/$//" # 方法 1
         sed -n p # 方法 2
         # DOS环境：转换DOS新行符（CR/LF）为Unix格式。
         # 下面的脚本只对UnxUtils sed 4.0.7 及更高版本有效。要识别UnxUtils版本的
         # sed可以通过其特有的“--text”选项。你可以使用帮助选项（“--help”）看
         # 其中有无一个“--text”项以此来判断所使用的是否是UnxUtils版本。其它DOS
         # 版本的的sed则无法进行这一转换。但可以用“tr”来实现这一转换。
         sed "s/\r//" infile >outfile # UnxUtils sed v4.0.7 或更高版本
         tr -d \r infile >outfile # GNU tr 1.22 或更高版本
         # 将每一行前导的“空白字符”（空格，制表符）删除
         # 使之左对齐
         sed 's/^[ \t]*//' # 见本文末尾关于'\t'用法的描述
         # 将每一行拖尾的“空白字符”（空格，制表符）删除
         sed 's/[ \t]*$//' # 见本文末尾关于'\t'用法的描述
         # 将每一行中的前导和拖尾的空白字符删除
         sed 's/^[ \t]*//;s/[ \t]*$//'
         # 在每一行开头处插入5个空格（使全文向右移动5个字符的位置）
         sed 's/^/ /'
         # 以79个字符为宽度，将所有文本右对齐
         sed -e :a -e 's/^.\{1,78\}$/ &/;ta' # 78个字符外加最后的一个空格
         # 以79个字符为宽度，使所有文本居中。在方法1中，为了让文本居中每一行的前
         # 头和后头都填充了空格。 在方法2中，在居中文本的过程中只在文本的前面填充
         # 空格，并且最终这些空格将有一半会被删除。此外每一行的后头并未填充空格。
         sed -e :a -e 's/^.\{1,77\}$/ & /;ta' # 方法1
         sed -e :a -e 's/^.\{1,77\}$/ &/;ta' -e 's/\( *\)\1/\1/' # 方法2
         # 在每一行中查找字串“foo”，并将找到的“foo”替换为“bar”
         sed 's/foo/bar/' # 只替换每一行中的第一个“foo”字串
         sed 's/foo/bar/4' # 只替换每一行中的第四个“foo”字串
         sed 's/foo/bar/g' # 将每一行中的所有“foo”都换成“bar”
         sed 's/\(.*\)foo\(.*foo\)/\1bar\2/' # 替换倒数第二个“foo”
         sed 's/\(.*\)foo/\1bar/' # 替换最后一个“foo”
         # 只在行中出现字串“baz”的情况下将“foo”替换成“bar”
         sed '/baz/s/foo/bar/g'
         # 将“foo”替换成“bar”，并且只在行中未出现字串“baz”的情况下替换
         sed '/baz/!s/foo/bar/g'
         # 不管是“scarlet”“ruby”还是“puce”，一律换成“red”
         sed 's/scarlet/red/g;s/ruby/red/g;s/puce/red/g' #对多数的sed都有效
         gsed 's/scarlet\|ruby\|puce/red/g' # 只对GNU sed有效
         # 倒置所有行，第一行成为最后一行，依次类推（模拟“tac”）。
         # 由于某些原因，使用下面命令时HHsed v1.5会将文件中的空行删除
         sed '1!G;h;$!d' # 方法1
         sed -n '1!G;h;$p' # 方法2
         # 将行中的字符逆序排列，第一个字成为最后一字，……（模拟“rev”）
         sed '/\n/!G;s/\(.\)\(.*\n\)/&\2\1/;//D;s/.//'
         # 将每两行连接成一行（类似“paste”）
         sed '$!N;s/\n/ /'
         # 如果当前行以反斜杠“\”结束，则将下一行并到当前行末尾
         # 并去掉原来行尾的反斜杠
         sed -e :a -e '/\\$/N; s/\\\n//; ta'
         # 如果当前行以等号开头，将当前行并到上一行末尾
         # 并以单个空格代替原来行头的“=”
         sed -e :a -e '$!N;s/\n=/ /;ta' -e 'P;D'
         # 为数字字串增加逗号分隔符号，将“1234567”改为“1,234,567”
         gsed ':a;s/\B[0-9]\{3\}\>/,&/;ta' # GNU sed
         sed -e :a -e 's/\(.*[0-9]\)\([0-9]\{3\}\)/\1,\2/;ta' # 其他sed
         # 为带有小数点和负号的数值增加逗号分隔符（GNU sed）
         gsed -r ':a;s/(^|[^0-9.])([0-9]+)([0-9]{3})/\1\2,\3/g;ta'
         # 在每5行后增加一空白行 （在第5，10，15，20，等行后增加一空白行）
         gsed '0~5G' # 只对GNU sed有效
         sed 'n;n;n;n;G;' # 其他sed
```


## **选择性地显示特定行**

> q命令   
> 退出循环

```shell
        --------
         # 显示文件中的前10行 （模拟“head”的行为）
         sed 10q
         # 显示文件中的第一行 （模拟“head -1”命令）
         sed q
         # 显示文件中的最后10行 （模拟“tail”）
         sed -e :a -e '$q;N;11,$D;ba'
         # 显示文件中的最后2行（模拟“tail -2”命令）
         sed '$!N;$!D'
         # 显示文件中的最后一行（模拟“tail -1”）
         sed '$!d' # 方法1
         sed -n '$p' # 方法2
         # 显示文件中的倒数第二行
         sed -e '$!{h;d;}' -e x # 当文件中只有一行时，输入空行
         sed -e '1{$q;}' -e '$!{h;d;}' -e x # 当文件中只有一行时，显示该行
         sed -e '1{$d;}' -e '$!{h;d;}' -e x # 当文件中只有一行时，不输出
         # 只显示匹配正则表达式的行（模拟“grep”）
         sed -n '/regexp/p' # 方法1
         sed '/regexp/!d' # 方法2
         # 只显示“不”匹配正则表达式的行（模拟“grep -v”）
         sed -n '/regexp/!p' # 方法1，与前面的命令相对应
         sed '/regexp/d' # 方法2，类似的语法
         # 查找“regexp”并将匹配行的上一行显示出来，但并不显示匹配行
         sed -n '/regexp/{g;1!p;};h'
         # 查找“regexp”并将匹配行的下一行显示出来，但并不显示匹配行
         sed -n '/regexp/{n;p;}'
         # 显示包含“regexp”的行及其前后行，并在第一行之前加上“regexp”所
         # 在行的行号 （类似“grep -A1 -B1”）
         sed -n -e '/regexp/{=;x;1!p;g;$!N;p;D;}' -e h
         # 显示包含“AAA”、“BBB”或“CCC”的行（任意次序）
         sed '/AAA/!d; /BBB/!d; /CCC/!d' # 字串的次序不影响结果
         # 显示包含“AAA”、“BBB”和“CCC”的行（固定次序）
         sed '/AAA.*BBB.*CCC/!d'
         # 显示包含“AAA”“BBB”或“CCC”的行 （模拟“egrep”）
         sed -e '/AAA/b' -e '/BBB/b' -e '/CCC/b' -e d # 多数sed
         gsed '/AAA\|BBB\|CCC/!d' # 对GNU sed有效
         # 显示包含“AAA”的段落 （段落间以空行分隔）
         # HHsed v1.5 必须在“x;”后加入“G;”，接下来的3个脚本都是这样
         sed -e '/./{H;$!d;}' -e 'x;/AAA/!d;'
         # 显示包含“AAA”“BBB”和“CCC”三个字串的段落 （任意次序）
         sed -e '/./{H;$!d;}' -e 'x;/AAA/!d;/BBB/!d;/CCC/!d'
         # 显示包含“AAA”、“BBB”、“CCC”三者中任一字串的段落 （任意次序）
         sed -e '/./{H;$!d;}' -e 'x;/AAA/b' -e '/BBB/b' -e '/CCC/b' -e d
         gsed '/./{H;$!d;};x;/AAA\|BBB\|CCC/b;d' # 只对GNU sed有效
         # 显示包含65个或以上字符的行
         sed -n '/^.\{65\}/p'
         # 显示包含65个以下字符的行
         sed -n '/^.\{65\}/!p' # 方法1，与上面的脚本相对应
         sed '/^.\{65\}/d' # 方法2，更简便一点的方法
         # 显示部分文本——从包含正则表达式的行开始到最后一行结束
         sed -n '/regexp/,$p'
         # 显示部分文本——指定行号范围（从第8至第12行，含8和12行）
         sed -n '8,12p' # 方法1
         sed '8,12!d' # 方法2
         # 显示第52行
         sed -n '52p' # 方法1
         sed '52!d' # 方法2
         sed '52q;d' # 方法3, 处理大文件时更有效率
         # 从第3行开始，每7行显示一次
         gsed -n '3~7p' # 只对GNU sed有效
         sed -n '3,${p;n;n;n;n;n;n;}' # 其他sed
         # 显示两个正则表达式之间的文本（包含）
         sed -n '/Iowa/,/Montana/p' # 区分大小写方式
```


## **选择性地删除特定行**

```shell
        --------
         # 显示通篇文档，除了两个正则表达式之间的内容
         sed '/Iowa/,/Montana/d'
         # 删除文件中相邻的重复行（模拟“uniq”）
         # 只保留重复行中的第一行，其他行删除
         sed '$!N; /^\(.*\)\n\1$/!P; D'
         # 删除文件中的重复行，不管有无相邻。注意hold space所能支持的缓存
         # 大小，或者使用GNU sed。
         sed -n 'G; s/\n/&&/; /^\([ -~]*\n\).*\n\1/d; s/\n//; h; P'
         # 删除除重复行外的所有行（模拟“uniq -d”）
         sed '$!N; s/^\(.*\)\n\1$/\1/; t; D'
         # 删除文件中开头的10行
         sed '1,10d'
         # 删除文件中的最后一行
         sed '$d'
         # 删除文件中的最后两行
         sed 'N;$!P;$!D;$d'
         # 删除文件中的最后10行
         sed -e :a -e '$d;N;2,10ba' -e 'P;D' # 方法1
         sed -n -e :a -e '1,10!{P;N;D;};N;ba' # 方法2
         # 删除8的倍数行
         gsed '0~8d' # 只对GNU sed有效
         sed 'n;n;n;n;n;n;n;d;' # 其他sed
         # 删除匹配式样的行
         sed '/pattern/d' # 删除含pattern的行。当然pattern
         # 可以换成任何有效的正则表达式
         # 删除文件中的所有空行（与“grep '.' ”效果相同）
         sed '/^$/d' # 方法1
         sed '/./!d' # 方法2
         # 只保留多个相邻空行的第一行。并且删除文件顶部和尾部的空行。
         # （模拟“cat -s”）
         sed '/./,/^$/!d' #方法1，删除文件顶部的空行，允许尾部保留一空行
         sed '/^$/N;/\n$/D' #方法2，允许顶部保留一空行，尾部不留空行
         # 只保留多个相邻空行的前两行。
         sed '/^$/N;/\n$/N;//D'
         # 删除文件顶部的所有空行
         sed '/./,$!d'
         # 删除文件尾部的所有空行
         sed -e :a -e '/^\n*$/{$d;N;ba' -e '}' # 对所有sed有效
         sed -e :a -e '/^\n*$/N;/\n$/ba' # 同上，但只对 gsed 3.02.*有效
         # 删除每个段落的最后一行
         sed -n '/^$/{p;h;};/./{x;/./p;}'
```


## 特殊应用

```shell
        --------
         # 移除手册页（man page）中的nroff标记。在Unix System V或bash shell下使
         # 用'echo'命令时可能需要加上 -e 选项。
         sed "s/.`echo \\\b`//g" # 外层的双括号是必须的（Unix环境）
         sed 's/.^H//g' # 在bash或tcsh中, 按 Ctrl-V 再按 Ctrl-H
         sed 's/.\x08//g' # sed 1.5，GNU sed，ssed所使用的十六进制的表示方法
         # 提取新闻组或 e-mail 的邮件头
         sed '/^$/q' # 删除第一行空行后的所有内容
         # 提取新闻组或 e-mail 的正文部分
         sed '1,/^$/d' # 删除第一行空行之前的所有内容
         # 从邮件头提取“Subject”（标题栏字段），并移除开头的“Subject:”字样
         sed '/^Subject: */!d; s///;q'
         # 从邮件头获得回复地址
         sed '/^Reply-To:/q; /^From:/h; /./d;g;q'
         # 获取邮件地址。在上一个脚本所产生的那一行邮件头的基础上进一步的将非电邮
         # 地址的部分剃除。（见上一脚本）
         sed 's/ *(.*)//; s/>.*//; s/.*[:'
         # 在每一行开头加上一个尖括号和空格（引用信息）
         sed 's/^/> /'
         # 将每一行开头处的尖括号和空格删除（解除引用）
         sed 's/^> //'
         # 移除大部分的HTML标签（包括跨行标签）
         sed -e :a -e 's/]*>//g;/'
         # 将分成多卷的uuencode文件解码。移除文件头信息，只保留uuencode编码部分。
         # 文件必须以特定顺序传给sed。下面第一种版本的脚本可以直接在命令行下输入；
         # 第二种版本则可以放入一个带执行权限的shell脚本中。（由Rahul Dhesi的一
         # 个脚本修改而来。）
         sed '/^end/,/^begin/d' file1 file2 ... fileX | uudecode # vers. 1
         sed '/^end/,/^begin/d' "$@" | uudecode # vers. 2
         # 将文件中的段落以字母顺序排序。段落间以（一行或多行）空行分隔。GNU sed使用
         # 字元“\v”来表示垂直制表符，这里用它来作为换行符的占位符——当然你也可以
         # 用其他未在文件中使用的字符来代替它。
         sed '/./{H;d;};x;s/\n/={NL}=/g' file | sort | sed '1s/={NL}=//;s/={NL}=/\n/g'
         gsed '/./{H;d};x;y/\n/\v/' file | sort | sed '1s/\v//;y/\v/\n/'
         # 分别压缩每个.TXT文件，压缩后删除原来的文件并将压缩后的.ZIP文件
         # 命名为与原来相同的名字（只是扩展名不同）。（DOS环境：“dir /b”
         # 显示不带路径的文件名）。
         echo @echo off >zipup.bat
         dir /b *.txt | sed "s/^\(.*\)\.TXT/pkzip -mo \1 \1.TXT/" >>zipup.bat
        使用SED：Sed接受一个或多个编辑命令，并且每读入一行后就依次应用这些命令。
        当读入第一行输入后，sed对其应用所有的命令，然后将结果输出。接着再读入第二
        行输入，对其应用所有的命令……并重复这个过程。上一个例子中sed由标准输入设
        备（即命令解释器，通常是以管道输入的形式）获得输入。在命令行给出一个或多
        个文件名作为参数时，这些文件取代标准输入设备成为sed的输入。sed的输出将被
        送到标准输出（显示器）。因此：
         cat filename | sed '10q' # 使用管道输入
         sed '10q' filename # 同样效果，但不使用管道输入
         sed '10q' filename > newfile # 将输出转移（重定向）到磁盘上
```


    要了解sed命令的使用说明，包括如何通过脚本文件（而非从命令行）来使用这些命
    令，请参阅《sed & awk》第二版，作者Dale Dougherty和Arnold Robbins
    （O'Reilly，1997；http://www.ora.com），《UNIX Text Processing》，作者 Dale Dougherty和Tim O'Reilly（Hayden Books，1987）或者是Mike Arst写的教程——压缩包的名称是“U-SEDIT2.ZIP”（在许多站点上都找得到）。要发掘sed的潜力，则必须对“正则表达式”有足够的理解。正则表达式的资料可以看《Mastering Regular Expressions》作者Jeffrey Friedl（O'reilly 1997）。
    Unix系统所提供的手册页（“man”）也会有所帮助（试一下这些命令
    “man sed”、“man regexp”，或者看“man ed”中关于正则表达式的部分），但手册提供的信息比较“抽象”——这也是它一直为人所诟病的。不过，它本来就不 是用来教初学者如何使用sed或正则表达式的教材，而只是为那些熟悉这些工具的人提供的一些文本参考。
    括号语法：前面的例子对sed命令基本上都使用单引号（'...'）而非双引号（"..."）这是因为sed通常是在Unix平台上使用。单引号下，Unix的shell（命令解释器）不会对美元符（$）和后引号（`...`）进行解释和执行。而在双引号下美元符会被展开为变量或参数的值，后引号中的命令被执行并以输出的结果代替后引号中的内容。而在“csh”及其衍生的shell中使用感叹号（!）时需要在其前面加上转义用的反斜杠（就像这样：\!）以保证上面所使用的例子能正常运行（包括使用单引号的情况下）。DOS版本的Sed则一律使用双引号（"..."）而不是引号来圈起命令。
    '\t'的用法：为了使本文保持行文简洁，我们在脚本中使用'\t'来表示一个制表符。但是现在大部分版本的sed还不能识别'\t'的简写方式，因此当在命令行中为脚本输入制表符时，你应该直接按TAB键来输入制表符而不是输入'\t'。下列的工具软件都支持'\t'做为一个正则表达式的字元来表示制表符：awk、perl、HHsed、sedmod以及GNU sed v3.02.80。
    不同版本的SED：不同的版本间的sed会有些不同之处，可以想象它们之间在语法上会有差异。具体而言，它们中大部分不支持在编辑命令中间使用标签（:name）或分支命令（b,t），除非是放在那些的末尾。这篇文档中我们尽量选用了可移植性较高的语法，以使大多数版本的sed的用户都能使用这些脚本。不过GNU版本的sed允许使
    用更简洁的语法。想像一下当读者看到一个很长的命令时的心情：
     sed -e '/AAA/b' -e '/BBB/b' -e '/CCC/b' -e d
    好消息是GNU sed能让命令更紧凑：
     sed '/AAA/b;/BBB/b;/CCC/b;d' # 甚至可以写成
     sed '/AAA|BBB|CCC/b;d'
    此外，请注意虽然许多版本的sed接受象“/one/ s/RE1/RE2/”这种在's'前带有空格的命令，但这些版本中有些却不接受这样的命令:“/one/! s/RE1/RE2/”。这时只需要把中间的空格去掉就行了。
    速度优化：当由于某种原因（比如输入文件较大、处理器或硬盘较慢等）需要提高
    命令执行速度时，可以考虑在替换命令（“s/.../.../”）前面加上地址表达式来
    提高速度。举例来说：
     sed 's/foo/bar/g' filename # 标准替换命令
     sed '/foo/ s/foo/bar/g' filename # 速度更快
     sed '/foo/ s//bar/g' filename # 简写形式
    当只需要显示文件的前面的部分或需要删除后面的内容时，可以在脚本中使用“q”
    命令（退出命令）。在处理大的文件时，这会节省大量时间。因此：
     sed -n '45,50p' filename # 显示第45到50行
     sed -n '51q;45,50p' filename # 一样，但快得多
    如果你有其他的单行脚本想与大家分享或者你发现了本文档中错误的地方，请发电
    子邮件给本文档的作者（Eric Pement）。邮件中请记得提供你所使用的sed版本、
    该sed所运行的操作系统及对问题的适当描述。本文所指的单行脚本指命令行的长
    度在65个字符或65个以下的sed脚本〔译注1〕。本文档的各种脚本是由以下所列作
    者所写或提供：
     Al Aab # 建立了“seders”邮件列表
     Edgar Allen # 许多方面
     Yiorgos Adamopoulos # 许多方面
     Dale Dougherty # 《sed & awk》作者
     Carlos Duarte # 《do it with sed》作者
     Eric Pement # 本文档的作者
     Ken Pizzini # GNU sed v3.02 的作者
     S.G. Ravenhall # 去html标签脚本
     Greg Ubben # 有诸多贡献并提供了许多帮助
    -------------------------------------------------------------------------
    译注1：大部分情况下，sed脚本无论多长都能写成单行的形式（通过`-e'选项和`;'
    号）——只要命令解释器支持，所以这里说的单行脚本除了能写成一行还对长度有
    所限制。因为这些单行脚本的意义不在于它们是以单行的形式出现。而是让用户能
    方便地在命令行中使用这些紧凑的脚本才是其意义所在。
    

- - -

# BSD版本Sed的文档

B S D G e n e r a l C o m m a n d s M a n u a l

NAME   
sed – stream editor

> SYNOPSIS【简略】   
> sed [-Ealn] command [file …]   
> 【即为】sed [option] command [file…]   
> sed [-Ealn] [-e command] [-f command_file] [-i extension] [file …]

- - -

> DESCRIPTION【描述】   
> The sed utility reads the specified files【指定的文件】, or the standard input if no   
> files are specified, modifying the input as specified by a list of com-   
> mands. The input is then written to the standard output.

- - -

> A single command may be specified as the first argument to sed. Multiple   
> commands may be specified by using the -e or -f options. All commands   
> are applied to the input in the order they are specified regardless of   
> their origin.

- - -

> 【可选的option】The following options are available:

     -E【使用扩展正则表达式】      Interpret regular expressions as extended (modern) regular
             expressions rather than basic regular expressions (BRE's).  The
             re_format(7) manual page fully describes both formats.
    
     -a      The files listed as parameters for the ``w'' functions are cre-
             ated (or truncated) before any processing begins, by default.
             The -a option causes sed to delay opening each file until a com-
             mand containing the related ``w'' function is applied to a line
             of input.
    
     -e command【使用多个command】
             Append the editing commands specified by the command argument to
             the list of commands.
    
     -f command_file【指令文件】
             Append the editing commands found in the file command_file to the
             list of commands.  The editing commands should each be listed on
             a separate line.
    
    
    
     -i extension
             Edit files in-place, saving backups with the specified extension.
             If a zero-length extension is given, no backup will be saved.  It
             is not recommended to give a zero-length extension when in-place
             editing files, as you risk corruption or partial content in situ-
             ations where disk space is exhausted, etc.
    
     -l      Make output line buffered.
    
     -n      By default, each line of input is echoed to the standard output
             after all of the commands have been applied to it.  The -n option
             suppresses（压制，阻碍） this behavior.
    

- - -

> 【command中所可选的限定符】   
> The form of a sed command is as follows:   
> [address[,address]]function[arguments]

     Whitespace may be inserted before the first address and the function por-
     tions of the command.
    
     Normally, sed cyclically copies a line of input, not including its termi-
     nating newline character, into a pattern space, (unless there is some-
     thing left after a  ), applies all of the commands with
     addresses that select that pattern space, copies the pattern space to the
     standard output, appending a newline, and deletes the pattern space.
    
     Some of the functions use a hold space to save all or part of the pattern
     space for subsequent retrieval.
    

> Sed Addresses   
> An address is not required, but if specified must be a number (that   
> counts input lines cumulatively across input files), a dollar (“$”)   
> character that addresses the last line of input, or a context address   
> (which consists of a regular expression preceded and followed by a delimiter).

     A command line with no addresses selects every pattern space.
    
     A command line with one address selects all of the pattern spaces that
     match the address.
    
     A command line with two addresses selects an inclusive range.  This range starts with the first pattern space that matches the first address.
    
     The end of the range is the next following pattern space that matches the second address.  If the second address is a number less than or equal to the line number first selected, only that line is selected.  In the case when the second address is a context address, sed does not re-match the second address against the pattern space that matched the first address.
    
     Starting at the first line following the selected range, sed starts looking again for the first address.
    
     Editing commands can be applied to non-selected pattern spaces by use of
     the exclamation character (``!'') function.
    

> 【sed中的正则表达式】   
> Sed Regular Expressions   
> The regular expressions used in sed, by default, are basic regular   
> expressions (BREs, see re_format(7) for more information), but extended   
> (modern) regular expressions can be used instead if the -E flag is given.   
> In addition, sed has the following two additions to regular expressions:

     1.   In a context address, any character other than a backslash (``\'') or newline character may be used to delimit the regular expression.
          Also, putting a backslash character before the delimiting character causes the character to be treated literally.  
          For example, in the context address \xabc\xdefx, the RE delimiter is an ``x'' and the  second ``x'' stands for itself, so that the regular expression is  ``abcxdef''.
    
     2.   The escape sequence \n matches a newline character embedded in the pattern space.  You cannot, however, use a literal newline character in an address or in the substitute command.
    
     One special feature of sed regular expressions is that they can default to the last regular expression used.  If a regular expression is empty, i.e., just the delimiter characters are specified, the last regular expression encountered is used instead.  The last regular expression is  defined as the last regular expression used as part of an address or sub-  stitute command, and at run-time, not compile-time.  For example, the command ``/abc/s//XXX/'' will substitute ``XXX'' for the pattern ``abc''.
    

- - -

> Sed Functions   
> In the following list of commands, the maximum number of permissible【允许的】 addresses for each command is indicated【指示】 by [0addr], [1addr], or [2addr],representing zero, one, or two addresses.

     The argument text consists of one or more lines.  To embed【嵌入】 a newline in the text, 【在之前】precede it with a backslash.  Other backslashes in text are deleted and the following character taken literally.
    
     The ``r'' and ``w'' functions【读取、写入函数】 take an optional file parameter, which should be separated from the function letter by white space.  Each file given as an argument to sed is created (or its contents truncated) before any input processing begins.
    
     The ``b'', ``r'', ``s'', ``t'', ``w'', ``y'', ``!'', and ``:'' functions all accept additional arguments.  The following synopses【概要】 indicate which arguments have to be separated from the function letters by white space characters.
    
     Two of the functions take a function-list.  This is a list of sed functions separated by newlines, as follows:
    
           { function
             function
             ...
             function
           }
    
     The ``{'' can be preceded by white space and can be followed by whitespace.  The function can be preceded by white space.  The terminating ``}'' must be preceded by a newline or optional white space.
    
     [2addr] function-list
             Execute function-list only when the pattern space is selected.
    
     [1addr]a\
             text    
     Write text to standard output immediately before each attempt to read a line of input, whether by executing the ``N'' function or by beginning a new cycle.
    
     [2addr]b[label]
             Branch to the ``:'' function with the specified label.  If the label is not specified, branch to the end of the script.
    
     [2addr]c\
     text    
     Delete the pattern space.  With 0 or 1 address or at the end of a 2-address range, text is written to the standard output.
    
     [2addr]d
             Delete the pattern space and start the next cycle.
    
     [2addr]D
             Delete the initial segment of the pattern space through the first newline character and start the next cycle.
    
     [2addr]g
             Replace the contents of the pattern space with the contents of the hold space.
    
     [2addr]G
             Append a newline character followed by the contents of the hold space to the pattern space.
    
     [2addr]h
             Replace the contents of the hold space with the contents of the pattern space.
    
     [2addr]H
             Append a newline character followed by the contents of the pattern space to the hold space.
    
     [1addr]i\
     text   
     Write text to the standard output.
    
     [2addr]l
             (The letter ell.)  Write the pattern space to the standard output in a visually unambiguous form.  This form is as follows:
    
                   backslash          \\
                   alert              \a
                   form-feed          \f
                   carriage-return    \r
                   tab                \t
                   vertical tab       \v
    
      Nonprintable characters are written as three-digit octal numbers(with a preceding backslash) for each byte in the character (most significant byte first).  Long lines are folded, with the point of folding indicated by displaying a backslash followed by a newline.  The end of each line is marked with a ``$''.
    
     [2addr]n
             Write the pattern space to the standard output if the default output has not been suppressed, and replace the pattern space with the next line of input.
    
     [2addr]N
             Append the next line of input to the pattern space, using an embedded newline character to separate the appended material from the original contents.  Note that the current line number changes.
    
     [2addr]p
             Write the pattern space to standard output.
    
     [2addr]P
             Write the pattern space, up to the first newline character to the standard output.
    
     [1addr]q
             Branch to the end of the script and quit without starting a new cycle.
    
     [1addr]r file
             Copy the contents of file to the standard output immediately before the next attempt to read a line of input.  If file cannot be read for any reason, it is silently ignored and no error condition is set.
    
     [2addr]s/regular expression/replacement/flags
             Substitute the replacement string for the first instance of the regular expression in the pattern space.  Any character other than backslash or newline can be used instead of a slash to delimit the RE and the replacement.  Within the RE and the replacement, the RE delimiter itself can be used as a literal character if it is preceded by a backslash.
    
     An ampersand (``&'') appearing in the replacement is replaced by the string matching the RE.  The special meaning of ``&'' in this context can be suppressed by preceding it by a backslash.  The string ``\#'', where ``#'' is a digit, is replaced by the text matched by the corresponding back reference expression (see re_format(7)).
    
             A line can be split by substituting a newline character into it.
             To specify a newline character in the replacement string, precede it with a backslash.
    
             The value of flags in the substitute function is zero or more of the following:
    
                   N       Make the substitution only for the N'th occurrence of the regular expression in the pattern space.
    
                   g       Make the substitution for all non-overlapping matches of the regular expression, not just the first one.
    
                   p       Write the pattern space to standard output if a replacement was made.  If the replacement string is identical to that which it replaces, it is still considered to have been a replacement.
    
                   w file  Append the pattern space to file if a replacement was made.  If the replacement string is identical to that which it replaces, it is still considered to have been a replacement.
    
     [2addr]t [label]
             Branch to the ``:'' function bearing the label if any substitutions have been made since the most recent reading of an input line or execution of a ``t'' function.  If no label is specified, branch to the end of the script.
    
     [2addr]w file
             Append the pattern space to the file.
    
     [2addr]x
             Swap the contents of the pattern and hold spaces.
    
     [2addr]y/string1/string2/
             Replace all occurrences of characters in string1 in the pattern space with the corresponding characters from string2.  Any character other than a backslash or newline can be used instead of a slash to delimit the strings.  Within string1 and string2, a backslash followed by an ``n'' is replaced by a newline character.  A pair of backslashes is replaced by a literal backslash.
             Finally, a backslash followed by any other character (except a newline) is that literal character.
    
     [2addr]!function
     [2addr]!function-list
             Apply the function or function-list only to the lines that are not selected by the address(es).
    
     [0addr]:label
             This function does nothing; it bears a label to which the ``b'' and ``t'' commands may branch.
    
     [1addr]=
             Write the line number to the standard output followed by a newline character.
    
     [0addr]
             Empty lines are ignored.
    
     [0addr]#
             The ``#'' and the remainder of the line are ignored (treated as a comment), with the single exception that if the first two characters in the file are ``#n'', the default output is suppressed.This is the same as specifying the -n option on the command line.
    

- - -

> ENVIRONMENT   
> The COLUMNS, LANG, LC_ALL, LC_CTYPE and LC_COLLATE environment variables   
> affect the execution of sed as described in environ(7).

- - -

> EXIT STATUS   
> The sed utility exits 0 on success, and >0 if an error occurs.

- - -

> LEGACY DESCRIPTION   
> Warnings are not generated for unused labels. In legacy mode, they are.   
> In the -y function, doubled backslashes are not converted to single ones.   
> In legacy mode, they are.   
> For more information about legacy mode, see compat(5).

- - -

> SEE ALSO   
> awk(1), ed(1), grep(1), regex(3), compat(5), re_format(7)

- - -

> STANDARDS   
> The sed utility is expected to be a superset of the IEEE Std 1003.2   
> (“POSIX.2”) specification.   
> The -E, -a and -i options are non-standard FreeBSD extensions and may not   
> be available on other operating systems.

- - -

> HISTORY   
> A sed command, written by L. E. McMahon, appeared in Version 7 AT&T UNIX.

- - -

> AUTHORS   
> Diomidis D. Spinellis [dds@FreeBSD.org][19]

- - -

> BUGS   
> Multibyte characters containing a byte with value 0x5C (ASCII `\’) may be   
> incorrectly treated as line continuation characters in arguments to the   
> a'', c” and  i'' commands. Multibyte characters cannot be used as   
> delimiters with the s” and “y” commands.

BSD May 10, 2005 BSD

</font>

[0]: #目录
[1]: #初级入门
[2]: #主要应用场景
[3]: #删除
[4]: #查找替换
[5]: #字符转换
[6]: #插入文本
[7]: #鸟哥私房菜
[8]: #sed到底是如何工作的
[9]: #高级用法
[10]: #高级应用实例
[11]: #编号
[12]: #文本转换和替代
[13]: #选择性地显示特定行
[14]: #选择性地删除特定行
[15]: #特殊应用
[16]: #bsd版本sed的文档
[17]: http://www.thegeekstuff.com/2009/11/unix-sed-tutorial-append-insert-replace-and-count-file-lines/
[18]: http://www.cnblogs.com/nhlinkin/p/3565922.html
[19]: mailto:dds@FreeBSD.org