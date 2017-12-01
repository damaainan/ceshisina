# 生信linux 常用命令手册（50个）

 时间 2017-11-28 23:40:28  

原文_http://www.jianshu.com/p/c8445de52f20][1]


#### 目录

1. ls [-alrtAFR] [name...]
1. mv [options] source dest/directory
1. cp [options] source dest/directory
1. rm [options] name...
1. touch [-acfm][-d<日期时间>][-r<参考文件或目录>] [-t<日期时间>][--help][--version][文件或目>录…]
1. pwd [--help][--version]
1. cd [dirName]
1. mkdir [-p] dirName
1. rmdir [-p] dirName
1. cat [-AbeEnstTuv] [--help] [--version] fileName
1. tac 反向显示
1. more [-dlfpcsu] [-num] [+/pattern] [+linenum] [fileNames..]
1. less [参数] 文件
1. head [参数]… [文件]… | 显示档案开头，默认开头10行
1. tail [必要参数] [选择参数] [文件] | 显示文件结尾内容
1. echo string
1. vi/vim 编辑文件
1. which [文件...]
1. locate [-d ][--help][--version][范本样式...]
1. cut [参数] [file]
1. ln [参数][源文件或目录][目标文件或目录]
1. tar -f[cxzjv] <file>
1. zip [-1..9][-r] <newfile.zip> <sourcefile/dir>
1. gzip [-1..9][-r] <file/dirname>
1. bzip2 <file>
1. sort [-bcdfimMnr][-o<输出文件>][-t<分隔字符>][+<起始栏位>-<结束栏位>][--help][--verison][文件]
1. uniq [-cdu][-f<栏位>][-s<字符位置>][-w<字符位置>][--help][--version][输入文件][输出文件]
1. wc [-clw][--help][--version][文件...]
1. grep [-abcEFGhHilLnqrsvVwxy][-A<显示列数>][-B<显示列数>][-C<显示列数>][-d<进行动作>][-e<范本样式>][-f<范本文件>][--help][范本样式][文件或目录...]
1. awk [-F] ‘(condition){operate}’ <filename>
1. sed [-i] '{command}' <filename>
1. md5sum [-c] <filename>
1. chmod [-R] <mode> <file/dirname>
1. find [path] [expression]
1. du [-ash] [--max-depth=<n>] <file/dirname>
1. ps [options] [--help]
1. top [-bcdu]
1. jobs [-l]
1. kill [-num] <PID> [-l]
1. fg [%num]
1. bg [%num]
1. history
1. nohup
1. 转后台 &
1. 重定向 >
1. 追加 >>
1. 管道符 |
1. finger [选项] [使用者] [用户@主机] | 查看用户信息
1. paste 合并文件，需确保合并的两文件行数相同
1. watch [参数] [命令] |重复执行某一命令以观察变化

#### 1. ls [-alrtAFR] [name...]

* Linux ls命令用于显示指定工作目录下之内容（列出目前工作目录所含之文件及子目录)。

-|-
-|-
-a | 显示所有文件及目录 (ls内定将文件名或目录名称开头为"."的视为隐藏档，不会列出) 
-l | 除文件名称外，亦将文件型态、权限、拥有者、文件大小等资讯详细列出 
-r | 将文件以相反次序显示(原定依英文字母次序) 
-t | 将文件依建立时间之先后次序列出 
-A | 同 -a ，但不列出 "." (目前目录) 及 ".." (父目录) 
-F | 在列出的文件名称后加一符号；例如可执行档则加 "*", 目录则加 "/" 
-R | 若目录下有文件，则以下之文件亦皆依序列出 

    # 列出目前工作目录下所有名称是 s 开头的文件，越新的排越后面 :
    ls -ltr s*
    # 将 /bin 目录以下所有目录及文件详细资料列出 
    ls -lR /bin
    # 出目前工作目录下所有文件及目录；目录于名称后加 "/", 可执行档于名称后加 "*" :
    ls -AF

#### 2. mv [options] source dest/directory

* Linux mv命令用来为文件或目录改名、或将文件或目录移入其它位置。

