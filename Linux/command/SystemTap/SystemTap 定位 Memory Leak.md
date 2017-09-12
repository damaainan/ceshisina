# SystemTap 定位 Memory Leak

 [首页][0]  [分类][1]  [标签][2]  [留言][3]  [关于][4]  [订阅][5]  2017-01-12 | 分类 [linux][6] | 标签 [linux][7] # 前言
<font face=微软雅黑>

C/C++程序，Memory Leak是非常讨厌也是非常具有挑战的topic。简单的程序，靠code review就可以搞定，但是复杂的体系庞大的程序，很难通过code review找到对应的点，通过手段缩小范围是十分必要的。

有Valgrind的工具可以跑程序，检查内存泄漏，但是更多地用于SuperLab自测。客户现场出了内存泄漏，需要的是一种不改变程序，尽可能保留宝贵的现场的定位手段。SystemTap提供出一种动态追踪的手段，当内存泄漏正在发生时，可以通过SystemTap追踪内存的分配（malloc）和释放（free），同时记录下用户程序的调用堆栈，统计一段时间，以分配内存的堆栈作为key，而以分配出去但是尚未free的内存总量作为value，观察对内存增长贡献最大的代码路径。

我们希望有一个一致的手段，当内存泄漏发生时，我们运行追踪脚本，统计出最可疑的调用堆栈路径。

之所以琢磨这件事情，是因为我们的ceph-mds程序在某种的情况下发生了内存泄漏，驻留集内存RSS不断增长。希望通过手段，快速找到对应的调用堆栈。

# SystemTap追踪malloc and free

关于SystemTap跟踪glibc的函数，淘宝的霸爷（褚霸）早就指出了明路，对此不了解的可以参考：

[systemtap如何跟踪libc.so][8]

因为我的系统是Ubuntu，对我而言，就是要照着霸爷指的明路，调通即可。

在Ubuntu下要glibc的符号表：

    sudo apt-get install libc6-dbg
    

可以通过的dpkg -l 检查下是否装过:

    ubuntu CODE/stap » dpkg -l |grep libc6-dbg 
    ii  libc6-dbg:amd64                            2.23-0ubuntu5                                               amd64        GNU C Library: detached debugging symbols
    

装好之后，还是用淘宝霸爷的程序测试下（glibc的路径不同，略有修改）：

    cat t.c
    #include <stdlib.h>
     
    void fun() {
      malloc(1000);
    }
     
    int main(int argc, char *argv[]) {
      fun();
      return 0;
    }
     
    $cat m.stp
    probe process("/lib/x86_64-linux-gnu/libc.so.6").function("malloc") {
        if (target()== pid()) {
            print_ubacktrace();
            exit();
        }
    }
    probe begin {
        println("~");
    }
    

通过stap来探测malloc的运行，如下所示：

    ubuntu CODE/stap » sudo stap m.stp -c /home/manu/CODE/C/t -d /home/manu/CODE/C/t
    ~
     0x7fef26c15580 : __libc_malloc+0x0/0x1a0 [/lib/x86_64-linux-gnu/libc-2.23.so]
     0x400534 : fun+0xe/0x11 [/home/manu/CODE/C/t]
     0x400550 : main+0x19/0x29 [/home/manu/CODE/C/t]
     0x7fef26bb2830 : __libc_start_main+0xf0/0x1d0 [/lib/x86_64-linux-gnu/libc-2.23.so]
     0x400459 : _start+0x29/0x30 [/home/manu/CODE/C/t]
    
    

在我另一个服务器节点上(glibc的版本是2.15)运行如下

    root@node-186:~# stap m.stap -c ./t -d ./t
    ~
     0x7fbcc2544f2b : __libc_malloc+0x1b/0x250 [/lib/x86_64-linux-gnu/libc-2.15.so]
     0x400502 : fun+0xe/0x10 [/root/t]
     0x40051d : main+0x19/0x2c [/root/t]
     0x7fbcc24e476d : __libc_start_main+0xed/0x1c0 [/lib/x86_64-linux-gnu/libc-2.15.so]
     0x400439 : _start+0x29/0x2c [/root/t]
    
    

当调用malloc的时候，可以打出调用栈关系。这就是我们的基础。

# SystemTap找出泄漏内存的函数堆栈

一个C/C++程序，会调用malloc和free申请和释放内存，到底那个函数栈泄露了内存？应该是我们最想知道的东西。

