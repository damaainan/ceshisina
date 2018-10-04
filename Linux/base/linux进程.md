## linux进程

2017.07.16 18:05*

来源：[https://www.jianshu.com/p/3109d49db4dd](https://www.jianshu.com/p/3109d49db4dd)


  
1. 进程介绍
    1. 进程和程序
    2. 进程层次结构
    3. 进程状态
2. 进程控制块
    1. 进程状态
    2. 进程标志符
    3. 进程之间亲属关系
3. 进程创建与调度
    1. 进程创建
    2. 进程调度
4. 进程间通信
    1. 管道
        1. 匿名管道pipe
        2. 有名管道fifo
    2. 信号
    3. 消息队列
    4. 共享内存
    5. 信号量
    6. 套接字
5. 进程相关命令
    1. ps/pstree
    2. kill/killall
    3. ipcs/ipcrm
6. 其他
    1. 进程/线程context switch消耗
7. 参考


## 1 进程介绍
### 1.1 进程和程序

所谓进程是由正文段（text）、用户数据段（user segment）以及系统数据段（system　segment）共同组成的一个执行环境， 具体如下图所示：


![][0]


进程的组成

### 1.2 进程层次结构

Linux所有进程形成了一颗完整的进程树，Linux在启动时就创建一个称为init的特殊进程，顾名思义，它是起始进程，是祖先，以后诞生的所有进程都是它的后代——或是它的儿子，或是它的孙子。init进程为每个终端(tty)创建一个新的管理进程，这些进程在终端上等待着用户的登录。当用户正确登录后，系统再为每一个用户启动一个shell进程，由shell进程等待并接受用户输入的命令信息，下图是一颗进程树示意图：


![][1]


进程树


通过 pstree  命令或者 ps -ejH  命令可以查看Linux进程树。

　　如果子进程所属的父进程结束了，这个子进程就会变成"孤儿进程"，init进程会负责收养孤儿进程。

　　如果子进程退出的信号父进程没有通过wait或者waitpid处理，那么子进程会释放基本所有占用的资源，除了保留PCB(见后文)之外，这样的进程会变成"僵尸进程"
## 2 进程控制块

Linux进程控制块PCB描述了进程的状态、优先级、地址空间以及可访问的文件等等信息，Linux上使用了一个task_struct结构体描述了这些信息，这个结构体包含的详细信息如下：


* 状态信息：描述进程动态的变化，如就绪态，等待态，僵死态等
* 链接信息： 描述进程的亲属关系，如祖父进程，父进程，养父进程，子进程，兄进程，孙进程等
* 各种标识符：用简单数字对进程进行标识，如进程标识符，用户标识符等
* 进程间通信信息：描述多个进程在同一任务上协作工作，如管道，消息队列，共享内存，套接字等
* 时间和定时器信息：描述进程在生存周期内使用CPU时间的统计、计费等信息等
* 调度信息：描述进程优先级、调度策略等信息
* 文件系统信息：对进程使用文件情况进行记录，如文件描述符，系统打开文件表，用户打开文件表等
* 虚拟内存信息：描述每个进程拥有的地址空间，也就是进程编译连接后形成的空间
* 处理器环境信息：描述进程的执行环境(处理器的各种寄存器及堆栈等)


在进程的整个生命周期中，系统（也就是内核）总是通过PCB对进程进行控制的；当系统创建一个新的进程时，就为它建立一个PCB；进程结束时又收回其PCB，进程随之也消亡。PCB是内核中被频繁读写的数据结构，故应常驻内存。
### 2.1 进程状态

Linux上进程状态如下描述：


* 就绪态（TASK_RUNNING）：正在运行或准备运行，处于这个状态的所有进程组成就绪队列
* 睡眠（或等待）态：分为浅度睡眠态和深度睡眠态
* 浅度睡眠态（TASK_INTERRUPTIBLE）：进程正在睡眠（被阻塞），等待资源有效时被唤醒，不仅如此，也可以由其他进程通过信号 或时钟中断唤醒
* 深度睡眠态（TASK_UNINTERRUPTIBLE）： 与前一个状态类似，但其它进程发来的信号和时钟中断并不能打断它的熟睡，处于uninterruptible sleep状态的进程通常是在等待IO，比如磁盘IO，网络IO，其他外设IO，如果进程正在等待的IO在较长的时间内都没有响应， 在ps命令看到的是处于-D状态的进程，除非等待的事件响应，否则只能重启系统才能干掉这个进程
* 暂停状态（TASK_STOPPED）：进程暂停执行，比如，当进程接收到如下信号后，进入暂停状态：

SIGSTOP-停止进程执行

SIGTSTP-从终端发来信号停止进程

SIGTTIN-来自键盘的中断

SIGTTOU-后台进程请求输出
* 僵死状态（TASK_ZOMBIE）：进程执行结束但尚未消亡的一种状态。此时，进程已经结束且释放大部分资源，但尚未释放其PCB



![][2]


Linux进程状态


其中就绪状态表示进程已经分配到除CPU以外的资源，等CPU调度它时就可以马上执行了。运行状态就是正在运行了，获得包括CPU在内的所有资源。等待状态表示因等待某个事件而没有被执行，这时候不耗CPU时间，而这个时间有可能是等待IO、申请不到足够的缓冲区或者在等待信号。
### 2.2 进程标志符

每个进程有进程标识符、用户标识符、组标识符。进程标识符PID是32位的无符号整数，它被顺序编号：新创建进程的PID通常是前一个进程的PID加1。在Linux上允许的最大PID号是由变量pid_max来指定，可以在内核编译的配置界面里配置0x1000和0x8000两种值，即在4096以内或是32768以内。当内核在系统中创建进程的PID大于这个值时，就必须重新开始使用已闲置的PID号,可以通过cat命令查看系统pid_max的值。

```c
cat /proc/sys/kernel/pid_max
32768

```

另外，每个进程都属于某个用户组。task_struct结构中定义有用户标识符UID（User Identifier）和组标识符GID（Group Identifier）。它们同样是简单的数字，这两种标识符用于系统的安全控制。系统通过这两种标识符控制进程对系统中文件和设备的访问。
### 2.3 进程之间亲属关系

系统创建的进程具有父/子关系。因为一个进程能创建几个子进程，而子进程之间有兄弟关系。一个进程可能有两个父亲，一个为亲生父亲，一个为养父。因为父进程有可能在子进程之前销毁，就得给子进程重新找个养父，但大多数情况下，生父和养父是相同的。进程间亲属关系如下图所示：


![][3]


Linux进程关系图

## 3 进程创建与调度
### 3.1 进程创建

Linux首先通过fork()通过拷贝当前进程创建一个子进程。然后，exec()函数负责读取可执行文件并将其载入进程的地址空间开始运行。

　　新进程是通过克隆父进程（当前进程）而建立的。fork() 和 clone()（用于线程）系统调用可用来建立新的进程。当这两个系统调用结束时，内核在内存中为新的进程分配新的PCB，同时为新进程要使用的堆栈分配物理页。Linux 还会为新进程分配新的进程标识符。然后，新的PCB地址保存在链表中，而父进程的PCB内容被复制到新进程的 PCB中。

　　在克隆进程时，Linux 允许父子进程共享相同的资源。可共享的资源包括文件、信号处理程序和进程地址空间等。当某个资源被共享时，该资源的引用计数值会增加 1，从而只有在两个进程均终止时，内核才会释放这些资源。
### 3.2 进程调度

CFS（完全公平调度器）是Linux内核2.6.23版本开始采用的进程调度器，它的基本原理是这样的：设定一个调度周期（sched_latency_ns），目标是让每个进程在这个周期内至少有机会运行一次，换一种说法就是每个进程等待CPU的时间最长不超过这个调度周期；然后根据进程的数量，大家平分这个调度周期内的CPU使用权，由于进程的优先级即nice值不同，分割调度周期的时候要加权；每个进程的累计运行时间保存在自己的vruntime字段里，哪个进程的vruntime最小就获得本轮运行的权利。

　　新进程的vruntime初值的设置与两个参数有关：


* sched_child_runs_first：规定fork之后让子进程先于父进程运行;
* sched_features的START_DEBIT位：规定新进程的第一次运行要有延迟。

　　具体实现时，CFS通过每个进程的虚拟运行时间（vruntime）来衡量哪个进程最值得被调度。CFS中的就绪队列是一棵以vruntime为键值的红黑树，虚拟时间越小的进程越靠近整个红黑树的最左端。因此，调度器每次选择位于红黑树最左端的那个进程，该进程的vruntime最小。

　　每个时钟周期内一个进程的虚拟运行时间是通过下面的方法计算的：


```c
一次调度间隔的虚拟运行时间=实际运行时间*（NICE_0_WEIGHT/权重）

```

其中，NICE_0_WEIGHT是nice为0时的权重。也就是说，nice值为0的进程实际运行时间和虚拟运行时间相同。通过这个公式可以看到，权重越大的进程获得的虚拟运行时间越小，那么它将被调度器所调度的机会就越大。
## 4. 进程间通信

每个进程各自有不同的用户地址空间，任何一个进程的全局变量在另一个进程中都看不到，所以进程之间要交换数据必须通过内核，在内核中开辟一块缓冲区，进程1把数据从用户空间拷到内核缓冲区，进程2再从内核缓冲区把数据读走，内核提供的这种机制称为进程间通信，如下图所示：


![][4]


进程间通信

### 4.1 管道
#### 4.1.1 匿名管道pipe


* 匿名管道是半双工的，数据只能向一个方向流动；需要双方通信时，需要建立起两个管道
* 只能用于父子进程或者兄弟进程之间(具有亲缘关系的进程);
* 单独构成一种独立的文件系统：管道对于管道两端的进程而言，就是一个文件，但它不是普通的文件，它不属于某种文件系统，而是自立门户，单独构成一种文件系统，并且只存在与内存中。
* 数据的读出和写入：一个进程向管道中写的内容被管道另一端的进程读出。写入的内容每次都添加在管道缓冲区的末尾，并且每次都是从缓冲区的头部读出数据。如果管道中没有数据，读操纵将被阻塞；如果管道buffer写满了，写操纵将会被阻塞。


管道是基于文件描述符的通信方式。当一个管道建立时，它会创建两个文件描述符fd[0]和fd[1]。其中fd[0]固定用于读管道，而fd[1]固定用于写管道,一般文件I/O的函数都可以用来操作管道。下面是一个父子进程通过匿名管道通信的代码示例：

```c
#include <unistd.h>
#include <sys/types.h>
main()
{
    int pipe_fd[2];
    pid_t pid;
    char r_buf[4];
    char** w_buf[256];
    int childexit=0;
    int i;
    int cmd;
    
    memset(r_buf,0,sizeof(r_buf));
    if(pipe(pipe_fd)<0)
    {
        printf("pipe create error\n");
        return -1;
    }
    if((pid=fork())==0)
    //子进程：解析从管道中获取的命令，并作相应的处理
    {
        printf("\n");
        close(pipe_fd[1]);
        sleep(2);
        
        while(!childexit)
        {   
            read(pipe_fd[0],r_buf,4);
            cmd=atoi(r_buf);
            if(cmd==0)
            {
printf("child: receive command from parent over\n now child process exit\n");
                childexit=1;
            }
            
               else if(handle_cmd(cmd)!=0)
                return;
            sleep(1);
        }
        close(pipe_fd[0]);
        exit();
    }
    else if(pid>0)
    //parent: send commands to child
    {
    close(pipe_fd[0]);
    w_buf[0]="003";
    w_buf[1]="005";
    w_buf[2]="777";
    w_buf[3]="000";
    for(i=0;i<4;i++)
        write(pipe_fd[1],w_buf[i],4);
    close(pipe_fd[1]);
    }   
}
//下面是子进程的命令处理函数（特定于应用）：
int handle_cmd(int cmd)
{
if((cmd<0)||(cmd>256))
//suppose child only support 256 commands
    {
    printf("child: invalid command \n");
    return -1;
    }
printf("child: the cmd from parent is %d\n", cmd);
return 0;
}

```
#### 4.1.2 有名管道fifo

无名管道，由于没有名字，只能用于亲缘关系的进程间通信.。为了克服这个缺点，提出了有名管道(FIFO)。FIFO不同于无名管道之处在于它提供了一个路径名与之关联，以FIFO的文件形式存在于文件系统中，这样，即使与FIFO的创建进程不存在亲缘关系的进程，只要可以访问该路径，就能够彼此通过FIFO相互通信，因此，通过FIFO不相关的进程也能交换数据。有名管道的名字存在于文件系统中，内容存放在内存中。有名管道创建的API是：


![][5]


有名管道创建


下面创建一个FIFO，并且写入数据：

```c
#include <sys/types.h>
#include <sys/stat.h>
#include <errno.h>
#include <fcntl.h>
#define FIFO_SERVER "/tmp/fifoserver"
main(int argc,char** argv)
//参数为即将写入的字节数
{
    int fd;
    char w_buf[4096*2];
    int real_wnum;
    memset(w_buf,0,4096*2);
    if((mkfifo(FIFO_SERVER,O_CREAT|O_EXCL)<0)&&(errno!=EEXIST))
        printf("cannot create fifoserver\n");
    if(fd==-1)
        if(errno==ENXIO)
            printf("open error; no reading process\n");
        
        fd=open(FIFO_SERVER,O_WRONLY|O_NONBLOCK,0);
    //设置非阻塞标志
    //fd=open(FIFO_SERVER,O_WRONLY,0);
    //设置阻塞标志
    real_wnum=write(fd,w_buf,2048);
    if(real_wnum==-1)
    {
        if(errno==EAGAIN)
            printf("write to fifo error; try later\n");
    }
    else 
        printf("real write num is %d\n",real_wnum);
    real_wnum=write(fd,w_buf,5000);
    //5000用于测试写入字节大于4096时的非原子性
    //real_wnum=write(fd,w_buf,4096);
    //4096用于测试写入字节不大于4096时的原子性
    
    if(real_wnum==-1)
        if(errno==EAGAIN)
            printf("try later\n");
}

```

下面读取这个FIFO写入的数据：

```c
#include <sys/types.h>
#include <sys/stat.h>
#include <errno.h>
#include <fcntl.h>
#define FIFO_SERVER "/tmp/fifoserver"
main(int argc,char** argv)
{
    char r_buf[4096*2];
    int  fd;
    int  r_size;
    int  ret_size;
    r_size=atoi(argv[1]);
    printf("requred real read bytes %d\n",r_size);
    memset(r_buf,0,sizeof(r_buf));
    fd=open(FIFO_SERVER,O_RDONLY|O_NONBLOCK,0);
    //fd=open(FIFO_SERVER,O_RDONLY,0);
    //在此处可以把读程序编译成两个不同版本：阻塞版本及非阻塞版本
    if(fd==-1)
    {
        printf("open %s for read error\n");
        exit(); 
    }
    while(1)
    {
        
        memset(r_buf,0,sizeof(r_buf));
        ret_size=read(fd,r_buf,r_size);
        if(ret_size==-1)
            if(errno==EAGAIN)
                printf("no data avlaible\n");
        printf("real read bytes %d\n",ret_size);
        sleep(1);
    }   
    pause();
    unlink(FIFO_SERVER);
}

```
### 4.2 信号


* 信号是进程间通信机制中唯一的异步通信机制，可以看作是异步通知，通知接收信号的进程有哪些事情发生了。信号机制经过POSIX实时扩展后，功能更加强大，除了基本通知功能外，还可以传递附加信息。
* 如果该进程当前并未处于执行状态，则该信号就有内核保存起来，知道该进程回复执行并传递给它为止。
* 如果一个信号被进程设置为阻塞，则该信号的传递被延迟，直到其阻塞被取消是才被传递给进程。


信号产生有两个来源：硬件来源(比如我们按下了键盘或者其它硬件故障)；软件来源，最常用发送信号的系统函数是kill, raise, alarm和setitimer以及sigqueue函数，软件来源还包括一些非法运算等操作。

Linux系统中常用信号：

（1）SIGHUP：用户从终端注销，所有已启动进程都将收到该进程。系统缺省状态下对该信号的处理是终止进程。

（2）SIGINT：程序终止信号。程序运行过程中，按Ctrl+C键将产生该信号。

（3）SIGQUIT：程序退出信号。程序运行过程中，按Ctrl+\键将产生该信号。

（4）SIGBUS和SIGSEGV：进程访问非法地址。

（5）SIGFPE：运算中出现致命错误，如除零操作、数据溢出等。

（6）SIGKILL：用户终止进程执行信号。shell下执行kill -9发送该信号。

（7）SIGTERM：结束进程信号。shell下执行kill 进程pid发送该信号。

（8）SIGALRM：定时器信号。

（9）SIGCLD：子进程退出信号。如果其父进程没有忽略该信号也没有处理该信号，则子进程退出后将形成僵尸进程。

进程可以通过三种方式来响应一个信号：（1）忽略信号，即对信号不做任何处理，其中，有两个信号不能忽略：SIGKILL及SIGSTOP；（2）捕捉信号。定义信号处理函数，当信号发生时，执行相应的处理函数；（3）执行缺省操作，Linux对每种信号都规定了默认操作。

下面是一个alarm信号使用例子：

```c
#include <unistd.h>
#include <sys/types.h>
#include <stdlib.h>
#include <stdio.h>
#include <signal.h>

static int alarm_fired = 0;

void ouch(int sig)
{
    alarm_fired = 1;
}

int main()
{
    //关联信号处理函数
    signal(SIGALRM, ouch);
    //调用alarm函数，5秒后发送信号SIGALRM
    alarm(5);
    //挂起进程
    pause();
    //接收到信号后，恢复正常执行
    if(alarm_fired == 1)
        printf("Receive a signal %d\n", SIGALRM);
    exit(0);
}

```

注：如果父进程在子进程的信号到来之前没有事情可做，我们可以用函数pause（）来挂起父进程，直到父进程接收到信号。当进程接收到一个信号时，预设好的信号处理函数将开始运行，程序也将恢复正常的执行。这样可以节省CPU的资源，因为可以避免使用一个循环来等待。
### 4.3 消息队列

消息队列是消息的链表，存放在内核中并由消息队列标识符标识。在某个进程往一个队列写入消息之前，并不需要另外某个进程在该队列上等待消息的到达。这跟管道和FIFO是相反的，对后两者来说，除非读出者已存在，否则先有写入者是没有意义的。

　　管道和FIFO都是随进程持续的，XSI IPC(消息队列、信号量、共享内存)都是随内核持续的。当一个管道或FIFO的最后一次关闭发生时，仍在该管道或FIFO上的数据将被丢弃。消息队列，除非系统重启或显式删除，否则其一直存在。

　对于系统中的每个消息队列，内核维护一个定义在<sys/msg.h>头文件中的信息结构。

```c
struct msqid_ds {
    struct ipc_perm msg_perm ; 
    struct msg*    msg_first ; //指向队列中的第一个消息
    struct msg*    msg_last ; //指向队列中的最后一个消息
    ……
} ;

```

下面是一个接收消息队列数据的代码：

```c
#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <errno.h>
#include <sys/msg.h>

struct msg_st
{
    long int msg_type;
    char text[BUFSIZ];
};

int main()
{
    int running = 1;
    int msgid = -1;
    struct msg_st data;
    long int msgtype = 0; //注意1

    //建立消息队列
    msgid = msgget((key_t)1234, 0666 | IPC_CREAT);
    if(msgid == -1)
    {
        fprintf(stderr, "msgget failed with error: %d\n", errno);
        exit(EXIT_FAILURE);
    }
    //从队列中获取消息，直到遇到end消息为止
    while(running)
    {
        if(msgrcv(msgid, (void*)&data, BUFSIZ, msgtype, 0) == -1)
        {
            fprintf(stderr, "msgrcv failed with errno: %d\n", errno);
            exit(EXIT_FAILURE);
        }
        printf("You wrote: %s\n",data.text);
        //遇到end结束
        if(strncmp(data.text, "end", 3) == 0)
            running = 0;
    }
    //删除消息队列
    if(msgctl(msgid, IPC_RMID, 0) == -1)
    {
        fprintf(stderr, "msgctl(IPC_RMID) failed\n");
        exit(EXIT_FAILURE);
    }
    exit(EXIT_SUCCESS);
}

```

下面是对消息队列写消息的例子：

```c
#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <sys/msg.h>
#include <errno.h>

#define MAX_TEXT 512
struct msg_st
{
    long int msg_type;
    char text[MAX_TEXT];
};

int main()
{
    int running = 1;
    struct msg_st data;
    char buffer[BUFSIZ];
    int msgid = -1;

    //建立消息队列
    msgid = msgget((key_t)1234, 0666 | IPC_CREAT);
    if(msgid == -1)
    {
        fprintf(stderr, "msgget failed with error: %d\n", errno);
        exit(EXIT_FAILURE);
    }

    //向消息队列中写消息，直到写入end
    while(running)
    {
        //输入数据
        printf("Enter some text: ");
        fgets(buffer, BUFSIZ, stdin);
        data.msg_type = 1;    //注意2
        strcpy(data.text, buffer);
        //向队列发送数据
        if(msgsnd(msgid, (void*)&data, MAX_TEXT, 0) == -1)
        {
            fprintf(stderr, "msgsnd failed\n");
            exit(EXIT_FAILURE);
        }
        //输入end结束输入
        if(strncmp(buffer, "end", 3) == 0)
            running = 0;
        sleep(1);
    }
    exit(EXIT_SUCCESS);
}

```
### 4.4 共享内存

采用共享内存通信的一个显而易见的好处是效率高，因为进程可以直接读写内存，而不需要任何数据的拷贝。对于像管道和消息队列等通信方式，则需要在内核和用户空间进行四次的数据拷贝，而共享内存则只拷贝两次数据：


* 一次从输入文件到共享内存区
* 另一次从共享内存区到输出文件。


现代Linux有两种共享内存机制：


* POSIX共享内存（shm_open()、shm_unlink()）
* System V共享内存（shmget()、shmat()、shmdt()）


其中，System V共享内存历史悠久；而POSIX共享内存机制接口更加方便易用，一般是结合内存映射mmap使用。

mmap和System V共享内存的主要区别在于：


* sysv shm是持久化的，除非被一个进程明确的删除，否则它始终存在于内存里，直到系统关机；
* mmap映射的内存在不是持久化的，如果进程关闭，映射随即失效，除非事先已经映射到了一个文件上。


内存映射机制mmap是POSIX标准的系统调用，有匿名映射和文件映射两种。


* 匿名映射使用进程的虚拟内存空间，它和malloc(3)类似，实际上有些malloc实现会使用mmap匿名映射分配内存，不过匿名映射不是POSIX标准中规定的。
* 文件映射有MAP_PRIVATE和MAP_SHARED两种。前者使用COW的方式，把文件映射到当前的进程空间，修改操作不会改动源文件。后者直接把文件映射到当前的进程空间，所有的修改会直接反应到文件的page cache，然后由内核自动同步到映射文件上。


相比于IO函数调用，基于文件的mmap的一大优点是把文件映射到进程的地址空间，避免了数据从用户缓冲区到内核page cache缓冲区的复制过程；当然还有一个优点就是不需要频繁的read/write系统调用, 比较适合随机读取文件内容的场景。

由于接口易用，且可以方便的persist到文件，避免主机shutdown丢失数据的情况，所以在现代操作系统上一般偏向于使用mmap而不是传统的System V的共享内存机制。

建议仅把mmap用于需要大量内存数据操作的场景，而不用于IPC。因为IPC总是在多个进程之间通信，而通信则涉及到同步问题，如果自己手工在mmap之上实现同步，容易滋生bug。推荐使用socket之类的机制做IPC，基于socket的通信机制相对健全很多，有很多成熟的机制和模式，比如epoll, reactor等。

下面是一个mmap共享内存的范例：

```c
#include<stdio.h>
#include<sys/mman.h>
#include<unistd.h>
#include<stdlib.h>
#include<string.h>
#include<fcntl.h>
#include<errno.h>
#include<sys/types.h>
#include<sys/stat.h>


#define ERR_EXIT(m)\
    do\
    {\
        perror(m);\
        exit(EXIT_FAILURE);\
    }while(0)


int main()
{
    int fd = open("/home/jay/linux/test.txt",O_CREAT|O_WRONLY,0666); //1
    if(-1 == fd)
        ERR_EXIT("open");

    struct stat buf;
    fstat(fd,&buf);
    void* p = mmap(NULL,(int)buf.st_size,PROT_WRITE,MAP_SHARED,fd,0);

    if(MAP_FAILED == p)   //MAP_FAILED 是一个宏 等于 (void*)-1
        ERR_EXIT("mmap");

    strcpy(p,"hello world");

    if(-1 == munmap(p,buf.st_size))
        ERR_EXIT("munmp");

    return 0;
}

```

下面是一个system V api实现的内存共享范例：

```c
#include<stdio.h>
#include<sys/types.h>
#include<string.h>
#include<unistd.h>
#include<stdlib.h>
#include<sys/ipc.h>
#include<sys/shm.h>

#define ERR_EXIT(m)\
    do\
    {\
        perror(m);\
        exit(EXIT_FAILURE);\
    }while(0)


int main()
{
    key_t key=ftok("/tmp",12343);
    if(-1 == key)
        ERR_EXIT("ftok");

    int shmid = shmget(key,1024,IPC_CREAT|IPC_EXCL|0666);  //创建一个共享内存
    if(-1 == shmid)
        ERR_EXIT("shmget");

    void* p = shmat(shmid,NULL,0);  
    if((void*)-1 == p)
        ERR_EXIT("shmat");

    strcpy(p,"11111111111");

    shmdt(p);    //只是断开连接而已

    return 0;
}

```
### 4.5 信号量

为了防止出现因多个程序同时访问一个共享资源而引发的一系列问题，我们需要一种方法，它可以通过生成并使用令牌来授权，在任一时刻只能有一个执行线程访问代码的临界区域。临界区域是指执行数据更新的代码需要独占式地执行。而信号量就可以提供这样的一种访问机制，让一个临界区同一时间只有一个线程在访问它，也就是说信号量是用来调协进程对共享资源的访问的。

　　信号量是一个特殊的变量，程序对其访问都是原子操作，且只允许对它进行等待（即P(信号变量))和发送（即V(信号变量))信息操作。最简单的信号量是只能取0和1的变量，这也是信号量最常见的一种形式，叫做二进制信号量。而可以取多个正整数的信号量被称为通用信号量。这里主要讨论二进制信号量。

