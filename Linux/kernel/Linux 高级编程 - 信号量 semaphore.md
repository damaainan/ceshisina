# Linux 高级编程 - 信号量 semaphore

 时间 2017-09-14 08:19:41  

原文[http://blog.csdn.net/qq_22075977/article/details/77973186][2]


## 信号量 semaphore

信号量（semaphore）与之前介绍的管道，消息队列的等 IPC 的思想不同， **信号量是一个计数器** ，用来为多个进程或线程提供对共享数据的访问。 

## 信号量的原理

常用的信号量是 **二值信号量** ，它控制单个共享资源，初始值为 1，操作如下： 

1. 测试该信号量是否可用

2. 若信号量为 1，则当前进程使用共享资源，并将信号量减 1（加锁）

3. 若信号量为 0，则当前进程不可以使用共享资源并休眠，必须等待信号量为 1 时进程才能继续执行（解锁）

要注意因为是使用信号量来保护共享资源，所以信号量本身的操作不能被打断，即必须是 **原子操作** ，因此由内核来实现信号量。 

## 查看信号量

类似消息队列和共享内存，我们也可以使用 `ipcs` 命令来查看当前系统的信号量资源： 

    ipcs -s
    
    ------ Semaphore Arrays --------
    key        semid      owner      perms      nsems

目前我的系统中没有信号量，在后面例子中会使用这个命令来查看创建的信号量。

## 信号量的基本操作

Linux 内核提供了一套对信号量的操作，包括获取，设置，操作信号量，下面就来学习具体的 API。

### 1. 获取信号量

使用 `semget` 来创建或获取一个与 key 有关的信号量。 

```c
    #include <sys/types.h>
    #include <sys/ipc.h>
    #include <sys/sem.h>
    
    /*
     * key：返回的 ID 与 key 有关系
     * nsems：信号量的值
     * semflg：创建标记
     * return：成功返回信号量 ID，失败返回 -1，并设置 erron
     */
    int semget(key_t key, int nsems, int semflg);
```

关于参数的详细解释参考 `man semget`

### 2. 操作信号量

使用 `semop` 可以对一个信号量加 1 或者减 1： 

```c
    #include <sys/types.h>
    #include <sys/ipc.h>
    #include <sys/sem.h>
    
    /*
     * semid：信号量 ID
     * sops：对信号量的操作
     * nsops：要操作的信号数量
     * return：成功返回 0，失败返回 -1，并设置 erron
     */
    int semop(int semid, struct sembuf *sops, size_t nsops);
```

`sembuf` 表示了对信号量操作的属性： 

```c
    struct sembuf {
        /* 信号量的个数，除非使用多个信号量，否则设置为 0 */
        unsigned short sem_num;  
    
        /* 信号量的操作，-1 表示 p 操作，1 表示 v 操作 */
        short          sem_op;   
    
        /* 通常设置为 SEM_UNDO，使得 OS 能够跟踪信号量并在没有释放时自动释放 */
        short          sem_flg;  
    };
```

在进行信号量的 `pv` 操作时都是使用这个结构作为参数，详细解释参考 `man semop` 。 

### 3. 设置信号量

使用 `semctl` 可以设置一个信号量的初始值： 

```c
    #include <sys/types.h>
    #include <sys/ipc.h>
    #include <sys/sem.h>
    
    /*
     * semid：要设置的信号量 ID
     * semnum：要设置的信号量的个数
     * cmd：设置的属性
     */
    int semctl(int semid, int semnum, int cmd, ...);
```

第 4 个参数的类型是 `union semun` 结构： 

```c
    union semun {
        int              val;    /* Value for SETVAL */
        struct semid_ds *buf;    /* Buffer for IPC_STAT, IPC_SET */
        unsigned short  *array;  /* Array for GETALL, SETALL */
    };
```

在使用信号量时 **必须手动定义这个结构** ，并且在初始化设置信号量（SETVAL）时需要使用这个参数，详细解释可以参考 man semctl 。 

## 例子：使用信号量进行进程间的同步

下面来学习一个实际使用信号量来进行进程间通信的例子，例子实现的功能是： **一个程序的两个实例同步访问同一段代码** ，先来看看使用的关键的函数。 

### 1. 获取信号量

在这个例子中将获取信号量包装成一个函数 `sem_get` ： 

```c
    // 创建或获取一个信号量
    int sem_get(int sem_key) {
        int sem_id = semget(sem_key, 1, IPC_CREAT | 0666);
    
        if (sem_id == -1) {
            printf("sem get failed.\n");
            exit(-1);
        } else {
            printf("sem_id = %d\n", sem_id);
            return sem_id;
        }   
    }
```

创建或者获取成功打印信号量的 id，否则打印错误信息。

### 2. 初始化信号量

我们只初始化一个信号量，并设置 val = 1 ： 

```c
    // 初始化信号量
    int set_sem(int sem_id) {
        union semun sem_union;  
        sem_union.val = 1;  
        if(semctl(sem_id, 0, SETVAL, sem_union) == -1) { 
            fprintf(stderr, "Failed to set sem\n");  
            return 0;  
        }
        return 1;  
    }
```

主要使用了 `union semun` 作为第 4 个参数，其中 `sem_union.val = 1` ，并且第 3 个参数必须为 `SETVAL` 。 

### 3. 删除信号量

虽然可以指定 OS 自动释放信号量，但这个还是要介绍手动释放的方法：

```c
    // 删除信号量  
    void del_sem(int sem_id) {  
        union semun sem_union;  
        if(semctl(sem_id, 0, IPC_RMID, sem_union) == -1)  
            fprintf(stderr, "Failed to delete sem, sem has been del.\n");  
    }
```

第 3 个参数指定 `IPC_RMID` 来删除信号量。 

### 4. 信号量的 PV 操作

下面的函数将信号量的 val 减 1，实现了 PV 操作：

```c
    // 减 1，加锁，P 操作
    void sem_down(int sem_id) {
        if (-1 == semop(sem_id, &sem_lock, 1)) 
            fprintf(stderr, "semaphore lock failed.\n");
    }
    
    // 加 1，解锁，V 操作
    void sem_up(int sem_id) {
        if (-1 == semop(sem_id, &sem_unlock, 1))
            fprintf(stderr, "semaphore unlock failed.\n");
    }
```

### 5. main 函数

最后来看看主程序的逻辑，先创建或获取信号量，然后在第一次调用时初始化，接着执行 PV 操作，最后在第二次调用后删除信号量：

```c
    int main(int argc, char **argv) {
        int sem_id = sem_get(12);
    
        // 第一次调用多加一个参数，第二次调用不加参数，仅在第一次调用时创建信号量
        if (argc > 1 && (!set_sem(sem_id))) {
            printf("set sem failed.\n");
            return -1;
        }
    
        // P 操作
        sem_down(sem_id);
        printf("sem lock...\n");
    
        printf("do something...\n");
        sleep(10);
    
        // V 操作
        sem_up(sem_id);
        printf("sem unlock...\n");
    
        // 第二次调用后删除信号量
        if (argc == 1)
            del_sem(sem_id);    
    
        return 0;
    }
```

### 6. 编译，运行，测试

先编译：

    gcc sem.c -o sem

在第一个终端运行，我们多加一个无用的参数来表示这是第一次运行：

    ./sem 1
    
    sem_id = 0
    sem lock...
    do something...
    # 10 s 等待
    sem unlock...

我们使用 ipcs -s 查看一下当前系统中的信号量： 

    ipcs -s
    
    ------ Semaphore Arrays --------
    key        semid      owner      perms      nsems     
    0x0000000c 0          orange     666        1

看到用户 orange 已经成功创建了 **一个权限为 666 ，ID 为 0 的信号量** 了，再打开第二个终端，不加额外的参数再运行一次： 

    ./sem
    
    sem_id = 0
    # 第一个终端打印完 sem unlock 后
    sem lock...
    do something...
    # 10 s 等待
    sem unlock...

因为是第二次运行，所以最后信号量会被删除，我们再来看看 ipcs -s 的结果： 

    ipcs -s
    
    ------ Semaphore Arrays --------
    key        semid      owner      perms      nsems

可以看到信号量被 **成功删除** 了，这个效果亲自运行测试后可以理解的更加深刻，这两个进程是同步访问 do something 这部分代码的，第二个进程会等待第一个进程 unlock 后再运行，建议你[下载代码]({{ site.url }}/file/sem/sem.c)实际运行一下。 

## 拓展：信号量在 Linux 内核中的实现机制

最后，我们再来简单分析下信号量在 Linux 内核中的实现机制，了解机制可以帮助我们更好的理解和使用信号量。其实内核中的共享内存，消息队列和信号量的实现机制几乎是相同的，信号量也是开辟一片内存，然后对链表进行操作。

### 1. glibc 信号量函数分析

```c
    int semget (key, nsems, semflg)
    key_t key;
    int nsems;
    int semflg;
    {
        return INLINE_SYSCALL (ipc, 5, IPCOP_semget, key, nsems, semflg, NULL);
    }
```

`semget` 函数直接使用 `INLINE_SYSCALL` 进行系统调用陷入内核， `semop` 和 `semctl` 也是类似，下面来看看内核中的实现。 

### 2. semget 分析

`semget` 函数为信号量开辟一片新的内存，内核中的调用如下，也是使用了 `ipc_ops` 这个数据结构： 

![][4]

其中回调了 `newary` 这个函数，它完成信号量的创建和获取： 

![][5]

可以看出，整个过程与消息队列和共享内存几乎相同。

### 3. semop 分析

`semop` 对信号量进行 PV 操作，其中主要是对 `sem_op` 进行加 1 或者减 1，大体的过程如下： 

![][6]

### 4. semctl 分析

`semctl` 对信号量进行控制，主要是使用 `switch` 来判断当前的命令然后执行相应的操作： 

![][7]

要注意的是，主要的处理逻辑在 `semctl_main` 这个函数中，其中每个 `cmd` 都有具体的执行逻辑，有兴趣可以详细分析。 

## 结语

本次就简单地介绍了信号量的基本操作和内核的实现机制，对与信号量的应用并没有介绍太多，更多的应用方法还需要在实际工作中去实践。建议你将共享内存，消息队列和信号量自己总结对照分析一遍，看看它们的实现机制是不是几乎相同的，这可以加深你对他们的理解，了解些原理总是有些好处的。那我们下次再见，谢谢你的阅读。


[2]: http://blog.csdn.net/qq_22075977/article/details/77973186

[4]: ../IMG/3IbiMjY.png
[5]: ../IMG/ANnaI3B.png
[6]: ../IMG/juyYrqU.png
[7]: ../IMG/6neQBby.png