章亦春大神利用SystemTap写了一个神器，检查在一定的时间内，那条堆栈路径泄露出去最多的内存。当然了，该脚本统计了观察时间内每一条调用malloc 的堆栈，以函数堆栈为key值，以malloc出去的内存量减去已经free的内存量，作为“泄漏”出去的内存（加引号原因是，并不一定是真正泄漏），时间结束后，打印执行过malloc的所有的堆栈，并且输出“泄漏”出去的内存总量。

毫无疑问，如果某个调用堆栈“泄漏”出去的内存特别多，就是我们重点的怀疑对象。我不啰嗦了，大家直接看章亦春大神的代码吧：

    global ptr2bt
    global ptr2size
    global bt_stats
    global quit
    
    probe begin {
        warn("Start tracing. Wait for 10 sec to complete.\n")
    }
    
    probe process("/lib/x86_64-linux-gnu/libc.so.6").function("malloc").return {
        if (pid() == target()) {
            if (quit) {
                foreach (bt in bt_stats) {
                    print_ustack(bt)
                    printf("\t%d\n", @sum(bt_stats[bt]))
                }
    
                exit()
    
            } else {
    
                //printf("malloc: %p (bytes %d)\n", $return, $bytes)
                ptr = $return
                bt = ubacktrace()
                ptr2bt[ptr] = bt
                ptr2size[ptr] = $bytes
                bt_stats[bt] <<< $bytes
            }
        }
    }
    
    probe process("/lib/x86_64-linux-gnu/libc.so.6").function("free") {
        if (pid() == target()) {
            //printf("free: %p\n", $mem)
            ptr = $mem
    
            bt = ptr2bt[ptr]
            delete ptr2bt[ptr]
    
            bytes = ptr2size[ptr]
            delete ptr2size[ptr]
    
            bt_stats[bt] <<< -bytes
            if (@sum(bt_stats[bt]) == 0) {
                delete bt_stats[bt]
            }
        }
    }
    
    probe timer.s(10) {
        quit = 1
        delete ptr2bt
        delete ptr2size
    }
    

因为要排查的程序是ceph-mds，因此使用如下命令：

    stap -d /usr/lib/x86_64-linux-gnu/libstdc++.so.6.0.16 -d /usr/bin/ceph-mds  -d  /lib/x86_64-linux-gnu/libpthread-2.15.so  -DSTP_OVERLOAD_THRESHOLD=50000000000  -DMAXMAPENTRIES=1024000 -DMAXACTION=50000 -DMAXSKIPPED=2000 ./leaks.stp -x 372785
    

因为要检查的程序是ceph-mds，其进程ID为372785。该进程调用malloc的堆栈特别多，因此很容易超过默认的MAXMAPENTRIES，因为为了执行，不得不调大的相应的参数。