　　由于信号量只能进行两种操作等待和发送信号，即P(sv)和V(sv),他们的行为是这样的：


* P(sv)：如果sv的值大于零，就给它减1；如果它的值为零，就挂起该进程的执行
* V(sv)：如果有其他进程因等待sv而被挂起，就让它恢复运行，如果没有进程因等待sv而挂起，就给它加1.


下面是一个信号量实现临界区访问的示例代码：

```c
#include <unistd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <sys/sem.h>

union semun
{
    int val;
    struct semid_ds *buf;
    unsigned short *arry;
};

static int sem_id = 0;

static int set_semvalue();
static void del_semvalue();
static int semaphore_p();
static int semaphore_v();

int main(int argc, char *argv[])
{
    char message = 'X';
    int i = 0;

    //创建信号量
    sem_id = semget((key_t)1234, 1, 0666 | IPC_CREAT);

    if(argc > 1)
    {
        //程序第一次被调用，初始化信号量
        if(!set_semvalue())
        {
            fprintf(stderr, "Failed to initialize semaphore\n");
            exit(EXIT_FAILURE);
        }
        //设置要输出到屏幕中的信息，即其参数的第一个字符
        message = argv[1][0];
        sleep(2);
    }
    for(i = 0; i < 10; ++i)
    {
        //进入临界区
        if(!semaphore_p())
            exit(EXIT_FAILURE);
        //向屏幕中输出数据
        printf("%c", message);
        //清理缓冲区，然后休眠随机时间
        fflush(stdout);
        sleep(rand() % 3);
        //离开临界区前再一次向屏幕输出数据
        printf("%c", message);
        fflush(stdout);
        //离开临界区，休眠随机时间后继续循环
        if(!semaphore_v())
            exit(EXIT_FAILURE);
        sleep(rand() % 2);
    }

    sleep(10);
    printf("\n%d - finished\n", getpid());

    if(argc > 1)
    {
        //如果程序是第一次被调用，则在退出前删除信号量
        sleep(3);
        del_semvalue();
    }
    exit(EXIT_SUCCESS);
}

static int set_semvalue()
{
    //用于初始化信号量，在使用信号量前必须这样做
    union semun sem_union;

    sem_union.val = 1;
    if(semctl(sem_id, 0, SETVAL, sem_union) == -1)
        return 0;
    return 1;
}

static void del_semvalue()
{
    //删除信号量
    union semun sem_union;

    if(semctl(sem_id, 0, IPC_RMID, sem_union) == -1)
        fprintf(stderr, "Failed to delete semaphore\n");
}

static int semaphore_p()
{
    //对信号量做减1操作，即等待P（sv）
    struct sembuf sem_b;
    sem_b.sem_num = 0;
    sem_b.sem_op = -1;//P()
    sem_b.sem_flg = SEM_UNDO;
    if(semop(sem_id, &sem_b, 1) == -1)
    {
        fprintf(stderr, "semaphore_p failed\n");
        return 0;
    }
    return 1;
}

static int semaphore_v()
{
    //这是一个释放操作，它使信号量变为可用，即发送信号V（sv）
    struct sembuf sem_b;
    sem_b.sem_num = 0;
    sem_b.sem_op = 1;//V()
    sem_b.sem_flg = SEM_UNDO;
    if(semop(sem_id, &sem_b, 1) == -1)
    {
        fprintf(stderr, "semaphore_v failed\n");
        return 0;
    }
    return 1;
}

```
### 4.6 套接字

