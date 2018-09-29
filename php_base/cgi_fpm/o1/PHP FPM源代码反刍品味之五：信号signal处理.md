## PHP FPM源代码反刍品味之五：信号signal处理

2016.08.04 18:08*

来源：[https://www.jianshu.com/p/e3074fcf1b9d](https://www.jianshu.com/p/e3074fcf1b9d)


unix 的信号signal常用于进程管理.

比如管理员或者操作系统通过向master进程实现重启和关闭服务．

master进程通过向worker进程发信号管理worker进程．

通常会在进程自定义信号处理函数,处理相关的逻辑.

自定义信号处理函数,从使用者的角度看,很简单,有点像快捷键的定制.
### FPM 信号处理有以下几个特点:

* master进程,不是直接处理信号,而是通过socketpair创建一个管道,把信号转换一个字符,写到管道里,master进程事件处理无限循环,读取到这个字符时,调用对应的函数.

socketpair,通常管道不同进程通信,而这里确是在同一个进程内部通信,左手交右手,感觉多此一举.

这样做的好处是: 避免信号处理函数与事件处理逻辑同时运行的情况.

注意worker 进程没有用到这个socketpair管道.
* worker 进程的信号处理常见的方式,直接绑定处理函数.

处理过程: sig_soft_quit -> fpm_php_soft_quit -> fcgi_set_in_shutdown

fcgi_set_in_shutdown 函数很简单 就是设置in_shutdown这个全局的worker进程开关

worker进程无限循环时,每次都会检查这个开关, in_shutdown=1 时,跳出循环,优雅退出.


### 源码注释说明:

```c
//fpm_signals.c
#include "fpm_config.h"
...
//整数数组,存放socketpair创建的管道两端文件句柄
static int sp[2];
...

//worker进程信号处理函数
static void sig_soft_quit(int signo) /* {{{ */
{
    int saved_errno = errno;

    /* closing fastcgi listening socket will force fcgi_accept() exit immediately */
    close(0);
    if (0 > socket(AF_UNIX, SOCK_STREAM, 0)) {
        zlog(ZLOG_WARNING, "failed to create a new socket");
    }
    fpm_php_soft_quit();
    errno = saved_errno;
}

//master进程信号处理函数
static void sig_handler(int signo) /* {{{ */
{
    //C99 的数组初始化语法
    //信号整数和字符的对应关系.
    static const char sig_chars[NSIG + 1] = {
        [SIGTERM] = 'T',
        [SIGINT]  = 'I',
        [SIGUSR1] = '1',
        [SIGUSR2] = '2',
        [SIGQUIT] = 'Q',
        [SIGCHLD] = 'C'
    };
    char s;
    int saved_errno;

    if (fpm_globals.parent_pid != getpid()) {
        return;
    }

    saved_errno = errno;
    s = sig_chars[signo];
   //信号对应的字符写到管道
    write(sp[1], &s, sizeof(s));
    errno = saved_errno;
}


int fpm_signals_init_main() /* {{{ */
{
    struct sigaction act;
    //创建socketpair管道,管道两端的文件句柄fd 放在数组sp里
    if (0 > socketpair(AF_UNIX, SOCK_STREAM, 0, sp)) {
        zlog(ZLOG_SYSERROR, "failed to init signals: socketpair()");
        return -1;
    }

    if (0 > fd_set_blocked(sp[0], 0) || 0 > fd_set_blocked(sp[1], 0)) {
        zlog(ZLOG_SYSERROR, "failed to init signals: fd_set_blocked()");
        return -1;
    }

    if (0 > fcntl(sp[0], F_SETFD, FD_CLOEXEC) || 0 > fcntl(sp[1], F_SETFD, FD_CLOEXEC)) {
        zlog(ZLOG_SYSERROR, "falied to init signals: fcntl(F_SETFD, FD_CLOEXEC)");
        return -1;
    }

    memset(&act, 0, sizeof(act));
    act.sa_handler = sig_handler; //所有信号使用同一个处理函数
    sigfillset(&act.sa_mask);

    if (0 > sigaction(SIGTERM,  &act, 0) ||
        0 > sigaction(SIGINT,   &act, 0) ||
        0 > sigaction(SIGUSR1,  &act, 0) ||
        0 > sigaction(SIGUSR2,  &act, 0) ||
        0 > sigaction(SIGCHLD,  &act, 0) ||
        0 > sigaction(SIGQUIT,  &act, 0)) {

        zlog(ZLOG_SYSERROR, "failed to init signals: sigaction()");
        return -1;
    }
    return 0;
}

int fpm_signals_init_child() 
{
    struct sigaction act, act_dfl;

    memset(&act, 0, sizeof(act));
    memset(&act_dfl, 0, sizeof(act_dfl));

    act.sa_handler = &sig_soft_quit;
    act.sa_flags |= SA_RESTART;

    act_dfl.sa_handler = SIG_DFL; //系统默认动作
    
    //worker 进程不使用socketpair创建的管道
    close(sp[0]);
    close(sp[1]);

    if (0 > sigaction(SIGTERM,  &act_dfl,  0) ||
        0 > sigaction(SIGINT,   &act_dfl,  0) ||
        0 > sigaction(SIGUSR1,  &act_dfl,  0) ||
        0 > sigaction(SIGUSR2,  &act_dfl,  0) ||
        0 > sigaction(SIGCHLD,  &act_dfl,  0) ||
        0 > sigaction(SIGQUIT,  &act,      0)) {

        zlog(ZLOG_SYSERROR, "failed to init child signals: sigaction()");
        return -1;
    }
    return 0;
}


int fpm_signals_get_fd() 
{
    return sp[0];
}

```

master 进程的信号被写到了管道,管道另一端的处理:

```c
//fpm_events.c
static void fpm_got_signal(struct fpm_event_s *ev, short which, void *arg) 
{
    char c;
    int res, ret;
    int fd = ev->fd;

    do {
        do {
            res = read(fd, &c, 1);
        } while (res == -1 && errno == EINTR);

        if (res <= 0) {
            if (res < 0 && errno != EAGAIN && errno != EWOULDBLOCK) {
                zlog(ZLOG_SYSERROR, "unable to read from the signal pipe");
            }
            return;
        }
        //依据读取到的字符做处理
        switch (c) {
            ...
            case 'Q' :                  /* SIGQUIT */
                zlog(ZLOG_DEBUG, "received SIGQUIT");
                zlog(ZLOG_NOTICE, "Finishing ...");
                fpm_pctl(FPM_PCTL_STATE_FINISHING, FPM_PCTL_ACTION_SET);
                break;
            case '1' :                  /* SIGUSR1 */
                zlog(ZLOG_DEBUG, "received SIGUSR1");
                if (0 == fpm_stdio_open_error_log(1)) {
                    zlog(ZLOG_NOTICE, "error log file re-opened");
                } else {
                    zlog(ZLOG_ERROR, "unable to re-opened error log file");
                }

                ret = fpm_log_open(1);
                if (ret == 0) {
                    zlog(ZLOG_NOTICE, "access log file re-opened");
                } else if (ret == -1) {
                    zlog(ZLOG_ERROR, "unable to re-opened access log file");
                }
                /* else no access log are set */

                break;
            case '2' :                  /* SIGUSR2 */
                zlog(ZLOG_DEBUG, "received SIGUSR2");
                zlog(ZLOG_NOTICE, "Reloading in progress ...");
                fpm_pctl(FPM_PCTL_STATE_RELOADING, FPM_PCTL_ACTION_SET);
                break;
        }

        if (fpm_globals.is_child) {
            break;
        }
    } while (1);
    return;
}

```