-|-
-|-
-b | 覆盖前做备份 
-f | 如存在不询问而强制覆盖 
-i | 如存在则询问是否覆盖 
-u | 较新才覆盖 
-t | 将多个源文件移动到统一目录下，目录参数在前，文件参数在后 mv 文件名 文件名 将源文件名改为目标文件名 mv 文件名 目录名 将文件移动到目标目录 mv 目录名 目录名 目标目录已存在，将源目录移动到目标目录；目标目录不存在则改名 mv 目录名 文件名 出错 

    # 将文件 aaa 更名为 bbb
    mv aaa bbb
    # 将info目录放入logs目录中。注意，如果logs目录不存在，则该命令将info改名为logs。
    mv info/ logs 
    # 再如将/usr/student下的所有文件和目录移到当前目录下，命令行为：
    $ mv /usr/student/*  .

#### 3. cp [options] source dest/directory

* Linux cp命令主要用于复制文件或目录。

-|-
-|-
-a | 此选项通常在复制目录时使用，它保留链接、文件属性，并复制目录下的所有内容。其作用等于dpR组合。 
-d | 复制时保留链接。这里所说的链接相当于Windows系统中的快捷方式。 
-f | 覆盖已经存在的目标文件而不给出提示。 
-i | 与-f选项相反，在覆盖目标文件之前给出提示，要求用户确认是否覆盖，回答"y"时目标文件将被覆盖。 
-p | 除复制文件的内容外，还把修改时间和访问权限也复制到新文件中。 
-r | 若给出的源文件是一个目录文件，此时将复制该目录下所有的子目录和文件。 
-l | 不复制文件，只是生成链接文件。 

    # 使用指令"cp"将当前目录"test/"下的所有文件复制到新目录"newtest"下，输入如下命令：
    $ cp –r test/ newtest

#### 4. rm [options] name...

* Linux rm命令用于删除一个文件或者目录。

-|-
-|-
-i 删除前逐一询问确认。 
-f 即使原档案属性设为唯读，亦直接删除，无需逐一确认。 
-r 将目录及以下之档案亦逐一删除。 

    # rm  test.txt 
    rm：是否删除 一般文件 "test.txt"? y  
    # rm  homework  
    rm: 无法删除目录"homework": 是一个目录  
    # rm  -r  homework  
    rm：是否删除 目录 "homework"? y 
    
    # 删除当前目录下的所有文件及目录，命令行为：
    rm  -r  *

#### 5. touch [-acfm][-d<日期时间>][-r<参考文件或目录>] [-t<日期时间>][--help][--version][文件或目录…]

* Linux touch命令用于修改文件或者目录的时间属性，包括存取时间和更改时间。若文件不存在，系统会建立一个新的文件。

-|-
-|-
-a 改变档案的读取时间记录。 
-m 改变档案的修改时间记录。 
-c 假如目的档案不存在，不会建立新的档案。与 
--no-create 的效果一样。 
-f 不使用，是为了与其他 unix 系统的相容性而保留。 
-r 使用参考档的时间记录，与 
--file 的效果一样。 
-d 设定时间与日期，可以使用各种不同的格式。 
-t 设定档案的时间记录，格式与 date 指令相同。 
--no-create 不会建立新档案。 
--help 列出指令格式。 
--version 列出版本讯息。 

    $ touch testfile                #修改文件时间属性为当前系统时间  
    $ ls -l testfile                #查看文件的时间属性  
    $ touch file            #创建一个名为“file”的新的空白文件

#### 6. pwd [--help][--version]

* Linux pwd命令用于显示工作目录（绝对路径）。
```
    # pwd
    /root/test           #输出结果
```

#### 7. cd [dirName]

* Linux cd命令用于切换当前工作目录至 dirName(目录参数)。

-|-
-|-
- / .. 返回上层目录 
回车 / ~ 返回主目录 
/ 根目录 
```
    # 跳到 /usr/bin/ :
    cd /usr/bin
    # 跳到自己的 home 目录 :
    cd ~
    # 跳到目前目录的上上两层 :
    cd ../..
```
#### 8. mkdir [-p] dirName

* Linux mkdir命令用于建立名称为 dirName 之子目录。

-|-
-|-
-p 递归创建目录，若父目录不存在则依次创建 
-m 自定义创建目录的权限 eg:mkdir -m 777 hehe 
-v 显示创建目录的详细信息 

    # 在工作目录下，建立一个名为 AAA 的子目录 :
    mkdir AAA
    # 在工作目录下的 BBB 目录中，建立一个名为 Test 的子目录。 若 BBB 目录原本不存在，则建立一个。（注：本例若不加 -p，且原本 BBB目录不存在，则产生错误。）
    mkdir -p BBB/Test

#### 9. rmdir [-p] dirName

* Linux rmdir命令删除空的目录。

-p 当子目录被删除后使它也成为空目录的话，则顺便一并删除。 

    # 将工作目录下，名为 AAA 的子目录删除 :
    rmdir AAA
    # 在工作目录下的 BBB 目录中，删除名为 Test 的子目录。若 Test 删除后，BBB 目录成为空目录，则 BBB 亦予删除。
    rmdir -p BBB/Test

#### 10. cat [-AbeEnstTuv] [--help] [--version] fileName

* cat 命令用于连接文件并打印到标准输出设备上。

-|-
-|-
-n 或 --number 由 1 开始对所有输出的行数编号。 
-b 或 --number-nonblank 和 -n 相似，只不过对于空白行不编号。 
-s 或 --squeeze-blank 当遇到有连续两行以上的空白行，就代换为一行的空白行。 
-v 或 --show-nonprinting 使用 ^ 和 M- 符号，除了 LFD 和 TAB 之外。 
-E 或 --show-ends 在每行结束处显示 $。 
-T 或 --show-tabs 将 TAB 字符显示为 ^I。 
-e 等价于 -vE。 
-A, --show-all 等价于 -vET。 
-e 等价于"-vE"选项； 
-t 等价于"-vT"选项； 

    # 把 textfile1 的文档内容加上行号后输入 textfile2 这个文档里：
    cat -n textfile1 > textfile2
    # 把 textfile1 和 textfile2 的文档内容加上行号（空白行不加）之后将内容附加到 textfile3 文档里：
    cat -b textfile1 textfile2 >> textfile3
    # 清空 /etc/test.txt 文档内容：
    cat /dev/null > /etc/test.txt
    # cat 也可以用来制作镜像文件。例如要制作软盘的镜像文件，将软盘放好后输入：
    cat /dev/fd0 > OUTFILE
    # 相反的，如果想把 image file 写到软盘，输入：
    cat IMG_FILE > /dev/fd0

#### 11. tac 反向显示

#### 12. more [-dlfpcsu] [-num] [+/pattern] [+linenum] [fileNames..]

* Linux more 命令类似 cat ，不过会以一页一页的形式显示，更方便使用者逐页阅读

-|-
-|-
-num 一次显示的行数 
-d 提示使用者，在画面下方显示 [Press space to continue, 'q' to quit.] ，如果使用者按错键，则会显示 [Press 'h' for instructions.] 而不是 '哔' 声 
-l 取消遇见特殊字元 ^L（送纸字元）时会暂停的功能 
-f 计算行数时，以实际上的行数，而非自动换行过后的行数（有些单行字数太长的会被扩展为两行或两行以上） 
-p 不以卷动的方式显示每一页，而是先清除萤幕后再显示内容 
-c 跟 -p 相似，不同的是先显示内容再清除其他旧资料 
-s 当遇到有连续两行以上的空白行，就代换为一行的空白行 
-u 不显示下引号 （根据环境变数 TERM 指定的 terminal 而有所不同） +/pattern 在每个文档显示前搜寻该字串（pattern），然后从该字串之后开始显示 +num 从第 num 行开始显示 fileNames 欲显示内容的文档，可为复数个数 

    # 逐页显示 testfile 文档内容，如有连续两行以上空白行则以一行空白行显示。
    more -s testfile
    # 从第 20 行开始显示 testfile 之文档内容。
    more +20 testfile

#### 13. less [参数] 文件

* less 与 more 类似，但使用 less 可以随意浏览文件，而 more 仅能向前移动，却不能向后移动，而且 less 在查看之前不会加载整个文件。

-|-
-|-
-m 显示类似于more命令的百分比 
-N 显示行号 
/ 字符串：向下搜索“字符串”的功能 
? 字符串：向上搜索“字符串”的功能 
n 重复前一个搜索（与 / 或 ? 有关） 
N 反向重复前一个搜索（与 / 或 ? 有关） 
b 向后翻一页 
d 向后翻半页 

    # 查看文件
    less log2013.log
    # ps查看进程信息并通过less分页显示
    ps -ef |less
    # 浏览多个文件
    less log2013.log log2014.log

#### 14. head [参数]… [文件]… | 显示档案开头，默认开头10行

-|-
-|-
-v 显示文件名 
-c number 显示前number个字符,若number为负数,则显示除最后number个字符的所有内容 
-number/n (+)number 显示前number行内容， -n number 若number为负数，则显示除最后number行数据的所有内容 

#### 15. tail [必要参数] [选择参数] [文件] | 显示文件结尾内容

-|-
-|-
-v 显示详细的处理信息 
-q 不显示处理信息 
-num/-n (-)num 显示最后num行内容 
-n +num 从第num行开始显示后面的数据 
-c 显示最后c个字符 
-f 循环读取 

#### 16. echo string

* 用于字符串的输出

-|-
-|-
-n 输出后不换行 
-e 遇到转义字符特殊处理 

    # 1.显示普通字符串:
    echo "It is a test"
    It is a test
    
    # 2.显示转义字符
    echo "\"It is a test\""
    "It is a test"
    
    # 3.显示变量
    # read 命令从标准输入中读取一行,并把输入行的每个字段的值指定给 shell 变量
    
    #!/bin/sh
    read name 
    echo "$name It is a test"
    
    #以上代码保存为 test.sh，name 接收标准输入的变量，结果将是:
    [root@www ~]# sh test.sh
    OK                     #标准输入
    OK It is a test        #输出
    
    # 5.显示不换行
    echo "he\nhe"   显示he\nhe
    ehco -e "he\nhe"    显示he(换行了)he
    
    # 6.显示结果定向至文件
    echo "It is a test" > myfile
    
    #7.原样输出字符串，不进行转义或取变量(用单引号)
    echo '$name\"'
    # 输出结果：
    $name\"
    
    # 8.显示命令执行结果
    echo `date`
    Thu Jul 24 10:08:46 CST 2014

#### 17. vi/vim 编辑文件

![][3]

image.png

    :w filename 将文章以指定的文件名保存起来  
    :wq 保存并退出
    :q! 不保存而强制退出
    命令行模式功能键
    1）插入模式
        按「i」切换进入插入模式「insert mode」，按"i"进入插入模式后是从光标当前位置开始输入文件；
        按「a」进入插入模式后，是从目前光标所在位置的下一个位置开始输入文字；
        按「o」进入插入模式后，是插入新的一行，从行首开始输入文字。
    
    2）从插入模式切换为命令行模式
      按「ESC」键。
    3）移动光标
    　　vi可以直接用键盘上的光标来上下左右移动，但正规的vi是用小写英文字母「h」、「j」、「k」、「l」，分别控制光标左、下、上、右移一格。
    　　按「ctrl」+「b」：屏幕往"后"移动一页。
    　　按「ctrl」+「f」：屏幕往"前"移动一页。
    　　按「ctrl」+「u」：屏幕往"后"移动半页。
    　　按「ctrl」+「d」：屏幕往"前"移动半页。
    　　按数字「0」：移到文章的开头。
    　　按「G」：移动到文章的最后。
    　　按「$」：移动到光标所在行的"行尾"。
    　　按「^」：移动到光标所在行的"行首"
    　　按「w」：光标跳到下个字的开头
    　　按「e」：光标跳到下个字的字尾
    　　按「b」：光标回到上个字的开头
    　　按「#l」：光标移到该行的第#个位置，如：5l,56l。
    
    4）删除文字
    　　「x」：每按一次，删除光标所在位置的"后面"一个字符。
    　　「#x」：例如，「6x」表示删除光标所在位置的"后面"6个字符。
    　　「X」：大写的X，每按一次，删除光标所在位置的"前面"一个字符。
    　　「#X」：例如，「20X」表示删除光标所在位置的"前面"20个字符。
    　　「dd」：删除光标所在行。
    　　「#dd」：从光标所在行开始删除#行
    
    5）复制
    　　「yw」：将光标所在之处到字尾的字符复制到缓冲区中。
    　　「#yw」：复制#个字到缓冲区
    　　「yy」：复制光标所在行到缓冲区。
    　　「#yy」：例如，「6yy」表示拷贝从光标所在的该行"往下数"6行文字。
    　　「p」：将缓冲区内的字符贴到光标所在位置。注意：所有与"y"有关的复制命令都必须与"p"配合才能完成复制与粘贴功能。
    
    6）替换
    　　「r」：替换光标所在处的字符。
    　　「R」：替换光标所到之处的字符，直到按下「ESC」键为止。
    
    7）回复上一次操作
    　　「u」：如果您误执行一个命令，可以马上按下「u」，回到上一个操作。按多次"u"可以执行多次回复。
    
    8）更改
    　　「cw」：更改光标所在处的字到字尾处
    　　「c#w」：例如，「c3w」表示更改3个字
    
    9）跳至指定的行
    　　「ctrl」+「g」列出光标所在行的行号。
    　　「#G」：例如，「15G」，表示移动光标至文章的第15行行首。

#### 18. which [文件...]

* Linux which命令用于查找文件。

-|-
-|-
-n<文件名长度> 指定文件名长度，指定的长度必须大于或等于所有文件中最长的文件名。 
-p<文件名长度> 与-n参数相同，但此处的<文件名长度>包括了文件的路径。 
-w 指定输出时栏位的宽度。 
-V 显示版本信息。 

    #使用指令"which"查看指令"bash"的绝对路径，输入如下命令：
    $ which bash
    #上面的指令执行后，输出信息如下所示：
    /bin/bash

#### 19. locate [-d ][--help][--version][范本样式...]

* Linux locate命令用于查找符合条件的文档，他会去保存文档和目录名称的数据库内，查找合乎范本样式条件的文档或目录。

-|-
-|-
-d或--database= 配置locate指令使用的数据库。 locate指令预设的数据库位于/var/lib/slocate目录里，文档名为slocate.db，您可使用 这个参数另行指定。 
--help 在线帮助。 
--version 显示版本信息。 

    #查找passwd文件，输入以下命令：
    locate passwd

#### 20. cut [参数] [file]

* Linux cut命令用于显示每行从开头算起 num1 到 num2 的文字。

-|-
-|-
-b 以字节为单位进行分割。这些字节位置将忽略多字节字符边界，除非也指定了 -n 标志。 
-c 以字符为单位进行分割。 
-d 自定义分隔符，默认为制表符。 
-f 与-d一起使用，指定显示哪个区域。 
-n 取消分割多字节字符。仅和 -b 标志一起使用。如果字符的最后一个字节落在由 -b 标志的 List 参数指示的范围之内，该字符将被写出；否则，该字符将被排除 

    cut -c 1-10 file #显示文件file每行开头的10个字符
    cut -f 1-10 file #显示文件file每行开头10列（以“\t”分割）
    cut -d “ ” -f 2 #显示文件file第二列（以空格分割）

#### 21. ln [参数][源文件或目录][目标文件或目录]

* Linux ln命令是一个非常重要命令，它的功能是为某一个文件在另外一个位置建立一个同步的链接。

-|-
-|-
-s 建立软连接 
-v 显示详细的处理过程 

    ln -s file1 file2   #将file1链接为file2（注：file2必须不存在）
    ln -s file1 file2 dirname/   #将file1和file2链接到dirname/下

#### 22. tar -f[cxzjv] <file>

* 加入或还原备份文件内的文件

-|-
-|-
-f 必加参数 
-c 创建备份文件 
-x 从备份文件中还原文件 
-z 调用gzip/gunzip来压缩/解压缩文件 
-j 调用bzip2/bunzip2来压缩/解压缩文件 
-v 显示命令执行过程 

    #将file1，file2文件打包到newfile.tar
    tar -cf newfile.tar file1 file2
    #提取newfile.tar中的文件
    tar -xf newfile.tar
    #将file1，file2文件打包并调用gzip程序将文件压缩为
    tar -czvf newfile.tar.gz file1 file2newfile.tar.gz
    #将newfile.tar.gz文件解压并提取里边的文件
    tar -xzvf newfile.tar.gz

#### 23.zip [-1..9][-r] <newfile.zip> <sourcefile/dir>

* 压缩并生成“.zip”结尾的文件

-|-
-|-
-r 将子目录下所有文件和目录一并处理 
-1..9 压缩效率，数值越大，压缩效率越高 

    #将file1，file2进行压缩到newfile.zip内
    zipnewfile.zip file1 file2
    # 使用unzip进行解压
    unzip newfile.zip

#### 24.gzip [-1..9][-r] <file/dirname>

* 压缩并生成“.gz”结尾的文件

-|-
-|-
-r 对目录下的文件进行压缩，但不会对目录进行压缩 
-1..9 压缩效率，数值越大，压缩效率越高（默认6） 

    # 将file压缩为file.gz并删除源文件
    gzip file
    # 使用gunzip进行解压
    gunzip file.gz

#### 25. bzip2 <file>

* 压缩并生成“.bz2”结尾的文件
```
    # 将file压缩为file.bz2并删除源文件
    bzip2 file
    # 使用bunzip2进行解压
    bunzip2file.bz2
```

#### 26.sort [-bcdfimMnr][-o<输出文件>][-t<分隔字符>][+<起始栏位>-<结束栏位>][--help][--verison][文件]

* 将文本文件内容进行排序

-|-
-|-
-b 忽略每行前面开始出的空格字符。 
-c 检查文件是否已经按照顺序排序。 
-d 排序时，处理英文字母、数字及空格字符外，忽略其他的字符。 
-f 排序时，将小写字母视为大写字母。 
-i 排序时，除了040至176之间的ASCII字符外，忽略其他的字符。 
-m 将几个排序好的文件进行合并。 
-M 将前面3个字母依照月份的缩写进行排序。 
-n 依照数值的大小排序。 
-o<输出文件> 将排序后的结果存入指定的文件。 
-r 以相反的顺序来排序。 
-t<分隔字符> 指定排序时所用的栏位分隔字符。 +<起始栏位>-<结束栏位> 以指定的栏位来排序，范围由起始栏位到结束栏位的前一栏位。 
--help 显示帮助。 
--version 显示版本信息。 

    # 对file文件按第一列内容ascii码值从小到大排序并输出。
    sort file
    # 对file文件按第3列内容数值大小从小到大排序。
    sort -n -k 3 file
    # 对file文件按数值大小反向排序，优先考虑第一列，再考虑第二列
    sort -nr -k1,2 file

#### 27. uniq [-cdu][-f<栏位>][-s<字符位置>][-w<字符位置>][--help][--version][输入文件][输出文件]

* Linux uniq命令用于检查及删除文本文件中重复出现的行列。

-|-
-|-
-c或--count 在每列旁边显示该行重复出现的次数。 
-d或--repeated 仅显示重复出现的行列。 
-f<栏位>或--skip-fields=<栏位> 忽略比较指定的栏位。 
-s<字符位置>或--skip-chars=<字符位置> 忽略比较指定的字符。 
-u或--unique 仅显示出一次的行列。 
-w<字符位置>或--check-chars=<字符位置> 指定要比较的字符。 
--help 显示帮助。 
--version 显示版本信息。 [输入文件] 指定已排序好的文本文件。 [输出文件] 指定输出的文件。 

    # 合并相同的行，并统计每行重复次数，输出到屏幕
    uniq -c file
    # 合并相同的行，并显示file中有重复出现的行，输出到outfile文件中
    uniq -d file outfile

#### 28. wc [-clw][--help][--version][文件...]

* Linux wc命令用于计算字数。
* 利用wc指令我们可以计算文件的Byte数、字数、或是列数，若不指定文件名称、或是所给予的文件名为"-"，则wc指令会从标准输入设备读取数据。

-|-
-|-
-c或--bytes或--chars 只显示Bytes数。 
-l或--lines 只显示列数。 
-w或--words 只显示字数。 
--help 在线帮助。 
--version 显示版本信息。 

    $ cat testfile  
    Linux networks are becoming more and more common, but scurity is often an overlooked  
    issue. Unfortunately, in today’s environment all networks are potential hacker targets,  
    fro0m tp-secret military research networks to small home LANs.  
    Linux Network Securty focuses on securing Linux in a networked environment, where the  
    security of the entire network needs to be considered rather than just isolated machines.  
    It uses a mix of theory and practicl techniques to teach administrators how to install and  
    use security applications, as well as how the applcations work and why they are necesary.

    $ wc testfile           # testfile文件的统计信息  
    3 92 598 testfile       # testfile文件的行数为3、单词数92、字节数598 
    
    $ wc testfile testfile_1 testfile_2  #统计三个文件的信息  
    3 92 598 testfile                    #第一个文件行数为3、单词数92、字节数598  
    9 18 78 testfile_1                   #第二个文件的行数为9、单词数18、字节数78  
    3 6 32 testfile_2                    #第三个文件的行数为3、单词数6、字节数32  
    15 116 708 总用量                    #三个文件总共的行数为15、单词数116、字节数708

#### 29. grep [-abcEFGhHilLnqrsvVwxy][-A<显示列数>][-B<显示列数>][-C<显示列数>][-d<进行动作>][-e<范本样式>][-f<范本文件>][--help][范本样式][文件或目录...]

* Linux grep命令用于查找文件里符合条件的字符串。

-|-
-|-
-a或--text 不要忽略二进制的数据。 
-A<显示列数>或--after-context=<显示列数> 除了显示符合范本样式的那一列之外，并显示该列之后的内容。 
-b或--byte-offset 在显示符合范本样式的那一列之前，标示出该列第一个字符的位编号。 
-B<显示列数>或--before-context=<显示列数> 除了显示符合范本样式的那一列之外，并显示该列之前的内容。 
-c或--count 计算符合范本样式的列数。 
-C<显示列数>或--context=<显示列数>或-<显示列数> 除了显示符合范本样式的那一列之外，并显示该列之前后的内容。 
-d<进行动作>或--directories=<进行动作> 当指定要查找的是目录而非文件时，必须使用这项参数，否则grep指令将回报信息并停止动作。 
-e<范本样式>或--regexp=<范本样式> 指定字符串做为查找文件内容的范本样式。 
-E或--extended-regexp 将范本样式为延伸的普通表示法来使用。 
-f<范本文件>或--file=<范本文件> 指定范本文件，其内容含有一个或多个范本样式，让grep查找符合范本条件的文件内容，格式为每列一个范本样式。 
-F或--fixed-regexp 将范本样式视为固定字符串的列表。 
-G或--basic-regexp 将范本样式视为普通的表示法来使用。 
-h或--no-filename 在显示符合范本样式的那一列之前，不标示该列所属的文件名称。 
-H或--with-filename 在显示符合范本样式的那一列之前，表示该列所属的文件名称。 
-i或--ignore-case 忽略字符大小写的差别。 
-l或--file-with-matches 列出文件内容符合指定的范本样式的文件名称。 
-L或--files-without-match 列出文件内容不符合指定的范本样式的文件名称。 
-n或--line-number 在显示符合范本样式的那一列之前，标示出该列的列数编号。 
-q或--quiet或--silent 不显示任何信息。 
-r或--recursive 此参数的效果和指定"-d recurse"参数相同。 
-s或--no-messages 不显示错误信息。 
-v或--revert-match 反转查找。 
-V或--version 显示版本信息。 
-w或--word-regexp 只显示全字符合的列。 
-x或--line-regexp 只显示全列符合的列。 
-y 此参数的效果和指定"-i"参数相同。 
--help 在线帮助。 

    # 查找文件中含有“world”的行
    grep world file
    # 查找文件中不含有“world”的行
    grep -v world file

#### 30. awk [-F] ‘(condition){operate}’ <filename>

* 对特定的行中特定的列进行操作

-F 指定列的分割符，可以使任意字符，默认按空白分割 

    # 按“：”来分割并打印出第一列
    awk -F “:” ‘{print $1}’
    # 对第一列大于100的行整行输出
    awk ‘($1 > 100){print $0}’
    # 对第一列大于100的行输出第一列和第二列的结果并以“\t”分割。
    awk ‘($1 > 100){print $1”\t”$2}’
    #对第三列匹配“world”的行的第一列求和，全部处理完之后输出结果x的值
    awk ‘($3~/world/){ x+= $1}END{print x}’

#### 31. sed [-i] '{command}' <filename>

* 文本处理并可对文件进行编辑

-i 直接在原文件中修改（默认修改后屏幕输出，原文件不变） a 新增，在新的下一行出现 c 取代，取代 n1,n2 之间的行 eg: sed '1,2c Hi' ab d 删除 

    # 将file文件中的test字符替换为new_word
    sed -i ‘s/test/new_word/’ file
    # 将file文件中匹配pattern字串的行进行替换操作
    sed -i ‘/pattern/ s/ test/new_word/’ file
    # 将文件file中的空白行删除（d）
    sed -i ‘/^$/ d’ file

#### 32. md5sum [-c] <filename>

* 验证文件传输的完整性

-c 校验文件传输后是否完整 

    # 对file1文件生成md5值。
    md5sumfile1
    # 对file1文件生成md5值并重定向到newfile中。
    md5sum file1 > newfile
    # 检验newfile中文件的md5值是否和文件一致。
    md5sum -c newfile

#### 33. chmod [-R] <mode> <file/dirname>

* 设置文件或目录权限

-R 对目录和目录下所有文件均设置权限 详细说明 文件权限分为是否可读(r)、是否可写(w)、是否可执行(x)3种，同时对应档案拥有者(u)、同组成员(g)、其他成员(o)3种。 符号模式 [ugoa] [+-=] [rwx] 数值模式 用1/0表示，则111表示可读可写可执行，000表示不可读不可写不可执行，可读可写可执行 分别对应10进制的4,2,1 则5表示可读不可写可执行。 

    # 对file 文件用户自身增加可执行权限，同组成员权限设置为可读可写，对其他成员移除rwx权限。
    chmod u+xg=rx o-rwx file
    # 对file文件所有成员移除可写权限。
    chmod a-w file
    # 对dirname目录及其下所有文件权限设置为用户自身可读可写可执行，同组成员可读可执行，其他成员无权限。
    chmod -R 750 dirname

![][4]

image.png

#### 34. find [path] [expression]

* Linux find命令用来在指定目录下查找文件

-|-
-|-
path 在path路径下进行查找 
-name <filename> 按文件名查找（文件名允许通配符） 
-perm <mode> 按文件权限查找 
-user <user name> 按文件所有者查找 
-group <group name> 按文件所在组查找 
-mtime <+n/-n> 按文件更改时间查找，-n表示更改时间距现在n天以内，+n表示距现在n天以外 
-type <l/d/f> 按文件类型查找，l：符号链接文件，f：普通文件，d：目录文件 

    # 在当前目录及其子目录下查找文件名为file的文件
    find./ -name file
    # 寻找文件名以a结尾的目录文件。
    find ./ -name ‘*a’ -type d 
    
    将目前目录及其子目录下所有延伸档名是 c 的文件列出来。
    # find . -name "*.c"
    将目前目录其其下子目录中所有一般文件列出
    # find . -type f
    将目前目录及其子目录下所有最近 20 天内更新过的文件列出
    # find . -ctime -20
    查找/var/logs目录中更改时间在7日以前的普通文件，并在删除之前询问它们：
    $ find /var/logs -type f -mtime +7 -ok rm { } ;
    查找前目录中文件属主具有读、写权限，并且文件所属组的用户和其他用户具有读权限的文件：
    $ find . -type f -perm 644 -exec ls -l { } ;
    为了查找系统中所有文件长度为0的普通文件，并列出它们的完整路径：
    $ find / -type f -size 0 -exec ls -l { } ;
    查找/var/logs目录中更改时间在7日以前的普通文件，并在删除之前询问它们:
    $ find /var/logs -type f -mtime +7 -ok rm { } ;

#### 35. du [-ash] [--max-depth=<n>] <file/dirname>

* 显示目录或文件的大小

-|-
-|-
-a 显示目录中个别文件大小 
-s 只显示总计 
-h 以“K” ，“M” ，“G”为单位显示 
--max-depth=<n> 只显示n层目录以内的文件 

    # 显示当前目录的大小
    du -sh ./
    # 显示dir目录下所有文件大小，不包括下一级目录
    du -ah --max-depth=1 dir

#### 36. ps [options] [--help]

* Linux ps命令用于显示当前进程 (process) 的状态。


-u <usrname> 显示usr用户的进程（默认显示自身用户进程） 

    # ps -A 显示进程信息
    PID TTY     TIME CMD
      1 ?    00:00:02 init
      2 ?    00:00:00 kthreadd

#### 37. top [-bcdu]

* 显示用户进程（实时）

-|-
-|-
-b 批处理模式，可以将top内容重定向到文件中 
-c 显示详细信息 
-d <n> 刷新时间间隔，n秒刷新一次 
-u <usrname> 只显示usr用户的进程 

    显示进程信息
    # top
    设置信息更新时间
    # top -d 3
    
    //表示更新周期为3秒
    显示指定的进程信息
    # top -p 139
    
    //显示进程号为139的进程信息，CPU、内存占用率等

#### 38. jobs [-l]

* 显示后台任务（当前节点，当前窗口）

-l 显示任务进程ID 

    jobs
    jobs -l

#### 39. kill [-num] <PID> [-l]

* 删除或控制执行中的进程

-|-
-|-
-num 通过num传递一个信号控制进程（默认15，终止进程），常用值如下 
9 强制删除进程 
-19 暂停一个进程（使之处于T状态） 
18 继续暂停的进程 -l 显示信号列表 

    kill28004    #终止进程号为28004的进程
    kill -9 28004   #强制删除进程号为28004的进程
    kill -19 28004   #暂停进程号为28004的进程
    kill -18 28004   #继续进程号为28004的进程
    kill -l   #显示信号列表

#### 40. fg [%num]

* 将后台进程转换到前台

%num 将编号为num的后台任务转换到前台，num由jobs 命令得到。 

    fg       #将最近的一个转后台的任务转为前台
    fg %2     #将编号为2的后台任务转到前台

#### 41. bg [%num]

* 将前台任务转为后台（需先用ctrl+z暂停任务），或者继续后台暂停的任务

%num 将编号为num的后台暂停任务继续。 

    ctrl+z， bg
    bg %2 将编号为2的后台暂停任务继续。

#### 42. history

* 查询该节点上执行过的历史命令

#### 43. nohup

* 加到命令前使得该命令在用户退出登录后也能继续执行
* 一般与转后台’&’一起使用，屏幕输出默认重定向到nohup.out文件中
```
    nohup perl test.pl &
```
#### 44. 转后台 &

* 加到命令结尾，使该命令在后台运行
```
    cp file1file2 & 后台执行拷贝命令
```

#### 45. 重定向 >

* 将标准输出内容重定向到文件中
```
    ls dir > dir_list 显示dir目录下的文件并将内容输出到dir_list文件中，若dir_list文件存在，则会被清空，若不纯在，将创建。
```

#### 46. 追加 >>

* 表示将内容追加到文件末尾。
```
    ls dir >> dir_list 显示dir目录下的文件并将内容追加到dir_list文件中，若dir_list文件存在，内容追加到文件末尾，若不纯在，将创建。
```

#### 47. 管道符 |

* 将”|”前一部分的输出作为”|”后一部分的输入
```
    less file1| grep world | awk ‘{print $1}’ 显示file1的内容，将带有world的行输出，打印该行的第一列
```

#### 48. finger [选项] [使用者] [用户@主机] | 查看用户信息

-|-
-|-
-s 显示用户的注册名、实际姓名、终端名称、写状态、停滞时间、登录时间等信息 
-l 除了用-s选项显示的信息外，还显示用户主目录、登录shell、邮件状态等信息，以及用户主目录下的.plan、.project和.forward文件的内容。 
-p 除了不显示.plan文件和.project文件以外，与-l选项相同 

#### 49. paste 合并文件，需确保合并的两文件行数相同

-|-
-|-
-d 指定不同于空格或tab键的域分隔符 
-s 按行合并，单独一个文件为一行 

#### 50. watch [参数] [命令] |重复执行某一命令以观察变化

-|-
-|-
-n 时隔多少秒刷新 
-d 高亮显示动态变化

[1]: http://www.jianshu.com/p/c8445de52f20
[3]: https://img2.tuicool.com/YzAvq2F.png
[4]: https://img2.tuicool.com/myiqYni.png