套接字（socket）是一种通信机制，凭借这种机制，客户/服务器（即要进行通信的进程）系统的开发工作既可以在本地单机上进行，也可以跨网络进行。也就是说它可以让不在同一台计算机但通过网络连接计算机上的进程进行通信。


![][6]


socket通信流程

## 5 进程相关命令
### 5.1 ps/pstree

ps命令参考前文 [Linux内存][7] 这里还有额外补充一下关系进程状态的说明。通过ps aux可以看到进程的状态。

O：进程正在处理器运行,这个状态从来没有见过.

S：休眠状态（sleeping）

R：等待运行（runable）R Running or runnable (on run queue) 进程处于运行或就绪状态

I：空闲状态（idle）

Z：僵尸状态（zombie）

T：跟踪状态（Traced）

B：进程正在等待更多的内存页

D: 不可中断的深度睡眠，一般由IO引起，同步IO在做读或写操作时，cpu不能做其它事情，只能等待，这时进程处于这种状态，如果程序采用异步IO，这种状态应该就很少见到了。

pstree命令将所有进程以树状图显示，基本用法如下描述：

```
pstree [-a] [-c] [-h|-Hpid] [-l] [-n] [-p] [-u] [-G|-U] [pid|user]

```

下面是一个pstree命令的实例：

