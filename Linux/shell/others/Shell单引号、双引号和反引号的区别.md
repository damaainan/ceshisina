## Shell单引号、双引号和反引号的区别

来源：[https://www.kawabangga.com/posts/2819](https://www.kawabangga.com/posts/2819)

时间 2018-03-29 11:32:54


每次在shell用到引号的时候，都会因为用单引号还是双引号纠结不已。最近看了《    [Shell十三问][0]
》，终于弄清楚了它们的区别。


## 反引号

首先，反引号是明显与单双引号有区别的。放到这篇文章里面一起写可能是因为我觉得它的名字里面有引号二字吧。命令行是支持拼接的，而反引号的作用就是“执行反引号内的命令，将结果返回到字符串”。比如在下面的命令中，反引号内的命令就会先执行，得到结果`a`，然后反引号的内容会被执行结果替换，组合成最终命令`cat a`，执行。

```sh
vagrant@ubuntu:~$ tree
.
|-- bar
|   |-- a
|   |-- b
|   `-- c
`-- foo
 
2 directories, 3 files
vagrant@ubuntu:~$ cat `find . -name a`
CONTENT IN FILE A.

```

这样就可以很方便地批量执行命令，例如删除所有docker的容器，可以使用：

```sh
docker rm $(docker ps -qa)

```

这个作用基本等同于`$()`，上面的命令等价于`cat $(find . -name a)`事实上，`$()`更好一些，因为它支持嵌套，反引号不支持。

其实这个功能也可以使用管道配合`xargs`实现（例如上面的打开文件命令，可以用 find . -name a | xargs -I {} cat {} )，但是`xargs`要更繁琐一些。直接使用反引号这种替换更加直观。而且对于多个命令的结果组合成一条命令来说，反引号要更方便。


## 单引号和双引号

Shell中有些字符，是不表示字符意义的。比如说，你想`echo`一个`>`，你需要`echo \>`进行转义。

对于字符的意义和转义的意义，我经常搞混：这个字符需要转义之后是特殊意义还是字面意义呢？后来相出了一个窍门：如果一个字符是shell中的meta字符，那么它不表示字面的意义，需要转义之后表示字面的意义。这个窍门也适用于其他需要转义的地方，例如正则表达式的`.`，它因为是特殊字符所以不表示字面的意义`.`，如果你想要匹配一个`.`字符，就需要在写正则的时候加上一个`\`，来表示它的字面意义。这段对某些朋友来说可能是废话……但是确实是让我纠结了好久的。剩下的就是需要记住环境下的特殊字符了。

```
Symbol          Meaning
>               Output redirection, (see File Redirection)
>>              Output redirection (append)
<               Input redirection
*               File substitution wildcard; zero or more characters
?               File substitution wildcard; one character
[ ]             File substitution wildcard; any character between brackets
`cmd`           Command Substitution
$(cmd)          Command Substitution
|               The Pipe (|)
;               Command sequence, Sequences of Commands
||              OR conditional execution
&&              AND conditional execution
( )             Group commands, Sequences of Commands
&               Run command in the background, Background Processes
#               Comment
$               Expand the value of a variable
\               Prevent or escape interpretation of the next character

```

So，你肯定不想在命令行写很多很多`\`来转义，所以就出现了单引号和双引号。其实就类似于Python的字符串前面加`r`，表示引号内的字符全表示字面意义。在shell中，如果字符在引号内，就不会被shell解释成特殊意义。

例如空格表示`分隔符`（**`IFS`**），`cat a b`的意义是打开文件a和文件b。但是`cat "a b"`的意义是打开文件名为 a空格b 的文件。等价于`cat a\ b`。

单引号和双引号的区别都是protect，保护字符串不被shell解释。但是区别就是，双引号不会保护三个字符：反引号字符、反斜杠字符`\`，以及`$`这正好可以方便我们一些操作，比如将命令的结果当做字符串。或者在字符串内引用环境变量的值。下面的例子可以简单地展示它们的区别：

```sh
vagrant@ubuntu:~/bar$ echo "`ls`"
a
b
c
vagrant@ubuntu:~/bar$ echo '`ls`'
`ls`
vagrant@ubuntu:~/bar$ echo '\\'
\\
vagrant@ubuntu:~/bar$ echo "\\"
\

```

这个地方有个关键，就是你要明白你想要的是一个表示字面意义的字符串，还是一个命令组合。如果你想给一个命令传入参数，例如`awk`或`grep`，那么你的参数一般是字符串；如果你是要在shell上执行一连串的命令，那么可能不需要转义。其实就是区别 shell meta 和 command meta， 这个可以参考文章开头的《Shell 十三问》。

参考资料：

* [shell metachar][1]


[0]: http://bbs.chinaunix.net/thread-218853-1-1.html
[1]: http://faculty.salina.k-state.edu/tim/unix_sg/shell/metachar.html