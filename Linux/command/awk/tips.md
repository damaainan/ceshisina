## 如何将一个程序的输出赋值给awk变量


经常需要在 **awk**中调用其他小程序，一般都是通过system调用来完成，但是用system调用的时候无法获取被调用程序的输出，那如何将一个程序的输出赋值给awk变量呢？答案就是通过getline！

    CodingAnts@ubuntu:~/awk$ awk 'BEGIN{"pwd" | getline dir; print dir}'
    /home/CodingAnts/awk

执行linux的 **pwd**命令，并通过管道输出给getline，再把输出值赋值给awk变量dir，最后打印出变量dir。这样，就实现了将程序的输出值赋值给awk变量了。

关于如何将一个程序的输出值赋值给awk，总结下就是采用下述方式：

    "需要执行的程序" | getline awk变量名

将上面蓝色和红色部分替换成自己需要的程序和变量名即可，例如上面示例中的程序即为pwd，变量为dir，为了加强印象，再举一例：

```shell
    CodingAnts@ubuntu:~/awk$ date
    Fri Mar 23 21:39:05 PDT 2012
    CodingAnts@ubuntu:~/awk$ awk 'BEGIN{"date" | getline d; split(d,a); print a[1]}'
    Fri
```

Linux中的**date**命令输出当前的日期信息，具体参见上面输出。上面的示例先执行Linux的date命令获取日期信息，通过管道输出给geiline，并赋值给awk的变量d，再以默认的空格为分割方式，将变量拆分到awk数组a中，数组a中每一项保存了date输出信息的一部分，**a数组下标从1开始**，要输出星期信息，则print a[1]，如果需要输出其他信息，更改下标值即可。


## awk内建变量示例详解之字段分隔符FS

 在《[awk文件处理方式——记录和字段][0]》中介绍了**awk**对于数据的处理方式，awk默认按照换行符分割输入，每读取一条输入信息后以$0来表示，若程序中进一步使用$1、$2…$NF等内建变量的时候，awk会按照默认的字段分隔符来切割$0，并将字段值分别保存在$1、$2等变量中。

 awk中默认的字段分隔符为空白（空格或者tab） ，awk提供两种方式修改默认的字段分隔符，一种是命令行中的-F选项（示例请参考《[awk命令语法详解][1]》），另一种就是通过修改awk内建的字段分隔符变量：**FS**。

awk的字段分隔符可以为**单个字符**也可以为**正则表达式**！请看下面的示例：

```shell
    CodingAnts@ubuntu:~/awk$ date > dt | cate dt
    Fri Mar 23 23:18:14 PDT 2012
    CodingAnts@ubuntu:~/awk$ cat dt | > awk '{for(i=1; i<=NF; i++) print $i}'> 
    Fri
    Mar
    23
    23:18:14
    PDT
    2012
    CodingAnts@ubuntu:~/awk$ cat dt | > awk 'BEGIN {FS=":"} {for(i=1; i<=NF; i++) print $i}'> 
    Fri Mar 23 23
    18
    14 PDT 2012
    CodingAnts@ubuntu:~/awk$ cat dt | > awk 'BEGIN {FS="[: ]"} {for(i=1; i<=NF; i++) print $i}' > 
    Fri
    Mar
    23
    23
    18
    14
    PDT
    2012
```

首先通过date命令获取日期信息，并重定向输出到dt文件中，再通过cat 命令显示出来，这样做的目的主要是保证下面命令中的日期信息是一致，如果你要保持和本示例一样的效果，那就新建个dt文件，然后手动输入上面的日期时间信息即可。

示例中红色显示的awk指令采用awk默认的字段分隔符对date的输出信息进行分割，awk默认按照空白来进行分割，这里共将date的输出信息分为6段，可以分别用$1-$6来获取。

蓝色显示的awk指令，修改了awk的内建的字段分隔符变量FS，这里采用**单个字符**（冒号）的分割方式对记录进行分割，共分割出3个字段，分别以$1、$2、$3来表示。

紫色显示的awk指令，也修改了awk的内建的字段分隔符变量FS，这里采用的是**正则表达式**的分割方式，冒号和空格都可以作为分割方式，因此date的输出信息被分割为8个字段。

## awk如何处理连续出现的分隔符

```shell
    CodingAnts@ubuntu:~/awk$ echo "a  b :4::3" | awk 'BEGIN{FS="[ :]"} {for(i=1; i<=NF; i++) print "$"i"="$i}'
    $1=a
    $2=
    $3=b
    $4=
    $5=4
    $6=
    $7=3
    CodingAnts@ubuntu:~/awk$ echo "a  b :4::3" | awk 'BEGIN{FS="[ :]**> +**> "} {for(i=1; i<=NF; i++) print "$"i"="$i}'
    $1=a
    $2=b
    $3=4
    $4=3
```

对于连续出现的分隔符，awk完全按照FS的设定进行，上面示例中均设置FS为一个正则表达式，不同之处是一个**+**的区别，这里的+是扩展正则表达式中的一个元字符，表示前面中括号中的字符可以出现1次或者多次，因此下面的awk指令可以正确的将输入分割为四个字段，而前一个awk指令则分割出了7个字段，其中有三个字段为空，这个主要是FS使用的正则表达式不同所导致。

    awk '!_[$0]++{print}' 去重


[0]: http://www.letuknowit.com/topics/20120318/the-records-and-fields-of-awk.html
[1]: http://www.letuknowit.com/topics/20120320/awk-command-syntax-and-detailed-examples.html