```
pstree
init─┬─acpid
     ├─agetty
     ├─bcron_start───sleep
     ├─bns_nginx───bns_nginx
     ├─casio-loader64───scribed───5*[{scribed}]
     ├─casio-loader64───casio-agent───5*[{casio-agent}]
     ├─casio-loader64───log_counter_col───3*[{log_counter_col}]

```
### 5.2 kill/killall

kill 的用法：

```
kill ［信号代码］ 进程ID（kill  -pid）
－s：指定发送的信号。 
－p：模拟发送信号。 
－l：指定信号的名称列表。 
pid：要中止进程的ID号。 
Signal：表示信号。
注：信号代码可以省略, 默认发送的信号是 15) SIGTERM；我们常用的信号代码是-9 ，表示强制终止；对于僵尸进程，可以用kill -9 来强制终止退出；

```

killall命令使用进程的名称来杀死进程，使用此指令可以杀死一组同名进程。我们可以使用kill命令杀死指定进程PID的进程，如果要找到我们需要杀死的进程，我们还需要在之前使用ps等命令再配合grep来查找进程，而killall把这两个过程合二为一，是一个很好用的命令, 用法：

```
killall 参数 进程名字
-e：对长名称进行精确匹配；
-l：忽略大小写的不同； 
-p：杀死进程所属的进程组；
-i：交互式杀死进程，杀死进程前需要进行确认； 
-l：打印所有已知信号列表；
-q：如果没有进程被杀死。则不输出任何信息；
-r：使用正规表达式匹配要杀死的进程名称； 
-s：用指定的进程号代替默认信号“SIGTERM”；
-u：杀死指定用户的进程。

```
### 5.3 ipcs/ipcrm

