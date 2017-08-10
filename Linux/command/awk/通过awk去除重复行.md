## awk使用案例：通过awk去除重复行

 重复的数据总是让人各种不爽，占用空间、看起来费劲等等，今天就介绍一个通过**awk**去除文件中重复数据的办法，awk默认是一行行来处理数据的，那我们就重点说说如何通过awk去除文件中的重复行。

首先准备一个文本文件，随便写个文件，包含重复行数据的即可，或者你可以参考我这里的文件：

```shell
CodingAnts@ubuntu:~/awk$ cat dup
hello world
awk
coding ants
hello world
awk
hello world
awk
coding ants
coding ants
```

共有9行，后面6行都是重复的前面的几行，最终的效果应该是只显示上面重点显示的那几行，先来看看效果：

    CodingAnts@ubuntu:~/awk$ awk '!a[$0]++' dup
    hello world
    awk
    coding ants

在《[awk程序指令模型][0]》中介绍了awk的程序指令由模式和操作组成，即Pattern { Action }的形式，如果省略Action，则默认执行 print $0 的操作。

实现去除重复功能的就是这里的Pattern：

**!a[$0]++**

在awk中，对于未初始化的数组变量，在进行数值运算的时候，会赋予初值0，因此a[$0]=0，++运算符的特性是先取值，后加1，因此Pattern等价于

**!0**

而0为假，!为取反，因此整个Pattern最后的结果为1，相当于if(1)，Pattern匹配成功，输出当前记录，对于dup文件，前3条记录的处理方式都是如此。

当读取第4行数据“hello world”的时候，a[$0]=1，取反后的结果为0，即Pattern为0，Pattern匹配失败，因此不输出这条记录，后续的数据以此类推，最终成功实现去除文件中的重复行。

[0]: http://www.letuknowit.com/topics/20120319/awk-program-model.html