其输出为：

     0x7fe89d3e5ded : _Znwm+0x1d/0xa0 [/usr/lib/x86_64-linux-gnu/libstdc++.so.6.0.16]
     0x5e71e7 : _ZN6Server13reply_requestERNSt3tr110shared_ptrI13MDRequestImplEEiP6CInodeP7CDentry+0x37/0x300 [/usr/bin/ceph-mds]
     0x5ecd47 : _ZN6Server26handle_client_file_setlockERNSt3tr110shared_ptrI13MDRequestImplEE+0xb37/0x10f0 [/usr/bin/ceph-mds]
     0x60f061 : _ZN6Server23dispatch_client_requestERNSt3tr110shared_ptrI13MDRequestImplEE+0x471/0x670 [/usr/bin/ceph-mds]
     0x612802 : _ZN6Server21handle_client_requestEP14MClientRequest+0x542/0xcc0 [/usr/bin/ceph-mds]
     0x61340b : _ZN6Server8dispatchEP7Message+0x48b/0x540 [/usr/bin/ceph-mds]
     0x5931b7 : _ZN3MDS25handle_deferrable_messageEP7Message+0x9a7/0x1090 [/usr/bin/ceph-mds]
     0x5acdc0 : _ZN3MDS9_dispatchEP7Message+0xe10/0x13e0 [/usr/bin/ceph-mds]
     0x5ad55b : _ZN3MDS11ms_dispatchEP7Message+0x1cb/0x220 [/usr/bin/ceph-mds]
     0xa19e56 : _ZN9Messenger19ms_deliver_dispatchEP7Message+0x76/0x6e0 [/usr/bin/ceph-mds]
     0xa16ffb : _ZN13DispatchQueue5entryEv+0x45b/0x920 [/usr/bin/ceph-mds]
     0x9245cd : _ZN13DispatchQueue14DispatchThread5entryEv+0xd/0x20 [/usr/bin/ceph-mds]
     0x7fe89e140e9a : start_thread+0xda/0x340 [/lib/x86_64-linux-gnu/libpthread-2.15.so]
     0x7fe89cb5338d : clone+0x6d/0x90 [/lib/x86_64-linux-gnu/libc-2.15.so]
        680
     0x7fe89d3e5ded : _Znwm+0x1d/0xa0 [/usr/lib/x86_64-linux-gnu/libstdc++.so.6.0.16]
     0x7d1592 : _ZNSt8_Rb_treeISt4pairI7utime_tNSt3tr110shared_ptrI9TrackedOpEEES6_St9_IdentityIS6_ESt4lessIS6_ESaIS6_EE10_M_insert_EPKSt18_Rb_tree_node_baseSF_RKS6_+0x42/0x130 [/usr/bin/ceph-mds]
     0x7d17c2 : _ZNSt8_Rb_treeISt4pairI7utime_tNSt3tr110shared_ptrI9TrackedOpEEES6_St9_IdentityIS6_ESt4lessIS6_ESaIS6_EE16_M_insert_uniqueERKS6_+0x142/0x190 [/usr/bin/ceph-mds]
     0x7d0719 : _ZN9OpHistory6insertE7utime_tNSt3tr110shared_ptrI9TrackedOpEE+0x279/0x530 [/usr/bin/ceph-mds]
     0x7d0af6 : _ZN9OpTracker22unregister_inflight_opEP9TrackedOp+0x126/0x2a0 [/usr/bin/ceph-mds]
     0x7d0ce8 : _ZN9OpTracker14RemoveOnDeleteclEP9TrackedOp+0x78/0xd0 [/usr/bin/ceph-mds]
     0x5b0d39 : _ZNSt3tr114__shared_countILN9__gnu_cxx12_Lock_policyE2EED2Ev+0x49/0x90 [/usr/bin/ceph-mds]
     0x6b0693 : _ZN18C_MDS_RetryRequestD0Ev+0x23/0x60 [/usr/bin/ceph-mds]
     0x7bea6c : _ZN22MDSInternalContextBase8completeEi+0x1ec/0x260 [/usr/bin/ceph-mds]
     0x5b109d : _Z15finish_contextsI22MDSInternalContextBaseEvP11CephContextRSt4listIPT_SaIS5_EEi+0x8d/0x280 [/usr/bin/ceph-mds]
     0x6fdf65 : _ZN6Locker4evalEP6CInodeib+0x105/0xe10 [/usr/bin/ceph-mds]
     0x70daf7 : _ZN6Locker18handle_client_capsEP11MClientCaps+0x8b7/0x1d80 [/usr/bin/ceph-mds]
     0x592fdf : _ZN3MDS25handle_deferrable_messageEP7Message+0x7cf/0x1090 [/usr/bin/ceph-mds]
     0x5acdc0 : _ZN3MDS9_dispatchEP7Message+0xe10/0x13e0 [/usr/bin/ceph-mds]
     0x5ad55b : _ZN3MDS11ms_dispatchEP7Message+0x1cb/0x220 [/usr/bin/ceph-mds]
     0xa19e56 : _ZN9Messenger19ms_deliver_dispatchEP7Message+0x76/0x6e0 [/usr/bin/ceph-mds]
     0xa16ffb : _ZN13DispatchQueue5entryEv+0x45b/0x920 [/usr/bin/ceph-mds]
     0x9245cd : _ZN13DispatchQueue14DispatchThread5entryEv+0xd/0x20 [/usr/bin/ceph-mds]
     0x7fe89e140e9a : start_thread+0xda/0x340 [/lib/x86_64-linux-gnu/libpthread-2.15.so]
     0x7fe89cb5338d : clone+0x6d/0x90 [/lib/x86_64-linux-gnu/libc-2.15.so]
        112
    

堆栈在上面，“泄漏”出去的字节数在下面。上面的两个堆栈，一个泄漏了680字节，一个泄漏了112字节。因此，对泄露出的字节数排个序，就可以看出，那个堆栈的嫌疑最大了。

