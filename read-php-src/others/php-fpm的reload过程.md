## php-fpm的reload过程

来源：[https://blog.csdn.net/wapeyang/article/details/61913645](https://blog.csdn.net/wapeyang/article/details/61913645)

时间：

摘要：背景谈谈PHP的Reload操作 中提到reload会让sleep提前结束,所以就探究了下fpm的reload操作如何实现.本文在php7.0 fpm下分析,…


php-fpm的reload过程


背景 

 中提到reload会让sleep提前结束，所以就探究了下fpm的reload操作如何实现。


本文在PHP7.0 fpm下分析，process_control_timeout设置不为0。


重启信号 

首先，我们从  可以知道，fpm的reload操作实际上就是对fpm进程发送了USR2信号。


fpm的master进程中，  通过  注册了信号处理函数  ：

```c
int fpm_signals_init_main() /* {{{ */{ struct sigactionact;
  // 。。。。。。  memset(&act, 0, sizeof(act));
act.sa_handler = sig_handler;
sigfillset(&act.sa_mask);
  if (0 > sigaction(SIGTERM,  &act, 0) ||
    0 > sigaction(SIGINT,  &act, 0) ||
    0 > sigaction(SIGUSR1,  &act, 0) ||
    0 > sigaction(SIGUSR2,  &act, 0) ||
    0 > sigaction(SIGCHLD,  &act, 0) ||
    0 > sigaction(SIGQUIT,  &act, 0)) {

zlog(ZLOG_SYSERROR, "failed to init signals: sigaction()"); return -1;
} return 0;
}/* }}} */
```


简而言之，通过  设置为block掉所有的信号，然后通过sigaction设置对应的信号处理函数。


当我们reload fpm时，systemctl向fpm的master进程发送USR2信号，执行函数  ：

```c
static void sig_handler(int signo) /* {{{ */{ static const char sig_chars[NSIG + 1] = {
[SIGTERM] = 'T',
[SIGINT]  = 'I',
[SIGUSR1] = '1',
[SIGUSR2] = '2',
[SIGQUIT] = 'Q',
[SIGCHLD] = 'C'
}; char s;
        // ***
s = sig_chars[signo];
zend_quiet_write(sp[1], &s, sizeof(s));
errno = saved_errno;
}/* }}} */
```


关键点在 zend_quiet_write，它就是  。sig_handler函数就是向sp[ 1 ]中写入了一个字符串2。


此处需要注意的是，sp[0]和sp[1]是通过 创建的本地套接字。


master开始重启 

之前的信号处理函数，在信号发生的时候会被调用，但是程序的主逻辑仍然不会被打乱，那fpm master进程怎么知道要reload呢？


答案就在 中，这是master进程的事件循环。


在循环之前， 我们需要用 sp[0]  一个 struct fpm_event_s，添加到监听的fd中：

```c
int fpm_event_set(struct fpm_event_s *ev, int fd, int flags, void (*callback)(struct fpm_event_s *, short, void *), void *arg) /* {{{ */{ if (!ev || !callback || fd < -1) { return -1;
}
memset(ev, 0, sizeof(struct fpm_event_s));
ev->fd = fd;
ev->callback = callback;
ev->arg = arg;
ev->flags = flags; return 0;
}/* }}} */
```


然后将这个 struct fpm_event_s，也就是代码中的ev，  监听的fd中。


实际上，这个添加过程也和fpm不同的异步模型有关（都是由对应fpm_event_module_s的add方法实现的），比如  就是将ev参数整体放到epoll_event的data.ptr中的。（poll的add可以参考  ）


当所有的fd都添加了之后（当然不仅仅是signal相关的fd咯），我们就可以使用  等待事件来临了。 （epoll和poll也都各自实现了wait方法）


好，回到sig_handler给sp[1]写了个字符串2。 wait方法 接到了信号，拿到对应的ev，调用 ，实际上就是调用了  ，就是  ：

```c
static void fpm_got_signal(struct fpm_event_s *ev, short which, void *arg) /* {{{ */{ char c; int res, ret; int fd = ev->fd;
  do {
res = read(fd, &c, 1);
  switch (c) { // 。。。。。。
case '2' :                  /* SIGUSR2 */
zlog(ZLOG_DEBUG, "received SIGUSR2");
zlog(ZLOG_NOTICE, "Reloading in progress ...");
fpm_pctl(FPM_PCTL_STATE_RELOADING, FPM_PCTL_ACTION_SET); break;
}
  if (fpm_globals.is_child) { break;
}
} while (1); return;
}/* }}} */
```


如果接收到了字符串2，则执行

```c
fpm_pctl(FPM_PCTL_STATE_RELOADING, FPM_PCTL_ACTION_SET)
```


实际上就  ：

```c
void fpm_pctl(int new_state, int action) /* {{{ */{ switch (action) { case FPM_PCTL_ACTION_SET :
//。。。。。。
fpm_signal_sent = 0;
fpm_state = new_state;

zlog(ZLOG_DEBUG, "switching to '%s' state", fpm_state_names[fpm_state]); /* fall down */  case FPM_PCTL_ACTION_TIMEOUT :
fpm_pctl_action_next(); break; //。。。。。
}
}/* }}} */
```


即，将fpm_state设置为FPM_PCTL_STATE_RELOADING后，没有break，继续执行 ：

```c
static void fpm_pctl_action_next() /* {{{ */
{ int sig, timeout;
  if (!fpm_globals.running_children) {
fpm_pctl_action_last();
}
  if (fpm_signal_sent == 0) { if (fpm_state == FPM_PCTL_STATE_TERMINATING) { sig = SIGTERM;
} else { sig = SIGQUIT;
}
timeout = fpm_global_config.process_control_timeout;
} else { if (fpm_signal_sent == SIGQUIT) { sig = SIGTERM;
} else { sig = SIGKILL;
}
timeout = 1;
}

fpm_pctl_kill_all(sig);
fpm_signal_sent = sig;
fpm_pctl_timeout_set(timeout);
}
/* }}} */
```


即，给所有子进程发送SIGQUIT信号。


这边还有一个  ，这个等会讨论。


子进程处理信号 

父进程发送完信号了，就该子进程处理啦。


子进程只有  交给sig_soft_quit处理。子进程初始化完成后，收到了SIGQUIT信号，由sig_soft_quit处理，最终调用 处理：

```c
void fcgi_terminate(void){
in_shutdown = 1;
}
```


就是将in_shutdown设置为1。


子进程退出 

子进程的循环主体在 fcgi_accept_request 中，其中多出判断in_shutdown，若为1则直接退出：


超时处理 

前面提到的  是  。执行了如下操作：

```c
fpm_pctl(FPM_PCTL_STATE_UNSPECIFIED, FPM_PCTL_ACTION_TIMEOUT);
```


在这种条件下，  ，直接退出了子进程。


为何sleep会被打断？ 

我们可以看到，  就是系统调用sleep（php_sleep是sleep的一个宏）：

```c
/* {{{ proto void sleep(int seconds)
   Delay for a given number of seconds */PHP_FUNCTION(sleep)
{
zend_longnum;
  if (zend_parse_parameters(ZEND_NUM_ARGS(), "l", &num) == FAILURE) {
RETURN_FALSE;
}
php_sleep((unsigned int)num);

}/* }}} */
```


sleep函数执行时，此时进程的状态是S：


interruptiblesleep 

此时一旦有信号触发，立马处理信号，比如我们刚刚说过的SIGQUIT，结束了之后发现，sleep执行完了。


因为  写了啊： 
` 

<b>sleep</b>() makesthecallingthreadsleepuntil <i>seconds</i> secondshave 

      elapsedor a signalarriveswhichis not ignored. 

`

需要注意的是，  ，所以即使信号打断了sleep，也仅仅是跳过sleep继续执行而已。


原文链接:  [http://www.kubiji.cn/juhe-id7904.html][0]

[0]: http://www.kubiji.cn/juhe-id7904.html
[1]: http://www.kubiji.cn/juhe-id7904.html