ipcs命令用于报告Linux中进程间通信设施的状态，显示的信息包括消息列表、共享内存和信号量的信息。用法如下：

```
ipcs 选项
-a：显示全部可显示的信息； 
-q：显示活动的消息队列信息； 
-m：显示活动的共享内存信息； 
-s：显示活动的信号量信息。

```

下面是一个ipcs命令输出

```
ipcs -a

------ Shared Memory Segments --------
key        shmid      owner      perms      bytes      nattch     status

------ Semaphore Arrays --------
key        semid      owner      perms      nsems

------ Message Queues --------
key        msqid      owner      perms      used-bytes   messages

```

ipcrm命令用来删除一个或更多的消息队列、信号量集或者共享内存标识。基本用法如下描述：

```
 ipcrm [-M shmkey] [-m shmid] [-Q msgkey] [-q msqid] [-S semkey] [-s semid] ...

-m SharedMemory id 删除共享内存标识 SharedMemoryID。与 SharedMemoryID 有关联的共享内存段以及数据结构都会在最后一次拆离操作后删除。 
-M SharedMemoryKey 删除用关键字 SharedMemoryKey 创建的共享内存标识。与其相关的共享内存段和数据结构段都将在最后一次拆离操作后删除。 
-q MessageID 删除消息队列标识 MessageID 和与其相关的消息队列和数据结构。 
-Q MessageKey 删除由关键字 MessageKey 创建的消息队列标识和与其相关的消息队列和数据结构。 
-s SemaphoreID 删除信号量标识 SemaphoreID 和与其相关的信号量集及数据结构。 
-S SemaphoreKey 删除由关键字 SemaphoreKey 创建的信号标识和与其相关的信号量集和数据结构。

```
## 6. 其他
### 6.1 进程/线程context switch消耗

