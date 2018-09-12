## [PHP源码分析 – PHP-FPM运行模式详解](http://mojijs.com/2016/11/221271/index.html)

<font face=黑体>

前篇文章已经介绍PHP-FPM的运行原理。本文将细说PHP-FPM的运行模式。实际上，在上一篇文章简单提到过FPM运行模式，之所以把这块内容拉出单独讲解，笔者认为它是一个值得学习及借鉴的地方。

FPM支持`static`、`ondemand`、`dynamic`三种运行模式。我们可以通过修改`php-fpm.conf`配置文件中的 **pm参数**进行设置.

#### **static模式**

static：又称静态模式，该模式比较容易理解，即启动时分配固定的进程数。 执行流程：fpm_run()->fpm_children_create_initial()。fpm_children_create_initial内部调用fpm_children_make()分配worker进程。worker进程循环接受和处理请求。

```c
    fpm_children_make(wp, 0 /* not in event loop yet */, 0, 1);

    int fpm_children_make(struct fpm_worker_pool_s *wp, int in_event_loop, int nb_to_spawn, int is_debug)
    {
        ......
        //fork子进程
        pid = fork();
        switch (pid) {
    
            case 0 :
                fpm_child_resources_use(child);
                fpm_globals.is_child = 1;
                //子进程初始化
                fpm_child_init(wp);
                return 0;
            case -1 :
                zlog(ZLOG_SYSERROR, "fork() failed");
                fpm_resources_discard(child);
                return 2;
            default :
                child->pid = pid;
                fpm_clock_get(&child->started);
                fpm_parent_resources_use(child);
        }
        ......
    }
```
#### **ondemand模式**

ondemand：按需分配子进程模式. 执行流程与上面相同。与static模式不同的是该阶段`fpm_children_create_initial`不会分配`worker`进程。
```c
if (wp->config->pm == PM_STYLE_ONDEMAND) {
    wp->ondemand_event = (struct fpm_event_s *)malloc(sizeof(struct fpm_event_s));
    ......
    memset(wp->ondemand_event, 0, sizeof(struct fpm_event_s));
    fpm_event_set(wp->ondemand_event, wp->listening_socket, FPM_EV_READ | FPM_EV_EDGE, fpm_pctl_on_socket_accept, wp);
    wp->socket_event_set = 1;
    fpm_event_add(wp->ondemand_event, 0);
    return 1;
}
```
从上图可以看出，ondemand模式注册一个接受请求事件，事件回调函数为`fpm_pctl_on_socket_accept`，然后return。   
接下来，看下`fpm_pctl_on_socket_accept`函数代码：
```c
if (wp->running_children >= wp->config->pm_max_children) {
    ......
    return;
}
for (child = wp->children; child; child = child->next) {
    if (fpm_request_is_idle(child)) {
        return;
    }
}
wp->warn_max_children = 0;
fpm_children_make(wp, 1, 1, 1);
```
从代码可以看出，`fpm_pctl_on_socket_accept`先判断worker进程数是否超过限制，如果没有，则往下继续执行。然后判断worker进程列表是否存在`fpm_request_is_idle(child)`为True的情况。

`fpm_request_is_idle()`是什么意思呢？我们来看下代码：

```c
int fpm_request_is_idle(struct fpm_child_s *child)
{
    struct fpm_scoreboard_proc_s *proc;
    proc = fpm_scoreboard_proc_get(child->wp->scoreboard, child->scoreboard_i);
    if (!proc) {
        return 0;
    }
    return proc->request_stage == FPM_REQUEST_ACCEPTING;
}
```
实际上就是判断该进程是否处于`FPM_REQUEST_ACCEPTING`阶段，`FPM_REQUEST_ACCEPTING`代表等待客户端请求。

回到`fpm_pctl_on_socket_accept`函数的执行流程，也就是说当前如果有worker进程处于空闲状态时，则return，不分配worker进程。否则继续执行，调用 `fpm_children_make`创建worker进程。

以上就是ondemand模式分配进程的模式。

现在我们思考一个问题：随着客户端请求越来越多，该模式下worker进程数会随之增多，消耗的系统资源越来越大，当系统闲时，我们希望能够关闭一些的空闲进程来降低服务器的压力，那么FPM是如何进行管理的呢？