当然了，这个手段能帮我们缩小范围了，找到嫌疑犯，并不能说数字最大的堆栈就一定是元凶。

因为ceph-mds的程序是C++的，其符号都是这种_ZN3MDS25handle_deferrable_messageEP7Message让人很吐血很蛋疼的形式，这种形式《程序员的自我修养》一书中提到了命名规则，Linux提供了c++filt工具来转化成更加好读懂的形式：

    root@node-186:~# c++filt _ZN3MDS25handle_deferrable_messageEP7Message
    MDS::handle_deferrable_message(Message*)
    

# 容易遇到的错误

上述的流程看的很happy，褚霸和章亦春两位大神给出了思路，但是这条路容易遇到很多的问题。我把我遇到的问题，和解决的办法列一下，减少后来人的effort。

首先我的内核版本是很高的，我服务器的内核版本4.1，我自己的Desktop的版本是Ubuntu 16.04，很容易遇到的问题如下所示：

    ubuntu CODE/stap » sudo stap m.stp -c ../C/t
    In file included from /usr/share/systemtap/runtime/transport/control.c:14:0,
                     from /usr/share/systemtap/runtime/transport/transport.c:76,
                     from /usr/share/systemtap/runtime/linux/print.c:17,
                     from /usr/share/systemtap/runtime/print.c:17,
                     from /usr/share/systemtap/runtime/runtime_context.h:22,
                     from /tmp/staptMFvEL/stap_03866c8d30036065063c2a0480a89281_3107_src.c:124:
    /usr/share/systemtap/runtime/transport/symbols.c: In function ‘_stp_module_update_self’:
    /usr/share/systemtap/runtime/transport/symbols.c:243:44: error: ‘struct module’ has no member named ‘symtab’
        if (attr->address == (unsigned long) mod->symtab)
                                                ^
    /usr/share/systemtap/runtime/transport/symbols.c:245:9: error: ‘struct module’ has no member named ‘num_symtab’
          mod->num_symtab * sizeof(mod->symtab[0]);
             ^
    /usr/share/systemtap/runtime/transport/symbols.c:245:34: error: ‘struct module’ has no member named ‘symtab’
          mod->num_symtab * sizeof(mod->symtab[0]);
                                      ^
    scripts/Makefile.build:258: recipe for target '/tmp/staptMFvEL/stap_03866c8d30036065063c2a0480a89281_3107_src.o' failed
    make[1]: *** [/tmp/staptMFvEL/stap_03866c8d30036065063c2a0480a89281_3107_src.o] Error 1
    Makefile:1420: recipe for target '_module_/tmp/staptMFvEL' failed
    make: *** [_module_/tmp/staptMFvEL] Error 2
    WARNING: kbuild exited with status: 2
    Pass 4: compilation failed.  [man error::pass4]
    Tip: /usr/share/doc/systemtap/README.Debian should help you get started.
    

这个错误是说/usr/share/systemtap/runtime/transport/symbols.c中调用的struct module已经没有一个成员变量为symtab，以及blabla，这个原因是内核代码的变动：

请看如下[[Bug runtime/19644] New: linux 4.5-rc4 commit 8244062ef1][9].