线程和进程在内核的管理结构 task struct是一样的，切换的差别也只是是否刷新TLB，刷新TLB本身不费时，只是刷新后，TLB miss会比较影响性能，但这不能算在“切换”时间内。真正的切换时间只有寄存器的保持到内存中，并把将要执行的线程/进程的上下文从内存拷贝到寄存器中，就完成了一次切换，非常快。
## 7. 参考


* [https://xylinuxer.gitbooks.io/lkpa/content/%E7%AC%AC%E4%B8%89%E7%AB%A0/section3_1.html][8]
* [http://www.jianshu.com/p/c1015f5ffa74][9]
* [http://blog.chinaunix.net/uid-26833883-id-3227144.html][10]
* [https://www.ibm.com/developerworks/cn/linux/l-ipc/part1/index.html][11]
* [https://www.ibm.com/developerworks/cn/linux/l-ipc/part2/index1.html][12]
* [http://blog.csdn.net/yang_yulei/article/details/19772649][13]
* [http://www.cnblogs.com/linuxbug/p/4882776.html][14]
* [http://www.voidcn.com/blog/kannimad/article/p-4141585.html][15]
* [http://blog.jqian.net/post/linux-shm.html][16]
* [https://www.zhihu.com/question/21342211][17]
* [http://linuxperf.com/?p=33][18]



[7]: https://www.jianshu.com/p/97871c14aaf2
[8]: https://link.jianshu.com?t=https://xylinuxer.gitbooks.io/lkpa/content/%E7%AC%AC%E4%B8%89%E7%AB%A0/section3_1.html
[9]: https://www.jianshu.com/p/c1015f5ffa74
[10]: https://link.jianshu.com?t=http://blog.chinaunix.net/uid-26833883-id-3227144.html
[11]: https://link.jianshu.com?t=https://www.ibm.com/developerworks/cn/linux/l-ipc/part1/index.html
[12]: https://link.jianshu.com?t=https://www.ibm.com/developerworks/cn/linux/l-ipc/part2/index1.html
[13]: https://link.jianshu.com?t=http://blog.csdn.net/yang_yulei/article/details/19772649
[14]: https://link.jianshu.com?t=http://www.cnblogs.com/linuxbug/p/4882776.html
[15]: https://link.jianshu.com?t=http://www.voidcn.com/blog/kannimad/article/p-4141585.html
[16]: https://link.jianshu.com?t=http://blog.jqian.net/post/linux-shm.html
[17]: https://link.jianshu.com?t=https://www.zhihu.com/question/21342211
[18]: https://link.jianshu.com?t=http://linuxperf.com/?p=33
[0]: ./img/6497917-f4b161b653c9be30.png
[1]: ./img/6497917-4aa49ca76e72bfb0.png
[2]: ./img/6497917-3a81f99b3292e1aa.png
[3]: ./img/6497917-cd0d228f9f95905c.png
[4]: ./img/6497917-4066d6cca15a3768.png
[5]: ./img/6497917-2b09f8b5d042f2d7.png
[6]: ./img/6497917-67d5383f67724a1e.png