PFM内部注册了一个定时事件`fpm_pctl_perform_idle_server_maintenance_heartbeat`来定时检查该模式下worker进程状态。
```c
if (wp->config->pm == PM_STYLE_ONDEMAND) {
    struct timeval last, now;
    if (!last_idle_child) continue;
    fpm_request_last_activity(last_idle_child, &last);
    fpm_clock_get(&now);
    //accept操作超出idle时间,则关闭子进程
    if (last.tv_sec < now.tv_sec - wp->config->pm_process_idle_timeout) {
        last_idle_child->idle_kill = 1;
        fpm_pctl_kill(last_idle_child->pid, FPM_PCTL_QUIT);
    }
    continue;
}
```
上面的代码通俗易懂，当空闲进程接受请求等待时间超过`pm_process_idle_timeout`时间，会对最后一个空闲`worker`进程发出关闭信号。整个关闭功能是一个渐进式的分步操作。这里留意下 `last.tv_sec`值，刚才有提到worker进程内部是不断循环接受请求的，每到接到一个请求后，该值都会进行更新。

SO，我们可以推出`ondemand`模式下`worker进程`并不是处理完一个请求后立刻退出，而是当进程空闲时间达到一定的阀值时才会被回收。这样可以有效地降低重复”分配->释放”的情况,一定程度上能缓解服务器的压力。

#### **dynamic模式**

dynamic：动态分配模式。启动时分配固定的进程数，随着请求需求增加，适当地增加进程数。该模式有关的几个配置参数如下。

参数名 | 说明 | 范围 
-|-|-
max_children | worker进程总数 | - 
pm_max_spare_servers | 允许最大分配的idle(空闲状态)进程数 | X <= max_children 
min_spare_servers | 允许最小分配的idle(空闲状态)进程数 | X <= min_spare_servers 
start_servers | 启动分配的worker进程数 | min_spare_servers <= X <=pm_max_spare_servers 

执行的流程：启动时`fpm_children_create_initial`内部调用`fpm_children_make()`分配固定的worker进程,然后主进程通过执行`fpm_pctl_perform_idle_server_maintenance_heartbeat`定时事件，根据需求进行扩容或者回收worker进程。

`fpm_pctl_perform_idle_server_maintenance_heartbeat`有关dynamic模式代码如下：

```c
//idle : 空闲的进程数
//last_idle_child : 最后一个idle进程
//wp->config->pm_max_spare_servers : 允许最大分配的空闲worker(idle)进程数
//wp->config->pm_min_spare_servers : 运行最小分配的空闲worker(idle)进程数
//wp->config->pm_max_children : 进程总数
//wp->running_children : 已启动的进程数
//wp->idle_spawn_rate : 默认为1，分配进程基数
#define FPM_MAX_SPAWN_RATE (32)
//当idle > wp->config->pm_max_spare_servers时，则进行回收进程。
if (idle > wp->config->pm_max_spare_servers && last_idle_child) {
    last_idle_child->idle_kill = 1;
    fpm_pctl_kill(last_idle_child->pid, FPM_PCTL_QUIT);
    wp->idle_spawn_rate = 1;
    continue;
}
//当idle进程数 < wp->config->pm_min_spare_servers时，分配进程。
if (idle < wp->config->pm_min_spare_servers) {
    if (wp->running_children >= wp->config->pm_max_children) {
        ......
        wp->idle_spawn_rate = 1;
        continue;
    }
    children_to_fork = MIN(wp->idle_spawn_rate, wp->config->pm_min_spare_servers - idle);
    children_to_fork = MIN(children_to_fork, wp->config->pm_max_children - wp->running_children);
    if (children_to_fork <= 0) {
        .......
        continue;
    }
    wp->warn_max_children = 0;
    fpm_children_make(wp, 1, children_to_fork, 1);
    ......
    //当wp->idle_spawn_rate < FPM_MAX_SPAWN_RATE时，wp->idle_spawn_rate值以2倍数进行增长。
    if (wp->idle_spawn_rate < FPM_MAX_SPAWN_RATE) {
        wp->idle_spawn_rate *= 2;
    }
    continue;
}
```
上面的代码分为两部分.   
第一部分代码功能用于释放超出限制的idle进程，判断idle是否大于 `wp->config->pm_max_spare_servers` , 如果是，则回收最后一个`idle`进程。与ondemand模式一样，这也是一个分步执行的过程。   
第二部分代码功能用于扩充idle进程数。首先判断idle数是否小于`wp->config->pm_min_spare_servers`,如果是，则调用`fpm_children_make`扩充worker进程，否则，退出。

**扩充的进程数=MIN(`wp->idle_spawn_rate`, `wp->config->pm_min_spare_servers - idle`,`wp->config->pm_max_children - wp->running_children`)。**

#### **总结**

FPM提供了static、ondemand、dynamic三种运行模式。前者比较简单；后两者稍微复杂些，内部通过`fpm_pctl_perform_idle_server_maintenance_heartbeat`定时事件来管理worker进程。   
ondemand模式下worker进程并不是处理完一个请求后才释放，而是当处于空闲状态的时间达到一定的阀值时进行回收。   
dynamic模式下根据`idle数值`合理地扩充和回收worker进程。

</font>