内核部分，commit 8244062ef1改变了相应的成员变量，如下所示：

    diff --git a/include/linux/module.h b/include/linux/module.h
    index 4560d8f1..2bb0c30 100644
    --- a/include/linux/module.h
    +++ b/include/linux/module.h
    @@ -324,6 +324,12 @@ struct module_layout {
     #define __module_layout_align
     #endif
     
    
    +struct mod_kallsyms {
    +       Elf_Sym *symtab;
    +       unsigned int num_symtab;
    +       char *strtab;
    +};
    +
     struct module {
            enum module_state state;
     
    @@ -405,15 +411,10 @@ struct module {
     #endif
     
     #ifdef CONFIG_KALLSYMS
    -       /*
    -        * We keep the symbol and string tables for kallsyms.
    -        * The core_* fields below are temporary, loader-only (they
    -        * could really be discarded after module init).
    -        */
    -       Elf_Sym *symtab, *core_symtab;
    -       unsigned int num_symtab, core_num_syms;
    -       char *strtab, *core_strtab;
    -
    +       /* Protected by RCU and/or module_mutex: use rcu_dereference() */
    +       struct mod_kallsyms *kallsyms;
    +       struct mod_kallsyms core_kallsyms;
    +       
    

而SystemTap有些滞后，直到这个commit才修复这种不一致：

    commit 64ffc49b5deb57e19e94f67f9878f5c03e617775
    Author: David Smith <dsmith@redhat.com>
    Date:   Tue Feb 16 11:06:58 2016 -0600
    
        Fix PR19644 by updating the runtime to handle linux 4.5 commit 8244062ef1.
    
        * runtime/transport/symbols.c (_stp_module_update_self): Handle kernel
          change moving module symbol table information into a 'struct
          mod_kallsyms'.
        * runtime/linux/autoconf-mod_kallsyms.c: New autoconf test.
        * buildrun.cxx (compile_pass): Add autoconf test for 'struct
          mod_kallsyms'.
    
    commit 4075f4b3ad52cc2f41808e9c069aaa6edee4d7e0
    

改变是非常的小，如下所示：

    diff --git a/buildrun.cxx b/buildrun.cxx
    index a923a6a..3a427a8 100644
    --- a/buildrun.cxx
    +++ b/buildrun.cxx
    @@ -444,6 +444,8 @@ compile_pass (systemtap_session& s)
    
       output_autoconf(s, o, "autoconf-module_layout.c",
              "STAPCONF_MODULE_LAYOUT", NULL);
    +  output_autoconf(s, o, "autoconf-mod_kallsyms.c",
    +         "STAPCONF_MOD_KALLSYMS", NULL);
    
       o << module_cflags << " += -include $(STAPCONF_HEADER)" << endl;
    
    diff --git a/runtime/linux/autoconf-mod_kallsyms.c b/runtime/linux/autoconf-mod_kallsyms.c
    new file mode 100644
    index 0000000..e286440
    --- /dev/null
    +++ b/runtime/linux/autoconf-mod_kallsyms.c
    @@ -0,0 +1,3 @@
    +#include <linux/module.h>
    +
    +struct mod_kallsyms mk;
    diff --git a/runtime/transport/symbols.c b/runtime/transport/symbols.c
    index 41782f2..cb7964f 100644
    --- a/runtime/transport/symbols.c
    +++ b/runtime/transport/symbols.c
    @@ -248,10 +248,17 @@ static int _stp_module_update_self (void)
                found_eh_frame = true;
            }
            else if (!strcmp(".symtab", attr->name)) {
    -           _stp_module_self.sections[0].static_addr = attr->address;
    +#ifdef STAPCONF_MOD_KALLSYMS
    +           struct mod_kallsyms *kallsyms = rcu_dereference_sched(mod->kallsyms);
    +           if (attr->address == (unsigned long) kallsyms->symtab)
    +               _stp_module_self.sections[0].size =
    +                   kallsyms->num_symtab * sizeof(kallsyms->symtab[0]);
    +#else
                if (attr->address == (unsigned long) mod->symtab)
                    _stp_module_self.sections[0].size =
                        mod->num_symtab * sizeof(mod->symtab[0]);
    +#endif
    +           _stp_module_self.sections[0].static_addr = attr->address;
            }
            else if (!strcmp(".text", attr->name)) {
                _stp_module_self.sections[1].static_addr = attr->address;
    

说完了原因，然后说这种错误的解决方法：

* 打patch，将上面diff内容存成 /tmp/stap.diff,然后跳转到/usr/share/systemtap/,执行：

```
      /usr/share/systemtap $ sudo patch -p1 < /tmp/stap.diff
```

然后添加 如下内容 到 runtime/linux/autoconf-mod_kallsyms.c的头部.

      #define STAPCONF_MOD_KALLSYMS 1
    

* 卸载已有的SystemTap，源码安装SystemTap-3.0，下载地址如下： [源码下载地址][10]

安装步骤为： configure / make / make install 三部曲。

</font>

[0]: http://bean-li.github.io/
[1]: http://bean-li.github.io/categories/
[2]: http://bean-li.github.io/tags/
[3]: http://bean-li.github.io/guestbook/
[4]: http://bean-li.github.io/about/
[5]: http://bean-li.github.io/feed/
[6]: http://bean-li.github.io/categories/#linux
[7]: http://bean-li.github.io/tags/#linux
[8]: http://blog.yufeng.info/archives/2033
[9]: https://sourceware.org/ml/systemtap/2016-q1/msg00090.html
[10]: https://sourceware.org/systemtap/ftp/releases/