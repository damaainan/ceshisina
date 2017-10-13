# Linux 信号介绍 

   发表于 2016-12-04   | 

是内容受限时的一种异步通信机制

1. 首先是用来通信的
1. 是异步的
1. 本质上是 int 型的数字编号，早期Unix系统只定义了32种信号，Ret hat7.2支持64种信号，编号0-63(SIGRTMIN=31，SIGRTMAX=63)
1. 信号是用户在终端输入或者按下键盘
1. 硬件异常后操作系统向内核发送的
1. 用户使用 kill 命令发出

### 信号的种类

1. 可靠性分类为：可靠信号和不可靠信号
1. 时间分类为：实时信号和非实时信号

### 进程对信息的处理

1. 忽略信号，其中 `SIGKILL` 和 `SIGSTOP` 这两个信号是不能忽略的
1. 捕捉信号，定义信号处理函数，当信号捕捉到时，处理相应的处理函数
1. 执行默认操作，Linux 对每种信息做了规定的默认操作。注：进程对实时信号的缺省处理都是进程终止

### 信号的发送

发送信息的函数：

1. kill
1. raise
1. sigqueue
1. alarm
1. setitimer
1. abort

### 参考文章

[信号上][0]

[信号下][1]

[0]: http://www.ibm.com/developerworks/cn/linux/l-ipc/part2/index1.html
[1]: http://www.ibm.com/developerworks/cn/linux/l-ipc/part2/index2.html