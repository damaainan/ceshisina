## 自己动手实现 Shell 多进程管道符

来源：<https://juejin.im/post/5bcd33456fb9a05d2f36e799>

时间：2018年10月22日

一篇技术文章如果仅仅是理论上讲得天花乱坠，却不能自己撸出东西来，那么它写的再好，也只能算纸上谈兵。继上一篇 [《深入 Shell 管道符的内部原理》][6] 收到大量读者粉丝的点赞之后，本篇我们自己来实现一下管道符的功能。比如我们将支持下面的复杂指令，有很多个管套符串起来的一系列指令。

```
$ cmd1 | cmd2 | cmd3 | cmd4 | cmd5
```

我们要使用 Python 语言，因为 Go 和  Java 语言都不支持 fork 函数。我们最终需要的是下面这张图，这张图很简单，但是为了构造出这张图，是需要费一番功夫的。


![][0]


程序的代码文件名是  [pipe.py][7]，程序的运行形式如下

```
python pipe.py "cat pipe.py | grep def | wc -l"
```

统计 [pipe.py][7] 文件代码中包含 def 单词的个数，输出

```
3
```
### 指令执行

每一条指令的运行都需要至少携带一个管道，左边的管道或者右边的管道。第一个指令和最后一个指令只有一个管道，中间的指令有两个管道。管道的标识是它的一对读写描述符（r, w）。


![][1]


左边管道的读描述符 left_pipe[0] 对接进程的标准输入。右边管道的写描述符 right_pipe[1] 对接进程的标准输出。调整完描述符后，就可以使用 exec 函数来执行指令。


![][2]


```python
def run_cmd(cmd, left_pipe, right_pipe):
    if left_pipe:
        os.dup2(left_pipe[0], sys.stdin.fileno())
        os.close(left_pipe[0])
        os.close(left_pipe[1])
    if right_pipe:
        os.dup2(right_pipe[1], sys.stdout.fileno())
        os.close(right_pipe[0])
        os.close(right_pipe[1])
    # 分割指令参数
    args = [arg.strip() for arg in cmd.split()]
    args = [arg for arg in args if arg]
    try:
        # 传入指令名称、指令参数数组
        # 指令参数数组的第一个参数就是指令名称
        os.execvp(args[0], args)
    except OSError as ex:
        print "exec error:", ex
```
### 

### 进程关系

shell 需要运行多个进程，就必须用到 fork 函数来创建子进程，然后使用子进程来执行指令。


![][3]

子又生孙，孙又生子，子子孙孙无穷尽也。理论上使用管道可以串接非常多的进程输入输出流。

```python
# 指令的列表以及下一条指令左边的管道作为参数
def run_cmds(cmds, left_pipe):
    # 取出指令串的第一个指令，即将执行这第一个指令
    cur_cmd = cmds[0]
    other_cmds = cmds[1:]
    # 创建管道
    pipe_fds = ()
    if other_cmds:
        pipe_fds = os.pipe()
    # 创建子进程
    pid = os.fork()
    if pid < 0:
        print "fork process failed"
        return
    if pid > 0:
        # 父进程来执行指令
        # 同时传入左边和右边的管道(可能为空)
        run_cmd(cur_cmd, left_pipe, pipe_fds)
    elif other_cmds:
        # 莫忘记关闭不再使用的描述符
        if left_pipe:
            os.close(left_pipe[0])
            os.close(left_pipe[1])
        # 子进程递归继续执行后续指令，携带新创建的管道
        run_cmds(other_cmds, pipe_fds)

```
### 启动脚本

需要对命令行参数按竖线进行分割得出多条指令，开始进入递归执行

```python
def main(cmdtext):
    cmds = [cmd.strip() for cmd in cmdtext.split("|")]
    # 第一条指令左边没有管道
    run_cmds(cmds, ())
    
if __name__ == '__main__':
    main(argv[1])
```
### 观察进程关系

因为例子中的几条指令执行时间太短，无法通过 ps 命令来观察进程关系。所以我们在代码里加了一句调试用的输出代码，输出当前进程执行的指令名称、进程号和父进程号。

```python
def run_cmd(cmd, left_pipe, right_pipe):
   print cmd, os.getpid(), os.getppid()
   ...
```

运行脚本时观察输出

```
$ python pipe.py "cat pipe.py | grep def | wc -l"
cat pipe.py 49782 4503
grep def 49783 49782
wc -l 49784 49783
       3
```

从输出中可以明显看出父子进程的关系，第 N 条指令进程是第 N+1 条指令进程的父进程。在 run_cmds 函数中，fork 出子进程后由父进程来负责执行当前指令，剩余的指令交给子进程执行。所以才形成了上面的进程关系。读者可以尝试调整交互执行顺序，让子进程负责执行当前指令，然后再观察输出

```
$ python pipe.py "cat pipe.py | grep def | wc -l"
cat pipe.py 49949 49948
grep def 49950 49948
wc -l 49951 49948
       3
```

![][4]


你会发现这三个指令进程都共享同一个父进程，这个父进程就是 Python 进程。如上图所示，我们平时使用的 shell 在执行指令的时候形成的进程关系都是这种形式的，这种形式在逻辑结构上看起来更加清晰。

需要上面的完整源代码，请关注下面的公众号，在里面回复「管道」即可得到源码。

[6]: https://link.juejin.im?target=https%3A%2F%2Fjuejin.im%2Fpost%2F5bc98b36f265da0af93b34c6
[7]: https://link.juejin.im?target=http%3A%2F%2Fpipe.py
[8]: https://link.juejin.im?target=http%3A%2F%2Fpipe.py
[0]: ../img/1669ac3edf68365c.png
[1]: ../img/1669ac3bad0ef3e1.png
[2]: ../img/1669ac34eb1ff102.png
[3]: ../img/1669ac31198b2d3a.png
[4]: ../img/1669ac2d0da39daf.png
