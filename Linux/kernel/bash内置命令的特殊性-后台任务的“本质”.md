## bash内置命令的特殊性，后台任务的"本质"

来源：[http://www.cnblogs.com/f-ck-need-u/p/9183819.html](http://www.cnblogs.com/f-ck-need-u/p/9183819.html)

时间 2018-06-14 16:52:00

 
本文解释bash内置命令的特殊性、前台、后台任务的"本质"，以及前、后台任务和bash进程、终端的关系。网上没类似的资料，所以都是自己的感悟和总结，如有错误，120分的期待盼请指正。
 
因为要详细分析每一个涉及到的内容，我用了很多示例，所以结论比较分散。因此在文章的结尾，我将这些结论大概做了个总结。
 
## 1.引子：一个示例
 
首先通过一个示例做个引子。
 
当直接在当前bash环境下执行一个普通命令，这个普通命令的进程会挂在当前bash进程之下(即父进程为当前bash进程)。
 
例如：
 
``` 
# 在窗口1执行：
[root@xuexi ~]# sleep 30

# 在窗口2查看sleep进程信息：
[root@xuexi ~]# pstree -p | grep slee[p]
           |-sshd(1145)---sshd(5230)-+-bash(5232)---sleep(5599)
```
 
如果，在当前bash环境下将普通命令放入后台执行，这个命令的进程还是会挂在当前bash进程下。
 
如果是在当前bash环境下执行一个内置命令呢？因为是内置命令，它不会有自己的进程(原因后文解释)。
 
``` 
# 窗口1查询当前bash进程信息
[root@xuexi ~]# pstree -p | grep bas[h]
           |-sshd(1145)---sshd(5230)-+-bash(5232)---sleep(5642)
           |                         `-bash(5557)-+-grep(5644)

# 窗口2执行bash内置命令if
[root@xuexi ~]# if true;then sleep 30;fi

# 回到窗口1查询当前bash进程信息，和前面是一样的
[root@xuexi ~]# pstree -p | grep bas[h]
           |-sshd(1145)---sshd(5230)-+-bash(5232)---sleep(5642)
           |                         `-bash(5557)-+-grep(5644)
```
 
发现bash进程没有任何变化。
 
再如果一次，将bash内置命令放入后台执行呢？
 
![][0]
 
例如：
 
``` 
# 当前bash进程信息
[root@xuexi ~]# pstree -p | grep bas[h]
           |-sshd(1145)---sshd(5230)-+-bash(5232)
           |                         `-bash(5557)-+-grep(5634)

[root@xuexi ~]# if true;then sleep 30;fi &
[1] 5635

# 再次查看bash进程信息
[root@xuexi ~]# pstree -p | grep bas[h]
           |-sshd(1145)---sshd(5230)-+-bash(5232)---bash(5635)---sleep(5636)
           |                         `-bash(5557)-+-grep(5638)
```
 
结果发现，多了一个bash进程。为什么会如此？
 
## 2.bash进程和bash内置命令
 
当登陆Linux系统时，会为用户分配一个shell。如果在/etc/passwd中该用户配置的shell为 /bin/bash  ，那么就为用户分配一个bash shell。
 
``` 
[root@xuexi ~]# head -1 /etc/passwd
root:x:0:0:root:/root:/bin/bash
```
 
当登陆用户的身份审核通过后，就会加载bash进程，bash进程再加载它的各个配置文件(/etc/profile、/etc/profile.d/*.sh、~/.bashrc等)，从而配置好bash的 **`执行环境`**  。注意"执行环境"这个词，它将贯穿本文。
 
再来说bash内置命令。
 
bash内置命令和普通的命令都能在bash环境下执行，并实现它们对应的功能。但它们却有很大区别，最典型的一个区别是ps等工具能捕捉到普通命令的进程，却捕捉不到bash内置命令的进程。
 
那么哪些是bash内置命令呢？只要查它的man文档时，给出bash文档的都是bash内置命令，如cd、declare、read等。但不代表man时不是bash手册的就不是内置命令，例如kill、echo、pwd、test等。
 
以下是内置命令列表。
 
``` 
bash, :, ., [, alias, bg, bind, break, builtin, caller, cd, command, compgen, complete, compopt, continue, declare, dirs, disown, echo, enable, eval, exec, exit, export, false, fc, fg, getopts, hash, help, history, jobs, kill, let, local, logout, mapfile, popd, printf, pushd, pwd, read, readonly, return, set, shift, shopt, source, suspend, test, times, trap, true, type, typeset, ulimit, umask, unalias, unset, wait
```
 
除此之外，还有一些保留关键字也是bash内置命令。包括：
 
``` 
! case do done elif else esac fi for function if in select then until while { } time [[ ]]
```
 
#### 那么bash内置命令和bash进程有什么关系？
 
bash内置命令和普通命令不一样。普通命令可以直接执行，不依赖于某种执行环境。例如，sleep命令，可以直接以pid=1的init/systemd为父进程而执行。那些daemon类的服务进程更是如此，它们不依赖于终端，也不依赖于执行环境，只要给它们配置好，就可以直接找init/systemd当爹。
 
而bash内置命令，既然称之为"bash内置命令"，顾名思义是bash内置的。当我们在当前bash环境下执行bash内置命令，经过shell的一轮解析之后，发现这是个bash内置命令，于是直接在当前bash进程的内部调用执行它们。所以bash内置命令自身是没有进程的。
 
换句话说，bash内置命令的执行是由它们的bash爹带着它们执行的。这个bash爹是一个负责任的好爹，什么都帮它们准备好，还带着它们一起浪。但正因为爹太负责，把孩子们给宠坏了，这些bash内置命令无论什么时候执行都必须先找好bash爹为它们提供执行环境。
 
于是问题出现了，如果它们的bash爹死了怎么办(即bash进程被杀或者已经结束)？这个问题并不像想象中的那么简单。下面会非常详细地结合后台任务来分析它。
 
## 3.前台任务和后台任务的本质
 
后台任务，专业术语是"作业"。它是指"能选择性地停止、暂停、继续运行某个进程的能力"。这和我们理解的"放进看不见的后台默默地执行"好像有点区别啊？这无关紧要。
 
关于后台任务，首先要说明的是后台任务是怎么实现的：通过bash和系统终端的驱动共同提供的交互式界面来实现后台作业能力。在bash手册中是如此解释的：
 
``` 
A user typically employs this facility via an interactive interface supplied jointly by the operating system kernel’s terminal driver and Bash.
```
 
其实，当一个进程的进程组号和当前终端的进程组号相同，则这个进程是前台进程，受键盘影响，可以读、写终端。当一个进程的进程组号和当前终端的进程组号不同时，则这个进程是后台进程，它们不受键盘影响，读、写终端时需要发送特定的信号。
 
换句话说，后台任务依赖于当前所在的终端。但当前终端往往会和一个bash进程关联绑定，该bash进程具有当前终端的控制权  ，所以杀掉终端进程和杀掉当前所在bash进程都能结束一个终端，但是它们却有本质上的区别，后文我用了 **`军营、将军来比喻当前终端、当前bash进程的关系`**  ，这就是它们的区别，详细内容见后文一步一步的分析。
 
#### 当一个进程是前台进程时，它是在当前bash进程下执行的，此时该bash进程失去控制权，也就是被阻塞。当进程进入后台，意味着它会离开当前bash环境，进入后台执行环境，因为只有离开当前bash环境，才能立刻将终端的控制权还给当前bash进程。
 
于是，可以将当前终端进程、当前bash进程、前台进程、后台进程的关系大概理解为下图形式：
 
![][1]
 
## 3.1 普通命令和bash内置命令放入后台的区别
 
回头看本文开头的引子，为什么`sleep 30 &`的sleep进程是在当前bash进程下的，而`if true;then sleep 30;fi &`则会新开一个bash进程？
 
上一小节中说过：bash内置命令的执行依赖于bash进程提供的执行环境，而普通命令则没有依赖性。
 `sleep 30 &`的sleep是普通命令，不依赖于bash进程，所以它可以直接进入后台，但它毕竟是后台任务，它 **`暂时`**  还依赖于当前终端，且受当前bash进程的控制(例如能放回前台，能被bash查看后台任务信息)，所以它 **`暂时`**  还必须挂在当前bash进程下。之所以是暂时，稍后就解释。
 
``` 
[root@xuexi ~]# sleep 30 &
[1] 6300

[root@xuexi ~]# pstree -p | grep bas[h]
           |-sshd(1145)-+-sshd(5230)---bash(5557)-+-grep(6302)
           |            `-sshd(6047)---bash(6049)---sleep(6300)
```
 
而`if true;then sleep 30;fi &`是bash内置命令要放入后台，放入后台意味着它要离开当前bash环境，所以它在进入后台开始执行前，必须新找一个bash爹为它提供执行环境，所以它新生成了一个bash进程。
 
``` 
# 当前bash进程信息
[root@xuexi ~]# pstree -p | grep bas[h]
           |-sshd(1145)---sshd(5230)-+-bash(5232)
           |                         `-bash(5557)-+-grep(5634)

[root@xuexi ~]# if true;then sleep 30;fi &
[1] 5635

# 再次查看bash进程信息
[root@xuexi ~]# pstree -p | grep bas[h]
           |-sshd(1145)---sshd(5230)-+-bash(5232)---bash(5635)---sleep(5636)
           |                         `-bash(5557)-+-grep(5638)
```
 
补充一个小知识：其实这个新的bash爹和当前bash进程是不一样的，这个新bash爹是非交互式的shell，可以直接使用`kill -15`杀掉这个新bash进程。而交互式shell下，在没有设置任何陷阱(trap)时，默认是忽略TERM信号的，无法直接`kill -15`杀掉一个当前活动的bash进程。
 
所以，完善一下上面的图：
 
![][2]
 
## 3.2 杀掉后台任务的父进程
 
还是围绕普通命令的后台和bash内置命令的后台任务来说明。
 
上一小节在解释普通命令放入后台执行时，后台进程会挂在当前bash进程下，还特地加上了" **`暂时`**  "两个字。其实，普通命令的进程进入后台时，它不是一定要挂在当前bash进程下的，甚至它不再依赖于终端，之所以还暂时挂在当前bash进程下，是因为它还是个后台任务，还需要被当前bash管理  。例如将其放回前台，查看后台任务列表，如果不在当前bash进程下，当前bash进程必然无法管理它。
 
如果将当前bash进程或者当前终端进程杀掉，对普通命令的后台任务会造成什么影响？试试看。
 
``` 
# 在窗口1执行：
[root@xuexi ~]# sleep 67 & 
[1] 6464

# 在窗口2查看sleep进程的父进程
[root@xuexi ~]# pstree -p | grep sleep
           |            `-sshd(6047)---bash(6049)---sleep(6464)

# 杀掉父进程：bash进程。因为是交互式shell，所以必须使用SIGKILL信号
[root@xuexi ~]# kill -9 6049

# 再查看sleep进程
[root@xuexi ~]# pstree -p | grep sleep
           |-sleep(6464)
```
 
从结果中不难发现，杀掉后台sleep进程的父进程bash(因为和终端绑定，所以也是杀掉终端)后，sleep进程没有随之中止，而是挂在init/systemd下  。前面分析过，它是 **`暂时`**  挂在bash进程下的，它不依赖于bash进程，也不依赖于终端。
 
再来分析bash内置命令放入后台时，杀掉它的父进程会如何。
 
以`if true;then sleep 55;fi &`为例，这里还要再细致一点地分析它的父进程。
 
``` 
[root@xuexi ~]# if true;then sleep 55;fi &
[1] 6520
[root@xuexi ~]# pstree -p | grep sleep
           |         `-sshd(6476)---bash(6478)-+-bash(6520)---sleep(6521)
```
 
这里的sleep有两个父bash进程，其中pid=6520的是新生成的bash爹，pid=6478的是当前bash进程。是否注意到上面if放入后台时返回的进程号为6520，这个进程号对应的是新bash爹。换句话说，pid=6520的bash进程对应的是if命令，而这才是后台进程，但因为sleep在if所在bash进程的进程组内，所以sleep也是后台进程。
 
#### 所以，当杀掉pid=6520的bash进程后，表示杀掉的是普通命令sleep后台的父进程，也就是if结构，所以sleep进程会直接挂在init/systemd下；当杀掉pid=6478的bash进程后，表示杀掉内置命令if(对应的是pid=6520的bash进程)的父进程，所以if命令的bash爹将带着整个进程组挂在init/systemd下。
 
分别验证它们。
 
``` 
# 杀掉新生成的bash爹
[root@xuexi ~]# if true;then sleep 55;fi &
[1] 6551

[root@xuexi ~]# pstree -p | grep sleep     
           |        `-sshd(6476)---bash(6478)-+-bash(6551)---sleep(6552)

[root@xuexi ~]# kill 6551

# 查看sleep进程，发现确实已经挂在Init/systemd下了
[root@xuexi ~]# pstree -p | grep sleep
           |-sleep(6552)
```
 
``` 
# 杀掉新生成的bash爹的父bash进程，也就是左边那个bash进程

# 在窗口1执行
[root@xuexi ~]# if true;then sleep 55;fi &
[1] 6563

# 在窗口2执行
[root@xuexi ~]# pstree -p | grep sleep    
           |            `-sshd(6476)---bash(6478)-+-bash(6563)---sleep(6564)
[root@xuexi ~]# kill -9 6478

# 窗口2查看sleep进程，发现bash爹带着整个进程组都挂在init/systemd下
[root@xuexi ~]# pstree -p | grep sleep
           |-bash(6563)---sleep(6564)
```
 
如果此时把pid=6563的bash爹杀了，会如何呢？如果前面都理解了的话，这里很容易知道答案。这个进程组是个后台进程组，包括其中的sleep进程，把sleep的父进程杀掉，sleep当然是直接挂在init/systemd下。
 
过程参考下图：
 
![][3]
 
还没完呢。上面杀的都是它们的直系爹bash进程，如果把它们的爷爷杀掉呢？或者直接把终端关掉呢？这时又不一样了。
 
## 3.3 杀掉后台任务所在的终端
 
杀掉终端进程和杀掉当前bash进程对后台任务的影响不一样：杀掉当前bash进程后，后台任务会挂在init/systemd下，而杀掉终端后，后台任务也会中止。
 
来一个示例。
 
``` 
# 窗口1执行
[root@xuexi ~]# sleep 65 &
[1] 7108

# 窗口2查看
[root@xuexi ~]# pstree -p | grep sleep
           |            `-sshd(7014)---bash(7016)---sleep(7108)

# 窗口2杀掉pid=7014，或者直接关掉窗口1
[root@xuexi ~]# kill 7014

# 窗口2再查看sleep进程信息，啥也没有
[root@xuexi ~]# pstree -p | grep sleep
```
 
前面说过，后台任务依赖于当前所在的终端。但当前终端往往会和一个bash进程关联绑定，该bash进程具有当前终端的控制权  ，所以杀掉终端进程和杀掉当前所在bash进程都能结束一个终端，但是它们却又本质上的区别。
 
其实，可以将当前终端、当前bash进程(更确切地说是后台任务的父进程)、后台的关系看作军营、将军、小兵的关系。当军营中分配了一个将军后，军营为将军和小兵提供休息、商量等环境，将军具有军营的控制权，负责管理军营中的一切，包括小兵。如果杀掉军营中的将军，小兵们发现"营中无大王"，于是立刻收拾行李就走，投奔皇帝去了(init/systemd)。但如果直接把军营给炸了，那么将军和小兵将无一幸免，全军覆没。
 
实际上，杀掉终端进程时，终端进程会给自己进程组内的所有进程包括bash进程发送一个SIGHUP信号，正是因为收到这个信号，进程组内的所有进程才会中止。
 
如何让后台进程不依赖于终端？不考虑借助nohup、screen、tmux等第三方工具实现，bash其实也提供了解决方案，有两种方法：
 
#### 1.将后台任务放入子shell。
 
这算是对bash深刻认识后才能想到或真正理解的方法。因为将后台任务放入子shell(子shell下一节说明)，当子shell结束后，其内后台任务会立即挂到init/systemd下，这样就脱离了终端。
 
``` 
[root@xuexi ~]# (sleep 30 &)
[root@xuexi ~]# pstree -p | grep sleep
           |-sleep(2392)
```
 
或者，放进一个脚本(执行脚本也是进入一个子shell)。例如以下是a.sh的内容。
 
``` 
#!/bin/bash

sleep 60 &

sleep 20
```
 
在执行该脚本的20秒内，两个sleep进程都是在a.sh进程下的。
 
``` 
[root@xuexi ~]# pstree -p | grep sleep
           |---sshd(2317)-+-bash(2348)---a.sh(2449)-+-sleep(2450)
           |              |                         `-sleep(2451)
```
 
20秒后，脚本结束，也就是子shell退出，该子shell中的后台sleep将挂在init/systemd下。
 
``` 
[root@xuexi ~]# pstree -p | grep sleep
           |-sleep(2450)
```
 
如果把脚本中的`sleep 20`去掉，那么后台sleep也将是瞬间就挂到init/systemd下的。
 
#### 2.利用bash内置命令disown将任务移出后台或设置为忽略SIGHUP信号。
 
``` 
# 在窗口1执行
[root@xuexi ~]# sleep 60 &   # jobs的作业号码：%1
[root@xuexi ~]# disown %1    # 将后台作业%1移出后台
[root@xuexi ~]# jobs         # 返回空
```
 
当进程disown移出后台后，虽然暂时还挂在bash进程下，但结束终端进程时，该进程将挂到init/systemd下。所以，这样做也将脱离终端。
 
或者，`disown -h`设置后台作业忽略SIGHUP信号。前文说过，当终端进程退出时，将会向终端进程组中的所有进程发送SIGHUP信号，收到这些信号，终端下的所有进程都会终止。
 
``` 
[root@xuexi ~]# sleep 60 &
[root@xuexi ~]# disown -h %1  # 为%1后台作业打上忽略SIGHUP的标记
```
 
然后关闭终端，会发现sleep进程也将挂在init/systemd下。
 
## 4."奇怪"的问题以及解决方案
 
前面验证过杀掉`if true;then sleep 55;fi &`的bash爹的父进程(也就是sleep的爷爷进程)后，bash爹将带着sleep一起挂在init/systemd进程下。
 
但是考虑一个"极端"一点的问题。如果这里不是if，而是while/for/until循环，如果这个命令不是在当前bash下执行，而是在一个脚本中执行，杀掉脚本进程后，会如何？
 
比如，某脚本test.sh内容如下：
 
``` 
#!/bin/bash

while true;do
    sleep 10
done &
```
 
或者如下：
 
``` 
#!/bin/bash

while true;do
    sleep 10
done &

sleep 50
```
 
执行这个脚本时，将有两个test.sh进程，其中一个是test.sh进程自身(称之为进程A)，一个是为while提供bash环境的子shell进程(称之为进程B)。
 
当在脚本运行时，杀掉进程A时或者按下CTRL+C(第二个脚本情况)时，如果查看进程的话，会发现后台永远有一个 "test.sh进程+sleep进程" 在运行。这不是我们想要的结果，我们想要的是脚本结束时，里面的进程也一起结束，而不是有个进程在后台"偷偷地"运行。
 
之所以出现这样的问题，是因为进程A终止后，while的bash爹(进程B)带着while结构里的进程sleep一起挂到init/systemd下了，而且很不幸，这是个while循环，会一直不断地在后台运行。
 
应该怎样解决这种问题？可以将所有的test.sh进程都杀掉，比如使用`killall sleep test.sh`命令。还有更好的方法，见我的另一篇文章，专门写这个问题：如何让shell脚本自杀。
 
## 5.本文的总结
 
 
* 其实本文讲的全是子shell的内容，尽管文中很少出现子shell的字眼。。  
* bash内置命令的执行依赖于bash进程提供的执行环境。  
* 当前bash环境下执行bash内置命令不会新生成bash进程，除非将它放进后台。  
* 杀掉后台任务的父进程，后台任务会挂到pid=1的init/systemd进程下。  
* 终端进程、bash进程和后台任务之间的关系：军营、将军、小兵。 
 
 
* (1).终端进程为bash进程和其他进程提供生存环境。  
* (2).终端进程往往会和一个bash进程绑定，这个bash进程具有终端的控制权，也就是管理军营。  
* (3).杀掉终端的管理bash进程，终端进程也会随之终止。  
* (4).bash进程是后台任务的暂时管理者。当bash进程终止时，后台任务就会挂到pid=1的进程下接受init/systemd的管理。  
   
  
* 杀掉终端进程，会发送SIGHUP信号给终端进程组中的所有进程。收到SIGHUP信号后，这些进程都会终止，包括bash进程和后台任务。  
* 让后台任务脱离终端的方法，除了nohup、screen、tmux等三方工具，bash自身也能实现。只需将后台任务放进子shell中执行即可(最简单的方法`(sleep 30 &)`)，或者用disown命令将后台任务移除后台，或`disown -h`设置后台进程忽略SIGHUP信号。  
* 分析下面的脚本，为什么执行过程中按下 CTRL+C 后，还会时不时地向终端上输出点东西，如何解决这个问题？

``` 
#!/bin/bash
while true;do
sleep 3
echo "hello world! hello world! "
done &
sleep 60
```
  
 
 
最后，本文的姊妹篇：
 


[0]: ../IMG/m2EZRjJ.png 
[1]: ../IMG/rEvABna.png 
[2]: ../IMG/VfEviiu.png 
[3]: ../IMG/Nv6rqae.png 